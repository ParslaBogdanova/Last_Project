<?php

/**
 * Model Day, holds information/data about zoom meetings, schedules and blocked days the user created at specific date.
 * When user clicks on it, it shows everything and ability to create own plans.
 * 
 * @property int $id
 * @property int $user_id
 * @property int $calendar_id
 * @property \Carbon\Carbon $date
 * 
 * @property-read \App\Models\Calendar $calendar
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\ZoomMeeting[] $zoomMeetings
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\BlockedDays[] $blockedDays
 * @property-read \App\Models\User $user
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'calendar_id',
    ];

/**
 * A day belongs to a calendar, where the calendar represents a specific month and year user selects.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */  
    public function calendar() {
        return $this->belongsTo(Calendar::class);
    }

/**
 * A single day, a user can have many schedules, which don't include into a logic if the user is available or not.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */  
    public function schedules() {
        return $this->hasMany(Schedule::class, 'date', 'date');
    }

/**
 * Including a user being invited or the creator of the meeting, it still saves in that specific date based of column and attribute.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */  
    public function zoomMeetings() {
        return $this->hasMany(ZoomMeeting::class, 'date', 'date');
    }

/**
 * A blocked day can be created once per date. Yes, each day can have a different reason of blocking the day.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */  
    public function blockedDays() {
        return $this->hasMany(BlockedDays::class, 'date', 'date');
        //'date' twice->telling Laravel how to connect the two models through their foreign key.
    }

/**
 * Each day is associated with a specific user.
 * This relationship links the day to the user that creates their private schedules, but shared with zoom meetings.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */  
    public function user() {
        return $this->belongsTo(User::class);
    }
}
