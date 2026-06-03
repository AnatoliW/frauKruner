<div class="row product-card-parent">
	@foreach($products as $product)
			 <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-4">
				<div class="product-card card-gape">
					<div class="product-img">
					  <a href="{{$product->path()}}"> 
						<img src="{{media_url($product->image)}}" alt="product">
					  </a>
					</div>
					<div class="product-content">
						<div class="product-name">
							<p class="h6"><a href="{{$product->path()}}">{{ Str::limit($product->name, $limit = 20, $end = '...')}}</a></p>
						</div>
						<div class="product-price">
							@if(!$product->is_variable)
							   @if($product->saleprice)
								  <p class="h6"><del class="mr-2">{{ Shop::price($product->price)}}</del>{{ Shop::price($product->saleprice)}} </p>
							   @else 
								  <p class="h6">{{ Shop::price($product->price)}} </p>
							   @endif
							@endif
							@if(Shop::average_rating($product->ratings) > 0 )
								<div class="product-rating">
									<i class="fas fa-star"></i>
									<span>{{Shop::average_rating($product->ratings)}}/5</span>
								</div>
							@endif
						</div>
						<div class="product-btn">
							<form action="{{route('cart.store')}}" method="post">
							  @csrf
								 <input type="hidden" class="form-control qty" value="1" min="1" name="quantity">
								 <input type="hidden" name="product_id"value="{{$product->id}}" />
								 @if(!$product->is_variable)
									<button class="btn btn-outline"><i class="fas fa-shopping-basket"></i> zum Warenkorb hinzufügen</button>
								@else
									<a href="{{$product->path()}}" class="btn btn-outline">
										<i class="fas fa-shopping-basket"></i>
										<span>Variation auswählen</span>					 
									</a>
								@endif											
							</form>											
						</div>
					</div>
				</div>
			</div> 
	@endforeach
</div>
<div class="row"> 
   <div class="col-md-12 ajax"> 
      {{$products->links()}}
   </div>
</div>
