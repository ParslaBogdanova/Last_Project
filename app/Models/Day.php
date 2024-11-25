<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'calender_id',
        'date',
        'day_name',
        'week',
    ];

    public function calenders(){
        return $this->belongTo(Calenders::class);
    }

    public function schedules(){
        return $this->hasMany(Scedule::class); //Zinu.. drukas kļūda T-T
    }
}
