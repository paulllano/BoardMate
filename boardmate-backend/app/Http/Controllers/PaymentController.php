<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\DeletedPayment;
use App\Models\Boarder;
use App\Models\Contract;
use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $isBoarder = $user && !($user instanceof Admin);
        
        if ($isBoarder && $user) {
            // Filter payments for the logged-in boarder
            $boarderId = $user->id;
            $payments = Payment::whereHas('contract', function($q) use ($boarderId) {
                    $q->where('boarder_id', $boarderId);
                })
                ->with(['boarder', 'contract'])
                ->latest()
                ->paginate(10);
            
            // Get boarder's contracts for the contract summary
            $contracts = Contract::where('boarder_id', $boarderId)
                ->where('status', 'Active')
                ->with(['boardingHouse'])
                ->get();
        } elseif ($user && $user instanceof Admin) {
            // Admin - filter payments by contracts in their boarding houses
            // Exclude payments marked as deleted_by_admin
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $payments = Payment::whereHas('contract', function($q) use ($adminBoardingHouseIds) {
                    $q->whereIn('boarding_house_id', $adminBoardingHouseIds);
                })
                ->where('deleted_by_admin', false) // Exclude payments deleted by admin
                ->with(['boarder', 'contract'])
                ->latest()
                ->paginate(10);
            $contracts = collect(); // Empty collection for admins
        } else {
            // Unauthenticated - show all payments (for public API if needed)
            $payments = Payment::with(['boarder', 'contract'])
                ->latest()
                ->paginate(10);
            $contracts = collect();
        }
        
        return response()->json($payments);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json([
                'boarders' => [],
                'contracts' => []
            ]);
        }
        
        // Check if user is a boarder
        if (!($user instanceof Admin)) {
            // For boarders, only show their own contracts
            $contracts = Contract::where('boarder_id', $user->id)
                ->whereIn('status', ['Active', 'Pending'])
                ->with(['boardingHouse'])
                ->get();
            $boarders = collect(); // Not needed for boarders
        } else {
            // For admins, only show contracts in their boarding houses
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $contracts = Contract::whereIn('boarding_house_id', $adminBoardingHouseIds)
                ->whereIn('status', ['Active', 'Pending'])
                ->with(['boarder', 'boardingHouse'])
                ->get();
            $boarders = Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
        }
        
        return response()->json([
            'boarders' => $boarders,
            'contracts' => $contracts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        
        // If boarder is making payment, automatically set boarder_id and status
        if ($user && !($user instanceof Admin)) {
            $data['boarder_id'] = $user->id;
            $data['status'] = 'pending'; // Boarder payments start as pending
        } elseif ($user && $user instanceof Admin) {
            // If admin is creating payment, verify they own the contract's boarding house
            $contract = Contract::findOrFail($data['contract_id']);
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only create payments for contracts in your own boarding houses.'
                ], 403);
            }
        }
        
        // Auto-generate reference number if not provided
        if (empty($data['reference_number']) && !empty($data['payment_method'])) {
            if ($data['payment_method'] === 'GCash') {
                // GCash format: GC + 10 digits (e.g., GC1234567890)
                $data['reference_number'] = 'GC' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            } else {
                // Cash format: CASH + date + random (e.g., CASH202501201234)
                $data['reference_number'] = 'CASH' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        }
        
        $payment = Payment::create($data);
        
        // Update contract status if payment is completed (admin-created payments)
        if ($payment->status === 'completed' && $payment->contract) {
            $contract = $payment->contract;
            
            // If contract is Pending and this is the first completed payment, activate it
            if ($contract->status === 'Pending') {
                $contract->update(['status' => 'Active']);
            }
            
            // Calculate total completed payments for this contract
            $totalPaid = Payment::where('contract_id', $contract->id)
                ->where('status', 'completed')
                ->sum('amount');
            
            // Check if full payment has been made
            if ($totalPaid >= $contract->rent_amount) {
                // Full payment completed - mark contract as Completed
                $contract->update(['status' => 'Completed']);
            } elseif ($contract->status === 'Pending') {
                // Still partial but activate contract if it was pending
                $contract->update(['status' => 'Active']);
            }
        }
        
        $message = ($user && !($user instanceof Admin)) ? 'Payment submitted successfully. It will be reviewed by the admin.' : 'Payment created successfully.';
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'payment' => $payment->load(['boarder', 'contract']),
            'contract_updated' => ($payment->status === 'completed' && $payment->contract) ? $payment->contract->status : null
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payment = Payment::with([
            'boarder',
            'contract.boardingHouse',
            'contract.boarder'
        ])->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own the contract's boarding house
        // Admins can view payments even if deleted_by_admin is true (for viewing deleted items)
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($payment->contract && $payment->contract->boardingHouse && $payment->contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view payments for contracts in your own boarding houses.'
                ], 403);
            }
        } elseif ($user && !($user instanceof Admin)) {
            // For boarders, ensure they can only view their own payments
            // Boarders can always see their payments, even if deleted_by_admin is true
            if ($payment->contract && $payment->contract->boarder_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view your own payments.'
                ], 403);
            }
        }
        
        return response()->json($payment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        // Check authorization: if user is admin, ensure they own the contract's boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($payment->contract && $payment->contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit payments for contracts in your own boarding houses.'
                ], 403);
            }
            
            // Only show admin's contracts and boarders
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $contracts = Contract::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
            $boarders = Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
        } else {
            $boarders = Boarder::all();
            $contracts = Contract::all();
        }
        
        return response()->json([
            'payment' => $payment,
            'boarders' => $boarders,
            'contracts' => $contracts
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        // Check authorization: if user is admin, ensure they own the contract's boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($payment->contract && $payment->contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update payments for contracts in your own boarding houses.'
                ], 403);
            }
            
            // If contract_id is being changed, verify new one belongs to admin
            $data = $request->validated();
            if (isset($data['contract_id']) && $data['contract_id'] != $payment->contract_id) {
                $newContract = Contract::findOrFail($data['contract_id']);
                if ($newContract->boardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only move payments to contracts in your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $payment->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'payment' => $payment->load(['boarder', 'contract'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * For admins: Sets deleted_by_admin flag (payment remains visible to boarders).
     * For boarders: Not allowed to delete payments.
     */
    public function destroy(Payment $payment)
    {
        $user = request()->user();
        
        // Only admins can delete payments
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can delete payments.'
            ], 403);
        }
        
        // Check authorization: ensure admin owns the contract's boarding house
        if ($payment->contract && $payment->contract->boardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only delete payments for contracts in your own boarding houses.'
            ], 403);
        }
        
        // Set deleted_by_admin flag instead of archiving
        // This keeps the payment visible to boarders but hidden from admin views
        $payment->update([
            'deleted_by_admin' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully. It will remain visible to the boarder.'
        ]);
    }

    /**
     * Approve a payment (admin only)
     */
    public function approve(Request $request, Payment $payment)
    {
        // Check authorization: if user is admin, ensure they own the contract's boarding house
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can approve payments.'
            ], 403);
        }
        
        if ($payment->contract && $payment->contract->boardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only approve payments for contracts in your own boarding houses.'
            ], 403);
        }
        
        // Check if payment is deleted by admin
        if ($payment->deleted_by_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve a payment that has been deleted. Please restore it first.'
            ], 400);
        }
        
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.'
            ], 400);
        }

        $payment->update([
            'status' => 'completed',
        ]);

        // Update contract status based on payment
        if ($payment->contract) {
            $contract = $payment->contract;
            
            // If contract is Pending and this is the first approved payment, activate it
            if ($contract->status === 'Pending') {
                $contract->update(['status' => 'Active']);
            }
            
            // Calculate total approved payments for this contract
            $totalPaid = Payment::where('contract_id', $contract->id)
                ->where('status', 'completed')
                ->sum('amount');
            
            // Check if full payment has been made
            if ($payment->payment_type === 'full' && $totalPaid >= $contract->rent_amount) {
                // Full payment completed - mark contract as Completed
                $contract->update(['status' => 'Completed']);
            } elseif ($payment->payment_type === 'full' && $totalPaid < $contract->rent_amount) {
                // Full payment type but amount is less than rent - keep Active
                // This handles cases where rent might be paid in installments
                if ($contract->status === 'Pending') {
                    $contract->update(['status' => 'Active']);
                }
            } elseif ($payment->payment_type === 'partial') {
                // Partial payment - check if total paid equals or exceeds rent
                if ($totalPaid >= $contract->rent_amount) {
                    $contract->update(['status' => 'Completed']);
                } else {
                    // Still partial - keep Active
                    if ($contract->status === 'Pending') {
                        $contract->update(['status' => 'Active']);
                    }
                }
            } else {
                // No payment type specified - check total paid
                if ($totalPaid >= $contract->rent_amount) {
                    $contract->update(['status' => 'Completed']);
                } elseif ($contract->status === 'Pending') {
                    $contract->update(['status' => 'Active']);
                }
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Payment approved successfully!',
            'payment' => $payment->load(['boarder', 'contract']),
            'contract_updated' => $payment->contract ? $payment->contract->status : null
        ]);
    }

    /**
     * Reject a payment (admin only)
     */
    public function reject(Request $request, Payment $payment)
    {
        // Check authorization: if user is admin, ensure they own the contract's boarding house
        $user = $request->user();
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can reject payments.'
            ], 403);
        }
        
        if ($payment->contract && $payment->contract->boardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only reject payments for contracts in your own boarding houses.'
            ], 403);
        }
        
        // Check if payment is deleted by admin
        if ($payment->deleted_by_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject a payment that has been deleted. Please restore it first.'
            ], 400);
        }
        
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.'
            ], 400);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status' => 'failed',
            'notes' => $request->rejection_reason ? ($payment->notes ? $payment->notes . "\n\nRejection reason: " . $request->rejection_reason : "Rejection reason: " . $request->rejection_reason) : $payment->notes,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully!',
            'payment' => $payment->load(['boarder', 'contract'])
        ]);
    }

    /**
     * Get all deleted payments (admin only)
     */
    public function deleted()
    {
        $user = request()->user();
        
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can view deleted payments.'
            ], 403);
        }

        // Filter deleted payments by admin's boarding houses
        $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id')->toArray();
        
        // If admin has no boarding houses, return empty array
        if (empty($adminBoardingHouseIds)) {
            return response()->json([]);
        }
        
        // Get payments deleted by admin (deleted_by_admin = true) from main payments table
        $deletedByAdminPayments = Payment::with([
            'boarder:id,name,email',
            'contract:id,boarding_house_id,boarder_id,status,rent_amount',
            'contract.boardingHouse:id,name,address'
        ])
            ->where('deleted_by_admin', true)
            ->whereHas('contract', function($query) use ($adminBoardingHouseIds) {
                $query->whereIn('boarding_house_id', $adminBoardingHouseIds);
            })
            ->latest('updated_at')
            ->get();
        
        // Also get archived payments (from deleted_payments table) - these are from deleted contracts
        // Use boarding_house_id directly since contract might be deleted
        $archivedPayments = DeletedPayment::with([
            'boarder:id,name,email',
            'boardingHouse:id,name,address',
            'contract:id,boarding_house_id,boarder_id,status,rent_amount',
            'deletedBy:id,name,email'
        ])
            ->whereIn('boarding_house_id', $adminBoardingHouseIds)
            ->latest('deleted_at')
            ->get();
        
        // Combine both types of deleted payments and convert to arrays
        $adminDeletedArray = $deletedByAdminPayments->map(function($payment) {
            return [
                'id' => $payment->id,
                'type' => 'admin_deleted',
                'original_payment_id' => $payment->id,
                'contract_id' => $payment->contract_id,
                'boarder' => $payment->boarder ? [
                    'id' => $payment->boarder->id,
                    'name' => $payment->boarder->name,
                    'email' => $payment->boarder->email,
                ] : null,
                'contract' => $payment->contract ? [
                    'id' => $payment->contract->id,
                    'boarding_house_id' => $payment->contract->boarding_house_id,
                    'boarder_id' => $payment->contract->boarder_id,
                    'status' => $payment->contract->status,
                    'rent_amount' => $payment->contract->rent_amount,
                ] : null,
                'amount' => (float) $payment->amount,
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                'status' => $payment->status,
                'deleted_at' => $payment->updated_at ? $payment->updated_at->toDateTimeString() : null,
            ];
        })->toArray();
        
        $archivedArray = $archivedPayments->map(function($payment) {
            return [
                'id' => $payment->id,
                'type' => 'archived',
                'original_payment_id' => $payment->original_payment_id,
                'contract_id' => $payment->contract_id,
                'boarder' => $payment->boarder ? [
                    'id' => $payment->boarder->id,
                    'name' => $payment->boarder->name,
                    'email' => $payment->boarder->email,
                ] : null,
                'boardingHouse' => $payment->boardingHouse ? [
                    'id' => $payment->boardingHouse->id,
                    'name' => $payment->boardingHouse->name,
                    'address' => $payment->boardingHouse->address,
                ] : null,
                'contract' => $payment->contract ? [
                    'id' => $payment->contract->id,
                    'boarding_house_id' => $payment->contract->boarding_house_id,
                    'boarder_id' => $payment->contract->boarder_id,
                    'status' => $payment->contract->status,
                    'rent_amount' => $payment->contract->rent_amount,
                ] : null,
                'amount' => (float) $payment->amount,
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                'status' => $payment->status,
                'deleted_at' => $payment->deleted_at ? $payment->deleted_at->toDateTimeString() : null,
            ];
        })->toArray();
        
        // Combine arrays
        $allDeletedPayments = array_merge($adminDeletedArray, $archivedArray);
        
        // Sort by deleted_at descending
        usort($allDeletedPayments, function($a, $b) {
            $dateA = $a['deleted_at'] ?? '';
            $dateB = $b['deleted_at'] ?? '';
            return strcmp($dateB, $dateA); // Descending order
        });
        
        return response()->json($allDeletedPayments);
    }

    /**
     * Restore a deleted payment
     * Handles both admin-deleted payments (deleted_by_admin flag) and archived payments
     */
    public function restore($id)
    {
        $user = request()->user();
        
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can restore payments.'
            ], 403);
        }

        // First, try to find as admin-deleted payment (in main payments table)
        $adminDeletedPayment = Payment::where('id', $id)
            ->where('deleted_by_admin', true)
            ->with(['contract.boardingHouse'])
            ->first();
        
        if ($adminDeletedPayment) {
            // Check authorization: ensure admin owns the contract's boarding house
            if ($adminDeletedPayment->contract && $adminDeletedPayment->contract->boardingHouse && $adminDeletedPayment->contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only restore payments for contracts in your own boarding houses.'
                ], 403);
            }

            // Restore by unsetting deleted_by_admin flag
            $adminDeletedPayment->update([
                'deleted_by_admin' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment restored successfully.',
                'payment' => $adminDeletedPayment->load(['boarder', 'contract'])
            ]);
        }

        // If not found, try to find as archived payment (in deleted_payments table)
        // Check both the deleted_payments table ID and original_payment_id
        $deletedPayment = DeletedPayment::with(['boardingHouse', 'contract'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('original_payment_id', $id);
            })
            ->first();
        
        if (!$deletedPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found in deleted records.'
            ], 404);
        }
        
        // Check authorization: ensure admin owns the boarding house
        // Use boarding_house_id directly since contract might be deleted
        if ($deletedPayment->boarding_house_id) {
            $boardingHouse = BoardingHouse::find($deletedPayment->boarding_house_id);
            if ($boardingHouse && $boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only restore payments for contracts in your own boarding houses.'
                ], 403);
            }
        } elseif ($deletedPayment->contract && $deletedPayment->contract->boardingHouse) {
            // Fallback to contract relationship if boarding_house_id is null
            if ($deletedPayment->contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only restore payments for contracts in your own boarding houses.'
                ], 403);
            }
        }

        // First, check if payment was already restored (might have been restored when contract was restored)
        $existingPayment = Payment::find($deletedPayment->original_payment_id);
        if ($existingPayment) {
            // Payment already exists, just remove from deleted_payments table
            $deletedPayment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Payment was already restored.',
                'payment' => $existingPayment->load(['boarder', 'contract'])
            ]);
        }

        // Check if contract exists - payments need a contract
        // If contract_id is null (due to cascade delete), try to find contract by boarding_house_id
        $contract = null;
        if ($deletedPayment->contract_id) {
            $contract = Contract::find($deletedPayment->contract_id);
        } elseif ($deletedPayment->boarding_house_id) {
            // Try to find the contract by boarding_house_id and boarder_id if available
            // This handles cases where contract_id became null due to cascade
            $contractQuery = Contract::where('boarding_house_id', $deletedPayment->boarding_house_id);
            if ($deletedPayment->boarder_id) {
                $contractQuery->where('boarder_id', $deletedPayment->boarder_id);
            }
            // Get the most recent active contract for this boarder and boarding house
            $contract = $contractQuery->where('status', '!=', 'Terminated')
                ->latest()
                ->first();
        }

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot restore: The associated contract does not exist. Please restore the contract first.'
            ], 400);
        }

        // Restore the payment with original ID
        // Use the found contract's ID (in case contract_id was null in deleted_payments)
        $payment = new Payment([
            'contract_id' => $contract->id,
            'boarder_id' => $deletedPayment->boarder_id,
            'amount' => $deletedPayment->amount,
            'payment_date' => $deletedPayment->payment_date,
            'method' => $deletedPayment->method,
            'payment_method' => $deletedPayment->payment_method,
            'status' => $deletedPayment->status,
            'reference_number' => $deletedPayment->reference_number,
            'notes' => $deletedPayment->notes,
            'payment_type' => $deletedPayment->payment_type,
            'deleted_by_admin' => false, // Ensure it's not marked as deleted
        ]);
        $payment->id = $deletedPayment->original_payment_id;
        $payment->save();

        // Delete from archived table
        $deletedPayment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment restored successfully.',
            'payment' => $payment->load(['boarder', 'contract'])
        ]);
    }
}
