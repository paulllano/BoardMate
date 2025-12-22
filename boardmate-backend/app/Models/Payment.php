<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['contract_id', 'boarder_id', 'amount', 'payment_date', 'method', 'payment_method', 'status', 'reference_number', 'notes', 'payment_type', 'deleted_by_admin'];

    protected $casts = [
        'payment_date' => 'date',
        'deleted_by_admin' => 'boolean',
    ];

    public function contract() {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function boarder() {
        return $this->belongsTo(Boarder::class, 'boarder_id');
    }
}
