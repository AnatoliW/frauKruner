<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteUnpaidOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:unpaidorder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command will delete all the orders that was unpaid last 6 hours';

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

        $currentDateTime = Carbon::now();
        // Subtract 6 hours from the current time
        $sixHoursAgo = $currentDateTime->subHours(6);

        // Retrieve the orders
        $order = Order::whereNotNull('parent_id')
            ->where('payment_status', 0)
            ->whereNotNull('payment_id')
            ->where('created_at', '<', $sixHoursAgo)
            ->first();

        if (! $order) {
            return;
        }

            Log::create([
                'details'=>json_encode($order),
                'email'=>$order->email ?? null,
                'admin_id'=>$order->vendor_id ?? null,
                'user_id'=>$order->user_id ?? null,
               ]);
            if(isset($order->parent->childrens)){
                foreach($order->parent->childrens as $children){
                    $children->delete();
                }
            }
        if(isset($order->parent)){
            $order->parent->delete();
        }
    }
}
