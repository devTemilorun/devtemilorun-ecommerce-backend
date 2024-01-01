<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\WelcomeMail;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

        public function register(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $verificationToken = Str::random(60);
                
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'customer',
                    'verification_token' => $verificationToken,
                    'email_verified_at' => null,
                ]);
                
                $this->sendVerificationEmail($user);
                
                return response()->json([
                    'message' => 'Registration successful. Please verify your email.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'requires_verification' => true,
                ], 201);
            }catch (\Exception $e) {
                Log::error('Registration failed: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'message' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }

        }


    protected function sendVerificationEmail($user)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $verificationUrl = $frontendUrl . '/verify-email?token=' . $user->verification_token . '&email=' . urlencode($user->email);

        Log::info('Queuing verification email', [
            'email' => $user->email,
            'url' => $verificationUrl,
        ]);

        try {
            Mail::to($user->email)->queue(new VerifyEmailMail($user, $verificationUrl));
            Log::info('Verification email queued for: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue verification email: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyEmail(Request $request)
    {
        Log::info('Verify email request received', [
            'email' => $request->email,
            'token' => $request->token,
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Verification validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
                'verified' => true,
            ]);
        }

        $storedToken = $user->getRawOriginal('verification_token');

        Log::info('Token comparison', [
            'stored'   => $storedToken,
            'provided' => $request->token,
            'match'    => $storedToken === $request->token,
        ]);

        if ($storedToken !== $request->token) {
            Log::error('Token mismatch');
            return response()->json(['message' => 'Invalid verification token'], 400);
        }

        $user->email_verified_at = Carbon::now();
        $user->verification_token = null;
        $user->save();

        Log::info('Email verified successfully', ['user_id' => $user->id]);

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'verified' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->verification_token = Str::random(60);
        $user->save();

        $this->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Verification email resent successfully',
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email before logging in',
                'requires_verification' => true,
                'email' => $user->email,
            ], 403);
        }

        $user->last_login_at = Carbon::now();
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'expires_in' => 86400,
        ]);
    }

    public function checkAuth(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => $user,
        ]);
    }
}