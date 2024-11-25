<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_id',
        'title',
        'start_time',
        'am_pm',
        'meeting_link'
    ];

    public function day(){
        return $this->belongsTo(Day::class);
    }

}
