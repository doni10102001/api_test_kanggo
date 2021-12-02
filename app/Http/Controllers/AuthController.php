<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use DB;

class AuthController extends Controller
{
    public function __construct()
    {
        // Protected Middleware Auth
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        // Validasi Request 
        if ($request->email == '' && $request->password == '') {
            return $this->response_data("Validation Error!", ["email" => "Email Must Required!", "password" => "Password Must Required!"], 409);
        }

        if ($request->email == '') {
            return $this->response_data("Validation Error!", ["email" => "Email Must Required!"], 409);
        }

        if ($request->password == '') {
            return $this->response_data("Validation Error!", ["password" => "Password Must Required!"], 409);
        }

        // Get Request All
        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($request->password);
            $data['cps'] = $request->password;
        }

        // Insert DB
        $save = DB::table('users')->insert($data);

        if ($save == true) {
            $response = $this->response_message("Register User Success.");
        } else {
            $response = $this->response_message("Register User Failed!", 409);
        }

        return $response;
    }

    public function login(Request $request)
    {
        // Validasi Request 
        if ($request->email == '' && $request->password == '') {
            return $this->response_data("Validation Error!", ["email" => "Email Must Required!", "password" => "Password Must Required!"], 422);
        }

        if ($request->email == '') {
            return $this->response_data("Validation Error!", ["email" => "Email Must Required!"], 422);
        }

        if ($request->password == '') {
            return $this->response_data("Validation Error!", ["password" => "Password Must Required!"], 422);
        }

        // Request with authenticationn
        $credentials = [
            "email"     => $request->email,
            "password"  => $request->password
        ];

        // Process authenticationn user
        if (!$token = Auth::attempt($credentials)) {
            return $this->response_message("Authentication is Unauthorized!", 401);
        }

        return $this->response_data("Authenticationn User Success", [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 6000
        ]);
    }

    public function logout()
    {
        // Remove Auth User
        Auth::logout();
        return $this->response_message("Logout Success.");
    }

    public function profile()
    {
        // Result Data User By Auth Login
        return $this->response_data("Results Data User", Auth::user());
    }

    public function refresh()
    {
        // Refresh Token user
        return $this->response_data("Authenticationn User Success", [
            'access_token' => Auth::refresh(),
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 6000
        ]);
    }
}
