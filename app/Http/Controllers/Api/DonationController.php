<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /**
     * __construct
     * Set config midtrans
     * 
     * @return void
     */
    public function __construct()
    {
        \Midtrans\Config::$serverKey = \config('services.midtrans.serverKey');
        \Midtrans\Config::$clientKey = \config('services.midtrans.clientKey');
        \Midtrans\Config::$isProduction = \config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized = \config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = \config('services.midtrans.is3ds');
    }

    /**
     * index
     * 
     * @return void
     */
    public function index()
    {
        $donations = Donation::with("campaign")->where("donatur_id", \auth('api')->user()->id)->latest()->paginate(5);

        return \response()->json([
            'success' => TRUE,
            'message' => 'List data donasi : ' . \auth('api')->user()->name,
            'data' => $donations
        ], 200);
    }

    /**
     * Store
     * 
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // dd(\Midtrans\Config::$isProduction);
        DB::transaction(function () use ($request) {
            /**
             * Algorihm create no invoice 
             */
            $length = 10;
            $random = '';

            for ($i = 0; $i < $length; $i++) {
                $random .= \rand(0, 1) ? \rand(0, 9) : \chr(\rand(\ord('a'), \ord('z')));
            }
            $no_invoice = "TRX-" .  Str::upper($random);

            // Get data campaign
            $campaign = Campaign::where("slug", $request->campaign)->first();

            // Insert to donation table
            $donation = Donation::create([
                'invoice' => $no_invoice,
                'campaign_id' => $campaign->id,
                'donatur_id' => \auth('api')->user()->id,
                'amount' => $request->amount,
                'pray' => $request->pray,
                'status' => 'pending',
            ]);

            // Create request snap token midtrans and save snap token
            $payload = [
                'transaction_details' => [
                    'order_id' => $no_invoice,
                    'gross_amount' => $request->amount
                ],
                'customer_details' => [
                    'first_name' => \auth('api')->user()->name,
                    'email' => \auth('api')->user()->email,
                    // 'phone' => \auth('api')->user()->phone
                ]
            ];

            // Request snap token
            $snap_token = Snap::getSnapToken($payload);
            $donation->snap_token = $snap_token;
            $donation->save();

            $this->response['snap_token'] = $snap_token;
        });

        return \response()->json([
            'success' => TRUE,
            'message' => 'Donasi berhasil dibuat',
            $this->response
        ]);
    }

    /**
     * Webhook notification handler
     * 
     * @param mixed $request
     * @return void
     */
    public function notification_handler(Request $request)
    {
        $payload = $request->getContent();
        $notification = \json_decode($payload, TRUE);

        $validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . config('services.midtrans.serverKey'));
        if ($notification->signature_key != $validSignatureKey) {
            return response(['message' => 'Invalid signature'], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $order_id = $notification->order_id;
        $fraud = $notification->fraud_status;

        // Get data donation
        $data_donation = Donation::where('invoice', $order_id)->first();

        switch ($transaction) {
            case 'capture' : 
                if ($type == "credit_card") {
                    if ($fraud == "challenge") {

                        $data_donation->update([
                            'status' => 'pending'
                        ]);

                    } else {

                        $data_donation->update([
                            'status' => 'success'
                        ]);

                    }
                }
                break;
            case 'settlement' :

                $data_donation->update([
                    'status' => 'success'
                ]);    

                break;
            case 'pending' :

                $data_donation->update([
                    'status' => 'pending'
                ]);

                break;
            case 'deny' :

                $data_donation->update([
                    'status' => 'failed'
                ]);

                break;
            case 'expire' : 

                $data_donation->update([
                    'status' => 'expired'
                ]);

                break;
            case 'cancel' :

                $data_donation->update([
                    'status' => 'failed'
                ]);

                break;
        }
    }
}
