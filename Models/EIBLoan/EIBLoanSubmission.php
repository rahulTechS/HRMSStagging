<?php

namespace App\Models\EIBLoan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EIBLoanSubmission extends Model
{
    use SoftDeletes;
    protected $table = 'EIB_loan_submission';
}
