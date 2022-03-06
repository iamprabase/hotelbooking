<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelRoomDetail extends Model
{
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function pictures()
    {
        return $this->hasMany(FileManage::class)->where('upload_type', 'LIKE', 'hotel_rooms');
    }
}
