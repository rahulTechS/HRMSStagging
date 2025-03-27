<?php

namespace App\Models\AgentTargets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentTarget extends Model
{
    use SoftDeletes;
    protected $table = 'agentTargets';
}
