<?php

namespace App\Console\Commands;

use App\Mail\ShippedEmail as MailShippedEmail;
use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class shippedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipped:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seller provides the delivery date After six days, this directive will go into effect.';

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
        $sixDaysAgo = Carbon::now()->subDays(6);

        $orders = Order::where('shipping_date', '<=', $sixDaysAgo)
            ->whereNotNull('parent_id')
            ->whereNull('send_shipping_email')
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            Mail::to($order->email)->send(new MailShippedEmail($order));
            $order->send_shipping_email = 1;
            $order->save();
        }
    }
}
