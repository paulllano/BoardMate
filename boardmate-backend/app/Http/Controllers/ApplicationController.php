<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BoardingHouse;
use App\Models\Contract;
use App\Models\Admin;
use App\Models\Payment;
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
        $boardingHouse = BoardingHouse::findOrFail($request->boarding_house_id);
        
        // Manual validation to avoid exception handler issues
        $errors = [];
        
        // Validate boarding_house_id
        if (!$request->has('boarding_house_id') || !$request->boarding_house_id) {
            $errors['boarding_house_id'] = ['The boarding house ID is required.'];
        }
        
        // Validate policies_accepted
        $policiesAccepted = $request->input('policies_accepted');
        if (!$policiesAccepted || ($policiesAccepted !== true && $policiesAccepted !== 'true' && $policiesAccepted !== 1 && $policiesAccepted !== '1')) {
            $errors['policies_accepted'] = ['You must accept the policies and terms & conditions to submit your application.'];
        }
        
        // Validate message if provided
        if ($request->has('message') && strlen($request->message) > 1000) {
            $errors['message'] = ['The message must not exceed 1000 characters.'];
        }
        
        // If advance payment is required, validate payment fields
        $advancePaymentAmount = floatval($boardingHouse->advance_payment_amount ?? 0);
        if ($advancePaymentAmount > 0) {
            $paymentMethod = $request->input('advance_payment_method');
            if (!$paymentMethod || !in_array($paymentMethod, ['Cash', 'GCash'])) {
                $errors['advance_payment_method'] = ['The payment method is required and must be either Cash or GCash.'];
            }
            
            if ($paymentMethod === 'GCash') {
                $reference = $request->input('advance_payment_reference');
                if (!$reference || trim($reference) === '') {
                    $errors['advance_payment_reference'] = ['The reference number is required for GCash payments.'];
                }
            }
            
            if ($request->has('advance_payment_reference') && strlen($request->advance_payment_reference) > 255) {
                $errors['advance_payment_reference'] = ['The reference number must not exceed 255 characters.'];
            }
        }
        
        // Return validation errors if any
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $errors
            ], (int) 422);
        }

        // Get authenticated user (works with Sanctum)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], (int) 401);
        }
        
        // Check if user is a boarder
        if ($user instanceof \App\Models\Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Only boarders can apply for boarding houses.'
            ], (int) 403);
        }
        
        $boarder = $user;
        
        // Check if boarder is already assigned to this boarding house
        if ($boarder->boarding_house_id == $boardingHouse->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already assigned to this boarding house.'
            ], (int) 400);
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
            ], (int) 400);
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
            ], (int) 400);
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
            ], (int) 400);
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
                ], (int) 400);
            }
        }

        // Create advance payment record if required
        $advancePayment = null;
        $advancePaymentAmount = floatval($boardingHouse->advance_payment_amount ?? 0);
        if ($advancePaymentAmount > 0) {
            try {
                $advancePayment = Payment::create([
                    'contract_id' => null, // No contract yet for advance payments
                    'boarder_id' => $boarder->id,
                    'amount' => $advancePaymentAmount,
                    'payment_date' => now(),
                    'method' => $request->input('advance_payment_method'),
                    'payment_method' => $request->input('advance_payment_method'),
                    'status' => 'completed', // Paid upfront
                    'reference_number' => $request->input('advance_payment_reference'),
                    'payment_type' => null, // Not a rent payment
                    'is_advance_payment' => true,
                    'used_as_credit' => false,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error creating advance payment: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create advance payment record. Please try again.',
                    'error' => $e->getMessage()
                ], (int) 500);
            }
        }

        // Create application with policies acceptance
        try {
            $application = Application::create([
                'boarder_id' => $boarder->id,
                'boarding_house_id' => $boardingHouse->id,
                'message' => $request->input('message'),
                'status' => 'pending',
                'advance_payment_id' => $advancePayment ? $advancePayment->id : null,
                'policies_accepted' => true,
                'policies_accepted_at' => now(),
                'policies_text' => $boardingHouse->policies, // Store snapshot
            ]);

            // Link payment to application if created
            if ($advancePayment) {
                $advancePayment->update(['application_id' => $application->id]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating application: ' . $e->getMessage());
            // If advance payment was created, we should delete it or mark it as failed
            if ($advancePayment) {
                try {
                    $advancePayment->delete();
                } catch (\Exception $deleteException) {
                    \Log::error('Error deleting advance payment after application creation failed: ' . $deleteException->getMessage());
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to create application. Please try again.',
                'error' => $e->getMessage()
            ], (int) 500);
        }

        $responseMessage = 'Application submitted successfully!';
        if ($advancePaymentAmount > 0) {
            $responseMessage .= ' Advance payment of ₱' . number_format($advancePaymentAmount, 2) . ' has been recorded.';
        }
        if ($isTransfer && $currentBoardingHouse) {
            $responseMessage .= ' Note: You are currently assigned to ' . $currentBoardingHouse->name . '. If approved, you will be transferred.';
        }

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'application' => $application->load(['boarder', 'boardingHouse', 'advancePayment']),
            'is_transfer' => $isTransfer,
            'current_boarding_house' => $currentBoardingHouse ? [
                'id' => $currentBoardingHouse->id,
                'name' => $currentBoardingHouse->name
            ] : null
        ], (int) 201);
    }

    /**
     * Display the specified application
     */
    public function show($id)
    {
        $application = Application::with(['boarder', 'boardingHouse', 'reviewedBy', 'transferApprovedBy', 'advancePayment'])->findOrFail($id);
        
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

        // Note: Advance payment credit will be transferred to contract when contract is created
        // This is handled in ContractController when creating the contract

        $responseMessage = 'Application approved successfully! Boarder has been assigned to the boarding house.';
        if ($application->advancePayment) {
            $responseMessage .= ' Advance payment of ₱' . number_format($application->advancePayment->amount, 2) . ' will be available as credit when a contract is created.';
        }
        if ($isTransfer && $previousBoardingHouse) {
            $responseMessage = 'Application approved! Boarder has been transferred from ' . $previousBoardingHouse->name . ' to ' . $application->boardingHouse->name . '.';
            if ($application->advancePayment) {
                $responseMessage .= ' Advance payment credit will be available when contract is created.';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'application' => $application->load(['boarder', 'boardingHouse', 'reviewedBy', 'advancePayment']),
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

        // Auto-refund advance payment if exists
        $refundMessage = '';
        $refundAmount = null;
        $application->load('advancePayment');
        
        if ($application->advancePayment) {
            $advancePayment = $application->advancePayment;
            $refundAmount = $advancePayment->amount;
            $advancePayment->update([
                'status' => 'refunded'
            ]);
            $refundMessage = ' Advance payment of ₱' . number_format($refundAmount, 2) . ' has been automatically refunded.';
        }
        
        // Reload application to get updated relationships
        $application->refresh();
        $application->load(['boarder', 'boardingHouse', 'reviewedBy', 'advancePayment']);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected successfully!' . $refundMessage,
            'application' => $application,
            'refunded' => $refundAmount !== null,
            'refund_amount' => $refundAmount
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
