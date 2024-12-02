<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
    ];

    public function calenders(){
        return $this->belongsTo(Calenders::class);
    }

    public function schedules(){
        return $this->hasMany(Schedule::class); 
    }
}
