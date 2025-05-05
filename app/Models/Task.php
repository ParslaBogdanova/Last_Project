<?php
/**
 * Model Tasks
 * Just own task list
 * 
 * @property int $id
 * @property string $description
 * @property int $user_id
 * @property bool $completed
 * 
 * @property-read \App\Models\User $user
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereUserId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'completed',
        'user_id',
    ];

/**
 * Each task belongs to a user.
 * Its not shared.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */ 
    public function user() {
        return $this->belongsTo(User::class);
    }
}
