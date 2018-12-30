<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Models\User;
use Illuminate\Http\Request;

class OwnUsersController extends Controller
{
    public function index()
    {
        $user = $this->getCurrentUser();
        return $user;
    }

    public function store(Request $request)
    {
        $user = $request->all();
        
        if ($request->has('password')) {
            $user['password'] = bcrypt($user['password']);
        }
        $this->getCurrentUser()->update($user);
    }
}
