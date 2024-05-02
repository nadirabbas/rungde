<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password'
    ];

    protected $hidden = [
        'remember_token',
        'password'
    ];

    public function room()
    {
        return $this->hasOneThrough(Room::class, RoomUser::class, 'user_id', 'id', 'id', 'room_id');
    }

    public function roomUser()
    {
        return $this->hasOne(RoomUser::class);
    }

    public function voiceTokens()
    {
        return $this->hasMany(VoiceToken::class);
    }
}
