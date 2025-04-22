<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Calendar;
use App\Models\BlockedDays;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedDayTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function a_user_can_block_a_specific_date() {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $month = 4;
        $year = 2025;
        $date = '2025-04-20';

        $calendar = Calendar::create([
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
        ]);

        $day = $calendar->days()->create([
            'date' => $date,
        ]);

        $response = $this->post(route('calendar.blockDay', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]), [
            'reason' => 'Sick/have to pick up the kids/emergency etc.',
        ]);

        $response->assertRedirect(route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]));

        $this->assertDatabaseHas('blocked_days', [
            'user_id' => $user->id,
            'date' => $date,
            'reason' => 'Sick/have to pick up the kids/emergency etc.',
            'status' => true,
            'calendar_id' => $calendar->id,
        ]);
    }

    

    /** @test */
    public function a_user_can_unblock_the_same_date()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $month = 4;
        $year = 2025;
        $date = '2025-04-20';

        $calendar = Calendar::create([
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
        ]);

        $day = $calendar->days()->create([
            'date' => $date,
        ]);

        $blocked = BlockedDays::create([
            'user_id' => $user->id,
            'date' => $date,
            'reason' => 'Sick/have to pick up the kids/emergency etc.',
            'status' => true,
            'calendar_id' => $calendar->id,
        ]);

        $response = $this->delete(route('calendar.unblock', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]));

        $this->assertDatabaseMissing('blocked_days', [
            'id' => $blocked->id,
        ]);
    }
}
