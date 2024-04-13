<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api',['except'=>['login','register']]);
    }

    public function index()
    {
        $admins = User::all();
        return response()->json($admins);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = new User([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
    
        $user->email_verified_at = now();
        $user->save();
    
        // Generate token
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            "status" => 1,
            "message" => "User registered successfully",
            "user" => $user,
            "token" => $token
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Invalid credentials"
                ], 401);
                // return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token is expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token not found'], 500);
        }
    
        if ($user) {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } else {
            return response()->json(['error' => 'Could not log out']);
        }
    }
}
