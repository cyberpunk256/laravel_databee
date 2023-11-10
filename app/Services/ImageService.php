<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

use App\Models\Image as ImageModel;

class ImageService
{

    public function create(UploadedFile $file, $type = "image") // type = 'avatar' or 'image'
    {
        $image = \Image::make($file);
        $filename = time().'.'. $file->getClientOriginalExtension();
        $upload_folder = config('values.image.folder');
        $file_public_path = "{$upload_folder}/{$filename}";

        if($type == 'avatar') {
            $avatar_size = config('values.image.avatar_size');
            $image->fit($avatar_size['width'], $avatar_size['height'], function ($constraint) {
                $constraint->upsize();
            });
        }
        $image->save($file_public_path);
        
        $attributes = [
            "name" => "{$filename}",
            "original_name" => $file->getClientOriginalName(),
            "mime" => $file->getClientMimeType(),
            "path" => "/{$file_public_path}",
            "collection" => $type,
        ];

        $model = ImageModel::create($attributes);

        return $model;
    }

    public function remove($image) {
        $file_path = public_path($image->path); // app_path("public/test.txt");
        if(File::exists($file_path)) File::delete($file_path);
        $image->delete();
    }
}
