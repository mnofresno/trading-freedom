<?php

namespace App\Services\Repositories;

use App\Models\UserAsset as UserAsset;
use App\Services\BittrexCrawlerService as BittrexCrawlerService;

class UsersAssetsRepository
{
    private $userAsset;
    private $crawlerService;
    private $assetsRepository;
    
    public function __construct(UserAsset             $userAsset,
                                BittrexCrawlerService $crawlerService,
                                AssetsRepository      $assetsRepository)
    {
        $this->userAsset        = $userAsset;
        $this->crawlerService   = $crawlerService;
        $this->assetsRepository = $assetsRepository;
    }
    
    public function UpdateUserAssetsValues($user_id)
    {
        $balances = $this->crawlerService->GetBalances($user_id);
        
        foreach($balances as $balance)
        {
            $asset_id = $this->assetsRepository->findOrCreateBySymbol($balance->Currency)->id;
            
            $SALDO    = $balance->Balance;
            
            $this->userAsset->create(compact('user_id', 'asset_id', 'SALDO'));
        }
    }
    
    public function GetBalances()
    {
        
    }
}
