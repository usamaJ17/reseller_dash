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
        'pro_det',
        'reseller_id',
        'order_id'
    ];
    public function getProDetAttribute($value)
    {
        return json_decode($value);
    }
}
