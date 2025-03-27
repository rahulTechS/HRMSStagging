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


class EmpLeavesLayoutController extends Controller
{
	
	public function searchLeaves(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		
		$widgetID = $parametersInput['widgetID'];


		
		
		if(isset($parametersInput['department']) && $parametersInput['department'] != '' && $parametersInput['department'] != NULL )
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][department]',$parametersInput['department']);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][department]','');	
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
		return redirect('reloadEmpLeaves/'.$widgetID);
	}
	
	public function resetLeaves(Request $request)
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
		$request->session()->put('widgetFilterHiring['.$widgetID.'][department]','');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][teamLeaders]','');		
		return redirect('reloadEmpLeaves/'.$widgetID);
	}
	
	public function reloadEmpLeaves(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/Leaves/reloadEmpLeaves",compact('wid'));
	}
	
	
	public function expandLeaves(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadEmpLeaves/'.$wid);
	}
	
	public function compressLeaves(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadEmpLeaves/'.$wid);
	}
	public function PerformanceCBDspreadByGraph(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYGraph['.$widgetID.']','Graph');	
		$request->session()->put('widgetFilterBYTable['.$widgetID.']','');	
			
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function PerformanceCBDByTable(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYGraph['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTable['.$widgetID.']','Table');
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function OLDocsPendingLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function empAttendanceDeptLink(Request $request)
	{
		//echo "hello";exit;
		$dept = $request->dept;
		$wid = $request->wid;
		$request->session()->put('attendance_department_filter',$dept);
		// $request->session()->put('filterpendingofferletter_filter_inner_list',1);
		// $request->session()->put('tabOpenByWidget',"OfferletterPending");		
		return redirect('departmentMarkAttendance');
	}
	public function candidateoldocsPendingTotelLink(Request $request)
	{
		//echo "hello";exit;
		
		$wid = $request->wid;
		$request->session()->put('filterpendingofferletter_filter_inner_list',1);
		$request->session()->put('tabOpenByWidget',"OfferletterPending");		
		return redirect('documentcollectionAjax');
	}
}