<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public function hotel_rooms()
    {
        return $this->hasMany(HotelRoomDetail::class);
    }

    public function picture()
    {
        return $this->hasOne(FileManage::class)->where('upload_type', 'LIKE', 'hotel');
    }
}
