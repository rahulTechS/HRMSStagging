<?php
namespace App\Http\Controllers\Autoloan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AutoloanMIS\AutoloanMIS;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use App\Models\Employee\Employee_attribute;
use App\Models\Dashboard\MasterPayout;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Recruiter\Designation;
use App\Models\SEPayout\RangeDetailsVintage;

use Session;
ini_set("max_execution_time", 0);
class AutoloanMISController extends Controller
{
   
    public function auto_loan_mis()
    {
		$AutoLoanMISDetails = AutoloanMIS::orderBy("created_at","DESC")->get();        
        return view("AutoloanMIS/AutoloanMIS",compact('AutoLoanMISDetails'));
    }
	
	
	public function setPaginationValueAutoloan(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("AutoloanMIS");
	}

	
	
	public function loadAutoLoanMIS(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		//$paginationValue = 20;
		$whereRaw = " id!=''";	

		

		if(@$request->session()->get('team_internal') != '')
		{
			$team = $request->session()->get('team_internal');
			$team_str = '';
			foreach($team as $team_value)
			{
				if($team_str == '')
				{
					$team_str = "'".$team_value."'";
				}
				else
				{
					$team_str = $team_str.","."'".$team_value."'";
				}
			}
			$whereRaw .= " AND team IN (".$team_str.")";	
			$searchValues['team_internal'] = $team;
			
		}

		if(@$request->session()->get('emp_id_internal') != '')
		{
			$emp_id = $request->session()->get('emp_id_internal');
			$emp_id_str = '';
			foreach($emp_id as $emp_id_value)
			{
				if($emp_id_str == '')
				{
					$emp_id_str = "'".$emp_id_value."'";
				}
				else
				{
					$emp_id_str = $emp_id_str.","."'".$emp_id_value."'";
				}
			}
			$whereRaw .= " AND emp_id IN (".$emp_id_str.")";	
			$searchValues['emp_id_internal'] = $emp_id;
			
		}

		if(@$request->session()->get('application_status_enbd_loan_internal') != '')
		{
			$application_status = $request->session()->get('application_status_enbd_loan_internal');
			$application_status_str = '';
			foreach($application_status as $application_status_value)
			{
				if($application_status_str == '')
				{
					$application_status_str = "'".$application_status_value."'";
				}
				else
				{
					$application_status_str = $application_status_str.","."'".$application_status_value."'";
				}
			}
			$whereRaw .= " AND application_status IN (".$application_status_str.")";	
			$searchValues['application_status_enbd_loan_internal'] = $application_status;
			
		}


		if(@$request->session()->get('tracker_id_autoloan') != '')
		{
			$tracker_id_autoloan = $request->session()->get('tracker_id_autoloan');						
			$whereRaw .= " AND tracker_id = '".$tracker_id_autoloan."'";	
			$searchValues['tracker_id_autoloan'] = $tracker_id_autoloan;
		}

		if(@$request->session()->get('customer_name_autoloan') != '')
		{
			$customer_name_autoloan = $request->session()->get('customer_name_autoloan');						
			$whereRaw .= " AND customer_name LIKE '%".$customer_name_autoloan."%'";	
			$searchValues['customer_name_autoloan'] = $customer_name_autoloan;
		}

		if(@$request->session()->get('dealer_private_autoloan') != '')
		{
			$dealer_private_autoloan = $request->session()->get('dealer_private_autoloan');						
			$whereRaw .= " AND dealer_private LIKE '%".$dealer_private_autoloan."%'";	
			$searchValues['dealer_private_autoloan'] = $dealer_private_autoloan;
		}

		if(@$request->session()->get('app_id_internal') != '')
		{
			$app_id_internal = $request->session()->get('app_id_internal');						
			$whereRaw .= " AND app_id = '".$app_id_internal."'";	
			$searchValues['app_id_internal'] = $app_id_internal;
		}	

		
		if($request->session()->get('start_login_date_internal') != '')
		{
			$start_login_date_internal = $request->session()->get('start_login_date_internal');			
			$whereRaw .= " AND login_date >='".date('Y-m-d',strtotime($start_login_date_internal))."'";
			$searchValues['start_login_date_internal'] = $start_login_date_internal;
			$login_flag = 1;
		}

		if($request->session()->get('end_login_date_internal') != '')
		{
			$end_login_date_internal = $request->session()->get('end_login_date_internal');			
			$whereRaw .= " AND login_date <='".date('Y-m-d',strtotime($end_login_date_internal))."'";
			$searchValues['end_login_date_internal'] = $end_login_date_internal;
			$login_flag = 1;
		}

		$currentDate = date("d",strtotime(date("Y-m-d")));
				
		if($currentDate<=20){
			$endDate = date("Y").'-'.date("m").'-'.'20';
		}
		else{
			$endDate = date("Y-m-d");
		}
		$startDate = date("Y",strtotime("-1 Month")).'-'.date("m",strtotime("-1 Month")).'-'.'21';
		
		$datasAutoLoanMISCount = DB::table('autoloan_internal_mis')->whereRaw($whereRaw)->whereRaw($whereRaw)->whereBetween('login_date', [$startDate, $endDate])->orderby('login_date','DESC')->get()->count();
		
		$datasAutoLoanMIS = DB::table('autoloan_internal_mis')->whereRaw($whereRaw)->whereRaw($whereRaw)->whereBetween('login_date', [$startDate, $endDate])->orderby('login_date','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("AutoloanMIS/loadAutoLoanMIS",compact('datasAutoLoanMIS','searchValues','seller_id','datasAutoLoanMISCount','Employee_details','user_id','username'));
	}

	

	public function searchAutoloanMISInner(Request $request)
	{
		$requestParameters = $request->input();

		$tracker_id_autoloan = '';
		$team_internal = '';
		$emp_id_internal = '';
		$customer_name_autoloan = '';
		$dealer_private_autoloan = '';
		$start_login_date_internal = '';
		$end_login_date_internal = '';

		if(@isset($requestParameters['team_internal']))
		{
			$team_internal = @$requestParameters['team_internal'];
		}
		if(@isset($requestParameters['emp_id_internal']))
		{
			$emp_id_internal = @$requestParameters['emp_id_internal'];
		}

		if(@isset($requestParameters['tracker_id_autoloan']))
		{
			$tracker_id_autoloan = @$requestParameters['tracker_id_autoloan'];
		}
		if(@isset($requestParameters['customer_name_autoloan']))
		{
			$customer_name_autoloan = @$requestParameters['customer_name_autoloan'];
		}
		if(@isset($requestParameters['dealer_private_autoloan']))
		{
			$dealer_private_autoloan = @$requestParameters['dealer_private_autoloan'];
		}
		if(@isset($requestParameters['start_login_date_internal']))
		{
			$start_login_date_internal = @$requestParameters['start_login_date_internal'];
		}
		if(@isset($requestParameters['end_login_date_internal']))
		{
			$end_login_date_internal = @$requestParameters['end_login_date_internal'];
		}
		
		
		$request->session()->put('team_internal',$team_internal);
		$request->session()->put('emp_id_internal',$emp_id_internal);
		$request->session()->put('tracker_id_autoloan',$tracker_id_autoloan);
		$request->session()->put('customer_name_autoloan',$customer_name_autoloan);
		$request->session()->put('dealer_private_autoloan',$dealer_private_autoloan);
		$request->session()->put('start_login_date_internal',$start_login_date_internal);
		$request->session()->put('end_login_date_internal',$end_login_date_internal);
		return redirect("loadAutoLoanMIS");
	}

	
	
	public function resetAutoloanMISInner(Request $request)
	{	
		$request->session()->put('team_internal','');
		$request->session()->put('emp_id_internal','');
		$request->session()->put('tracker_id_autoloan','');
		$request->session()->put('customer_name_autoloan','');
		$request->session()->put('dealer_private_autoloan','');
		$request->session()->put('start_login_date_internal','');
		$request->session()->put('end_login_date_internal','');
		return redirect("loadAutoLoanMIS");
	}

	
	public function addAutoloanMISData()
    {
		  	  
		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();        
		  return view("AutoloanMIS/addAutoloanMISData",compact('Employee_details'));
    }
	

	public function editAutoloanMISData($id=NULL)
    {
		  $AutoLoanMISData = DB::table('autoloan_internal_mis')->where('id', $id)->first();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("AutoloanMIS/editAutoloanMISData",compact('AutoLoanMISData','Employee_details'));
    }


	
	public function addAutoloanMISPost(Request $req)
    {			
			
			
			$user_id = $req->session()->get('EmployeeId');

			$emp_id = $req->input('emp_id');
			$team = $req->input('team');

			$Employee_details_data = $this->getEmployeeDetails($emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			
			$login_date = ($req->input('login_date')?date('Y-m-d',strtotime($req->input('login_date'))):date('Y-m-d'));
			$location = $req->input('location')?$req->input('location'):'Dubai';
			$tracker_id = $req->input('tracker_id');
			$customer_name = $req->input('customer_name');
			$contact = $req->input('contact');
			$loan = $req->input('loan');
			$tenure = $req->input('tenure');
			$ro_name = $emp_name;
			$dealer_private = $req->input('dealer_private');
			$make_and_model = $req->input('make_and_model');
			$private_dealer = $req->input('private_dealer');
			$car_value = $req->input('car_value');
			$license = $req->input('license');
			$visa = $req->input('visa');
			$registere = $req->input('registere');
			$stage = $req->input('stage');
			$lpo = ($req->input('lpo')?date('Y-m-d',strtotime($req->input('lpo'))):'0000-00-00');
			$resubmitted = $req->input('resubmitted');
			$remark_1 = addslashes(str_replace("'","`",$req->input('remark_1')));
			$created_at = date('Y-m-d H:i:s');

			$values = array('location' => $location,'user_id' => $user_id,'emp_id' => $emp_id,'emp_name'=>$emp_name,'team' => $team,'login_date' => $login_date,'tracker_id' => $tracker_id,'customer_name' => $customer_name,'contact' => $contact,'loan' => $loan,'tenure' => $tenure,'ro_name' => $ro_name,'dealer_private' => $dealer_private,'make_and_model' => $make_and_model,'private_dealer' => $private_dealer,'car_value' => $car_value,'license' => $license,'visa' => $visa,'registere' => $registere,'stage' => $stage,'lpo' => $lpo,'resubmitted' => $resubmitted,'remark_1' => $remark_1,'created_at' => $created_at);
			DB::table('autoloan_internal_mis')->insert($values);		            
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('AutoloanMIS');
    }

	

	public function editAutoloanMISPost(Request $req)
    {	

			$emp_id = $req->input('emp_id');
			$team = $req->input('team');

			$Employee_details_data = $this->getEmployeeDetails($emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

		    $login_date = ($req->input('login_date')?date('Y-m-d',strtotime($req->input('login_date'))):date('Y-m-d'));
			$location = $req->input('location')?$req->input('location'):'Dubai';
			$tracker_id = $req->input('tracker_id');
			$customer_name = $req->input('customer_name');
			$contact = $req->input('contact');
			$loan = $req->input('loan');
			$tenure = $req->input('tenure');
			$ro_name = $emp_name;
			$dealer_private = $req->input('dealer_private');
			$make_and_model = $req->input('make_and_model');
			$private_dealer = $req->input('private_dealer');
			$car_value = $req->input('car_value');
			$license = $req->input('license');
			$visa = $req->input('visa');
			$registere = $req->input('registere');
			$stage = $req->input('stage');
			$lpo = ($req->input('lpo')?date('Y-m-d',strtotime($req->input('lpo'))):'0000-00-00');
			$resubmitted = $req->input('resubmitted');
			$remark_1 = addslashes(str_replace("'","`",$req->input('remark_1')));

		
			$values = array('location' => $location,'emp_id' => $emp_id,'emp_name'=>$emp_name,'team' => $team, 'login_date' => $login_date,'tracker_id' => $tracker_id,'customer_name' => $customer_name,'contact' => $contact,'loan' => $loan,'tenure' => $tenure,'ro_name' => $ro_name,'dealer_private' => $dealer_private,'make_and_model' => $make_and_model,'private_dealer' => $private_dealer,'car_value' => $car_value,'license' => $license,'visa' => $visa,'registere' => $registere,'stage' => $stage,'lpo' => $lpo,'resubmitted' => $resubmitted,'remark_1' => $remark_1);

			DB::table('autoloan_internal_mis')->where('id', $req->input('id'))->update($values);
           
            $req->session()->flash('message','Record updated Successfully.');
            return redirect('AutoloanMIS');
    }
	

	
	public static function getEmployeeDetails($id=NULL)
    {
		return $Employee_details =  Employee_details::where("emp_id",$id)->first();
    }





	// new start


	public function loadAutoLoanMISHistoricData(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		//$paginationValue = 20;
		$whereRaw = " id!=''";	

		

		if(@$request->session()->get('team_internal_autoloanHistorical') != '')
		{
			$team = $request->session()->get('team_internal_autoloanHistorical');
			$team_str = '';
			foreach($team as $team_value)
			{
				if($team_str == '')
				{
					$team_str = "'".$team_value."'";
				}
				else
				{
					$team_str = $team_str.","."'".$team_value."'";
				}
			}
			$whereRaw .= " AND team IN (".$team_str.")";	
			$searchValues['team_internal_autoloanHistorical'] = $team;
			
		}

		if(@$request->session()->get('emp_id_internal_autoloanHistorical') != '')
		{
			$emp_id = $request->session()->get('emp_id_internal_autoloanHistorical');
			$emp_id_str = '';
			foreach($emp_id as $emp_id_value)
			{
				if($emp_id_str == '')
				{
					$emp_id_str = "'".$emp_id_value."'";
				}
				else
				{
					$emp_id_str = $emp_id_str.","."'".$emp_id_value."'";
				}
			}
			$whereRaw .= " AND emp_id IN (".$emp_id_str.")";	
			$searchValues['emp_id_internal_autoloanHistorical'] = $emp_id;
			
		}

		if(@$request->session()->get('application_status_enbd_loan_internal') != '')
		{
			$application_status = $request->session()->get('application_status_enbd_loan_internal');
			$application_status_str = '';
			foreach($application_status as $application_status_value)
			{
				if($application_status_str == '')
				{
					$application_status_str = "'".$application_status_value."'";
				}
				else
				{
					$application_status_str = $application_status_str.","."'".$application_status_value."'";
				}
			}
			$whereRaw .= " AND application_status IN (".$application_status_str.")";	
			$searchValues['application_status_enbd_loan_internal'] = $application_status;
			
		}


		if(@$request->session()->get('tracker_id_autoloan_autoloanHistorical') != '')
		{
			$tracker_id_autoloan = $request->session()->get('tracker_id_autoloan_autoloanHistorical');						
			$whereRaw .= " AND tracker_id = '".$tracker_id_autoloan."'";	
			$searchValues['tracker_id_autoloan_autoloanHistorical'] = $tracker_id_autoloan;
		}

		if(@$request->session()->get('customer_name_autoloan_autoloanHistorical') != '')
		{
			$customer_name_autoloan = $request->session()->get('customer_name_autoloan_autoloanHistorical');						
			$whereRaw .= " AND customer_name LIKE '%".$customer_name_autoloan."%'";	
			$searchValues['customer_name_autoloan_autoloanHistorical'] = $customer_name_autoloan;
		}

		if(@$request->session()->get('dealer_private_autoloan_autoloanHistorical') != '')
		{
			$dealer_private_autoloan = $request->session()->get('dealer_private_autoloan_autoloanHistorical');						
			$whereRaw .= " AND dealer_private LIKE '%".$dealer_private_autoloan."%'";	
			$searchValues['dealer_private_autoloan_autoloanHistorical'] = $dealer_private_autoloan;
		}

		if(@$request->session()->get('app_id_internal') != '')
		{
			$app_id_internal = $request->session()->get('app_id_internal');						
			$whereRaw .= " AND app_id = '".$app_id_internal."'";	
			$searchValues['app_id_internal'] = $app_id_internal;
		}	

		
		if($request->session()->get('start_login_date_internal_autoloanHistorical') != '')
		{
			$start_login_date_internal = $request->session()->get('start_login_date_internal_autoloanHistorical');			
			$whereRaw .= " AND login_date >='".date('Y-m-d',strtotime($start_login_date_internal))."'";
			$searchValues['start_login_date_internal_autoloanHistorical'] = $start_login_date_internal;
			$login_flag = 1;
		}

		if($request->session()->get('end_login_date_internal_autoloanHistorical') != '')
		{
			$end_login_date_internal = $request->session()->get('end_login_date_internal_autoloanHistorical');			
			$whereRaw .= " AND login_date <='".date('Y-m-d',strtotime($end_login_date_internal))."'";
			$searchValues['end_login_date_internal_autoloanHistorical'] = $end_login_date_internal;
			$login_flag = 1;
		}

		
		
		$datasAutoLoanMISCount = DB::table('autoloan_internal_mis')->whereRaw($whereRaw)->orderby('login_date','DESC')->get()->count();
		
		$datasAutoLoanMIS = DB::table('autoloan_internal_mis')->whereRaw($whereRaw)->orderby('login_date','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("AutoloanMIS/loadAutoLoanMISHistoric",compact('datasAutoLoanMIS','searchValues','seller_id','datasAutoLoanMISCount','Employee_details','user_id','username'));
	}




	public function searchAutoloanMISInnerHistoricData(Request $request)
	{
		$requestParameters = $request->input();

		$tracker_id_autoloan = '';
		$team_internal = '';
		$emp_id_internal = '';
		$customer_name_autoloan = '';
		$dealer_private_autoloan = '';
		$start_login_date_internal = '';
		$end_login_date_internal = '';

		if(@isset($requestParameters['team_internal_autoloanHistorical']))
		{
			$team_internal = @$requestParameters['team_internal_autoloanHistorical'];
		}
		if(@isset($requestParameters['emp_id_internal_autoloanHistorical']))
		{
			$emp_id_internal = @$requestParameters['emp_id_internal_autoloanHistorical'];
		}

		if(@isset($requestParameters['tracker_id_autoloan_autoloanHistorical']))
		{
			$tracker_id_autoloan = @$requestParameters['tracker_id_autoloan_autoloanHistorical'];
		}
		if(@isset($requestParameters['customer_name_autoloan_autoloanHistorical']))
		{
			$customer_name_autoloan = @$requestParameters['customer_name_autoloan_autoloanHistorical'];
		}
		if(@isset($requestParameters['dealer_private_autoloan_autoloanHistorical']))
		{
			$dealer_private_autoloan = @$requestParameters['dealer_private_autoloan_autoloanHistorical'];
		}
		if(@isset($requestParameters['start_login_date_internal_autoloanHistorical']))
		{
			$start_login_date_internal = @$requestParameters['start_login_date_internal_autoloanHistorical'];
		}
		if(@isset($requestParameters['end_login_date_internal_autoloanHistorical']))
		{
			$end_login_date_internal = @$requestParameters['end_login_date_internal_autoloanHistorical'];
		}
		
		
		$request->session()->put('team_internal_autoloanHistorical',$team_internal);
		$request->session()->put('emp_id_internal_autoloanHistorical',$emp_id_internal);
		$request->session()->put('tracker_id_autoloan_autoloanHistorical',$tracker_id_autoloan);
		$request->session()->put('customer_name_autoloan_autoloanHistorical',$customer_name_autoloan);
		$request->session()->put('dealer_private_autoloan_autoloanHistorical',$dealer_private_autoloan);
		$request->session()->put('start_login_date_internal_autoloanHistorical',$start_login_date_internal);
		$request->session()->put('end_login_date_internal_autoloanHistorical',$end_login_date_internal);
		return redirect("loadAutoLoanMISHistoric");
	}

	
	
	public function resetAutoloanMISInnerHistoricData(Request $request)
	{	
		$request->session()->put('team_internal_autoloanHistorical','');
		$request->session()->put('emp_id_internal_autoloanHistorical','');
		$request->session()->put('tracker_id_autoloan_autoloanHistorical','');
		$request->session()->put('customer_name_autoloan_autoloanHistorical','');
		$request->session()->put('dealer_private_autoloan_autoloanHistorical','');
		$request->session()->put('start_login_date_internal_autoloanHistorical','');
		$request->session()->put('end_login_date_internal_autoloanHistorical','');
		return redirect("loadAutoLoanMISHistoric");
	}










	
	
// Export Agent Performance Module Start

public function exportAgentPerformanceDataAutoloanMISData(Request $request)
{
		$start_date_application_SCB_internal = '';
		$end_date_application_SCB_internal = '';
		$whereRaw = '';
		$whereRawBank = "tracker_id != ''";
	
		if($request->session()->get('start_login_date') != '')
		{
			$start_date_application_SCB_internal = $request->session()->get('start_login_date');			
			$whereRaw .= "login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
		elseif($request->session()->get('start_login_date_internal_autoloanHistorical') != '')
		{
			$start_date_application_SCB_internal = $request->session()->get('start_login_date_internal_autoloanHistorical');			
			$whereRaw .= "login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
	    else
		{
			$start_date_application_SCB_internal = date("Y")."-".date("m")."-01";			
			$whereRaw .= "login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
		if($request->session()->get('end_login_date') != '')
		{
			$end_date_application_SCB_internal = $request->session()->get('end_login_date');			
			$whereRaw .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}	
		elseif($request->session()->get('end_login_date_internal_autoloanHistorical') != '')
		{
			$end_date_application_SCB_internal = $request->session()->get('end_login_date_internal_autoloanHistorical');			
			$whereRaw .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}	
		else
		{
			$end_date_application_SCB_internal = date("Y-m-d");	
			$whereRaw .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND login_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}
		/* echo $start_date_application_SCB_internal;
		echo "<pre>";
		echo $end_date_application_SCB_internal;
		exit; */
			/*
			*-1,-2 month Name
			*start code
			*/

			// $endDate = '2024-08-30';
			// $startDate = '2024-06-01';
			
			// $whereRawBank = "app_id != '' AND login_date >='$startDate' AND login_date <='$endDate'";


			//$whereRawBank='';
			// echo $whereRawBank;
			// echo $whereRaw;
			// exit;
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			// $collectionModel = AutoloanMIS::selectRaw('count(*) as total, emp_id,team,vintage,range_id,doj,agent_code')
			// 									  ->groupBy('emp_id')
			// 									  ->whereRaw($whereRaw)
			// 									  ->get();



			$collectionModel = AutoloanMIS::selectRaw('count(*) as total, emp_id,team')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRaw)
												  ->get();


			// print_r($collectionModel);
			// die;
		
		    $filename = 'Agent_performance_Auto_Loans_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R2');
			$sheet->setCellValue('Q1', 'Agents Performance Auto Loans - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).'to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$indexCounter = 5;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent Emp Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('SM Manager'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Total Submissions'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Vintage'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Range Id'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Designation'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Agent Salary'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('DOJ'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$empMoreThanZeroSubmission = array();
			$totalSubmission = 0;
			$totalBookingBank = 0;
			$totalBookingMTD = 0;
			$totalLastBooking = 0;
			$totalLastBookingP = 0;
			$totalBooking = 0;
			$t1Total = 0;
			$t2Total = 0;
			$usedEmp = array();
			$totalNotCaptured = 0;
			foreach ($collectionModel as $model) {
				if($model->emp_id != '')
				{
					$usedEmp[] = $model->emp_id; 
					$vintageDays = '-';
					$empAttr = Employee_attribute::where("emp_id",$model->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
					$empMoreThanZeroSubmission[] = $model->emp_id;
					$totalBankBooking = AutoloanMIS::select("id")->where("emp_id",$model->emp_id)->whereIn("stage",array("END"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $model->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeName($model->emp_id))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $model->team)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $model->total)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $totalBankBooking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($model->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($model->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($model->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($model->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($model->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($model->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($model->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($model->emp_id);
					
						$totalBooking = $totalBooking+$totalBankBooking;
					
					
					$sheet->setCellValue('Q'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$model->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				}
			}
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
				$empwithZeroSubmission = Employee_details::where("dept_id",9)
								->whereNotIn("emp_id",$empMoreThanZeroSubmission)
								->where("job_function",2)
								->get();
				
				foreach($empwithZeroSubmission as $zeroSubmission)
				{
					if($zeroSubmission->offline_status != 1)
					{
						
					$offlineEmp = DB::table('offline_empolyee_details')->whereRaw("emp_id='".$zeroSubmission->emp_id."' AND last_working_day_resign>='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."' AND last_working_day_resign IS NOT NULL")->get();
					/*
					*check Emp exist in last submission
					*/
					$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
					$pYear = date("Y",strtotime($previousdate));
					$pMonth = date("m",strtotime($previousdate));
					$startDate = $pYear."-".$pMonth."-01";
					$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
					$endDate = $pYear."-".$pMonth."-".$d;
					$totalBankBooking = AutoloanMIS::select("id")->where("emp_id",$zeroSubmission->emp_id)->whereIn("stage",array("END"))->whereBetween("login_date",[$startDate,$endDate])->get()->count();
					if($totalBankBooking >0)
					{
						$offlineEmp = DB::table('offline_empolyee_details')->where("emp_id",$zeroSubmission->emp_id)->get();
					}
					/*
					*check Emp exist in last submission
					*/
					if(count($offlineEmp)>0)
					{
						$usedEmp[] = $zeroSubmission->emp_id;
						$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$zeroSubmission->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_SCB_internal) && $doj != '')
						{
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				
					$sheet->setCellValue('R'.$indexCounter,$zeroSubmission->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
					$totalBooking = $totalBooking+0;
					
					}
					else
					{
						continue;
					}
				
					}
					}
					else
					{
						$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$zeroSubmission->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_SCB_internal) && $doj != '')
						{
							$usedEmp[] = $zeroSubmission->emp_id;
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$zeroSubmission->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalBooking = $totalBooking+0;
						}
						
						
						
					
						
					}
					
			
				}
				
				
				$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				
				 $collectionModelMissing = AutoloanMIS::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereDate('login_date', '>=', $startDateMissing)
												  ->whereNotIn('emp_id',$usedEmp)
												 
												  ->get();
												  
				foreach($collectionModelMissing as $missing)
				{
				$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$missing->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
				$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $missing->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $missing->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($missing->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($missing->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($missing->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($missing->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($missing->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($missing->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($missing->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($missing->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($missing->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($missing->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$missing->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalBooking = $totalBooking+0;

				}					
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':R'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('C'.$indexCounter, "Total")->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalSubmission)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalBookingBank)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $totalLastBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $totalLastBookingP)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, $t1Total)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, $t2Total)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			 
			$approvalRateALL =  @round(($totalBooking/$totalSubmission),2);
		
			$sheet->setCellValue('Q'.$indexCounter,$approvalRateALL)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			for($col = 'A'; $col !== 'R'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
					$spreadsheet->getActiveSheet()->getStyle('A1:R2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','R') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$spreadsheet->getActiveSheet()->setTitle('Agent Reports');
				$spreadsheet->createSheet(1); 
				$spreadsheet->setActiveSheetIndex(1); 
				$spreadsheet->getActiveSheet()->setTitle('TL Reports'); 
				/*
				*Sheet2
				*/
				$this->sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$start_date_application_SCB_internal,$end_date_application_SCB_internal);
				$spreadsheet->createSheet(2); 
				$spreadsheet->setActiveSheetIndex(2); 
				$spreadsheet->getActiveSheet()->setTitle('Flag Details');
				/*
				*Sheet3
				*/
				$this->sheet3FlagDetails($spreadsheet,$start_date_application_SCB_internal,$end_date_application_SCB_internal);
				// $logObj = new ExportDataLog();
				// $logObj->user_id =$request->session()->get('EmployeeId');
				// $logObj->download_date =date("Y-m-d");
				// $logObj->tilte ="SCB-Final-Report";					
				// $logObj->save();
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
}


public function getTLName($empId)
{
		$empDetailsModel = Employee_details::select("tl_id")->where("emp_id",$empId)->first();
		if($empDetailsModel != '')
		{
			$tlID = $empDetailsModel->tl_id;
			if($tlID != '' && $tlID != NULL)
			{
				$empTlName = Employee_details::select("export_name")->where("id",$tlID)->first()->export_name;

				if($empTlName!='')
				{
					return $empTlName;
				}
				else
				{
					$empDetailsData = Employee_details::where("id",$tlID)->where("job_function",3)->first();

					if($empDetailsData)
					{
						return $empDetailsData->emp_name;
					}
					else
					{
						return "--";
					}
				}
				
			}
			else
			{
				return "-";
			}
		}
		else
		{
			return "-";
		}

		
}


protected function lastMonthBooking($empId,$start_date_application_SCB_internal)
{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",9)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
		/* $previousMonthPayout = date("m-Y",strtotime($start_date_application_SCB_internal." -1 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",47)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			return 0;
		} */
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		/* echo $startDate;
		echo "<br />";
		echo $endDate;
		exit;	 */	
		$totalBankBooking = AutoloanMIS::select("id")->where("emp_id",$empId)->whereIn("stage",array("END"))->whereBetween("login_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
}
	
	
	protected function lastMonthBookingP($empId,$start_date_application_SCB_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",9)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
		/* $previousMonthPayout = date("m-Y",strtotime($start_date_application_SCB_internal." -1 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",47)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			return 0;
		} */
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		/* echo $startDate;
		echo "<br />";
		echo $endDate;
		exit;	 */	
		$totalBankBooking = AutoloanMIS::select("id")->where("emp_id",$empId)->whereIn("stage",array("END"))->whereBetween("login_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
	}


	protected function getrecruiterNameSCB($empid = NULL)
	{
		$recruiterMod = Employee_details::where("emp_id",$empid)->first();
		if($recruiterMod != '')
		{
			$recruiter = $recruiterMod->recruiter;
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
		else
		{
			return ''; 
		}
	}
	
	protected function getrecruiterCatSCB($empid = NULL)
	{
		$recruiterMod = Employee_details::where("emp_id",$empid)->first();
		if($recruiterMod != '')
		{
			$recruiter = $recruiterMod->recruiter;
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
		else
		{
			return ''; 
		}
	}



	protected function getRangeIdData($vintageDays)
	{
		if($vintageDays < 711 )
		{
			if($vintageDays != '' && $vintageDays != NULL)
			{
				return RangeDetailsVintage::where("vintage",$vintageDays)->first()->range_id;
			}
			else
			{
				return "-";
			}
		}
		else
		{
			return '25';
		}
	}
			
			public function getDesignation($empId)
			{
				$empDetailsModel = Employee_details::select("designation_by_doc_collection")->where("emp_id",$empId)->first();
				if($empDetailsModel != '')
				{
					$empdesignationId = $empDetailsModel->designation_by_doc_collection;
					if($empdesignationId != '' && $empdesignationId != NULL)
					{
						$designationMod = Designation::select("name")->where("id",$empdesignationId)->first();
						if($designationMod != '')
						{
							return $designationMod->name;
						}
						else
						{
							return "-";
						}
					}
					else
					{
						return "-";
					}
				}
				else
				{
					return "-";
				}
			}


	protected function t1Submissions($empId)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return AutoloanMIS::select("id")->whereDate("login_date","=",$previousDate)->where("emp_id",$empId)->get()->count();
		
	}
	protected function t2Submissions($empId)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return AutoloanMIS::select("id")->whereBetween("login_date",[$StartDate,$endDate])->where("emp_id",$empId)->get()->count();
		
	}

	protected function getAgentSalary($empId)
	{
		$empDetailsModel = Employee_attribute::select("attribute_values")->where("emp_id",$empId)->where("attribute_code","total_gross_salary")->first();
			if($empDetailsModel != '')
			{
				$basic_salary = $empDetailsModel->attribute_values;
				if($basic_salary != '' && $basic_salary != NULL)
				{
					return $basic_salary;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				return 0;
			}
	}

	protected function getApprovalRate($totalSubmission,$booking)
	{
		
			if($totalSubmission <= 0)
			{
				return 0;
			}
			else
			{
			return round(($booking/$totalSubmission),2);
			}
		
	}




	protected function sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
		$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			
		$collectionModel = AutoloanMIS::selectRaw('count(*) as total,team')
												->groupBy('team')
												->whereRaw($whereRaw)
												->get();


		// echo "<pre>";
		// print_r($collectionModel);
		// exit;




		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A1:H2');
		$sheet->setCellValue('A1', 'TL Performance ENBD Auto Loans - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).' to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
		$indexCounter = 5;
		$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Manager'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('C'.$indexCounter, strtoupper('Total Submissions'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('D'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('F'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('G'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('H'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
		$sheet->setCellValue('I'.$indexCounter, strtoupper('Submission to Booking'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


		/*
				*Sheet2
				*/
				$sn = 1;
			$empMoreThanZeroSubmission = array();
			$totalSubmission = 0;
			$totalBookingBank = 0;
			$totalBookingMTD = 0;
			$totalLastBooking = 0;
			$totalLastBookingP = 0;
			$totalBooking = 0;
			$t1Total = 0;
			$t2Total = 0;
			$teamValue = array();
				foreach ($collectionModel as $model) 
				{
					if($model->team != '')
					{
					
						
						$teamValue[] = $model->team;
						$totalBankBooking = AutoloanMIS::select("id")->where("team",$model->team)->whereIn("stage",array("END"))->whereRaw($whereRawBank)->get()->count();
						$indexCounter++;
						
						$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('G'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('H'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$totalSubmission = $totalSubmission+$model->total;
						$totalBookingBank = $totalBookingBank+$totalBankBooking;
						
						$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal);
						$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal);
						$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
						$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
						$totalBooking = $totalBooking+$totalBankBooking;
						
						
						
						$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
						$sheet->setCellValue('I'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
						
						$sn++;
					}
				}




				/*
			*adding missing team
			*/
				$previousdatePP =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
				$pYearPP = date("Y",strtotime($previousdatePP));
				$pMonthPP = date("m",strtotime($previousdatePP));
				$startDatePP = $pYearPP."-".$pMonthPP."-01";
				$collectionModelP = AutoloanMIS::selectRaw('team')
														->groupBy('team')
														->whereDate('login_date','>=',$startDatePP)
														->whereNotIn("team",$teamValue)
														->get();
														
				foreach ($collectionModelP as $model) 
				{
						if($model->team != '')
						{
				
					
					
					$totalBankBooking = AutoloanMIS::select("id")->where("team",$model->team)->whereIn("stage",array("END"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
					$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
					$totalBooking = $totalBooking+$totalBankBooking;
					
					
					
					$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
					$sheet->setCellValue('I'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					
					$sn++;
				}
	
		}			
		
		


				/**
			*Total Rows
			*/
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':T'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('B'.$indexCounter, "Total")->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $totalSubmission)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $totalBookingBank)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalLastBooking)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalLastBookingP)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $t1Total)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $t2Total)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			if($totalSubmission != 0)
			{
				
				$approvalRateALL =  round(($totalBooking/$totalSubmission),2);

			}
			else
			{
				$approvalRateALL = 0;
			}
			
			$sheet->setCellValue('I'.$indexCounter,$approvalRateALL)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
		/*	
		*Total Rows
		*/
		$indexCounter++;

			for($col = 'A'; $col !== 'I'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
	
			$spreadsheet->getActiveSheet()->getStyle('A1:I2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
		for($index=1;$index<=$indexCounter;$index++)
		{
			  foreach (range('A','I') as $col) {
					$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
			  }
		}

















	}





	protected function sheet3FlagDetails($spreadsheet,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
			$sheet->setCellValue('A1','Flag Details')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Employee ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$selectedId = DB::table('master_payout')->whereRaw("dept_id = '9'")->limit(3)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order']);
			
			$k=1;
			$sort_orders = '';
			foreach ($selectedId as $mis) 
			{
				if($k==1)
				{
					$col='C';
				}
				if($k==2)
				{
					$col='D';
				}
				if($k==3)
				{
					$col='E';
				}

				$sort_orders .= $mis->sort_order.',';

				$sheet->setCellValue($col.$indexCounter, $mis->sales_time)->getStyle($col.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$k++;
			}

			$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('G'.$indexCounter, strtoupper('DOJ'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sort_orders = substr($sort_orders,0,-1);
		
			
			$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '9' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);
			
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);

			$no_of_ele = count($exp_sort_orders);
			
			if($no_of_ele == 2)
			{
				array_push($exp_sort_orders,0);
			}
			if($no_of_ele == 1)
			{
				array_push($exp_sort_orders,0,0);
			}

			// print_r($exp_sort_orders);
			// exit;
			
			foreach ($selectedEmp as $selectedEmpData) 
			{
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				

				$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$FirstData = DB::table('master_payout')->whereRaw("dept_id = '9' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);	
				

				foreach($FirstData as $FirstDataVal)
				{
					$bgcolor = 'FFFFFF';
					if($FirstDataVal->flag_rule_name == 'Red')
					{
						$bgcolor = 'FF0000';
					}
					if($FirstDataVal->flag_rule_name == 'Green')
					{
						$bgcolor = '66cc66';
					}
					if($FirstDataVal->flag_rule_name == 'Yellow')
					{
						$bgcolor = 'ffff66';
					}
					$sheet->setCellValue('C'.$indexCounter, $FirstDataVal->tc)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':'.'C'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
				}

				$SecondData = DB::table('master_payout')->whereRaw("dept_id = '9' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

				foreach($SecondData as $SecondDataVal)
				{
					$bgcolor = 'FFFFFF';
					if($SecondDataVal->flag_rule_name == 'Red')
					{
						$bgcolor = 'FF0000';
					}
					if($SecondDataVal->flag_rule_name == 'Green')
					{
						$bgcolor = '66cc66';
					}
					if($SecondDataVal->flag_rule_name == 'Yellow')
					{
						$bgcolor = 'ffff66';
					}

					$sheet->setCellValue('D'.$indexCounter, $SecondDataVal->tc)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$spreadsheet->getActiveSheet()->getStyle('D'.$indexCounter.':'.'D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
				}


				if($exp_sort_orders[2]==0)
				{

				}
				else
				{
					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '9' AND sort_order ='".$exp_sort_orders[2]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

					foreach($ThirdData as $ThirdDataVal)
					{
						$bgcolor = 'FFFFFF';
						if($ThirdDataVal->flag_rule_name == 'Red')
						{
							$bgcolor = 'FF0000';
						}
						if($ThirdDataVal->flag_rule_name == 'Green')
						{
							$bgcolor = '66cc66';
						}
						if($ThirdDataVal->flag_rule_name == 'Yellow')
						{
							$bgcolor = 'ffff66';
						}
	
						$sheet->setCellValue('E'.$indexCounter, $ThirdDataVal->tc)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
	
						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}
				}

				

				$sheet->setCellValue('F'.$indexCounter, $selectedEmpData->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $selectedEmpData->doj)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				


				
				$sn++;
				
			}
			
			
			
			for($col = 'A'; $col !== 'G'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','G') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}





	protected function lastMonthBookingTeam($team,$start_date_application_SCB_internal)
	{
	
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		/* echo $saleEnd;
		exit; */
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",9)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
		$totalCard = 0;
		if(count($employeePayoutData) > 0)
		{
		 foreach($employeePayoutData as $empPayout)
		 {
			 $totalCard = $totalCard+$empPayout->tc;
		 }
			return $totalCard;
		/*
		*check master payout first
		*/		
		}
		else
		{
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		
		$totalBankBooking = AutoloanMIS::select("id")->where("team",$team)->whereIn("stage",array("END"))->whereBetween("login_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}
	
	
	protected function lastMonthBookingTeamP($team,$start_date_application_SCB_internal)
	{
	
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		/* echo $saleEnd;
		exit; */
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",9)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
		$totalCard = 0;
		if(count($employeePayoutData) > 0)
		{
		 foreach($employeePayoutData as $empPayout)
		 {
			 $totalCard = $totalCard+$empPayout->tc;
		 }
			return $totalCard;
		/*
		*check master payout first
		*/		
		}
		else
		{
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$totalBankBooking = AutoloanMIS::select("id")->where("team",$team)->whereIn("stage",array("END"))->whereBetween("login_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}


	protected function t1SubmissionsTeam($team)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return AutoloanMIS::select("id")->whereDate("login_date","=",$previousDate)->where("team",$team)->get()->count();
		
	}
	protected function t2SubmissionsTeam($team)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return AutoloanMIS::select("id")->whereBetween("login_date",[$StartDate,$endDate])->where("team",$team)->get()->count();
		
	}




	public function getTLNamefromBank($empid)
	{
		 if($empid != '' && $empid != NULL)
		 {
			$empName = AutoloanMIS::select("team")->where("emp_id",$empid)->first();
			if($empName != '')
			{
				return $empName->team;
			}
			else
			{
				return '--';
			}
		 }
		 else
		 {
			 return '--';
		 }
	}


	protected function getEmployeeName($empid)
	 {
		 if($empid != '' && $empid != NULL)
		 {
			$empName = Employee_details::select("emp_name")->where("emp_id",$empid)->first();
			if($empName != '')
			{
				return $empName->emp_name;
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



// Export Agent Performance Module End


	

}
