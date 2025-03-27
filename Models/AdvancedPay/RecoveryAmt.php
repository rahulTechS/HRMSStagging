<?php

namespace App\Models\AdvancedPay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecoveryAmt extends Model
{
    use SoftDeletes;
    protected $table = 'advancedPayRecoveryAmt';
}
