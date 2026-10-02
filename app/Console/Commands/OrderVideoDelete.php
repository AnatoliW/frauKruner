<?php

namespace App\Console\Commands;

use App\Models\Orderimage;
use App\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderVideoDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:delete';

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
        $storageDisk = config('voyager.storage.disk', config('filesystems.default'));

        $videoOrders = Order::whereNotNull('video')->where('video_uploaded_at', '<', now()->subDays(37))->get();
        foreach ($videoOrders as $video) {
            if (Storage::disk($storageDisk)->exists($video->video)) {
                Storage::disk($storageDisk)->delete($video->video);
            }
            $video->update([
                'video' => null,
            ]);
        }
        $photoOrders = Orderimage::where('created_at', '<', now()->subDays(37))->get();
        foreach ($photoOrders as $photo) {
            if (Storage::disk($storageDisk)->exists($photo->image)) {
                Storage::disk($storageDisk)->delete($photo->image);
            }
            $photo->delete();
        }
    }
}
