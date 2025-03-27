<?php

namespace App\Models\CompareFeatures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Features extends Model
{
    use SoftDeletes;
    protected $table = 'compare_features';
}
