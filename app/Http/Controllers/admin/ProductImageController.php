<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductImageController extends Controller
{
    public function update(Request $request)
    {
        $images = $request->file('image');
        // Check if $image is an array
        $responses = [];

        foreach ($images as $image) {
            $ext = $image->getClientOriginalExtension();
            $sourcePath = $image->getPathName();

            $productImage = new ProductImage();
            $productImage->product_id = $request->product_id;
            $productImage->image = 'NULL';
            $productImage->save();

            $ImageName = $request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
            $productImage->image = $ImageName;
            $productImage->save();

            // Large Image
            $largeDestPath = public_path('/uploads/product/large/'.$ImageName);
            $manager = new ImageManager(new Driver());
            $imageObj = $manager->read($sourcePath);
            $imageObj->cover(1400, 1000);
            $imageObj->toPng()->save($largeDestPath);

            // Small Image
            $smallDestPath = public_path('/uploads/product/small/'.$ImageName);
            $imageObj->cover(300, 300);
            $imageObj->toPng()->save($smallDestPath);

            // Add the response for each image
            $responses[] = [
                'status'=> true,
                'image_id' => $productImage->id,
                'ImagePath' => asset('/uploads/product/small/'.$ImageName),
                'message' => 'Image Saved Successfully',
            ];
        }
        // Return all responses for each image
        return response()->json($responses);
    }

    public function destroy(Request $request)
    {
        $productImage = ProductImage::find($request->id);

        if(empty($productImage)){
            return response()->json([
                'status'=> false,
                'message' => 'Image Not Found'
            ]);
        }

        //Delete image from folder
        File::delete(public_path('uploads/product/large/'.$productImage->image));
        File::delete(public_path('uploads/product/small/'.$productImage->image));


        $productImage->delete();
        return response()->json([
           'status'=> true,
           'message' => 'Image Deleted Successfully'
        ]);

    }
}
