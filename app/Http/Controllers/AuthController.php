<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request){
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:8',
            ]);
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            return response()->json(['message' => 'New user registered'], 201);
        } catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

public function login(Request $request) {
    try {

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['fel inlog'],
            ]);
        }

        $token = $user->createToken('accessToken');

        return response()->json(['accessToken' => $token->plainTextToken], 200);

    } catch(\Exception $e) {

        return response()->json(['message' => $e->getMessage()], 401);
    }
}
public function logout (Request $request){
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'logged out'], 200);
}
}