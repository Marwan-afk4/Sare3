<?php

namespace App\trait;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

trait ImageUpload
{

    public function uploadFromFile(UploadedFile $file, string $dir): array
{
    if (!$file->isValid() || empty($dir)) {
        return [
            'status' => 'error',
            'message' => 'Invalid file or missing directory'
        ];
    }

    try {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getContent());
        $originalWidth = $image->width();

        $imageExtension = $file->getClientOriginalExtension();
        $uniqueName = uniqid() . '_' . rand(100000, 999999) . '_' . microtime(true);
        $uniqueName = str_replace(' ', '_', $uniqueName);
        $fileName = $uniqueName . '.' . $imageExtension;

        $filePath = 'uploads/' . $dir . '/' . date('Y/m/d/');
        $originalPath = $filePath . 'org/' . $fileName;
        $smallPath = $filePath . '300/' . $fileName;
        $mediumPath = $filePath . '600/' . $fileName;

        Storage::disk('public')->put($originalPath, (string) $image->encode());

        if ($originalWidth > 300) {
            $smallImage = $image->scaleDown(300, null);
            Storage::disk('public')->put($smallPath, (string) $smallImage->encode());
        } else {
            Storage::disk('public')->put($smallPath, (string) $image->encode());
        }

        if ($originalWidth > 600) {
            $mediumImage = $image->scaleDown(600, null);
            Storage::disk('public')->put($mediumPath, (string) $mediumImage->encode());
        } else {
            Storage::disk('public')->put($mediumPath, (string) $image->encode());
        }

        return [
            'status' => 'success',
            'message' => 'Image uploaded successfully!',
            'file_name' => $fileName,
            'paths' => [
                'original' => $originalPath,
                'small' => $smallPath,
                'medium' => $mediumPath
            ]
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to upload image: ' . $e->getMessage()
        ];
    }
}
}
