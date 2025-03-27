<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportsDepartment extends Model
{
    use SoftDeletes;
    protected $table = 'reports_department';
}
