<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class StripeVideoMetaData
{
    public function removeExifMetadata($s3Path)
    {
        $fileName = basename($s3Path);
        $tempInput = storage_path("app/tmp/{$fileName}");
        $tempOutput = storage_path("app/tmp/cleaned_{$fileName}");
    
        // try {
            // Check if file exists on S3
            if (!Storage::disk('s3')->exists($s3Path)) {
                Log::error("File does not exist on S3: $s3Path");
                return false;
            }
    
            // Download from S3
            $videoContent = Storage::disk('s3')->get($s3Path);
            file_put_contents($tempInput, $videoContent);
    
            // Remove metadata using ffmpeg
            shell_exec("ffmpeg -y -i \"$tempInput\" -map_metadata -1 -c copy \"$tempOutput\"");
    
            // Upload cleaned video back
            Storage::disk('s3')->put($s3Path, file_get_contents($tempOutput));
    
            // Cleanup
            unlink($tempInput);
            unlink($tempOutput);
    
            return true;
        // } catch (\Exception $e) {
        //     Log::error("Metadata removal failed for $fileName: " . $e->getMessage());
        //     return false;
        // }
     
    }
}
