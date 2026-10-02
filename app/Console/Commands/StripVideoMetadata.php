<?php

namespace App\Console\Commands;

use App\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class StripVideoMetadata extends Command
{
    protected $signature = 'videos:strip-metadata';
    protected $description = 'Download videos from S3, strip metadata, and re-upload';

    public function handle()
    {
        $this->info("Starting metadata removal...");

        $order = Order::where('meta_remove_status',0)->whereNotNull('video')->first();

        if (! $order) {
            $this->info("Nothing to process.");
            return;
        }

        $s3Path = $order->video;
        $fileName = basename($s3Path);
        $tempInput = storage_path("app/tmp/{$fileName}");
        $tempOutput = storage_path("app/tmp/cleaned_{$fileName}");

        if (! is_dir(dirname($tempInput))) {
            mkdir(dirname($tempInput), 0755, true);
        }

        $storageDisk = config('voyager.storage.disk', config('filesystems.default'));

        if (! Storage::disk($storageDisk)->exists($s3Path)) {
            $order->update(['meta_remove_status' => 2]);
            $this->error("Missing: $fileName");
            return;
        }

        try {
            // Download from storage
            $videoContent = Storage::disk($storageDisk)->get($s3Path);
            file_put_contents($tempInput, $videoContent);

            // Remove metadata
            shell_exec("ffmpeg -y -i \"$tempInput\" -map_metadata -1 -c copy \"$tempOutput\"");

            // Never overwrite the original with an empty file when ffmpeg failed
            if (! file_exists($tempOutput) || filesize($tempOutput) === 0) {
                throw new \RuntimeException('ffmpeg produced no output');
            }

            // Upload cleaned video
            Storage::disk($storageDisk)->put($s3Path, file_get_contents($tempOutput));

            $order->update(['meta_remove_status' => 1]);
            $this->info("Processed: $fileName");
        } catch (\Exception $e) {
            $order->update(['meta_remove_status' => 2]);
            $this->error("Failed: $fileName - " . $e->getMessage());
        } finally {
            // Cleanup
            @unlink($tempInput);
            @unlink($tempOutput);
        }

        $this->info("Metadata removal completed.");

    }
}
