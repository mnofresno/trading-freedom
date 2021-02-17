<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\ExchangeProvider;
use App\Models\User;
use App\Models\UserKey;
use App\Services\PoloniexCrawlerService;

class PoloniexCrawlerServiceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetBalances()
    {
        $user = new User();
        $exchangeProvider = new ExchangeProvider();
        $subject = new PoloniexCrawlerService($user, $exchangeProvider);
        $result = $subject->GetBalances(1);
        $data = $result->toArray();
        print_r($data);
        $this->assertTrue($data != null);
    }

    public function testGetBtcMarket()
    {
        $user = new User();
        $exchangeProvider = new ExchangeProvider();
        $subject = new PoloniexCrawlerService($user, $exchangeProvider);
        $data = $subject->GetBitcoinDollarMarket(1);
        $this->assertTrue($data != null);
    }
    
    public function testGetAllBalances()
    {
        $user = new User();
        $exchangeProvider = new ExchangeProvider();
        $subject = new PoloniexCrawlerService($user, $exchangeProvider);
        $data = $subject->GetAllBalances(1);
        print_r($data);
        $this->assertTrue($data != null);
    }

    public function testGetOpenOrders()
    {
        $user = new User();
        $user->save();
        $apiKeys = new UserKey();
        $apiKeys->api_key = 'test-key';
        $apiKeys->api_secret = 'test-secret';
        $apiKeys->user = $user;
        $exchangeProvider = new ExchangeProvider(['code' => 'POLONIEX']);
        $exchangeProvider->save();
        $apiKeys->exchangeProvider = $exchangeProvider;
        $apiKeys->save();
        $subject = new PoloniexCrawlerService($user, $apiKeys->exchangeProvider);
        $data = $subject->GetOpenOrders(1);
        print_r($data);
        $this->assertTrue($data != null);
    }
}
