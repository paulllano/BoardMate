<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Models\Boarder;
use App\Http\Requests\StoreBoardingHouseRequest;
use App\Http\Requests\UpdateBoardingHouseRequest;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class BoardingHouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = BoardingHouse::with('admin')->withCount('boarders');
        
        // If user is an admin, filter by their admin_id
        if ($user && $user instanceof Admin) {
            // Filter by admin's boarding houses only
            $query->where('admin_id', $user->id);
        } elseif (!$user) {
            // If no user is authenticated, this might be a public endpoint
            // For now, we'll show all (but in production, you might want to restrict this)
        }
        
        // Search functionality for browse page
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $boardingHouses = $query->latest()->paginate($request->get('per_page', 9))->withQueryString();
        
        return response()->json($boardingHouses);
    }

    public function browse(Request $request)
    {
        $query = BoardingHouse::query();
        
        // Try to get authenticated user (optional authentication)
        $user = null;
        $token = $request->bearerToken();
        
        if ($token) {
            // Check if token exists and is valid
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                // Get the tokenable (user) model
                $tokenable = $accessToken->tokenable;
                if ($tokenable instanceof Boarder || $tokenable instanceof Admin) {
                    $user = $tokenable;
                }
            }
        }
        
        // Gender-based filtering: Only show boarding houses that match boarder's gender
        if ($user && !($user instanceof Admin)) {
            // User is a boarder - filter by gender preference
            // Get gender directly from the user model
            $boarderGender = $user->gender;
            
            if ($boarderGender && in_array($boarderGender, ['male', 'female', 'other'])) {
                // Strict filtering: Show only houses that accept this gender OR everyone
                // Male boarders: see 'male' or 'everyone' houses (NOT 'female')
                // Female boarders: see 'female' or 'everyone' houses (NOT 'male')
                // Other gender: see 'everyone' houses only (since no house has 'other' preference)
                if ($boarderGender === 'other') {
                    // For 'other' gender, only show 'everyone' houses
                    $query->where('gender_preference', 'everyone');
                } else {
                    // For 'male' or 'female', show matching gender or 'everyone'
                    // This ensures male boarders don't see female-only houses and vice versa
                    $query->where(function($q) use ($boarderGender) {
                        $q->where('gender_preference', 'everyone')
                          ->orWhere('gender_preference', $boarderGender);
                    });
                }
            }
            // If boarder has no gender set or invalid gender, show all houses (fallback)
        }
        // If no user or admin, show all houses (no gender filtering)
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $boardingHouses = $query->latest()->paginate(9)->withQueryString();
        
        return response()->json($boardingHouses);
    }

    /**
     * Get boarders for a specific boarding house (API endpoint)
     */
    public function boarders($id)
    {
        $boardingHouse = BoardingHouse::with('boarders')->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own this boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view boarders for your own boarding houses.'
                ], 403);
            }
        }
        
        return response()->json($boardingHouse->boarders);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions(Request $request)
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $suggestions = BoardingHouse::where('name', 'like', '%' . $search . '%')
            ->orWhere('address', 'like', '%' . $search . '%')
            ->limit(5)
            ->get(['id', 'name', 'address'])
            ->map(function($house) {
                return [
                    'id' => $house->id,
                    'name' => $house->name,
                    'address' => $house->address,
                    'display' => $house->name . ' - ' . $house->address
                ];
            });
        
        return response()->json($suggestions);
    }

    /**
     * Display boarding house details for boarders (public view)
     */
    public function view(BoardingHouse $boardingHouse)
    {
        $boardingHouse->load(['admin', 'boarders', 'reviews', 'contracts', 'services']);
        
        // Explicitly return all attributes including advance_payment_amount and policies
        return response()->json([
            'id' => $boardingHouse->id,
            'name' => $boardingHouse->name,
            'address' => $boardingHouse->address,
            'description' => $boardingHouse->description,
            'admin_id' => $boardingHouse->admin_id,
            'gender_preference' => $boardingHouse->gender_preference,
            'advance_payment_amount' => $boardingHouse->advance_payment_amount,
            'policies' => $boardingHouse->policies,
            'admin' => $boardingHouse->admin,
            'boarders' => $boardingHouse->boarders,
            'reviews' => $boardingHouse->reviews,
            'contracts' => $boardingHouse->contracts,
            'services' => $boardingHouse->services,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // For admins, they can only create boarding houses for themselves
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            return response()->json([
                'admin' => $user,
                'message' => 'Boarding house will be assigned to your account.'
            ]);
        }
        
        // For non-admin users (if any), show all admins
        $admins = Admin::all();
        return response()->json(['admins' => $admins]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardingHouseRequest $request)
    {
        $user = $request->user();
        
        // If user is an admin, automatically assign boarding house to them
        if ($user && $user instanceof Admin) {
            $data = $request->validated();
            $data['admin_id'] = $user->id;
            $boardingHouse = BoardingHouse::create($data);
        } else {
            $boardingHouse = BoardingHouse::create($request->validated());
        }
        
        $boardingHouse->load('admin');
        
        return response()->json([
            'success' => true,
            'message' => 'Boarding house created successfully.',
            'data' => $boardingHouse
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BoardingHouse $boardingHouse)
    {
        // Check authorization: if user is admin, ensure they own this boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view your own boarding houses.'
                ], 403);
            }
        }
        
        // Load relationships
        $boardingHouse->load(['admin', 'boarders', 'reviews', 'contracts', 'services']);
        
        // Refresh to ensure we have latest data
        $boardingHouse->refresh();
        
        // Explicitly return all attributes and relationships
        return response()->json([
            'id' => $boardingHouse->id,
            'name' => $boardingHouse->name,
            'address' => $boardingHouse->address,
            'description' => $boardingHouse->description,
            'admin_id' => $boardingHouse->admin_id,
            'gender_preference' => $boardingHouse->gender_preference,
            'advance_payment_amount' => $boardingHouse->advance_payment_amount,
            'policies' => $boardingHouse->policies,
            'admin' => $boardingHouse->admin,
            'boarders' => $boardingHouse->boarders,
            'reviews' => $boardingHouse->reviews,
            'contracts' => $boardingHouse->contracts,
            'services' => $boardingHouse->services,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoardingHouse $boardingHouse)
    {
        // Check authorization: if user is admin, ensure they own this boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit your own boarding houses.'
                ], 403);
            }
            
            // Only show current admin
            return response()->json([
                'boarding_house' => $boardingHouse,
                'admin' => $user
            ]);
        }
        
        // For non-admin users (if any), show all admins
        $admins = Admin::all();
        return response()->json([
            'boarding_house' => $boardingHouse,
            'admins' => $admins
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardingHouseRequest $request, BoardingHouse $boardingHouse)
    {
        // Check authorization: if user is admin, ensure they own this boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update your own boarding houses.'
                ], 403);
            }
            
            // Prevent admin from changing admin_id
            $data = $request->validated();
            unset($data['admin_id']); // Remove admin_id from update data
            $boardingHouse->update($data);
        } else {
            $boardingHouse->update($request->validated());
        }
        
        $boardingHouse->load('admin');
        
        return response()->json([
            'success' => true,
            'message' => 'Boarding house updated successfully.',
            'data' => $boardingHouse
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardingHouse $boardingHouse)
    {
        // Check authorization: if user is admin, ensure they own this boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete your own boarding houses.'
                ], 403);
            }
        }
        
        $boardingHouse->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Boarding house deleted successfully.'
        ]);
    }
}
