<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\MessageFile;
use App\Models\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagesTest extends TestCase {
    use RefreshDatabase;



    /** @test */
    public function user_can_send_message_to_another_user() {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($sender);

        $response = $this->post('/messages', [
            'content' => 'Hello!',
            'receiver_id' => $receiver->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => 'Hello!',
        ]);
    }



    /** @test */
    public function user_can_update_own_text_message() {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($sender);

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => 'Old text',
        ]);

        $response = $this->put("/messages/{$message->id}", [
            'content' => 'Updated text',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => 'Updated text',
        ]);
    }



    /** @test */
    public function user_can_delete_own_message() {
        $user = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($user);

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'content' => 'Delete me',
        ]);

        $response = $this->delete("/messages/{$message->id}");

        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }



    /** @test */
    public function sender_cannot_delete_receivers_message() {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA);

        $message = Message::create([
            'sender_id' => $userB->id,
            'receiver_id' => $userA->id,
            'content' => 'Message not deletable by receiver',
        ]);

        $response = $this->delete("/messages/{$message->id}");
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
        ]);
    }



    /** @test */
    public function user_can_send_text_with_image() {
        Storage::fake('public/message');
    
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
    
        $this->actingAs($sender);
    
        $file = UploadedFile::fake()->create('photo.jpg', 'image/jpeg');

    
        $response = $this->post('/messages', [
            'content' => 'Here is a photo',
            'receiver_id' => $receiver->id,
            'files' => [$file], // assuming you're allowing multiple files
        ]);
    
        $message = Message::latest()->first();
        Storage::disk('public')->assertExists('messages/' . $file->hashName());
    
        $this->assertDatabaseHas('message_files', [
            'message_id' => $message->id,
            'file_path' => 'messages/' . $file->hashName(),
        ]);
    }


    
    /** @test */
    public function user_can_send_text_with_document() {
        Storage::fake('public');
    
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
    
        $this->actingAs($sender);
    
        $doc = UploadedFile::fake()->create('doc.pdf', 'application/pdf');
    
        $response = $this->post('/messages', [
            'content' => 'Please review this doc',
            'receiver_id' => $receiver->id,
            'files' => [$doc],
        ]);
    
        $message = Message::latest()->first();
        Storage::disk('public')->assertExists('messages/' . $doc->hashName());
    
        $this->assertDatabaseHas('message_files', [
            'message_id' => $message->id,
            'file_path' => 'messages/' . $doc->hashName(),
        ]);
    }


    
    /** @test */
    public function user_can_view_list_of_other_users_to_chat_with() {
        $loggedUser = User::factory()->create();
        $users = User::factory()->count(3)->create();

        $this->actingAs($loggedUser);

        $response = $this->get('/messages');

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }
}
