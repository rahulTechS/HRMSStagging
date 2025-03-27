<?php

namespace App\Http\Controllers\CronJob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\KycDocuments;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\VisaDetails;
use App\Models\Onboarding\DocumentCollectionBackout;
use App\Models\Onboarding\DocumentVisaStageStatus;
use App\Models\Onboarding\IncentiveLetterDetails;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Onboarding\TrainingProcess;
use UserPermissionAuth;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use App\Models\Logs\DocumentCollectionDetailsLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DepartmentPermission;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Onboarding\EmployeeIncrement;
use App\Models\Onboarding\EmployeeOnboardData;
use App\Models\Onboarding\EmployeeOnboardLogdata;
use App\Models\Dashboard\MashreqFinalMTD;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Dashboard\MasterPayout;
use App\Models\Employee\EmpAppAccess;

class CronJobSanjeetController extends Controller
{
public function updateOfflineStatusOnboarding(){
	
	$docdata = RecruiterDetails::get();
	//echo $docdata;
	if($docdata!=''){
		foreach($docdata As $_data){
   $documentValuescv = Employee_details::where("emp_id",$_data->employee_id)->first();
		//echo $documentValuescv->offline_status;
			//print_r($documentValuescv);exit;
			if($documentValuescv!='' && $documentValuescv!=NULL){
				//echo $_data->id;exit;
				$documentCollectionMod = RecruiterDetails::find($_data->id);
				$documentCollectionMod->offline_status=$documentValuescv->offline_status;
				$documentCollectionMod->save();
					//echo $_data->id."<br>";
				
			}
		}
	}
	echo "Data Update";
			
	}
}