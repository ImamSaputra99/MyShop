<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with(['parent'])->orderBy('created_at','DESC')->paginate(10);
        $parent = Category::getParent()->orderBy('name','ASC')->get();

        return view('categories.index', compact('category','parent'));
    }

    public function store(Request $req)
    {
        $this->validate($req,[
            'name' => 'required|string|max:50|unique:categories'
        ]);

        $req->request->add(['slug' => $req->name]);

        Category::create($req->except('_token'));
        return redirect(route('category.index'))->with(['success' => 'Category Baru Ditambahkan']);
    }

    public function edit($id)
    {
        $category = Category::find($id);
        $parent = Category::getParent()->orderBy('name','ASC')->get();
        return view('categories.edit', compact('category', 'parent'));
    }

    public function update(Request $req, $id)
    {
        $this->validate($req,[
            'name' => 'required|string|unique:categories,name' .$id
        ]);

        $category = Category::find($id);
        $category->update([
            'name' => $req->name,
            'parent_id' => $req->parent_id
        ]);
    }

    public function destroy($id)
    {
        $category = Category::withCount(['child'])->find($id);
        if($category->child_count == 0 && $category->product_count == 0){
            $category->delete();
            return redirect (route('category.index'))->with(['success' => 'kategori Dihapus']);
        }
        return redirect(route('category.index'))->with(['error'=> 'kategori ini memiliki Anak kategori']);
    }
}
