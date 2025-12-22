<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['boarding_house_id', 'boarder_id', 'comment', 'rating', 'is_anonymous'];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function boarder()
    {
        return $this->belongsTo(Boarder::class);
    }
}
