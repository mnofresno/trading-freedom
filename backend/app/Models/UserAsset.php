<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAsset extends Model
{
    protected $table = "users_assets";
    
    protected $guarded = ['id'];
    
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function getLastAssetValueAttribute()
    {
        return $this->asset->assetsValues->first();
    }
    
    public function getSaldoUsdtAttribute()
    {
        return $this->last_asset_value->VALUE_USDT * $this->SALDO;
    }
    
    public function getSaldoMbtcAttribute()
    {
        return $this->last_asset_value->VALUE_MBTC * $this->SALDO;
    }
}
