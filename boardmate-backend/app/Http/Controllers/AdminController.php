<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        
        // If user is an admin, only show themselves
        if ($user && $user instanceof Admin) {
            $admins = Admin::with('boardingHouses')->where('id', $user->id)->paginate(10);
        } else {
            // For non-admin users (if any), show all admins
            $admins = Admin::with('boardingHouses')->paginate(10);
        }
        
        return response()->json($admins);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json(['message' => 'Use POST /api/admins to create an admin.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        
        $admin = Admin::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully.',
            'data' => $admin
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        // Check authorization: if user is admin, they can only view themselves
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($admin->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view your own profile.'
                ], 403);
            }
        }
        
        $admin->load('boardingHouses');
        
        return response()->json($admin);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        // Check authorization: if user is admin, they can only edit themselves
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($admin->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit your own profile.'
                ], 403);
            }
        }
        
        return response()->json(['admin' => $admin]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        // Check authorization: if user is admin, they can only update themselves
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($admin->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update your own profile.'
                ], 403);
            }
        }
        
        $data = $request->validated();
        
        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        
        $admin->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully.',
            'data' => $admin
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        // Check authorization: if user is admin, they can only delete themselves
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($admin->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete your own account.'
                ], 403);
            }
        }
        
        $admin->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully.'
        ]);
    }
}
