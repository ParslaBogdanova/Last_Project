<?php

/**
 * A user receives a reminder on that day that zoom meeting is planed, when they are add it to the zoom meeting.
 * Along with zoom meetings title, the creator by which user and the countdown till the zoom meeting starts.
 * 
 * @property int $id
 * @property int $user_id
 * @property int $zoom_meetings_id
 * @property bool $seen
 * 
 * @property-read App\Models\ZoomMeeting $zoomMeeting
 * @property-read App\Models\User $user
 */

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


/**
 * Single reminder belongs to a zoom meeting, means each zoom meeting have their own reminder as well.
 * Only difference that even creator gets a reminder about zoom meeting.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function zoomMeeting() {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meetings_id');
    }

/**
 * Also all invited users are getting their own reminders till the zoom meeting starts.
 * Only difference that even creator gets a reminder about zoom meeting.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
