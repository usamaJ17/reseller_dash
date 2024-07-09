<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutMethod extends Model
{
    use HasFactory;
    protected $table = 'payout_method';
    protected $fillable = [
        'user_id',
        'type',
        'mobile_finance_type',
        'bank_name',
        'branch_name',
        'account_holder_name',
        'account_number'
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
