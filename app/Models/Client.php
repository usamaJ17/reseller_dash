<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'contact',
        'country_id',
        'state_id',
        'city_id',
        'postal_code',
        'address',
        'reseller_id',
        'country_name',
        'state_name',
        'city_name',
    ];
}
