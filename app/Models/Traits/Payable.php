<?php

namespace App\Models\Traits;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Payable
{

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function charge($amount)
    {
        $this->payment()->create([
            'status' => 'PENDING',
            'tax' => $amount * (setting('finance.vat') / 100) ,
            'amount' => $amount + ($amount * (setting('finance.vat') / 100))
        ]);

        return $this->payment;
    }
}
