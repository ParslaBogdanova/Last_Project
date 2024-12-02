<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tool_name',
        'email',
        'password',
        'display_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
