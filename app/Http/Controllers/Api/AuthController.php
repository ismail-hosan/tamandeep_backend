<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetRequest;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Mail\OtpMail;
use App\Models\C_M_S;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    // public function test(Request $request)
    // {

    //     $cms = C_M_S::create([
    //         'type' => 'land_second',
    //         'first_desc' => json_encode($request->all()), // Encode all request data into JSON for the 'index' field
    //     ]);

    //     return response()->json(['message' => 'Data saved successfully', 'data' => $cms]);
    // }
    //register functions
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create a new user
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Successful response
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ]);
    }

    //login functions
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to find the user by email
        $user = User::where('email', $request->input('email'))->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Successful response
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ]);
    }


    //check finction
    public function check(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not authenticated',
            ], 401);
        }
    }


    public function checkOtp(OtpRequest $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        if (!$email || !$otp) {
            return response()->json([
                'status' => false,
                'errors' => 'Email or OTP not provided',
                'code' => 400,
            ], 400);
        }
        $user = User::whereEmail($email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'errors' => 'User not found',
                'code' => 404,
            ], 404);
        }
        if (strval($user->otp) === strval($otp)) {
            // $user->otp = null;
            // $user->save();

            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully',
                'code' => 200,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'errors' => 'Invalid OTP',
            'code' => 401,
        ], 401);
    }

    public function forgetPassword(ForgetRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email');
            $user = User::whereEmail($email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'code' => 404,
                ]);
            }

            // Generate OTP
            $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $user->otp = $otp;

            if (!$user->save()) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save OTP. Please try again.',
                ], 500);
            }
            $otp = $user->otp;

            // Send OTP Email
            try {
                Mail::to($user->email)->send(new OtpMail($otp));
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                    'otp' => $user->otp, // Remove this in production for security
                ], 200);
            } catch (\Exception $mailException) {
                dd('Mail Error: ' . $mailException->getMessage());
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send OTP. Please try again.',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('General Error: ' . $e->getMessage());
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::whereEmail($request->input('email'))->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'code' => 404,
                ], 404);
            }

            if (strval($request->otp) !== strval($user->otp)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP',
                    'code' => 401,
                ], 401);
            }

            $user->password = Hash::make($request->input('new_password'));
            $user->otp = null;
            $user->email_verified_at = now();
            $user->update();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
                'code' => 500,
            ], 500);
        }
    }

    //logout functions
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }


    //Account Delete functions
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Revoke all tokens before deleting the account
        $user->tokens()->delete();

        // Delete the user account
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Account deleted successfully'
        ]);
    }
}
