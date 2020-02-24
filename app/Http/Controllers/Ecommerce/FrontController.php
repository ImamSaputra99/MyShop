<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Customer;

class FrontController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('ecommerce.index', compact('products'));
    }
    public function product()
    {
        $products = Product::orderBY('created_at','DESC')->paginate(10);

        $categories = Category::with(['child'])->withCount(['child'])->getParent()->orderBy('name','ASC')->get();
        return view('ecommerce.product', compact('products','categories'));
    }
    public function categoryProduct($slug)
    {
        $products = Category::where('slug',$slug)->first()->product()->orderBy('created_at','DESC')->paginate(12);
        return view('ecommerce.product',compact('products'));
    }
    public function show($slug)
    {
        $product = Product::with(['category'])->where('slug',$slug)->first();
        return view('ecommerce.show',compact('product'));
    }

    public function verifyCustomer($customer)
    {
        $customer = Customer::where('active_token', $token)->first();
        if($customer){
            $customer->update([
                'activer_token' => null,
                'status' => 1
            ]);

            return redirect(route('customer.login'))->with(['success' => 'verifikasi email berhasil, Silahkan logih']);
        }

        return redirect(route('customer.login'))->with(['error' => 'Invalid Verifikasi token']);
    }

}
