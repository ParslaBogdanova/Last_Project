<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable =[
        'title',
        'topic',
        'invited_users',
        'start_time',
        'end_time',
        'user_id',
        'day_id',
    ];
        
    public function invitedUsers(){
        return $this->belongsToMany(User::class, 'user_zoom_meeting', 'zoom_meeting_id', 'user_id');
    }

    public function day(){
        return $this->belongsTo(Day::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
