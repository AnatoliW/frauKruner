<?php

namespace App\Http\Controllers;

use App\Models\Boost;
use App\Order;
use Illuminate\Http\Request;


class AdminController extends Controller
{
    public function index()
    {
        $dataTypeContent = Order::Paid()->active()->Children()->filter()->latest()->paginate(10);

        return view('admin.payouts', compact('dataTypeContent'));
    }

    public function lists(Request $request)
    {
        $dataTypeContent = Order::filter()->children()->paid()->latest()->paginate(10);

        return view('admin.orders', compact('dataTypeContent'));
    }

    public function payemntCheck(Order $order)
    {
        return view('admin.advance_payment_check', compact('order'));
    }

    public function payemntCheckUpdate(Order $order)
    {
        if (! $order->markAsPaid()) {
            return redirect()->back()->with([
                'message' => 'Bestellung war bereits als bezahlt markiert',
                'alert-type' => 'warning',
            ]);
        }

        return redirect()->back()->with([
            'message' => 'Bestellunge als bezahlt markiert',
            'alert-type' => 'success',
        ]);
    }

    public function prepayments(Request $request)
    {
        $dataTypeContent = Order::filter()->children()->where('payment_status', 0)->where('payment_gateway', 'pre_payment')->latest()->paginate(10);

        return view('admin.pre_payments', compact('dataTypeContent'));
    }
    public function boosts() {
         $boosts=Boost::filter()->paidOrFree()->latest()->paginate(15);
         return view('admin.boosts',compact('boosts'));
    }
    public function boostInvoice(Boost $boost)  {
        $dataTypeContent=$boost;
        return view('admin.boost_invoice',compact('dataTypeContent'));
        
    }
}

