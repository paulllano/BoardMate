<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Boarder extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'boarding_house_id', 
        'name', 
        'email', 
        'phone', 
        'age', 
        'contact', 
        'date_of_birth', 
        'address',
        'password',
        'gender',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id');
    }

    // ✅ FIXED: Add this
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'boarder_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'boarder_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'boarder_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'boarder_id');
    }
}
