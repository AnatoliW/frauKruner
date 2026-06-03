@extends('layouts.app')
@section('title',request('search'))
@section('content')
	<div class="search pt-5 bg-white"> 
		<div class="container">
			<h3 class="h3 mb-5 heading text-center">Suchergebnisse: {{request('search')}} </h3>
			<div class="row">
			  <x-products :products="$products"/>
			</div>
		</div>
	</div>
@endsection
