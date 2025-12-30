<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Boarder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ============================================
    // API METHODS (For Nuxt.js Frontend)
    // ============================================

    /**
     * API: Admin Login - Returns token
     */
    public function apiAdminLogin(Request $request)
    {
        try {
            // Force JSON response for API
            $request->headers->set('Accept', 'application/json');
            
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            // Find admin by email
            $admin = Admin::where('email', $credentials['email'])->first();

            // Check if admin exists and password matches
            if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ], 401, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }

            // Create token for API authentication
            $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone ?? null,
                    'role' => $admin->role,
                    'type' => 'admin'
                ]
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Boarder Login - Returns token
     */
    public function apiBoarderLogin(Request $request)
    {
        try {
            // Force JSON response for API
            $request->headers->set('Accept', 'application/json');
            
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            // Find boarder by email
            $boarder = Boarder::where('email', $credentials['email'])->first();

            // Check if boarder exists and password matches
            if (!$boarder || !Hash::check($credentials['password'], $boarder->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ], 401, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }

            // Create token for API authentication
            $token = $boarder->createToken('boarder-token', ['boarder'])->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $boarder->id,
                    'name' => $boarder->name,
                    'email' => $boarder->email,
                    'phone' => $boarder->phone ?? null,
                    'gender' => $boarder->gender,
                    'type' => 'boarder'
                ]
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Admin Register - Returns token
     */
    public function apiAdminRegister(Request $request)
    {
        try {
            // Force JSON response for API
            $request->headers->set('Accept', 'application/json');
            
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
                'password' => ['required', 'min:6', 'confirmed'],
                'role' => ['required', 'in:staff,owner'],
                'phone' => ['nullable', 'string', 'max:50'],
            ]);

            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone ?? null,
                    'role' => $admin->role,
                    'type' => 'admin'
                ]
            ], 201, [], JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Boarder Register - Returns token
     */
    public function apiBoarderRegister(Request $request)
    {
        try {
            // Force JSON response for API
            $request->headers->set('Accept', 'application/json');
            
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:boarders,email'],
                'password' => ['required', 'min:6', 'confirmed'],
                'phone' => ['nullable', 'string', 'max:50'],
                'date_of_birth' => ['nullable', 'date'],
                'address' => ['nullable', 'string'],
                'gender' => ['required', 'in:male,female,other'],
            ]);

            $boarder = Boarder::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'gender' => $data['gender'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $boarder->createToken('boarder-token', ['boarder'])->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $boarder->id,
                    'name' => $boarder->name,
                    'email' => $boarder->email,
                    'phone' => $boarder->phone ?? null,
                    'gender' => $boarder->gender,
                    'type' => 'boarder'
                ]
            ], 201, [], JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Logout - Revokes token
     */
    public function apiLogout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * API: Update profile for current authenticated user
     */
    public function apiUpdateProfile(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        // Determine if user is admin or boarder
        $isAdmin = $user instanceof Admin;
        
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:' . ($isAdmin ? 'admins' : 'boarders') . ',email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
        ];
        
        $data = $request->validate($rules);
        
        // Update user fields
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        // Update phone if the model has it
        if (isset($data['phone']) && array_key_exists('phone', $user->getAttributes())) {
            $user->phone = $data['phone'];
        }
        
        // Update password if provided
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        
        $user->save();
        
        // Return updated user data
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'type' => $isAdmin ? 'admin' : 'boarder',
                'role' => $isAdmin ? $user->role : null,
            ]
        ]);
    }

    /**
     * API: Get current authenticated user
     */
    public function apiUser(Request $request)
    {
        // Check both guards since Sanctum with multiple guards needs explicit checking
        $admin = null;
        $boarder = null;
        
        // Try to get user from token - Sanctum should handle this
        $user = $request->user();
        
        // If no user from token, check guards directly
        if (!$user) {
            // Check admin guard
            if (Auth::guard('admin')->check()) {
                $admin = Auth::guard('admin')->user();
            }
            // Check boarder guard
            if (Auth::guard('boarder')->check()) {
                $boarder = Auth::guard('boarder')->user();
            }
        } else {
            // User from token - determine which type
            if ($user instanceof Admin) {
                $admin = $user;
            } else {
                $boarder = $user;
            }
        }
        
        if (!$admin && !$boarder) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $userData = [];
        
        if ($admin) {
            $userData = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone ?? null,
                'role' => $admin->role,
                'type' => 'admin'
            ];
        } else {
            $userData = [
                'id' => $boarder->id,
                'name' => $boarder->name,
                'email' => $boarder->email,
                'phone' => $boarder->phone ?? null,
                'gender' => $boarder->gender,
                'type' => 'boarder'
            ];
        }

        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }
}


