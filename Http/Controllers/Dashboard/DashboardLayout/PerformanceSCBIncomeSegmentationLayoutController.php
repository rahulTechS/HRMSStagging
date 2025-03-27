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


class PerformanceSCBIncomeSegmentationLayoutController extends Controller
{
	
	public function searchPerformanceSCBIncomeSegmentation(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		
		$widgetID = $parametersInput['widgetID'];
		
		
		if(isset($parametersInput['team']) && $parametersInput['team'] != '' && $parametersInput['team'] != NULL )
		{
			if(isset($parametersInput['team'])!=''){
			$team = implode(",",$parametersInput['team']);
			}
			else{
				$team ='';
			}
			$request->session()->put('widgetFiltermolTeam['.$widgetID.']',$team);	
		}
		else
		{
			$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		}
		if(isset($parametersInput['processor']) && $parametersInput['processor'] != '' && $parametersInput['processor'] != NULL )
		{
			$processor = implode(",",$parametersInput['processor']);
			$request->session()->put('widgetFilterprocessor['.$widgetID.']',$processor);	
		}
		else
		{
			$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
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
	
	public function resetsearchPerformanceSCBIncomeSegmentation(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function reloadmePerformanceSCBIncomeSegmentation(Request $request)
	{
		$wid = $request->wid;
		
		return view("components/Performance/reloadmePerformancescbincomesegmentation",compact('wid'));
	}
	
	public function MOLBarLink(Request $request)
	{
		$jobopeningName  =  $request->jobopeningName;
		$wid = $request->wid;
		/**
		*getJOb OPening id
		*/
		$jobOpeningArray = explode("-",$jobopeningName);
		$jobName = $jobOpeningArray[0];
		$jobDepartment = $jobOpeningArray[1];
		$location = $jobOpeningArray[2];
		
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
				$request->session()->put('subtabOpenByWidget',"step1");
			}
			else
			{
				$request->session()->put('opening_cand_filter_inner_list','');
				$request->session()->put('company_RecruiterName_filter_inner_list','');
				$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step1");
			}
		}
		else
		{
			$request->session()->put('opening_cand_filter_inner_list','');
				$request->session()->put('company_RecruiterName_filter_inner_list','');
				$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
				$request->session()->put('tabOpenByWidget',"visa");
				$request->session()->put('subtabOpenByWidget',"step1");
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
	public function expandPerformanceSCBIncomeSegmentation(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmePerformanceSCBIncomeSegmentation/'.$wid);
	}
	
	public function compressPerformanceSCBIncomeSegmentation(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmePerformanceSCBIncomeSegmentation/'.$wid);
	}
	public function PerformanceSCBIncomeSegmentationBookings(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','Bookings');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','');	
		
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function PerformanceSCBIncomeSegmentationSubmissions(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','Submissions');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function PerformanceSCBIncomeSegmentationLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
}