<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calender extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
    ];

    public function days(){
        return $this->hasMany(Day::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
