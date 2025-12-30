<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;


    protected $fillable = [
        'boarder_id', 'boarding_house_id', 'start_date', 'end_date', 'status', 'rent_amount'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function boarder()
    {
        return $this->belongsTo(Boarder::class, 'boarder_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'contract_id');
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class, 'contract_id');
    }

    /**
     * Calculate available advance payment credit dynamically
     * This is the total advance payment amount minus any credit already used
     */
    public function getAdvancePaymentCreditAttribute()
    {
        // Find approved application for this contract's boarder and boarding house
        $approvedApplication = \App\Models\Application::where('boarder_id', $this->boarder_id)
            ->where('boarding_house_id', $this->boarding_house_id)
            ->where('status', 'approved')
            ->whereNotNull('advance_payment_id')
            ->first();

        if (!$approvedApplication || !$approvedApplication->advance_payment_id) {
            return 0;
        }

        // Get the advance payment
        $advancePayment = Payment::find($approvedApplication->advance_payment_id);
        
        if (!$advancePayment || $advancePayment->status !== 'completed') {
            return 0;
        }

        // Calculate total credit used across ALL contracts for this advance payment
        // Credit is a balance tied to the advance payment, not to a specific contract
        $totalCreditUsed = \App\Models\CreditTransaction::where('advance_payment_id', $advancePayment->id)
            ->where('credit_amount_used', '>', 0) // Only count positive amounts (exclude refunds)
            ->sum('credit_amount_used');

        // Available credit = advance payment amount - credit already used (globally)
        return max(0, $advancePayment->amount - $totalCreditUsed);
    }

    /**
     * Get the advance payment record for this contract
     */
    public function getAdvancePayment()
    {
        // Find approved application for this contract's boarder and boarding house
        $approvedApplication = \App\Models\Application::where('boarder_id', $this->boarder_id)
            ->where('boarding_house_id', $this->boarding_house_id)
            ->where('status', 'approved')
            ->whereNotNull('advance_payment_id')
            ->first();

        if (!$approvedApplication || !$approvedApplication->advance_payment_id) {
            return null;
        }

        return Payment::find($approvedApplication->advance_payment_id);
    }
}
