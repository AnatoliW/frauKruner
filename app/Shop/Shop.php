<?php

namespace App\Shop;

use Cart;
use App\Shipping;
use Location;

class Shop
{
    public function price($price)
    {
        return $this->round_num($price) . ' €';
    }
    public function calculated_total($total, $product = null)
    {
        $commission=$this->commission($total, $product);
        $calculated_total = $total + $this->tax($commission) + $commission;
        return $this->round_num($calculated_total);
    }
    public function tax($total)
    {
        if (setting('finance.vat') && $total < 50) {
            // $total = $total + $this->commission($total);
            $vat = $total * (setting('finance.vat') / 100);
        } else {
            $vat = 0;
        }
        return $vat;
    }
    public function commission($total, $product = null)
    {

        if (isset($product->user->commission)) {
            $commisionInput = $product->user->commission;
            $type = "percentage";
        } else {
            $commisionInput = setting('finance.commission');
            $type = setting('finance.commission_type');
        }


        if ($commisionInput > 0) {
            switch ($type) {
                case 'percentage':
                    $commission = $total * ($commisionInput / 100);
                    break;

                default:
                    $commission = $commisionInput;
                    break;
            }
        } else {
            $commission = 0;
        }
        return $commission;
    }
    public function discount()
    {
        // Gehalten und aktualisiert von App\Support\CouponSession.
        return \App\Support\CouponSession::discount();
    }
    public function discount_code()
    {
        return \App\Support\CouponSession::code();
    }
    public function shipping_method()
    {
        if (session()->has('shipping_method')) {
            return session()->get('shipping_method');
        } else {
            $shipping = Shipping::first();
            return $shipping->Shipping_method;
        }
    }
    public function shipping()
    {
        if (session()->has('shipping_method')) {
            return session()->get('shipping_cost');
        } else {
            $shipping = Shipping::first();
            return $shipping->shipping_cost;
        }
    }
    public function newSubtotal()
    {
        $subtotal = \Cart::getSubTotal();

        return $subtotal + $this->tax($subtotal) - $this->discount();
    }
    public function newTotal()
    {
        return ($this->newSubtotal() + $this->shipping());
    }
    public function round_num($price)
    {
        return sprintf('%.2f', $price);
    }
    public function average_rating($ratings)
    {
        if ($ratings->count() > 0) {
            return $ratings->sum('rating') / $ratings->count();
        }
        return 0.00;
    }

   
}
