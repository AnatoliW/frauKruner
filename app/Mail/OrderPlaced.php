<?php

namespace App\Mail;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPlaced extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $massage;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order,$massage=null)
    {
        $this->order = $order;
        $this->massage = $massage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Bestellung #'.$this->order->id)
            ->markdown('emails.orders.placed');
    }
}
