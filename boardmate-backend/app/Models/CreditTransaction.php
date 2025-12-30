<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $fillable = [
        'contract_id',
        'payment_id',
        'advance_payment_id',
        'credit_amount_used',
        'used_at',
        'notes'
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'credit_amount_used' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function advancePayment()
    {
        return $this->belongsTo(Payment::class, 'advance_payment_id');
    }
}
