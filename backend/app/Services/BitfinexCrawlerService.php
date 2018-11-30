<?php

namespace App\Services;

use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

require(__DIR__."/../../vendor/mariodian/bitfinex-api-php/Bitfinex.php");

class BitfinexCrawlerService implements ICrawlerService
{
    private $user;
    private $exchangeProvider;
    
    public function __construct(User $user,  ExchangeProvider $exchangeProvider)
    {
        $this->exchangeProvider = $exchangeProvider;
        $this->user = $user;
    }
    
    private function GetBitfinex($user_id = null)
    {
        $currentUser = $this->user->find($user_id);
        
        $bitfinexExchangeProviderId = $this->exchangeProvider->where('code', '=', 'BITFINEX')->firstOrFail()->id;
        
        $userBittrexKey = $currentUser->apiKeys()->where('exchange_provider_id', '=', $bitfinexExchangeProviderId)->firstOrFail();
                     
        return new \Bitfinex($userBittrexKey->api_key, $userBittrexKey->api_secret);
    }

    public function GetBalances($user_id = null)
    {
        $bitfinexClient = $this->GetBitfinex($user_id);
        $balances = $bitfinexClient->get_balances();
        return collect($balances)->filter(function($v)
            {
                return $v["amount"] > 0;
            })->map(function($item)
            {
                $balance = ['Currency' => strtoupper($item['currency']),
                            'Balance' => $item['amount'],
                            'Available' => '',
                            'Pending' => '',
                            'CryptoAddress' => ''];
                return json_decode(json_encode($balance), FALSE);
            });
    }

    public function GetBitcoinDollarMarket($user_id)
    {
        $bitfinexClient = $this->GetBitfinex($user_id);
        
        $btcMkt = $bitfinexClient->get_ticker('BTCUSD');
        
        $btcLast = $btcMkt['last_price'];
        $btcBid  = $btcMkt['bid'];
        $btcAsk  = $btcMkt['ask'];
        $btcMean = ( $btcLast + $btcBid + $btcAsk ) / 3;
        
        return $btcMean;
        
    }
    
    public function GetAllBalances($user_id)
    {    
        $bitfinexClient = $this->GetBitfinex($user_id);
        
        $btcMean = $this->GetBitcoinDollarMarket($user_id);
        
        $balances = $this->GetBalances($user_id);
        
        $outputBalances = [];
        
        $saldoTotalMBTC = 0;
        $saldoTotalUSD  = 0;
        $symbols = $bitfinexClient->get_symbols();
        $symbols = collect($symbols);

        $symbols = $symbols->filter(function($s){ return  strpos($s, 'ltc') !== false; });

        $symbols = $symbols->toArray();
        foreach($balances as $balance)
        {
            $detalleBalance = [];
            $saldo = $balance->Balance;
            if($saldo > 0)
            {
                $currency = $balance->Currency;
                try{
                    
                    if($currency != 'BTC' && $currency != 'USD') 
                    {

                        $symbol = $currency.'BTC';
                        $mkt = $bitfinexClient->get_ticker($symbol);

                        $last        = $mkt['last_price'];
                        $bid         = $mkt['bid'];
                        $ask         = $mkt['ask'];
                        $mean        = ( $last + $bid + $ask ) / 3;
                        $mBtcMean    = $mean * 1000;
                        $dollarValue = $mean * $btcMean;
                    }
                    else if($currency == 'BTC')
                    {
                        $mean        = 1;
                        $mBtcMean    = 1000;
                        $dollarValue = $btcMean;
                    }
                    else if($currency == 'USD')
                    {
                        $mean = 1 / $btcMean;
                        $dollarValue = 1;
                        $mBtcMean = $mean * 1000;
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
/*        $bitfinex = $this->GetBitfinex();
        $markets = $bitfinex->getMarketSummaries()->result;
        
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
        */
        throw new BadMethodCallException("Not implemented");
    }
    
    public function GetAllAssetsVersusBtc()
    {
        /*$bitfinex = $this->GetBitfinex();
        $markets = $bitfinex->getMarkets()->result;
        
        $result = [];
        
        foreach($markets as $market)
        {
            if($market->BaseCurrency == 'BTC' && $market->IsActive == 1)
            {
                $result[] = [ 'code' => $market->MarketCurrency, 'description' => $market->MarketCurrencyLong ];
            }
        }
        
        $result[] = [ 'code' => 'BTC', 'description' => 'Bitcoin' ];
        
        return $result; */
        throw new BadMethodCallException("Not implemented");
    }
    
    private function toFixed($number)
    {
        return number_format($number, 4, ".", "");
    }
}
