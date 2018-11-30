<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\MultiCrawlerService;
use App\Models\ExchangeProvider;

class BalancesController extends Controller
{
    private $crawler;
    
    public function __construct(MultiCrawlerService $crawler)
    {
        $this->crawler = $crawler;
    }
    
    public function show($id)
    {
        $exchange = ExchangeProvider::find($id);
        $output = $this->crawler->GetAllBalances($this->getCurrentUser()->id, $exchange->code);
        $output['exchange'] = $exchange;
        return $output;
    }
}
