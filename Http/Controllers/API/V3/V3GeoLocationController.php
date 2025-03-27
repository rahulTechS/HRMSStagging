<?php
namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use App\Models\API\APIAuth;
use App\Models\API\V2\DeviceTokenFrontend;
use App\Models\API\V1\GeoLocationAgentDetails;
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

class V3GeoLocationController extends Controller
{
	
	
	public function GeoLocationGetDistance(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$result['responseCode'] = 200;
					$result['message'] = "Successfull.";
					$result['result']['DistanceInterval'] = 50;
					$result['result']['AppVersion'] = 'V2';
					
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
				}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		
		
		
		
		return response()->json($result);
	}
	
public function GeoLocationSave(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['long']) && $requestParameters['long'] != '' && isset($requestParameters['lat']) && $requestParameters['lat'] != '' && isset($requestParameters['altitude']) && $requestParameters['altitude'] != '')
		{
			/* echo "fdsfds";
			exit; */
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$long = $requestParameters['long'];
			$lat = $requestParameters['lat'];
			$altitude = $requestParameters['altitude'];
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$geoSave = new GeoLocationAgentDetails();
					$geoSave->emp_id = $empId;
					$geoSave->long = $long;
					$geoSave->lat = $lat;
					$geoSave->altitude = $altitude;
					if($geoSave->save())
					{
					$result['responseCode'] = 200;
						$result['message'] = "Successfull.";
					}
					else
					{
						$result['responseCode'] = 401;
						$result['message'] = "Issue to Save Geo Location.";
					}
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
				}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		
		
		
		
		return response()->json($result);
}

public function GeoPermission(Request $request)
{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['geopermission']) && $requestParameters['geopermission'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$geopermission = $requestParameters['geopermission'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					EmpAppAccess::where("employee_id",$empId)->update(array("geo_permission"=>$geopermission));
					
					$result['responseCode'] = 200;
						$result['message'] = "Successfull.";
					
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
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