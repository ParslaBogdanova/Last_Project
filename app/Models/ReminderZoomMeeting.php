<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReminderZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zoom_meetings_id',
        'seen',
    ];

    public function zoomMeeting() {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meetings_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
