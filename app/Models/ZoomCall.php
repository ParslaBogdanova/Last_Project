<?php
/**
 * Model ZoomCall represents a call that happens during a zoom meeting.
 * Each call is associated with a user and a specific zoom meeting that is
 * created at that time and date.
 * 
 * @property int $id
 * @property int $zoom_meetings_id
 * @property bool $status
 * @property int $user_id
 * 
 * @property-read \App\Models\User $user
 * @property-read \App\Models\ZoomMeeting $zoomMeeting
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomCall startCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomCall endCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomCall LeaveCall($value)
 * 
 */

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


/**
 * Each Zoom call is associated with a specific Zoom meeting. A Zoom call belongs 
 * to one Zoom meeting.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function zoomMeeting() {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meetings_id');
    }

/**
 * Like zoom meetings to a creator belongs to, same with zoom calls, but with every invited users is part of it.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function user() {
        return $this->belongsTo(User::class);
    }

/**
 * Starts the Zoom call by setting the status to 'active' and saving it.
 * It even shows the 'active' zoom meeting has been created.
 * 
 * @return void
 */   
    public function startCall() {
        $this->status = 'active';
        $this->save();
    }

/**
 * Ends the Zoom call by setting the status to 'ended' and saving it.
 * It changes when zoom call has end it.
 * 
 * @return void
 */
    public function endCall() {
        $this->status = 'ended';
        $this->save();
    }

/**
 * The call will only start based of zoom meetings attribute 'start_time'
 * 
 * @return \Carbon\Carbon  // Returns the start time as a Carbon instance 'date'
 */
    public function getStartTime() {
        return $this->zoomMeeting->start_time; //gets the attribute from ZoomMeeting's start_time
    }

/**
 * Zoom Call ends based of zoom meetings time 'end_time'
 * 
 * @return \Carbon\Carbon
 */
    public function getEndTime() {
        return $this->zoomMeeting->end_time; // same as end_time from ZoomMeeting table
    }
}
