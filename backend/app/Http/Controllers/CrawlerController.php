<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\BittrexCrawlerService as BittrexCrawler;

class CrawlerController extends Controller
{
    private $bittrexCrawler;
    
    public function __construct(BittrexCrawler $bittrexCrawler)
    {
        $this->bittrexCrawler = $bittrexCrawler;
    }
    
    public function GetBalances()
    {
        return $this->bittrexCrawler->GetAllBalances();
    }
}
