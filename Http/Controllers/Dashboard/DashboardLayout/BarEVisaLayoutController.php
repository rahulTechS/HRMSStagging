<?php

namespace App\Http\Controllers\Dashboard\DashboardLayout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;

use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetOnboardingHiring;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;


class BarEVisaLayoutController extends Controller
{
  

	
	
	
	public function searchEVisa(Request $request)
	{
		$parametersInput = $request->input();
		
		$widgetID = $parametersInput['widgetID'];
		$recruiterCat = $parametersInput['recruiterCat'];
		$jobOpeningArray = $parametersInput['job_opening'];
		$jobOpeningArray = $parametersInput['job_opening'];
		$department = isset($parametersInput['department']);
		if(isset($parametersInput['department']) && $parametersInput['department'] != '' && $parametersInput['department'] != NULL )
		{
			if(isset($parametersInput['department'])!=''){
			$departmentData = implode(",",$parametersInput['department']);
			}
			else{
				$departmentData ='';
			}
			$request->session()->put('widgetFiltermolDept['.$widgetID.']',$departmentData);	
		}
		else
		{
			$request->session()->put('widgetFiltermolDept['.$widgetID.']','');	
		}
		if(isset($parametersInput['recruiterId']) && $parametersInput['recruiterId'] != '' && $parametersInput['recruiterId'] != NULL )
		{
			$recruiterIdData = implode(",",$parametersInput['recruiterId']);
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]',$recruiterIdData);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');	
		}
		if(isset($parametersInput['job_opening']) && $parametersInput['job_opening'] != '' && $parametersInput['job_opening'] != NULL )
		{
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]',$jobOpeningArray);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		}
		if(isset($parametersInput['recruiterCat']) && $parametersInput['recruiterCat'] != '' && $parametersInput['recruiterCat'] != NULL )
		{
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]',$recruiterCat);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		}
		if(isset($parametersInput['data_type']) && $parametersInput['data_type'] != '' && $parametersInput['data_type'] != NULL )
		{
			$data_type = $parametersInput['data_type'];
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]',$data_type);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		}
		
		
		if(isset($parametersInput['from_salesTime']) && $parametersInput['from_salesTime'] != '' && $parametersInput['from_salesTime'] != NULL )
		{
			$from_salesTime = $parametersInput['from_salesTime'];
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]',$from_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		}
		
		
		if(isset($parametersInput['to_salesTime']) && $parametersInput['to_salesTime'] != '' && $parametersInput['to_salesTime'] != NULL )
		{
			$to_salesTime = $parametersInput['to_salesTime'];
			$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]',$to_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		}
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function resetEVisa(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function reloadmeBarEVisa(Request $request)
	{
		$wid = $request->wid;
		
		return view("components/bargraph/reloadmeBarEvisa",compact('wid'));
	}
	
	public function EVisaBarLink(Request $request)
	{
		$jobopeningName  =  $request->jobopeningName;
		$wid = $request->wid;
		/**
		*getJOb OPening id
		*/
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		$jobOpeningArray = explode("-",$jobopeningName);
		$jobName = $jobOpeningArray[0];
		$jobDepartment = $jobOpeningArray[1];
		$location = $jobOpeningArray[2];
		$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list','');
		$departmentMod = Department::where('department_name',$jobDepartment)->first();
		if($departmentMod != '')
		{
			$deptId = $departmentMod->id;
			$jobopeningMOd = JobOpening::where("department",trim($deptId))->where("name",trim($jobName))->where("location",$location)->first();
			if($jobopeningMOd  != '')
			{
				
				$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
				
			
				$request->session()->put('opening_cand_filter_inner_list',$jobopeningMOd->id);
				$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
				$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step2");
				$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list',$date30DaysBack);
				$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list',$currentDate);
				
			}
			else
			{
				$request->session()->put('opening_cand_filter_inner_list','');
				$request->session()->put('company_RecruiterName_filter_inner_list','');
				$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step2");
				$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list','');
			}
		}
		else
		{
			$request->session()->put('opening_cand_filter_inner_list','');
				$request->session()->put('company_RecruiterName_filter_inner_list','');
				$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step2");
				$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list','');
		}
		
		$dataType = $request->session()->get('widgetFilterHiring['.$wid.'][data_type]');
		if($dataType == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromData = date("Y").'-'.date("m").'-'.'01';
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$fromData);
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$toDate);
			
			
			
		}
		else if($dataType == 'month_3')
		{
			$toDate = date("Y-m-d");
			$fromData = date("Y-m-d",strtotime("-90 days")); 
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$fromData);
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$toDate);
			
			$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step2");
				$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list',$fromData);
				$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list',$toDate);
		}
		else if($dataType == 'custom')
		{
			$from_salesTime = date("Y-m-d",strtotime($request->session()->get('widgetFilterHiring['.$wid.'][from_salesTime]')));	
			$to_salesTime = date("Y-m-d",strtotime($request->session()->get('widgetFilterHiring['.$wid.'][to_salesTime]')));	
			
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$fromData);
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$toDate);
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromData = date("Y").'-'.date("m").'-'.'01'; 
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$fromData);
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$toDate);
		}
		$request->session()->put('interview_approved_by_filter_inner_list','');
		return redirect('documentcollectionAjax');
		/**
		*get JOb Opening id
		*/
	}
	public function SearchLastMonthEVisa(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function expandBarEVisa(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeBarEVisa/'.$wid);
	}
	
	public function compressBarEVisa(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeBarEVisa/'.$wid);
	}
	public function SearchLastMonthEVisaCM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','current_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function SearchLastMonthEVisaLM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function SearchLastMonthEVisa3M(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','month_3');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
}