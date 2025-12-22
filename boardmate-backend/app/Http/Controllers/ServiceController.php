<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $query = Service::with('boardingHouse');
        
        // If user is an admin, filter services by their boarding houses
        if ($user && $user instanceof Admin) {
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $query->whereIn('boarding_house_id', $adminBoardingHouseIds);
        }
        
        $services = $query->latest()->paginate(10);
        
        return response()->json($services);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = request()->user();
        
        // If user is an admin, only show their boarding houses
        if ($user && $user instanceof Admin) {
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
        } else {
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json(['boarding_houses' => $boardingHouses]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        
        // If user is an admin, verify they own the boarding house
        if ($user && $user instanceof Admin) {
            $boardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only create services for your own boarding houses.'
                ], 403);
            }
        }
        
        // Set both name and service_name to the same value for database compatibility
        $data['service_name'] = $data['name'];
        // Keep 'name' field as well since both are required
        
        $service = Service::create($data);
        $service->load('boardingHouse');
        
        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => $service
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $service = Service::with('boardingHouse')->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($service->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view services for your own boarding houses.'
                ], 403);
            }
        }
        
        return response()->json($service);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($service->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit services for your own boarding houses.'
                ], 403);
            }
            
            // Only show admin's boarding houses
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
        } else {
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json([
            'service' => $service,
            'boarding_houses' => $boardingHouses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($service->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update services for your own boarding houses.'
                ], 403);
            }
            
            // If boarding_house_id is being changed, verify new one belongs to admin
            $data = $request->validated();
            if (isset($data['boarding_house_id']) && $data['boarding_house_id'] != $service->boarding_house_id) {
                $newBoardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
                if ($newBoardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only move services to your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $data = $request->validated();
        // Set both name and service_name to the same value for database compatibility
        $data['service_name'] = $data['name'];
        // Keep 'name' field as well since both are required
        
        $service->update($data);
        $service->load('boardingHouse');
        
        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => $service
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($service->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete services for your own boarding houses.'
                ], 403);
            }
        }
        
        $service->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.'
        ]);
    }
}
