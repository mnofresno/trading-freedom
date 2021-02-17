<?php

namespace App\Services;

use App\Services\BitfinexCrawlerService;
use App\Services\BittrexCrawlerService;
use App\Services\PoloniexCrawlerService;

class MultiCrawlerService
{
    private $crawlers = [];

    public function __construct(
        BitfinexCrawlerService $bitfinexCrawler,
        BittrexCrawlerService $bittrexCrawler,
        PoloniexCrawlerService $poloniexCrawler)
    {
        $this->crawlers['BITTREX'] = $bittrexCrawler;
        $this->crawlers['BITFINEX'] = $bitfinexCrawler;
        $this->crawlers['POLONIEX'] = $poloniexCrawler;
    }

    public function GetAllBalances($user_id, $exchange = 'BITTREX')
    {
        $crawler = $this->crawlers[$exchange];
        return $crawler->GetAllBalances($user_id);
    }

    public function GetOrders($user_id) {
        // FIXME: By the moment only POLONIEX has implemented orders
        $crawler = $this->crawlers['POLONIEX'];
        return [
            'open' => $crawler->GetOpenOrders($user_id),
            'closed' => $crawler->GetTrades($user_id),
            'exchange' => 'POLONIEX'
        ];
    }
}
