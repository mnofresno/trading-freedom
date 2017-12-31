<?php

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

class ExchangeProvider extends Model
{
    protected $table = "exchange_providers";
    
    public function apiKeys()
    {
		return $this->hasMany(UserKey::class, 'user_id');
    }
}
