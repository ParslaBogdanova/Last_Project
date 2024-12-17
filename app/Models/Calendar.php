<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'user_id',
    ];

    public function days(){
        return $this->hasMany(Day::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
