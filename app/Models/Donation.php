<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice', 'campaign_id', 'donatur_id', 'amount', 'pray', 'snap_token', 'status'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donatur()
    {
        return $this->belongsTo(Donatur::class);
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d-M-Y');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d-M-Y');
    }
}
