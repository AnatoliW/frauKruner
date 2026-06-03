<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Product;
use Illuminate\Http\Request;
use App\Shipping\ShippingCalculation;
use DB;
use Cart;
use Shop;
class ProductVariationController extends Controller
{


	
    public function storeAttribue(Request $request)
    { 
	   Attribute::create([
	      'name' => str_replace(' ', '_', $request->attr_name),
	      'value' =>  $request->attr_value,
	      'product_id' => $request->product_id,
	   ]);
        return back()
            ->with([
                'message'    => "Attribute Added",
				'target'     => "attribute",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ]);
    }
	
    public function updateAttribue(Request $request)
    { 
	   $value= json_encode(explode(',', $request->attr_value));
	   Attribute::where('id',$request->attr_id)->update([
	      'name' => $request->attr_name,
	      'value' =>  $value,
	   ]);
        return back()
            ->with([
                'message'    => "Attribute Updated",
				'target'     => "attribute",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ]);
    }

    public function deleteProductAttribute(Attribute $attribute)
    {
        $attribute->delete();

        return back()
            ->with([
                'message'    => "Attribute deleted",
				'target'     => "attribute",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ])
            ->with('new-attribute', true);
    }

    public function newVariation(Product $product)
    {
		//return $product->id;
        Product::create([
		   'parent_id' => $product->id,
		   'details' => $product->details,
		   'name' => $product->name,
		   'image' => $product->image,
		   'price' => $product->price,
		   'quantity' => $product->quantity,
		   'sku' => $product->sku,
		]);
        return back()
            ->with([
                'message'    => "Product Added",
				'target'     => "variation",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ])
            ->with('new-attribute', true);
    }

    public function updateVariation(Request $request, Product $product)
    {
        $product->update([
            'variation' => $request->variation,
            'price' => $request->variable_price,
			'quantity' => $request->variable_stock,
			'sale_price' => $request->sale_price,
			'length' => $request->length,
			'width' => $request->width,
			'height' => $request->height,
			'weight' => $request->weight,
		]);
		
        return back()
            ->with([
                'message'    => "Product updated",
				'target'     => "variation",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ])
            ->with('new-attribute', true);
	}

    public function deleteProductMeta(Product $product)
    {
        $product->delete();

        return back()
            ->with([
                'message'    => "Variation deleted",
				'target'     => "variation",
				'scroll'     => "scroll",
                'alert-type' => 'success',
            ])
            ->with('new-vaiation', true);
    }
	public function CostCalculation(Request $request){
		session()->put('postal_code',$request->postalcode);
		session()->put('shipping_method',$request->shipping_method);
		session()->put('subrub',$request->subrub);		
	    $calculation = new ShippingCalculation();
		$product = Product::find($request->product_id_calculate);
		if($product->weight > 20){
		       return $shipping_cost = $calculation->CalculationTnt($product,$request->postalcode,$request->subrub,$request->quantity);		
			}else{
				return $shipping_cost = $calculation->CalculationAusPost($product,$request->postalcode,'AUS_PARCEL_REGULAR',$request->quantity);				
			}
		
	}
	public function CopyProduct(Product $product){
		$new_id = Product::create([
		   'name' =>$product->name,
		   'details' =>$product->details,
		   'price' =>$product->price,
		   'status' =>$product->status,
		   'mini_description' =>$product->mini_description,
		   'description' =>$product->description,
		   'featured' =>$product->featured,
		   'sale' =>$product->sale,
		   'new' =>$product->new,
		   'quantity' =>$product->quantity,
		   'image' =>$product->image,
		   'images' =>$product->images,
		   'variation' =>$product->variation,
		   'is_variable' =>$product->is_variable,
		   'length' =>$product->length,
		   'width' =>$product->width,
		   'height' =>$product->height,
		   'weight' =>$product->weight,
		   'box_2' =>$product->box_2,
		   'box_3' =>$product->box_3,
		]);
		$products = Product::with('subproducts','attributes')->find($product->id);
		
		foreach($products->subproducts as $product){
			   Product::create([
					   'name' =>$product->name,
					   'price' =>$product->price,
					   'quantity' =>$product->quantity,
					   'length' =>$product->length,
					   'width' =>$product->width,
					   'height' =>$product->height,
					   'weight' =>$product->weight,
					   'variation' =>$product->variation,
					   'box_2' =>$product->box_2,
					   'box_3' =>$product->box_3,
					   'parent_id' => $new_id->id,
					]);			
		}

		foreach($products->attributes as $attribute){
			   DB::table('attributes')->insert([
			     'name' => $attribute->name,
			     'value' => json_encode($attribute->value),
			     'product_id' => $new_id->id,
			   ]);
		
			
		}
        return back()
            ->with([
                'message'    => "Copied Successfully",
                'alert-type' => 'success',
            ]);
	}
}
