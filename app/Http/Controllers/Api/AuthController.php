<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\C_M_S;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    /*  public function ProfileUpdate(Request $request, $id)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required'
            ]);
            $user = User::find($id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile update failed',
                'error' => $th->getMessage()
            ], 400);
        }
    } */

    public function ProfileUpdate(Request $request, $id)
    {
        try {
            // Get the currently authenticated user
            $authenticatedUser = Auth::user();

            // Check if the authenticated user is trying to update their own profile
            if ($authenticatedUser->id !== (int) $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only update your own profile'
                ], 403);  // Forbidden
            }

            // Validate incoming data
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id
            ]);

            // Find the user to update
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);  // Not Found
            }

            // Update user details
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile update failed',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
