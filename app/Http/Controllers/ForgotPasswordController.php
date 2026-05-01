<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController
{
    /**
     * Send a password reset link to the given email.
     * POST /api/auth/forgot-password
     */
    public function sendResetLink(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Reset link sent to your email.',
                ]);
            }

            return response()->json([
                'message' => 'Unable to send reset link. Please check your email address.',
            ], 422);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reset the user's password.
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token'    => ['required', 'string'],
                'email'    => ['required', 'email'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password),
                    ])->save();

                    // Revoke all existing tokens so the user must log in again
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password reset successfully. Please log in.',
                ]);
            }

            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 422);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}