@extends('layouts.app')
@section('title','Products')
@section('css')
<link rel="stylesheet" href="{{asset('css/custom/product-list-1.css')}}">
<link rel="stylesheet" href="{{asset('css/vendor/jquery-ui.css')}}">
@endsection
@section('content')
<section class="single-banner">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="single-content">
                            <h2>Shop</h2>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Shop</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<section class="product-list">
            <div class="container">
                <div class="row content-reverse">
                    <div class="col-lg-3">
                        <div class="product-bar">
                            <h6>Filter by Price</h6>
                            <input type="text" id="amount" readonly>
							<input type="hidden" id="hidden_minimum_price" value="0" />
							<input type="hidden" id="hidden_maximum_price" value="2000"/>							
                            <div id="slider-range"></div>
					   </div>
                        <div class="product-bar">
                            <h6>Filter by Category</h6>
                            <div class="product-bar-content">
                                <ul class="">
								@foreach($categories as $category)
                                    <li>
                                        <div class="custom-control custom-checkbox">
                                            <input <?php if(request('category') == $category->slug){ echo "checked"; }?> value="{{$category->id}}" type="checkbox" class="custom-control-input common_selector category" id="{{$category->name}}">
                                            <label class="custom-control-label" for="{{$category->name}}">{{$category->name}}</label>
                                        </div>
                                    </li>
										@foreach($category->childrens as $children)
											<li class="ml-4">
												<div class="custom-control custom-checkbox">
													<input <?php if(request('category') == $children->slug){ echo "checked"; }?> value="{{$children->id}}" type="checkbox" class="custom-control-input common_selector category" id="{{$children->name}}">
													<label class="custom-control-label" for="{{$children->name}}">{{$children->name}}</label>
												</div>
											</li>
										@endforeach
								@endforeach
                                </ul>
								<a href="{{route('shop')}}" class="clear-filter-btn">Clear filter</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="product-filter">
									<select class="product-short-select custom-select" id="orderbyprice">
									  <option value="popularity"> Short by Popularity</option>
									  <option value="new"> Short by New Item</option>
									  <option value="bestsale"> Short by Best Sale</option>
									  <option value="asc"> Short by Price Ascending</option>
									  <option value="desc"> Short by Price Descinding</option>
									</select>									
                                </div>
                            </div>
                        </div>
                       
                               <div class="data"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection
@section('javascript')
        <script src="{{asset('js/vendor/jquery-ui.js')}}"></script>

<script type="text/javascript"> 
$(document).ready(function() {
	filter_data();
	$(document).on('click', '.ajax .pagination a', function(event) {
		event.preventDefault();
		$("html, body").animate({
			scrollTop: 150
		}, "slow");
		$('li').removeClass('active');
		$(this).parent('li').addClass('active');
		var page = $(this).attr('href').split('page=')[1];
		// console.log(page);
		var minimum_price = $('#hidden_minimum_price').val();
		var maximum_price = $('#hidden_maximum_price').val();
		//console.log(maximum_price);
		var brand = get_filter('brand');
		var category = get_filter('category');
		
		var orderbyprice = $('#orderbyprice').val();
		$.ajax({
			url: 'productfilter?page=' + page,
			method: "get",
			data: {
				minimum_price: minimum_price,
				maximum_price: maximum_price,
				brand: brand,
				category: category,
				orderbyprice: orderbyprice,
				_token: '{{csrf_token()}}'
			},
			success: function(data) {
				//$('.filter_data').html(data);
				$('.data').html(data);
			}
		});
	});

	function filter_data() {
		var minimum_price = $('#hidden_minimum_price').val();
		var maximum_price = $('#hidden_maximum_price').val();
		//console.log(maximum_price);
		var brand = get_filter('brand');
		var category = get_filter('category');
		var orderbyprice = $('#orderbyprice').val();
		//console.log(orderbyprice);
		//console.log(category);
		$.ajax({
			url: "productfilter",
			method: "get",
			data: {
				minimum_price: minimum_price,
				maximum_price: maximum_price,
				category: category,
				orderbyprice: orderbyprice,
				_token: '{{csrf_token()}}'
			},
			success: function(response) {
				$('.data').html(response);
				//console.log(response);
			}
		});
	}

	function get_filter(class_name) {
		var filter = [];
		$('.' + class_name + ':checked').each(function() {
			filter.push($(this).val());
		});
		return filter;
	}
	$("#orderbyprice").change(function() {
		filter_data();
	});
	$('.common_selector').click(function() {
		filter_data();
	});
	$('#slider-range').slider({
		range: true,
		min: 0,
		max: 500,
		values: [1, 300],
		stop: function(event, ui) {
			$('#hidden_minimum_price').val(ui.values[0]);
			$('#hidden_maximum_price').val(ui.values[1]);
			filter_data();
		},
      slide: function( event, ui ) {
        $( "#amount" ).val( "SAR" + ui.values[ 0 ] + " - SAR" + ui.values[ 1 ] );
      }
			
	});
    $( "#amount" ).val( "SAR" + $( "#slider-range" ).slider( "values", 0 ) +
      " - SAR" + $( "#slider-range" ).slider( "values", 1 ) );	
});

</script>	

@endsection