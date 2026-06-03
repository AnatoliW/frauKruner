@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing') . ' ' . 'Payouts')

@section('page_header')

    <div class="container-fluid">
        <h1 class="page-title" style="display: flex;align-items:center">
            <i class="voyager-dollar"></i>
            Verify payment
            <form action="{{route('admin.order.payment.check.update',$order)}}" method="post">
                @csrf

                <button type="submit" class="btn btn-danger" style="margin-left: 10px">Verify</button>
            </form>

        </h1>
    </div>
@stop

@section('content')
    <img src="{{ media_url($order->payment_prove) }}" alt="">

@endsection

