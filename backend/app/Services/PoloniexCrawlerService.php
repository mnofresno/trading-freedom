<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;
use Htunlogic\Poloniex\Client;
use Illuminate\Support\Facades\Config;

class PoloniexCrawlerService extends BaseCrawlerService implements ICrawlerService
{
    const PREFERRED_DATE_FORMAT = 'Y-m-d H:i:s';

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
        // $result = json_decode('{
        //     "BTC_ARDR": [],
        //     "BTC_BAT": [],
        //     "BTC_BCH": [],
        //     "BTC_ETH":
        //     [
        //         {
        //             "orderNumber": "514515459658",
        //             "type": "buy",
        //             "rate": "0.00001000",
        //             "startingAmount": "100.00000000",
        //             "amount": "100.00000000",
        //             "total": "0.00100000",
        //             "date": "2018-10-23 17:41:15",
        //             "margin": 0, 
        //             "clientOrderId": "12345"
        //         }
        //     ]
        // }', true);
          
        $result = $this->getClient($userId)->getOpenOrders('all');
        return $this->formatTrades($result);
    }

    public function getTrades($userId) {
        $start = Carbon::createFromFormat(self::PREFERRED_DATE_FORMAT, '2021-01-01 00:00:00')->timestamp;
        $result = $this->getTradesByDate($userId, $start);
        return $this->formatTrades($result);
    }

    private function formatTrades(array $trades): array {
        $notNullOrders = array_filter($trades, function (array $orders) {
            return !empty($orders);
        });
        $output = array_reduce(array_keys($notNullOrders), function (array &$result, string $pair) use ($notNullOrders) {
            $orders = $notNullOrders[$pair];
            $result = array_merge($result, array_map(function (array $item) use ($pair) {
                return $this->formatTradeItem($item, $pair);
            }, $orders)); 
            return $result;
        }, []);
        return $output;
    }

    private function formatTradeItem(array $item, string $pair): array {
        list($from, $to) = explode('_', $pair);
        return [
            'from' => $from,
            'to' => $to,
            'pair' => $pair,
            'type' => $item['type'],
            'amount' => $item['amount'],
            'total' => $item['total'],
            'rate' => $item['rate'],
            'date' => $item['date']
        ];
    }

    private function getTradesByDate($userId, $start, $end = null) {
        return $this->getClient($userId)->trading([
            'command' => 'returnTradeHistory',
            'currencyPair' => 'all',
            'start' => $start,
            'end' => $end
        ]);
    }
}
