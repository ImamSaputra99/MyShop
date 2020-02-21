<?php

namespace App\Http\Controllers;
use App\Product;
use App\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use File;
use App\Jobs\ProductJob;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::with(['category'])->orderBy('created_at','DESC');
        if(request()->q != ''){
            $product = $product->where('name','LIKE','%'. request()->q . '%');
        }

        $product = $product->paginate(10);
        return view('products.index', compact('product'));
    }

    public function create()
    {
        $category = Category::orderBy('name','DESC')->get();
        return view('products.create', compact('category'));
    }

    public function store(Request $req)
    {
        $this->validate($req,[
            'name' => 'required|string|max:30',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer',
            'weight' => 'required|integer',
            'image' => 'required|image|mimes:png,jpeg,jpg'
        ]);

        if($req->hasFile('image')){
            $file = $req->file('image');
            $filename = time() . Str::slug($req->name). '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products',$filename);

            $product = Product::create([
                'name' => $req->name,
                'slug' => $req->name,
                'category_id' => $req->category_id,
                'description' => $req->description,
                'image' => $filename,
                'price' => $req->price,
                'weight' => $req->weight,
                'status' => $req->status
            ]);

            return redirect(route('product.index'))->with(['success' => 'Produk Ditambahkan']);
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        File::delete(storage_path('app/public/products'. $product->image));
        $product->delete();
        return redirect(route('product.index'))->with(['success' => 'Produk telah dihapus']);
    }

    public function edit($id)
    {
        $product = Product::find($id);
        $category = Category::orderBy('name','DESC')->get();
        return view('products.edit', compact('product','category'));
    }

    public function update(Request $req, $id)
    {
        $this->validate($req,[
            'name' => 'required|max:30',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer',
            'weight' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png'
        ]);

        $product = Product::find($id);
        $filename = $product->image;

        if($req->hasFile('image')){
            $file = $req->file('image');
            $filename = time() . Str::slug($req->name). '.' . $file->getClientOriginalExtension();

            $file->storesAs('public/product', $filename);
            File::delete(storage_path('app/public/products' . $product->image));
        }

        $product->update([
            'name' => $req->name,
            'description' => $req->description,
            'category_id' => $req->category_id,
            'price' => $req->price,
            'weight' => $req->weight,
            'image' => $filename
        ]);

        return redirect(route('product.index'))->with(['success' => 'Produk telah update']);
    }

    public function massUploadForm()
    {
        $categoru = Category::orderBy('name','DESC')->get();
        return view('products.bulk', compact('category'));
    }
    public function massUpload()
    {
        $this->validate($req,[
            'category_id' => 'required|exist:categories,id',
            'file' => 'required|mimes:png,jpg,jpeg'
        ]);

        if($req->hasGile('image')){
            $file = $req->file('file');
            $filename = time() . '-product.' .$file->getClientOriginalExtension();
            $file->storeAs('public/upload' , $filename);

            ProductJob::dispatch($req->category_id, $filename);
            return redirect()->back()->with(['success' => 'Upload Produk Dijadwalkan']);
        }
    }


}
