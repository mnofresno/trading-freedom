<?php

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

class AssetValue extends Model
{
    protected $table = "assets_values";
    
    public function summary()
    {
        return $this->belongsTo(AssetValueSummary::class, 'asset_value_summary_id');
    }
    
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
