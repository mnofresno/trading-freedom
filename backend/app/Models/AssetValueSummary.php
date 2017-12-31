<?php

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

class AssetValueSummary extends Model
{
    protected $table = "assets_values_summaries";
    
    public function values()
    {
		return $this->hasMany(AssetValue::class, 'user_id');
    }
}
