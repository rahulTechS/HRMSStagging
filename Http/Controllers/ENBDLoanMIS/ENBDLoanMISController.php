<?php
namespace App\Http\Controllers\ENBDLoanMIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ENBDLoanMIS\ENBDLoanMIS;
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
class ENBDLoanMISController extends Controller
{
   
    public function enbd_loan_mis()
    {
		$ENBDLoanMISDetails = ENBDLoanMIS::orderBy("created_at","DESC")->get();        
        return view("ENBDLoanMIS/ENBDLoanMIS",compact('ENBDLoanMISDetails'));
    }

	public function editDepartmentForm($id=NULL)
    {
	  $departmentFormDetails =   DepartmentForm::where("id",$id)->first();
      $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
	  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
	  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();
	  $FormProductDetails = FormProduct::where("status",1)->orwhere("status",2)->orderBy("product","ASC")->get();

	  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $departmentFormDetails->form_id)->get();

      return view("ENBDLoanMIS/editDepartmentForm",compact('departmentFormDetails','FormProductDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails'));
    }

	public function editDepartmentFormPost(Request $req)
    {
        $divisonObj = DepartmentForm::find($req->id);
		
        $divisonObj->department_id = $req->department_id;
		$divisonObj->form_title = $req->form_title;
		$divisonObj->product = $req->product;
		$divisonObj->status = $req->status;
        $divisonObj->save();

		$delete = DB::table('department_form_attribute')->where('form_id', $req->id)->delete();

		$postData = $req->input();
		$attribute_id = $postData['attribute_id'];		
		
		foreach($attribute_id as $k=>$v)
		{
			$values = array('form_id' => $req->id,'attribute_id' => $v,'sort_order' => ($postData['sort_order'][$v]?$postData['sort_order'][$v]:'9999'),'required' => $postData['required'][$v],'form_section' => $postData['form_section'][$v]);
			DB::table('department_form_attribute')->insert($values);
			
		}

        $req->session()->flash('message','Record Updated Successfully.');
        return redirect('departmentForm');
    }
	
	
	public function deleteDepartmentForm(Request $req)
    {
		$ravi_data_obj = DepartmentForm::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('departmentForm');
    }

	public function addDepartmentForm()
    {    
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();
		$FormProductDetails = FormProduct::where("status",1)->orwhere("status",2)->orderBy("product","ASC")->get();
        return view('ENBDLoanMIS/addDepartmentForm',compact('DepartmentDetails','FormProductDetails','masterAttributeDetails','FormSectionDetails'));
    }

    public function addDepartmentFormPost(Request $req)
    {
        
			$divison_obj = new DepartmentForm();			

			$divison_obj->department_id = $req->input('department_id');
			$divison_obj->form_title = $req->input('form_title');
			$divison_obj->product = $req->product;
			$divison_obj->status = $req->input('status');
            $divison_obj->save();
			$form_id = $divison_obj->id;

			$postData = $req->input();
			$attribute_id = $postData['attribute_id'];
			
			foreach($attribute_id as $k=>$v)
			{
				$values = array('form_id' => $form_id,'attribute_id' => $v,'sort_order' => ($postData['sort_order'][$v]?$postData['sort_order'][$v]:'9999'), 'required' => $postData['required'][$v],'form_section' => $postData['form_section'][$v]);
				DB::table('department_form_attribute')->insert($values);
				
			}			
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('departmentForm');
    }

	public function departmentFormSearch(Request $request)
    {			
			$form_id = @$_REQUEST['form_id'];
			$ref_no = @$_REQUEST['ref_no'];
			$emp_id = @$_REQUEST['emp_id'];
			$team = @$_REQUEST['team'];
			$customer = @$_REQUEST['customer'];
			$card_type = @$_REQUEST['card_type'];
			$start_date = @$_REQUEST['start_date'];
			$end_date = @$_REQUEST['end_date'];

			if($form_id!='')
			{
				$request->session()->put('form_id',$form_id);				
			}
			else
			{
				$request->session()->put('form_id','');		
			}

			if($ref_no!='')
			{
				$request->session()->put('ref_no',$ref_no);				
			}
			else
			{
				$request->session()->put('ref_no','');		
			}

			if($team!='')
			{
				$request->session()->put('team',$team);				
			}
			else
			{
				$request->session()->put('team','');		
			}

			if($emp_id!='')
			{
				$request->session()->put('emp_id',$emp_id);				
			}
			else
			{
				$request->session()->put('emp_id','');		
			}

			if($customer!='')
			{
				$request->session()->put('customer',$customer);				
			}
			else
			{
				$request->session()->put('customer','');		
			}

			if($card_type!='')
			{
				$request->session()->put('card_type',$card_type);				
			}
			else
			{
				$request->session()->put('card_type','');		
			}
			
			if($start_date!='')
			{
				$request->session()->put('start_date',$start_date);				
			}
			else
			{
				$request->session()->put('start_date','');		
			}

			if($end_date!='')
			{
				$request->session()->put('end_date',$end_date);				
			}
			else
			{
				$request->session()->put('end_date','');		
			}
			
			return redirect("departmentFormData/".$form_id);
				
	}

	public function departmentFormPaginationSearch(Request $request)
    {			
			$form_id = @$_REQUEST['form_id'];
			$paginationValue = @$_REQUEST['paginationValue'];
			

			if($paginationValue!='')
			{
				$request->session()->put('paginationValue',$paginationValue);				
			}
			else
			{
				$request->session()->put('paginationValue','');		
			}

			
			
			return redirect("departmentFormData/".$form_id);
				
	}

	public function departmentFormSearchReset($form_id=NULL, Request $request)
    {			
		$request->session()->put('form_id','');		
		$request->session()->put('ref_no','');	
		$request->session()->put('team','');
		$request->session()->put('emp_id','');			
		$request->session()->put('customer','');
		$request->session()->put('card_type','');	
		$request->session()->put('start_date','');
		$request->session()->put('end_date','');
		$request->session()->put('paginationValue','');
		$request->session()->put('ref_no_string','');	
		$request->session()->put('applicationid_string','');
		
		return redirect("departmentFormData/".$form_id);
				
	}
	
	public function setPaginationValueENBDLoan(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("ENBDLoanMIS");
	}

	public function setPaginationValueENBDLoanAECB(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("ENBDLoanMIS");
	}

	
	public function loadJonusMIS(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();

		$requestParameters = $request->input();
		$strVal = @$requestParameters['str'];
		if($strVal!='')
		{
			if($strVal=='undefined')
			{
				$strVal='Master';
			}
			$request->session()->put('str',$strVal);
		}
		$str = @$request->session()->get('str');


		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		
		$whereRaw = " applicationsid!=''";

		if($str=='Personal')
		{
			$whereRaw .= " AND (product LIKE '%Personal%' OR product LIKE '%PL%')";
		}
		if($str=='Auto')
		{
			$whereRaw .= " AND product LIKE '%Auto%'";
		}
		if($str=='Merchant')
		{
			$whereRaw .= " AND (product NOT LIKE '%Auto%' AND product NOT LIKE '%Personal%' AND product NOT LIKE '%PL%')";
		}
		
		if(@$request->session()->get('application_id_jonus') != '')
		{
			$application_id_jonus = $request->session()->get('application_id_jonus');						
			$whereRaw .= " AND applicationsid = '".$application_id_jonus."'";			
		}

		if(@$request->session()->get('emp_id_jonus') != '')
		{
			$emp_id = $request->session()->get('emp_id_jonus');
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
			$searchValues['emp_id_jonus'] = $emp_id;
			
		}

		if($request->session()->get('start_signeddatetime') != '')
		{
			$start_signeddatetime = $request->session()->get('start_signeddatetime');			
			$whereRaw .= " AND signeddatetime >='".date('Y-m-d',strtotime($start_signeddatetime))."'";
			$searchValues['start_signeddatetime'] = $start_signeddatetime;			
		}

		if($request->session()->get('end_signeddatetime') != '')
		{
			$end_signeddatetime = $request->session()->get('end_signeddatetime');			
			$whereRaw .= " AND signeddatetime <='".date('Y-m-d',strtotime($end_signeddatetime))."'";
			$searchValues['end_signeddatetime'] = $end_signeddatetime;
			
		}

		if(@$request->session()->get('constitution_jonus') != '')
		{
			$constitution = $request->session()->get('constitution_jonus');
			$constitution_str = '';
			foreach($constitution as $constitution_value)
			{
				if($constitution_str == '')
				{
					$constitution_str = "'".$constitution_value."'";
				}
				else
				{
					$constitution_str = $constitution_str.","."'".$constitution_value."'";
				}
			}
			$whereRaw .= " AND constitution IN (".$constitution_str.")";	
			$searchValues['constitution_jonus'] = $constitution;
			
		}

		if(@$request->session()->get('loan_type_jonus') != '')
		{
			$loan_type = $request->session()->get('loan_type_jonus');
			$loan_type_str = '';
			foreach($loan_type as $loan_type_value)
			{
				if($loan_type_str == '')
				{
					$loan_type_str = "'".$loan_type_value."'";
				}
				else
				{
					$loan_type_str = $loan_type_str.","."'".$loan_type_value."'";
				}
			}
			$whereRaw .= " AND loan_type IN (".$loan_type_str.")";	
			$searchValues['loan_type_jonus'] = $loan_type;
			
		}

		if(@$request->session()->get('team_jonus') != '')
		{
			$team = $request->session()->get('team_jonus');
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
			$searchValues['team_jonus'] = $team;
			
		}



		$datasMashreqMTD = DB::table('enbd_jonus_import_file_data')->whereRaw($whereRaw)->orderby('updated_at','DESC')->paginate($paginationValue);
		$datasMashreqMTDCount = DB::table('enbd_jonus_import_file_data')->whereRaw($whereRaw)->orderby('updated_at','DESC')->count();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		
		 return view("ENBDLoanMIS/loadJonusMIS",compact('datasMashreqMTD','searchValues','datasMashreqMTDCount','Employee_details','str'));
	}
	
	
	public function loadENBDLoanMIS(Request $request)
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
		
		$whereRaw = " id!=''";	

		

		if(@$request->session()->get('team_enbd_loan_internal') != '')
		{
			$team = $request->session()->get('team_enbd_loan_internal');
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
			$searchValues['team_enbd_loan_internal'] = $team;
			
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

		if(@$request->session()->get('aecb_id_internal') != '')
		{
			$aecb_id_internal = $request->session()->get('aecb_id_internal');						
			$whereRaw .= " AND aecb_id = '".$aecb_id_internal."'";	
			$searchValues['aecb_id_internal'] = $aecb_id_internal;
		}

		if(@$request->session()->get('app_id_internal') != '')
		{
			$app_id_internal = $request->session()->get('app_id_internal');						
			$whereRaw .= " AND app_id = '".$app_id_internal."'";	
			$searchValues['app_id_internal'] = $app_id_internal;
		}

		if(@$request->session()->get('cm_full_name_enbd_loan') != '')
		{
			$cm_full_name_enbd_loan = $request->session()->get('cm_full_name_enbd_loan');						
			$whereRaw .= " AND cm_full_name LIKE '%".$cm_full_name_enbd_loan."%'";	
			$searchValues['cm_full_name_enbd_loan'] = $cm_full_name_enbd_loan;
		}

		if(@$request->session()->get('mobile_enbd_loan') != '')
		{
			$mobile_enbd_loan = $request->session()->get('mobile_enbd_loan');						
			$whereRaw .= " AND mobile = '".$mobile_enbd_loan."'";	
			$searchValues['mobile_enbd_loan'] = $mobile_enbd_loan;
		}

		if($request->session()->get('dob_doi_enbd_loan') != '')
		{
			$dob_doi_enbd_loan = $request->session()->get('dob_doi_enbd_loan');			
			//$whereRaw .= " AND date_of_birth ='".date('Y-m-d',strtotime($dob_doi_enbd_loan))."'";
			$whereRaw .= " AND date_of_birth ='".$dob_doi_enbd_loan."'";
			$searchValues['dob_doi_enbd_loan'] = $dob_doi_enbd_loan;
			
		}

		

		if($request->session()->get('start_date_fpd_internal') != '')
		{
			$start_date_fpd_internal = $request->session()->get('start_date_fpd_internal');			
			$whereRaw .= " AND fpd >='".date('Y-m-d',strtotime($start_date_fpd_internal))."'";
			$searchValues['start_date_fpd_internal'] = $start_date_fpd_internal;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_fpd_internal') != '')
		{
			$end_date_fpd_internal = $request->session()->get('end_date_fpd_internal');			
			$whereRaw .= " AND fpd <='".date('Y-m-d',strtotime($end_date_fpd_internal))."'";
			$searchValues['end_date_fpd_internal'] = $end_date_fpd_internal;
			$login_flag = 1;
		}

		if($request->session()->get('start_date_of_submission') != '')
		{
			$start_date_of_submission = $request->session()->get('start_date_of_submission');			
			$whereRaw .= " AND date_of_submission >='".date('Y-m-d',strtotime($start_date_of_submission))."'";
			$searchValues['start_date_of_submission'] = $start_date_of_submission;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_of_submission') != '')
		{
			$end_date_of_submission = $request->session()->get('end_date_of_submission');			
			$whereRaw .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_of_submission))."'";
			$searchValues['end_date_of_submission'] = $end_date_of_submission;
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

		
		
		$datasENBDLoanMISCount = DB::table('enbd_loan_mis')->whereRaw($whereRaw)->whereBetween('date_of_submission', [$startDate, $endDate])->orderby('date_of_submission','DESC')->get()->count();
		
		$datasENBDLoanMIS = DB::table('enbd_loan_mis')->whereRaw($whereRaw)->whereBetween('date_of_submission', [$startDate, $endDate])->orderby('date_of_submission','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("ENBDLoanMIS/loadENBDLoanMIS",compact('datasENBDLoanMIS','searchValues','seller_id','datasENBDLoanMISCount','Employee_details','user_id','username'));
	}

	

	public function searchENBDLoanMISInner(Request $request)
	{
		$requestParameters = $request->input();

		$team_enbd_loan_internal = '';
		$application_status_enbd_loan_internal = '';
		$app_id_internal = '';		
		$cm_full_name_enbd_loan = '';	
		$mobile_enbd_loan = '';	
		$start_date_fpd_internal = '';
		$end_date_fpd_internal = '';
		$aecb_id_internal = '';		
		$start_date_of_submission = '';
		$end_date_of_submission = '';
		$dob_doi_enbd_loan = '';	
		


		if(@isset($requestParameters['app_id_internal']))
		{
			$app_id_internal = @$requestParameters['app_id_internal'];
		}
		if(@isset($requestParameters['dob_doi_enbd_loan']))
		{
			$dob_doi_enbd_loan = @$requestParameters['dob_doi_enbd_loan'];
		}

		if(@isset($requestParameters['cm_full_name_enbd_loan']))
		{
			$cm_full_name_enbd_loan = @$requestParameters['cm_full_name_enbd_loan'];
		}
		if(@isset($requestParameters['mobile_enbd_loan']))
		{
			$mobile_enbd_loan = @$requestParameters['mobile_enbd_loan'];
		}

		if(@isset($requestParameters['aecb_id_internal']))
		{
			$aecb_id_internal = @$requestParameters['aecb_id_internal'];
		}
		if(@isset($requestParameters['team_enbd_loan_internal']))
		{
			$team_enbd_loan_internal = @$requestParameters['team_enbd_loan_internal'];
		}

		if(@isset($requestParameters['application_status_enbd_loan_internal']))
		{
			$application_status_enbd_loan_internal = @$requestParameters['application_status_enbd_loan_internal'];
		}

		if(@isset($requestParameters['start_date_fpd_internal']))
		{
			$start_date_fpd_internal = @$requestParameters['start_date_fpd_internal'];
		}
		
		if(@isset($requestParameters['end_date_fpd_internal']))
		{
			$end_date_fpd_internal = @$requestParameters['end_date_fpd_internal'];
		}	
		
		if(@isset($requestParameters['start_date_of_submission']))
		{
			$start_date_of_submission = @$requestParameters['start_date_of_submission'];
		}
		
		if(@isset($requestParameters['end_date_of_submission']))
		{
			$end_date_of_submission = @$requestParameters['end_date_of_submission'];
		}
		
		
		
		$request->session()->put('dob_doi_enbd_loan',$dob_doi_enbd_loan);
		$request->session()->put('mobile_enbd_loan',$mobile_enbd_loan);
		$request->session()->put('cm_full_name_enbd_loan',$cm_full_name_enbd_loan);
		$request->session()->put('aecb_id_internal',$aecb_id_internal);
		$request->session()->put('app_id_internal',$app_id_internal);
		$request->session()->put('application_status_enbd_loan_internal',$application_status_enbd_loan_internal);
		$request->session()->put('team_enbd_loan_internal',$team_enbd_loan_internal);
		$request->session()->put('start_date_fpd_internal',$start_date_fpd_internal);
		$request->session()->put('end_date_fpd_internal',$end_date_fpd_internal);
		$request->session()->put('start_date_of_submission',$start_date_of_submission);
		$request->session()->put('end_date_of_submission',$end_date_of_submission);	
		return redirect("loadENBDLoanMIS");
	}

	public function loadENBDLoanMIS_AECB(Request $request)
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
		
		$whereRaw = " id!=''";	

		

		if(@$request->session()->get('team_enbd_loan_aecb') != '')
		{
			$team = $request->session()->get('team_enbd_loan_aecb');
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
			$searchValues['team_enbd_loan_aecb'] = $team;
			
		}

		if(@$request->session()->get('aecb_id_aecb') != '')
		{
			$aecb_id_aecb = $request->session()->get('aecb_id_aecb');						
			$whereRaw .= " AND aecb_id = '".$aecb_id_aecb."'";	
			$searchValues['aecb_id_aecb'] = $aecb_id_aecb;
		}

		

		

		if($request->session()->get('start_date_of_request_aecb') != '')
		{
			$start_date_of_request_aecb = $request->session()->get('start_date_of_request_aecb');			
			$whereRaw .= " AND date_of_request >='".date('Y-m-d',strtotime($start_date_of_request_aecb))."'";
			$searchValues['start_date_of_request_aecb'] = $start_date_of_request_aecb;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_of_request_aecb') != '')
		{
			$end_date_of_request_aecb = $request->session()->get('end_date_of_request_aecb');			
			$whereRaw .= " AND date_of_request <='".date('Y-m-d',strtotime($end_date_of_request_aecb))."'";
			$searchValues['end_date_of_request_aecb'] = $end_date_of_request_aecb;
			$login_flag = 1;
		}

		
		
		$datasENBDLoanMISCount = DB::table('enbd_loan_mis_aecb')->whereRaw($whereRaw)->orderby('aecb_id','DESC')->get()->count();
		
		$datasENBDLoanMIS = DB::table('enbd_loan_mis_aecb')->whereRaw($whereRaw)->orderby('aecb_id','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("ENBDLoanMIS/loadENBDLoanMISAECB",compact('datasENBDLoanMIS','searchValues','seller_id','datasENBDLoanMISCount','Employee_details','user_id','username'));
	}

	

	public function searchENBDLoanMISAECBInner(Request $request)
	{
		$requestParameters = $request->input();

		$team_enbd_loan_aecb = '';
		$aecb_id_aecb = '';		
		$start_date_of_request_aecb = '';
		$end_date_of_request_aecb = '';
		


		if(@isset($requestParameters['aecb_id_aecb']))
		{
			$aecb_id_aecb = @$requestParameters['aecb_id_aecb'];
		}
		if(@isset($requestParameters['team_enbd_loan_aecb']))
		{
			$team_enbd_loan_aecb = @$requestParameters['team_enbd_loan_aecb'];
		}

		if(@isset($requestParameters['start_date_of_request_aecb']))
		{
			$start_date_of_request_aecb = @$requestParameters['start_date_of_request_aecb'];
		}
		
		if(@isset($requestParameters['end_date_of_request_aecb']))
		{
			$end_date_of_request_aecb = @$requestParameters['end_date_of_request_aecb'];
		}		
		
		
		$request->session()->put('team_enbd_loan_aecb',$team_enbd_loan_aecb);
		$request->session()->put('aecb_id_aecb',$aecb_id_aecb);
		$request->session()->put('start_date_of_request_aecb',$start_date_of_request_aecb);
		$request->session()->put('end_date_of_request_aecb',$end_date_of_request_aecb);	
		return redirect("loadENBDLoanMIS");
	}

	public function searchENBDJonusInner(Request $request)
	{
		$requestParameters = $request->input();		
		$application_id_jonus = '';	
		$emp_id_jonus = '';	
		$team_jonus = '';	
		$start_signeddatetime = '';
		$end_signeddatetime = '';
		$constitution_jonus = '';	
		$loan_type_jonus = '';	
		
		$str = 'Master';

		if(@isset($requestParameters['str']))
		{
			$str = @$requestParameters['str'];
		}

		if(@isset($requestParameters['application_id_jonus']))
		{
			$application_id_jonus = @$requestParameters['application_id_jonus'];
		}
		if(@isset($requestParameters['emp_id_jonus']))
		{
			$emp_id_jonus = @$requestParameters['emp_id_jonus'];
		}
		if(@isset($requestParameters['team_jonus']))
		{
			$team_jonus = @$requestParameters['team_jonus'];
		}
		if(@isset($requestParameters['start_signeddatetime']))
		{
			$start_signeddatetime = @$requestParameters['start_signeddatetime'];
		}
		if(@isset($requestParameters['end_signeddatetime']))
		{
			$end_signeddatetime = @$requestParameters['end_signeddatetime'];
		}
		if(@isset($requestParameters['constitution_jonus']))
		{
			$constitution_jonus = @$requestParameters['constitution_jonus'];
		}
		if(@isset($requestParameters['loan_type_jonus']))
		{
			$loan_type_jonus = @$requestParameters['loan_type_jonus'];
		}
		
		$request->session()->put('str',$str);
		$request->session()->put('application_id_jonus',$application_id_jonus);
		$request->session()->put('emp_id_jonus',$emp_id_jonus);	
		$request->session()->put('team_jonus',$team_jonus);	
		$request->session()->put('start_signeddatetime',$start_signeddatetime);
		$request->session()->put('end_signeddatetime',$end_signeddatetime);
		$request->session()->put('constitution_jonus',$constitution_jonus);
		$request->session()->put('loan_type_jonus',$loan_type_jonus);
		return redirect("loadJonusMIS/?str=".$str);
	}

	
	public function resetENBDLoanMISInner(Request $request)
	{		

		$request->session()->put('app_id_internal','');
		$request->session()->put('dob_doi_enbd_loan','');
		$request->session()->put('mobile_enbd_loan','');
		$request->session()->put('cm_full_name_enbd_loan','');
		$request->session()->put('aecb_id_internal','');
		$request->session()->put('team_enbd_loan_internal','');
		$request->session()->put('application_status_enbd_loan_internal','');
		$request->session()->put('start_date_fpd_internal','');
		$request->session()->put('end_date_fpd_internal','');
		$request->session()->put('start_date_of_submission','');
		$request->session()->put('end_date_of_submission','');
		return redirect("loadENBDLoanMIS");
	}

	public function resetENBDLoanMISAECBInner(Request $request)
	{
		$request->session()->put('team_enbd_loan_aecb','');
		$request->session()->put('aecb_id_aecb','');
		$request->session()->put('start_date_of_request_aecb','');
		$request->session()->put('end_date_of_request_aecb','');
		return redirect("loadENBDLoanMIS");
	}

	public function resetENBDJonusInner(Request $request)
	{
		$request->session()->put('str','Master');
		$request->session()->put('application_id_jonus','');
		$request->session()->put('emp_id_jonus','');	
		$request->session()->put('team_jonus','');	
		$request->session()->put('start_signeddatetime','');
		$request->session()->put('end_signeddatetime','');
		$request->session()->put('constitution_jonus','');
		$request->session()->put('loan_type_jonus','');
		return redirect("loadJonusMIS/?str=Master");
	}

	
	public function addENBDLoanMISData($id=NULL)
    {	  
		  $AECBData = DB::table('enbd_loan_mis_aecb')->where('id', $id)->first();		  
		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();        
		  return view("ENBDLoanMIS/addENBDLoanMISData",compact('Employee_details','AECBData'));
    }

	public function addENBDLoanMISAECBData($form_id=NULL)
    {	  

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();        
		  return view("ENBDLoanMIS/addENBDLoanMISAECBData",compact('Employee_details'));
    }

	public function editENBDLoanMISData($id=NULL)
    {
		  $ENBDLoanMISData = DB::table('enbd_loan_mis')->where('id', $id)->first();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("ENBDLoanMIS/editENBDLoanMISData",compact('ENBDLoanMISData','Employee_details'));
    }

	public function editENBDLoanMISAECBData($id=NULL)
    {
		  $ENBDLoanMISData = DB::table('enbd_loan_mis_aecb')->where('id', $id)->first();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("ENBDLoanMIS/editENBDLoanMISAECBData",compact('ENBDLoanMISData','Employee_details'));
    }

	public function viewDepartmentFormData($parent_id=NULL,$form_id=NULL)
    {
		  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		  $departmentFormParentDetails = DB::table('department_form_parent_entry')->where('id', $parent_id)->first();

		  $departmentFormChildDetails = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("ENBDLoanMIS/viewDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
    }

	
	public function addENBDLoanMISPost(Request $req)
    {			
			
			
			$user_id = $req->session()->get('EmployeeId');
			$aecb_id = $req->input('aecb_id');
			$date_of_submission = ($req->input('date_of_submission')?date('Y-m-d',strtotime($req->input('date_of_submission'))):date('Y-m-d'));
			$app_id_generation_date = ($req->input('app_id_generation_date')?date('Y-m-d',strtotime($req->input('app_id_generation_date'))):'0000-00-00');
			$approval_date = ($req->input('approval_date')?date('Y-m-d',strtotime($req->input('approval_date'))):'0000-00-00');
			$disbursal_date = ($req->input('disbursal_date')?date('Y-m-d',strtotime($req->input('disbursal_date'))):'0000-00-00');

			$approval_status = $req->input('approval_status');

			$emp_id = $req->input('emp_id');
			$team = $req->input('team');
			$cm_full_name = $req->input('cm_full_name');
			$gender = $req->input('gender');
			$date_of_birth = $req->input('date_of_birth');
			$nationality = $req->input('nationality');
			$marital_status = $req->input('marital_status');
			$salary = $req->input('salary');
			$company_name = $req->input('company_name');
			//$sourcing = $req->input('sourcing');
			$first_payment_date = ($req->input('first_payment_date')?date('Y-m-d',strtotime($req->input('first_payment_date'))):'0000-00-00');
			//$pre_calling = ($req->input('pre_calling')?date('Y-m-d',strtotime($req->input('pre_calling'))):'0000-00-00');
			$application_status = $req->input('application_status');
			$app_id = $req->input('app_id');
			//$fpd = ($req->input('fpd')?date('Y-m-d',strtotime($req->input('fpd'))):'0000-00-00');
			$roi = $req->input('roi');
			$loan_amount = $req->input('loan_amount');
			$tenure = $req->input('tenure');
			$mobile = $req->input('mobile');
			//$ale = $req->input('ale');
			$aecb_score = $req->input('aecb_score');
			$scheme_name = $req->input('scheme_name');
			$bank = $req->input('bank');
			$account_no = $req->input('account_no');
			$chq = $req->input('chq');
			$comment = addslashes(str_replace("'","`",$req->input('comment')));

			$values = array('user_id' => $user_id,'aecb_id' => $aecb_id,'date_of_submission' => $date_of_submission,'app_id_generation_date' => $app_id_generation_date,'approval_date' => $approval_date, 'approval_status'=>$approval_status, 'disbursal_date' => $disbursal_date,'emp_id' => $emp_id,'team' => $team,'cm_full_name' => $cm_full_name,'gender' => $gender,'date_of_birth' => $date_of_birth,'nationality' => $nationality,'marital_status' => $marital_status,'salary' => $salary,'company_name' => $company_name, 'first_payment_date' => $first_payment_date,'application_status' => $application_status,'app_id' => $app_id,'roi' => $roi,'loan_amount' => $loan_amount,'tenure' => $tenure,'mobile' => $mobile,'aecb_score' => $aecb_score,'scheme_name' => $scheme_name,'bank' => $bank,'account_no' => $account_no,'chq' => $chq,'comment' => $comment);
			DB::table('enbd_loan_mis')->insert($values);
			
			//$Updates = array('added_in_mis' => 1);
			//DB::table('enbd_loan_mis_aecb')->where('aecb_id', $aecb_id)->update($Updates);

			$Updates2 = array('mis_status' => 1);
			DB::table('enbd_jonus_import_file_data')->where('applicationsid', $app_id)->where('mis_status', 0)->update($Updates2);
            
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('ENBDLoanMIS');
    }

	public function addENBDLoanMISAECBPost(Request $req)
    {			
			
			
			$user_id = $req->session()->get('EmployeeId');			
			
			$date_of_request = ($req->input('date_of_request')?date('Y-m-d',strtotime($req->input('date_of_request'))):'0000-00-00');
			$emp_id = $req->input('emp_id');
			$team = $req->input('team');
			$cm_full_name = $req->input('cm_full_name');
			$gender = $req->input('gender');
			$date_of_birth = $req->input('date_of_birth');
			$nationality = $req->input('nationality');
			$marital_status = $req->input('marital_status');
			$salary = $req->input('salary');
			$company_name = $req->input('company_name');
			$company_type = $req->input('company_type');
			$length_of_service = $req->input('length_of_service');
			$salary_transfer_bank = $req->input('salary_transfer_bank');
			$no_of_dependents = $req->input('no_of_dependents');
			$education = $req->input('education');
			$emirates_nbd_account = $req->input('emirates_nbd_account');
			$mobile = $req->input('mobile');
			$aecb = $req->input('aecb');
			$emi = $req->input('emi');
			$cc_limits = $req->input('cc_limits');
			$loan_scheme = $req->input('loan_scheme');
			
			

			$values = array('user_id' => $user_id,'date_of_request' => $date_of_request,'emp_id' => $emp_id,'team' => $team,'cm_full_name' => $cm_full_name,'gender' => $gender,'date_of_birth' => $date_of_birth,'nationality' => $nationality,'marital_status' => $marital_status,'salary' => $salary,'company_name' => $company_name,'company_type' => $company_type,'length_of_service' => $length_of_service,'salary_transfer_bank' => $salary_transfer_bank,'no_of_dependents' => $no_of_dependents,'education' => $education,'emirates_nbd_account' => $emirates_nbd_account,'mobile' => $mobile,'aecb' => $aecb,'emi' => $emi,'cc_limits' => $cc_limits,'loan_scheme'=>$loan_scheme);
			$insert_id = DB::table('enbd_loan_mis_aecb')->insertGetId($values);	
			$aecb_id = $insert_id+1000;

			$Updates = array('aecb_id' => $aecb_id);
			DB::table('enbd_loan_mis_aecb')->where('id', $insert_id)->update($Updates);
            
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('ENBDLoanMIS');
    }

	public function editENBDLoanMISAECBPost(Request $req)
    {	

		    $date_of_request = ($req->input('date_of_request')?date('Y-m-d',strtotime($req->input('date_of_request'))):'0000-00-00');
			$emp_id = $req->input('emp_id');
			$team = $req->input('team');
			$cm_full_name = $req->input('cm_full_name');
			$gender = $req->input('gender');
			$date_of_birth = $req->input('date_of_birth');
			$nationality = $req->input('nationality');
			$marital_status = $req->input('marital_status');
			$salary = $req->input('salary');
			$company_name = $req->input('company_name');
			$company_type = $req->input('company_type');
			$length_of_service = $req->input('length_of_service');
			$salary_transfer_bank = $req->input('salary_transfer_bank');
			$no_of_dependents = $req->input('no_of_dependents');
			$education = $req->input('education');
			$emirates_nbd_account = $req->input('emirates_nbd_account');
			$mobile = $req->input('mobile');
			$aecb = $req->input('aecb');
			$emi = $req->input('emi');
			$cc_limits = $req->input('cc_limits');
			$loan_scheme = $req->input('loan_scheme');
			$qpl_agree_status = $req->input('qpl_agree_status');
			$comments = str_replace("'","`",$req->input('comments'));

		
			$values = array('date_of_request' => $date_of_request,'emp_id' => $emp_id,'team' => $team,'cm_full_name' => $cm_full_name,'gender' => $gender,'date_of_birth' => $date_of_birth,'nationality' => $nationality,'marital_status' => $marital_status,'salary' => $salary,'company_name' => $company_name,'company_type' => $company_type,'length_of_service' => $length_of_service,'salary_transfer_bank' => $salary_transfer_bank,'no_of_dependents' => $no_of_dependents,'education' => $education,'emirates_nbd_account' => $emirates_nbd_account,'mobile' => $mobile,'aecb' => $aecb,'emi' => $emi,'cc_limits' => $cc_limits,'loan_scheme'=>$loan_scheme,'qpl_agree_status' => $qpl_agree_status,'comments' => $comments);

			DB::table('enbd_loan_mis_aecb')->where('id', $req->input('id'))->update($values);
           
            $req->session()->flash('message','Record updated Successfully.');
            return redirect('ENBDLoanMIS');
    }

	public function editENBDLoanMISPost(Request $req)
    {	

		    $aecb_id = $req->input('aecb_id');
			$date_of_submission = ($req->input('date_of_submission')?date('Y-m-d',strtotime($req->input('date_of_submission'))):date('Y-m-d'));
			$app_id_generation_date = ($req->input('app_id_generation_date')?date('Y-m-d',strtotime($req->input('app_id_generation_date'))):'0000-00-00');
			$approval_date = ($req->input('approval_date')?date('Y-m-d',strtotime($req->input('approval_date'))):'0000-00-00');
			$disbursal_date = ($req->input('disbursal_date')?date('Y-m-d',strtotime($req->input('disbursal_date'))):'0000-00-00');

			$approval_status = $req->input('approval_status');

			$emp_id = $req->input('emp_id');
			$team = $req->input('team');
			$cm_full_name = $req->input('cm_full_name');
			$gender = $req->input('gender');
			$date_of_birth = $req->input('date_of_birth');
			$nationality = $req->input('nationality');
			$marital_status = $req->input('marital_status');
			$salary = $req->input('salary');
			$company_name = $req->input('company_name');			
			$first_payment_date = ($req->input('first_payment_date')?date('Y-m-d',strtotime($req->input('first_payment_date'))):'0000-00-00');			
			$application_status = $req->input('application_status');
			$app_id = $req->input('app_id');			
			$roi = $req->input('roi');
			$loan_amount = $req->input('loan_amount');
			$tenure = $req->input('tenure');
			$mobile = $req->input('mobile');			
			$aecb_score = $req->input('aecb_score');
			$scheme_name = $req->input('scheme_name');
			$bank = $req->input('bank');
			$account_no = $req->input('account_no');			
			$comment = addslashes(str_replace("'","`",$req->input('comment')));

			$values = array('aecb_id' => $aecb_id,'date_of_submission' => $date_of_submission,'app_id_generation_date' => $app_id_generation_date,'approval_date' => $approval_date, 'approval_status'=>$approval_status, 'disbursal_date' => $disbursal_date,'emp_id' => $emp_id,'team' => $team,'cm_full_name' => $cm_full_name,'gender' => $gender,'date_of_birth' => $date_of_birth,'nationality' => $nationality,'marital_status' => $marital_status,'salary' => $salary,'company_name' => $company_name, 'first_payment_date' => $first_payment_date,'application_status' => $application_status,'app_id' => $app_id,'roi' => $roi,'loan_amount' => $loan_amount,'tenure' => $tenure,'mobile' => $mobile,'aecb_score' => $aecb_score,'scheme_name' => $scheme_name,'bank' => $bank,'account_no' => $account_no,'comment' => $comment);

			DB::table('enbd_loan_mis')->where('id', $req->input('id'))->update($values);
           
            $req->session()->flash('message','Record updated Successfully.');
            return redirect('ENBDLoanMIS');
    }

	

	
	public static function getEmployeeDetails($id=NULL)
    {
		return $Employee_details =  Employee_details::where("emp_id",$id)->first();
    }

















	///new start



	public function loadENBDLoanMISHistoric(Request $request)
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
		
		$whereRaw = " id!=''";	

		

		if(@$request->session()->get('team_enbd_loan_internal_Historical') != '')
		{
			$team = $request->session()->get('team_enbd_loan_internal_Historical');
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
			$searchValues['team_enbd_loan_internal_Historical'] = $team;
			
		}

		if(@$request->session()->get('application_status_enbd_loan_internal_Historical') != '')
		{
			$application_status = $request->session()->get('application_status_enbd_loan_internal_Historical');
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
			$searchValues['application_status_enbd_loan_internal_Historical'] = $application_status;
			
		}

		if(@$request->session()->get('aecb_id_internal') != '')
		{
			$aecb_id_internal = $request->session()->get('aecb_id_internal');						
			$whereRaw .= " AND aecb_id = '".$aecb_id_internal."'";	
			$searchValues['aecb_id_internal'] = $aecb_id_internal;
		}

		if(@$request->session()->get('app_id_internal_Historical') != '')
		{
			$app_id_internal = $request->session()->get('app_id_internal_Historical');						
			$whereRaw .= " AND app_id = '".$app_id_internal."'";	
			$searchValues['app_id_internal_Historical'] = $app_id_internal;
		}

		if(@$request->session()->get('cm_full_name_enbd_loan_Historical') != '')
		{
			$cm_full_name_enbd_loan = $request->session()->get('cm_full_name_enbd_loan_Historical');						
			$whereRaw .= " AND cm_full_name LIKE '%".$cm_full_name_enbd_loan."%'";	
			$searchValues['cm_full_name_enbd_loan_Historical'] = $cm_full_name_enbd_loan;
		}

		if(@$request->session()->get('mobile_enbd_loan_Historical') != '')
		{
			$mobile_enbd_loan = $request->session()->get('mobile_enbd_loan_Historical');						
			$whereRaw .= " AND mobile = '".$mobile_enbd_loan."'";	
			$searchValues['mobile_enbd_loan_Historical'] = $mobile_enbd_loan;
		}

		if($request->session()->get('dob_doi_enbd_loan_Historical') != '')
		{
			$dob_doi_enbd_loan = $request->session()->get('dob_doi_enbd_loan_Historical');			
			//$whereRaw .= " AND date_of_birth ='".date('Y-m-d',strtotime($dob_doi_enbd_loan))."'";
			$whereRaw .= " AND date_of_birth ='".$dob_doi_enbd_loan."'";
			$searchValues['dob_doi_enbd_loan_Historical'] = $dob_doi_enbd_loan;
			
		}

		

		if($request->session()->get('start_date_fpd_internal') != '')
		{
			$start_date_fpd_internal = $request->session()->get('start_date_fpd_internal');			
			$whereRaw .= " AND fpd >='".date('Y-m-d',strtotime($start_date_fpd_internal))."'";
			$searchValues['start_date_fpd_internal'] = $start_date_fpd_internal;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_fpd_internal') != '')
		{
			$end_date_fpd_internal = $request->session()->get('end_date_fpd_internal');			
			$whereRaw .= " AND fpd <='".date('Y-m-d',strtotime($end_date_fpd_internal))."'";
			$searchValues['end_date_fpd_internal'] = $end_date_fpd_internal;
			$login_flag = 1;
		}

		if($request->session()->get('start_date_of_submission_Historical') != '')
		{
			$start_date_of_submission = $request->session()->get('start_date_of_submission_Historical');			
			$whereRaw .= " AND date_of_submission >='".date('Y-m-d',strtotime($start_date_of_submission))."'";
			$searchValues['start_date_of_submission_Historical'] = $start_date_of_submission;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_of_submission_Historical') != '')
		{
			$end_date_of_submission = $request->session()->get('end_date_of_submission_Historical');			
			$whereRaw .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_of_submission))."'";
			$searchValues['end_date_of_submission_Historical'] = $end_date_of_submission;
			$login_flag = 1;
		}

		
		
		$datasENBDLoanMISCount = DB::table('enbd_loan_mis')->whereRaw($whereRaw)->orderby('date_of_submission','DESC')->get()->count();
		
		$datasENBDLoanMIS = DB::table('enbd_loan_mis')->whereRaw($whereRaw)->orderby('date_of_submission','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("ENBDLoanMIS/loadENBDLoanMISHistoric",compact('datasENBDLoanMIS','searchValues','seller_id','datasENBDLoanMISCount','Employee_details','user_id','username'));
	}


	public function searchENBDLoanMISInnerHistoric(Request $request)
	{
		$requestParameters = $request->input();

		$team_enbd_loan_internal = '';
		$application_status_enbd_loan_internal = '';
		$app_id_internal = '';		
		$cm_full_name_enbd_loan = '';	
		$mobile_enbd_loan = '';	
		$start_date_fpd_internal = '';
		$end_date_fpd_internal = '';
		$aecb_id_internal = '';		
		$start_date_of_submission = '';
		$end_date_of_submission = '';
		$dob_doi_enbd_loan = '';	
		


		if(@isset($requestParameters['app_id_internal_Historical']))
		{
			$app_id_internal = @$requestParameters['app_id_internal_Historical'];
		}
		if(@isset($requestParameters['dob_doi_enbd_loan_Historical']))
		{
			$dob_doi_enbd_loan = @$requestParameters['dob_doi_enbd_loan_Historical'];
		}

		if(@isset($requestParameters['cm_full_name_enbd_loan_Historical']))
		{
			$cm_full_name_enbd_loan = @$requestParameters['cm_full_name_enbd_loan_Historical'];
		}
		if(@isset($requestParameters['mobile_enbd_loan_Historical']))
		{
			$mobile_enbd_loan = @$requestParameters['mobile_enbd_loan_Historical'];
		}

		if(@isset($requestParameters['aecb_id_internal']))
		{
			$aecb_id_internal = @$requestParameters['aecb_id_internal'];
		}
		if(@isset($requestParameters['team_enbd_loan_internal_Historical']))
		{
			$team_enbd_loan_internal = @$requestParameters['team_enbd_loan_internal_Historical'];
		}

		if(@isset($requestParameters['application_status_enbd_loan_internal_Historical']))
		{
			$application_status_enbd_loan_internal = @$requestParameters['application_status_enbd_loan_internal_Historical'];
		}

		if(@isset($requestParameters['start_date_fpd_internal']))
		{
			$start_date_fpd_internal = @$requestParameters['start_date_fpd_internal'];
		}
		
		if(@isset($requestParameters['end_date_fpd_internal']))
		{
			$end_date_fpd_internal = @$requestParameters['end_date_fpd_internal'];
		}	
		
		if(@isset($requestParameters['start_date_of_submission_Historical']))
		{
			$start_date_of_submission = @$requestParameters['start_date_of_submission_Historical'];
		}
		
		if(@isset($requestParameters['end_date_of_submission_Historical']))
		{
			$end_date_of_submission = @$requestParameters['end_date_of_submission_Historical'];
		}
		
		
		
		$request->session()->put('dob_doi_enbd_loan_Historical',$dob_doi_enbd_loan);
		$request->session()->put('mobile_enbd_loan_Historical',$mobile_enbd_loan);
		$request->session()->put('cm_full_name_enbd_loan_Historical',$cm_full_name_enbd_loan);
		$request->session()->put('aecb_id_internal',$aecb_id_internal);
		$request->session()->put('app_id_internal_Historical',$app_id_internal);
		$request->session()->put('application_status_enbd_loan_internal_Historical',$application_status_enbd_loan_internal);
		$request->session()->put('team_enbd_loan_internal_Historical',$team_enbd_loan_internal);
		$request->session()->put('start_date_fpd_internal',$start_date_fpd_internal);
		$request->session()->put('end_date_fpd_internal',$end_date_fpd_internal);
		$request->session()->put('start_date_of_submission_Historical',$start_date_of_submission);
		$request->session()->put('end_date_of_submission_Historical',$end_date_of_submission);	
		return redirect("loadENBDLoanContentHistoric");
	}


	public function resetENBDLoanMISInnerHistoric(Request $request)
	{		

		$request->session()->put('app_id_internal_Historical','');
		$request->session()->put('dob_doi_enbd_loan_Historical','');
		$request->session()->put('mobile_enbd_loan_Historical','');
		$request->session()->put('cm_full_name_enbd_loan_Historical','');
		$request->session()->put('aecb_id_internal','');
		$request->session()->put('team_enbd_loan_internal_Historical','');
		$request->session()->put('application_status_enbd_loan_internal_Historical','');
		$request->session()->put('start_date_fpd_internal','');
		$request->session()->put('end_date_fpd_internal','');
		$request->session()->put('start_date_of_submission_Historical','');
		$request->session()->put('end_date_of_submission_Historical','');
		return redirect("loadENBDLoanContentHistoric");
	}












	


// Export Agent Performance Module Start

public function exportAgentPerformanceDataENBDLoanMIS(Request $request)
{
		
	
	
		$start_date_application_SCB_internal = '';
		$end_date_application_SCB_internal = '';
		$whereRaw = '';
		$whereRawBank = "app_id != ''";
	
		if($request->session()->get('start_date_of_submission') != '')
		{
			$start_date_application_SCB_internal = $request->session()->get('start_date_of_submission');			
			$whereRaw .= "date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
		elseif($request->session()->get('start_date_of_submission_Historical') != '')
		{
			$start_date_application_SCB_internal = $request->session()->get('start_date_of_submission_Historical');			
			$whereRaw .= "date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
	    else
		{
			$start_date_application_SCB_internal = date("Y")."-".date("m")."-01";			
			$whereRaw .= "date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
		if($request->session()->get('end_date_of_submission') != '')
		{
			$end_date_application_SCB_internal = $request->session()->get('end_date_of_submission');			
			$whereRaw .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}	
		elseif($request->session()->get('end_date_of_submission_Historical') != '')
		{
			$end_date_application_SCB_internal = $request->session()->get('end_date_of_submission_Historical');			
			$whereRaw .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}	
		else
		{
			$end_date_application_SCB_internal = date("Y-m-d");	
			$whereRaw .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND date_of_submission <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
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
			
			// $whereRawBank = "app_id != '' AND date_of_submission >='$startDate' AND date_of_submission <='$endDate'";


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
			// $collectionModel = ENBDLoanMIS::selectRaw('count(*) as total, emp_id,team,vintage,range_id,doj,agent_code')
			// 									  ->groupBy('emp_id')
			// 									  ->whereRaw($whereRaw)
			// 									  ->get();



			$collectionModel = ENBDLoanMIS::selectRaw('count(*) as total, emp_id,team')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRaw)
												  ->get();


			// print_r($collectionModel);
			// die;
		
		    $filename = 'Agent_performance_ENBD_Loans_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R2');
			$sheet->setCellValue('Q1', 'Agents Performance ENBD Loans - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).'to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$indexCounter = 5;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent Emp Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('SM Manager'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Total Submissions'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('F'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Vintage'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Range Id'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Designation'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Agent Salary'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('P'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('DOJ'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
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
			foreach ($collectionModel as $model) 
			{
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
					$totalBankBooking = ENBDLoanMIS::select("id")->where("emp_id",$model->emp_id)->whereIn("application_status",array("APPROVE"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $model->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeName($model->emp_id))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $model->team)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $model->total)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('F'.$indexCounter, $totalBankBooking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->getrecruiterNameSCB($model->emp_id))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterCatSCB($model->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getDesignation($model->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->t1Submissions($model->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t2Submissions($model->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getAgentSalary($model->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($model->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($model->emp_id);
					
						$totalBooking = $totalBooking+$totalBankBooking;
					
					
					$sheet->setCellValue('P'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('Q'.$indexCounter,$model->doj)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
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
					$totalBankBooking = ENBDLoanMIS::select("id")->where("emp_id",$zeroSubmission->emp_id)->whereIn("status",array("APPROVE"))->whereBetween("date_of_submission",[$startDate,$endDate])->get()->count();
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
					// $sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $vintageDays)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('P'.$indexCounter,"0")->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				
					$sheet->setCellValue('Q'.$indexCounter,$zeroSubmission->doj)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
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
					// $sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $vintageDays)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('P'.$indexCounter,"0")->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('Q'.$indexCounter,$zeroSubmission->doj)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalBooking = $totalBooking+0;
						}
						
						
						
					
						
					}
					
			
				}
				
				
				$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				
				 $collectionModelMissing = ENBDLoanMIS::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereDate('date_of_submission', '>=', $startDateMissing)
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
					// $sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingP($missing->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->getrecruiterNameSCB($missing->emp_id))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterCatSCB($missing->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $vintageDays)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getDesignation($missing->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->t1Submissions($missing->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t2Submissions($missing->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getAgentSalary($missing->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($missing->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($missing->emp_id);	
					$sheet->setCellValue('P'.$indexCounter,"0")->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('Q'.$indexCounter,$missing->doj)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
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
			// $sheet->setCellValue('F'.$indexCounter, $totalBookingBank)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalLastBooking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $totalLastBookingP)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, $t1Total)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, $t2Total)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			 
			$approvalRateALL =  @round(($totalBooking/$totalSubmission),2);
		
			$sheet->setCellValue('P'.$indexCounter,$approvalRateALL)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
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
		$totalBankBooking = ENBDLoanMIS::select("id")->where("emp_id",$empId)->whereIn("application_status",array("APPROVE"))->whereBetween("date_of_submission",[$startDate,$endDate])->get()->count();
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
		$totalBankBooking = ENBDLoanMIS::select("id")->where("emp_id",$empId)->whereIn("application_status",array("APPROVE"))->whereBetween("date_of_submission",[$startDate,$endDate])->get()->count();
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
		return ENBDLoanMIS::select("id")->whereDate("date_of_submission","=",$previousDate)->where("emp_id",$empId)->get()->count();
		
	}
	protected function t2Submissions($empId)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return ENBDLoanMIS::select("id")->whereBetween("date_of_submission",[$StartDate,$endDate])->where("emp_id",$empId)->get()->count();
		
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
			
		$collectionModel = ENBDLoanMIS::selectRaw('count(*) as total,team')
												->groupBy('team')
												->whereRaw($whereRaw)
												->get();


		// echo "<pre>";
		// print_r($collectionModel);
		// exit;




		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A1:H2');
		$sheet->setCellValue('A1', 'TL Performance ENBD Loans - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).' to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
		$indexCounter = 5;
		$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Manager'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('C'.$indexCounter, strtoupper('Total Submissions'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('D'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('D'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('F'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('G'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
		$sheet->setCellValue('H'.$indexCounter, strtoupper('Submission to Booking'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


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
						$totalBankBooking = ENBDLoanMIS::select("id")->where("team",$model->team)->whereIn("application_status",array("APPROVE"))->whereRaw($whereRawBank)->get()->count();
						$indexCounter++;
						
						$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						// $sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('D'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('F'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('G'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$totalSubmission = $totalSubmission+$model->total;
						$totalBookingBank = $totalBookingBank+$totalBankBooking;
						
						$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal);
						$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal);
						$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
						$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
						$totalBooking = $totalBooking+$totalBankBooking;
						
						
						
						$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
						$sheet->setCellValue('H'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
						
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
				$collectionModelP = ENBDLoanMIS::selectRaw('team')
														->groupBy('team')
														->whereDate('date_of_submission','>=',$startDatePP)
														->whereNotIn("team",$teamValue)
														->get();
														
				foreach ($collectionModelP as $model) 
				{
						if($model->team != '')
						{
				
					
					
					$totalBankBooking = ENBDLoanMIS::select("id")->where("team",$model->team)->whereIn("application_status",array("APPROVE"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
					$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
					$totalBooking = $totalBooking+$totalBankBooking;
					
					
					
					$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
					$sheet->setCellValue('H'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					
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
			// $sheet->setCellValue('D'.$indexCounter, $totalBookingBank)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $totalLastBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalLastBookingP)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $t1Total)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $t2Total)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			if($totalSubmission != 0)
			{
				
				$approvalRateALL =  round(($totalBooking/$totalSubmission),2);

			}
			else
			{
				$approvalRateALL = 0;
			}
			
			$sheet->setCellValue('H'.$indexCounter,$approvalRateALL)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
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
		
		
		$totalBankBooking = ENBDLoanMIS::select("id")->where("team",$team)->whereIn("application_status",array("APPROVE"))->whereBetween("date_of_submission",[$startDate,$endDate])->get()->count();
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
		
		$totalBankBooking = ENBDLoanMIS::select("id")->where("team",$team)->whereIn("application_status",array("APPROVE"))->whereBetween("date_of_submission",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}


	protected function t1SubmissionsTeam($team)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return ENBDLoanMIS::select("id")->whereDate("date_of_submission","=",$previousDate)->where("team",$team)->get()->count();
		
	}
	protected function t2SubmissionsTeam($team)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return ENBDLoanMIS::select("id")->whereBetween("date_of_submission",[$StartDate,$endDate])->where("team",$team)->get()->count();
		
	}




	public function getTLNamefromBank($empid)
	{
		 if($empid != '' && $empid != NULL)
		 {
			$empName = ENBDLoanMIS::select("team")->where("emp_id",$empid)->first();
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
