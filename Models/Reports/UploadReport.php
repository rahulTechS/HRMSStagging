<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadReport extends Model
{
    use SoftDeletes;
    protected $table = 'uploadReports';
}
