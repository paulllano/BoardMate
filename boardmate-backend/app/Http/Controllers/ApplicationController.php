<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BoardingHouse;
use App\Models\Contract;
use App\Models\Admin;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * Display a listing of applications for admin
     */
    public function index(Request $request)
    {
        // Auto-cancel transfer requests older than 1 day
        $this->autoCancelOldTransferRequests();
        
        $query = Application::with(['boarder', 'boardingHouse', 'reviewedBy', 'transferApprovedBy']);
        
        // Get authenticated user
        $user = $request->user();
        
        // If user is an admin, show:
        // 1. Applications for their boarding houses (incoming applications)
        // 2. Transfer requests from boarders currently in their boarding houses (outgoing transfers)
        if ($user instanceof Admin) {
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            
            // Get boarders currently in admin's boarding houses
            $boarderIds = \App\Models\Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->pluck('id');
            
            // Applications for admin's boarding houses OR transfer requests from boarders in admin's houses
            $query->where(function($q) use ($adminBoardingHouseIds, $boarderIds) {
                // Incoming applications to admin's boarding houses
                $q->whereIn('boarding_house_id', $adminBoardingHouseIds)
                  // OR transfer requests from boarders currently in admin's boarding houses
                  ->orWhere(function($subQ) use ($boarderIds) {
                      $subQ->whereIn('boarder_id', $boarderIds)
                           ->whereNull('transfer_approved_at')
                           ->whereNull('transfer_rejected_at')
                           ->where('status', 'pending');
                  });
            });
        } elseif ($user && !($user instanceof Admin)) {
            // If user is a boarder, filter to only show their own applications
            $query->where('boarder_id', $user->id);
        }
        
        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }
        
        $applications = $query->latest()->paginate(10);
        
        return response()->json($applications);
    }

    /**
     * Auto-cancel transfer requests older than 1 day
     */
    private function autoCancelOldTransferRequests()
    {
        $oneDayAgo = now()->subDay();
        
        Application::where('status', 'pending')
            ->whereNull('transfer_approved_at')
            ->whereNull('transfer_rejected_at')
            ->whereHas('boarder', function($query) {
                $query->whereNotNull('boarding_house_id');
            })
            ->where('created_at', '<=', $oneDayAgo)
            ->each(function($application) {
                $application->update([
                    'status' => 'rejected',
                    'transfer_rejected_at' => now(),
                    'admin_notes' => 'Automatically cancelled: Transfer request not approved within 24 hours.',
                ]);
            });
    }

    /**
     * Show the form for creating a new application
     */
    public function create(BoardingHouse $boardingHouse)
    {
        return response()->json([
            'success' => false,
            'message' => 'Use POST /api/applications to create an application.',
            'boarding_house' => $boardingHouse
        ], 405);
    }

    /**
     * Store a newly created application
     */
    public function store(Request $request, $boardingHouse = null)
    {
        // Handle API requests (boarding_house_id in request body)
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'boarding_house_id' => 'required|exists:boarding_houses,id',
        ]);
        $boardingHouse = BoardingHouse::findOrFail($request->boarding_house_id);

        // Get authenticated user (works with Sanctum)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        // Check if user is a boarder
        if ($user instanceof \App\Models\Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Only boarders can apply for boarding houses.'
            ], 403);
        }
        
        $boarder = $user;
        
        // Check if boarder is already assigned to this boarding house
        if ($boarder->boarding_house_id == $boardingHouse->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already assigned to this boarding house.'
            ], 400);
        }
        
        // Check if boarder already has a pending application for this boarding house (check this first)
        $pendingApplication = Application::where('boarder_id', $boarder->id)
            ->where('boarding_house_id', $boardingHouse->id)
            ->where('status', 'pending')
            ->first();
        
        if ($pendingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending application for this boarding house.'
            ], 400);
        }
        
        // Check if boarder has an approved application for this boarding house
        // Only block if the boarder is currently assigned to this boarding house
        // (This allows re-applying to a boarding house they were previously assigned to but transferred away from)
        $approvedApplication = Application::where('boarder_id', $boarder->id)
            ->where('boarding_house_id', $boardingHouse->id)
            ->where('status', 'approved')
            ->first();
        
        // Only block if there's an approved application AND the boarder is currently assigned to this boarding house
        // (The check on line 87 already handles if they're currently assigned, so this is a safety check)
        if ($approvedApplication && $boarder->boarding_house_id == $boardingHouse->id) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an approved application for this boarding house.'
            ], 400);
        }
        
        // Check if boarder has an active contract for this boarding house
        $activeContract = Contract::where('boarder_id', $boarder->id)
            ->where('boarding_house_id', $boardingHouse->id)
            ->whereIn('status', ['Pending', 'Active'])
            ->first();
        
        if ($activeContract) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active contract for this boarding house.'
            ], 400);
        }

        // Check if boarder is already assigned to another boarding house (for transfer warning)
        $currentBoardingHouse = null;
        $isTransfer = false;
        if ($boarder->boarding_house_id) {
            $currentBoardingHouse = BoardingHouse::find($boarder->boarding_house_id);
            $isTransfer = true;
            
            // Check for outstanding balance before allowing transfer
            $activeContracts = Contract::where('boarder_id', $boarder->id)
                ->where('boarding_house_id', $boarder->boarding_house_id)
                ->whereIn('status', ['Pending', 'Active'])
                ->get();
            
            $outstandingContracts = [];
            foreach ($activeContracts as $contract) {
                // Calculate total approved payments for this contract
                $totalPaid = \App\Models\Payment::where('contract_id', $contract->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                $outstandingBalance = $contract->rent_amount - $totalPaid;
                
                if ($outstandingBalance > 0) {
                    $outstandingContracts[] = [
                        'contract_id' => $contract->id,
                        'rent_amount' => $contract->rent_amount,
                        'total_paid' => $totalPaid,
                        'outstanding_balance' => $outstandingBalance
                    ];
                }
            }
            
            if (!empty($outstandingContracts)) {
                $totalOutstanding = array_sum(array_column($outstandingContracts, 'outstanding_balance'));
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot transfer to a new boarding house while you have outstanding payments. Please clear your balance with ' . $currentBoardingHouse->name . ' first. Outstanding balance: ₱' . number_format($totalOutstanding, 2),
                    'outstanding_balance' => $totalOutstanding,
                    'current_boarding_house' => [
                        'id' => $currentBoardingHouse->id,
                        'name' => $currentBoardingHouse->name
                    ]
                ], 400);
            }
        }

        $application = Application::create([
            'boarder_id' => $boarder->id,
            'boarding_house_id' => $boardingHouse->id,
            'message' => $request->message,
            'status' => 'pending',
        ]);


        $responseMessage = 'Application submitted successfully!';
        if ($isTransfer && $currentBoardingHouse) {
            $responseMessage .= ' Note: You are currently assigned to ' . $currentBoardingHouse->name . '. If approved, you will be transferred.';
        }

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'application' => $application->load(['boarder', 'boardingHouse']),
            'is_transfer' => $isTransfer,
            'current_boarding_house' => $currentBoardingHouse ? [
                'id' => $currentBoardingHouse->id,
                'name' => $currentBoardingHouse->name
            ] : null
        ], 201);
    }

    /**
     * Display the specified application
     */
    public function show($id)
    {
        $application = Application::with(['boarder', 'boardingHouse', 'reviewedBy', 'transferApprovedBy'])->findOrFail($id);
        
        // Get authenticated user for authorization check
        $user = request()->user();
        
        // Load boarder's current boarding house for transfer information
        $boarder = $application->boarder;
        $currentBoardingHouse = null;
        $isTransfer = false;
        
        if ($boarder && $boarder->boarding_house_id && $boarder->boarding_house_id != $application->boarding_house_id) {
            $currentBoardingHouse = BoardingHouse::find($boarder->boarding_house_id);
            $isTransfer = true;
        }
        
        // If user is an admin, check authorization
        if ($user instanceof Admin) {
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id')->toArray();
            
            // Admin can view if:
            // 1. They own the boarding house the application is for (new boarding house)
            // 2. OR it's a transfer request and they own the boarder's current boarding house (previous boarding house)
            $canView = in_array($application->boarding_house_id, $adminBoardingHouseIds);
            
            if ($isTransfer && $currentBoardingHouse) {
                $canView = $canView || in_array($currentBoardingHouse->id, $adminBoardingHouseIds);
            }
            
            if (!$canView) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view applications for your boarding houses or transfer requests from your boarding houses.'
                ], 403);
            }
        } elseif ($user && !($user instanceof Admin)) {
            // If user is a boarder, check if this is their application
            if ($application->boarder_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view your own applications.'
                ], 403);
            }
        }
        
        // Load boarder relationship with boarding house
        $application->load(['boarder.boardingHouse']);
        
        return response()->json([
            ...$application->toArray(),
            'is_transfer' => $isTransfer,
            'current_boarding_house' => $currentBoardingHouse ? [
                'id' => $currentBoardingHouse->id,
                'name' => $currentBoardingHouse->name
            ] : null
        ]);
    }

    /**
     * Approve an application
     */
    public function approve(Request $request, Application $application)
    {
        // Check if application is already processed
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed.'
            ], 400);
        }

        // Get authenticated admin user
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can approve applications.'
            ], 403);
        }
        
        // Check if admin owns the boarding house for this application
        $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
        if (!in_array($application->boarding_house_id, $adminBoardingHouseIds->toArray())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only approve applications for your boarding houses.'
            ], 403);
        }

        // Check if boarder is already assigned to another boarding house (transfer scenario)
        $boarder = $application->boarder;
        $previousBoardingHouse = null;
        $isTransfer = false;
        
        if ($boarder->boarding_house_id && $boarder->boarding_house_id != $application->boarding_house_id) {
            $previousBoardingHouse = BoardingHouse::find($boarder->boarding_house_id);
            $isTransfer = true;
            
            // Check if transfer has been approved by previous admin
            if (!$application->transfer_approved_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This transfer request must be approved by the previous boarding house admin first.'
                ], 400);
            }
        }

        // Update application status
        $application->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // Assign boarder to the new boarding house
        $boarder->update([
            'boarding_house_id' => $application->boarding_house_id
        ]);


        $responseMessage = 'Application approved successfully! Boarder has been assigned to the boarding house.';
        if ($isTransfer && $previousBoardingHouse) {
            $responseMessage = 'Application approved! Boarder has been transferred from ' . $previousBoardingHouse->name . ' to ' . $application->boardingHouse->name . '.';
        }

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'application' => $application->load(['boarder', 'boardingHouse', 'reviewedBy']),
            'is_transfer' => $isTransfer,
            'previous_boarding_house' => $previousBoardingHouse ? [
                'id' => $previousBoardingHouse->id,
                'name' => $previousBoardingHouse->name
            ] : null
        ]);
    }

    /**
     * Reject an application
     */
    public function reject(Request $request, Application $application)
    {
        // Check if application is already processed
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed.'
            ], 400);
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        // Get authenticated admin user
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can reject applications.'
            ], 403);
        }
        
        // Check if admin owns the boarding house for this application
        $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
        if (!in_array($application->boarding_house_id, $adminBoardingHouseIds->toArray())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only reject applications for your boarding houses.'
            ], 403);
        }
        
        $application->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected successfully!',
            'application' => $application->load(['boarder', 'boardingHouse', 'reviewedBy'])
        ]);
    }

    /**
     * Approve transfer request (by previous boarding house admin)
     */
    public function approveTransfer(Request $request, Application $application)
    {
        // Check if application is already processed
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed.'
            ], 400);
        }

        // Get authenticated admin user
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can approve transfer requests.'
            ], 403);
        }

        // Get the boarder
        $boarder = $application->boarder;
        
        // Check if this is actually a transfer (boarder has a current boarding house)
        if (!$boarder->boarding_house_id || $boarder->boarding_house_id == $application->boarding_house_id) {
            return response()->json([
                'success' => false,
                'message' => 'This is not a transfer request.'
            ], 400);
        }

        // Check if admin owns the boarder's current boarding house (previous boarding house)
        $currentBoardingHouse = BoardingHouse::find($boarder->boarding_house_id);
        if (!$currentBoardingHouse || $currentBoardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only approve transfer requests for boarders currently in your boarding houses.'
            ], 403);
        }

        // Check if transfer is already approved or rejected
        if ($application->transfer_approved_at) {
            return response()->json([
                'success' => false,
                'message' => 'This transfer request has already been approved.'
            ], 400);
        }

        if ($application->transfer_rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'This transfer request has already been rejected.'
            ], 400);
        }

        // Approve the transfer
        $application->update([
            'transfer_approved_by_previous_admin' => $user->id,
            'transfer_approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfer request approved. The new boarding house admin can now review the application.',
            'application' => $application->load(['boarder', 'boardingHouse', 'transferApprovedBy'])
        ]);
    }

    /**
     * Reject transfer request (by previous boarding house admin)
     */
    public function rejectTransfer(Request $request, Application $application)
    {
        // Check if application is already processed
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed.'
            ], 400);
        }

        // Get authenticated admin user
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can reject transfer requests.'
            ], 403);
        }

        // Get the boarder
        $boarder = $application->boarder;
        
        // Check if this is actually a transfer
        if (!$boarder->boarding_house_id || $boarder->boarding_house_id == $application->boarding_house_id) {
            return response()->json([
                'success' => false,
                'message' => 'This is not a transfer request.'
            ], 400);
        }

        // Check if admin owns the boarder's current boarding house
        $currentBoardingHouse = BoardingHouse::find($boarder->boarding_house_id);
        if (!$currentBoardingHouse || $currentBoardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only reject transfer requests for boarders currently in your boarding houses.'
            ], 403);
        }

        // Check if transfer is already processed
        if ($application->transfer_approved_at || $application->transfer_rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'This transfer request has already been processed.'
            ], 400);
        }

        // Reject the transfer and cancel the application
        $adminNotes = $request->input('admin_notes', 'Transfer request rejected by previous boarding house admin.');
        
        $application->update([
            'transfer_rejected_at' => now(),
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfer request rejected. The application has been cancelled.',
            'application' => $application->load(['boarder', 'boardingHouse'])
        ]);
    }

    /**
     * Display applications for a specific boarding house
     */
    public function forBoardingHouse(BoardingHouse $boardingHouse)
    {
        $applications = $boardingHouse->applications()
            ->with(['boarder', 'reviewedBy', 'transferApprovedBy'])
            ->latest()
            ->paginate(10);
        
        return response()->json([
            'applications' => $applications,
            'boarding_house' => $boardingHouse
        ]);
    }
}
