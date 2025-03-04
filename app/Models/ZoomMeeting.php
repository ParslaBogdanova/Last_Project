<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_zoom',
        'topic_zoom',
        'invited_users',
        'start_time',
        'end_time',
        'user_id',
        'day_id',
    ];

    public function day(){
        return $this->belongsTo(Day::class);
    }
    public function users() {
        return $this->belongsToMany(User::class, 'user_zoom_meetings', 'zoom_meetings_id', 'user_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
