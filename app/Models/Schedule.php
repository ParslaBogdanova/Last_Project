<?php

/**
 * Model Schedule
 * Own private planing schedule, it can be anything, even grocery shopping list.
 * Its not shared with any of the users.
 * 
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $color
 * @property int $user_id
 * @property \Carbon\Carbon $date
 * 
 * @property-read \App\Models\Day $day
 * @property-read \App\Models\User $user
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereUserId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'title',
        'color',
        'user_id',
        'date',
    ];


/**
 * Each schedule of own belongs to user, its not shared with other users.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */  
    public function user() {
        return $this->belongsTo(User::class);
    }

/**
 * Also the schedule belongs to a date that user filled it up/created at.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */  
    public function day() {
        return $this->belongsTo(Day::class, 'date', 'date');
    }
}
