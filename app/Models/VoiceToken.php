<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoiceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'is_revoked',
        'expires_at',
        'user_id',
        'room_id'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revoke()
    {
        $this->is_revoked = true;
        $this->save();
    }

    public function isValid()
    {
        return !$this->is_revoked && $this->expires_at->isFuture();
    }
}
