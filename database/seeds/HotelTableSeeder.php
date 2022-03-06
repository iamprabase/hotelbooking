<?php

use Illuminate\Database\Seeder;

class HotelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hotel_1 = App\Hotel::create([
            'name' => 'The Everest Hotel',
            'abstract' => 'Discover the new design of the Everest Hotel, a 5 star Hotel in Nepal! Ideally situated, in the heart of Kathmandu, facing the cool mountains, the Everest hotel,named after the world’s highest peak Mt. is a fantastic combination of international standards with a Nepali feel.',
            'address' => 'Nepal, Kathmandu',
            'phone_number' => '1-4780100',
            'e_mail' => 'reservations@theeveresthotel.com',
            'description' => 'The 228 rooms and suites of the renovated the Everest Hotel are decorated and furnished in a contemporary style. With exceptional views of the lake, the hotel’s outdoor pool or Katmandu city, the rooms give an overall impression of luxury and refinement.The Everest Hotel is equipped with all the amenities that define luxurious living, including Spa, beauty centre and hairdresser, Fitness Centre open 24/24 (access is free for in-room guests). During summer time, enjoy the exclusive Pool Terrace overlooking the spectacular mountains surrounding the Kathmandu Valley! Hotel has positioned itself as a well-renowned international conference venue. The conference and meeting facilities meet the expectation of a high class meeting and offer exclusive services such as business centre, Free High speed Wireless Internet access in all public areas. Hotel offers an outside catering service should you wish to organize your event in an external location, in the town center or outside Kathmandu.',
            'facilities' => json_encode(['facilities', 'Room Service', 'Sauna', 'Telephone', 'Telephone']),
            'regular_checkin' => '15:00:00',
            'regular_checkout' => '11:00:00',
            'num_of_rooms' => 20,
            'tags' => json_encode(['Business', 'Casino', 'City', 'Family'])
        ]);
        $hotel_1_id = $hotel_1->id;
        $data = [[
            'hotel_id' => $hotel_1_id,
            'room_name' => 'Super Deluxe',
            'room_price' => 1500.85,
            'room_description' => 'Super Deluxe Room with Fridge',
            'available_num' => 5,
            'is_packed' => FALSE,
        ], [
            'hotel_id' => $hotel_1_id,
            'room_name' => 'Deluxe',
            'room_price' => 800.85,
            'room_description' => 'Deluxe Room with no A.C.',
            'available_num' => 15,
            'is_packed' => FALSE,
        ]];
        foreach($data as $d) {
            $rooms_1 = App\HotelRoomDetail::insert($d);
        }

        $hotel_2 = App\Hotel::create([
            'name' => 'Hyatt Regency',
            'abstract' => 'Discover the new design of the Everest Hotel, a 5 star Hotel in Nepal! Ideally situated, in the heart of Kathmandu, facing the cool mountains, the Everest hotel,named after the world’s highest peak Mt. is a fantastic combination of international standards with a Nepali feel.',
            'address' => 'Nepal, Kathmandu',
            'phone_number' => '1-4780100',
            'e_mail' => 'reservations@theeveresthotel.com',
            'description' => 'Developed by Taragaon Regency Hotels Limited, Hyatt Regency Kathmandu is a distinct mark of fine architecture and craftsmanship, the Hyatt Regecy is one of the finest Hotels in Kathmandu in Nepal, Hyatt Regency Kathmandu in Kathmandu is the perfect accommodation option for business travelers and holidaymakers alike. Ranking high among the 5 Star Hotels in Kathmandu, this posh hotel is equipped with the best possible amenities for the visitors. The convenient Location of Hyatt Regency Kathmandu in Kathmandu helps the tourists to visit the various tourist attractions in this capital city of Nepal with ease.Just 4 km away from Tribhuvan International Airport, the hotel is situated in close proximity to the business district and shopping centers. The Room Facilities at Hyatt Regency Kathmandu in Kathmandu promise a luxurious and comfortable stay for the visitors. There are 290 tastefully appointed rooms and plush suites at the hotel that are elegantly adorned with wooden flooring and Tibetan hand-woven carpeting.',
            'facilities' => json_encode(['facilities', 'Room Service', 'Sauna', 'Telephone', 'Telephone', 'Minibar']),
            'regular_checkin' => '12:00:00',
            'regular_checkout' => '10:00:00',
            'num_of_rooms' => 25,
            'tags' => json_encode(['Business', 'Casino', 'City', 'Family'])
        ]);
        $hotel_2_id = $hotel_2->id;
        $data_2 = [[
            'hotel_id' => $hotel_2_id,
            'room_name' => 'Super Deluxe',
            'room_price' => 1500.85,
            'room_description' => 'Super Deluxe Room with Fridge',
            'available_num' => 10,
            'is_packed' => FALSE,
        ], [
            'hotel_id' => $hotel_2_id,
            'room_name' => 'Deluxe',
            'room_price' => 800.85,
            'room_description' => 'Deluxe Room with no A.C.',
            'available_num' => 15,
            'is_packed' => FALSE,
        ]];
        foreach($data_2 as $d) {
            $rooms_1 = App\HotelRoomDetail::insert($d);
        }
    }
}
