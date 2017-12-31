<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserKey extends Authenticatable
{
    protected $table = "users_keys";
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function exchangeProvider()
    {
        return $this->belongsTo(ExchangeProvider::class, 'exchange_provider_id');
    }
}
