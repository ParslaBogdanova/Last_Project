<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


/**
 * Each user can only have one calendar.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\hasOne
 */ 
    public function calenders() {
        return $this-hasOne(Calenders::class);
    }

/**
 * Even user can have many days filled up, it doesn't mean it belongs to them.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function days() {
        return $this->hasMany(Day::class);
    }

/**
 * Allows a model to access multiple related records from a distant model through an intermediate model.
 * The relationship flows from Calendar → Day → Schedule.
 * Easier to show which date and what calendar the schedule needs to be showed.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
 */ 
    public function schedules() {
        return $this->hasManyThrough(Schedule::class, Day::class);
    }

/**
 * Allows a model to access multiple related records from a distant model through an intermediate model.
 * The relationship flows from Calendar → Day → blockedDay.
 * Easier to show which date and what calendar the blocked day needs to be showed.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
 */ 
    public function blockedDays() {
        return $this->hasManyThrough(BlockedDays::class, Day::class);
    }

/**
 * A user can send to a single user many messages.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function sentMessages() {
        return $this->hasMany(Message::class, 'sender_id');
    }

/**
 * Same as sender can send many messages, they can receive many messages.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function receivedMessages() {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    //----------------------------

/**
 * User itself is the creator and is allowed to create many zoom meetings.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function createdZoomMeetings() {
        return $this->hasMany(ZoomMeeting::class, 'creator_id');
    }

/**
 * The relationship between a user and Zoom meetings.
 * 
 * Defines a many-to-many relationship, where a user can be invited to many Zoom meetings,
 * and a Zoom meeting can have many users. The `user_zoom_meetings` pivot table is used
 * to store the relationship, along with additional data like the `date` and `status` of the invitation.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
 */ 
    public function zoomMeetings() {
        return $this->belongsToMany(ZoomMeeting::class, 'user_zoom_meetings', 'user_id', 'zoom_meetings_id')
                    ->withPivot('date', 'status');
    }

/**
 * Invited users get notifications of each zoom meetings they are invited, not past zoom meetings if it already end it or the day has passed.
 * It only shows of the future zoom meetings.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function notifications() {
        return $this->hasMany(Notification::class);
    }

/**
 * Every user gets a reminder, only shows on that day of the zoom meeting is planed.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */ 
    public function reminders() {
        return $this->hasMany(ReminderZoomMeeting::class);
    }
}
