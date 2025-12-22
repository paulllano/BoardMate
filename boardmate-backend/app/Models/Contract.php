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
}
