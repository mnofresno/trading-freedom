<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Response;
use Input;
use JWTAuth;

class UsersController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {

    }

    public function UpdateFCMToken($user_id)
    {
        $user = $this->getCurrentUser();

        if($user->id != $user_id)
        {
            return Response::json(["result" => "No puede cambiar el token de otro usuaro"], 401);
        }

        $token = Input::only("fcm_token");

        if($token["fcm_token"] != '' && $token["fcm_token"] != null)
        {
            $user->fcm_token = $token["fcm_token"];

            $user->save();

            return Response::json(["result" => "Token modificado correctamente"], 200);
        }
        return Response::json(["result" => "No se ha enviado un token"], 403);
    }
}
