<?php

namespace App\Models\SalaryCertificate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryCertificate extends Model
{
    use SoftDeletes;
    protected $table = 'salary_certificate_requests';
}
