<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = "assets";
    
    protected $guarded = ['id'];
    
    public function assetValues()
    {
		return $this->hasMany(AssetValue::class, 'asset_id');
    }
}
