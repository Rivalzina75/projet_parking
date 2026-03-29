<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'role',
        'is_validated',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_validated' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function waitingListEntry()
    {
        return $this->hasOne(WaitingListEntry::class);
    }

    public function activeReservation()
    {
        return $this->hasOne(Reservation::class)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now());
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->lastname);
    }
}
