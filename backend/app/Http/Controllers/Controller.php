<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use JWTAuth;
use Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getCurrentUser()
    {
        $headerAuth = Request::header('Authorization');
        if ($headerAuth)
        {
            $user = JWTAuth::parseToken()->authenticate();
        }
        return $user;
    }
}
