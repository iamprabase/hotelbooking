<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingHistory extends Model
{
    public $timestamps = false;
    public function customer()
    {
        return $this->belongsTo(CustomerRoomDetail::class, 'customer_room_detail_id', 'id');
    }
}
