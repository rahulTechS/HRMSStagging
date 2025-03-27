<?php

namespace App\Models\AdvancedPay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvancedPayRequest extends Model
{
    use SoftDeletes;
    protected $table = 'advanced_pay_requests';
}
