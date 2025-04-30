<?php

namespace Tests\Unit;

use App\Models\BlockedDays;
use App\Models\User;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedDaysTest extends TestCase {
    use RefreshDatabase;

     /** @test */
     public function a_blocked_day_can_have_status_blocked() {
         $user = User::factory()->create();
         $calendar = Calendar::create([
            'user_id' => $user->id,
            'month' => 4,
            'year' => 2025,
        ]);

         $blockedDay = BlockedDays::create([
             'calendar_id' => $calendar->id,
             'date' => '2025-04-20',
             'user_id' => $user->id,
             'reason' => 'Sick',
             'status' => true,
         ]);
 
         $this->assertTrue($blockedDay->status);
     }
 


     /** @test */
     public function blocked_day_status_can_be_unblocked() {
         $user = User::factory()->create();
         $calendar = Calendar::create([
            'user_id' => $user->id,
            'month' => 4,
            'year' => 2025,
        ]);

         $blockedDay = BlockedDays::create([
             'calendar_id' => $calendar->id,
             'date' => '2025-04-20',
             'user_id' => $user->id,
             'reason' => 'Sick',
             'status' => true,
         ]);
 
         $blockedDay->update(['status' => false]);
 
         $this->assertFalse($blockedDay->status);
     }
}
