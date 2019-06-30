<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Hash;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'fcm_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'fcm_token'
    ];

    public function apiKeys()
    {
		return $this->hasMany(UserKey::class, 'user_id');
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
    }
}
