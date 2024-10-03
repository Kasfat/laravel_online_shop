<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImagesController extends Controller
{
    public function create(Request $request)
    {
//        $image = $request->image;
        $images = $request->file('image');



        if (!empty($images)) {
            $responseArray = []; // store responses for multiple images

            foreach ($images as $image) {
                $ext = $image->getClientOriginalExtension();
                $newName = time() . rand(1000, 9999) . '.' . $ext; // ensure unique names

                // Save to database
                $tempImage = new TempImage();
                $tempImage->name = $newName;
                $tempImage->save();

                // Move original image to temp directory
                $image->move(public_path('/temp/'), $newName);

                // Generate thumbnail
                $sourcePath = public_path('/temp/' . $newName);
                $destPath = public_path('/temp/thumb/' . $newName);
                $manager = new ImageManager(new Driver());
                $image = $manager->read($sourcePath);
                $image->cover(300, 250);
                $image->toPng()->save($destPath);


                // Add response for each image
                $responseArray[] = [
                    'status' => true,
                    'image_id' => $tempImage->id,
                    'ImagePath' => asset('/temp/thumb/' . $newName),
                    'message' => 'Image uploaded successfully'
                ];
            }

            return response()->json($responseArray);
        }
        return response()->json(['status' => false, 'message' => 'No image uploaded']);
    }
}
