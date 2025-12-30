<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\DeletedContract;
use App\Models\DeletedPayment;
use App\Models\Payment;
use App\Models\Boarder;
use App\Models\BoardingHouse;
use App\Models\Admin;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $isBoarder = $user && !($user instanceof Admin);
        
        if ($isBoarder && $user) {
            // Filter contracts for the logged-in boarder
            $contracts = Contract::with(['boarder', 'boardingHouse'])
                ->where('boarder_id', $user->id)
                ->latest()
                ->paginate(10);
        } elseif ($user && $user instanceof Admin) {
            // Admin - filter contracts by their boarding houses
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $contracts = Contract::with(['boarder', 'boardingHouse'])
                ->whereIn('boarding_house_id', $adminBoardingHouseIds)
                ->latest()
                ->paginate(10);
        } else {
            // Unauthenticated - show all contracts (for public API if needed)
            $contracts = Contract::with(['boarder', 'boardingHouse'])
                ->latest()
                ->paginate(10);
        }
        
        return response()->json($contracts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = request()->user();
        
        // If user is an admin, only show their boarding houses and boarders assigned to them
        if ($user && $user instanceof Admin) {
            $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id');
            $boardingHouses = BoardingHouse::where('admin_id', $user->id)->get();
            $boarders = Boarder::whereIn('boarding_house_id', $adminBoardingHouseIds)->get();
        } else {
            $boarders = Boarder::all();
            $boardingHouses = BoardingHouse::all();
        }
        
        return response()->json([
            'boarders' => $boarders,
            'boarding_houses' => $boardingHouses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        $user = $request->user();
        
        // If user is an admin, verify they own the boarding house
        if ($user && $user instanceof Admin) {
            $data = $request->validated();
            $boardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
            if ($boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only create contracts for your own boarding houses.'
                ], 403);
            }
        }
        
        $data = $request->validated();
        
        // Check for approved application with advance payment for this boarder and boarding house
        $approvedApplication = \App\Models\Application::where('boarder_id', $data['boarder_id'])
            ->where('boarding_house_id', $data['boarding_house_id'])
            ->where('status', 'approved')
            ->whereNotNull('advance_payment_id')
            ->with('advancePayment')
            ->first();
        
        // Remove advance_payment_credit from data if present (shouldn't be in fillable anymore)
        unset($data['advance_payment_credit']);
        
        $contract = Contract::create($data);
        
        // Load contract with calculated credit (this calculates available credit dynamically)
        $contract->load(['boarder', 'boardingHouse']);
        
        // Get the actual available credit (after deducting what's already been used)
        $availableCredit = $contract->advance_payment_credit;
        
        $message = 'Contract created successfully.';
        if ($availableCredit > 0) {
            $message .= ' Advance payment credit of ₱' . number_format($availableCredit, 2) . ' is available for this contract.';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'contract' => $contract,
            'advance_payment_credit' => $availableCredit // Calculated dynamically (shows remaining balance)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contract = Contract::with(['boarder', 'boardingHouse', 'payments'])->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view contracts for your own boarding houses.'
                ], 403);
            }
        }
        
        return response()->json($contract);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit contracts for your own boarding houses.'
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
            'contract' => $contract,
            'boarders' => $boarders,
            'boarding_houses' => $boardingHouses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = $request->user();
        if ($user && $user instanceof Admin) {
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only update contracts for your own boarding houses.'
                ], 403);
            }
            
            // If boarding_house_id is being changed, verify new one belongs to admin
            $data = $request->validated();
            if (isset($data['boarding_house_id']) && $data['boarding_house_id'] != $contract->boarding_house_id) {
                $newBoardingHouse = BoardingHouse::findOrFail($data['boarding_house_id']);
                if ($newBoardingHouse->admin_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. You can only move contracts to your own boarding houses.'
                    ], 403);
                }
            }
        }
        
        $contract->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully.',
            'contract' => $contract->load(['boarder', 'boardingHouse', 'payments'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Archives the contract instead of permanently deleting it.
     */
    public function destroy(Contract $contract)
    {
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete contracts for your own boarding houses.'
                ], 403);
            }
        }
        
        try {
            // Handle refund of unused advance payment credit
            $refundMessage = '';
            $refundAmount = 0;
            $advancePayment = $contract->getAdvancePayment();
            
            if ($advancePayment) {
                // Calculate unused credit
                $availableCredit = $contract->advance_payment_credit;
                
                if ($availableCredit > 0) {
                    $refundAmount = $availableCredit;
                    
                    // Mark advance payment as refunded
                    $advancePayment->update([
                        'status' => 'refunded'
                    ]);
                    
                    // Create a refund transaction record
                    \App\Models\CreditTransaction::create([
                        'contract_id' => $contract->id,
                        'payment_id' => null,
                        'advance_payment_id' => $advancePayment->id,
                        'credit_amount_used' => -$refundAmount, // Negative to indicate refund
                        'used_at' => now(),
                        'notes' => 'Refunded unused credit due to contract cancellation'
                    ]);
                    
                    $refundMessage = ' Unused advance payment credit of ₱' . number_format($refundAmount, 2) . ' has been automatically refunded.';
                }
            }
            
            // First, archive all related payments before deleting the contract
            $payments = Payment::where('contract_id', $contract->id)->get();
            foreach ($payments as $payment) {
                DeletedPayment::create([
                    'original_payment_id' => $payment->id,
                    'contract_id' => $payment->contract_id,
                    'boarding_house_id' => $contract->boarding_house_id, // Store directly for filtering
                    'boarder_id' => $payment->boarder_id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'method' => $payment->method,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'reference_number' => $payment->reference_number,
                    'notes' => $payment->notes,
                    'payment_type' => $payment->payment_type,
                    'deleted_by' => ($user && $user instanceof Admin) ? $user->id : null,
                    'deleted_at' => now(),
                ]);
            }
            
            // Archive the contract to deleted_contracts table
            DeletedContract::create([
                'original_contract_id' => $contract->id,
                'boarder_id' => $contract->boarder_id,
                'boarding_house_id' => $contract->boarding_house_id,
                'start_date' => $contract->start_date,
                'end_date' => $contract->end_date,
                'status' => $contract->status,
                'rent_amount' => $contract->rent_amount,
                'deleted_by' => ($user && $user instanceof Admin) ? $user->id : null,
                'deleted_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to archive contract: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive contract: ' . $e->getMessage()
            ], 500);
        }
        
        // Now delete the original contract (this will cascade delete payments, but we've already archived them)
        $contract->delete();
        
        $message = 'Contract deleted successfully.' . $refundMessage;
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'refunded' => $refundAmount > 0,
            'refund_amount' => $refundAmount
        ]);
    }

    /**
     * Get all payments for a contract (API endpoint)
     */
    public function payments($id)
    {
        $contract = Contract::with('payments')->findOrFail($id);
        
        // Check authorization: if user is admin, ensure they own the boarding house
        $user = request()->user();
        if ($user && $user instanceof Admin) {
            if ($contract->boardingHouse->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view payments for contracts in your own boarding houses.'
                ], 403);
            }
        }
        
        return response()->json($contract->payments);
    }

    /**
     * Get all deleted contracts (admin only)
     */
    public function deleted()
    {
        $user = request()->user();
        
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can view deleted contracts.'
            ], 403);
        }

        // Filter deleted contracts by admin's boarding houses
        $adminBoardingHouseIds = BoardingHouse::where('admin_id', $user->id)->pluck('id')->toArray();
        
        // If admin has no boarding houses, return empty array
        if (empty($adminBoardingHouseIds)) {
            return response()->json([]);
        }
        
        $deletedContracts = DeletedContract::with([
            'boarder:id,name,email',
            'boardingHouse:id,name,address',
            'deletedBy:id,name,email'
        ])
            ->whereIn('boarding_house_id', $adminBoardingHouseIds)
            ->latest('deleted_at')
            ->get();
        
        // Ensure relationships are properly loaded
        $deletedContracts->loadMissing(['boardingHouse', 'boarder', 'deletedBy']);
        
        return response()->json($deletedContracts);
    }

    /**
     * Restore a deleted contract
     */
    public function restore($id)
    {
        $user = request()->user();
        
        if (!$user || !($user instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can restore contracts.'
            ], 403);
        }

        $deletedContract = DeletedContract::findOrFail($id);
        
        // Check authorization: ensure admin owns the boarding house
        if ($deletedContract->boardingHouse && $deletedContract->boardingHouse->admin_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only restore contracts for your own boarding houses.'
            ], 403);
        }

        // Check if original contract still exists (shouldn't, but safety check)
        $existingContract = Contract::find($deletedContract->original_contract_id);
        if ($existingContract) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot restore: A contract with this ID already exists.'
            ], 400);
        }

        // Restore the contract with original ID
        $contract = new Contract([
            'boarder_id' => $deletedContract->boarder_id,
            'boarding_house_id' => $deletedContract->boarding_house_id,
            'start_date' => $deletedContract->start_date,
            'end_date' => $deletedContract->end_date,
            'status' => $deletedContract->status,
            'rent_amount' => $deletedContract->rent_amount,
        ]);
        $contract->id = $deletedContract->original_contract_id;
        $contract->save();

        // Restore all archived payments that belonged to this contract
        $deletedPayments = DeletedPayment::where('contract_id', $contract->id)->get();
        foreach ($deletedPayments as $deletedPayment) {
            // Check if payment already exists (shouldn't, but safety check)
            $existingPayment = Payment::find($deletedPayment->original_payment_id);
            if (!$existingPayment) {
                // Restore the payment with original ID
                $payment = new Payment([
                    'contract_id' => $deletedPayment->contract_id,
                    'boarder_id' => $deletedPayment->boarder_id,
                    'amount' => $deletedPayment->amount,
                    'payment_date' => $deletedPayment->payment_date,
                    'method' => $deletedPayment->method,
                    'payment_method' => $deletedPayment->payment_method,
                    'status' => $deletedPayment->status,
                    'reference_number' => $deletedPayment->reference_number,
                    'notes' => $deletedPayment->notes,
                    'payment_type' => $deletedPayment->payment_type,
                ]);
                $payment->id = $deletedPayment->original_payment_id;
                $payment->save();
            }
            // Delete from archived table
            $deletedPayment->delete();
        }

        // Delete from archived table
        $deletedContract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract restored successfully.',
            'contract' => $contract->load(['boarder', 'boardingHouse', 'payments'])
        ]);
    }
}
