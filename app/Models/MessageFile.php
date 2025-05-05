<?php

/**
 * Model MessageFile
 * 
 * Represents a file (document or image) attached to a message.
 * A file must be sent together with a content.
 * 
 * @property int $id
 * @property string $file_path
 * @property string $file_title
 * 
 * @property-read \App\Models\Message $messages
 */

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


/**
 * Each file, may or only one, belongs to a message that is being attached too.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function messages() {
        return $this->belongsTo(Message::class);
    }
}
