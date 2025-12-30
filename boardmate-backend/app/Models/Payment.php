<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['contract_id', 'boarder_id', 'amount', 'original_amount', 'credit_applied', 'payment_date', 'method', 'payment_method', 'status', 'reference_number', 'notes', 'payment_type', 'deleted_by_admin', 'application_id', 'is_advance_payment', 'used_as_credit'];

    protected $casts = [
        'payment_date' => 'date',
        'deleted_by_admin' => 'boolean',
        'is_advance_payment' => 'boolean',
        'used_as_credit' => 'boolean',
    ];

    public function contract() {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function boarder() {
        return $this->belongsTo(Boarder::class, 'boarder_id');
    }

    public function application() {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function creditTransactions() {
        return $this->hasMany(CreditTransaction::class, 'payment_id');
    }

    /**
     * Get the credit applied for this payment
     */
    public function getCreditAppliedAttribute($value)
    {
        // If credit_applied is stored, use it; otherwise calculate from credit_transactions
        if ($value !== null && $value > 0) {
            return $value;
        }
        
        // Calculate from credit_transactions
        return $this->creditTransactions()
            ->where('credit_amount_used', '>', 0)
            ->sum('credit_amount_used');
    }

    /**
     * Get the original amount (before credit was applied)
     */
    public function getOriginalAmountAttribute($value)
    {
        // If original_amount is stored, use it; otherwise use amount + credit_applied
        if ($value !== null && $value > 0) {
            return $value;
        }
        
        // Calculate: original = amount + credit_applied
        return $this->amount + $this->credit_applied;
    }
}
