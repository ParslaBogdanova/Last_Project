<?php

/**
 * A user receives a notification, when they are add it to the zoom meeting.
 * Along with zoom meetings title, the creator by which user.
 * 
 * @property int $id
 * @property string $message
 * @property bool $is_read
 * @property int $zoom_meetings_id
 * @property int $user_id
 * 
 * @property-read App\Models\ZoomMeeting $zoomMeeting
 * @property-read App\Models\User $user
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zoom_meetings_id',
        'message',
        'is_read',
    ];

/**
 * Single notification belongs to a zoom meeting, means each zoom meeting have their own notification.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function zoomMeeting() {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meetings_id');
    }

/**
 * The notification belongs to a user who is being invited.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
