<?php

namespace App\Services;

use App\Services\BitfinexCrawlerService;
use App\Services\BittrexCrawlerService;

class MultiCrawlerService
{
    private $crawlers = [];

    public function __construct(
        BitfinexCrawlerService $bitfinexCrawler,
        BittrexCrawlerService $bittrexCrawler)
    {
        $this->crawlers['BITTREX'] = $bittrexCrawler;
        $this->crawlers['BITFINEX'] = $bitfinexCrawler;
    }

    public function GetAllBalances($user_id, $exchange = 'BITTREX')
    {
        $crawler = $this->crawlers[$exchange];
        return $crawler->GetAllBalances($user_id);
    }
}
