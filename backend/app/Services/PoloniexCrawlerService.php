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

    protected function getUSDSymbols(): array
    {
        return ['USDT', 'USDC'];
    }

    public function __construct(User $user,  ExchangeProvider $exchangeProvider)
    {
        parent::__construct($user, $exchangeProvider);
    }

    protected function getClientBalances($userId)
    {
        return $this->getCompleteBalances($userId);
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
            'Balance' => $this->getAmount($value),
            'Available' => $value['available'],
            'Pending' => $value['onOrders'],
            'CryptoAddress' => ''
        ];
        return json_decode(json_encode($balance), false);        
    }

    protected function getAmount($value)
    {
        return $value['available'] + $value['onOrders'];
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
        throw new BadMethodCallException("Not implemented");
    }
    
    public function GetAllAssetsVersusBtc()
    {
        throw new BadMethodCallException("Not implemented");
    }

    private function getCompleteBalances($userId)
    {
        return $this->getClient($userId)->trading([
            'command' => 'returnCompleteBalances',
            'account' => 'all'
        ]);
    }

    function GetOpenOrders($userId)
    {
        // Example response for 'all'
        // {
        //     "BTC_ARDR": [],
        //     "BTC_BAT": [],
        //     "BTC_BCH": [],
        //     "BTC_ETH":
        //     [
        //         {
        //             "orderNumber": '514515459658',
        //             "type": 'buy',
        //             "rate": '0.00001000',
        //             "startingAmount": '100.00000000',
        //             "amount": '100.00000000',
        //             "total": '0.00100000',
        //             "date": '2018-10-23 17:41:15',
        //             "margin": 0, 
        //             "clientOrderId": '12345'
        //         }
        //     ]
        // }
          
        $allOrders = $this->getClient($userId)->getOpenOrders('all');
        $output = array_reduce(array_keys($allOrders), function (array &$result, string $pair) use ($allOrders) {
            $orders = $allOrders[$pair];
            $result[] = array_map(function (array $item) use ($pair) {
                return [
                    'pair' => $pair,
                    'type' => $item['type'],
                    'amount' => $item['amount'],
                    'total' => $item['total'],
                    'rate' => $item['rate'],
                    'date' => $item['date']
                ];
            }, $orders); 
            return $result;
        }, []);
    }
}
