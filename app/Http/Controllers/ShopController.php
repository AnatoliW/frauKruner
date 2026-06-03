<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Product;
use App\Prodcat;
use App\Brand;
use App\Shipping;
use App\Rating;
use Illuminate\Support\Facades\Session;
use Shop;
class ShopController extends Controller
{
	public function  productfilter(Request $request){
		$min = $request->minimum_price * 100;
		$max = $request->maximum_price * 100;
		$category = $request->category;
		$orderbyprice = $request->orderbyprice;
		
		$products = Product::query();
		 $products = $products->whereBetween('price', [$min, $max]);


		if (isset($category)) {
			$products = $products->whereHas('categories', function ($query) use ($category) {
			    $query->whereIn('prodcats.id', $category);
			});
		}
		
		if($request->filled("orderbyprice")){
			if($orderbyprice == 'asc' || $orderbyprice == 'desc' ){
				$products = $products->orderby('price',$orderbyprice);
			}elseif($orderbyprice == 'bestsale'){
				$products = $products->orderby('sale_count','desc');
			}elseif($orderbyprice == 'new'){
				$products = $products->latest();
			}elseif($orderbyprice == 'popularity'){
				$products = $products->orderby('view','desc');
			}
			
		}
		$products = $products->where('status',1)->whereNull('parent_id')->paginate(12);
	    return view('components.products_ajax',compact('products'));	
	}
	
	public function product($slug){
		$related_products = Product::inRandomOrder()->where('status',1)->limit(4)->whereNull('parent_id')->get();
		$categories = Prodcat::all();
		$brands = Brand::all();
	    $product = Product::where('slug', '=', $slug)->with('subproductsuser','attributes','ratings')->firstOrFail();
		session()->put("product_id",$product->id);
		$product->increment('view');
		Session::push('last_viewed', $product->id);
		return view('product',compact('product','categories','brands','related_products'));
	}
	public function shipping(Request $request){
		 
		$shipping = Shipping::find($request->shipping_id);
		Session::put('shipping_method', $shipping->Shipping_method);
		Session::put('shipping_cost', $shipping->shipping_cost);		
	}
	
}
