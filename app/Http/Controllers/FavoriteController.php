<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use App\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, $id)
    {
        $modelType = $request->input('model_type');
        
        if (!in_array($modelType, [User::class, Product::class])) {
            return response()->json(['error' => 'Ungültiger Modelltyp'], 400);
        }
        
        $favoritable = $modelType::findOrFail($id);
      
      

        if ($request->favorite_id) {
            $favorite = Favorite::find($request->favorite_id);
            $favorite->delete();
            return response()->json(['success' => true, 'message' => 'Lieblingsartikel erfolgreich hinzugefügt', 'delete' => true,]);
        } else {
            $favorite = new Favorite();
            $favorite->favoritable()->associate($favoritable);
            $favorite->user_id = auth()->id();
            $favorite->save();
        }

        return response()->json(['success' => true, 'message' => 'Lieblingsartikel erfolgreich hinzugefügt', 'favorite' => $favorite]);
    }

    public function index()
    {
        $product_favorites = auth()->user()->favorites->where('favoritable_type', Product::class);
        $profile_favorites = auth()->user()->favorites->where('favoritable_type', User::class);

        return view('auth.buyer.pages.favorites', compact('product_favorites', 'profile_favorites'));
    }

    public function delete(Request $request, $id)
    {
        $favorite = Favorite::find($id);
        $modelType = $request->input('model_type');
        if ($request->create && $request->product) {
         
            $favoritable = $modelType::findOrFail($request->product);
            $favorite = new Favorite();
            $favorite->favoritable()->associate($favoritable);
            $favorite->user_id = auth()->id();
            $favorite->save();
        
            return response()->json(['success' => true, 'message' => 'Lieblingsartikel erfolgreich hinzugefügt', 'create' => false, 'favorite' => $favorite]);
        } else {
            $favorite->delete();
            return response()->json(['success' => true, 'message' => 'Favorit erfolgreich entfernt!', 'create' => true]);
        }
    }
}
