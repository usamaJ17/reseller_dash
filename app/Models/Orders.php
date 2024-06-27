<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $fillable =[
        'commission',
        'status',
        'customer_name',
        'total_amount',
        'reseller_id',
        'order_id'
    ];
}
