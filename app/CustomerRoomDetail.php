<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerRoomDetail extends Model
{
    public $timestamps = false;
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function bookinghistories()
    {
        return $this->hasMany(BookingHistory::class);
    }
}
