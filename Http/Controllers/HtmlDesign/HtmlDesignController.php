<?php

namespace App\Http\Controllers\HtmlDesign;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\RMProfile\RMPerformanceStatus;
use App\Models\RMProfile\RMDetails;
use App\Models\RMProfile\ENBDRMProfile;

use App\Models\MIS\ENBDCardsMisReport;
use App\Http\Controllers\MIS\MisController;
use App\Models\MIS\MainMisReport;
use App\Models\SEPayout\AgentPayout;



class HtmlDesignController extends Controller
{
  
			
	public function HtmlDesign()
	{
	return view("HtmlDesign/htmldesign");
	   	
	}
	

			
}
