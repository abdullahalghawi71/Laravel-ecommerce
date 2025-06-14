<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordCodeMail;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    /**
     * Send reset code to email (Step 1)
     */
    public function sendResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        PasswordReset::where('email', $validated['email'])->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordReset::create([
            'email' => $validated['email'],
            'code' => $code,
            'created_at' => now(),
        ]);

        try {
            Mail::to($validated['email'])->send(new ResetPasswordCodeMail($code));

            return response()->json([
                'success' => true,
                'message' => 'Reset code sent successfully.',
                'email' => $validated['email'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to send reset code email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset code. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify reset code (Step 2)
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $record = PasswordReset::where('code', $validator->validated())->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code verified successfully.',
        ]);
    }

    /**
     * Reset password (Step 3)
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = PasswordReset::where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset attempt.',
            ], 422);
        }

        $user = User::where('email', $validated['email'])->first();
        $user->password = Hash::make($validated['password']);
        $user->save();

        PasswordReset::where('email', $validated['email'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
