<?php

/**
 * Model Message, represents a message sent from one user to another. Users can send and receive messages.
 * Only the sender can update and delete their own messages.
 * 
 * @property int $id
 * @property string $content
 * @property int $sender_id
 * @property int $receiver_id
 * 
 * @property-read \App\Models\User $sender
 * @property-read \App\Models\User $receiver
 * @property-read \Illuminate\Database\Eloquent\Collection | \App\Models\MessageFile[] $files
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message sendMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message sendFile($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'sender_id',
        'receiver_id',
    ];


/**
 * Each own message belongs to a user, the sender
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

/**
 * Each sender also receivers from different user a message that they can read.
 * Each receiver's message belongs to them, not senders.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }


/**
 * Get the files attached to message along with content.
 * Files may include documents or images stored in public/storage/messages.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function files() {
        return $this->hasMany(MessageFile::class);
    }
}
