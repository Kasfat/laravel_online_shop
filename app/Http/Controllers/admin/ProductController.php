<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;

class ProductController extends Controller
{
    public function index(Request $request){
        $products = Product::latest('id')->with('product_images');

        if($request->get('keyword') != ''){
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }
        $products = $products->paginate();
        return view('admin.products.list',['products'=>$products]);
    }

    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        return view('admin.products.create',['categories'=>$categories, 'brands'=>$brands]);
    }

    public function store(Request $request){

        $rules = [
            'title' => 'required',
            'slug' => 'required|Unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|Unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];
        if(!empty($request->track_qty) && $request->track_qty=='Yes'){
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){
            $product = new Product();

            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->save();

            //save Gallery images
            if(!empty($request->image_array)){
                foreach ($request->image_array as $temp_image_id){
                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.',$tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $ImageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    $productImage->image = $ImageName;
                    $productImage->save();

                    //Generate Product Thumbnails

                    //Large Image
                    $sourcePath = public_path('/temp/'.$tempImageInfo->name);
                    $destPath = public_path('/uploads/product/large/'.$ImageName);
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($sourcePath);
                    $image->cover(1400, 1000);
                    $image->toPng()->save($destPath);

                    //Small Image
                    $destPath = public_path('/uploads/product/small/'.$ImageName);
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($sourcePath);
                    $image->cover(300,300);
                    $image->toPng()->save($destPath);
                }
            }

            session()->flash('success', 'Product added successfully');
            return response()->json([
                'status' => true,
                'message' => 'Product added successfully',
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit(Request $request,$id)
    {
        $product = Product::find($id);
        if(empty($product)){
            //$request->session()->flash('error','Product Not Found');
            return redirect()->route('products.index')->with('error','Product Not Found');
        }

        //fetch Product image
        $productImages = ProductImage::where('product_id',$product->id)->get();

        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        return view('admin.products.edit',['categories'=>$categories, 'brands'=>$brands, 'product'=>$product, 'subCategories'=>$subCategories, 'productImages'=>$productImages]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required|Unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|Unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];
        if(!empty($request->track_qty) && $request->track_qty=='Yes'){
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->save();

            //save Gallery images


            session()->flash('success', 'Product updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $productId = $request->id;
        $product = Product::find($productId);
        $productImages = ProductImage::where('product_id',$productId)->get();


        if($product == null){
            session()->flash('error', 'Product not found');
            return response()->json([
                'status' => true,
            ]);
        }

        if(!empty($productImages)){
            foreach ($productImages as $productImage){
                File::delete(public_path('/uploads/product/large/'.$productImage->image));
                File::delete(public_path('/uploads/product/small/'.$productImage->image));
            }
        }

        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
        return response()->json([
            'status' => true,
        ]);
    }
}
