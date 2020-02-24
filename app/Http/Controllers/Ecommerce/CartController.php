<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Product;
use App\Province;
use App\District;
use App\City;
use App\Customer;
use App\OrderDetails;
use App\Order;
use DB;


class CartController extends Controller
{
    private function getCarts()
    {
        $carts = json_decode(request()->cookie('dw-carts'), true);
        $carts = $carts != '' ? $carts:[];
        return $carts;
    }
    public function addToCart(Request $req)
    {
        $this->validate($req,[
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer'
        ]);

        $carts = $this->getCarts();
        
        if($carts && array_key_exists($req->product_id, $carts)) {
            $carts[$req->product_id]['qty'] += $req->qty;
        }else{
            $product = Product::find($req->product_id);
            $carts[$req->product_id] = [
                'qty' => $req->qty,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'product_image' => $product->image,
            ];
        }
     
        $cookie = cookie('dw-carts',json_encode($carts),2800);
     
        return redirect()->back()->cookie($cookie);
    }

    public function listCart()
    {
       
       $carts = $this->getCarts();
       
        $subtotal = collect($carts)->sum(function($q){
            
            return $q['qty'] * $q['product_price']; 
        });
        return view('ecommerce.cart',compact('carts','subtotal'));
    }

    public function updateCart(Request $req)
    {
       
        $carts = $this->getCarts();
      
        foreach($req->product_id as $key => $row){
            if($req->qty[$key]==0){
              
                unset($carts[$row]);
            }else{
                
                $carts[$row]['qty'] = $req->qty[$key];
            }
        }
        $cookie = cookie('dw-carts', json_encode($carts),2800);
       
        return redirect()->back()->cookie($cookie);
    }
    public function checkout()
    {
        $provinces = Province::orderBy('created_at','DESC')->get();
        $carts = $this->getCarts();
        $subtotal = collect($carts)->sum(function($q){
            return $q['qty'] * $q['product_price'];
        });

      return view('ecommerce.checkout', compact('provinces', 'carts','subtotal'));
    }

    public function getCity()
    {
        $cities = City::where('province_id', request()->province_id)->get();
        return response()->json(['status' => 'success', 'data' => $cities]);

    }

    public function getDistrict()
    {
        $districts = District::where('city_id', request()->city_id)->get();
        return response()->json(['status' => 'success', 'data' => $districts]);
    }

    public function processCheckout(Request $req)
    {
        $this->validate($req,[
            'customer_name' => 'required|string',
            'customer_phone' => 'required',
            'email'=>'required|email',
            'customer_address' => 'required',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id'
        ]);

        DB::beginTransaction();
        
        try{
            $customer = Customer::where('email', $req->email)->first();

            if (!auth()->check() && $customer) {
              
                return redirect()->back()->with(['error' => 'Silahkan Login Terlebih Dahulu']);
            }

            $carts = $this->getCarts();
            $subtotal = collect($carts)->sum(function($q){
                return $q['qty'] * $q['product_price'];
            });

            $customer = Customer::create([
                'name' => $req->customer_name,
                'email' => $req->email,
                'password' => $password,
                'phone_number' => $req->customer_phone,
                'address' => $req->customer_address,
                'district_id' => $req->district_id,
                'active_token' => Str::random(30),
                'status' => false
            ]);
           
            $order = Order::create([
                'invoice' => Str::random(4).'-' .time(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $req->customer_phone,
                'customer_address' => $req->customer_address,
                'district_id' => $req->district_id,
                'subtotal' => $subtotal
            ]);

            foreach($carts as $row){
                
                $product = Product::find($row['product_id']);

                OrderDetails::create([
                    'order_id'=> $order->id,
                    'product_id' => $row['product_id'],
                    'price' => $row['product_price'],
                    'qty' => $row['qty'],
                    'weight' => $product->weight
                ]);
            }

            DB::commit();

            $carts = [];
            $cookie = cookie('dw-carts',json_decode($carts), 2880);
            return redirect(route('front.finish_checkout', $order->invoice))->cookie($cookie);
        }catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function checkoutFinish($invoice)
    {
        $order = Order::with(['district.city'])->where('invoice', $invoice)->first();
        return view('ecommerce.checkout_finish', compact('order'));
    }

   
}
