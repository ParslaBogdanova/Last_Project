<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\ZoomMeeting;
use App\Models\Schedule;
use App\Models\BlockedDay;
use App\Models\Day;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function a_user_can_view_a_previous_or_next_month_year(){
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('calendar.index', [
            'month'=>5,
            'year'=>2025,
        ]));

        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'month' => 5,
            'year' => 2025,
        ]);
    }



    /** @test */
    public function it_can_navigate_from_january_to_december(){
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('calendar.index', [
            'month'=>1,
            'year'=>2025,
        ]));
        $this->get(route('calendar.index', [
            'month'=>12,
            'year'=>2024,
        ]));

        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'month' => 1,
            'year' => 2025,
        ]);
    
        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'month' => 12,
            'year' => 2024,
        ]);
    }



    /** @test */
    public function a_user_can_click_on_specific_day_in_the_calendar(){
        $user = User::factory()->create();

        $this->actingAs($user);

        $calendar = Calendar::create([
            'user_id' => $user->id,
            'month'=> 4,
            'year'=>2025,
        ]);

        $date = '2025-04-15';

        $day= Day::create([
            'calendar_id'=>$calendar->id,
            'date'=>$date,
        ]);

        $response =$this->get(route('calendar.show',[
            'month'=>4,
            'year'=>2025,
            'date'=> $date,
        ]));

        $response->assertViewIs('calendar.show');
        $response->assertViewHas('day', function ($viewDay) use ($day) {
            return $viewDay->id === $day->id;
        });
    }



    /** @test */
    public function a_user_can_see_their_own_schedules() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $calendar = Calendar::create([
            'user_id' => $user->id,
            'month'=> 4,
            'year'=>2025,
        ]);

        $date = '2025-04-15';

        $day= Day::create([
            'calendar_id'=>$calendar->id,
            'date'=>$date,
        ]);

        $day->schedules()->create([
            'user_id' => $user->id,
            'title' => 'My Task',
            'color' => '#ff0000',
        ]);

        $response = $this->get(route('calendar.index', [
            'month' => $calendar->month,
            'year' => $calendar->year,
        ]));
        $response->assertSee('My Task');
    }



    /** @test */
    public function a_user_can_see_their_own_blocked_days() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $calendar = Calendar::create([
            'user_id' => $user->id,
            'month'=> 4,
            'year'=>2025,
        ]);

        $date = '2025-04-15';

        $day= Day::create([
            'calendar_id'=>$calendar->id,
            'date'=>$date,
        ]);

        $day->blockedDays()->create([
            'user_id' => $user->id,
            'calendar_id' => $calendar->id,
            'date' => $day->date,
            'reason' => 'Busy',
        ]);

        $response = $this->get(route('calendar.index', [
            'month' => $calendar->month,
            'year' => $calendar->year,
        ]));

        $response->assertSee('blocked');
    }


/** @test */
    public function a_user_can_see_their_created_zoom_meetings() {
        $creator = User::factory()->create();

        $calendar = Calendar::create([
            'user_id' => $creator->id,
            'month'=> 4,
            'year'=>2025,
        ]);

        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        $this->actingAs($creator);

        $zoomMeeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'My Meeting',
            'topic_zoom' => 'Testing',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'date' => '2025-04-16',
        ]);
        $this->actingAs($creator);

        $response = $this->get(route('calendar.index', [
            'zoom_meetings_id' => $zoomMeeting->id,
            'month' => $calendar->month,
            'year' => $calendar->year,
        ]));

        $response->assertSee('My Meeting');
    }


    /** @test */
    public function an_invited_user_can_see_zoom_meetings_they_are_invited_to() {
        $creator = User::factory()->create();
        $invited = User::factory()->create();
        $this->actingAs($invited);

        $calendar = Calendar::create([
            'user_id' => $creator->id,
            'month'=> 4,
            'year'=>2025,
        ]);

        $day = $calendar->days()->create([
            'date' => '2025-04-16',
        ]);

        $zoomMeeting = ZoomMeeting::create([
            'creator_id' => $creator->id,
            'title_zoom' => 'Team Sync',
            'topic_zoom' => 'Invited topic',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'date' => '2025-04-16',
        ]);

        $zoomMeeting->invitedUsers()->attach($invited->id, ['date' => '2025-04-16']);

        $response = $this->get(route('calendar.index', [
            'zoom_meetings_id' => $zoomMeeting->id,
            'month' => $calendar->month,
            'year' => $calendar->year,
        ]));

        $response->assertSee('Team Sync');
    }
}
