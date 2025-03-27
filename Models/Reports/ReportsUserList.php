<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportsUserList extends Model
{
    use SoftDeletes;
    protected $table = 'reports_role_access';
}
