<?php
namespace App\Http\Controllers\Shifting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company\Department;

use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Logs\ShiftingLogs;



use Session;


class AgentShiftingController extends Controller
{
    
      public function agentShifting()
	  {
		  	$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		 return view("Shifting/agentShifting",compact('tL_details'));;
	  }
	  
	  public function listingEmployeeAsPerTL(Request $request)
	  {
				$tl = $request->tl;
				
					$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tl)->get();
				
				
				return view("Shifting/listingEmployeeAsPerTL",compact('agent_details'));
	  }
	  
	  public function selectedEmployeeShifting(Request $request)
	  {
		  $parameters = $request->input();
				
				$selectedEmp = $parameters['selectedEmp'];
				$selectedTL = $parameters['selectedTL'];
				$empArr = explode(",",$selectedEmp);
				
					$tL_details = Employee_details::where("job_role",'Team Leader')->where("id","!=",$selectedTL)->where("dept_id",9)->get();
					$agent_details = Employee_details::whereIn("id",$empArr)->get();
					
				
				return view("Shifting/selectedEmployeeShifting",compact('agent_details','tL_details'));
	  }
	  
	  
	  public function selectedEmployeeShiftingStart(Request $request)
	  {
		  $parameters = $request->input();
		 /*  echo '<pre>';
		  print_r($parameters);
		  exit; */
		  $selectedTLNew = $parameters['selectedTLNew'];
		  $selectedEmps = $parameters['selectedEmp'];
		  $selectedEmpsArray = explode(",",$selectedEmps);
		  $oldTl = 0;
		  foreach($selectedEmpsArray as $agent)
		  {
			  if($oldTl == 0)
			  {
			  $oldTl = Employee_details::where("id",$agent)->first()->tl_id;
			  }
			  $updateAgentOBJ = Employee_details::find($agent);
			  $updateAgentOBJ->tl_id = $selectedTLNew;
			  $updateAgentOBJ->save();
		  }
		  
		  $logsObj = new ShiftingLogs();
		  $logsObj->agent_id = $selectedEmps;
		  $logsObj->old_tl = $oldTl;
		  $logsObj->new_tl = $selectedTLNew;
		  $logsObj->created_by = $request->session()->get('EmployeeId');
		   $logsObj->save();
		  echo "DONE";
		  exit;
	  }
		
		
		
		
}
