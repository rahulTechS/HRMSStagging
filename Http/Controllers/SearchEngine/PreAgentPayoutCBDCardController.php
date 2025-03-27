<?php
namespace App\Http\Controllers\SearchEngine;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\EmpProcess\Emp_joining_data;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\MIS\WpCountries;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Recruiter\Designation;
use App\Models\Job\JobOpening;
use Session;
use App\Models\EmpProcess\EmpChangeLog;
use App\Models\Entry\Employee;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\JobFunction\JobFunction;
use App\Models\SEPayout\AgentPayoutByRange;
use App\Models\SearchEngine\PreAgentPayoutCBDCard;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqLoginMIS;
use App\Models\cronWork\CronRunLogs;
use App\Models\Bank\CBD\CBDBankMis;



class PreAgentPayoutCBDCardController extends Controller
{
    
	public function UpdatePrePayoutCBDCardData(){
		
		/*
		*Cron Logs works
		*/
		$createCronLogs = new CronRunLogs();
		$createCronLogs->title = 'SearchEngine-CBDCard';
		$createCronLogs->save();
		
		/*
		*Cron Logs works
		*/
		//$DepartmentData=DepartmentFormEntry::where("form_id",1)->whereNotNull('application_id')->whereNull('search_engine_status')->where('submission_date','>=',date("Y").'-'.date("m").'-01')->get();
		
		$_DepartmentData=DepartmentFormEntry::where("form_id",2)->whereNotNull('ref_no')->whereNull('search_engine_status')->orderBy("id","DESC")->first();
		//print_r($_DepartmentData);//exit;
		/* $DepartmentData=DepartmentFormEntry::where("form_id",1)
						->whereNotNull('application_id')
						->whereNull('search_engine_status')
						->where('submission_date','>=','2023-11-01')
						->get(); */
		
		if($_DepartmentData != ''){
			//$i=0;
			
				/*
				*check for employee existing
				*start code
				*/
				$agentDataExist=Employee_details::where("emp_id",$_DepartmentData->emp_id)->first();
				if($agentDataExist != '')
				{
				
				/*
				*check for employee existing
				*end code
				*/
				$PreAgentPayoutMashreqCardMOd = PreAgentPayoutCBDCard::where('application_id',$_DepartmentData->application_id)->first();							
				if($PreAgentPayoutMashreqCardMOd!='')
				{
				$prepayoutOBJ = PreAgentPayoutCBDCard::find($PreAgentPayoutMashreqCardMOd->id);
				//echo "h1<br>";
					
				}
				else{
					$prepayoutOBJ =new PreAgentPayoutCBDCard();
				}
				
				$agentData=Employee_details::where("emp_id",$_DepartmentData->emp_id)->first();
				if($agentData!=''){
					$agentname=$agentData->emp_name;
					$bankcode=$agentData->source_code;
					$tl_id=$agentData->tl_id;
					$doj=$agentData->doj;
					
				}
				else{
					$agentname='';
					$bankcode='';
					$tl_id='';
					$doj='';
				}
				if($doj==''){
				$empattributesMod = Employee_attribute::where('emp_id',$_DepartmentData->emp_id)->where('attribute_code','DOJ')->first();
				 //print_r($empattributesMod);exit;
				if(!empty($empattributesMod)){				 
				$doj = $empattributesMod->attribute_values;
				}else{
				$doj='';
				}
				}
				
				$Recruiter =RecruiterDetails::where("id",$agentData->recruiter)->first();
			  
					  if($Recruiter != '')
					  {
						
					  $RecruiterDetails= $Recruiter->name;
					  $RecruiterDetailscat= $Recruiter->recruit_cat;
					  }
					  else
					  {
					  $RecruiterDetails= '';
					  $RecruiterDetailscat='';
					  }
					  $bookingdata=CBDBankMis::where("ref_no",$_DepartmentData->ref_no)->whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
					  if($bookingdata>0){
						  $bookingmis=2;
					  }
					  else{
						  $bookingmis=1;  
					  }
					  
				$prepayoutOBJ->dept_id=49;
				$prepayoutOBJ->agent_product="CARD";
				$prepayoutOBJ->agent_name=$agentname;
				$prepayoutOBJ->agent_bank_code=$bankcode;
				$prepayoutOBJ->tl_name=$_DepartmentData->team;
				$prepayoutOBJ->tl_id=$tl_id;
				$prepayoutOBJ->application_id=$_DepartmentData->ref_no;
				$prepayoutOBJ->ref_no=$_DepartmentData->ref_no;
				if($bookingmis == 1)
				{
					$prepayoutOBJ->submission_status=$_DepartmentData->form_status;
					$prepayoutOBJ->use_cron=1;
				}
				else
				{
					$prepayoutOBJ->submission_status="Booked";
					$prepayoutOBJ->use_cron=2;
				}
				$prepayoutOBJ->month=date("m");
				$prepayoutOBJ->year=date("Y");
				$prepayoutOBJ->end_sales_time=date("m").'-'.date("Y");
				$prepayoutOBJ->doj=$doj;
				$prepayoutOBJ->employee_id=$_DepartmentData->emp_id;
				$prepayoutOBJ->recruiter_id=$agentData->recruiter;
				$prepayoutOBJ->recruiter_name=$RecruiterDetails;
				$prepayoutOBJ->recruiter_category=$RecruiterDetailscat;
				$prepayoutOBJ->booking_status=$bookingmis;	
				$prepayoutOBJ->submission_date=date("Y-m-d",strtotime($_DepartmentData->application_date));					
				if($prepayoutOBJ->save())
				{
					/*
					*update Status in mashreq internal mis
					*start code
					*/
					$updateMainM = DepartmentFormEntry::find($_DepartmentData->id);
					$updateMainM->search_engine_status =2;
					$updateMainM->save();
					/*
					*update Status in mashreq internal mis
					*end code
					*/
				}
				else
				{
					/*
					*update Status in mashreq internal mis
					*start code
					*/
					$updateMainM = DepartmentFormEntry::find($_DepartmentData->id);
					$updateMainM->search_engine_status =3;
					$updateMainM->save();
					/*
					*update Status in mashreq internal mis
					*end code
					*/
				}
				
				}
				else
				{
					$updateMainM = DepartmentFormEntry::find($_DepartmentData->id);
					$updateMainM->search_engine_status =4;
					$updateMainM->save();
				}
			
		
		
		
	}
	echo "done1";exit;
	}
	
	public function calculateVintageAgentPrePayoutCBDData()
			{
				/*
				*Cron Logs works
				*/
				$createCronLogs = new CronRunLogs();
				$createCronLogs->title = 'SearchEngine-Vintage';
				$createCronLogs->save();
				
				/*
				*Cron Logs works
				*/
				$agentPayoutMod = PreAgentPayoutCBDCard::whereNull("vintage_status")->get();
			     /*  echo '<pre>';
				echo (count($agentPayoutMod));
				exit;   */ 
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
					
					
						$salesTime = $payout->end_sales_time;
						$salesTimeArray = explode("-",$salesTime);
						if($salesTimeArray[0] == 2)
						{
							$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
						}
						else
						{
						$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
						}
						$dojEmp = $payout->doj;
						if($dojEmp != '' && $dojEmp != NULL)
						{
							$doj = str_replace("/","-",$dojEmp);//exit;
							
							//$date1 = date("Y-m-d",strtotime($doj));
							$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
							$agentPUpdate = PreAgentPayoutCBDCard::find($payout->id);
							$agentPUpdate->vintage = $daysInterval;
							$agentPUpdate->vintage_status = 2;
							$agentPUpdate->save();
							
						}
						
					}								
					
				
		echo "done";
		exit;
			}
			public function UpdateTimeRangePrePayoutCBDData(){
				/*
		*Cron Logs works
		*/
		$createCronLogs = new CronRunLogs();
		$createCronLogs->title = 'SearchEngine-Range';
		$createCronLogs->save();
		
		/*
		*Cron Logs works
		*/
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
					$PayoutData =PreAgentPayoutCBDCard::whereRaw($whereraw)->whereNull("range_status")->get();
					foreach($PayoutData as $_newdata){
						$updateMod = PreAgentPayoutCBDCard::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						$updateMod->range_status=2;
						$updateMod->save();
					}
					
				
			}
			echo "done";
		exit;
			}
			
			
public function UpdateCronCBDCard()
{
			$data = PreAgentPayoutCBDCard::where("use_cron",1)->first();
	
	
			
			$application_id = $data->application_id;
			
			$ref_no = $data->ref_no;
			/*
			*booking check
			*/
			$bookingdata=CBDBankMis::where("ref_no",$application_id)->whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
			if($bookingdata >0)
			{
				$updateSubmission = PreAgentPayoutCBDCard::find($data->id);
				$updateSubmission->booking_status = 2;
				$updateSubmission->use_cron = 2;
				$updateSubmission->submission_status="Booked";
				$updateSubmission->save();
				
			}
			else
			{					
						    $updateSubmission = PreAgentPayoutCBDCard::find($data->id);
					
							$updateSubmission->use_cron = 4;
							$updateSubmission->submission_status='WIP';
							$updateSubmission->save();
					
				
			}
			
			/*
			*booking check
			*/
	
	
	echo "done";
	exit;
}


public function updateBookingMISStatusCBD()
{
	/*
		*Cron Logs works
		*/
		$createCronLogs = new CronRunLogs();
		$createCronLogs->title = 'SearchEngine-Booking';
		$createCronLogs->save();
		
		/*
		*Cron Logs works
		*/
	$datas = CBDBankMis::whereNull("update_search_eng")->limit(100)->orderBy("id","DESC")->get();
	/* echo "<pre>";
	print_r($datas);
	exit; */
	foreach($datas as $data)
	{
		$appId = $data->instanceid;
		$checkBooking = PreAgentPayoutCBDCard::where("application_id",$appId)->get()->count();
		if($checkBooking > 0)
		{
			if(PreAgentPayoutCBDCard::where('application_id', $appId)->update([ 'use_cron' => 2, 'booking_status'=>2, 'submission_status'=>"Booked"]))
			{
				
				$update = CBDBankMis::find($data->id);
				$update->update_search_eng = 1;
				$update->save();
				/* exit; */
			}
		}
	}
	echo "all done";
	exit;
	
}
			
}
