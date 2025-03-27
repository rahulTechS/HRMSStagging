<?php
namespace App\Http\Controllers\InsuranceProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceProcess\InsuranceProcess;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use Session;
ini_set("max_execution_time", 0);
class InsuranceProcessController extends Controller
{
   
    public function insurance_process_data()
    {
		$InsuranceProcessDetails = InsuranceProcess::orderBy("created_at","DESC")->get();        
        return view("InsuranceProcess/InsuranceProcess",compact('InsuranceProcessDetails'));
    }
	
	
	public function setPaginationValueInsuranceProcess(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("InsuranceProcess");
	}

	
	
	public function loadInsuranceProcess(Request $request)
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

		
		if(@$request->session()->get('POLICY_NUMBER_Insurance') != '')
		{
			$POLICY_NUMBER_Insurance = $request->session()->get('POLICY_NUMBER_Insurance');						
			$whereRaw .= " AND POLICY_NUMBER = '".$POLICY_NUMBER_Insurance."'";	
			$searchValues['POLICY_NUMBER_Insurance'] = $POLICY_NUMBER_Insurance;
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

		
		
		$datasInsuranceProcessCount = DB::table('insurance_nexus_list')->whereRaw($whereRaw)->orderby('created_at','DESC')->get()->count();
		
		$datasInsuranceProcess = DB::table('insurance_nexus_list')->whereRaw($whereRaw)->orderby('created_at','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("InsuranceProcess/loadInsuranceProcess",compact('datasInsuranceProcess','searchValues','seller_id','datasInsuranceProcessCount','Employee_details','user_id','username'));
	}

	

	public function searchInsuranceProcessInner(Request $request)
	{
		$requestParameters = $request->input();

		$POLICY_NUMBER_Insurance = '';
		$start_login_date_internal = '';
		$end_login_date_internal = '';

		if(@isset($requestParameters['POLICY_NUMBER_Insurance']))
		{
			$POLICY_NUMBER_Insurance = @$requestParameters['POLICY_NUMBER_Insurance'];
		}
		if(@isset($requestParameters['start_login_date_internal']))
		{
			$start_login_date_internal = @$requestParameters['start_login_date_internal'];
		}
		if(@isset($requestParameters['end_login_date_internal']))
		{
			$end_login_date_internal = @$requestParameters['end_login_date_internal'];
		}
		
		
		$request->session()->put('POLICY_NUMBER_Insurance',$POLICY_NUMBER_Insurance);
		$request->session()->put('start_login_date_internal',$start_login_date_internal);
		$request->session()->put('end_login_date_internal',$end_login_date_internal);
		return redirect("loadInsuranceProcess");
	}

	
	
	public function resetInsuranceProcessInner(Request $request)
	{	
		$request->session()->put('POLICY_NUMBER_Insurance','');
		$request->session()->put('start_login_date_internal','');
		$request->session()->put('end_login_date_internal','');
		return redirect("loadInsuranceProcess");
	}

	
	public function addInsuranceProcessData()
    {
		  	  
		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();        
		  return view("InsuranceProcess/addInsuranceProcessData",compact('Employee_details'));
    }
	

	public function editInsuranceProcessData($id=NULL)
    {
		  $InsuranceProcessData = DB::table('autoloan_internal_mis')->where('id', $id)->first();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("InsuranceProcess/editInsuranceProcessData",compact('InsuranceProcessData','Employee_details'));
    }


	
	public function addInsuranceProcessPost(Request $req)
    {			
			
			
			$user_id = $req->session()->get('EmployeeId');
			
			$login_date = ($req->input('login_date')?date('Y-m-d',strtotime($req->input('login_date'))):date('Y-m-d'));
			$tracker_id = $req->input('tracker_id');
			$customer_name = $req->input('customer_name');
			$contact = $req->input('contact');
			$loan = $req->input('loan');
			$tenure = $req->input('tenure');
			$ro_name = $req->input('ro_name');
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

			$values = array('user_id' => $user_id,'login_date' => $login_date,'tracker_id' => $tracker_id,'customer_name' => $customer_name,'contact' => $contact,'loan' => $loan,'tenure' => $tenure,'ro_name' => $ro_name,'dealer_private' => $dealer_private,'make_and_model' => $make_and_model,'private_dealer' => $private_dealer,'car_value' => $car_value,'license' => $license,'visa' => $visa,'registere' => $registere,'stage' => $stage,'lpo' => $lpo,'resubmitted' => $resubmitted,'remark_1' => $remark_1,'created_at' => $created_at);
			DB::table('autoloan_internal_mis')->insert($values);		            
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('InsuranceProcess');
    }

	

	public function editInsuranceProcessPost(Request $req)
    {	

		    $login_date = ($req->input('login_date')?date('Y-m-d',strtotime($req->input('login_date'))):date('Y-m-d'));
			$tracker_id = $req->input('tracker_id');
			$customer_name = $req->input('customer_name');
			$contact = $req->input('contact');
			$loan = $req->input('loan');
			$tenure = $req->input('tenure');
			$ro_name = $req->input('ro_name');
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

		
			$values = array('login_date' => $login_date,'tracker_id' => $tracker_id,'customer_name' => $customer_name,'contact' => $contact,'loan' => $loan,'tenure' => $tenure,'ro_name' => $ro_name,'dealer_private' => $dealer_private,'make_and_model' => $make_and_model,'private_dealer' => $private_dealer,'car_value' => $car_value,'license' => $license,'visa' => $visa,'registere' => $registere,'stage' => $stage,'lpo' => $lpo,'resubmitted' => $resubmitted,'remark_1' => $remark_1);

			DB::table('autoloan_internal_mis')->where('id', $req->input('id'))->update($values);
           
            $req->session()->flash('message','Record updated Successfully.');
            return redirect('InsuranceProcess');
    }
	

	
	public static function getEmployeeDetails($id=NULL)
    {
		return $Employee_details =  Employee_details::where("emp_id",$id)->first();
    }

	

}
