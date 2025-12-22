<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id', 
        'service_name', 
        'name', 
        'description', 
        'price', 
        'category', 
        'availability', 
        'is_recurring', 
        'notes'
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
