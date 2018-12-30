<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use JWTAuthException;
use JWTAuth;
use Illuminate\Http\Request;
use Response;

class LoginController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|unique:users',
        ]);
        
        $user = $this->user->create([
          'name' => $request->get('name'),
          'email' => $request->get('email'),
          'password' => bcrypt($request->get('password'))
        ]);
        return response()->json(['status'=>true,'message'=>'User created successfully','data'=>$user]);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['E-mail o password inválidos'], 422);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        $user_id = $this->user->where('email', '=', $credentials['email'])->firstOrFail()->id;
        return response()->json(compact('token', 'user_id'));
    }

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->middleware('guest')->except('logout');
    }
}
