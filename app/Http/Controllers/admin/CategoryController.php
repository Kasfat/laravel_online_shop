<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest('id');

        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $categories = $categories->paginate(10);
        return view('admin.category.list', ['categories' => $categories]);
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {
            $category = new Category();

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            //Save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImagename = $category->id . '.' . $ext;
                $sPath = public_path('/temp/' . $tempImage->name);
                $dPath = public_path('/uploads/category/' . $newImagename);
                File::copy($sPath, $dPath);

                //Generate Image Thumbnail
                $manager = new ImageManager(new Driver());
                $img = $manager->read($sPath);
                $img->cover(450, 600);
                $img->toPng()->save(public_path('/uploads/category/thumb/' . $newImagename));

                $category->image = $newImagename;
                $category->save();
            }

            session()->flash('success', 'Category added successfully');
            return response()->json([
                'status' => true,
                'message' => 'category added successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit', ['category' => $category]);
    }

    public function update($categoryId, Request $request) {

        $category = Category::find($categoryId);
        if (empty($category)) {
            session()->flash('error', 'Category not found');
            return response()->json([
                    'status'=> false,
                    'notFound'=> true,
                    'message' => 'Category Not Found'
                ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
        ]);

        if ($validator->passes()) {
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            $oldImage = $category->image;

            //Save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImagename = $category->id.'-'.time(). '.' . $ext;
                $sPath = public_path('/temp/' . $tempImage->name);
                $dPath = public_path('/uploads/category/' . $newImagename);
                File::copy($sPath, $dPath);

                //Generate Image Thumbnail
                $manager = new ImageManager(new Driver());
                $img = $manager->read($sPath);
                $img->cover(450, 600);
                $img->toPng()->save(public_path('/uploads/category/thumb/' . $newImagename));

                $category->image = $newImagename;
                $category->save();

            // Delete old images
                File::delete(public_path('/uploads/category/thumb/'.$oldImage));
                File::delete(public_path('/uploads/category/'.$oldImage));

            }

            session()->flash('success', 'Category update successfully');
            return response()->json([
                'status' => true,
                'message' => 'Category update successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request) {
        $categoryId = $request->id;


        $category = Category::find($categoryId);
        if($category == null){
            session()->flash('error', 'Category not found');
            return response()->json([
                'status' => true,
            ]);
        }

        File::delete(public_path('/uploads/category/thumb/'.$category->image));
        File::delete(public_path('/uploads/category/'.$category->image));


        $category->delete();
        session()->flash('success', 'Category deleted successfully.');
        return response()->json([
            'status' => true,
        ]);
    }
}
