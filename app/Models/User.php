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

    public function tools(){
        return $this->hasMany(UserTool::class);
    }

    public function calenders(){
        return $this-hasOne(Calenders::class);
    }

    public function days(){
        return $this->hasMany(Day::class);
    }

    public function schedules(){
        return $this->hasManyThrough(Schedule::class, Day::class);
    }

    public function blockedDays(){
        return $this->hasManyThrough(BlockedDays::class, Day::class);
    }

    //----------------------------

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    
    public function contacts()
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_id');
    }

    //----------------------------

    public function createdZoomMeetings(){
        return $this->hasMany(ZoomMeeting::class, 'creator_id');
    }
    public function zoomMeetings(){
        return $this->belongsToMany(ZoomMeeting::class, 'user_zoom_meetings', 'user_id', 'zoom_meetings_id')
                    ->withPivot('date', 'status');
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
    public function reminders(){
        return $this->hasMany(ReminderZoomMeeting::class);
    }
    
}
