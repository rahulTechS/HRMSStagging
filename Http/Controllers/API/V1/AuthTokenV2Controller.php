<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\API\APIAuth;
use App\Models\API\V2\DeviceTokenFrontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee\EmpAppAccess;
use App\Models\Employee\Employee_details;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;
use App\Models\KYCProcess\KYCProcess;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use DateTime;
use Crypt;

class AuthTokenV2Controller extends Controller
{
	
	
	public function GetDeviceToken(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['DeviceToken']) && $requestParameters['DeviceToken'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['deviceType']) && $requestParameters['deviceType'] != '')
		{
			$empId = $requestParameters['empId'];
			/*
			*check already exist
			*/
			$existObj =  DeviceTokenFrontend::where("emp_id",$empId)->first();
			if($existObj != '')
			{
				$existId = $existObj->id;
				$updateObj = DeviceTokenFrontend::find($existId);
				$updateObj->device_token = $requestParameters['DeviceToken'];
				$updateObj->device_type = $requestParameters['deviceType'];
				$updateObj->status = 1;
				
				
				if($updateObj->save())
				{
					$result['responseCode'] = 200;
					$result['message'] = "Successfully.";
				}
				else
				{
					$result['responseCode'] = 300;
					$result['message'] = "issue to save.";
				}
				/*
				*check already exist
				*/
			}
			else
			{
			
				$saveObj = new DeviceTokenFrontend();
				$saveObj->device_token = $requestParameters['DeviceToken'];
				$saveObj->device_type = $requestParameters['deviceType'];
				$saveObj->emp_id = $empId;
				$saveObj->status = 1;
				if($saveObj->save())
				{
					$result['responseCode'] = 200;
					$result['message'] = "Successfully.";
				}
				else
				{
					$result['responseCode'] = 300;
					$result['message'] = "issue to save.";
				}
			}
		}
		else
		{	
				$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
				
		}
		
		
		return response()->json($result);
	}
}