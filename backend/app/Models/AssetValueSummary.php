<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetValueSummary extends Model
{
    protected $table = "assets_values_summaries";
    
    protected $guarded = ['id'];

    public function values()
    {
		return $this->hasMany(AssetValue::class, 'user_id');
    }
}
