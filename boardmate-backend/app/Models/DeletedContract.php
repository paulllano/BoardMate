<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedContract extends Model
{
    use HasFactory;

    protected $table = 'deleted_contracts';

    protected $fillable = [
        'original_contract_id',
        'boarder_id',
        'boarding_house_id',
        'start_date',
        'end_date',
        'status',
        'rent_amount',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted_at' => 'datetime',
        'rent_amount' => 'decimal:2',
    ];

    public function boarder()
    {
        return $this->belongsTo(Boarder::class, 'boarder_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }
}
