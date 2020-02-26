<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Customer;
use App\Province;

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

    public function verifyCustomerRegistration($token)
    {
        $customer = Customer::where('active_token', $token)->first();
        if($customer){
            $customer->update([
                'active_token' => null,
                'status' => 1
            ]);

            return redirect(route('customer.login'))->with(['success' => 'verifikasi email berhasil, Silahkan logih']);
        }

        return redirect(route('customer.login'))->with(['error' => 'Invalid Verifikasi token']);
    }


    public function customerSettingForm()
    {
        $customer = auth()->guard('customer')->user()->load('district');
        $provinces = Province::orderBy('name','ASC')->get();
        
        return view('ecommerce.setting', compact('customer','provinces'));
    }

    public function customerUpdateProfile(Request $req)
    {
        $this->validate($req,[
            'name' => 'required',
            'phone_number' => 'required|max:14',
            'address' => 'required|string',
            'district' => 'required',
            'password' => 'nullable|string|min:6'
        ]);

        $user = auth()->guard('customer')->user();

        $data = $req->only('name','phone_number','address','district_id');
        if($req->password != ''){
            $data['password'] = $req->password;
        }

        $user->update($data);
        return redirect()->back()->with(['success' => 'profil berhasil diperbaharui']);
    }

}
