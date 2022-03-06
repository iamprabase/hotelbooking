<?php

namespace App\Http\Controllers;

use App\Hotel;
use App\BookingHistory;
use App\CustomerRoomDetail;
use Illuminate\Http\Request;
use App\Traits\FileManageTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use FileManageTrait;

    private function formatResult($hotel) 
    {
        $hotel_room_details = [];
        foreach($hotel->hotel_rooms as $hotel_room)
        {
            $hotel_room_detail = [];
            $hotel_room_detail['hotel_room_id'] = $hotel_room->id;
            $hotel_room_detail['room_name'] = $hotel_room->room_name;
            $hotel_room_detail['room_price'] = $hotel_room->room_price;
            $hotel_room_detail['room_description'] = $hotel_room->room_description;
            $hotel_room_detail['total_rooms'] = $hotel_room->available_num;
            $hotel_room_detail['is_packed'] = $hotel_room->is_packed;
            $hotel_room_pictures = [];
            foreach($hotel_room->pictures as $room_picture)
            {
                $picture = [];
                $picture['path'] = $this->createPath(Storage::url($room_picture->file_path));
                array_push($hotel_room_pictures, $picture);
                unset($picture);
            }
            $hotel_room_detail['pictures'] = $hotel_room_pictures;
            array_push($hotel_room_details, $hotel_room_detail);
            unset($hotel_room_pictures);
            unset($hotel_room_detail);
        }
        $picture = $hotel->picture ? $this->createPath(Storage::url($hotel->picture->file_path)) : '';
        
        return [
          'hotel_id' => $hotel->id,
          'name' => $hotel->name,
          'abstract' => $hotel->abstract,
          'address' => $hotel->address,
          'phone_number' => $hotel->phone_number,
          'mail' => $hotel->e_mail,
          'description' => $hotel->description,
          'facilities' => json_decode($hotel->facilities),
          'regular_checkin' => date('h:i A', strtotime($hotel->regular_checkin)),
          'regular_checkout' => date('h:i A', strtotime($hotel->regular_checkout)),
          'num_of_rooms' => $hotel->num_of_rooms,
          'tags' => json_decode($hotel->tags),
          'hotel_picture' => $picture,
          'hotel_room_details' => $hotel_room_details,
        ];
    }

    public function getHotelListing(Request $request)
    {
        $offset = $request->has('offset') ? $request->get('offset') : 0;
        $limit = $request->has('limit') ? $request->get('limit') : 20;
        $pagination = $request->has('pagination') ? $request->get('pagination') : FALSE;
        $query = Hotel::with(['hotel_rooms' => function ($query) {
            $query->with('pictures');
        }], ['picture']);
        if($pagination) {
            $query->skip($offset)->take($limit);
        }
        $hotels = $query->get();
        $results = $hotels->map(function($hotel) {
            return $this->formatResult($hotel);
        });

        return response()->json([
            'hotels' => $results,
            'current_offset' => $offset,
            'current_limit' => $limit,
            'pagination' => $pagination
        ]);
    }

    public function getSingleHotelById(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'hotel_id' => 'required|integer',
        ]);
        
        if ($validate->fails()) {
            return response([
                'message' => $validate->errors(),
            ], 422);
        }
        
        $hotels = Hotel::with('hotel_rooms')->whereId($request->hotel_id)->first();
        
        return response()->json(
            $this->formatResult($hotels),
        );
    }
}
