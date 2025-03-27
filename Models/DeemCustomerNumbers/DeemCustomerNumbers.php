<?php

namespace App\Models\DeemCustomerNumbers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeemCustomerNumbers extends Model
{
    use SoftDeletes;
    protected $table = 'deem_customer_numbers';
}
