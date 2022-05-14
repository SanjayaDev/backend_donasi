<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Donatur extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'avatar'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    protected function password() : Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::make($value)
        );
    }

    public function getAvatarAttribute($value)
    {
        if (!empty($value)) :
            return asset('storage/donaturs/' . $value);
        else : 
            return 'https://ui-avatars.com/api/?name=' . str_replace(' ', '+', $this->name) . '&background=4e73df&color=ffffff&size=100';
        endif;
    }
}
