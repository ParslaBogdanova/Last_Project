<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'calendar_id',
    ];

    public function calendar(){
        return $this->belongsTo(Calendar::class);
    }

    public function schedules(){
        return $this->hasMany(Schedule::class, 'day_id');
    }
    public function zoomMeetings(){
        return $this->hasMany(ZoomMeeting::class, 'day_id');
    }

    public function blockedDays(){
        return $this->hasMany(BlockedDays::class);
    }
}
