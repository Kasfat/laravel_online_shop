<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $product = Product::with('product_images')->find($request->id);
        if($product == null){
            return response()->json([
                'status'=>false,
                'message'=> 'Product not found'
            ]);
        }

        if(Cart::count() > 0){
                //product found in cart
            //check if this product already in the cart
            //return as message that product already added in your cart
            // if product not found in the cart, then add product in cart
            $cartContent = Cart::content();
            $productAlreadyExist = false;
            foreach ($cartContent as $item){
                if($item->id == $product->id){
                    $productAlreadyExist = true;
                }
            }

            if($productAlreadyExist == false){
                Cart::add($product->id,$product->title,1,$product->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images->first():'']);
                $status = true;
                $message = $product->title.' Added in cart';
            }else{
                $status = false;
                $message = $product->title.' already added in cart';
            }

        }else{
            //cart is empty
            Cart::add($product->id,$product->title,1,$product->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images->first():'']);
            $status = true;
            $message = $product->title.' Added in cart';
        }
        return response()->json([
            'status'=>$status,
            'message'=> $message
        ]);
    }



    public function cart(){
        $cartContent = Cart::content();
        //dd($cartContent);
        return view('front.cart',['cartContent'=>$cartContent]);
    }


}
