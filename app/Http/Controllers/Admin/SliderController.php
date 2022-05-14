<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->paginate(5);
        return view("admin.slider.index", compact("sliders"));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "image" => "required|image|mimes:jpeg,jpg,png,gif|max:2048",
            "link" => "required|string"
        ]);

        $image = $request->file("image");
        $image->storeAs("public/sliders", $image->hashName());

        Slider::create([
            "image" => $image->hashName(),
            "link" => $request->link
        ]);

        return \redirect()->route("admin.slider.index")->with(["success" => "Data berhasil disimpan!"]);
    }

    public function destroy(Slider $slider)
    {
        Storage::disk("local")->delete("public/sliders/" . \basename($slider->image));
        $slider->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
