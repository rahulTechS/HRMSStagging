<?php

namespace App\Http\Controllers\DashboardManagement;
require_once "/srv/www/htdocs/core/autoload.php";
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;

use Carbon\Carbon;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\Onboarding\DocumentCollectionDetails;
use File;
class DashboardManagementController extends Controller
{
   public function managementDashboard(Request $request)
   {
	   $location = $request->location;
	   $locationTxt = '';
	   if($location == "DXB")
	   {
		   $locationTxt = 'DUBAI';
	   }
	   else
	   {
		   $locationTxt = 'ABU DHABI';
	   }
	   $collection = Employee_details::groupBy('designation_by_doc_collection')
						->selectRaw('count(*) as current_head_count , designation_by_doc_collection')->where("work_location",$locationTxt)
						->get();
	  return view("dashboardManagement/managementDashboard",compact('collection','location'));
   }
   
   public function dashboardHeadCountPlaning(Request $request)
   {
	   $current_head_count = $request->current_head_count;
	   $designation_id = $request->designation_id;
	   $work_location = $request->location;
	   $jobopening = JobOpening::where("designation",$designation_id)->where("location",$work_location)->first();
	    return view("dashboardManagement/dashboardHeadCountPlaning",compact('jobopening','current_head_count','work_location'));
   }
   
   public static function checkforBlockHeadCountPlaning($current_head_count,$designation_id,$location)
   {
	   
	   $jobopening = JobOpening::where("designation",$designation_id)->where("location",$location)->first();
	  return $jobopening;
	  
   }
   
   public static function getdepartmentName($deptId)
   {
	   return Department::where("id",$deptId)->first()->department_name;
   }
   public static function getMTDOnboarding($jobOpeningId)
   {
	   return DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->get()->count();
   }
   
   public static function getPreVisaOnboardingInComplete($jobOpeningId)
   {
	   return DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->get()->count();
   }
   public static function getDOJExpected($jobOpeningId)
   {
	   $mtd = DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->get()->count();
	   $preVisa = DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->get()->count();
	   return $mtd-$preVisa;
   }
   
   
     public static function getAttrition($jobOpeningId,$work_location) 
	   {
		   $designationId = JobOpening::where("id",$jobOpeningId)->first()->designation;
		    $locationTxt = '';
		   if($work_location == "DXB")
		   {
			   $locationTxt = 'DUBAI';
		   }
		   else
		   {
			   $locationTxt = 'ABU DHABI';
		   }
		   return Employee_details::where("designation_by_doc_collection",$designationId)->where("work_location",$locationTxt)->where("offline_status",2)->get()->count();
		  
		  
	   }
	   
	   public static function getExpectedMonthEndHC($jobOpeningId,$current_head_count,$work_location)
	   {
		       $locationTxt = '';
			   if($work_location == "DXB")
			   {
				   $locationTxt = 'DUBAI';
			   }
			   else
			   {
				   $locationTxt = 'ABU DHABI';
			   }
		   $mtd = DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->get()->count();
			$preVisa = DocumentCollectionDetails::where("job_opening",$jobOpeningId)->where("backout_status",1)->where("onboard_status",1)->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->get()->count();
			$expectedDOJ = $mtd-$preVisa;
			 $designationId = JobOpening::where("id",$jobOpeningId)->first()->designation;
			$attritionCount = Employee_details::where("designation_by_doc_collection",$designationId)->where("work_location",$locationTxt)->where("offline_status",2)->get()->count();
			$updatedcurrent_head_count = $current_head_count-$attritionCount;
			return $updatedcurrent_head_count+$expectedDOJ;
	   }
}
