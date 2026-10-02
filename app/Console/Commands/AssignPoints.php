<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Product;
use App\Rating;
use Illuminate\Console\Command;

class AssignPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:point';

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
        $this->profile_image_point();
        $this->ratting_point();
    }

    private function profile_image_point()
    {
        $sellers = User::where('role_id', 3)->has('profile')->get();
        foreach ($sellers as $seller) {
            if ($seller->profile->profile_img) {
                $seller->addPoint(10);
            }else{
                $seller->addPoint(0);
            }
        }
    }

    private function ratting_point()
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product->addPoint(0);
        }
        
        $ratings = Rating::all();
        foreach ($ratings as $rating) {
           
            if (@$rating->product->id) {
                $rating->product->addPoint($rating->rating);
            }

            if (@$rating->vendor->id) {
                $rating->vendor->addPoint($rating->rating);
            }
        }
    }
}
