<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
    public function index(Request $request){
        $brands = Brand::latest('id');
        if (!empty($request->get('keyword'))) {
            $brands = $brands->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $brands = $brands->paginate(10);

        return view('admin.brands.list',['brands'=>$brands]);
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if ($validator->passes()) {
          $brand = new Brand();

          $brand->name = $request->name;
          $brand->slug = $request->slug;
          $brand->status = $request->status;
          $brand->save();
            session()->flash('success', 'Brand added successfully');
            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id, Request $request){
        $brand = Brand::find($id);

        if(empty($brand)){
            $request->session()->flash('error','Record Not Found');
            return redirect()->route('brands.index');
        }

        return view('admin.brands.edit',['brand'=>$brand]);

    }

    public function update($id, Request $request){
        $brand = Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error','Record Not Found');
            return response()->json([
                'status'=>false,
                'notFound' => true,
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
        ]);
        if ($validator->passes()) {
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();
            session()->flash('success', 'Brand update successfully');
            return response()->json([
                'status' => true,
                'message' => 'Brand update successfully',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request){
        $brandId = $request->id;
        $brand = Brand::find($brandId);

        if($brand == null){
            session()->flash('error', 'Brand not found');
            return response()->json([
                'status'=>false,
            ]);
        }

        $brand->delete();
        session()->flash('success', 'Brand deleted successfully.');
        return response()->json([
            'status' => true,
        ]);

    }
}
