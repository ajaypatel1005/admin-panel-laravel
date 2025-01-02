<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ResponseHelper;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessTokenResult;

class AdminController extends Controller
{

    public function login(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:225|min:6',
            'password' => 'required',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all());
        }

        try {
            // Check if the admin exists and is active
            $admin = Admin::where('email', request('email'))->first();

            if (!$admin) {
                return ResponseHelper::errorResponse(['Account does not exist.']);
            }

            if ($admin->is_active != 1) {
                return ResponseHelper::errorResponse(['Your account is not activated. Please contact your administrator.']);
            }

            // Check if the password matches (without using attempt)
            if (password_verify(request('password'), $admin->password)) {
                // Create a Sanctum token for the authenticated admin
                $token = $admin->createToken('admin-api')->plainTextToken;

                // Return success response with the token
                $success['token'] = $token;
                return ResponseHelper::responseMessage('success', $success, "Admin login successfully.");
            } else {
                return ResponseHelper::errorResponse(['Invalid email or password.']);
            }
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong.' . $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return ResponseHelper::responseMessage('success', auth()->guard('admin-api')->user());
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the current authenticated admin's token
            $request->user()->tokens->each(function ($token) {
                $token->delete();
            });

            // Return success response
            return ResponseHelper::responseMessage('success', [], "Admin logged out successfully.");
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong. ' . $e->getMessage()]);
        }
    }

    public function updateProfile(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|max:225|min:6|unique:admins,email,' . auth()->guard('admin-api')->user()->id,
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        try {

            $user = Admin::find(auth()->guard('admin-api')->user()->id);
            if ($user) {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();

                return ResponseHelper::responseMessage('success', $user, "User updated successfully.");
            }

            return ResponseHelper::errorResponse(['User does not exist.'], 422);
        } catch (Exception $e) {

            Log::error('Error updating profile: ' . $e->getMessage());

            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }


    /**
     * For Change password ( isVerify )
     */
    public function changePassword(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8',  // You can add length constraints for password security
            'confirm_password' => 'required|same:new_password',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        try {
            // Get the authenticated user using the admin-api guard
            $user = auth('admin-api')->user();

            // Check if the user exists and is an instance of Admin
            if (!$user || !$user instanceof Admin) {
                return ResponseHelper::errorResponse(['User not found or not an Admin.'], 404);
            }

            // Check if the current password matches the one stored in the database
            if (!(Hash::check($request->get('current_password'), $user->password))) {
                return ResponseHelper::errorResponse(['Incorrect current password.'], 400);
            }

            // Ensure the new password is different from the current password
            if (strcmp($request->get('current_password'), $request->get('new_password')) == 0) {
                return ResponseHelper::errorResponse(['New password cannot be the same as your current password.'], 400);
            }

            // Check if new password and confirm password match
            if (strcmp($request->get('new_password'), $request->get('confirm_password')) != 0) {
                return ResponseHelper::errorResponse(['New password and confirm password must be the same.'], 400);
            }

            // Update the password
            $user->password = bcrypt($request->get('new_password'));
            $user->save();

            // Return success response
            return ResponseHelper::responseMessage('success', [], 'Password changed successfully.');
        } catch (Exception $e) {
            // Log the exception for debugging
            Log::error('Error changing password: ' . $e->getMessage());

            // Return generic error response
            return ResponseHelper::errorResponse(['Something went wrong.'], 500);
        }
    }
}
