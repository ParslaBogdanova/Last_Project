<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\ZoomMeeting;
use App\Models\Day;
use App\Models\Calendar;
use App\Models\ReminderZoomMeeting;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReminderTest extends TestCase {
    use RefreshDatabase;

/** @test */
    public function users_receive_notifications_via_notification_controller() {
        $creator = User::factory()->create();
        $invited = User::factory()->create();

        $zoomMeeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Notification Test',
            'topic_zoom' => 'Testing direct call',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'date' => '2025-04-16',
        ]);

        $notificationController = new NotificationController();

        $notificationController->sendNotification(
            $zoomMeeting->id,
            [$invited->id],
            'You have been invited to a meeting.'
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $invited->id,
            'zoom_meetings_id' => $zoomMeeting->id,
            'message' => 'You have been invited to a meeting.',
        ]);
    }



    /** @test */
    public function reminders_are_scheduled_for_creator_and_invited_users() {
        $creator = User::factory()->create();
        $invited = User::factory()->count(2)->create();

        $calendar = Calendar::create([
            'user_id' => $creator->id,
            'month' => 4,
            'year' => 2025,
        ]);
        
        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        $this->actingAs($creator);
        

        $response = $this->post("/calendar/4/2025/2025-04-16/zoom_meetings", [
            'title_zoom' => 'Reminder Test Meeting',
            'topic_zoom' => 'Testing reminders',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'invited_users' => $invited->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reminder_zoom_meetings', [
            'user_id' => $creator->id,
            'seen' => false,
        ]);

        foreach ($invited as $user) {
            $this->assertDatabaseHas('reminder_zoom_meetings', [
                'user_id' => $user->id,
                'seen' => false,
            ]);
        }
    }



    /** @test */
    public function past_reminders_do_not_show_in_the_view() {
        $user = User::factory()->create();

        $zoomMeeting = ZoomMeeting::create([
            'creator_id' => $user->id,
            'title_zoom' => 'Old Meeting',
            'topic_zoom' => 'Expired Reminder',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'date' => now('Europe/Riga')->subDay()->toDateString(),
        ]);

        ReminderZoomMeeting::create([
            'user_id' => $user->id,
            'zoom_meetings_id' => $zoomMeeting->id,
            'seen' => false,
        ]);

        $this->actingAs($user);
        
        $response = $this->get(route('tasks.index'));
        $response->assertDontSee('Expired Meeting');
    }
}
