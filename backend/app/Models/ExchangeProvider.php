<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeProvider extends Model
{
    protected $table = "exchange_providers";
    
    protected $guarded = ['id'];
    
    public function apiKeys()
    {
		return $this->hasMany(UserKey::class, 'user_id');
    }
}
