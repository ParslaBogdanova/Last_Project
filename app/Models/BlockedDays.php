<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockedDays extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'date',
        'user_id',
        'reason',
        'status',
    ];

    public function days() {
        return $this->belongsTo(Day::class, 'date', 'date');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
