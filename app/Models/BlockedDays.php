<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockedDays extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'day_id',
        'user_id',
        'reason',
    ];

    public function calendar(){
        return $this->belongsTo(Calendar::class);
    }

    public function days(){
        return $this->belongsTo(Day::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
