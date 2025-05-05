<?php
/**
 * Model Calendar helps users manage their personal schedules, public zoom meetings and private blocked days.
 * Each user can create their own unique work plan. Independently or in grouping with friends or coworkers.
 * 
 * @property int $id
 * @property int $month
 * @property int $year
 * @property int $user_id
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\Day[] $days
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\ZoomMeeting[] $zoomMeetings
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calendar whereUserId($value)
 * 
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'user_id',
    ];

/**
 * Calendar has many days;
 *  a year typically includes 365 days, and 366 during a leap year.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */  
    public function days() {
        return $this->hasMany(Day::class);
    }

/**
 * Each calendar belongs to a specific user.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */  
    public function user() {
        return $this->belongsTo(User::class);
    }

/**
 * Zoom meetings are shared with users who are invited.
 * Allows a model to access multiple related records from a distant model through an intermediate model.
 * The relationship flows from Calendar → Day → ZoomMeeting, and also connects indirectly to users and blocked days.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
 */  
    public function zoomMeetings() {
        return $this->hasManyThrough(ZoomMeeting::class, Day::class);
    }
}
