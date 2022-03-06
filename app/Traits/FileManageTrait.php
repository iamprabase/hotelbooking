<?php 
namespace App\Traits;

use App\FileManage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileManageTrait
{
    public function uploadOne(UploadedFile $uploadedFile, $id, $upload_type, $file_manage, $folder = 'images', $disk = 'public')
    {
        $user_id = $hotel_id = $hotel_room_id = NULL;
        $filename = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        if(is_null($filename)) {
            $filename = $name =  str_random(50);
        } else {
            $name = substr($filename, 0, 50) . '_' . time();
        }
        $path = 'uploads/' . $folder . '/';
        $file = $uploadedFile->storeAs($path, $name . '.' . $extension, $disk);
        if ($upload_type == 'user') {
            $user_id = $id;
        } elseif ($upload_type == 'hotel') {
            $hotel_id = $id;
        } elseif ($upload_type == 'hotel_rooms'){
            $hotel_room_id = $id;
        }

        if($file_manage->id) {
            $file_manage->update([
                'file_name' => $filename, 
                'file_path' => $path . $name . '.' . $extension,
                'file_ext' => $extension,
                'upload_type' => $upload_type
            ]);
        } else {
            $file = FileManage::create([
                'user_id' => $user_id,
                'hotel_id' => $hotel_id,
                'hotel_room_id' => $hotel_room_id, 
                'file_name' => $filename, 
                'file_path' => $path . $name . '.' . $extension,
                'file_ext' => $extension,
                'upload_type' => $upload_type
            ]);
        }


        return $file;
    }

    public function createPath($path)
    {
        $url = config('app.url');
        return config('app.url') . $path;
    }
}