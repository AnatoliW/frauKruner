@extends('layouts.app')
@section('title','Bestellungen')
@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Bestellungen</div>
                <div class="card-body">
				@if($orders->count() >0)
					<div class="table-responsive">
					  <table class="table table-condensed">
						<thead>
						  <tr>
							<th>Bestelldatum</th>
							<th>ID</th>
							<th>Gesamt</th>
							<th>Bestelldetails</th>
						  </tr>
						</thead>
						<tbody>
						@foreach($orders as $order)
						  <tr>
							<td>{{$order->created_at->format('d M Y')}}</td>
							<td>{{$order->id}}</td>
							<td>{{Shop::price($order->total)}}</td>
							<td>
							   <a href="{{route('invoice', ['order'=>$order->id])}}" class="btn btn-info">Details</a>
							</td>
						  </tr>
						@endforeach
						</tbody>
					  </table>
					</div>  
				@else
				<h3 class="mt-5"> Du hast noch keine Bestellungen aufgegeben</h3>
				@endif	
				
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
