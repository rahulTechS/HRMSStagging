<?php

namespace App\Http\Controllers\Flag;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;

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
use App\Models\Onboarding\IncentiveLetterDetails;

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
use App\Models\EmpOffline\EmpOffline;
use App\Models\EmpOffline\QuestionForLeaving;
use App\Models\Question\Question;
use App\Models\SettelementAttribute\SettelementAttribute;
use App\Models\CompanyAssets\CompanyAssets;
use App\Models\SettelementCheckList\SettelementCheckList;
use App\Models\EmpOffline\SettelementAttributes;
use App\Models\ReasonsForLeaving\ReasonsForLeaving;
use App\Models\EmpOffline\OffboardEMPData;
use App\Models\EmpOffline\SettelementLogs;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\EmpOffline\CancelationVisaProcess;


use Illuminate\Support\Facades\Validator;
use App\Models\PerformanceFlagRules\FlagRules;
use App\Models\PerformanceFlagRules\FlagTypes;
use App\Models\PerformanceFlagRules\FlagRange;


use App\Models\MIS\BankDetailsUAE;
use App\Models\Company\Department;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Dashboard\MasterPayout;
use Illuminate\Support\Facades\DB;



class FlagRuleController extends Controller
{
    
       
	
	
	public function updateMasterPayoutData($id)
	{
		

        $flagRulesData = FlagRules::where('id',$id)->orderBy('id','DESC')->first();

		// return $flagRulesData->rangeid;
		$myString = $flagRulesData->range_id;
		$myArray = explode(',', $myString);
		//print_r($myArray);
		//die;



		//return $flagRulesData->range_id;



		$flagRuleRequest = MasterPayout::where('dept_id',$flagRulesData->bank_name)
		->where('agent_target',$flagRulesData->target)
		//->where('range_id',$flagRulesData->rangeid)
		->whereIn('range_id', $myArray)
		->where('flag_status',1)
		->where('cards_point_m','<',$flagRulesData->card_points)
		->get();
		//->toSql();

		//dd($flagRuleRequest);


		return $flagRuleRequest;



		if($flagRulesData->acheived==1)
		{

			$flagRuleRequest = MasterPayout::where('dept_id',$flagRulesData->bank_name)
			->where('agent_target',$flagRulesData->target)
			->where('range_id',$flagRulesData->rangeid)
			->where('flag_status',1)
			->where('cards_point_m','<',$flagRulesData->card_points)
			->update(['flag_type' => $flagRulesData->flag_type,'flag_rule' => $flagRulesData->id, 'flag_status' => 2]);
		}
		if($flagRulesData->acheived==3)
		{

			$flagRuleRequest = MasterPayout::where('dept_id',$flagRulesData->bank_name)
			->where('agent_target',$flagRulesData->target)
			->where('range_id',$flagRulesData->rangeid)
			->where('flag_status',1)
			->where('cards_point_m','>',$flagRulesData->card_points)
			->update(['flag_type' => $flagRulesData->flag_type,'flag_rule' => $flagRulesData->id, 'flag_status' => 2]);
		}


		//->get();




		return response()->json(['success'=>'Flag Rule Applied Successfully.']);


		//dd($flagRuleRequest);




		// $masterPayoutData = MasterPayout::
		// join('performance_flag_rules', 'master_payout.dept_id', '=', 'performance_flag_rules.bank_name')
		// ->on('performance_flag_rules', 'master_payout.range_id', '=', 'performance_flag_rules.range_id')
		// ->where('master_payout.dept_id',36)
		// ->where('master_payout.agent_product','CARDS')
		// ->orderBy('master_payout.id','ASC')->get();


		

		// $masterPayoutData2 = MasterPayout::select("master_payout.id")


		// ->join("performance_flag_rules",function($join){

		// 	$join->on("performance_flag_rules.bank_name","=","master_payout.dept_id")

		// 		->on("performance_flag_rules.range_id","=","master_payout.range_id")
		// 		->on("performance_flag_rules.target","=","master_payout.agent_target");

		// })
		// ->where('master_payout.dept_id',36)
		// //->where('master_payout.flag_status',1)
		// ->where('master_payout.agent_product','CARDS')
		// ->orderBy('master_payout.id','ASC')
		// ->get();


		// $masterPayoutData = MasterPayout::select("master_payout.*","performance_flag_rules.flag_type as ruletype","performance_flag_rules.id as newid")


		// ->join("performance_flag_rules",function($join){

		// 	$join->on("performance_flag_rules.bank_name","=","master_payout.dept_id")

		// 		->on("performance_flag_rules.range_id","=","master_payout.range_id")
		// 		->on("performance_flag_rules.target","=","master_payout.agent_target");

		// })
		// ->where('master_payout.dept_id',36)
		// ->where('master_payout.flag_status',1)
		// ->where('master_payout.agent_product','CARDS')
		// ->orderBy('master_payout.id','ASC')
		// ->get();



		// $flagRulesData = MasterPayout::select('id')->whereIn('id',$masterPayoutData2)->orderBy('id','DESC')->get();
		//echo "<pre>";
		//echo "Find ids are----";
		//print_r($flagRulesData);

		//return $flagRulesData;


		//die;die;
		//exit;



		//return $flagRulesData;

		// foreach($masterPayoutData as $value)
		// {






		// 					//echo "Update value for id: ";
		// 					echo "Update values for id: ".$value->id."<br>";




		// }
		
			
		
		//echo "done";



		//return $masterPayoutData;
	}
	
	
	
	
	
	
	
	
	
		public function EmpOffBoardProcess(Request $req)
	   {
		$ReasonsForLeavingDetails = ReasonsForLeaving::where("status",1)->get();
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		$empId=EmpOffline::get();
		
		$Designation=Designation::where("status",1)->get();



		$flagRulesdata = FlagRules::orderBy('id','ASC')->get();


        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
       // $banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();

        $ranges = WorkTimeRange::orderBy('id','ASC')->get();


		$flagrulessalary = FlagRules::orderBy('id','ASC')->groupBy('salary')->get();
	


		$agentTarget = MasterPayout::where('dept_id',36)->where('agent_product','CARDS')
										->groupBy('agent_target')
										->orderBy('id','ASC')->get();



		














		return view("Flag/EmpOfflineProcessIndex",compact('agentTarget','flagrulessalary','flagRulesdata','ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation','flagTypesdata','banks','ranges'));
	   }




	   public function delete($id)
	   {
		   //return $id;
		   $flagRules = FlagRules::find($id)->delete();
   
		   $flagRulesdata = FlagRules::orderBy('id','ASC')->get();
		   $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
		   //$banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();
   
		   
		   $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
		   ->orderBy('id','ASC')->get();

		   $ranges = WorkTimeRange::orderBy('id','ASC')->get();

		   $flagrulessalary = FlagRules::orderBy('id','ASC')->groupBy('salary')->get();






		   $ReasonsForLeavingDetails = ReasonsForLeaving::where("status",1)->get();
		   $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		   $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		   $empId=EmpOffline::get();
		   
		   $Designation=Designation::where("status",1)->get();

		   
		$agentTarget = MasterPayout::where('dept_id',36)->where('agent_product','CARDS')
		->groupBy('agent_target')
		->orderBy('id','ASC')->get();


   
   
   
		   return view("Flag/EmpOfflineProcessIndex",compact('agentTarget','flagrulessalary','ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation','flagRules','flagTypesdata','banks','ranges'));

	   }

	   public function getFlagRuleContentData(Request $request)
    {
        $rowid = $request->rowid;
        $flagRulesData = FlagRules::where('id',$rowid)->orderBy('id','DESC')->first();

        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
        //$banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        
        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();

        $ranges = WorkTimeRange::orderBy('id','ASC')->get();

		$flagrulessalary = FlagRules::orderBy('id','ASC')->groupBy('salary')->get();

		
		$agentTarget = MasterPayout::where('dept_id',36)->where('agent_product','CARDS')
										->groupBy('agent_target')
										->orderBy('agent_target','ASC')->groupBy('agent_target')->get();



		return view("Flag/editFlagRule",compact('agentTarget','flagRulesData','flagTypesdata','banks','ranges'));



        //return view("PerformanceFlagRules/flagRuleContent",compact('flagRulesData','flagTypesdata','banks','ranges'));


    }



public function getFlagRuleAddPop()
{
	//$flagRulesData = FlagRules::where('id',$rowid)->orderBy('id','DESC')->first();

        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
        //$banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        
        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();

        $ranges = WorkTimeRange::orderBy('id','ASC')->get();

		$flagrulessalary = FlagRules::orderBy('id','ASC')->groupBy('salary')->get();


		
		$agentTarget = MasterPayout::where('dept_id',36)->where('agent_product','CARDS')
										->groupBy('agent_target')
										->orderBy('agent_target','ASC')->groupBy('agent_target')->get();



		return view("Flag/addFlagRule",compact('agentTarget','flagTypesdata','banks','ranges'));


}









	public function updateFlagRuleData(Request $request)
    {
		
		
		$validator = Validator::make($request->all(), [
            'bank_name' => 'required',
			'target' => 'required',
            'salary' => 'required',
            'numberofcards' => 'required|numeric',
            'acheived' => 'required',
            'rangeid' => 'required',
            'flag_type' => 'required',        
        ]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		
		
		
		
		
		
		$flagRuleRequest = FlagRules::where('bank_name',$request->bank_name)
		->where('salary',$request->salary)
		->where('target',$request->target)
		->where('range_id',$request->rangeid)
		->where('acheived',$request->acheived)			
		->orderBy('id','DESC')->first();

		if($flagRuleRequest)
		{
			return response()->json(['exist'=>1]);
		}
		else
		{
			$flagRulesData = FlagRules::where('id',$request->rowid)->orderBy('id','DESC')->first();

			
			$flagRulesData->bank_name = $request->bank_name;
			$flagRulesData->salary = $request->salary;
			$flagRulesData->target = $request->target;
			$flagRulesData->acheived = $request->acheived;
			$flagRulesData->card_points = $request->numberofcards;
	
			//$flagRulesData->acheived_percentage_from = $request->acheived_percentage_from;
			//$flagRulesData->acheived_percentage_to = $request->acheived_percentage_to;
	
			$flagRulesData->flag_type = $request->flag_type;
			$flagRulesData->status = $request->status;			
			$flagRulesData->range_id = $request->rangeid;	
			$flagRulesData->save();

			$flagRulesdel = FlagRange::where('ruleid',$request->rowid)->delete();


			$myString = $request->rangeid;
			$myArray = explode(',', $myString);

			foreach($myArray as $rangeid)
			{
				DB::table('flagrule_range')->insert([
				'value' => $rangeid,
				'ruleid' => $request->rowid
				]);
			}	
	
			$response['code'] = '200';
			$response['message'] = "Updated Successfully.";
			echo json_encode($response);

		}
		
		
		
		











    }




    public function createFlagRulePostAjax(Request $request)
    {
       //return $request->all();
    	$validator = Validator::make($request->all(), [
            'bank_name' => 'required',
			'target' => 'required',
            'salary' => 'required',
            'numberofcards' => 'required|numeric',
            'acheived' => 'required',
            'rangeid' => 'required',
            'flag_type' => 'required',        
        ]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$flagRuleRequest = FlagRules::where('bank_name',$request->bank_name)
			->where('salary',$request->salary)
			->where('target',$request->target)
			->where('range_id',$request->rangeid)
			->where('acheived',$request->acheived)			
			->orderBy('id','DESC')->first();




			if($flagRuleRequest)
			{
				return response()->json(['exist'=>1]);
			}
			else{ 
				
				

				$flagRuleRequest = new FlagRules();
				$flagRuleRequest->bank_name = $request->bank_name;
				$flagRuleRequest->salary = $request->salary;
				$flagRuleRequest->target = $request->target;
				$flagRuleRequest->acheived = $request->acheived;
				//$flagRuleRequest->acheived_percentage_from = $request->acheived_percentage_from;
				//$flagRuleRequest->acheived_percentage_to = $request->acheived_percentage_to;
				$flagRuleRequest->card_points = $request->numberofcards;

				$flagRuleRequest->flag_type = $request->flag_type;
				$flagRuleRequest->status = $request->status;
				$flagRuleRequest->range_id = $request->rangeid;
				$flagRuleRequest->status = 1;
				$flagRuleRequest->save();   
				
				$lastruleid = $flagRuleRequest->id;
				

				$myString = $request->rangeid;
				$myArray = explode(',', $myString);

				foreach($myArray as $rangeid)
				{
					 $rangeid;
					DB::table('flagrule_range')->insert([
					'value' => $rangeid,
					'ruleid' => $lastruleid
					]);
				}




			
				return response()->json(['success'=>'Saved Successfully.']);
			}

		}
	}






















	   

	   
	   
	   
	    public function flagRuleListingData(Request $request)
	   {
		   //$request->session()->put('company_RecruiterNameAll_filter_inner_list','');
		    $whereraw = '';
			$whereraw1 = '';
			$selectedFilter['CNAME'] = '';
			$selectedFilter['CEMAIL'] = '';
			$selectedFilter['DESC'] = '';
			$selectedFilter['DEPT'] = '';
			$selectedFilter['OPENING'] = '';
			$selectedFilter['STATUS'] = '';
			$selectedFilter['vintage'] = '';
			$selectedFilter['Company'] = '';
			$selectedFilter['Recruiter'] = '';
			
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		//$request->session()->put('cname_empAll_filter_inner_list','');
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  //$whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 100;
				}
				
				
				if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboardall_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardall_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'salary IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And salary IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'flagrule_range.value IN('.$teamlead.')';
						
					}
					else
					{
						$whereraw .= ' And flagrule_range.value IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'flag_type IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And flag_type IN ('.$empId.')';
					}
				}



				// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
				// {
				// 	$rangeid = $request->session()->get('range_filter_inner_list');
				// 	 if($whereraw == '')
				// 	{
				// 		$whereraw = 'range_id IN ('.$rangeid.')';
				// 	}
				// 	else
				// 	{
				// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
				// 	}
				// }










				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						//$whereraw = 'emp_name like "%'.$fname.'%"';
						$whereraw = 'bank_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And bank_name IN('.$finalcname.')';
					}
				}
				
				if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candAll_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candAll_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
				}
				


				
				
				if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candAll_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candAll_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				
				
				
				if($whereraw != '')
				{
					//echo "hello";
					// echo $whereraw;
					//exit;
					$documentCollectiondetails = FlagRules::join('flagrule_range', 'performance_flag_rules.id', '=', 'flagrule_range.ruleid')
					//->select('performance_flag_rules.*')
					->whereRaw($whereraw)
					->orderBy('performance_flag_rules.id', 'desc')
					->groupBy('ruleid')
					->get();	
	
					$newResult=array();
					foreach($documentCollectiondetails as $value)
					{
						$newResult[]=$value->id;
					}
					$documentCollectiondetails = FlagRules::join('flagrule_range', 'flagrule_range.ruleid', '=', 'performance_flag_rules.id')
					->whereIn('flagrule_range.id',$newResult)
					->orderBy('performance_flag_rules.id', 'desc')
					->paginate($paginationValue);

					$flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();			 
					 $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
					 ->orderBy('id','ASC')->get();			 
					 $ranges = WorkTimeRange::orderBy('id','ASC')->get();

				}
				else
				{
					//echo "hello1";
					//$documentCollectiondetails = FlagRules::orderBy('id','desc')->paginate($paginationValue);

					$documentCollectiondetails = FlagRules::
					join('flagrule_range', 'performance_flag_rules.id', '=', 'flagrule_range.ruleid')
					//->select('performance_flag_rules.*')
					->orderBy('performance_flag_rules.id', 'desc')
					->groupBy('ruleid')
					->get();


					$newResult=array();
					foreach($documentCollectiondetails as $value)
					{
						$newResult[]=$value->id;
					}
					$documentCollectiondetails = FlagRules::join('flagrule_range', 'flagrule_range.ruleid', '=', 'performance_flag_rules.id')
					->select('performance_flag_rules.*', 'flagrule_range.*')
					->whereIn('flagrule_range.id',$newResult)
					->orderBy('performance_flag_rules.id', 'desc')
					->paginate($paginationValue);


					$flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
				   // $banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();
			
					$banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
					->orderBy('id','ASC')->get();
			
					$ranges = WorkTimeRange::orderBy('id','ASC')->get();

					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
					$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					//$reportsCount = FlagRules::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
					$reportsCount = FlagRules::join('flagrule_range', 'performance_flag_rules.id', '=', 'flagrule_range.ruleid')
					->whereRaw($whereraw)
					->orderBy('performance_flag_rules.id', 'desc')
					->groupBy('ruleid')
					->get()->count();
				}
				else
				{
					$reportsCount = FlagRules::join('flagrule_range', 'performance_flag_rules.id', '=', 'flagrule_range.ruleid')
					->orderBy('performance_flag_rules.id', 'desc')
					->groupBy('ruleid')->get()->count();
					

				}
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessAll'));
				
		//print_r($documentCollectiondetails);exit;
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("Flag/listingEmpOfflineProcessAll",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	   }





	   public static function getBankData($bankid)
	   {
		   $bankData = Department::where('status',1)->where('id',$bankid)->orderBy('id','ASC')->first();
		   return $bankData->department_name;
   
	   }

















	   
	   public function filterByCandidateNameEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_emp_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_cand_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_cand_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_cand_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_cand_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_cand_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_cand_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_cand_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		//Start deem mashreq
		public function filterByCandidateNameDeemEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empDeem_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailDeemEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candDeem_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationDeemEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candDeem_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentDeemEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candDeem_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningDeemEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candDeem_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussDeemEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candDeem_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageDeemEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candDeem_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyDeemEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candDeem_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		//Start All
		public function filterByCandidateNameAllEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAll_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAllEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAll_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAllEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAll_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAllEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAll_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAllEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAll_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAllEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAll_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAllEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAll_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAllEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAll_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		
		//Start All
		public function filterByCandidateNameAafaqEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAafaq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAafaqEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAafaq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAafaqEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAafaq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAafaqEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAafaq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAafaqEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAafaq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAafaqEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAafaq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAafaqEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAafaq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAafaqEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAafaq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
	   //masr
	   public function filterByCandidateNamemashreqEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			//echo $cname;exit;
			$request->session()->put('cname_empmashreq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailmashreqEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candmashreq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationmashreqEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candmashreq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentmashreqEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candmashreq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningmashreqEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candmashreq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatusmashreqEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candmashreq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintagemashreqEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candmashreq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanymashreqEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candmashreq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameAllEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameAll_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamemashreqEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamemashreq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameenbdEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameenbd_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNameaafaqEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameaafaq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamedeemEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamedeem_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNamevisapipelineEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamevisapipeline_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   
	   
	   
	   

		public function resetDocumentCollectionFilter(Request $request)
		{
					$request->session()->put('emp_name','');		
			
					$request->session()->put('department','');
					$request->session()->put('caption','');
					
					$request->session()->put('designation','');
					$request->session()->put('status','');
					$request->session()->put('serialized_id','');
					$request->session()->put('visa_process_status','');
					$request->session()->flash('message','Filters Reset Successfully.');
					return redirect('documentcollection');
		}
		

		

		
		
	   
	   public function setOffSetForEmpOfflineProcess(Request $request)
	   {
		   $offset = $request->offset;
		  $request->session()->put('onboading_page_limit',$offset);
	   }
	   
	   public function filterReportAsPerDepartmentr(Request $request)
	   {
		   $deptid = $request->deptid;
		    $request->session()->put('onboarding_department_filter','');
		    $request->session()->put('onboarding_department_filter',$deptid);
	   }
	   
	   public function updateFilterOnBoarding(Request $request)
	   {
		    $filterList = array();
				
				$filterList['department'] = '';
			    if(!empty($request->session()->get('onboarding_department_filter')))
				  { 
						$_dpartId= $request->session()->get('onboarding_department_filter');
					  
					   $filterList['department'] =Department::where("id",$_dpartId)->first()->department_name;
				  }
		   return view("OnboardingAjax/updateFilterOnBoarding",compact('filterList'));
	   }
	   
	   public function cancelFiltersOnboard(Request $request)
	   {
		   $request->session()->put('onboarding_department_filter','');
	   }
	   public static function getonboardingAges($createAT)
			{
				echo $createAT;exit;
				if($createAT != '')
				{
					$doj = createAT;
					if($doj == NULL || $doj == '')
					{
						return "Not Decleared";
					}
					else
					{
						$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 return  $returnData;
					}
					
				}
				else
				{
					return "Not Decleared";
				}
			}
	public function updateVintage(Request $req){
		 $dateC = date("Y-m-d");
		 
		 $Collection  = EmpOffline::whereDate("vintage_updated_date","<",$dateC)->get();
		 if(count($Collection)>0)
			{
			foreach($Collection as $_coll)
			{
				$details = EmpOffline::where("id",$_coll->id)->first();
				
				/*update Obj*/
				$updateOBJ = EmpOffline::find($_coll->id);
				/*update Obj*/								
				$createdAT = $details->created_at;
				/*				
				$days INterbakl
				
				*/
				$doj = str_replace("/","-",$createdAT);
				$date1 = date("Y-m-d",strtotime($doj));
				$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);
				//echo $diff;exit;
				//$daysInterval=
				$updateOBJ->Vintage_days = $daysInterval;
				$updateOBJ->Vintage_updated_date = $dateC;
				$updateOBJ->save();
				
			}
			}
			else
			{
				//echo "All DONe";
				exit;
			}
	
	}	

	   public function filterByCandidateNamevisapipeline(Request $request)
		{
			$cname = $request->cname;
			//echo $cname;exit;
			$request->session()->put('cname_empvisapipeline_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailvisapipeline(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candvisapipeline_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationvisapipeline(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candvisapipeline_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentvisapipeline(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candvisapipeline_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningvisapipeline(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candvisapipeline_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatusvisapipeline(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candvisapipeline_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintagevisapipeline(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candvisapipeline_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyvisapipeline(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candvisapipeline_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		public function okForVisaPost(Request $request){
			$docid=$request->docId;
			$detailsObj = EmpOffline::find($docid);
			$detailsObj->ok_visa = 2; 
			$detailsObj->save();
		}
		public function documentcollectionbyfilterEmpOfflineProcess(Request $request)
		{
			
			//print_r($request->input());exit;
			$name = $request->input('candidatename');
			
			$job_openingarray = $request->input('job_opening');
			if($job_openingarray!=''){
			$job_opening=implode(",", $job_openingarray);
			}
			else{
				$job_opening='';
			}
			$RecruiterNamearray=$request->input('recruiterName');
			if($RecruiterNamearray!=''){
			$RecruiterName=implode(",", $RecruiterNamearray);
			}
			else{
				$RecruiterName='';
			}
			//echo $RecruiterName;exit;
			$request->session()->put('cname_emp_filter_inner_list',$name);
			$request->session()->put('opening_cand_filter_inner_list',$job_opening);
			$request->session()->put('company_RecruiterName_filter_inner_list',$RecruiterName);
			
			
			
		}
		public function documentresetfilterEmpOfflineProcess(Request $request)
		{
			
			$request->session()->put('cname_emp_filter_inner_list','');
			$request->session()->put('opening_cand_filter_inner_list','');
			$request->session()->put('company_RecruiterName_filter_inner_list','');
		}
	
public function searchFlagRuleData(Request $request)
		{
			//print_r($request->input());
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
			$name='';
			if($request->input('emp_name')!=''){
			 
			 $name=implode(",", $request->input('emp_name'));
			}
			//$name = $request->input('emp_name');
			$empId='';
			if($request->input('empId')!=''){
			 
			 $empId=implode(",", $request->input('empId'));
			}
			$design='';
			if($request->input('designationdata')!=''){
			 
			 $design=implode(",", $request->input('designationdata'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			//02-9-2023
			$ReasonofAttrition='';
			if($request->input('ReasonofAttrition')!=''){
			 
			 $ReasonofAttrition=implode(",", $request->input('ReasonofAttrition'));
			}
			$offboardstatus='';
			if($request->input('offboardstatus')!=''){
			 
			 $offboardstatus=implode(",", $request->input('offboardstatus'));
			}
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}



			$rangeid='';
			if($request->input('rangeid')!=''){
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}




			
			$request->session()->put('name_emp_offboard_filter_inner_list',$name);

			$request->session()->put('range_filter_inner_list',$rangeid);




			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			$request->session()->put('datefrom_offboard_filter_inner_list',$datefrom);
			$request->session()->put('dateto_offboard_filter_inner_list',$dateto);
			$request->session()->put('departmentId_filter_inner_list',$department);
			$request->session()->put('teamleader_filter_inner_list',$teamlaed);
			
			$request->session()->put('design_empoffboard_filter_inner_list',$design);
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetfilterFlagRuleData(Request $request){
			$request->session()->put('datefrom_offboard_filter_inner_list','');
			$request->session()->put('dateto_offboard_filter_inner_list','');
			$request->session()->put('departmentId_filter_inner_list','');
			$request->session()->put('teamleader_filter_inner_list','');
			$request->session()->put('name_emp_offboard_filter_inner_list','');
			$request->session()->put('empid_emp_offboard_filter_inner_list','');
			$request->session()->put('design_empoffboard_filter_inner_list','');
			$request->session()->put('dateto_offboard_lastworkingday_list','');
			$request->session()->put('datefrom_offboard_lastworkingday_list','');
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
			$request->session()->put('empoffboard_status_filter_list','');
			$request->session()->put('datefrom_offboard_dort_list','');
			$request->session()->put('dateto_offboard_dort_list','');
			$request->session()->put('empoffboard_ffstatus_filter_list','');
		}




		public function getRequestedSalaryData(Request $request)
		{
			$target = $request->targetid;			
			$bank = $request->bankid;


			$flagRuleRequest = MasterPayout::where('dept_id',$bank)
			->where('agent_target',$target)
			->first();

			if($flagRuleRequest)
			{
				return $flagRuleRequest->basic_salary;
			}
			else{
				return '';
			}




		}
	
}
