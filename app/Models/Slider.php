<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'image', 'link'
    ];

    public function getImageAttribute($value)
    {
        return asset('storage/sliders/' . $value);
    }
}
