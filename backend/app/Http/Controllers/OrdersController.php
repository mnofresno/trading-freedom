<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\MultiCrawlerService;
use App\Models\ExchangeProvider;

class OrdersController extends Controller
{
    private $crawler;
    
    public function __construct(MultiCrawlerService $crawler)
    {
        $this->crawler = $crawler;
    }
    
    public function index()
    {
        return $this->crawler->GetOrders($this->getCurrentUser()->id);
    }

    public function show($id)
    {
        throw new Exception('Method not implemented');
    }
}
