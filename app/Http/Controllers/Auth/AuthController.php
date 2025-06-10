<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Password;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:manager,employee,user,admin',
            'warehouse_id' => ''
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'     => $request->input('role'),
            'warehouse_id' => $request->input('warehouse_id')
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = auth()->user();
        return response()->json(compact('user','token'));
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $code = rand(100000, 999999);

        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'code' => $code,
                'created_at' => Carbon::now()
            ]
        );

        Mail::raw("Your reset code is: $code", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset Code');
        });

        return response()->json(['message' => 'Reset code sent.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['error' => 'Invalid or expired code'], 422);
        }

        // Reset user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset record
        PasswordReset::where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset.']);
    }
}
