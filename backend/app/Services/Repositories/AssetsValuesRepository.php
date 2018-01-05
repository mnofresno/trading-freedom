<?php

namespace App\Services\Repositories;

use App\Models\AssetValue as AssetValue;
use App\Models\AssetValueSummary as AssetValueSummary;
use App\Services\BittrexCrawlerService as BittrexCrawlerService;

class AssetsValuesRepository
{
    private $assetValue;
    private $crawlerService;
    private $assetValueSummary;
    
    public function __construct(AssetValueSummary     $assetValueSummary,
                                AssetValue            $assetValue,
                                BittrexCrawlerService $crawlerService,
                                AssetsRepository      $assetsRepository)
    {
        $this->assetValue        = $assetValue;
        $this->crawlerService    = $crawlerService;
        $this->assetValueSummary = $assetValueSummary;
        $this->assetsRepository  = $assetsRepository;
    }
    
    public function UpdateAssetsValues()
    {
        $markets = $this->crawlerService->GetAllAssetsVersusBtcWithMarketData();
        
        foreach($markets as $currentMarket)
        {
            $asset_id = $this->assetsRepository->findOrCreateBySymbol($currentMarket['code'])->id;
            unset($currentMarket['code']);
            $currentMarket['asset_id'] = $asset_id;
            
            $value = $this->assetValue->create($currentMarket);
        }
    }
    
    public function GetLastAssetValues($asset_id)
    {
        return $this->assetValue->where('asset_id', '=', $asset_id)->orderBy('updated_at', 'desc')->limit(2)->get()->toArray();
    }
}
