<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\BitfinexCrawlerService;
use App\Models\ExchangeProvider;
use App\Models\User;
use App\Services\BittrexCrawlerService;

class BittrexCrawlerServiceTest extends TestCase
{
    public function testGetBalances()
    {
        $exchangeProvider = new ExchangeProvider();
        $user = new User();
        $subject = new BittrexCrawlerService($user, $exchangeProvider);
        $result = $subject->GetBalances(1);
        $data = $result->toArray();
        $this->assertTrue($data != null);
        //print_r($data);
    }
    
    public function testGetAllBalances()
    {
        $exchangeProvider = new ExchangeProvider();
        $user = new User();
        $subject = new BittrexCrawlerService($user, $exchangeProvider);
        $result = $subject->GetAllBalances(1);
        $data = $result;
        $this->assertTrue($data != null);
        //print_r($data);
    }
}
