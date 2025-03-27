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


class ChangeStatusLayoutController extends Controller
{
	
	public function searchChangeStatus(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		$recruiterCat = $parametersInput['recruiterCat'];
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
		if(isset($parametersInput['recruiterCat']) && $parametersInput['recruiterCat'] != '' && $parametersInput['recruiterCat'] != NULL )
		{
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]',$recruiterCat);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
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
		//echo "hello";exit;
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	
	public function resetsearchChangeStatus(Request $request)
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
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');			
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	
	public function reloadmeChangeStatus(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/DeadLine/reloadmechangestatus",compact('wid'));
	}
	
	
	public function expandChangeStatus(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeChangeStatus/'.$wid);
	}
	
	public function compressChangeStatus(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeChangeStatus/'.$wid);
	}
	
	public function ChangeStatusLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function candidateChangeStatusLink(Request $request)
	{
		//echo "hello";exit;
		$name = $request->name;
		$wid = $request->wid;
		$request->session()->put('cname_emp_filter_inner_list',$name);
		//$request->session()->put('filtervisa_documents_status_filter_inner_list',2);
		$request->session()->put('tabOpenByWidget',"Deadline");
		//$request->session()->put('subtabOpenByWidget',"VisaInProcess");		
		return redirect('documentcollectionAjax');
	}
	public function ChangeStatusAll(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','All');	
		$request->session()->put('widgetFilterBYMissed['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYNotMissed['.$widgetID.']','');	
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	public function ChangeStatusMissed(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYMissed['.$widgetID.']','Missed');	
		$request->session()->put('widgetFilterBYNotMissed['.$widgetID.']','');	
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	public function ChangeStatusNotMissed(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYMissed['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYNotMissed['.$widgetID.']','Not Missed');	
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
		public function ChangeStatusbyfilter(Request $request)
	{
		$parametersInput = $request->input();
		
		$widgetID = $parametersInput['widgetId'];
		
		
		if(isset($parametersInput['candidatename']) && $parametersInput['candidatename'] != '' && $parametersInput['candidatename'] != NULL )
		{
			if(isset($parametersInput['candidatename'])!=''){
			$candidatename = implode(",",$parametersInput['candidatename']);
			}
			else{
				$candidatename ='';
			}
			$request->session()->put('cname_emp_filter_inner_list['.$widgetID.']',$candidatename);	
		}
		else
		{
			$request->session()->put('cname_emp_filter_inner_list['.$widgetID.']','');	
		}
		if(isset($parametersInput['department']) && $parametersInput['department'] != '' && $parametersInput['department'] != NULL )
		{
			$department = implode(",",$parametersInput['department']);
			$request->session()->put('widgetFilteronboardDept['.$widgetID.']',$department);	
		}
		else
		{
			$request->session()->put('widgetFilteronboardDept['.$widgetID.']','');	
		}
		if(isset($parametersInput['empId']) && $parametersInput['empId'] != '' && $parametersInput['empId'] != NULL )
		{
			$empId = implode(",",$parametersInput['empId']);
			$request->session()->put('empid_emp_filter_inner_list['.$widgetID.']',$empId);	
		}
		else
		{
			$request->session()->put('empid_emp_filter_inner_list['.$widgetID.']','');	
		}
		
		
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	public function ChangeStatussetfilter(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
			
		$request->session()->put('cname_emp_filter_inner_list['.$widgetID.']','');
		$request->session()->put('widgetFilteronboardDept['.$widgetID.']','');	
		$request->session()->put('empid_emp_filter_inner_list['.$widgetID.']','');			
		return redirect('reloadmeChangeStatus/'.$widgetID);
	}
	public function LoadChangestatusTableData(Request $request){
		$widgetId = $request->wid;
		$id = $request->empId;
		
	$docdata=DocumentCollectionDetails::where("id",$id)->first();
	
	return view("components/DeadLine/changestatusTable",compact('widgetId','docdata'));
	  	
		
	}
}