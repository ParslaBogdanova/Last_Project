<?php
/**
 * Model ZoomMeeting
 * 
 * Represents a Zoom meeting scheduled by a user(creator). A meeting can have multiple invited users
 * and notifications are sent out upon creation. The meeting also tracks the start and end time,
 * as well as the date.
 * 
 * @property int $id
 * @property string $title_zoom
 * @property string $topic_zoom
 * @property int $invited_users
 * @property time $start_time
 * @property time $end_time
 * @property int $creator_id
 * @property \Carbon\Carbon $date
 * 
 * @property-read \App\Models\Day $day
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\User[] $invitedUsers
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\Notification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\ZoomCall[] $zoomCall
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whichInvitedUsersId($value)
 */
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

/**
 * The day associated with the Zoom meeting. Each Zoom meeting is created at the specific day.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function day() {
        return $this->belongsTo(Day::class);
    }

/**
 * The users invited to the Zoom meeting.
 * This defines a many-to-many relationship, where a Zoom meeting can have many invited users,
 * and each user can be invited to many meetings. The `user_zoom_meetings` pivot table stores
 * additional data, such as the invitation status and date.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
 */
    public function invitedUsers() {
        return $this->belongsToMany(User::class, 'user_zoom_meetings', 'zoom_meetings_id', 'user_id')
                    ->withPivot('date', 'status');
    }

/**
 * The user who created the Zoom meeting.
 * This is the host of the meeting.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

/**
 * Notifications related to a Zoom meeting. Once a meeting is created, 
 * notifications are sent to all invited users.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function notifications() {
        return $this->hasMany(Notification::class, 'zoom_meetings_id');
    }

/**
 * Zoom calls related to this meeting. A Zoom meeting can have only one Zoom call, but many invited users 
 * are being invited to that call.
 * The call starts based on the 'start_time' attribute of the Zoom meeting.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function zoomCall() {
        return $this->hasMany(ZoomCall::class);
    }
}
