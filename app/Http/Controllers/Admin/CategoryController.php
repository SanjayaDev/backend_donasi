<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->when(\request()->q, function($categories) {
            $categories = $categories->where('name', 'like', '%'. request()->q . '%');
        })->paginate(10);

        return view('admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => "required|unique:categories",
            "image" => "required|image|mimes:jpeg,jpg,png|max:2000"
        ]);

        // Upload Image
        $image = $request->file('image');
        $image->storeAs("public/categories", $image->hashName());

        // Save to DB
        $category = Category::create([
            "image" => $image->hashName(),
            "name" => $request->name,
            "slug" => Str::slug($request->name, "-"),
        ]);

        if ($category) {
            return redirect()->route('admin.category.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }

        return redirect()->route('admin.category.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $this->validate($request, [
            "name" => "required|unique:categories,name,".$category->id,
            "image" => "nullable|image|mimes:jpeg,jpg,png|max:2000"
        ]);

        // Check if image is empty
        if ($request->file("image") == "") {
            $category->update([
                "name" => $request->name,
                "slug" => Str::slug($request->name, "-"),
            ]);
        } else {
            // Delete old image
            Storage::disk("local")->delete("public/categories/".\basename($category->image));

            // Upload new image
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            $category->update([
                'image'  => $image->hashName(),
                'name'   => $request->name,
                'slug'   => Str::slug($request->name, '-')
            ]);
        }

        if ($category) {
            return redirect()->route('admin.category.index')->with(['success' => 'Data Berhasil Diupdate!']);
        }

        return redirect()->route('admin.category.index')->with(['error' => 'Data Gagal Diupdate!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        Storage::disk("local")->delete("public/categories/".\basename($category->image));
        $category->delete();

        if ($category) {
            return response()->json([
                'status' => 'success'
            ]);
        }

        return response()->json([
            'status' => 'error'
        ]);
    }
}
