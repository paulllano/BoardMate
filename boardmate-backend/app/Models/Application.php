<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'boarder_id',
        'boarding_house_id',
        'message',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'transfer_approved_by_previous_admin',
        'transfer_approved_at',
        'transfer_rejected_at',
        'advance_payment_id',
        'policies_accepted',
        'policies_accepted_at',
        'policies_text'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'transfer_approved_at' => 'datetime',
        'transfer_rejected_at' => 'datetime',
        'policies_accepted' => 'boolean',
        'policies_accepted_at' => 'datetime',
    ];

    public function boarder()
    {
        return $this->belongsTo(Boarder::class);
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function transferApprovedBy()
    {
        return $this->belongsTo(Admin::class, 'transfer_approved_by_previous_admin');
    }

    public function advancePayment()
    {
        return $this->belongsTo(Payment::class, 'advance_payment_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
