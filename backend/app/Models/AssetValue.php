<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetValue extends Model
{
    protected $table = "assets_values";
    
    protected $guarded = ['id'];
    
    public function summary()
    {
        return $this->belongsTo(AssetValueSummary::class, 'asset_value_summary_id');
    }
    
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
