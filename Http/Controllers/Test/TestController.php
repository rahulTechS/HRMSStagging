<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LoggerFactory;
use Illuminate\Support\Facades\Validator;

use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Company\Department;
use App\User;
use App\Models\Employee_Leaves\RequestedLeavesLog;


class TestController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        //$this->log = $logFactory->setPath('logs/leaves')->createLogger('leaves'); 
    }
	public function Index(Request $request)
	{
        return view("Test/index");
    }


}