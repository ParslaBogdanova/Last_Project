<?php

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

    public function calendar() {
        return $this->belongsTo(Calendar::class);
    }

    public function schedules() {
        return $this->hasMany(Schedule::class, 'date', 'date');
    }

    public function zoomMeetings() {
        return $this->hasMany(ZoomMeeting::class, 'date', 'date');
    }

    public function blockedDays() {
        return $this->hasMany(BlockedDays::class, 'date', 'date'); //'date' twice->telling Laravel how to connect the two models through their foreign key.
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
