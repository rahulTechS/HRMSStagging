<?php

namespace App\Models\EmailTemplate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplates extends Model
{
    use SoftDeletes;
    protected $table = 'email_templates';
}
