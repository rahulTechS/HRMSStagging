<?php

namespace App\Models\CompareAttributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attributes extends Model
{
    use SoftDeletes;
    protected $table = 'compare_attributes';
}
