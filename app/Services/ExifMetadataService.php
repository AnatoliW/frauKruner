<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExifMetadataService
{
    public function removeExifMetadata($imagePath)
    {

        if (!Storage::exists($imagePath)) {
            Log::warning("Image not found: {$imagePath}");
            return false;
        }

        $fileName = uniqid() . '-' . basename($imagePath);
        $tmpPath = storage_path("app/public/tmp/{$fileName}");

        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }

        file_put_contents($tmpPath, Storage::get($imagePath));

        $cmd = "exiftool -all= -overwrite_original " . escapeshellarg($tmpPath);
        exec($cmd);


        Storage::put($imagePath, file_get_contents($tmpPath));

        // Remove temp files
        unlink($tmpPath);
        $metaBackup = $tmpPath . "_original";
        if (file_exists($metaBackup)) {
            unlink($metaBackup);
        }
        // Log::info("EXIF metadata removed for {$imagePath}");
        return true;
    }

   
}
