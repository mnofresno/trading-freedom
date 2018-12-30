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
    protected function getExchangeProviderCode()
    {
        return 'POLONIEX';
    }

    protected function getUSDSymbol()
    {
        return 'USDT';
    }

    public function __construct(User $user,  ExchangeProvider $exchangeProvider)
    {
        parent::__construct($user, $exchangeProvider);
    }

    protected function getClientBalances($userId)
    {
        return $this->getClient($userId)->getBalances();
    }
    
    protected function getClient($userId)
    {
        $urls = Config::get('poloniex.urls');
        return new Client($this->getAuthKeys($userId), $urls);  
    }

    protected function mapBalancesCallback($value, $key)
    {
        $balance = [
            'Currency' => $key,
            'Balance' => $value,
            'Available' => '',
            'Pending' => '',
            'CryptoAddress' => ''
        ];
        return json_decode(json_encode($balance), false);        
    }

    protected function getAmount($value)
    {
        return $value;
    }

    protected function GetMarketAverage($market)
    {
        $btcLast = $market['last'];
        $btcBid = $market['highestBid'];
        $btcAsk = $market['lowestAsk'];
        return ($btcLast + $btcBid + $btcAsk) / 3;
    }

    protected function GetBitcoinDollarMarket($userId)
    {
        $poloniexClient = $this->getClient($userId);
        
        $btcMkt = $poloniexClient->getTicker('USDT_BTC');
        
        return $this->GetMarketAverage($btcMkt);
    }
    
    protected function getCurrencyMarket($currency, $tickers, $client)
    {
        $marketSymbol = 'BTC_' . $currency;
        return $tickers[$marketSymbol];
    }

    protected function getTickers($client)
    {
        return $client->getTickers();
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
}
