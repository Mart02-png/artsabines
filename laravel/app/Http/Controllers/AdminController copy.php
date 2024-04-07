<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;
use Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $admins = User::all();
        return response()->json($admins);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required',
            'password' => 'required',
            
        ]);
        $validatedData['email_verified_at'] = now();
        $admin = User::create($validatedData);
        \Log::info('Validated Data:', $validatedData);

        return response()->json($admin, 201);
    }


    public function __construct(){
        $this->middleware('auth:api',['except'=>['login','register']]);
    }

    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Create user instance
        $user = new User([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Set email_verified_at
        $user->email_verified_at = now();

        // Save user
        $user->save();

        return response()->json([
            "status" => 1,
            "message" => "User registered successfully",
            "user" => $user
        ], 200);
    }

        
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation failed",
                "errors" => $validator->errors(),
            ], 422);
        }

        // Attempt to authenticate the user
        if (!$token = Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid credentials"
            ], 401);
        }

        // Send response with token
        return response()->json([
            "status" => "success",
            "message" => "Logged in successfully",
            "access_token" => $token
        ]);
    }

    public function logout(Request $request)
    {
        // Attempt to logout the authenticated user
        Auth::logout();

        return response()->json([
            "status" => "success",
            "message" => "Logged out successfully"
        ]);
    }

}
