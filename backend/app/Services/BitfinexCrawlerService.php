<?php

namespace App\Services;

use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

require(__DIR__."/../../vendor/mariodian/bitfinex-api-php/Bitfinex.php");

class BitfinexCrawlerService extends BaseCrawlerService implements ICrawlerService
{
    protected function getExchangeProviderCode()
    {
        return 'BITFINEX';
    }

    protected function getUSDSymbols(): array
    {
        return ['USD'];
    }

    public function __construct(User $user,  ExchangeProvider $exchangeProvider)
    {
        parent::__construct($user, $exchangeProvider);
    }
    
    protected function getClient($userId)
    {
        $userKey = $this->getAuthKeys($userId);
        return new \Bitfinex($userKey['key'], $userKey['secret']);
    }

    protected function getClientBalances($userId)
    {
        return $this->getClient($userId)->get_balances();
    }

    protected function getAmount($value)
    {
        return $value["amount"] > 0;
    }

    protected function mapBalancesCallback($value, $key)
    {
        $balance = [
            'Currency' => strtoupper($value['currency']),
            'Balance' => $value['amount'],
            'Available' => '',
            'Pending' => '',
            'CryptoAddress' => ''
        ];
        return json_decode(json_encode($balance), false);        
    }
    
    protected function GetMarketAverage($market)
    {
        $btcLast = $market['last_price'];
        $btcBid = $market['bid'];
        $btcAsk = $market['ask'];
        return ($btcLast + $btcBid + $btcAsk) / 3;
    }

    protected function GetBitcoinDollarMarket($userId)
    {
        $bitfinexClient = $this->getClient($userId);
        
        $btcMkt = $bitfinexClient->get_ticker('BTCUSD');
        
        return $this->GetMarketAverage($btcMkt);
    }

    protected function getCurrencyMarket($currency, $tickers, $client)
    {
        $marketSymbol = $currency . 'BTC';
        return $client->get_ticker($marketSymbol);
    }
    
    protected function getTickers($client)
    {
        // By the moment, bitfinex has no get all tickers function
        return null;
    }

    public function GetAllAssetsVersusBtcWithMarketData()
    {
/*        $bitfinex = $this->getClient();
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
        /*$bitfinex = $this->getClient();
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
