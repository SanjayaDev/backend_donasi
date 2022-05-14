<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donatur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * login
     *
     * @param  mixed $request
     * @return void
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), 400);
        }

        $donatur = Donatur::where("email", $request->email)->first();
        // dd($request->password);

        if (!$donatur || !Hash::check($request->password, $donatur->password)) {
            return \response()->json([
                "success" => FALSE,
                "message" => "Login failed!",
            ], 400);
        }

        return \response()->json([
            'success' => true,
            'message' => 'Login Berhasil!',
            'data'    => $donatur,
            'token'   => $donatur->createToken('authToken')->accessToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil!',  
        ]);
    }
}
