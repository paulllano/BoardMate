<?php

namespace App\Http\Controllers;

use App\Models\Boarder;
use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Http\Requests\StoreBoarderRequest;
use App\Http\Requests\UpdateBoarderRequest;
use Illuminate\Http\Request;

class BoarderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $query = Boarder::with(['boardingHouse', 'contracts']);
        
        // If user is an admin, filter boarders by their boarding houses
        if ($user && $user instanceof Admin) {
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $query->whereIn('boarding_house_id', $adminBoardingHouseIds);
        }
        
        $boarders = $query->latest()->paginate(10);
        
        return response()->json($boarders);
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
    public function store(StoreBoarderRequest $request)
    {
        $user = $request->user();
        
        // If user is an admin, verify they own the boarding house
        if ($user && $user instanceof Admin) {
            $data = $request->validated();
            if (isset($data['boarding_house_id'])) {
                $boardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
                if ($boardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only create boarders for your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $boarder = Boarder::create($request->validated());
        $boarder->load('boardingHouse');
        
        return response()->json([
            'success' => true,
            'message' => 'Boarder created successfully.',
            'data' => $boarder
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Boarder $boarder)
    {
        // Check authorization: if user is admin, ensure boarder belongs to their boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boarder->boardingHouse && $boarder->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view boarders in your own boarding houses.'
                ], 403);
            }
        }
        
        $boarder->load(['boardingHouse', 'contracts', 'reviews', 'payments']);
        
        return response()->json($boarder);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Boarder $boarder)
    {
        // Check authorization: if user is admin, ensure boarder belongs to their boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boarder->boardingHouse && $boarder->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit boarders in your own boarding houses.'
                ], 403);
            }
            
            // Only show admin's boarding houses
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
        } else {
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json([
            'boarder' => $boarder,
            'boarding_houses' => $boardingHouses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoarderRequest $request, Boarder $boarder)
    {
        // Check authorization: if user is admin, ensure boarder belongs to their boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($boarder->boardingHouse && $boarder->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update boarders in your own boarding houses.'
                ], 403);
            }
            
            // If boarding_house_id is being changed, verify new one belongs to admin
            $data = $request->validated();
            if (isset($data['boarding_house_id']) && $data['boarding_house_id'] != $boarder->boarding_house_id) {
                $newBoardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
                if ($newBoardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only move boarders to your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $boarder->update($request->validated());
        $boarder->load('boardingHouse');
        
        return response()->json([
            'success' => true,
            'message' => 'Boarder updated successfully.',
            'data' => $boarder
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boarder $boarder)
    {
        // Check authorization: if user is admin, ensure boarder belongs to their boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boarder->boardingHouse && $boarder->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete boarders from your own boarding houses.'
                ], 403);
            }
        }
        
        $boarder->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Boarder deleted successfully.'
        ]);
    }

    /**
     * Get all contracts for a boarder (API endpoint)
     */
    public function contracts($id)
    {
        $boarder = Boarder::with('contracts')->findOrFail($id);
        return response()->json($boarder->contracts);
    }
}
