<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordCodeMail;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Send reset code to email (Step 1)
     */
    public function sendResetCode(Request $request)
    {
        // 1. Validate email input
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // 2. Delete existing codes for this email (if any)
        PasswordReset::where('email', $validated['email'])->delete();

        // 3. Generate a new 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 4. Store the code using Eloquent
        PasswordReset::create([
            'email' => $validated['email'],
            'code' => $code,
            'created_at' => now(),
        ]);

        // 5. Attempt to send the email
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
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.'
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code verified successfully.',
            'email' => $request->email
        ]);
    }

    /**
     * Reset password (Step 3)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
            'password' => 'required|min:8|confirmed'
        ]);

        // Verify code again for security
        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset attempt.'
            ], 422);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Clean up
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ]);
    }
}
