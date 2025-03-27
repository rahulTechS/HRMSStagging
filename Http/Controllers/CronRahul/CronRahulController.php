<?php
namespace App\Http\Controllers\CronRahul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;

use Illuminate\Support\Facades\Validator;

use DateTime;
use Session;
use App\Models\cronWork\CandidatesVisaCompleteData;


use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\VisaPermission;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Employee\Employee_details;
use App\Models\Dashboard\MasterPayout;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;
use App\User;
use App\Models\SEPayout\SalaryStruture;
use App\Models\cronWork\OfficialEmailDetails;
use App\Models\Employee\Employee_attribute;
use App\Models\Common\MashreqLoginMIS;

class CronRahulController extends Controller
{
	public function updateVisaDataCandidate()
	{
	
		$documentCDetails = DocumentCollectionDetails::where("visa_process_status",4)->where("backout_status",1)->get();
		
		foreach($documentCDetails as $doc)
		{
			$totalCost = Visaprocess::where("document_id",$doc->id)->sum("cost");
			$totalfine = Visaprocess::where("document_id",$doc->id)->sum("cost_fine");
			$stampingIds = VisaPermission::where("title","Stamping")->first();
			$stampingIdsArray = explode(",",$stampingIds->stageid);
			$stampingDetailsVisa = Visaprocess::whereIn("visa_stage",$stampingIdsArray)->where("document_id",$doc->id)->first();
			$stampingDate = '';
			if($stampingDetailsVisa != '')
			{
				$stampingDate = $stampingDetailsVisa->closing_date;
				$stampingid = $stampingDetailsVisa->visa_stage;
			}
			$recurId = $doc->recruiter_name;
			$recruiterData = RecruiterDetails::where("id",$recurId)->first();
			$recruName = '';
			if($recruiterData != '')
			{
				$recruName = $recruiterData->name;
			}
			$employee_id = $doc->employee_id;
			$empName = '';
			if($employee_id != '' && $employee_id != NULL)
			{
				$empD = Employee_details::where("emp_id",$employee_id)->first();
				$empName = $empD->emp_name;
			}
			 
		
			$createObj = new CandidatesVisaCompleteData();
			$createObj->employee_id = $employee_id;
			$createObj->employee_name = $empName;
			$createObj->document_id = $doc->id;
			$createObj->stamping_date = date("Y-m-d",strtotime($stampingDate));
			$createObj->stamping_visa_id = $stampingid;
			$createObj->recruiter_id = $recurId;
			$createObj->recruiter_name = $recruName;
			$createObj->cost = $totalCost;
			$createObj->fine = $totalfine;
			$createObj->save();
		}
		echo "Yes";
		exit;
	}


  public function updateEmpIdToVisaCandidate()
  {
	  	echo "yes";
		exit;
	  $nullEmp = CandidatesVisaCompleteData::get();
	  /* echo count($nullEmp);
	  exit; */
	  foreach($nullEmp as $candidate)
	  {
			$empIdData  = Employee_details::where("document_collection_id",$candidate->document_id)->first();
			if($empIdData != '')
			{
				$updateObj = CandidatesVisaCompleteData::find($candidate->id);
				$updateObj->employee_id = $empIdData->emp_id;
				$updateObj->employee_name = $empIdData->emp_name;
				$updateObj->save();
			}
	  }
	  echo "done";
	  exit;
  }
  
  public function updateRecruiterMasterPayment()
  {
	  $masterPayoutEmps = MasterPayout::whereNull('recruiter_id')->get();
	  foreach($masterPayoutEmps as $emps)
	  {
		  $employee_id = $emps->employee_id;
		  if($employee_id != NULL && $employee_id != '')
		  {
			$empData = Employee_details::where("emp_id",$employee_id)->first();
			if($empData != '')
			{
				$recruiter = $empData->recruiter;
				if($recruiter != NULL && $recruiter != '')
				{
					$updateObj = MasterPayout::find($emps->id);
				$updateObj->recruiter_name = $this->getrecruiterName($recruiter);
				$updateObj->recruiter_id = $recruiter;
				$updateObj->recruiterCat = $this->getrecruiterCat($recruiter);
				$updateObj->save();
				}
			}
		  }
	  }
  }
  
  protected function getrecruiterName($recruiter)
	{
		$rdata = RecruiterDetails::where("id",$recruiter)->first();
		if($rdata != '')
		{
		 return $rdata->name;
			
		}
		else
		{
			return ''; 
		}
	}
	
	protected function getrecruiterCat($recruiter)
	{
		$rdata = RecruiterDetails::where("id",$recruiter)->first();
		if($rdata != '')
		{
			$r = $rdata->recruit_cat;
			if($r != '' && $r != NULL)
			{
				return RecruiterCategory::where("id",$r)->first()->name;
			}
			else
			{
				return '';
			}
		}
		else
		{
			return ''; 
		}
	}
	
	
	 public function updateOthersDetailsToVisaCandidate()
	  {
		  $emps = CandidatesVisaCompleteData::get();
		  /* echo count($nullEmp);
		  exit; */
		  foreach($emps as $candidate)
		  {
				$empIdData  = Employee_details::where("document_collection_id",$candidate->document_id)->first();
				if($empIdData != '')
				{
					$updateObj = CandidatesVisaCompleteData::find($candidate->id);
					$updateObj->dept_id = $empIdData->dept_id;
					if($empIdData->job_function != '' && $empIdData->job_function != NULL)
					{
						$updateObj->job_function_name = $this->getJobFunctionName($empIdData->job_function);
					}
					$updateObj->job_function_id = $empIdData->job_function;
					if($empIdData->designation_by_doc_collection != '' && $empIdData->designation_by_doc_collection != NULL)
					{
					$updateObj->designation = $this->designationName($empIdData->designation_by_doc_collection);
					}
					$updateObj->onboard_status = 2;
					$updateObj->offboard_status = $empIdData->offline_status;
					$updateObj->save();
				}
		  }
		  echo "done";
		  exit;
	  }
	  
	  protected function designationName($designationId)
	  {
		  $data = Designation::where("id",$designationId)->first();
		  if($data != '')
		  {
			  return $data->name;
		  }
		  else
		  {
			  return '';
		  }
	  }
	  
	  protected function getJobFunctionName($jobId)
	  {
		  $jobfunction = JobFunction::where("id",$jobId)->first();
		  if($jobfunction != '')
		  {
			  return $jobfunction->name;
		  }
		  else
		  {
			  return '';
		  }
		  
	  }
	  
	/*
	*cron run on manage employee to 
	*update target from salary_struture table
	*/
	public function updateTargetOfEMPOnboarded()
	{
	
		$datas = Employee_details::whereNull("target_status")->get();
		
		foreach($datas as $data)
		{
			$deptId = $data->dept_id;
			$actual_salary = $data->actual_salary;
			$targetObj = SalaryStruture::where("bank_id",$deptId)->where("salary",$actual_salary)->first();
			if($targetObj != '')
			{
				$updateEmp = Employee_details::find($data->id);
				$updateEmp->target = $targetObj->target;
				$updateEmp->target_status = 2;
				$updateEmp->save();
			}
			else
			{
				$updateEmp = Employee_details::find($data->id);
				
				$updateEmp->target_status = 3;
				$updateEmp->save();
			}
		}
		echo "done";
		exit;
	}
	
public function updateMobileCBD()
{
	$datss = DepartmentFormEntry::where("form_id",2)->whereNull("mob_status")->get();
	foreach($datss as $dat)
	{
		$da = DepartmentFormChildEntry::select("attribute_value")->where("parent_id",$dat->id)->where("attribute_code","customer_mobile")->first();
		
		if($da != '')
		{
			$update= DepartmentFormEntry::find($dat->id);
			$update->customer_mobile = $da->attribute_value;
			$update->mob_status =1;
			$update->save();
		}
	}
	echo "done";
	exit;
}

public function updateOfficialEmail()
{
	$datas = OfficialEmailDetails::where("status",1)->get();
	
	
	foreach($datas as $data)
	{
		$empDetails = Employee_details::where("emp_id",$data->emp_id)->first();
		if($empDetails != '')
		{
			$empUpdate = Employee_details::find($empDetails->id);
			$empUpdate->official_email = $data->officical_email;
			if($empUpdate->save())
			{
				$attrexist = Employee_attribute::where("emp_id",$data->emp_id)->where("attribute_code","official_email")->first();
				if($attrexist != '')
				{
					$attrupdate = Employee_attribute::find($attrexist->id);
					$attrupdate->attribute_values = $data->officical_email;
					$attrupdate->save();
				}
				else
				{
					$attrCreate = new Employee_attribute();
					$attrCreate->attribute_values = $data->officical_email;
					$attrCreate->attribute_code = 'official_email';
					$attrCreate->emp_id = $data->emp_id;
					$attrCreate->dept_id = $empDetails->dept_id;
					$attrCreate->status = 1;
					$attrCreate->save();
				}
				
				$update = OfficialEmailDetails::find($data->id);
				$update->status= 2;
				$update->save();
			}
			else
			{
				$update = OfficialEmailDetails::find($data->id);
				$update->status= 3;
				$update->save();
			}
		}
		else
		{
			$update = OfficialEmailDetails::find($data->id);
				$update->status= 3;
				$update->save();
		}
	}
	echo "done";
	exit;
}

public function updateMashreqMissingLoginFile()
{
	$departModels = DepartmentFormEntry::whereNull("login_check_status")->get();
	foreach($departModels as $model)
	{
		$loginDone = MashreqLoginMIS::where("ref_no",$model->ref_no)->first();
		if($loginDone != '')
		{
		$updateObj = DepartmentFormEntry::find($model->id);
		$updateObj->MissingLogin = 2;
		$updateObj->login_check_status = 2;
		$updateObj->save();
		}
		else
		{
			$updateObj = DepartmentFormEntry::find($model->id);
		
			$updateObj->login_check_status = 3;
			$updateObj->save();
		}
	}
	echo "done";
	exit;
}

public function APICheck1()
{
	$str = JobFunction::all();
	return response()->json($str);
}
}