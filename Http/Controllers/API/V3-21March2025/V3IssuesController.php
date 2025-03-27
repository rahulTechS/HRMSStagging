<?php
namespace App\Http\Controllers\API\V3;

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
use App\Models\Entry\Employee;
use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee_Leaves\RequestedLeavesLog;
use App\Models\Reportissue\Reportissue;
use DateTime;
use Crypt;

class V3IssuesController extends Controller
{
	
	public function issuesType(Request $request)
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
							$result['message'] = "Sucessfull.";
				$changeSalaryList = array();
				$changeSalaryList[0]['Values'] = 'Change Salary';
				$changeSalaryList[1]['Values'] = 'Warning Letter';
				$changeSalaryList[2]['Values'] = 'Leave';
				$changeSalaryList[3]['Values'] = "Attendance";
				
				$changeSalaryList[0]['Keys'] = 'change_salary';
				$changeSalaryList[1]['Keys'] = 'w_letter';
				$changeSalaryList[2]['Keys'] = 'leave';
				$changeSalaryList[3]['Keys'] = 'attendance';
				
				$result['result'] = $changeSalaryList;
					
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
	
	
	public function issuesList(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$reportMod = Reportissue::where("user_id",$empId)->get();
					$reportModCount = Reportissue::where("user_id",$empId)->get()->count();
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				$issueList = array();
				$index =0;
				foreach($reportMod as $report)
				{
					$issueList[$index]['issuesType'] = $report->module;
					$issueList[$index]['comment'] = $report->comment;
					$issueList[$index]['CreatedAt'] = $report->created_at;
					$issueList[$index]['Status'] = 'Pending';
					$index++;
					
				}
				
				$result['result'] = $issueList;
				$result['resultCount'] = $reportModCount;
				$result['resultKeyTitle']['issuesType'] = 'Issue Type';
				$result['resultKeyTitle']['comment'] = 'Comment';
				$result['resultKeyTitle']['CreatedAt'] = 'Created At';
				$result['resultKeyTitle']['Status'] = 'Status';
					
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
	
	
	public function PostReportissues(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['issuesType']) && $requestParameters['issuesType'] != '' && isset($requestParameters['comments']) && $requestParameters['comments'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$issuesType = $requestParameters['issuesType'];
			$comments = $requestParameters['comments'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
			
					$saveObj = new Reportissue();
					$saveObj->user_id = $empId;
					$saveObj->comment = $comments;
					$saveObj->module = $issuesType;
					$saveObj->from_area = 'APP';
					$saveObj->save();
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