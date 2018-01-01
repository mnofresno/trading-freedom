<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserKey extends Model
{
    protected $table = "users_keys";
    
    protected $guarded = ['id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function exchangeProvider()
    {
        return $this->belongsTo(ExchangeProvider::class, 'exchange_provider_id');
    }
}
