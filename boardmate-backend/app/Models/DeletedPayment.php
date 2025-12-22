<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedPayment extends Model
{
    use HasFactory;

    protected $table = 'deleted_payments';

    protected $fillable = [
        'original_payment_id',
        'contract_id',
        'boarding_house_id',
        'boarder_id',
        'amount',
        'payment_date',
        'method',
        'payment_method',
        'status',
        'reference_number',
        'notes',
        'payment_type',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'deleted_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id');
    }

    public function boarder()
    {
        return $this->belongsTo(Boarder::class, 'boarder_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }
}
