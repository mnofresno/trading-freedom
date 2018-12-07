<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\ExchangeProvider;

class OwnExchangeProvidersController extends Controller
{
    
    public function index()
    {
        $user = $this->getCurrentUser();

        $exchanges = $user->apiKeys->pluck('exchange_provider_id')->toArray();
        
        return ExchangeProvider::whereIn('id', $exchanges)->get();
    }
}
