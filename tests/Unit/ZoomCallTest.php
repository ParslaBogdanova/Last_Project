<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\ZoomMeeting;
use App\Models\ZoomCall;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoomCallTest extends TestCase {
    use RefreshDatabase;

        /** @test */
    public function it_sets_status_to_active_when_call_starts() {
        $user = User::factory()->create();
        $meeting = ZoomMeeting::create([
            'title_zoom' => 'Team Sync',
            'topic_zoom' => 'Daily meeting',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'creator_id' => $user->id,
            'date' => '2025-04-30',
        ]);
        $zoomCall = ZoomCall::create([
            'user_id' => $user->id,
            'zoom_meetings_id' => $meeting->id,
            'status' => 'active',
        ]);
    
        $zoomCall->startCall();
        $this->assertEquals('active', $zoomCall->status);
    }



    /** @test */
    public function it_can_fetch_start_and_end_times_from_meeting() {
        $user = User::factory()->create();
        $meeting = ZoomMeeting::create([
            'user_id' => $user->id,
            'title_zoom' => 'Team Sync',
            'topic_zoom' => 'Daily meeting',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'creator_id' => $user->id,
            'date' => '2025-04-30',
        ]);

        $zoomCall = ZoomCall::create([
            'zoom_meetings_id' => $meeting->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('14:00', $zoomCall->getStartTime());
        $this->assertEquals('15:00', $zoomCall->getEndTime());
    }
}
