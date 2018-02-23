<?php

namespace App\Services;

use Messerli90\Bittrex\Bittrex;
use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;

class BittrexCrawlerService
{
    private $user;
    private $exchangeProvider;
    
    public function __construct(User $user, ExchangeProvider $exchangeProvider)
    {
        $this->exchangeProvider = $exchangeProvider;
        $this->user = $user;
    }
    
    private function GetBittrex($user_id = null)
    {
        if($user_id == null) return new Bittrex();
        $currentUser = $this->user->find($user_id);
        
        $bittrexExchangeProviderId = $this->exchangeProvider->where('code', '=', 'BITTREX')->firstOrFail()->id;
        
        $userBittrexKey = $currentUser->apiKeys()->where('exchange_provider_id', '=', $bittrexExchangeProviderId)->firstOrFail();
                     
        return new Bittrex($userBittrexKey->api_key, $userBittrexKey->api_secret);
    }

    public function GetBalances($user_id = null)
    {
        $bittrexClient = $this->GetBittrex($user_id);
        $balances = $bittrexClient->getBalances()->result;
        return collect($balances)->filter(function($v){ return $v->Balance > 0; });
    }

    private function GetBitcoinDollarMarket()
    {
        $bittrexClient = $this->GetBittrex();
        
        $btcMkt = $bittrexClient->getMarketSummary('USDT-BTC');
        
        $btcMkt = $btcMkt->result[0];
        
        $btcLast = $btcMkt->Last;
        $btcBid  = $btcMkt->Bid;
        $btcAsk  = $btcMkt->Ask;
        $btcMean = ( $btcLast + $btcBid + $btcAsk ) / 3;
        
        return $btcMean;
        
    }
    
    public function GetAllBalances($user_id)
    {    
        $bittrexClient = $this->GetBittrex($user_id);
        $summaries = collect($bittrexClient->getMarketSummaries()->result);
        
        $btcMean = $this->GetBitcoinDollarMarket();
        
        $balances = $bittrexClient->getBalances()->result;
        
        $outputBalances = [];
        
        $saldoTotalMBTC = 0;
        $saldoTotalUSD  = 0;
        
        foreach($balances as $balance)
        {
            $detalleBalance = [];
            $saldo = $balance->Balance;
            if($saldo > 0)
            {
                $currency = $balance->Currency;
                try{
                    
                    if($currency != 'BTC') 
                    {
                        $mkt = $summaries->first(function($s)use($currency){ return $s->MarketName == 'BTC-'.$currency; });

                        $last        = $mkt->Last;
                        $bid         = $mkt->Bid;
                        $ask         = $mkt->Ask;
                        $mean        = ( $last + $bid + $ask ) / 3;
                        $mBtcMean    = $mean * 1000;
                        $dollarValue = $mean * $btcMean;
                    }
                    else
                    {
                        $mean        = 1;
                        $mBtcMean    = 1000;
                        $dollarValue = $btcMean;
                    }
                    
                    $mBtcMean    = $this->toFixed($mBtcMean);
                    $dollarValue = $this->toFixed($dollarValue);
                    $saldoMBTC   = $this->toFixed($mBtcMean * $saldo);
                    $saldoUSD    = $this->toFixed($dollarValue * $saldo);
                    $saldo       = $this->toFixed($saldo);
                    
                    $detalleBalance['MONEDA'    ] = $currency;
                    $detalleBalance['VALOR_MBTC'] = $mBtcMean;
                    $detalleBalance['VALOR_USDT'] = $dollarValue;
                    $detalleBalance['SALDO'     ] = $saldo;
                    $detalleBalance['SALDO_MBTC'] = $saldoMBTC;
                    $detalleBalance['SALDO_USDT'] = $saldoUSD;
                    
                    $saldoTotalMBTC += $saldoMBTC;
                    $saldoTotalUSD  += $saldoUSD;
                }
                catch(\Exception $e){
                    $detalleBalance['ERROR'] = "Error obteniendo datos para $currency";
                }
                $outputBalances[] = $detalleBalance;
            }
        }
        
        return [ 'TOTAL_USD'     => $saldoTotalUSD,
                 'TOTAL_MBTC'    => $saldoTotalMBTC,
                 'VALOR_BTC_USD' => $btcMean,
                 'assets'        => $outputBalances ];
    }
    
    public function GetAllAssetsVersusBtcWithMarketData()
    {
        $bittrex = $this->GetBittrex();
        $markets = $bittrex->getMarketSummaries()->result;
        
        $result = [];
        
        $valorBtc = $this->GetBitcoinDollarMarket();
        
        foreach($markets as $market)
        {
            if(starts_with($market->MarketName, 'BTC-'))
            {
                $valorPromedio = ($market->Last + $market->Bid + $market->Ask) / 3;                
                $result[] = [ 'code' => str_after($market->MarketName, 'BTC-'), 'VALOR_MBTC' => $valorPromedio * 1000, 'VALOR_USDT' => $valorPromedio * $valorBtc ];
            }
        }
        
        $result[] = [ 'code' => 'BTC', 'VALOR_MBTC' => 1000, 'VALOR_USDT' => $valorBtc ];
        
        return $result;
    }
    
    public function GetAllAssetsVersusBtc()
    {
        $bittrex = $this->GetBittrex();
        $markets = $bittrex->getMarkets()->result;
        
        $result = [];
        
        foreach($markets as $market)
        {
            if($market->BaseCurrency == 'BTC' && $market->IsActive == 1)
            {
                $result[] = [ 'code' => $market->MarketCurrency, 'description' => $market->MarketCurrencyLong ];
            }
        }
        
        $result[] = [ 'code' => 'BTC', 'description' => 'Bitcoin' ];
        
        return $result;
    }
    
    private function toFixed($number)
    {
        return number_format($number, 4, ".", "");
    }
}
