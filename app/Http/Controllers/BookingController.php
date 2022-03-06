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

    public function bookHotel(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'hotel_id' => 'required|integer|min:0',
            'hotel_room_id' => 'required|integer|min:0',
            'checkin_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'checkout_date' => 'required|date|date_format:Y-m-d|after_or_equal:checkin_date',
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => 'required|date_format:H:i',
            'num_of_stay_days' => 'required|integer|min:1',
            'num_of_rooms' => 'required|integer|min:1',
            'advance_paid' => 'sometimes|regex:/^\d+(\.\d{10,2})?$/',
            'total_amount' => 'required|regex:/^\d+(\.\d{10,2})?$/',
            'checked_out' => 'sometimes|integer|min:0',
        ]);
        
        if ($validate->fails()) {
            return response([
                'message' => $validate->errors(),
            ], 422);
        }

        $customer_booking_data = $request->only('hotel_id', 'hotel_room_id', 'num_of_stay_days', 'num_of_rooms', 'advance_paid', 'total_amount', 'checked_out');
        $customer_booking_data['user_id'] = Auth::user()->id;
        $customer_booking_data['checkin_datetime'] = $request->checkin_date . ' ' . $request->checkin_time; 
        $customer_booking_data['checkout_datetime'] = $request->checkout_date . ' ' . $request->checkout_time;
        
        DB::beginTransaction();
        try{
            $customer_booking = CustomerRoomDetail::create($customer_booking_data);
            BookingHistory::create([
                'customer_room_detail_id' => $customer_booking->id,
                'booking_datetime' => date('Y-m-d H:i:s')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Booking confirmed.',
            ]);
        } catch(\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "Some error occured. Your Request cannot be processed.",
            ], 400);
        }
    }

    public function cancelBooking(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'booking_id' => 'required|integer|min:0',
        ]);
        
        if ($validate->fails()) {
            return response([
                'message' => $validate->errors(),
            ], 422);
        }
        $has_booking =  CustomerRoomDetail::with('bookinghistories')->where('user_id', Auth::user()->id)->whereStatus('Active')->whereId($request->booking_id)->first();
        DB::beginTransaction();
        if($has_booking) {
            try{
                CustomerRoomDetail::where('user_id', Auth::user()->id)->whereStatus('Active')->whereId($request->booking_id)->update([
                    'status' => 'Inactive'
                ]);
    
                BookingHistory::whereCustomerRoomDetailId($request->booking_id)->update([
                    'has_cancelled' => 1,
                    'booking_cancellation_datetime' => date('Y-m-d H:i:s')
                ]);
                DB::commit();

            } catch(\Exception $e) {
                DB::rollBack();

                return response()->json([
                    "message" => "Some error occured. Your Request cannot be processed.",
                ], 400);
            }
        }
        
        return response()->json([
            "message" => "No Active Booking Found.",
        ], 404);
    }

    public function updateBooking(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'booking_id' => 'required|integer|min:0',
            'hotel_id' => 'required|integer|min:0',
            'hotel_room_id' => 'required|integer|min:0',
            'checkin_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'checkout_date' => 'required|date|date_format:Y-m-d|after_or_equal:checkin_date',
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => 'required|date_format:H:i',
            'num_of_stay_days' => 'required|integer|min:1',
            'num_of_rooms' => 'required|integer|min:1',
            'advance_paid' => 'sometimes|regex:/^\d+(\.\d{10,2})?$/',
            'total_amount' => 'required|regex:/^\d+(\.\d{10,2})?$/',
            'checked_out' => 'sometimes|integer|min:0',
        ]);
        
        if ($validate->fails()) {
            return response([
                'message' => $validate->errors(),
            ], 422);
        }
        $customer_booking_data = $request->only('hotel_id', 'hotel_room_id', 'num_of_stay_days', 'num_of_rooms', 'advance_paid', 'total_amount', 'checked_out');
        $customer_booking_data['user_id'] = Auth::user()->id;
        $customer_booking_data['checkin_datetime'] = $request->checkin_date . ' ' . $request->checkin_time; 
        $customer_booking_data['checkout_datetime'] = $request->checkout_date . ' ' . $request->checkout_time;
        
        $has_booking =  CustomerRoomDetail::with('bookinghistories')->where('user_id', Auth::user()->id)->whereStatus('Active')->whereId($request->booking_id)->first();
        DB::beginTransaction();
        if($has_booking) {
            try{
                BookingHistory::whereCustomerRoomDetailId($request->booking_id)->update($customer_booking_data);
                
                return response()->json([
                    'message' => 'Exisitng Booking has been updated.',
                ]);
            } catch(\Exception $e) {
                DB::rollBack();

                return response()->json([
                    "message" => "Some error occured. Your Request cannot be processed.",
                ], 400);
            }
        }
        
        return response()->json([
            "message" => "No Active Booking Found.",
        ], 404);
    }

    public function getBookingInfo(Request $request)
    {
        $query = CustomerRoomDetail::with('bookinghistories')->where('user_id', Auth::user()->id);

        if($request->has('history')) {
            $query = $query->whereStatus('Active');
        }
        $booking_info = $query->get();
        
        return response()->json([
            'booking_info' => $booking_info,
        ]);
    }
}
