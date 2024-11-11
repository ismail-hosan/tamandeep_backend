<?php

namespace App\Helper;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageHelper
{
    
    public static function handleImageUpload(UploadedFile $imageFile, ?string $existingImagePath, string $destinationPath): string
    {
        // Delete old image if a new one is uploaded and exists
        if ($existingImagePath && File::exists(public_path($existingImagePath))) {
            File::delete(public_path($existingImagePath));
        }

        // Generate a new unique image name
        $imageName = time() . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();
        $imagePath = $destinationPath . '/' . $imageName;

        // Move the uploaded file to the destination path
        $imageFile->move(public_path($destinationPath), $imageName);

        return $imagePath;
    }
}
