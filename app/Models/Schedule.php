<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'title',
        'day_id',
        'color',
    ];

    public function day(){
        return $this->belongsTo(Day::class);
    }

}
