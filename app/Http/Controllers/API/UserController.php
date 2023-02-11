<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            //validate  request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            //find user
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials))
            {
                ResponseFormatter::error('Unautherized',401);
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password,$user->password)) {
                throw new Exception('Invalid Password');
            }

            //generate Token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return result
            return ResponseFormatter::success([
                'accessToken' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Login Success');

        } catch (Exception $th) {
            return ResponseFormatter::error('Authentication Failed');
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['required','email','unique:users'],
                'password' => ['required' ,'string', New Password]
            ]);

            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
            ]);

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'accessToken' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Register Success');
            
        } catch (Exception $err) {
            return ResponseFormatter::error($err->getMessage());
        }

    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token,"Logout Success");
    }

    public function fetch(Request $request)
    {
        $user = $request->user();
        return ResponseFormatter::success($user, 'Fetch success');
    }
}
