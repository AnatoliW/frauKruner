@extends('layouts.app')
@section('title','FrauKruner – Alles was deine Lust begehrt.')
@section('description','Getragene Slips, BHs, Socken, Bikinis und vieles mehr günstig kaufen & verkaufen. Frau Kruner ist 100% echt und ehrlich und immer für dich da.')
@section('content')

<section class="slider">
	<div id="homeslider" class="carousel slide" data-ride="carousel">
	  <div class="carousel-inner">
	  	@foreach($sliders as $slider)
		<div class="carousel-item {{$loop->index == 0 ? 'active' : ''}}">
          <img class="d-block w-100" src="{{Storage::url($slider->image)}}" alt="{{setting('site.title')}}">
		  @if($slider->heading)
		  <div class="carousel-caption d-none d-md-block bg-dark rounded p-2" style="opacity:.8">
		    <h5 class="text-light">{{$slider->heading}}</h5>
		    <p>{{$slider->paragraph}}</p>
		  </div>
		  @endif
		</div>
	  	@endforeach
	  </div>
	  <a class="carousel-control-prev" href="#homeslider" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">Vorher</span>
	  </a>
	  <a class="carousel-control-next" href="#homeslider" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">Nächste</span>
	  </a>
	</div>
</section>
<section class="section trend-part">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-heading">
					<h2 class="title">Neue Produkte</h2>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="product-slider slider-arrow">
				   <x-productslide :products="$new_products"/>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="section trend-part">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-heading">
					<h2 class="title">Bestseller</h2>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="product-slider slider-arrow">
				   <x-productslide :products="$bestsaleproducts"/>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="section trend-part mb-3">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-heading">
					<h2 class="title">Sale</h2>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="product-slider slider-arrow">
				   <x-productslide :products="$saleproducts"/>
				</div>
			</div>
		</div>
	</div>
</section>

@endsection
