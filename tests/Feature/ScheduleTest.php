<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function creating_schedule_on_a_specific_date() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $month = 4;
        $year = 2025;
        $date = '2025-04-15';

        $calendar = Calendar::create([
            'month' => $month,
            'year' => $year,
            'user_id' => $user->id,
        ]);

        $calendar->days()->create([
            'date' => $date
        ]);

        $response = $this->post(route('schedules.store', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]), [
            'title' => 'Dentists/Doctors/etc Appointment',
            'description' => 'At 10am or later or early',
            'color' => '#a83260',
        ]);

        $response->assertRedirect(route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]));

        $this->assertDatabaseHas('schedules', [
            'title' => 'Dentists/Doctors/etc Appointment',
            'description' => 'At 10am or later or early',
            'color' => '#a83260',
            'date' => $date,
            'user_id' => $user->id,
        ]);
    }



    /** @test */
    public function updates_a_schedule_by_clicking_on_chosen_one() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $month = 4;
        $year = 2025;
        $date = '2025-04-15';

        $calendar = Calendar::create([
            'month' => $month,
            'year' => $year,
            'user_id' => $user->id,
        ]);

        $calendar->days()->create([
            'date' => $date
        ]);

        $schedule = Schedule::create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'color' => '#123456',
            'date' => $date,
            'user_id' => $user->id,
        ]);

        $response = $this->put(route('schedules.update', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]), [
            'schedule_id' => $schedule->id,
            'title' => 'New Title',
            'description' => 'Updated description',
            'color' => '#654321',
        ]);

        $response->assertRedirect(route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]));

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'title' => 'New Title',
            'description' => 'Updated description',
            'color' => '#654321',
        ]);
    }


    
    /** @test */
    public function it_deletes_a_schedule() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $month = 4;
        $year = 2025;
        $date = '2025-04-15';

        $calendar = Calendar::create([
            'month' => $month,
            'year' => $year,
            'user_id' => $user->id,
        ]);

        $calendar->days()->create(['date' => $date]);

        $schedule = Schedule::create([
            'title' => 'Delete Me',
            'description' => 'To be removed',
            'color' => '#00ff00',
            'date' => $date,
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('schedules.destroy', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
            'schedule_id' => $schedule->id,
        ]));

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }
}
