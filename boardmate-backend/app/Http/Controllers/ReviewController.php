<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Boarder;
use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $isBoarder = $user && !($user instanceof Admin);
        
        if ($isBoarder && $user) {
            // Filter reviews for the logged-in boarder
            $reviews = Review::with(['boarder', 'boardingHouse'])
                ->where('boarder_id', $user->id)
                ->latest()
                ->paginate(10);
        } elseif ($user && $user instanceof Admin) {
            // Admin - filter reviews by their boarding houses
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $reviews = Review::with(['boarder', 'boardingHouse'])
                ->whereIn('boarding_house_id', $adminBoardingHouseIds)
                ->latest()
                ->paginate(10);
        } else {
            // Unauthenticated - show all reviews (for public API if needed)
            $reviews = Review::with(['boarder', 'boardingHouse'])
                ->latest()
                ->paginate(10);
        }
        
        return response()->json($reviews);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = request()->user();
        $currentBoarder = null;
        
        // If user is a boarder, get their info
        if ($user && !($user instanceof Admin)) {
            $currentBoarder = $user;
        }
        
        // If user is an admin, only show their boarding houses
        if ($user && $user instanceof Admin) {
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
            $adminBoardingHouseIds = $boardingHouses->pluck('id');
            $boarders = Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
        } else {
            $boarders = Boarder::all();
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json([
            'boarders' => $boarders,
            'boarding_houses' => $boardingHouses,
            'current_boarder' => $currentBoarder
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        $data = $request->validated();
        
        // Get authenticated user (works with Sanctum)
        $user = $request->user();
        
        // If a boarder is logged in, automatically set the boarder_id
        if ($user && !($user instanceof \App\Models\Admin)) {
            $data['boarder_id'] = $user->id;
        }
        
        // Handle is_anonymous checkbox - check the actual value, not just if it exists
        // When checkbox is unchecked, it might not be in request, so default to false
        // When checked, it will be true or "1" or "on", so cast to bool
        $data['is_anonymous'] = (bool) $request->input('is_anonymous', false);
        
        $review = Review::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Review created successfully.',
            'review' => $review->load(['boarder', 'boardingHouse'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $review = Review::with(['boarder', 'boardingHouse'])->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($review->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view reviews for your own boarding houses.'
                ], 403);
            }
        }
        
        return response()->json($review);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($review->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit reviews for your own boarding houses.'
                ], 403);
            }
            
            // Only show admin's boarding houses and boarders
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
            $boarders = Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
        } else {
            $boarders = Boarder::all();
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json([
            'review' => $review,
            'boarders' => $boarders,
            'boarding_houses' => $boardingHouses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($review->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update reviews for your own boarding houses.'
                ], 403);
            }
            
            // If boarding_house_id is being changed, verify new one belongs to admin
            $data = $request->validated();
            if (isset($data['boarding_house_id']) && $data['boarding_house_id'] != $review->boarding_house_id) {
                $newBoardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
                if ($newBoardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only move reviews to your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $data = $request->validated();
        // Handle is_anonymous checkbox - check the actual value, not just if it exists
        // When checkbox is unchecked, it might not be in request, so default to false
        // When checked, it will be true or "1" or "on", so cast to bool
        $data['is_anonymous'] = (bool) $request->input('is_anonymous', false);
        
        $review->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'review' => $review->load(['boarder', 'boardingHouse'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($review->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete reviews for your own boarding houses.'
                ], 403);
            }
        }
        
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.'
        ]);
    }

    /**
     * Get reviews for a specific boarding house (API endpoint)
     */
    public function houseReviews($id)
    {
        $reviews = Review::with(['boarder', 'boardingHouse'])
            ->where('boarding_house_id', $id)
            ->get();
        return response()->json($reviews);
    }
}
