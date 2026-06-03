<?php

namespace App\Http\Controllers;

use App\Mail\VendorOrderEmail;
use App\Models\Boost;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


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
        $order->update([
            'payment_status' => 1,
            'status' => 1,
        ]);
        $order->parent->update([
            'payment_status' => 1,
            'status' => 1,
        ]);
        Mail::to($order->vendor->email)->send(new VendorOrderEmail($order));

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
         $boosts=Boost::filter()->paid()->latest()->paginate(15);
         return view('admin.boosts',compact('boosts'));
    }
    public function boostInvoice(Boost $boost)  {
        $dataTypeContent=$boost;
        return view('admin.boost_invoice',compact('dataTypeContent'));
        
    }
}

