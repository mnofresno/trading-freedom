<?php

namespace App\Services;

use Messerli90\Bittrex\Bittrex;
use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;

class BittrexCrawlerService extends BaseCrawlerService implements ICrawlerService
{
    protected function getExchangeProviderCode()
    {
        return 'BITTREX';
    }

    protected function getUSDSymbols(): array
    {
        return ['USDT'];
    }

    public function __construct(User $user, ExchangeProvider $exchangeProvider)
    {
        parent::__construct($user, $exchangeProvider);
    }
    
    protected function getClientBalances($userId)
    {
        return $this->getClient($userId)->getBalances()->result;
    }

    protected function getClient($userId)
    {
        $userKey = $this->getAuthKeys($userId);
        return new Bittrex($userKey['key'], $userKey['secret']);
    }

    protected function getAmount($value)
    {
        return $value->Balance;
    }

    protected function GetMarketAverage($market)
    {
        $btcMkt = $market->result[0];

        $btcLast = $btcMkt->Last;
        $btcBid = $btcMkt->Bid;
        $btcAsk = $btcMkt->Ask;
        return ($btcLast + $btcBid + $btcAsk) / 3;
    }

    protected function GetBitcoinDollarMarket($userId)
    {
        $bittrexClient = $this->getClient($userId);
        
        $btcMkt = $bittrexClient->getMarketSummary('USDT-BTC');
        
        return $this->GetMarketAverage($btcMkt);
    }

    protected function getCurrencyMarket($currency, $tickers, $client)
    {
        $marketSymbol = 'BTC-' . $currency;
        return $tickers->first(function ($s) use ($currency, $marketSymbol) {
            return $s->MarketName == $marketSymbol;
        });
    }
    
    protected function getTickers($client)
    {
        return collect($client->getMarketSummaries()->result);
    }

    public function GetAllAssetsVersusBtcWithMarketData()
    {
        $bittrex = $this->getClient();
        $markets = $bittrex->getMarketSummaries()->result;
        
        $result = [];
        
        $valorBtc = $this->GetBitcoinDollarMarket();
        
        foreach($markets as $market)
        {
            if(starts_with($market->MarketName, 'BTC-'))
            {
                $valorPromedio = $this->GetMarketAverage($market);
                $result[] = [ 'code' => str_after($market->MarketName, 'BTC-'), 'VALOR_MBTC' => $valorPromedio * 1000, 'VALOR_USDT' => $valorPromedio * $valorBtc ];
            }
        }
        
        $result[] = [ 'code' => 'BTC', 'VALOR_MBTC' => 1000, 'VALOR_USDT' => $valorBtc ];
        
        return $result;
    }
    
    public function GetAllAssetsVersusBtc()
    {
        $bittrex = $this->getClient();
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
}
