<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Donatur;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index()
    {
        $data = [
            "donaturs" => Donatur::count(),
            "campaigns" => Campaign::count(),
            "donations" => Donation::where("status", "success")->sum("amount"),
        ];

        return view("admin.dashboard.index", $data);
    }
}
