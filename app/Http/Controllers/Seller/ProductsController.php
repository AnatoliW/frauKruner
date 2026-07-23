<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Finishing;
use App\Category;
use App\Addition;
use App\Models\Image;
use App\Package;
use App\WearingTime;
use App\Product;
use App\Services\ExifMetadataService;
use App\Tag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', Auth::id())->latest()->get();
        return view('auth.seller.pages.products.index', compact('products'));
    }

    public function create()
    {

        $finishings = Finishing::all();
        $categories = Category::all();
        $additions = Addition::all();
        $wearingTimes = WearingTime::all();
        $packages = false;
        $tags = Tag::all();
        $product = new product;
        $images = new Image();
        return view('auth.seller.pages.products.create', compact('images', 'finishings', 'categories', 'additions', 'wearingTimes', 'product', 'tags', 'packages'));
    }

    public function store(Request $request)
    {
        $fullFilename = null;
        $file = $request->file('thumbnail');


        $request->validate([
            'name' => ['required', 'max:40'],
            'category' => ['required'],
            'details' => ['required'],
            'selloption' => ['required'],
            'tags' => ['nullable'],
            // 'slug' => 'max:25|string|unique:products,slug|alpha_dash',
            'price' => ['required', 'numeric'],
            'shipping_cost' => ['required', 'numeric'],
            // 'finishings.*' => 'required',
            'addition' => 'nullable',
            'finishings' => 'nullable',
            'wearing_time' => 'nullable',
            'thumbnail' => ['required', 'mimes:jpeg,jpg,png,webp']
        ]);

        DB::beginTransaction();
        // $path = 'thumbnail'.'/'.date('F').date('Y').'/';
        $path = 'thumbnail' . '/';
        $filename = basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension());
        $filename_counter = 1;
        // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
        while (Storage::exists($path . $filename . '.' . $file->getClientOriginalExtension())) {
            $filename = basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension()) . (string) ($filename_counter++);
        }
        $fullPath = $path . $filename . '.' . $file->getClientOriginalExtension();
        $ext = $file->guessClientExtension();
        if (in_array($ext, ['jpeg', 'jpg', 'png',])) {
            if (Storage::disk(config('filesystems.default'))->putFileAs($path, $file, $filename . '.' . $file->getClientOriginalExtension())) {
                $status = 'Upload successful';
                $fullFilename = $fullPath;
            } else {
                $status = 'Upload failed';
            }
        } else {
            $fullPath = $request->thumbnail;
        }
        $uniqueId = substr((string) Str::uuid(), 0, 10);
        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category,
            'details' => $request->details,
            'tags' => json_encode($request->tags),
            'description' => $request->description,
            'finishings' => json_encode($request->finishings),
            'addition' => json_encode($request->addition),
            'wearing_time' => json_encode($request->wearing_time),
            'price' => $request->price,
            'shipping_cost' => $request->shipping_cost,
            'user_id' => Auth::id(),
            'image' => $fullPath,
            'selloption' => $request->selloption,
            'slug' => $uniqueId,
        ]);
        $exifService = new ExifMetadataService();
        $processImage = $exifService->removeExifMetadata($product->image);
        if ($processImage) {
            $product->update([
                'meta_remove_status' => 1,
            ]);
        }

        if ($request->has('images')) {

            foreach ($request->images as $image) {
                if (isset($image['image']) && $image['image'] !== null) {
                    $productImage = $product->images()->create([
                        'image' => @$image['image']->store('images'),
                        'nsfw' => @$image['nsfw'] ?? 0
                    ]);
                    $exifServiceImages = app(ExifMetadataService::class);
                    $processImagesImage = $exifServiceImages->removeExifMetadata($productImage->image);

                    if ($processImagesImage) {
                        $productImage->update([
                            'meta_remove_status' => 1,
                        ]);
                    }
                }
            }
        }
        DB::commit();
        $slug = Str::slug($product->name);
        $product->update([
            'slug' => $slug . '-' . $uniqueId,
        ]);

        return redirect()->route('seller.products')->with('boost', $product->id);
    }

    public function edit(Product $product)
    {
        $tags = Tag::all();
        $finishings = Finishing::all();
        $categories = Category::all();
        $additions = Addition::all();
        $wearingTimes = WearingTime::all();
        $packages = Package::where('type', 'Product')->get();
        $images = $product->images;
        return view('auth.seller.pages.products.edit', compact('finishings', 'categories', 'additions', 'wearingTimes', 'product', 'images', 'tags', 'packages'));
    }
    public function update(Request $request, Product $product)
    {
        $fullFilename = null;
        $resizeWidth = 780;
        $resizeHeight = null;
        $file = $request->file('thumbnail');
        $request->validate([
            'name' => ['required', 'max:40'],
            'category' => ['required'],
            'selloption' => ['required'],
            'details' => ['required'],
            'tags' => ['nullable'],
            'price' => ['required', 'numeric'],
            'shipping_cost' => ['required', 'numeric'],
            'finishings' => 'nullable',
            'addition' => 'nullable',
            'wearing_time' => 'nullable',
            'thumbnail' => ['nullable', 'image'],
            'images.*.image' => ['nullable', 'image']
        ]);
        if ($request->images) {
            $removed = $product->images->pluck('id')->diff(((new Collection($request->images))->pluck('id')));

            foreach ($removed as $id) {
                $data = Image::find($id);
                if (! $data) {
                    continue;
                }
                if (filled($data->image) && Storage::exists($data->image)) {
                    Storage::delete($data->image);
                }
                $data->delete();
            }
            foreach ($request->images as $image) {

                if (isset($image['image'])) {

                    if (isset($image['id'])) {
                        $data = Image::find($image['id']);
                        if ($data) {
                            if (filled($data->image) && Storage::disk('s3')->exists($data->image)) {
                                Storage::disk('s3')->delete($data->image);
                            }
                            $data->delete();
                        }
                    }
                    if (isset($image['image']) && $image['image'] !== null) {
                       $productImage= $product->images()->create([
                            'image' => $image['image']->store('images'),
                            'nsfw' => @$image['nsfw'] ?? 0
                        ]);
                        $exifServiceImages = app(ExifMetadataService::class);
                        $processImagesImage = $exifServiceImages->removeExifMetadata($productImage->image);

                        if ($processImagesImage) {
                            $productImage->update([
                                'meta_remove_status' => 1,
                            ]);
                        }
                    }
                } else {
                    if (isset($image['id'])) {
                        $data = Image::find($image['id']);
                        if ($data) {
                            $data->update([
                                'nsfw' => @$image['nsfw'] ?? 0
                            ]);
                        }
                    }
                }
            }
        } else {
            $removed = $product->images->pluck('id')->diff([]);
            foreach ($removed as $id) {
                $data = Image::find($id);
                if (! $data) {
                    continue;
                }
                if (filled($data->image) && Storage::exists($data->image)) {
                    Storage::delete($data->image);
                }
                $data->delete();
            }
        }

        if ($request->thumbnail) {

            if (filled($product->image) && Storage::exists($product->image)) {
                Storage::delete($product->image);
            }
            $path = 'thumbnail' . '/';
            $filename = basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension());
            $filename_counter = 1;
            // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
            while (Storage::disk(config('filesystems.default'))->exists($path . $filename . '.' . $file->getClientOriginalExtension())) {
                $filename = basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension()) . (string) ($filename_counter++);
            }
            $fullPath = $path . $filename . '.' . $file->getClientOriginalExtension();
            $ext = $file->guessClientExtension();
            if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                if (Storage::disk(config('filesystems.default'))->putFileAs($path, $file, $filename . '.' . $file->getClientOriginalExtension())) {
                    $status = 'Upload successful';
                    $fullFilename = $fullPath;
                } else {
                    $status = 'Upload failed';
                }
            } else {
                $status = 'Unsupported file type';
            }
            $product->update([
                'image' => $fullPath,
            ]);

            $exifService = new ExifMetadataService();
            $processImage = $exifService->removeExifMetadata($product->image);
            if ($processImage) {
                $product->update([
                    'meta_remove_status' => 1,
                ]);
            }
            // $product->save();
        }
        if ($request->has('package')) {
            $package = Package::find($request->package);
            $boost = $product->boosts()->create([
                'package_id' => $package->id,
                'user_id' => Auth::id(),
                'price' => $package->price_with_tax,
                'base_price' => $package->price,
                'start_day' => Carbon::now(),
                'end_day' => Carbon::now()->addDays($package->days),
            ]);
            $payment = $boost->charge($package->price);
            return redirect()->route('seller.payment', $payment);
        }

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category,
            'details' => $request->details,
            'tags' => $request->tags,
            'description' => $request->description,
            'finishings' => json_encode($request->finishings),
            'addition' => json_encode($request->addition),
            'wearing_time' => json_encode($request->wearing_time),
            'price' => $request->price,
            'shipping_cost' => $request->shipping_cost,
            'selloption' => $request->selloption,
            // 'slug' => $product->slug,
        ]);
        return redirect()->route('seller.products')->with('success', 'Produkt erfolgreich aktualisiert');
    }

    public function delete(Product $product)
    {

        if (filled($product->image) && Storage::exists($product->image)) {
            Storage::delete($product->image);
        }
        if ($product->images) {

            foreach ($product->images as $image) {
                if (filled($image->image) && Storage::exists($image->image)) {
                    Storage::delete($image->image);
                }
                $image->delete();
            }
        }
        $product->delete();
        return redirect()->route('seller.products')->with('success', 'Produkt erfolgreich gelöscht');
    }
    public function productActive(Product $product)
    {
        // Ensure product belongs to current user
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        // Use raw status - accessor combines user status, which would always return false when profile is paused
        $rawStatus = $product->getRawOriginal('status');
        $product->update([
            'status' => !$rawStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produktaktion erfolgreich erstellt',
        ]);
    }
}

