<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\ExchangeProvider;

class ExchangeProvidersController extends Controller
{
    
    public function index()
    {
        return ExchangeProvider::all();
    }
}
