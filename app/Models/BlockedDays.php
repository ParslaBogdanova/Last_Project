<?php
/**
 * Model BlockedDays, represents a blocked day in a user's calendar.
 * Once they blocked it, the specific chosen date is blocked with an reason.
 * 
 * @property int $id
 * @property int $calendar_id
 * @property \Carbon\Carbon $date
 * @property int $user_id
 * @property string $reason
 * @property bool $status
 * 
 * @property-read \App\Models\Day $days
 * @property-read \App\Models\User $user
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedDays whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedDays whereUserId($value)
 * 
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockedDays extends Model {
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'date',
        'user_id',
        'reason',
        'status',
    ];

/**
 * The blocked day belongs to a day record/entry with the same date.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function days() {
        return $this->belongsTo(Day::class, 'date', 'date');
    }


/**
 * The blocked day record belongs to the user who created it.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
