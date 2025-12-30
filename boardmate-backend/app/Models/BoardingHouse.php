<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouse extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'name', 'address', 'description', 'gender_preference', 'advance_payment_amount', 'policies'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // ✅ FIXED: plural relationship name
    public function boarders()
    {
        return $this->hasMany(Boarder::class, 'boarding_house_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'boarding_house_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'boarding_house_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'boarding_house_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'boarding_house_id');
    }
}
