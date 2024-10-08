<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $request){
        $subCategories = SubCategory::with('category')
            ->latest('id');

//        if (!empty($request->get('keyword'))) {
//            $subCategories = $subCategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
//            $subCategories = $subCategories->orwhere('category.name', 'like', '%' . $request->get('keyword') . '%');
//        }

        if (!empty($request->get('keyword'))) {
            $keyword = $request->get('keyword');

            // Join with categories table and apply the search condition for both sub_categories and categories
            $subCategories = $subCategories->where(function ($query) use ($keyword) {
                $query->where('sub_categories.name', 'like', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($query) use ($keyword) {
                        $query->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list',['subCategories'=>$subCategories]);
    }
    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        return view('admin.sub_category.create',['categories'=>$categories]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategory = new SubCategory();

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category added successfully');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category added successfully',
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($subCategoryId, Request $request){
        $categories = Category::orderBy('name','ASC')->get();
        $subCategory = SubCategory::find($subCategoryId);
        if (empty($subCategory)) {
            return redirect()->route('sub-categories.index');
        }
        return view('admin.sub_category.edit',['subCategory'=>$subCategory,'categories'=>$categories]);
    }

    public function update(Request $request, $subCategoryId){
        $subCategory = SubCategory::find($subCategoryId);
        if (empty($subCategory)) {
            $request->session()->flash('error','Record Not Found');
            return response()->json([
                'status'=> false,
                'notFound' => true,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            //'slug' => 'required|unique:sub_categories',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category update successfully');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category update successfully',
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy(Request $request){
        $subCategoryId = $request->id;

        $subCategory = SubCategory::find($subCategoryId);

        if($subCategory == null){
            session()->flash('error', 'Sub Category not found');
            return response()->json([
                'status' => true,
            ]);
        }

        $subCategory->delete();
        session()->flash('success', 'Sub Category deleted successfully.');
        return response()->json([
            'status' => true,
        ]);
    }
}
