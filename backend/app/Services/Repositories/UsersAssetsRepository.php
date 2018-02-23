<?php

namespace App\Services\Repositories;

use App\Models\UserAsset as UserAsset;
use App\Services\BittrexCrawlerService as BittrexCrawlerService;
use DB;

class UsersAssetsRepository
{
    private $userAsset;
    private $crawlerService;
    private $assetsRepository;
    private $assetsValuesRepository;
    
    public function __construct(UserAsset              $userAsset,
                                BittrexCrawlerService  $crawlerService,
                                AssetsRepository       $assetsRepository,
                                AssetsValuesRepository $assetsValuesRepository)
    {
        $this->userAsset              = $userAsset;
        $this->crawlerService         = $crawlerService;
        $this->assetsRepository       = $assetsRepository;
        $this->assetsValuesRepository = $assetsValuesRepository;
    }
    
    public function UpdateUserAssetsValues($user_id)
    {
        $userAssetsValues = $this->userAsset->where('user_id', '=', $user_id)->delete();
        $balances = $this->crawlerService->GetBalances($user_id);
        
        foreach($balances as $balance)
        {
            $asset_id = $this->assetsRepository->findOrCreateBySymbol($balance->Currency)->id;
            
            $SALDO    = $balance->Balance;
            
            $this->userAsset->create(compact('user_id', 'asset_id', 'SALDO'));
        }
        return date('Y-m-d H:i:s');
    }
    
    public function GetBalances($user_id)
    {
        $userAssetsValues = $this->userAsset->where('user_id', '=', $user_id)->get();
        
        $assets = [];
        
        $btcData = null;
        
        $saldoTotalMBTC = 0;
        $saldoTotalUSD  = 0;
        
        foreach($userAssetsValues as $userAssetValue)
        {
            $asset                     = $this->assetsRepository->find($userAssetValue->asset_id);
            $lastValues                = $this->assetsValuesRepository->GetLastAssetValues($userAssetValue->asset_id);
            $lastValue                 = $lastValues->first();
            $previousValue             = $lastValues->last();
            $increment = (($lastValue->VALOR_MBTC - $previousValue->VALOR_MBTC) / $previousValue->VALOR_MBTC) * 100;
            
            $saldoMBTC = $userAssetValue->SALDO * $lastValue->VALOR_MBTC;
            $saldoUSDT = $userAssetValue->SALDO * $lastValue->VALOR_USDT;
            
            $currentAsset = [   'MONEDA'     => $asset->code,
                                'SALDO'      => $userAssetValue->SALDO,
                                'SALDO_MBTC' => $saldoMBTC,
                                'SALDO_USDT' => $saldoUSDT,
                                'VALOR_MBTC' => $lastValue->VALOR_MBTC,
                                'VALOR_USDT' => $lastValue->VALOR_USDT,
                                'INCREMENTP'  => $increment ];
            
            $saldoTotalMBTC += $saldoMBTC;
            $saldoTotalUSD  += $saldoUSDT;
            
            if($asset->code === 'BTC') $btcData = $currentAsset;
            
            $assets[] = $currentAsset;
        }
        
        return [    'TOTAL_USD'      => $saldoTotalUSD,
                    'TOTAL_MBTC'     => $saldoTotalMBTC,
                    'VALOR_BTC_USD'  => $btcData['VALOR_USDT'],
                    'INCREMENTP_BTC' => $btcData['INCREMENTP'],
                    'assets'         => $assets ];
    }
}
