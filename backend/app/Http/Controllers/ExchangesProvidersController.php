<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\ExchangeProvider;

class ExchangesProvidersController extends Controller
{
    public function index()
    {
        return ExchangeProvider::all();
    }
}
