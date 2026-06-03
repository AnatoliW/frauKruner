@extends('layouts.app')
@section('title',$product->name)
@section('css')
<link rel="stylesheet" href="{{asset('css/custom/product-details-3.css')}}">
<link rel="stylesheet" href="{{asset('css/custom/star-rating.css')}}" media="all" type="text/css"/>
<style type="text/css">
  .qty{
    width: 100px;
    padding: 0px 15px;
    height: 50px;
    border-radius: 3px;
    border: 2px solid #e8e8e8;
  }
</style>
@endsection
@section('content')
@php
  $images = json_decode($product->images);
  $variatImg = json_decode($product->image);
@endphp

<section class="single-banner">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="single-content">
                            <h2>{{$product->name}}</h2>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{$product->name}}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="product-part pb-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-lg-6">
                        <div class="preview-slider slider-arrow">
                            <img src="{{media_url($product->image)}}" alt="product">
							@if($product->images)
								@foreach($images as $image)
								   <img src="{{media_url($image)}}" alt="product">
								@endforeach
							@endif
                        </div>
                        <div class="thumb-slider">
                            <img src="{{media_url($product->image)}}" alt="product">
							@if($product->images)
								@foreach($images as $image)
								   <img src="{{media_url($image)}}" alt="product">
								@endforeach
							@endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="product-name">
                            <h3>{{$product->name}}</h3>
                        </div>
                        <div class="product-review">
                            <ul>
                                <li><i class="fas fa-star"></i></li>
                                <li><i class="fas fa-star"></i></li>
                                <li><i class="fas fa-star"></i></li>
                                <li><i class="fas fa-star"></i></li>
                                <li><i class="fas fa-star"></i></li>
                                <li><a href="#">({{$product->ratings->count()}} Reviews)</a></li>
                            </ul>
                        </div>
                        <div class="product-price">
                            <p><span class="amount" id="amount">{{Shop::price($product->price)}}</span></p>
                        </div>
					@if($product->quantity > 0)
					<p class="mb-1">Instock: {{$product->quantity}}</p>
				    @else
						<p class="text-danger mb-1">Ausverkauft</p>
					@endif
					<p class="mb-1">Poduct Code : {{$product->sku}}</p>
                        <div class="product-summery">
                            <p>{{$product->details}}</p>
                        </div>
                        <div class="product-cart">
							<form action="{{route('cart.store')}}" method="post" class="form-inline">
							@csrf
							<input type="hidden" name="product_id"value="{{$product->id}}" />
									@if($product->is_variable && count($product->subproductsuser) > 0)
										<div class="row mt-2 pt-2 w-100 mb-2">
											@foreach($product->attributes as $attribute)
												<div class="form-group col-md-4 pl-0 ">
												  <label for="{{$attribute->name}}"> {{str_replace('_', ' ', $attribute->name)}}</label>
												  <select class="form-control w-100" id="{{str_replace(' ', '_', $attribute->name)}}" name="variable_attribute[{{$attribute->name}}]" onchange="change_variable()" onload="" required>

												  @foreach($attribute->value as $value)
													<option value="{{$value}}">{{$value}}</option>
												   @endforeach
												  </select>
												</div>
											@endforeach

										</div>
									@endif
									<div class="row">
										<div class="input-group">
										  <input type="number" class="form-control qty" value="1" min="1" name="quantity" style="width:80px">
										</div>
									</div>
									<button class="btn btn-outline ml-4" type="submit"> <i class="fas fa-shopping-basket"></i> Zum Warenkorb</button>
							</form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<section class="product-info">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="product-info-content">
                            <h2>specification</h2>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">Product code</th>
                                        <td>SKU: {{$product->sku}}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Weight</th>
                                        <td>{{$product->weight}} kg</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Categories</th>
                                        <td>
										  @foreach($product->categories as $category)
										   <a href="{{route('shop',['category' => $category->slug])}}" class="text-dark">{{$category->name}} ,</a>
										  @endforeach
										</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<div class="product bg-white pt-3 pb-3" >
		 <div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="row mt-3">
					  <div class="col-md-12">
						<ul class="nav nav-tabs" id="myTab" role="tablist">
						  <li class="nav-item">
							<a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
						  </li>
						  <li class="nav-item">
							<a class="nav-link" id="rating-tab" data-toggle="tab" href="#rating" role="tab" aria-controls="rating" aria-selected="false">Ratings</a>
						  </li>
						</ul>
						</div>
						<div class="col-md-12">
							<div class="tab-content" id="myTabContent">
							  <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
								  <div class="card">
									  <div class="card-body">
										{!! $product->description !!}
									  </div>
								  </div>
							  </div>
							  <div class="tab-pane fade" id="rating" role="tabpanel" aria-labelledby="rating-tab">
								<div class="card">
									<div class="card-body">
										<form action="{{url('/rating',['product_id'=>$product->id])}}" method="post">
										 @csrf
											<div class="row">
												<div class="col-md-12">
												   <h4>Your Rating</h4>
												</div>
												<div class="col-lg-12 mt-3">
													<input name="rating" type="number" value="1" class="rating product_rating" min="1" max="5" step=".5" data-size="xs">
												</div>
											   <div class="col-md-6">
												 <div class="form-group">
													<label for="name">Name</label>
													<input value="{{old('name')}}" type="text" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Name" name="name" required>
													@error('name')
														<span class="invalid-feedback" role="alert">
															<strong>{{ $message }}</strong>
														</span>
													@enderror
												 </div>
											   </div>
											   <div class="col-md-6">
												 <div class="form-group">
													<label for="email">Email</label>
													<input value="{{old('email')}}" type="text" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="admin@example.com" name="email" required>
													@error('email')
														<span class="invalid-feedback" role="alert">
															<strong>{{ $message }}</strong>
														</span>
													@enderror
												 </div>
											   </div>
											   <div class="col-md-12">
												 <div class="form-group">
													<label for="comment">Comment</label>
													<textarea name="comment"cols="30" rows="10" class="form-control @error('comment') is-invalid @enderror" id="comment" required></textarea>
													@error('comment')
														<span class="invalid-feedback" role="alert">
															<strong>{{ $message }}</strong>
														</span>
													@enderror
												 </div>
											   </div>
											   <div class="col-md-12">
												  <button type="submit" class="btn btn-secondary">Senden</button>
											   </div>
											</div>
										</form>
										<hr />
										@foreach($product->ratings as $rating)
											<div class="row mt-5">
												<div class="col-md-2">
													<img src="{{asset('storage/users/default.png')}}"style="width:100px" class="img img-rounded"/>
													<p class="text-secondary">{{ $rating->created_at->diffForHumans() }}</p>
												</div>
												<div class="col-md-10">
														<a class="float-left" href="#"><strong>{{$rating->name}}</strong></a> <br />
														<input name="rating" type="number" value="{{$rating->rating}}" class="rating published_rating" data-size="xs">
												   <div class="clearfix"></div>
													<p>{{$rating->review}}</p>
												</div>
											</div>
										@endforeach
									</div>
								</div>
							  </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row mt-5 poppins">
				<div class="col-md-12">
					<h3 class="border-bottom pb-2">Product you may like</h3>
				</div>
				 <x-products :products="$related_products"/>
			</div>
		  </div>
		</div>

@endsection
@section('javascript')
 <script src="{{asset('js/custom/star-rating.js')}}" type="text/javascript"></script>
<script type="text/javascript">
	@if($product->is_variable)
		var products = {!! $product->subproductsuser->toJson() !!};
       change_variable();
		function change_variable(){
			variations={
				@foreach($product->attributes as $attribute)
				  '{{$attribute->name}}' : $('#{{str_replace(' ', '_', $attribute->name)}}').val(),
				@endforeach
			}
			var product =  products.filter(function(product) {
				return Object.keys(variations).every(function (variation) {
					return product.variation[variation] === variations[variation];
				});
			});
			if(product.length > 0){
				$("#amount").text("$"+product[0].price);
				if(product[0].image){
					$("#showcase").attr("src", "/storage/" +product[0].image);
				}
			}else{
				$("#amount").text("No variation found. Please select other variation");
			}
		};
	@endif
	$("#product_rating").rating({
		showCaption: true
	});
	$(".published_rating").rating({
		showCaption: false,
		readonly:true,
	});

</script>


@endsection

