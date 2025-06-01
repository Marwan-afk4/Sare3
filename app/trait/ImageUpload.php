<?php

namespace App\trait;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ImageUpload
{
    // This Trait Aboute Image

    public function upload_image(Request $request,$fileName = 'image',$directory){
        if($request->has($fileName)){// if Request has a Image
            $uploadImage = new request();
            $imagePath = $request->file($fileName)->store($directory,'public'); // Take Image from Request And Save inStorage;
            return $imagePath;
        }
        return Null;
    }

    public function update_image(Request $request, $old_image_path,$fileName = 'image',$directory){
        if($request->has($fileName)){// if Request has a Image
            $uploadImage = new request();
            $imagePath = $request->file($fileName)->store($directory,'public'); // Take Image from Request And Save inStorage;
            if ($old_image_path && Storage::disk('public')->exists($old_image_path)) {
                Storage::disk('public')->delete($old_image_path);
            }
            return $imagePath;
        }
        return Null;
    }

    // This to upload file
    public function uploadFile($file, $directory) {
        if ($file) {
            $filePath = $file->store($directory, 'public');
            return $filePath;
        }
        return null;
    }

    // This Trait Aboute file

    public function upload_array_of_file(Request $request,$fileName = 'image',$directory){
        // Check if the request has an array of files
        if ($request->has($fileName)) {
            $uploadedPaths = []; // Array to store the paths of uploaded files

            // Loop through each file in the array
            foreach ($request->file($fileName) as $file) {
                // Store each file in the specified directory
                $imagePath = $file->store($directory, 'public');
                $uploadedPaths[] = $imagePath;
            }

            return $uploadedPaths; // Return an array of uploaded file paths
        }

        return null;
    }

    public function storeBase64Image($base64Image, $folderPath = 'images/nowhere') {

        // Validate if the base64 string has a valid image MIME type
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            // Extract the image MIME type
            $imageType = $type[1]; // e.g., 'jpeg', 'png', 'gif', etc.

            // Extract the actual base64 encoded data (remove the data URL part)
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);

            // Generate a unique file name with the appropriate extension
            $fileName = uniqid() . '.' . $imageType;

            // Save the image to the storage disk (default is local)
            Storage::disk('public')->put($folderPath . '/' . $fileName, $imageData);

            // Return the image path
            return $folderPath . '/' . $fileName;
        }

        return response()->json(['errors' => 'Invalid base64 image string'], 400);
    }


    public function deleteImage($imagePath){
        // Check if the file exists
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
