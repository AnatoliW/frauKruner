<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessMediaImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $image = Image::where('meta_remove_status', 0)->whereNotNull('image')->first();

        if (! $image) {
            return;
        }

       
        if (Storage::exists($image->image)) {
            $tmpPath = storage_path('app/public/tmp/' . uniqid() . '-' . basename($image->image));

            // Ensure directory exists
            if (!file_exists(dirname($tmpPath))) {
                mkdir(dirname($tmpPath), 0755, true);
            }

            // Download image locally
            file_put_contents($tmpPath, Storage::get($image->image));

            // Strip metadata with exiftool
            $cmd = "exiftool -all= -overwrite_original " . escapeshellarg($tmpPath);
            exec($cmd);

            // Re-upload the cleaned image
            Storage::put($image->image, file_get_contents($tmpPath));

            $metaBackup = $tmpPath . "_original"; // ExifTool backup

            unlink($tmpPath);
            $metaBackup = $tmpPath . "_original"; // ExifTool backup
            if (file_exists($metaBackup)) {
                unlink($metaBackup);
            }
            

            $image->update(['meta_remove_status' => 1]);
            // Log::info('EXIF metadata removed using exiftool for ' . $image->image);
          
        }else{
            $image->update(['meta_remove_status' => 2]);
        }
    }
}
