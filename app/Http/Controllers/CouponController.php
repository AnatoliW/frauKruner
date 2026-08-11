<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Support\CouponMessages;
use App\Support\CouponSession;
use Cart;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $coupon = Coupon::query()->code($request->coupon_code)->first();

        if (! $coupon) {
            return redirect()->back()->withErrors(CouponMessages::unknownCode());
        }

        $subtotal = (float) Cart::getSubTotal();

        if ($reason = $coupon->rejectionFor($subtotal)) {
            return redirect()->back()->withErrors(CouponMessages::forRejection($coupon, $reason));
        }

        // Die Einlösung wird erst beim Abschluss der Bestellung gebucht,
        // damit Anwenden/Entfernen im Warenkorb das Limit nicht aufbraucht.
        CouponSession::apply($coupon, $subtotal);

        return back()->with('success', 'Gutschein wurde erfolgreich angewendet');
    }

    public function destroy()
    {
        CouponSession::forget();

        return back()->with('success', 'Gutschein erfolgreich entfernt');
    }
}
