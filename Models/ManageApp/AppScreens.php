<?php

namespace App\Models\ManageApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppScreens extends Model
{
    use SoftDeletes;
    protected $table = 'app_screens';
}