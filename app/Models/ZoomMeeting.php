<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $table = 'zoom_meetings';

    protected $fillable = [
        'title_zoom',
        'topic_zoom',
        'invited_users',
        'start_time',
        'end_time',
        'creator_id',
        'date',
    ];

    public function day(){
        return $this->belongsTo(Day::class);
    }
    public function invitedUsers()
    {
        return $this->belongsToMany(User::class, 'user_zoom_meetings', 'zoom_meetings_id', 'user_id')
        ->withPivot('date', 'status');
    }
    
    public function creator(){
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function notifications(){
        return $this->hasMany(Notification::class, 'zoom_meetings_id');
    }
}
