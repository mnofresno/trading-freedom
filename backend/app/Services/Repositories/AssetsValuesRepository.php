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
    
    public function UpdateAssetsValues($user_id = null)
    {
        $balances = $this->crawlerService->GetAllBalances($user_id);
        $summary = $this->assetValueSummary->create(collect($balances)->except('assets')->toArray());
        $summary->user_id = $user_id; 
        $summary->save();
        
        foreach($balances['assets'] as $currentValue)
        {
            $currentValue['asset_id'] = $this->assetsRepository->findOrCreateBySymbol($currentValue['MONEDA'])->id;
            $value = $this->assetValue->create($currentValue);
            $value->asset_value_summary_id = $summary->id;
            $value->save();
        }
    }
    
    public function GetBalances()
    {
        
    }
}
