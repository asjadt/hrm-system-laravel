<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwaggerLoginController extends Controller
{
    public function login(Request $request) {
        return view("swagger-login");

    }
    public function passUser(Request $request) {
        if($request->email == "asjadtariq@gmail.com" && $request->password == "Sw@gger@doc1") {
            session(['token' => '12345678']);
            return redirect("/api/documentation");
        }
        return response()->json([
            "message" => "Invalid Credentials"
        ],422);




    }
}
