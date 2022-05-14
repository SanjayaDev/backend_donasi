<?php

namespace App\Http\Controllers\Api;

use App\Models\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    /**
     * Register
     * 
     * @param mixed $request
     * @return void
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email|unique:donaturs",
            "password" => "required|min:8|confirmed",
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), 400);
        }

        // dd($validator->validated());
        $donatur = Donatur::create($validator->validated());

        return \response()->json([
            "success" => TRUE,
            "message" => "Successfully registration",
            "data"    => $donatur,
            "token"    => $donatur->createToken('authToken')->accessToken,
        ], 201);
    }
}
