<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\BittrexCrawlerService as BittrexCrawler;

class BalancesController extends Controller
{
    private $bittrexCrawler;
    
    public function __construct(BittrexCrawler $bittrexCrawler)
    {
        $this->bittrexCrawler = $bittrexCrawler;
    }
    
    public function index()
    {
        return $this->bittrexCrawler->GetAllBalances($this->getCurrentUser()->id);
    }
}
