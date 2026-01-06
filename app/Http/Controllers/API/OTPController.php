<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class OTPController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone']);
        $user = User::where('phone', $request->phone)->first();
        $user->sendOneTimePassword();
        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone', 'otp' => 'required|string']);
        $user = User::where('phone', $request->phone)->first();
        if ($user->attemptLoginUsingOneTimePassword($request->otp)->isOk()) {
            return response()->json(['message' => 'OTP verified successfully']);
        }
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }
}
