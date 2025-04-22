<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ZoomMeeting;
use App\Models\Day;
use App\Models\Calendar;
use App\Models\BlockedDays;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoomMeetingTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function user_as_a_creator_can_create_zoom_meeting_with_invited_users() {
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
            'title_zoom' => 'Weekly checkup',
            'topic_zoom' => 'Team Tech',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'invited_users' => $invited->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('zoom_meetings', ['title_zoom' => 'Weekly checkup']);
    }



    /** @test */
    public function creator_can_edit_their_zoom_meeting() {
        $creator = User::factory()->create();
        
        $calendar = Calendar::create([
            'user_id' => $creator->id,
            'month' => 4,
            'year' => 2025,
        ]);
        
        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);
        
        $zoom_meeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Weekly checkup',
            'topic_zoom' => 'Team Tech',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'date' => '2025-04-16',
        ]);
        
        $this->actingAs($creator);
        
        $response = $this->put("/calendar/4/2025/2025-04-16/zoom_meetings", [
            'zoom_meetings_id' => $zoom_meeting->id,
            'title_zoom' => 'Updated Title',
            'topic_zoom' => 'Updated Topic',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'invited_users' => [],
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('zoom_meetings', ['title_zoom' => 'Updated Title']);
    }

    
    
    /** @test */
    public function creator_can_delete_their_own_meeting() {
        $creator = User::factory()->create();
        $zoom_meeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Different title',
            'topic_zoom' => 'Different topic',
            'start_time' => '13:00',
            'end_time' => '14:00',
            'date' => '2025-04-16',
        ]);
        
        $this->actingAs($creator);
        $response = $this->delete("/calendar/4/2025/2025-04-16/zoom_meetings/{$zoom_meeting->id}");
        $this->assertDatabaseMissing('zoom_meetings', ['id' => $zoom_meeting->id]);
    }



    /** @test */
    public function invited_user_cannot_edit_creators_zoom_meeting() {
        $creator = User::factory()->create();
        $invited = User::factory()->create();

        $calendar = Calendar::create([
            'user_id'=>$creator->id,
            'month' => 4,
            'year'=> 2025,
        ]);

        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        $zoomMeeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Weekly checkup',
            'topic_zoom' => 'Team Tech',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'date' => '2025-04-16',
        ]);

        $zoomMeeting->invitedUsers()->attach($invited->id, ['date' => '2025-04-16']);

        $this->actingAs($invited);

        $response = $this->put("/calendar/4/2025/2025-04-16/zoom_meetings", [
            'zoom_meetings_id' => $zoomMeeting->id,
            'title_zoom' => 'Should not update',
            'topic_zoom' => 'Trying to update',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'invited_users' => [],
        ]);

        $response->assertRedirect();
    }

    
    
    /** @test */
    public function user_cannot_be_invited_if_already_in_conflicting_meeting() {
        $creator = User::factory()->create();
        $user = User::factory()->create();

        $calendar = Calendar::create([
            'user_id'=>$creator->id,
             'month' => 4,
            'year'=> 2025,
        ]);

        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        $existingMeeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Existing Meeting',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'date' => '2025-04-16',
        ]);

        $existingMeeting->invitedUsers()->attach($user->id, ['date' => '2025-04-16']);

        $this->actingAs($creator);

        $response = $this->post("/calendar/4/2025/2025-04-16/zoom_meetings", [
            'title_zoom' => 'New Overlapping Meeting',
            'topic_zoom' => 'Overlap test',
            'start_time' => '09:30',
            'end_time' => '10:30',
            'invited_users' => [$user->id],
        ]);

        $response->assertRedirect();
    }

    
    
    /** @test */
    public function unavailable_users_are_unable_to_join_due_to_blocked_day() {
        $creator = User::factory()->create();
        $user = User::factory()->create();

        $calendar = Calendar::create([
            'user_id'=>$creator->id,
            'month' => 4,
            'year'=> 2025,
        ]);

        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        BlockedDays::create([
            'user_id' => $user->id,
            'calendar_id' => $calendar->id,
            'date' => '2025-04-16',
            'reason' => 'On vacation',
        ]);

        $this->actingAs($creator);

        $response = $this->post("/calendar/4/2025/2025-04-16/zoom_meetings", [
            'title_zoom' => 'Team Call',
            'topic_zoom' => 'Blocked day test',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'invited_users' => [$user->id],
        ]);

        $response->assertRedirect();
    }
}
