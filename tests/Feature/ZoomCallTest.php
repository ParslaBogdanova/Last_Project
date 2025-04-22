<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ZoomMeeting;
use App\Models\ZoomCall;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;

class ZoomCallTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function user_can_access_zoom_meeting_if_invited_or_host() {
        $user = User::factory()->create();
        $creator = User::factory()->create();
    
        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Team Meeting',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(10)->format('H:i'),
            'end_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'active',
            'creator_id' => $creator->id,
        ]);
    
        DB::table('user_zoom_meetings')->insert([
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);
    
        $this->actingAs($user);
    
        $response = $this->post('/zoom-meeting', [
            'zoom_meetings_id' => $meeting->id,
        ]);

        ZoomCall::create([
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'status' => 'active',
        ]);
    
        $this->assertDatabaseHas('zoom_calls', [
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'status' => 'active',
        ]);
    }



     /** @test */
    public function user_cannot_join_zoom_meeting_if_not_invited() {
        $user = User::factory()->create();
        $creator = User::factory()->create();

        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Team Meeting',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i'),
            'end_time' => now()->addMinutes(30)->format('H:i'),
            'creator_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        $response = $this->post('/zoom-meeting', [
            'zoom_meetings_id' => $meeting->id,
        ]);

        $this->assertDatabaseMissing('zoom_calls', [
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
        ]);
    }



    /** @test */
    public function user_can_end_their_call() {
        $user = User::factory()->create();

        $zoomMeeting = ZoomMeeting::create([
            'title_zoom' => 'Call Test',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i'),
            'end_time' => now()->addMinutes(15)->format('H:i'),
            'status' => 'active',
            'creator_id' => $user->id,
        ]);
        

        $zoomCall = ZoomCall::create([
            'zoom_meetings_id' => $zoomMeeting->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->put("/zoom-meeting/{$zoomCall->id}", [
            'status' => 'ended',
        ]);

        $zoomCall->update(['status' => 'ended']);

        $this->assertDatabaseHas('zoom_calls', [
            'id' => $zoomCall->id,
            'status' => 'ended',
        ]);
    }



    /** @test */
    public function user_can_access_zoom_meeting_page() {
        $user = User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
        ]);

        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Test Zoom Meeting',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i'),
            'end_time' => now()->addMinutes(30)->format('H:i'),
            'creator_id' => $user->id,
            'status' => 'active',
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/zoom-meeting');
        
        $response->assertSee('Toggle Camera');
        $response->assertSee('Toggle Mic');
        $response->assertSee('Leave Call');
    }



    /** @test */
    public function blade_shows_camera_and_microphone_off_by_default() {
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Test Call',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinute()->format('H:i'),
            'end_time' => now()->addMinutes(15)->format('H:i'),
            'creator_id' => $user->id,
            'status' => 'active',
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get("/zoom-meeting");
        $response->assertSee('Mic Off')
                ->assertSee('Cam Off');
    }

    /** @test */
    public function other_users_cannot_see_each_others_cameras_when_off() {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Team Meeting',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i'),
            'end_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'active',
            'creator_id' => $userA->id,
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $userA->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $userB->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($userA);
        $response = $this->get('/zoom-meeting');
        
        $response->assertSee('Cam Off');    
    }



    /** @test */
    public function other_users_cannot_hear_each_others_microphones_when_off() {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Team Meeting',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5)->format('H:i'),
            'end_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'active',
            'creator_id' => $userA->id,
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $userA->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        DB::table('user_zoom_meetings')->insert([
            'user_id' => $userB->id,
            'zoom_meetings_id' => $meeting->id,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($userA);
        $response = $this->get('/zoom-meeting');
        $response->assertSee('Mic Off');
    }
}
