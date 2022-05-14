<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;


class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            "campaigns" => Campaign::latest()->when(\request()->q, function($campaigns) {
                $campaigns = $campaigns->where('title', 'like', '%'. request()->q . '%');
            })->with("category")->paginate(10),
        ];

        return view("admin.campaign.index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            "categories" => Category::latest()->get(),
        ];

        return \view("admin.campaign.create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        $validate = $this->validate($request, [
            "image" => "required|image|mimes:jpg,png,jpeg",
            "title" => "required|string",
            "category_id" => "required|numeric|exists:categories,id",
            "target_donation" => "required|numeric",
            "max_date" => "required|date_format:Y-m-d",
            "description" => "required|string"
        ]);

        // Upload Image
        $image = $request->file("image");
        $image->storeAs("public/campaigns", $image->hashName());

        $validate["slug"] = Str::slug($request->title, "-");
        $validate["user_id"] = \auth()->user()->id;
        $validate["image"] = $image->hashName();

        $campaign = Campaign::create($validate);

        if ($campaign) {
            return redirect()->route('admin.campaign.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }
        return redirect()->route('admin.campaign.index')->with(['error' => 'Data Gagal Disimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Campaign $campaign)
    {
        $data = [
            "campaign" => $campaign,
            "categories" => Category::latest()->get(),
        ];

        return \view("admin.campaign.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Campaign $campaign)
    {
        $validate = $this->validate($request, [
            "image" => "nullable|image|mimes:jpg,png,jpeg",
            "title" => "required|string",
            "category_id" => "required|numeric|exists:categories,id",
            "target_donation" => "required|numeric",
            "max_date" => "required|date_format:Y-m-d",
            "description" => "required|string"
        ]);

        if ($request->hasFile("image")) {
            // Delete old photo
            Storage::disk("local")->delete("public/campaigns/" . \basename($campaign->image));

            // Upload new photo
            $image = $request->file("image");
            $image->storeAs("public/campaigns", $image->hashName());

            $validate["image"] = $image->hashName();
        }

        $validate["slug"] = Str::slug($request->title, "-");
        $update = $campaign->update($validate);

        if ($update) {
            return redirect()->route('admin.campaign.index')->with(['success' => 'Data Berhasil Diupdate!']);
        }

        return redirect()->route('admin.campaign.index')->with(['error' => 'Data Gagal Diupdate!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign $campaign)
    {
        // Delete old photo
        Storage::disk('local')->delete('public/campaigns/'.basename($campaign->image));

        $delete = $campaign->delete();
        if($delete){
            return response()->json([
                'status' => 'success'
            ]);
        }else{
            return response()->json([
                'status' => 'error'
            ]);
        }
    }
}
