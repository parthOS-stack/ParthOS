<?php

namespace App\Http\Controllers;

use App\Services\ForgotPasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function show(ForgotPasswordService $forgotPassword)
    {
        if (session('admin_logged_in')) {
            return redirect('/dashboard');
        }

        return view('auth.forgot-password', [
            'defaultEmail' => $forgotPassword->defaultEmail(),
        ]);
    }

    public function sendOtp(Request $request, ForgotPasswordService $forgotPassword)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $forgotPassword->sendOtp(
            (string) $request->input('email'),
            (string) $request->ip()
        );

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    public function verifyOtp(Request $request, ForgotPasswordService $forgotPassword)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Enter the 6-digit verification code.',
            'otp.size' => 'Enter the 6-digit verification code.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $forgotPassword->verifyOtp(
            (string) $request->input('email'),
            (string) $request->input('otp'),
            (string) $request->ip()
        );

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    public function resetPassword(Request $request, ForgotPasswordService $forgotPassword)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $forgotPassword->resetPassword(
            (string) $request->input('email'),
            (string) $request->input('password'),
            (string) $request->input('password_confirmation')
        );

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    public function dashboard(Request $request, ForgotPasswordService $forgotPassword)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $forgotPassword->loginToDashboard((string) $request->input('email'));

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }
}
