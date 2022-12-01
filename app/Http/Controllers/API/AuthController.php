<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;



class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'emp_code' => 'required|max:8|unique:users',
            'mobile_number' => 'required|max:11',
            'mac_address' => '', 
            'ip_address' => '',   
            'email' => 'email|required',
            'password' => 'required|confirmed',
            'sccode' => '',
            'prj_code' => '',
            'desig_code' => '',
            'desig_name' => '',
            'dept_code' => '',
            'dept_name' => '',
            'off_code' => '',
            'off_type' => '',
            'zone_code' => '',
            'div_code' => '',
            'android_id' => 'required',
        ]);

        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user' => $user, 'access_token' => $accessToken], 201);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'emp_code' => 'required',
            'password' => 'required',
            'android_id' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'This User does not exist, check your details'], 400);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken]);
    }

    public function logout (Request $request) {
        $accessToken = auth()->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();

        return response([
            'message' => 'You have been successfully logged out.',
        ], 200);
    }
}