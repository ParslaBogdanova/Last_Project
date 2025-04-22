<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZoomCall extends Model
{
    use HasFactory;

    protected $fillable=[
        'zoom_meetings_id',
        'user_id',
        'status',
    ];

    public function zoomMeeting() {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meetings_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function startCall() {
        $this->status = 'active';
        $this->save();
    }

    public function endCall() {
        $this->status = 'ended';
        $this->save();
    }

    public function getStartTime() {
        return $this->zoomMeeting->start_time; //gets the attribute from ZoomMeeting's start_time
    }
    public function getEndTime() {
        return $this->zoomMeeting->end_time; // same as end_time from ZoomMeeting table
    }
}
