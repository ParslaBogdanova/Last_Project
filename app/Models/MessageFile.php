<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_title',
    ];

    public function messages() {
        return $this->belongsTo(Message::class);
    }
}
