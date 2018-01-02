<?php

namespace App\Services\Repositories;

use App\Models\Asset as Asset;
use App\Services\BittrexCrawlerService as BittrexCrawlerService;

class AssetsRepository
{
    private $asset;
    private $crawlerService;
    
    public function __construct(Asset $asset,
                                BittrexCrawlerService $crawlerService)
    {
        $this->asset          = $asset;
        $this->crawlerService = $crawlerService;
    }
    
    public function UpdateAssets()
    {
        $assetsMarkets = $this->crawlerService->GetAllAssetsVersusBtc();
        
        foreach($assetsMarkets as $assetMarket)
        {
            $currentAsset = $this->findBySymbol($assetMarket['code']);
            if($currentAsset == null)
            {
                $newAsset = $this->asset->create($assetMarket);
                $newAsset->save();                
            }
            else if($currentAsset->description == '')   
            {
                $currentAsset->update($assetMarket);
                $currentAsset->save();
            }
        }
    }
    
    public function findBySymbol($symbol)
    {
        return $this->asset->where('code', '=', $symbol)->first();
    }
    
    public function findOrCreateBySymbol($symbol)
    {
        $asset = $this->findBySymbol($symbol);
        
        if($asset == null)
        {
            $asset = $this->asset->create([ 'code' => $symbol, 'description' => '' ]);
            $asset->save();
        }
        
        return $asset;
    }
}
