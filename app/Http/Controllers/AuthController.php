<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt([
            'username'=>$request->get('username'),
            'password'=>$request->get('password')
        ])){
            $token=Auth::user()->createToken('myToken'.Auth::user()->id)->plainTextToken;
            $user = Auth::user();
            return \response(['user'=> $user,'token'=>$token]);
        }

        else {
            return \response('error with the username or password', 401);
        }
    }

    public function logout()
    {
        return auth()->user()->tokens()->delete();
    }


}
