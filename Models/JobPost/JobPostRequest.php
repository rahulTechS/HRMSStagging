<?php

namespace App\Models\JobPost;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPostRequest extends Model
{
    use SoftDeletes;
    protected $table = 'job_post_request';
}