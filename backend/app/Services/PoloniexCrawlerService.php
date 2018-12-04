<?php

namespace App\Services;

use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Htunlogic\Poloniex\Poloniex;
use Htunlogic\Poloniex\Client;
use Illuminate\Support\Facades\Config;

class PoloniexCrawlerService extends BaseCrawlerService implements ICrawlerService
{
    public function __construct(User $user,  ExchangeProvider $exchangeProvider)
    {
        parent::__construct($user, $exchangeProvider);
    }
    
    private function GetPoloniex($user_id = null)
    {
        $currentUser = $this->user->find($user_id);
     
        $poloniexExchangeProviderId = $this->exchangeProvider->where('code', '=', 'POLONIEX')->firstOrFail()->id;
        
        $userPoloniexKey = $currentUser->apiKeys()->where('exchange_provider_id', '=', $poloniexExchangeProviderId)->firstOrFail();
        $auth = ['key' => $userPoloniexKey->api_key,'secret' => $userPoloniexKey->api_secret ];
        $urls = Config::get('poloniex.urls');
        return new Client($auth, $urls);  
    }

    public function GetBalances($user_id = null)
    {
        $poloniexClient = $this->GetPoloniex($user_id);
        $balances = $poloniexClient->getBalances();
        return collect($balances)->filter(function($v)
            {
                return $v > 0;
            })->map(function($value, $key)
            {
                $balance = ['Currency' => $key,
                            'Balance' => $value,
                            'Available' => '',
                            'Pending' => '',
                            'CryptoAddress' => ''];
                return json_decode(json_encode($balance), FALSE);
            });
    }

    public function GetBitcoinDollarMarket($user_id)
    {
        $poloniexClient = $this->GetPoloniex($user_id);
        
        $btcMkt = $poloniexClient->getTicker('USDT_BTC');
        
        $btcLast = $btcMkt['last'];
        $btcBid  = $btcMkt['highestBid'];
        $btcAsk  = $btcMkt['lowestAsk'];
        $btcMean = ( $btcLast + $btcBid + $btcAsk ) / 3;
        
        return $btcMean;
    }
    
    public function GetAllBalances($user_id)
    {    
        $poloniexClient = $this->GetPoloniex($user_id);
        
        $btcMean = $this->GetBitcoinDollarMarket($user_id);
        
        $balances = $this->GetBalances($user_id);
        
        $outputBalances = [];
        
        $saldoTotalMBTC = 0;
        $saldoTotalUSD  = 0;
        
        $tickers = $poloniexClient->getTickers();

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
                        $mkt = $tickers[$symbol];

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
