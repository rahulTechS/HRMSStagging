<?php
namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\MashreqMissingMobileCronLog;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Attribute\CdaDeviationDetails;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Common\MashreqMasterMIS;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Employee\ExportDataLog;
use App\Models\Attribute\MashreqRemarkLogs;
use App\Models\Entry\Employee;
use Session;
ini_set("max_execution_time", 0);
class DepartmentFormController extends Controller
{
   
    public function departmentForm()
    {
		$departmentFormDetails = DepartmentForm::where("status",1)->orwhere("status",2)->orderBy("form_title","ASC")->get();        
        return view("Attribute/departmentForm",compact('departmentFormDetails'));
    }

	public function editDepartmentForm($id=NULL)
    {
	  $departmentFormDetails =   DepartmentForm::where("id",$id)->first();
      $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
	  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
	  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();
	  $FormProductDetails = FormProduct::where("status",1)->orwhere("status",2)->orderBy("product","ASC")->get();

	  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $departmentFormDetails->form_id)->get();

      return view("Attribute/editDepartmentForm",compact('departmentFormDetails','FormProductDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails'));
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
        return view('Attribute/addDepartmentForm',compact('DepartmentDetails','FormProductDetails','masterAttributeDetails','FormSectionDetails'));
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

	public function viewMissingMobileMashreq($table_id=NULL,Request $request)
    {	
			$user_id =$request->session()->get('EmployeeId');
			MashreqMissingMobileCronLog::where('id', $table_id)->update(['user_id' => $user_id,'view_date' => date('Y-m-d')]);

			$request->session()->put('ref_no_internal','');
			$request->session()->put('ref_no_internal_bulk','');
			$request->session()->put('application_id_internal','');
			$request->session()->put('remarks','');
			$request->session()->put('form_status','');
			$request->session()->put('emp_id_internal','');
			$request->session()->put('sales_processor_internal','');
			$request->session()->put('missing_login_internal','');
			$request->session()->put('team_internal','');
			$request->session()->put('start_date_internal','');
			$request->session()->put('end_date_internal','');
			$request->session()->put('search_internal_flag','');
			$request->session()->put('CurrentMonthFilter','');

			$form_id = 1;
			$request->session()->put('form_id',$form_id);		
			
			$result = MashreqMissingMobileCronLog::where("id",$table_id)->first();
			if($result != '')
			{
					$ref_no_json = json_decode($result->ref_no);					
					$ref_no_internal_bulk = array();
					foreach($ref_no_json as $ref_no_json_data)
					{
						$ref_no_internal_bulk[] = $ref_no_json_data;
					}
					$request->session()->put('ref_no_internal_bulk',$ref_no_internal_bulk);
					$request->session()->put('auto_download','');
					
			}
		
			
			return redirect("departmentFormData/".$form_id);
				
	}

	public function downloadMissingMobileMashreq($table_id=NULL,Request $request)
    {		
			$user_id =$request->session()->get('EmployeeId');
			MashreqMissingMobileCronLog::where('id', $table_id)->update(['user_id' => $user_id,'download_date' => date('Y-m-d')]);

			$request->session()->put('ref_no_internal','');
			$request->session()->put('ref_no_internal_bulk','');
			$request->session()->put('application_id_internal','');
			$request->session()->put('remarks','');
			$request->session()->put('form_status','');
			$request->session()->put('emp_id_internal','');
			$request->session()->put('sales_processor_internal','');
			$request->session()->put('missing_login_internal','');
			$request->session()->put('team_internal','');
			$request->session()->put('start_date_internal','');
			$request->session()->put('end_date_internal','');
			$request->session()->put('search_internal_flag','');
			$request->session()->put('CurrentMonthFilter','');

			$form_id = 1;
			$request->session()->put('form_id',$form_id);		
			
			$result = MashreqMissingMobileCronLog::where("id",$table_id)->first();
			if($result != '')
			{
					$ref_no_json = json_decode($result->ref_no);					
					$ref_no_internal_bulk = array();
					$table_id_array = array();
					foreach($ref_no_json as $ref_no_json_data)
					{
						$table_id = DepartmentFormEntry::where("ref_no",$ref_no_json_data)->first();
						$ref_no_internal_bulk[] = $ref_no_json_data;
						$table_id_array[] = $table_id->id;
					}
					
					$request->session()->put('ref_no_internal_bulk',$ref_no_internal_bulk);
					$request->session()->put('auto_download',$table_id_array);
					
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
	
	public function setPaginationValueMashreqCard(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("departmentFormData/1");
	}

	public function departmentFormData($form_id=NULL,Request $request)
    {
		/*$d1 = DB::table('department_form_child_entry')->where('attribute_code', 'ref_no')->get();
		foreach($d1 as $v1)
		{
			$parent_id = $v1->parent_id;
			$ref_no  = $v1->attribute_value;

			DepartmentFormEntry::where('id', $parent_id)
				->update(['ref_no' => $ref_no]);
			
		}*/

		$searchValues = array();

		$paginationValue = 20;
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";
		$whereRawLogin = " ref_no!=''";

		$internal_flag = 0;
		/*if($request->session()->get('team') != '')
		{
			$team = $request->session()->get('team');
			$whereRaw .= " AND team ='".$team."'";
			$searchValues['team'] = $team;
			$internal_flag = 1;
		}*/
		/*if($request->session()->get('emp_id') != '')
		{
			$emp_id = $request->session()->get('emp_id');
			$whereRaw .= " AND emp_id ='".$emp_id."'";
			$searchValues['emp_id'] = $emp_id;
			$internal_flag = 1;
		}*/
		if(@$request->session()->get('team') != '')
		{
			$team = $request->session()->get('team');
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
			$searchValues['team'] = $team;
			$internal_flag = 1;
		}
		
		if(@$request->session()->get('emp_id') != '')
		{
			$emp_id = $request->session()->get('emp_id');
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
			$searchValues['emp_id'] = $emp_id;
			$internal_flag = 1;
		}
		
		$login_flag = 0;

		if(@$request->session()->get('ref_no') != '')
		{
			$ref_no = $request->session()->get('ref_no');
			/*$exp_ref_no = explode(",",$ref_no);

			$whereOR_ref="(";
			foreach($exp_ref_no as $exp_ref_no_value)
			{
				$whereOR_ref .= " ref_no LIKE '%".$exp_ref_no_value."%' OR";
			}
			$whereOR_ref = substr($whereOR_ref,0,-2).")";

			$whereRawLogin .= " AND ".$whereOR_ref;*/
			$whereRawLogin .= " AND ref_no ='".$ref_no."'";
			$whereRaw .= " AND ref_no ='".$ref_no."'";
			$searchValues['ref_no'] = $ref_no;
			$login_flag = 1;
		}
		/*if(@$request->session()->get('card_type') != '')
		{
			$card_type = $request->session()->get('card_type');			
			$whereRawLogin .= " AND card_type ='".$card_type."'";
			$searchValues['card_type'] = $card_type;
			$login_flag = 1;
		}*/
		if(@$request->session()->get('card_type') != '')
		{
			$card_type = $request->session()->get('card_type');
			$card_type_str = '';
			foreach($card_type as $card_type_value)
			{
				if($card_type_str == '')
				{
					$card_type_str = "'".$card_type_value."'";
				}
				else
				{
					$card_type_str = $card_type_str.","."'".$card_type_value."'";
				}
			}
			$whereRawLogin .= " AND card_type IN (".$card_type_str.")";	
			$whereRaw .= " AND card_type IN (".$card_type_str.")";
			$searchValues['card_type'] = $card_type;
			$login_flag = 1;
		}
		
		if($request->session()->get('customer') != '')
		{
			$customer = $request->session()->get('customer');
			$exp_customer = explode(",",$customer);

			$whereOR_customer="(";
			foreach($exp_customer as $exp_customer_value)
			{
				$whereOR_customer .= " customer_name LIKE '%".$exp_customer_value."%' OR";
			}
			$whereOR_customer = substr($whereOR_customer,0,-2).")";

			$whereRawLogin .= " AND ".$whereOR_customer;
			$whereRaw .= " AND ".$whereOR_customer;
			//$whereRawLogin .= " AND customer_name LIKE '%".$customer."%'";
			$searchValues['customer'] = $customer;
			$login_flag = 1;
		}

		if($request->session()->get('start_date') != '')
		{
			$start_date = $request->session()->get('start_date');			
			$whereRawLogin .= " AND application_date >='".date('Y-m-d',strtotime($start_date))."'";
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date))."'";
			$searchValues['start_date'] = $start_date;
			$login_flag = 1;
		}

		if($request->session()->get('end_date') != '')
		{
			$end_date = $request->session()->get('end_date');			
			$whereRawLogin .= " AND application_date <='".date('Y-m-d',strtotime($end_date))."'";
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date))."'";
			$searchValues['end_date'] = $end_date;
			$login_flag = 1;
		}

		//$departmentFormParentTotal = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		$departmentFormParentTotal = count($departmentFormParentDetails);

		

		$Employee_details = Employee_details::where("offline_status",'1')->orderby('first_name','ASC')->get();
		
		$ref_no_string = "'0',";

		if($internal_flag>0)
		{
			$datasMashreqInternal = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','ASC')->get(['ref_no']);
			
			
			if(count($datasMashreqInternal)>0)
			{			
				foreach($datasMashreqInternal as $datasMashreqInternalData)
				{				
					$ref_no_string .= "'".trim($datasMashreqInternalData->ref_no)."',";			
				}				
			}
			$ref_no_string = substr($ref_no_string,0,-1);
			$whereRawLogin .= " AND ref_no IN (".$ref_no_string.")";
		}

		$datasMashreqLogin = DB::table('mashreq_login_data')->whereRaw($whereRawLogin)->orderby('application_date','DESC')->get(['ref_no','applicationid']);		
		
		$applicationid_string = "'0',";
		if(count($datasMashreqLogin)>0)
		{			
			foreach($datasMashreqLogin as $datasMashreqLoginData)
			{				
				$ref_no_string .= "'".trim($datasMashreqLoginData->ref_no)."',";
				$applicationid_string .= "'".trim($datasMashreqLoginData->applicationid)."',";
				
			}
			$ref_no_string = substr($ref_no_string,0,-1);
			$applicationid_string = substr($applicationid_string,0,-1);
			$request->session()->put('ref_no_string',$ref_no_string);	
			$request->session()->put('applicationid_string',$applicationid_string);	
		}
		else
		{
			$request->session()->put('ref_no_string',"'0'");	
			$request->session()->put('applicationid_string',"'0'");
		}
		if($ref_no_string == "'0',")
		{
			$ref_no_string == "'0'";
		}
		$request->session()->put('ref_no_string',$ref_no_string);

		$card_type_details = MashreqLoginMIS::where("card_type","!=",'')->groupBy('card_type')
		->selectRaw('count(*) as total, card_type')
		->get();

        return view("Attribute/departmentFormData",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues','form_id','card_type_details'));
    }
	
	
	public function loadBankContentsMashreqCard_OLD(Request $request)
	{
		$form_id = $request->form_id;
		$searchValues = array();

		$paginationValue = 20;
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";

		
		if(@$request->session()->get('ref_no_string') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string');
			$whereRaw .= " AND ref_no IN (".$ref_no_string.")";			
		}

		if(@$request->session()->get('form_status') != '')
		{
			$form_status = $request->session()->get('form_status');
			$form_status_str = '';
			foreach($form_status as $form_status_value)
			{
				if($form_status_str == '')
				{
					$form_status_str = "'".$form_status_value."'";
				}
				else
				{
					$form_status_str = $form_status_str.","."'".$form_status_value."'";
				}
			}
			$whereRaw .= " AND form_status IN (".$form_status_str.")";			
		}

		if(@$request->session()->get('remarks') != '')
		{
			$remarks = $request->session()->get('remarks');						
			$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
		}

		if($request->session()->get('start_date_internal') != '')
		{
			$start_date_internal = $request->session()->get('start_date_internal');			
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
			$searchValues['start_date_internal'] = $start_date_internal;			
		}

		if($request->session()->get('end_date_internal') != '')
		{
			$end_date_internal = $request->session()->get('end_date_internal');			
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
			$searchValues['end_date_internal'] = $end_date_internal;			
		}

		$departmentFormParentTotal = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$form_status = DepartmentFormEntry::where("form_status","!=",'')->groupBy('form_status')
		->selectRaw('count(*) as total, form_status')
		->get();

        return view("Attribute/Mashreq/loadBankContentsMashreqCard",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues','form_status'));
	}

	public function loadBankContentsMashreqCard(Request $request)
	{
		$form_id = $request->form_id;
		$searchValues = array();
		$search_internal_flag = '';
		$CurrentMonthFilter = '';

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');

		$paginationValue = 20;

		if(@$request->session()->get('CurrentMonthFilter') != '')
		{
			$paginationValue = 6000;
			$searchValues['paginationValue'] = $paginationValue;
		}

		if(@$request->session()->get('paginationValue') != '' && $request->session()->get('CurrentMonthFilter') == '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";

		
		if(@$request->session()->get('ref_no_string') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string');
			if(strlen($ref_no_string)>4)
			{
				$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
			}
			else
			{
				$whereRaw .= " AND ref_no IN ('0')";	
			}	
		}

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
		$auto_download_array = array();
		if(@$request->session()->get('ref_no_internal_bulk') != '')
		{
			$ref_no_internal_bulk = $request->session()->get('ref_no_internal_bulk');
			
			$paginationValue = 500;
			$searchValues['paginationValue'] = $paginationValue;
			$ref_no_internal_str = '';
			foreach($ref_no_internal_bulk as $ref_no_internal_value)
			{
				if($ref_no_internal_str == '')
				{
					$ref_no_internal_str = "'".$ref_no_internal_value."'";
				}
				else
				{
					$ref_no_internal_str = $ref_no_internal_str.","."'".$ref_no_internal_value."'";
				}
			}
			$whereRaw .= " AND ref_no IN (".$ref_no_internal_str.")";
			
			$auto_download = @$request->session()->get('auto_download');
			if($auto_download!='')
			{
				$auto_download_array = $auto_download;			
				
			}
			
		}
		$request->session()->put('auto_download','');


		if(@$request->session()->get('sales_processor_internal') != '')
		{
			$team = array();
			$team_Mahwish_130 = array('Ajay','Anas','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');

			$sales_processor_internal = $request->session()->get('sales_processor_internal');
			
			foreach($sales_processor_internal as $sales_processor_internal_value)
			{				
				if($sales_processor_internal_value=='Mahwish')
				{
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_internal_value=='Arsalan')
				{
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_internal_value=='Umer')
				{
					$team = array_merge($team,$team_Umar_168);
				}
			}
			
			
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
			$searchValues['sales_processor_internal'] = $sales_processor_internal;
			
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

		if(@$request->session()->get('form_status') != '')
		{
			$form_status = $request->session()->get('form_status');
			$form_status_str = '';
			foreach($form_status as $form_status_value)
			{
				if($form_status_str == '')
				{
					$form_status_str = "'".$form_status_value."'";
				}
				else
				{
					$form_status_str = $form_status_str.","."'".$form_status_value."'";
				}
			}
			$whereRaw .= " AND form_status IN (".$form_status_str.")";			
		}

		if(@$request->session()->get('application_id_internal') != '')
		{
			$application_id_internal = $request->session()->get('application_id_internal');						
			$whereRaw .= " AND application_id = '".$application_id_internal."'";			
		}

		if(@$request->session()->get('missing_login_internal') != '')
		{
			$missing_login_internal = $request->session()->get('missing_login_internal');
			if($missing_login_internal=='Missing in Login (Current Month)')
			{
				$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
				$whereRaw .= " AND submission_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND submission_date <='".date('Y-m-d')."'";
			}
			else if($missing_login_internal=='Linked From Booking')
			{
				$whereRaw .= " AND missing_booking_link_status = '1'";
				
			}
			else if($missing_login_internal=='Missing in Login')
			{
				$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
			}
			else if($missing_login_internal=='Missing Mobile Number')
			{
				$whereRaw .= " AND (customer_mobile = '' OR customer_mobile IS NULL)";
			}
			else
			{
				$whereRaw .= " AND (customer_mobile != '' AND customer_mobile IS NOT NULL)";
			}
		}

		if(@$request->session()->get('ref_no_internal') != '')
		{
			$ref_no_internal = $request->session()->get('ref_no_internal');						
			$whereRaw .= " AND ref_no = '".$ref_no_internal."'";			
		}

		if(@$request->session()->get('remarks') != '')
		{
			$remarks = $request->session()->get('remarks');						
			$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
		}

		if($request->session()->get('start_date_internal') != '')
		{
			$start_date_internal = $request->session()->get('start_date_internal');			
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
			$searchValues['start_date_internal'] = $start_date_internal;			
		}

		if($request->session()->get('end_date_internal') != '')
		{
			$end_date_internal = $request->session()->get('end_date_internal');			
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
			$searchValues['end_date_internal'] = $end_date_internal;			
		}

		if($request->session()->get('search_internal_flag') != '')
		{
			$search_internal_flag = $request->session()->get('search_internal_flag');
		}

		if($request->session()->get('CurrentMonthFilter') != '')
		{
			$CurrentMonthFilter = $request->session()->get('CurrentMonthFilter');
		}

		$departmentFormParentID = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get(['id']);

		$tableID = array();
		foreach($departmentFormParentID as $departmentFormParentIDData)
		{
			$tableID[] = $departmentFormParentIDData->id;
		}

		$departmentFormParentTotal = count($departmentFormParentID);

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$form_status = DepartmentFormEntry::where("form_status","!=",'')->where("form_id",'1')->groupBy('form_status')
		->selectRaw('count(*) as total, form_status')
		->get();

		

        return view("Attribute/Mashreq/loadBankContentsMashreqCard",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues','form_status','user_id','username','tableID','auto_download_array','search_internal_flag','CurrentMonthFilter'));
		//$request->session()->put('CurrentMonthFilter','');
	}
	
	
	public function loadBankContentsMashreqCardLogin(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_login_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		if($request->session()->get('search_login_flag') != '')
		{
			$search_login_flag = $request->session()->get('search_login_flag');
		}

		$whereRaw = " ref_no!=''";	

		if(@$request->session()->get('ref_no_string') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string');
			if(strlen($ref_no_string)>4)
			{
				$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
			}
			else
			{
				$whereRaw .= " AND ref_no IN ('0')";	
			}
			//$whereRaw .= " AND ref_no IN (".$ref_no_string.")";			
		}

		if(@$request->session()->get('team_login') != '')
		{
			$team = $request->session()->get('team_login');
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
			$searchValues['team_login'] = $team;
			
		}

		if(@$request->session()->get('sales_processor_login') != '')
		{
			$team = array();
			$team_Mahwish_130 = array('Ajay','Anas','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');

			$sales_processor_login = $request->session()->get('sales_processor_login');
			
			foreach($sales_processor_login as $sales_processor_login_value)
			{				
				if($sales_processor_login_value=='Mahwish')
				{
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_login_value=='Arsalan')
				{
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_login_value=='Umer')
				{
					$team = array_merge($team,$team_Umar_168);
				}
			}
			
			
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
			$searchValues['sales_processor_login'] = $sales_processor_login;
			
		}

		if(@$request->session()->get('emp_id_login') != '')
		{
			$emp_id = $request->session()->get('emp_id_login');
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
			$searchValues['emp_id_login'] = $emp_id;
			
		}

		if(@$request->session()->get('app_decision') != '')
		{
			$app_decision = $request->session()->get('app_decision');
			$app_decision_str = '';
			foreach($app_decision as $app_decision_value)
			{
				if($app_decision_str == '')
				{
					$app_decision_str = "'".$app_decision_value."'";
				}
				else
				{
					$app_decision_str = $app_decision_str.","."'".$app_decision_value."'";
				}
			}
			$whereRaw .= " AND app_decision IN (".$app_decision_str.")";			
		}

		if(@$request->session()->get('application_status') != '')
		{
			$application_status = $request->session()->get('application_status');
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
		}

		if(@$request->session()->get('cda_descision') != '')
		{
			$cda_descision = $request->session()->get('cda_descision');
			$cda_descision_str = '';
			foreach($cda_descision as $cda_descision_value)
			{
				if($cda_descision_str == '')
				{
					$cda_descision_str = "'".$cda_descision_value."'";
				}
				else
				{
					$cda_descision_str = $cda_descision_str.","."'".$cda_descision_value."'";
				}
			}
			$whereRaw .= " AND cda_descision IN (".$cda_descision_str.")";			
		}

		if(@$request->session()->get('booked_flag') != '')
		{
			$booked_flag = $request->session()->get('booked_flag');
			$booked_flag_str = '';
			foreach($booked_flag as $booked_flag_value)
			{
				if($booked_flag_str == '')
				{
					$booked_flag_str = "'".$booked_flag_value."'";
				}
				else
				{
					$booked_flag_str = $booked_flag_str.","."'".$booked_flag_value."'";
				}
			}
			$whereRaw .= " AND booked_flag IN (".$booked_flag_str.")";			
		}

		if(@$request->session()->get('bureau_segmentation') != '')
		{
			$bureau_segmentation = $request->session()->get('bureau_segmentation');
			$bureau_segmentation_str = '';
			foreach($bureau_segmentation as $bureau_segmentation_value)
			{
				if($bureau_segmentation_str == '')
				{
					$bureau_segmentation_str = "'".$bureau_segmentation_value."'";
				}
				else
				{
					$bureau_segmentation_str = $bureau_segmentation_str.","."'".$bureau_segmentation_value."'";
				}
			}
			$whereRaw .= " AND bureau_segmentation IN (".$bureau_segmentation_str.")";			
		}

		if(@$request->session()->get('employee_category_desc') != '')
		{
			$employee_category_desc = $request->session()->get('employee_category_desc');
			$employee_category_desc_str = '';
			foreach($employee_category_desc as $employee_category_desc_value)
			{
				if($employee_category_desc_str == '')
				{
					$employee_category_desc_str = "'".$employee_category_desc_value."'";
				}
				else
				{
					$employee_category_desc_str = $employee_category_desc_str.","."'".$employee_category_desc_value."'";
				}
			}
			$whereRaw .= " AND employee_category_desc IN (".$employee_category_desc_str.")";			
		}

		if(@$request->session()->get('applicationid') != '')
		{
			$applicationid = $request->session()->get('applicationid');						
			$whereRaw .= " AND applicationid = '".$applicationid."'";			
		}
		if(@$request->session()->get('mrs_score') != '')
		{
			$mrs_score_exp = explode("-",$request->session()->get('mrs_score'));
			if(count($mrs_score_exp)>1)
			{
				$whereRaw .= " AND mrs_score BETWEEN '".@$mrs_score_exp[0]."' AND '".@$mrs_score_exp[1]."'";
			}
			else
			{			
				$whereRaw .= " AND mrs_score = '".$mrs_score_exp[0]."'";	
			}
		}

		if(@$request->session()->get('bureau_score') != '')
		{
			$bureau_score_exp = explode("-",$request->session()->get('bureau_score'));
			if(count($bureau_score_exp)>1)
			{
				$whereRaw .= " AND bureau_score BETWEEN '".@$bureau_score_exp[0]."' AND '".@$bureau_score_exp[1]."'";
			}
			else
			{			
				$whereRaw .= " AND bureau_score = '".$bureau_score_exp[0]."'";	
			}
		}

		if($request->session()->get('start_date_login') != '')
		{
			$start_date_login = $request->session()->get('start_date_login');			
			$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_login))."'";
			$searchValues['start_date_login'] = $start_date_login;			
		}

		if($request->session()->get('end_date_login') != '')
		{
			$end_date_login = $request->session()->get('end_date_login');			
			$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_login))."'";
			$searchValues['end_date_login'] = $end_date_login;			
		}
		if(@$request->session()->get('ref_no_login') != '')
		{
			$ref_no_login = $request->session()->get('ref_no_login');						
			$whereRaw .= " AND ref_no = '".$ref_no_login."'";
			$searchValues['ref_no_login'] = $ref_no_login;
		}

		if(@$request->session()->get('cif_login') != '')
		{
			$cif_login = $request->session()->get('cif_login');						
			$whereRaw .= " AND cif = '".$cif_login."'";
			$searchValues['cif_login'] = $cif_login;
		}
		$tableID = array();
		if($whereRaw != " ref_no!=''")
		{
			$datasMashreqLoginID = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->get(['id']);		
			foreach($datasMashreqLoginID as $datasMashreqLoginIDData)
			{
				$tableID[] = $datasMashreqLoginIDData->id;
			}
		}

		$datasMashreqLoginCount = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->get(['id'])->count();
		
		$datasMashreqLogin = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->paginate($paginationValue);

		$app_decision = MashreqLoginMIS::where("app_decision","!=",'')->groupBy('app_decision')
		->selectRaw('count(*) as total, app_decision')
		->get();

		$application_status = MashreqLoginMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();

		$cda_descision = MashreqLoginMIS::where("cda_descision","!=",'')->groupBy('cda_descision')
		->selectRaw('count(*) as total, cda_descision')
		->get();

		$booked_flag = MashreqLoginMIS::where("booked_flag","!=",'')->groupBy('booked_flag')
		->selectRaw('count(*) as total, booked_flag')
		->get();		
		
		$bureau_segmentation = MashreqLoginMIS::where("bureau_segmentation","!=",'')->groupBy('bureau_segmentation')
		->selectRaw('count(*) as total, bureau_segmentation')
		->get();

		$employee_category_desc = MashreqLoginMIS::where("employee_category_desc","!=",'')->groupBy('employee_category_desc')
		->selectRaw('count(*) as total, employee_category_desc')
		->get();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();


		 return view("Attribute/Mashreq/loadBankContentsMashreqCardLogin",compact('datasMashreqLogin','datasMashreqLoginCount','app_decision','application_status','cda_descision','booked_flag','bureau_segmentation','employee_category_desc','searchValues','Employee_details','tableID','search_login_flag'));
	}

	public function loadBankContentsMashreqCardLoginCurrentMonth(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_login_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		if($request->session()->get('search_login_flag_cm') != '')
		{
			$search_login_flag = $request->session()->get('search_login_flag_cm');
		}

		$whereRaw = " ref_no!=''";	

		if(@$request->session()->get('ref_no_string_cm') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string_cm');
			if(strlen($ref_no_string)>4)
			{
				$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
			}
			else
			{
				$whereRaw .= " AND ref_no IN ('0')";	
			}
			//$whereRaw .= " AND ref_no IN (".$ref_no_string.")";			
		}

		if(@$request->session()->get('team_login_cm') != '')
		{
			$team = $request->session()->get('team_login_cm');
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
			$searchValues['team_login_cm'] = $team;
			
		}

		if(@$request->session()->get('sales_processor_login_cm') != '')
		{
			$team = array();
			$team_Mahwish_130 = array('Ajay','Anas','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');

			$sales_processor_login = $request->session()->get('sales_processor_login_cm');
			
			foreach($sales_processor_login as $sales_processor_login_value)
			{				
				if($sales_processor_login_value=='Mahwish')
				{
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_login_value=='Arsalan')
				{
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_login_value=='Umer')
				{
					$team = array_merge($team,$team_Umar_168);
				}
			}
			
			
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
			$searchValues['sales_processor_login_cm'] = $sales_processor_login;
			
		}

		if(@$request->session()->get('emp_id_login_cm') != '')
		{
			$emp_id = $request->session()->get('emp_id_login_cm');
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
			$searchValues['emp_id_login_cm'] = $emp_id;
			
		}

		if(@$request->session()->get('app_decision_cm') != '')
		{
			$app_decision = $request->session()->get('app_decision_cm');
			$app_decision_str = '';
			foreach($app_decision as $app_decision_value)
			{
				if($app_decision_str == '')
				{
					$app_decision_str = "'".$app_decision_value."'";
				}
				else
				{
					$app_decision_str = $app_decision_str.","."'".$app_decision_value."'";
				}
			}
			$whereRaw .= " AND app_decision IN (".$app_decision_str.")";			
		}

		if(@$request->session()->get('application_status_cm') != '')
		{
			$application_status = $request->session()->get('application_status_cm');
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
		}

		if(@$request->session()->get('cda_descision_cm') != '')
		{
			$cda_descision = $request->session()->get('cda_descision_cm');
			$cda_descision_str = '';
			foreach($cda_descision as $cda_descision_value)
			{
				if($cda_descision_str == '')
				{
					$cda_descision_str = "'".$cda_descision_value."'";
				}
				else
				{
					$cda_descision_str = $cda_descision_str.","."'".$cda_descision_value."'";
				}
			}
			$whereRaw .= " AND cda_descision IN (".$cda_descision_str.")";			
		}

		if(@$request->session()->get('booked_flag_cm') != '')
		{
			$booked_flag = $request->session()->get('booked_flag_cm');
			$booked_flag_str = '';
			foreach($booked_flag as $booked_flag_value)
			{
				if($booked_flag_str == '')
				{
					$booked_flag_str = "'".$booked_flag_value."'";
				}
				else
				{
					$booked_flag_str = $booked_flag_str.","."'".$booked_flag_value."'";
				}
			}
			$whereRaw .= " AND booked_flag IN (".$booked_flag_str.")";			
		}

		if(@$request->session()->get('bureau_segmentation_cm') != '')
		{
			$bureau_segmentation = $request->session()->get('bureau_segmentation_cm');
			$bureau_segmentation_str = '';
			foreach($bureau_segmentation as $bureau_segmentation_value)
			{
				if($bureau_segmentation_str == '')
				{
					$bureau_segmentation_str = "'".$bureau_segmentation_value."'";
				}
				else
				{
					$bureau_segmentation_str = $bureau_segmentation_str.","."'".$bureau_segmentation_value."'";
				}
			}
			$whereRaw .= " AND bureau_segmentation IN (".$bureau_segmentation_str.")";			
		}

		if(@$request->session()->get('employee_category_desc_cm') != '')
		{
			$employee_category_desc = $request->session()->get('employee_category_desc_cm');
			$employee_category_desc_str = '';
			foreach($employee_category_desc as $employee_category_desc_value)
			{
				if($employee_category_desc_str == '')
				{
					$employee_category_desc_str = "'".$employee_category_desc_value."'";
				}
				else
				{
					$employee_category_desc_str = $employee_category_desc_str.","."'".$employee_category_desc_value."'";
				}
			}
			$whereRaw .= " AND employee_category_desc IN (".$employee_category_desc_str.")";			
		}

		if(@$request->session()->get('applicationid_cm') != '')
		{
			$applicationid = $request->session()->get('applicationid_cm');						
			$whereRaw .= " AND applicationid = '".$applicationid."'";			
		}
		if(@$request->session()->get('mrs_score_cm') != '')
		{
			$mrs_score_exp = explode("-",$request->session()->get('mrs_score'));
			if(count($mrs_score_exp)>1)
			{
				$whereRaw .= " AND mrs_score BETWEEN '".@$mrs_score_exp[0]."' AND '".@$mrs_score_exp[1]."'";
			}
			else
			{			
				$whereRaw .= " AND mrs_score = '".$mrs_score_exp[0]."'";	
			}
		}

		if(@$request->session()->get('bureau_score_cm') != '')
		{
			$bureau_score_exp = explode("-",$request->session()->get('bureau_score'));
			if(count($bureau_score_exp)>1)
			{
				$whereRaw .= " AND bureau_score BETWEEN '".@$bureau_score_exp[0]."' AND '".@$bureau_score_exp[1]."'";
			}
			else
			{			
				$whereRaw .= " AND bureau_score = '".$bureau_score_exp[0]."'";	
			}
		}

		//if($request->session()->get('start_date_login_cm') != '')
		{
			$start_date_login = $request->session()->get('start_date_login_cm');			
			$whereRaw .= " AND application_date >='".date('Y-m-01')."'";
			$searchValues['start_date_login'] = $start_date_login;			
		}

		//if($request->session()->get('end_date_login_cm') != '')
		{
			$end_date_login = $request->session()->get('end_date_login_cm');			
			$whereRaw .= " AND application_date <='".date('Y-m-d')."'";
			$searchValues['end_date_login'] = $end_date_login;			
		}
		if(@$request->session()->get('ref_no_login_cm') != '')
		{
			$ref_no_login = $request->session()->get('ref_no_login_cm');						
			$whereRaw .= " AND ref_no = '".$ref_no_login."'";
			$searchValues['ref_no_login_cm'] = $ref_no_login;
		}

		if(@$request->session()->get('cif_login_cm') != '')
		{
			$cif_login = $request->session()->get('cif_login_cm');						
			$whereRaw .= " AND cif = '".$cif_login."'";
			$searchValues['cif_login_cm'] = $cif_login;
		}
		
		$tableID = array();
		if($whereRaw != " ref_no!=''")
		{
			$datasMashreqLoginID = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->get(['id']);		
			foreach($datasMashreqLoginID as $datasMashreqLoginIDData)
			{
				$tableID[] = $datasMashreqLoginIDData->id;
			}
		}

		$datasMashreqLoginCount = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->get()->count();
		$paginationValue = $datasMashreqLoginCount;
		$datasMashreqLogin = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('application_date','DESC')->paginate($paginationValue);

		$app_decision = MashreqLoginMIS::where("app_decision","!=",'')->groupBy('app_decision')
		->selectRaw('count(*) as total, app_decision')
		->get();

		$application_status = MashreqLoginMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();

		$cda_descision = MashreqLoginMIS::where("cda_descision","!=",'')->groupBy('cda_descision')
		->selectRaw('count(*) as total, cda_descision')
		->get();

		$booked_flag = MashreqLoginMIS::where("booked_flag","!=",'')->groupBy('booked_flag')
		->selectRaw('count(*) as total, booked_flag')
		->get();		
		
		$bureau_segmentation = MashreqLoginMIS::where("bureau_segmentation","!=",'')->groupBy('bureau_segmentation')
		->selectRaw('count(*) as total, bureau_segmentation')
		->get();

		$employee_category_desc = MashreqLoginMIS::where("employee_category_desc","!=",'')->groupBy('employee_category_desc')
		->selectRaw('count(*) as total, employee_category_desc')
		->get();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();


		 return view("Attribute/Mashreq/loadBankContentsMashreqCardLoginCurrentMonth",compact('datasMashreqLogin','datasMashreqLoginCount','app_decision','application_status','cda_descision','booked_flag','bureau_segmentation','employee_category_desc','searchValues','Employee_details','tableID','search_login_flag'));
	}
	
	
	public function loadBankContentsMashreqCardBooking(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_booking_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		$whereRaw = " instanceid!=''";
		if($request->session()->get('search_booking_flag') != '')
		{
			$search_booking_flag = $request->session()->get('search_booking_flag');
		}

		if(@$request->session()->get('team_booking') != '')
		{
			$team = $request->session()->get('team_booking');
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
			$searchValues['team_booking'] = $team;
			
		}

		if(@$request->session()->get('emp_id_booking') != '')
		{
			$emp_id = $request->session()->get('emp_id_booking');
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
			$searchValues['emp_id_booking'] = $emp_id;
			
		}



		if(@$request->session()->get('applicationid_string') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string');
			$whereRaw .= " AND instanceid IN (".$applicationid_string.")";			
		}
		if($request->session()->get('start_date_booking') != '')
		{
			$start_date_booking = $request->session()->get('start_date_booking');			
			$whereRaw .= " AND dateofdisbursal >='".date('Y-m-d',strtotime($start_date_booking))."'";
			$searchValues['start_date_booking'] = $start_date_booking;			
		}

		if($request->session()->get('end_date_booking') != '')
		{
			$end_date_booking = $request->session()->get('end_date_booking');			
			$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_booking))."'";
			$searchValues['end_date_booking'] = $end_date_booking;			
		}

		if(@$request->session()->get('ref_no_booking') != '')
		{
			$ref_no_booking = $request->session()->get('ref_no_booking');						
			$whereRaw .= " AND ref_no = '".$ref_no_booking."'";
			$searchValues['ref_no_booking'] = $ref_no_booking;
		}

		if(@$request->session()->get('card_status_booking') != '')
		{
			$card_status_booking = $request->session()->get('card_status_booking');	
			$card_status_str = '';
			foreach($card_status_booking as $card_status_booking_value)
			{
				if($card_status_str == '')
				{
					$card_status_str = "'".$card_status_booking_value."'";
				}
				else
				{
					$card_status_str = $card_status_str.","."'".$card_status_booking_value."'";
				}
			}
			$whereRaw .= " AND card_status IN (".$card_status_str.")";				
			$searchValues['card_status_booking'] = $card_status_booking;
		}

		if(@$request->session()->get('missing_internal_booking') != '')
		{
			$missing_internal_booking = $request->session()->get('missing_internal_booking');
			if($missing_internal_booking=='Missing in Internal (Current Month)')
			{
				$whereRaw .= " AND ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";
				$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
			}
			if($missing_internal_booking=='Missing in Internal')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";	
				$whereRaw .= " AND (emp_id='' OR emp_id IS NULL)";
				//$whereRaw .= " AND ref_no =''";	
			}

			if($missing_internal_booking=='Missing in Login and Internal (Current Month)')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (ref_no='' OR ref_no IS NULL)";
				$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";
				$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
			}
			if($missing_internal_booking=='Missing in Login and Internal')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (ref_no='' OR ref_no IS NULL)";
			}

			if($missing_internal_booking=='Unclaimed Booking')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (emp_id='' OR emp_id IS NULL)";
			}

			if($missing_internal_booking=='Coppied from MTD')
			{
				$whereRaw .= " AND data_from_mtd='1'";
			}
			
			$searchValues['missing_internal_booking'] = $missing_internal_booking;
		}

		if(@$request->session()->get('application_id_booking') != '')
		{
			$application_id_booking = $request->session()->get('application_id_booking');						
			$whereRaw .= " AND instanceid = '".$application_id_booking."'";
			$searchValues['application_id_booking'] = $application_id_booking;
		}
		if(@$request->session()->get('cif_cis_number') != '')
		{
			$cif_cis_number = $request->session()->get('cif_cis_number');						
			$whereRaw .= " AND cif_cis_number = '".$cif_cis_number."'";
			$searchValues['cif_cis_number'] = $cif_cis_number;
		}
		if(@$request->session()->get('sellerid') != '')
		{
			$sellerid = $request->session()->get('sellerid');						
			$whereRaw .= " AND sellerid = '".$sellerid."'";
			$searchValues['sellerid'] = $sellerid;
		}

		$datasMashreqBookingID = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->get(['id']);

		$tableID = array();
		foreach($datasMashreqBookingID as $datasMashreqBookingIDData)
		{
			$tableID[] = $datasMashreqBookingIDData->id;
		}

		$datasMashreqBooking = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->paginate($paginationValue);

		$datasMashreqBookingCount = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->count();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		//$datasMashreqBooking = MashreqBookingMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqBooking);
		exit;
		echo "done";
		exit;   */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBooking",compact('datasMashreqBooking','searchValues','datasMashreqBookingCount','Employee_details','tableID','search_booking_flag'));
	}

	public function loadBankContentsMashreqCardBookingCurrentMonth(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_booking_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		$whereRaw = " instanceid!=''";
		if($request->session()->get('search_booking_flag') != '')
		{
			$search_booking_flag = $request->session()->get('search_booking_flag');
		}

		if(@$request->session()->get('team_booking_cm') != '')
		{
			$team = $request->session()->get('team_booking_cm');
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
			$searchValues['team_booking_cm'] = $team;
			
		}

		if(@$request->session()->get('emp_id_booking_cm') != '')
		{
			$emp_id = $request->session()->get('emp_id_booking_cm');
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
			$searchValues['emp_id_booking_cm'] = $emp_id;
			
		}



		if(@$request->session()->get('applicationid_string_cm') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string_cm');
			$whereRaw .= " AND instanceid IN (".$applicationid_string.")";			
		}
		//if($request->session()->get('start_date_booking_cm') != '')
		{
			//$start_date_booking = $request->session()->get('start_date_booking_cm');			
			$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";
			//$searchValues['start_date_booking_cm'] = $start_date_booking;			
		}

		//if($request->session()->get('end_date_booking_cm') != '')
		{
			//$end_date_booking = $request->session()->get('end_date_booking_cm');			
			$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
			//$searchValues['end_date_booking_cm'] = $end_date_booking;			
		}

		if(@$request->session()->get('ref_no_booking_cm') != '')
		{
			$ref_no_booking = $request->session()->get('ref_no_booking_cm');						
			$whereRaw .= " AND ref_no = '".$ref_no_booking."'";
			$searchValues['ref_no_booking_cm'] = $ref_no_booking;
		}

		if(@$request->session()->get('card_status_booking_cm') != '')
		{
			$card_status_booking_cm = $request->session()->get('card_status_booking_cm');
			$card_status_str = '';
			foreach($card_status_booking_cm as $card_status_booking_value)
			{
				if($card_status_str == '')
				{
					$card_status_str = "'".$card_status_booking_value."'";
				}
				else
				{
					$card_status_str = $card_status_str.","."'".$card_status_booking_value."'";
				}
			}
			$whereRaw .= " AND card_status IN (".$card_status_str.")";				
			$searchValues['card_status_booking_cm'] = $card_status_booking_cm;
		}

		if(@$request->session()->get('missing_internal_booking_cm') != '')
		{
			$missing_internal_booking = $request->session()->get('missing_internal_booking_cm');
			if($missing_internal_booking=='Missing in Internal (Current Month)')
			{
				$whereRaw .= " AND ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";
				$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
			}
			if($missing_internal_booking=='Missing in Internal')
			{
				//$whereRaw .= " AND ref_no='' AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (emp_id='' OR emp_id IS NULL)";
				//$whereRaw .= " AND ref_no = ''";
			}

			if($missing_internal_booking=='Missing in Login and Internal (Current Month)')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (ref_no='' OR ref_no IS NULL)";
				$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";
				$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
			}
			if($missing_internal_booking=='Missing in Login and Internal')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (ref_no='' OR ref_no IS NULL)";
			}

			if($missing_internal_booking=='Unclaimed Booking')
			{
				//$whereRaw .= " AND (ref_no='' OR ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND (emp_id='' OR emp_id IS NULL)";
			}

			if($missing_internal_booking=='Coppied from MTD')
			{
				$whereRaw .= " AND data_from_mtd='1'";
			}
			
			$searchValues['missing_internal_booking'] = $missing_internal_booking;
		}

		if(@$request->session()->get('application_id_booking_cm') != '')
		{
			$application_id_booking = $request->session()->get('application_id_booking_cm');						
			$whereRaw .= " AND instanceid = '".$application_id_booking."'";
			$searchValues['application_id_booking_cm'] = $application_id_booking;
		}
		if(@$request->session()->get('cif_cis_number_cm') != '')
		{
			$cif_cis_number = $request->session()->get('cif_cis_number_cm');						
			$whereRaw .= " AND cif_cis_number = '".$cif_cis_number."'";
			$searchValues['cif_cis_number_cm'] = $cif_cis_number;
		}
		if(@$request->session()->get('sellerid_cm') != '')
		{
			$sellerid = $request->session()->get('sellerid_cm');						
			$whereRaw .= " AND sellerid = '".$sellerid."'";
			$searchValues['sellerid_cm'] = $sellerid;
		}

		$datasMashreqBookingID = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->get(['id']);

		$tableID = array();
		foreach($datasMashreqBookingID as $datasMashreqBookingIDData)
		{
			$tableID[] = $datasMashreqBookingIDData->id;
		}

		$datasMashreqBookingCount = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->count();

		$paginationValue = $datasMashreqBookingCount;
		if($paginationValue==0)
		{
			$paginationValue = 1;
		}

		$datasMashreqBooking = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->paginate($paginationValue);

		
		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		//$datasMashreqBooking = MashreqBookingMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqBooking);
		exit;
		echo "done";
		exit;   */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBookingCurrentMonth",compact('datasMashreqBooking','searchValues','datasMashreqBookingCount','Employee_details','tableID','search_booking_flag'));
	}

	public function loadBankContentsMashreqCardBank(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_bank_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		if($request->session()->get('search_bank_flag') != '')
		{
			$search_bank_flag = $request->session()->get('search_bank_flag');
		}
		$whereRaw = " rcms_id!=''";
		if(@$request->session()->get('applicationid_string') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string');
			$whereRaw .= " AND rcms_id IN (".$applicationid_string.")";			
		}

		if(@$request->session()->get('team_bank') != '')
		{
			$team = $request->session()->get('team_bank');
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
			$searchValues['team_bank'] = $team;
			
		}

		if(@$request->session()->get('emp_id_bank') != '')
		{
			$emp_id = $request->session()->get('emp_id_bank');
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
			$searchValues['emp_id_bank'] = $emp_id;
			
		}
		
		
		if(@$request->session()->get('booked_flag_bank') != '')
		{
			$booked_flag_bank = $request->session()->get('booked_flag_bank');
			$booked_flag_bank_str = '';
			foreach($booked_flag_bank as $bankS)
			{
				if($booked_flag_bank_str == '')
				{
					$booked_flag_bank_str = "'".$bankS."'";
				}
				else
				{
					$booked_flag_bank_str = $booked_flag_bank_str.","."'".$bankS."'";
				}
			}
			$whereRaw .= " AND booked_flag IN (".$booked_flag_bank_str.")";			
		}
		
		
		if(@$request->session()->get('status_bank') != '')
		{
			$status_bank = $request->session()->get('status_bank');
			$status_bank_str = '';
			foreach($status_bank as $bankStat)
			{
				if($status_bank_str == '')
				{
					$status_bank_str = "'".$bankStat."'";
				}
				else
				{
					$status_bank_str = $status_bank_str.","."'".$bankStat."'";
				}
			}
			$whereRaw .= " AND status IN (".$status_bank_str.")";			
		}

		if(@$request->session()->get('all_cda_deviation') != '')
		{
			$all_cda_deviation = $request->session()->get('all_cda_deviation');
			
			if(is_array($all_cda_deviation) && count($all_cda_deviation)>0)
			{
				$whereOR="(";
				foreach($all_cda_deviation as $all_cda_deviation_value)
				{
					$whereOR .= " all_cda_deviation LIKE '%".$all_cda_deviation_value."%' OR";
				}
				$whereOR = substr($whereOR,0,-2).")";

				$whereRaw .= " AND ".$whereOR;				
			}			
						
		}

		
		if(@$request->session()->get('disbured_date_from_bank') != '')
		{
			$disbured_date_from_bank = $request->session()->get('disbured_date_from_bank');
			$disbured_date_from_bank = date("Y-m-d",strtotime($disbured_date_from_bank));
			$whereRaw .= " AND disbursed_date >= '".$disbured_date_from_bank."'";			
		}
		
		if(@$request->session()->get('disbured_date_to_bank') != '')
		{
			$disbured_date_to_bank = $request->session()->get('disbured_date_to_bank');
			$disbured_date_to_bank = date("Y-m-d",strtotime($disbured_date_to_bank));
			$whereRaw .= " AND disbursed_date <= '".$disbured_date_to_bank."'";			
		}

		if(@$request->session()->get('application_date_from_bank') != '')
		{
			$application_date_from_bank = $request->session()->get('application_date_from_bank');
			$application_date_from_bank = date("Y-m-d",strtotime($application_date_from_bank));
			$whereRaw .= " AND application_date >= '".$application_date_from_bank."'";			
		}
		
		if(@$request->session()->get('application_date_to_bank') != '')
		{
			$application_date_to_bank = $request->session()->get('application_date_to_bank');
			$application_date_to_bank = date("Y-m-d",strtotime($application_date_to_bank));
			$whereRaw .= " AND application_date <= '".$application_date_to_bank."'";			
		}

		if(@$request->session()->get('ref_no_bank') != '')
		{
			$ref_no_bank = $request->session()->get('ref_no_bank');						
			$whereRaw .= " AND application_ref_no = '".$ref_no_bank."'";			
		}

		if(@$request->session()->get('application_id_bank') != '')
		{
			$application_id_bank = $request->session()->get('application_id_bank');						
			$whereRaw .= " AND rcms_id = '".$application_id_bank."'";			
		}

		if(@$request->session()->get('missing_internal_bank') != '')
		{
			$missing_internal_bank = $request->session()->get('missing_internal_bank');
			if($missing_internal_bank=='Missing in Internal (Current Month)')
			{
				$whereRaw .= " AND application_ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND application_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND application_date <='".date('Y-m-d')."'";
			}
			if($missing_internal_bank=='Missing in Internal')
			{
				$whereRaw .= " AND application_ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";				
			}

			if($missing_internal_bank=='Missing in Login and Internal (Current Month)')
			{
				$whereRaw .= " AND (application_ref_no='' OR application_ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND application_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND application_date <='".date('Y-m-d')."'";
			}
			if($missing_internal_bank=='Missing in Login and Internal')
			{
				$whereRaw .= " AND (application_ref_no='' OR application_ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
			}
			
			$searchValues['missing_internal_bank'] = $missing_internal_bank;
		}

		$datasMashreqBankId = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->get(['id']);
		$tableID = array();
		foreach($datasMashreqBankId as $datasMashreqBankIdData)
		{
			$tableID[] = $datasMashreqBankIdData->id;
		}
		
	
		$datasMashreqBank = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->paginate($paginationValue);
		
		$datasMashreqBankCount = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->count();

		//$datasMashreqBank = MashreqBankMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqBank);
		exit;
		echo "done";
		exit;    */

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$bankbooked_flag = MashreqBankMIS::where("booked_flag","!=",'')->groupBy('booked_flag')
		->selectRaw('DISTINCT booked_flag')
		->get();

		$cda_deviation = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();
		
		$status = MashreqBankMIS::where("status","!=",'')->groupBy('status')
		->selectRaw('DISTINCT status')
		->get();
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBank",compact('datasMashreqBank','searchValues','cda_deviation','bankbooked_flag','status','datasMashreqBankCount','Employee_details','tableID','search_bank_flag'));
	}

	public function loadBankContentsMashreqCardBankCurrentMonth(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_bank_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		if($request->session()->get('search_bank_flag_cm') != '')
		{
			$search_bank_flag = $request->session()->get('search_bank_flag_cm');
		}
		$whereRaw = " rcms_id!=''";
		if(@$request->session()->get('applicationid_string_cm') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string_cm');
			$whereRaw .= " AND rcms_id IN (".$applicationid_string.")";			
		}

		if(@$request->session()->get('team_bank_cm') != '')
		{
			$team = $request->session()->get('team_bank_cm');
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
			$searchValues['team_bank_cm'] = $team;
			
		}

		if(@$request->session()->get('emp_id_bank_cm') != '')
		{
			$emp_id = $request->session()->get('emp_id_bank_cm');
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
			$searchValues['emp_id_bank_cm'] = $emp_id;
			
		}
		
		
		if(@$request->session()->get('booked_flag_bank_cm') != '')
		{
			$booked_flag_bank = $request->session()->get('booked_flag_bank_cm');
			$booked_flag_bank_str = '';
			foreach($booked_flag_bank as $bankS)
			{
				if($booked_flag_bank_str == '')
				{
					$booked_flag_bank_str = "'".$bankS."'";
				}
				else
				{
					$booked_flag_bank_str = $booked_flag_bank_str.","."'".$bankS."'";
				}
			}
			$whereRaw .= " AND booked_flag IN (".$booked_flag_bank_str.")";			
		}
		
		
		if(@$request->session()->get('status_bank_cm') != '')
		{
			$status_bank = $request->session()->get('status_bank_cm');
			$status_bank_str = '';
			foreach($status_bank as $bankStat)
			{
				if($status_bank_str == '')
				{
					$status_bank_str = "'".$bankStat."'";
				}
				else
				{
					$status_bank_str = $status_bank_str.","."'".$bankStat."'";
				}
			}
			$whereRaw .= " AND status IN (".$status_bank_str.")";			
		}

		if(@$request->session()->get('all_cda_deviation_cm') != '')
		{
			$all_cda_deviation = $request->session()->get('all_cda_deviation_cm');
			
			if(is_array($all_cda_deviation) && count($all_cda_deviation)>0)
			{
				$whereOR="(";
				foreach($all_cda_deviation as $all_cda_deviation_value)
				{
					$whereOR .= " all_cda_deviation LIKE '%".$all_cda_deviation_value."%' OR";
				}
				$whereOR = substr($whereOR,0,-2).")";

				$whereRaw .= " AND ".$whereOR;				
			}			
						
		}

		
		
			$disbured_date_from_bank = date("Y-m-01");
			$whereRaw .= " AND disbursed_date >= '".$disbured_date_from_bank."'";
			
			$disbured_date_to_bank = date("Y-m-31");
			$whereRaw .= " AND disbursed_date <= '".$disbured_date_to_bank."'";	
		
		
		

		if(@$request->session()->get('application_date_from_bank_cm') != '')
		{
			$application_date_from_bank = $request->session()->get('application_date_from_bank_cm');
			$application_date_from_bank = date("Y-m-d",strtotime($application_date_from_bank));
			$whereRaw .= " AND application_date >= '".$application_date_from_bank."'";			
		}
		
		if(@$request->session()->get('application_date_to_bank_cm') != '')
		{
			$application_date_to_bank = $request->session()->get('application_date_to_bank_cm');
			$application_date_to_bank = date("Y-m-d",strtotime($application_date_to_bank));
			$whereRaw .= " AND application_date <= '".$application_date_to_bank."'";			
		}

		if(@$request->session()->get('ref_no_bank_cm') != '')
		{
			$ref_no_bank = $request->session()->get('ref_no_bank_cm');						
			$whereRaw .= " AND application_ref_no = '".$ref_no_bank."'";			
		}

		if(@$request->session()->get('application_id_bank_cm') != '')
		{
			$application_id_bank = $request->session()->get('application_id_bank_cm');						
			$whereRaw .= " AND rcms_id = '".$application_id_bank."'";			
		}

		if(@$request->session()->get('missing_internal_bank_cm') != '')
		{
			$missing_internal_bank = $request->session()->get('missing_internal_bank_cm');
			if($missing_internal_bank=='Missing in Internal (Current Month)')
			{
				$whereRaw .= " AND application_ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND application_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND application_date <='".date('Y-m-d')."'";
			}
			if($missing_internal_bank=='Missing in Internal')
			{
				$whereRaw .= " AND application_ref_no!='' AND (emp_id = '' OR emp_id IS NULL)";				
			}

			if($missing_internal_bank=='Missing in Login and Internal (Current Month)')
			{
				$whereRaw .= " AND (application_ref_no='' OR application_ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
				$whereRaw .= " AND application_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND application_date <='".date('Y-m-d')."'";
			}
			if($missing_internal_bank=='Missing in Login and Internal')
			{
				$whereRaw .= " AND (application_ref_no='' OR application_ref_no IS NULL) AND (emp_id = '' OR emp_id IS NULL)";
			}
			
			$searchValues['missing_internal_bank_cm'] = $missing_internal_bank;
		}

		$datasMashreqBankId = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->get(['id']);
		$tableID = array();
		foreach($datasMashreqBankId as $datasMashreqBankIdData)
		{
			$tableID[] = $datasMashreqBankIdData->id;
		}
		
		$datasMashreqBankCount = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->count();
		$paginationValue = $datasMashreqBankCount;

		$datasMashreqBank = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->orderby('disbursed_date','DESC')->paginate($paginationValue);
		
		

		//$datasMashreqBank = MashreqBankMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqBank);
		exit;
		echo "done";
		exit;    */

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$bankbooked_flag = MashreqBankMIS::where("booked_flag","!=",'')->groupBy('booked_flag')
		->selectRaw('DISTINCT booked_flag')
		->get();

		$cda_deviation = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();
		
		$status = MashreqBankMIS::where("status","!=",'')->groupBy('status')
		->selectRaw('DISTINCT status')
		->get();
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBankCurrentMonth",compact('datasMashreqBank','searchValues','cda_deviation','bankbooked_flag','status','datasMashreqBankCount','Employee_details','tableID','search_bank_flag'));
	}

	public function loadBankContentsMashreqCardMTD(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		
		$whereRaw = " instanceid!=''";
		if(@$request->session()->get('applicationid_string') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string');
			$whereRaw .= " AND instanceid IN (".$applicationid_string.")";			
		}

		if(@$request->session()->get('team_mtd') != '')
		{
			$team = $request->session()->get('team_mtd');
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
			$searchValues['team_mtd'] = $team;
			
		}

		if(@$request->session()->get('emp_id_mtd') != '')
		{
			$emp_id = $request->session()->get('emp_id_mtd');
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
			$searchValues['emp_id_mtd'] = $emp_id;
			
		}

		if($request->session()->get('start_date_mtd') != '')
		{
			$start_date_mtd = $request->session()->get('start_date_mtd');			
			$whereRaw .= " AND dateofdisbursal >='".date('Y-m-d',strtotime($start_date_mtd))."'";
			$searchValues['start_date_mtd'] = $start_date_mtd;			
		}

		if($request->session()->get('end_date_mtd') != '')
		{
			$end_date_mtd = $request->session()->get('end_date_mtd');			
			$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_mtd))."'";
			$searchValues['end_date_mtd'] = $end_date_mtd;			
		}

		if(@$request->session()->get('ref_no_mtd') != '')
		{
			$ref_no_mtd = $request->session()->get('ref_no_mtd');						
			$whereRaw .= " AND ref_no = '".$ref_no_mtd."'";			
		}

		if(@$request->session()->get('application_id_mtd') != '')
		{
			$application_id_mtd = $request->session()->get('application_id_mtd');						
			$whereRaw .= " AND instanceid = '".$application_id_mtd."'";			
		}

		$datasMashreqMTD = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->paginate($paginationValue);
		$datasMashreqMTDCount = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->count();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		//$datasMashreqMTD = MashreqMTDMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqMTD);
		exit;
		echo "done";
		exit;     */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMTD",compact('datasMashreqMTD','searchValues','datasMashreqMTDCount','Employee_details'));
	}

	public function loadBankContentsMashreqCardMTDCurrentMonth(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		
		$whereRaw = " instanceid!=''";
		if(@$request->session()->get('applicationid_string_cm') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string_cm');
			$whereRaw .= " AND instanceid IN (".$applicationid_string.")";			
		}

		if(@$request->session()->get('team_mtd_cm') != '')
		{
			$team = $request->session()->get('team_mtd_cm');
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
			$searchValues['team_mtd_cm'] = $team;
			
		}

		if(@$request->session()->get('emp_id_mtd_cm') != '')
		{
			$emp_id = $request->session()->get('emp_id_mtd_cm');
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
			$searchValues['emp_id_mtd_cm'] = $emp_id;
			
		}

					
			$whereRaw .= " AND dateofdisbursal >='".date('Y-m-01')."'";			
					
			$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d')."'";
		

		

		if(@$request->session()->get('ref_no_mtd_cm') != '')
		{
			$ref_no_mtd = $request->session()->get('ref_no_mtd_cm');						
			$whereRaw .= " AND ref_no = '".$ref_no_mtd."'";			
		}

		if(@$request->session()->get('application_id_mtd_cm') != '')
		{
			$application_id_mtd = $request->session()->get('application_id_mtd_cm');						
			$whereRaw .= " AND instanceid = '".$application_id_mtd."'";			
		}
		$datasMashreqMTDCount = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->count();
		$paginationValue = $datasMashreqMTDCount;

		$datasMashreqMTD = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->orderby('dateofdisbursal','DESC')->paginate($paginationValue);
		

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		//$datasMashreqMTD = MashreqMTDMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqMTD);
		exit;
		echo "done";
		exit;     */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMTDCurrentMonth",compact('datasMashreqMTD','searchValues','datasMashreqMTDCount','Employee_details'));
	}

	public function loadBankContentsMashreqCardFinalMTD(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		$search_final_mtd_flag = '';
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		
		$whereRaw = " instanceid!=''";
		if(@$request->session()->get('applicationid_string') != '' && @$request->session()->get('form_id') != '')
		{
			$applicationid_string = $request->session()->get('applicationid_string');
			$whereRaw .= " AND instanceid IN (".$applicationid_string.")";			
		}

		if(@$request->session()->get('team_Finalmtd') != '')
		{
			$team = $request->session()->get('team_Finalmtd');
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
			$searchValues['team_Finalmtd'] = $team;
			
		}

		if(@$request->session()->get('emp_id_Finalmtd') != '')
		{
			$emp_id = $request->session()->get('emp_id_Finalmtd');
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
			$searchValues['emp_id_Finalmtd'] = $emp_id;
			
		}

		if($request->session()->get('start_date_Finalmtd') != '')
		{
			$start_date_Finalmtd = $request->session()->get('start_date_Finalmtd');			
			$whereRaw .= " AND dateofdisbursal >='".date('Y-m-d',strtotime($start_date_Finalmtd))."'";
			$searchValues['start_date_Finalmtd'] = $start_date_Finalmtd;			
		}

		if($request->session()->get('end_date_Finalmtd') != '')
		{
			$end_date_Finalmtd = $request->session()->get('end_date_Finalmtd');			
			$whereRaw .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_Finalmtd))."'";
			$searchValues['end_date_Finalmtd'] = $end_date_Finalmtd;			
		}

		if(@$request->session()->get('ref_no_Finalmtd') != '')
		{
			$ref_no_Finalmtd = $request->session()->get('ref_no_Finalmtd');						
			$whereRaw .= " AND ref_no = '".$ref_no_Finalmtd."'";			
		}

		if(@$request->session()->get('application_id_Finalmtd') != '')
		{
			$application_id_Finalmtd = $request->session()->get('application_id_Finalmtd');						
			$whereRaw .= " AND instanceid = '".$application_id_Finalmtd."'";			
		}

		if($request->session()->get('search_final_mtd_flag') != '')
		{
			$search_final_mtd_flag = $request->session()->get('search_final_mtd_flag');
		}

		$datasMashreqMTD = DB::table('mashreq_final_mtd_mis')->whereRaw($whereRaw)->orderby('instanceid','DESC')->paginate($paginationValue);
		$datasMashreqMTDCount = DB::table('mashreq_final_mtd_mis')->whereRaw($whereRaw)->orderby('instanceid','DESC')->count();

		$datasMashreqFinalMTD = DB::table('mashreq_final_mtd_mis')->whereRaw($whereRaw)->orderby('instanceid','DESC')->get(['id']);

		$tableID = array();
		foreach($datasMashreqFinalMTD as $datasMashreqFinalMTDData)
		{
			$tableID[] = $datasMashreqFinalMTDData->id;
		}

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		//$datasMashreqMTD = MashreqMTDMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqMTD);
		exit;
		echo "done";
		exit;     */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardFinalMTD",compact('datasMashreqMTD','searchValues','datasMashreqMTDCount','Employee_details','tableID','search_final_mtd_flag'));
	}
	
	public function loadBankContentsMashreqCardMaster_old(Request $request)
	{
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		
		$whereRaw = " ref_no!=''";	

		if(@$request->session()->get('ref_no_string') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string');
			$whereRaw .= " AND ref_no IN (".$ref_no_string.")";			
		}

		if(@$request->session()->get('app_decision') != '')
		{
			$app_decision = $request->session()->get('app_decision');
			$app_decision_str = '';
			foreach($app_decision as $app_decision_value)
			{
				if($app_decision_str == '')
				{
					$app_decision_str = "'".$app_decision_value."'";
				}
				else
				{
					$app_decision_str = $app_decision_str.","."'".$app_decision_value."'";
				}
			}
			$whereRaw .= " AND app_decision IN (".$app_decision_str.")";			
		}

		if(@$request->session()->get('application_status') != '')
		{
			$application_status = $request->session()->get('application_status');
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
		}

		if(@$request->session()->get('cda_descision') != '')
		{
			$cda_descision = $request->session()->get('cda_descision');
			$cda_descision_str = '';
			foreach($cda_descision as $cda_descision_value)
			{
				if($cda_descision_str == '')
				{
					$cda_descision_str = "'".$cda_descision_value."'";
				}
				else
				{
					$cda_descision_str = $cda_descision_str.","."'".$cda_descision_value."'";
				}
			}
			$whereRaw .= " AND cda_descision IN (".$cda_descision_str.")";			
		}

		if(@$request->session()->get('seller_id') != '')
		{
			$seller_id = $request->session()->get('seller_id');
			$seller_id_str = '';
			foreach($seller_id as $seller_id_value)
			{
				if($seller_id_str == '')
				{
					$seller_id_str = "'".$seller_id_value."'";
				}
				else
				{
					$seller_id_str = $seller_id_str.","."'".$seller_id_value."'";
				}
			}
			$whereRaw .= " AND seller_id IN (".$seller_id_str.")";			
		}

		if(@$request->session()->get('master_remarks') != '')
		{
			$remarks = $request->session()->get('master_remarks');						
			$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
		}

		if($request->session()->get('start_date_master') != '')
		{
			$start_date_master = $request->session()->get('start_date_master');			
			$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_master))."'";
			$searchValues['start_date_master'] = $start_date_master;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_master') != '')
		{
			$end_date_master = $request->session()->get('end_date_master');			
			$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_master))."'";
			$searchValues['end_date_master'] = $end_date_master;
			$login_flag = 1;
		}

		if(@$request->session()->get('ref_no_master') != '')
		{
			$ref_no_master = $request->session()->get('ref_no_master');						
			$whereRaw .= " AND ref_no = '".$ref_no_master."'";			
		}



		$datasMashreqMasterCount = DB::table('mashreq_master_mis')->whereRaw($whereRaw)->orderby('application_date','DESC')->get()->count();
		
		$datasMashreqMaster = DB::table('mashreq_master_mis')->whereRaw($whereRaw)->orderby('application_date','DESC')->paginate($paginationValue);

		$app_decision = MashreqMasterMIS::where("app_decision","!=",'')->groupBy('app_decision')
		->selectRaw('count(*) as total, app_decision')
		->get();

		$application_status = MashreqMasterMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();

		$cda_descision = MashreqMasterMIS::where("cda_descision","!=",'')->groupBy('cda_descision')
		->selectRaw('count(*) as total, cda_descision')
		->get();

		$seller_id = MashreqMasterMIS::where("seller_id","!=",'')->groupBy('seller_id')
		->selectRaw('count(*) as total, seller_id')
		->get();

		//$datasMashreqMaster = MashreqMasterMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqMaster);
		exit;
		echo "done";
		exit;      */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMaster",compact('datasMashreqMaster','searchValues','app_decision','application_status','cda_descision','seller_id','datasMashreqMasterCount'));
	}

	public function loadBankContentsMashreqCardMaster(Request $request)
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
		
		$whereRaw = " ref_no!=''";	

		if(@$request->session()->get('ref_no_string') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string');
			if(strlen($ref_no_string)>4)
			{
				$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
			}
			else
			{
				$whereRaw .= " AND ref_no IN ('0')";	
			}
			
					
		}

		if(@$request->session()->get('team_master') != '')
		{
			$team = $request->session()->get('team_master');
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
			$searchValues['team_master'] = $team;
			
		}

		if(@$request->session()->get('sales_processor_master') != '')
		{
			$team = array();
			$team_Mahwish_130 = array('Ajay','Anas','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');

			$sales_processor_master = $request->session()->get('sales_processor_master');
			
			foreach($sales_processor_master as $sales_processor_master_value)
			{				
				if($sales_processor_master_value=='Mahwish')
				{
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_master_value=='Arsalan')
				{
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_master_value=='Umer')
				{
					$team = array_merge($team,$team_Umar_168);
				}
			}		
			
			
			
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
			$searchValues['sales_processor_master'] = $sales_processor_master;
			
		}

		if(@$request->session()->get('emp_id_master') != '')
		{
			$emp_id = $request->session()->get('emp_id_master');
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
			$searchValues['emp_id_master'] = $emp_id;
			
		}

		if(@$request->session()->get('app_decision') != '')
		{
			$app_decision = $request->session()->get('app_decision');
			$app_decision_str = '';
			foreach($app_decision as $app_decision_value)
			{
				if($app_decision_str == '')
				{
					$app_decision_str = "'".$app_decision_value."'";
				}
				else
				{
					$app_decision_str = $app_decision_str.","."'".$app_decision_value."'";
				}
			}
			$whereRaw .= " AND app_decision IN (".$app_decision_str.")";			
		}

		if(@$request->session()->get('application_status') != '')
		{
			$application_status = $request->session()->get('application_status');
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
		}

		if(@$request->session()->get('cda_descision') != '')
		{
			$cda_descision = $request->session()->get('cda_descision');
			$cda_descision_str = '';
			foreach($cda_descision as $cda_descision_value)
			{
				if($cda_descision_str == '')
				{
					$cda_descision_str = "'".$cda_descision_value."'";
				}
				else
				{
					$cda_descision_str = $cda_descision_str.","."'".$cda_descision_value."'";
				}
			}
			$whereRaw .= " AND cda_descision IN (".$cda_descision_str.")";			
		}

		/*if(@$request->session()->get('seller_id') != '')
		{
			$seller_id = $request->session()->get('seller_id');
			$seller_id_str = '';
			foreach($seller_id as $seller_id_value)
			{
				if($seller_id_str == '')
				{
					$seller_id_str = "'".$seller_id_value."'";
				}
				else
				{
					$seller_id_str = $seller_id_str.","."'".$seller_id_value."'";
				}
			}
			$whereRaw .= " AND seller_id IN (".$seller_id_str.")";			
		}*/

		if(@$request->session()->get('master_remarks') != '')
		{
			$remarks = $request->session()->get('master_remarks');						
			$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
		}

		if($request->session()->get('start_date_master') != '')
		{
			$start_date_master = $request->session()->get('start_date_master');			
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_master))."'";
			$searchValues['start_date_master'] = $start_date_master;
			$login_flag = 1;
		}

		if($request->session()->get('end_date_master') != '')
		{
			$end_date_master = $request->session()->get('end_date_master');			
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_master))."'";
			$searchValues['end_date_master'] = $end_date_master;
			$login_flag = 1;
		}

		if(@$request->session()->get('ref_no_master') != '')
		{
			$ref_no_master = $request->session()->get('ref_no_master');						
			$whereRaw .= " AND ref_no = '".$ref_no_master."'";			
		}
		if(@$request->session()->get('applicationid_master') != '')
		{
			$applicationid_master = $request->session()->get('applicationid_master');						
			$whereRaw .= " AND applicationid = '".$applicationid_master."'";			
		}

		$datasMashreqMasterCount = DB::table('mashreq_master_mis_1')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();
		
		$datasMashreqMaster = DB::table('mashreq_master_mis_1')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		$app_decision = MashreqMasterMIS::where("app_decision","!=",'')->groupBy('app_decision')
		->selectRaw('count(*) as total, app_decision')
		->get();

		$application_status = MashreqMasterMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();

		$cda_descision = MashreqMasterMIS::where("cda_descision","!=",'')->groupBy('cda_descision')
		->selectRaw('count(*) as total, cda_descision')
		->get();

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		/*$seller_id = MashreqMasterMIS::where("seller_id","!=",'')->groupBy('seller_id')
		->selectRaw('count(*) as total, seller_id')
		->get();*/

		//$datasMashreqMaster = MashreqMasterMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqMaster);
		exit;
		echo "done";
		exit;      */
		$seller_id = array();
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMaster",compact('datasMashreqMaster','searchValues','app_decision','application_status','cda_descision','seller_id','datasMashreqMasterCount','Employee_details','user_id','username'));
	}

	public function load_reProcess(Request $request)
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

		//$whereRaw = " min_startdate!='' AND min_startdate!='0000-00-00'";
		$whereRaw = " min_startdate IS NOT NULL";
		
		$vardays='0';

		if($request->session()->get('cif_reProcess') != '')
		{
			$cif_reProcess = $request->session()->get('cif_reProcess');
			$whereRaw .= " AND cif ='".$cif_reProcess."'";
			$searchValues['cif_reProcess'] = $cif_reProcess;
		}

		if($request->session()->get('ref_no_reProcess') != '')
		{
			$ref_no_reProcess = $request->session()->get('ref_no_reProcess');
			$whereRaw .= " AND ref_no ='".$ref_no_reProcess."'";
			$searchValues['ref_no_reProcess'] = $ref_no_reProcess;
		}

		if($request->session()->get('statistics_reProcess') != '')
		{
			if($request->session()->get('statistics_reProcess') == 'Booked')
			{
				$whereRaw .= " AND booking_status ='1'";					
			}
			if($request->session()->get('statistics_reProcess') == 'Re-submitted')
			{
				$whereRaw .= " AND submit_count>0";					
			}
			if($request->session()->get('statistics_reProcess') == 'Not Re-submitted')
			{
				$whereRaw .= " AND submit_count IS NULL";					
			}

			$searchValues['statistics_reProcess'] = $request->session()->get('statistics_reProcess');
		}

		if(@$request->session()->get('team_reProcess') != '')
		{
			$team = $request->session()->get('team_reProcess');
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
			$searchValues['team_reProcess'] = $team;
			
		}
		

		

		if((@$request->session()->get('scheme_group_reProcess') == '' || @$request->session()->get('scheme_group_reProcess') == 'Bureau Segmentation Change') && $request->session()->get('ref_no_reProcess') == '')
		{
			$request->session()->put('scheme_group_reProcess','Bureau Segmentation Change');
			$request->session()->put('scheme_reProcess','BSC: NH --> T2- 5/6.99K, T2');

			$whereRaw .= " AND bureau_segmentation='NO HIT' and cdafinalsalary<'7000.00'";		

			if($request->session()->get('eligibility_date_minus') != '')
			{
				$request->session()->put('start_date_reProcess','');
				$request->session()->put('end_date_reProcess','');

				$vardays = $request->session()->get('eligibility_date_minus');
				
				$eligibility_date_var = date("Y-m-d", strtotime("-6 months -".$vardays." days"));
				$whereRaw .= " AND min_startdate ='".$eligibility_date_var."'";
				$searchValues['eligibility_date_minus'] = $vardays;
				
			}

			if($request->session()->get('start_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-6 months", strtotime($request->session()->get('start_date_reProcess'))));

				$start_date_reProcess = $request->session()->get('start_date_reProcess');			
				$whereRaw .= " AND min_startdate >='".$eligibility_date_6."'";
				$searchValues['start_date_reProcess'] = $start_date_reProcess;
				
			}

			if($request->session()->get('end_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-6 months", strtotime($request->session()->get('end_date_reProcess'))));

				$end_date_reProcess = $request->session()->get('end_date_reProcess');			
				$whereRaw .= " AND min_startdate <='".$eligibility_date_6."'";
				$searchValues['end_date_reProcess'] = $end_date_reProcess;
				
			}
					
		}

		if(@$request->session()->get('scheme_group_reProcess') == '' || @$request->session()->get('scheme_group_reProcess') == 'Salary Credit Alert')
		{
			$request->session()->put('scheme_group_reProcess','Salary Credit Alert');
			$scheme_reProcess = @$request->session()->get('scheme_reProcess');

			if($scheme_reProcess=='SCA: 1/4 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='NO HIT' and cdafinalsalary>='7000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%1 v/s 4%' OR bank_remarks LIKE '%1 vs 4%')";
			}

			if($scheme_reProcess=='SCA: 1/6 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='NO HIT' and cdafinalsalary>='7000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%1 v/s 6%' OR bank_remarks LIKE '%1 vs 6%')";
			}

			if($scheme_reProcess=='SCA: 2/4 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='NO HIT' and cdafinalsalary>='7000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%2 v/s 4%' OR bank_remarks LIKE '%2 vs 4%')";
			}

			if($scheme_reProcess=='SCA: 2/6 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='NO HIT' and cdafinalsalary>='7000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%2 v/s 6%' OR bank_remarks LIKE '%2 vs 6%')";
			}

			if($scheme_reProcess=='SCA: 3/4 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='THIN2' and cdafinalsalary>='5000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%3 v/s 4%' OR bank_remarks LIKE '%3 vs 4%')";
			}
			if($scheme_reProcess=='SCA: 3/6 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='THIN1' and cdafinalsalary>='5000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%3 v/s 6%' OR bank_remarks LIKE '%3 vs 6%')";
			}
			if($scheme_reProcess=='SCA: 4/6 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='RICH' and cdafinalsalary>='5000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%4 v/s 6%' OR bank_remarks LIKE '%4 vs 6%')";
			}
			if($scheme_reProcess=='SCA: 5/6 salaries reached')
			{
				//$whereRaw .= " AND bureau_segmentation='RICH' and cdafinalsalary>='5000.00'";	

				$whereRaw .= " AND (bank_remarks LIKE '%5 v/s 6%' OR bank_remarks LIKE '%5 vs 6%')";
			}

			

			

			if($request->session()->get('start_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-6 months", strtotime($request->session()->get('start_date_reProcess'))));

				$start_date_reProcess = $request->session()->get('start_date_reProcess');			
				$whereRaw .= " AND min_startdate >='".$eligibility_date_6."'";
				$searchValues['start_date_reProcess'] = $start_date_reProcess;
				
			}

			if($request->session()->get('end_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-6 months", strtotime($request->session()->get('end_date_reProcess'))));

				$end_date_reProcess = $request->session()->get('end_date_reProcess');			
				$whereRaw .= " AND min_startdate <='".$eligibility_date_6."'";
				$searchValues['end_date_reProcess'] = $end_date_reProcess;
				
			}
					
		}


		if(@$request->session()->get('scheme_group_reProcess') != '' && @$request->session()->get('scheme_group_reProcess') == 'BV Waiver Approved')
		{
			$whereRaw .= " AND bureau_segmentation='THIN2' and cdafinalsalary>='5000.00'";	
			$whereRaw .= " AND (last_comment LIKE '%D211%' OR last_comment LIKE '%B101%' OR last_comment LIKE '%Company%' OR last_comment LIKE '%BV%' OR last_comment LIKE '%Office%' OR last_comment LIKE '%Website%' OR last_comment LIKE '%Employer%')";	
			/*
			D211
			no hit and thin 2 customer and not listed company
			-- BV -required 
			After 6 Month if thin2 and 1 year if No hit
			Thin 1 -- -- BV -not required
			Base condition (B101)- "Company" "BV" "Office" "Website" "Employer"
			*/
			if($request->session()->get('eligibility_date_minus') != '')
			{
				$request->session()->put('start_date_reProcess','');
				$request->session()->put('end_date_reProcess','');

				$vardays = $request->session()->get('eligibility_date_minus');
				
				$eligibility_date_var = date("Y-m-d", strtotime("-12 months -".$vardays." days"));
				$whereRaw .= " AND min_startdate ='".$eligibility_date_var."'";
				$searchValues['eligibility_date_minus'] = $vardays;
				
			}

			if($request->session()->get('start_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-12 months", strtotime($request->session()->get('start_date_reProcess'))));

				$start_date_reProcess = $request->session()->get('start_date_reProcess');			
				$whereRaw .= " AND min_startdate >='".$eligibility_date_6."'";
				$searchValues['start_date_reProcess'] = $start_date_reProcess;
				
			}

			if($request->session()->get('end_date_reProcess') != '')
			{
				$eligibility_date_6 = date("Y-m-d", strtotime("-12 months", strtotime($request->session()->get('end_date_reProcess'))));

				$end_date_reProcess = $request->session()->get('end_date_reProcess');			
				$whereRaw .= " AND min_startdate <='".$eligibility_date_6."'";
				$searchValues['end_date_reProcess'] = $end_date_reProcess;
				
			}
					
		}
		
		
		//echo $whereRaw;

		$today = date('Y-m-d');
		$month6back = date("Y-m-d", strtotime("-6 months"));
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

			
		$datasReProcess = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('min_startdate','ASC')->paginate($paginationValue);
		$datasReProcessCount = DB::table('mashreq_login_data')->whereRaw($whereRaw)->orderby('min_startdate','ASC')->count();		

		
		 return view("Attribute/Mashreq/load_reProcess",compact('datasReProcess','datasReProcessCount','searchValues','Employee_details','user_id','username'));
	}
	
	public function searchBankInner(Request $request)
	{
		$requestParameters = $request->input();
		$ref_no_bank = '';
		$application_id_bank = '';
		$missing_internal_bank = '';

		if(@isset($requestParameters['ref_no_bank']))
		{
			$ref_no_bank = @$requestParameters['ref_no_bank'];
		}

		if(@isset($requestParameters['application_id_bank']))
		{
			$application_id_bank = @$requestParameters['application_id_bank'];
		}
		
		if(isset($requestParameters['booked_flag_bank']))
		{
			$booked_flag_bank = $requestParameters['booked_flag_bank'];
		}
		else
		{
			$booked_flag_bank = '';
		}

		if(@isset($requestParameters['team_bank']))
		{
			$team_bank = @$requestParameters['team_bank'];
		}
		else
		{
			$team_bank = '';
		}

		if(@isset($requestParameters['emp_id_bank']))
		{
			$emp_id_bank = @$requestParameters['emp_id_bank'];
		}
		else
		{
			$emp_id_bank = '';
		}
		
		
		if(isset($requestParameters['status_bank']))
		{
			$status_bank = $requestParameters['status_bank'];
		}
		else
		{
			$status_bank = '';
		}

		if(isset($requestParameters['all_cda_deviation']))
		{
			$all_cda_deviation = $requestParameters['all_cda_deviation'];
		}
		else
		{
			$all_cda_deviation = '';
		}

		if(@isset($requestParameters['missing_internal_bank']))
		{
			$missing_internal_bank = @$requestParameters['missing_internal_bank'];
		}
		
		
		$disbured_date_from = $requestParameters['disbured_date_from'];
		$disbured_date_to = $requestParameters['disbured_date_to'];

		$application_date_from_bank = $requestParameters['application_date_from_bank'];
		$application_date_to_bank = $requestParameters['application_date_to_bank'];
		
		$request->session()->put('ref_no_bank',$ref_no_bank);
		$request->session()->put('missing_internal_bank',$missing_internal_bank);
		$request->session()->put('application_id_bank',$application_id_bank);
		$request->session()->put('all_cda_deviation',$all_cda_deviation);
		$request->session()->put('booked_flag_bank',$booked_flag_bank);
		$request->session()->put('team_bank',$team_bank);
		$request->session()->put('emp_id_bank',$emp_id_bank);
		$request->session()->put('status_bank',$status_bank);
		$request->session()->put('disbured_date_from_bank',$disbured_date_from);
		$request->session()->put('disbured_date_to_bank',$disbured_date_to);
		$request->session()->put('application_date_from_bank',$application_date_from_bank);
		$request->session()->put('application_date_to_bank',$application_date_to_bank);
		$request->session()->put('search_bank_flag','1');
		return redirect("loadBankContentsMashreqCardBank");
	}

	public function searchBankInnerCM(Request $request)
	{
		$requestParameters = $request->input();
		$ref_no_bank_cm = '';
		$application_id_bank_cm = '';
		$missing_internal_bank_cm = '';

		if(@isset($requestParameters['ref_no_bank_cm']))
		{
			$ref_no_bank_cm = @$requestParameters['ref_no_bank_cm'];
		}

		if(@isset($requestParameters['application_id_bank_cm']))
		{
			$application_id_bank_cm = @$requestParameters['application_id_bank_cm'];
		}
		
		if(isset($requestParameters['booked_flag_bank_cm']))
		{
			$booked_flag_bank_cm = $requestParameters['booked_flag_bank_cm'];
		}
		else
		{
			$booked_flag_bank_cm = '';
		}

		if(@isset($requestParameters['team_bank_cm']))
		{
			$team_bank_cm = @$requestParameters['team_bank_cm'];
		}
		else
		{
			$team_bank_cm = '';
		}

		if(@isset($requestParameters['emp_id_bank_cm']))
		{
			$emp_id_bank_cm = @$requestParameters['emp_id_bank_cm'];
		}
		else
		{
			$emp_id_bank_cm = '';
		}
		
		
		if(isset($requestParameters['status_bank_cm']))
		{
			$status_bank_cm = $requestParameters['status_bank_cm'];
		}
		else
		{
			$status_bank_cm = '';
		}

		if(isset($requestParameters['all_cda_deviation_cm']))
		{
			$all_cda_deviation_cm = $requestParameters['all_cda_deviation_cm'];
		}
		else
		{
			$all_cda_deviation_cm = '';
		}

		if(@isset($requestParameters['missing_internal_bank_cm']))
		{
			$missing_internal_bank_cm = @$requestParameters['missing_internal_bank_cm'];
		}	
		

		$application_date_from_bank_cm = $requestParameters['application_date_from_bank_cm'];
		$application_date_to_bank_cm = $requestParameters['application_date_to_bank_cm'];
		
		$request->session()->put('ref_no_bank_cm',$ref_no_bank_cm);
		$request->session()->put('missing_internal_bank_cm',$missing_internal_bank_cm);
		$request->session()->put('application_id_bank_cm',$application_id_bank_cm);
		$request->session()->put('all_cda_deviation_cm',$all_cda_deviation_cm);
		$request->session()->put('booked_flag_bank_cm',$booked_flag_bank_cm);
		$request->session()->put('team_bank_cm',$team_bank_cm);
		$request->session()->put('emp_id_bank_cm',$emp_id_bank_cm);
		$request->session()->put('status_bank_cm',$status_bank_cm);
		$request->session()->put('application_date_from_bank_cm',$application_date_from_bank_cm);
		$request->session()->put('application_date_to_bank_cm',$application_date_to_bank_cm);
		$request->session()->put('search_bank_flag_cm','1');
		return redirect("loadBankContentsMashreqCardBankCurrentMonth");
	}

	public function searchInternalInner(Request $request)
	{
		$requestParameters = $request->input();
		$remarks = '';
		$ref_no_internal = '';
		$application_id_internal = '';
		$form_status = '';
		$team_internal = '';
		$emp_id_internal = '';
		$start_date_internal ='';
		$end_date_internal ='';
		$sales_processor_internal = '';
		$missing_login_internal = '';

		if(@isset($requestParameters['ref_no_internal']))
		{
			$ref_no_internal = @$requestParameters['ref_no_internal'];
		}
		if(@isset($requestParameters['sales_processor_internal']))
		{
			$sales_processor_internal = @$requestParameters['sales_processor_internal'];
		}
		if(@isset($requestParameters['missing_login_internal']))
		{
			$missing_login_internal = @$requestParameters['missing_login_internal'];
		}

		if(@isset($requestParameters['application_id_internal']))
		{
			$application_id_internal = @$requestParameters['application_id_internal'];
		}

		if(@isset($requestParameters['remarks']))
		{
			$remarks = @$requestParameters['remarks'];
		}
		if(@isset($requestParameters['team_internal']))
		{
			$team_internal = @$requestParameters['team_internal'];
		}
		if(@isset($requestParameters['emp_id_internal']))
		{
			$emp_id_internal = @$requestParameters['emp_id_internal'];
		}
		if(isset($requestParameters['form_status']))
		{
			$form_status = @$requestParameters['form_status'];
		}
		if(isset($requestParameters['start_date_internal']))
		{
			$start_date_internal = @$requestParameters['start_date_internal'];
		}
		if(isset($requestParameters['end_date_internal']))
		{
			$end_date_internal = @$requestParameters['end_date_internal'];
		}
		
		
		$request->session()->put('ref_no_internal',$ref_no_internal);
		$request->session()->put('sales_processor_internal',$sales_processor_internal);
		$request->session()->put('missing_login_internal',$missing_login_internal);
		$request->session()->put('application_id_internal',$application_id_internal);
		$request->session()->put('remarks',$remarks);
		$request->session()->put('team_internal',$team_internal);
		$request->session()->put('emp_id_internal',$emp_id_internal);
		$request->session()->put('form_status',$form_status);
		$request->session()->put('start_date_internal',$start_date_internal);
		$request->session()->put('end_date_internal',$end_date_internal);
		$request->session()->put('search_internal_flag','1');

		if(isset($requestParameters['CurrentMonthFilter']) && $requestParameters['CurrentMonthFilter']!='')
		{
			$request->session()->put('CurrentMonthFilter','1');
			if($request->session()->get('start_date_internal') == '')
			{
			$request->session()->put('start_date_internal',date('01-m-Y'));
			}
			if($request->session()->get('end_date_internal') == '')
			{
			$request->session()->put('end_date_internal',date('d-m-Y'));
			}
		}
		else
		{
			$request->session()->put('CurrentMonthFilter','');
		}
		
		return redirect("loadBankContentsMashreqCard/1");
	}

	public function searchMasterInner(Request $request)
	{
		$requestParameters = $request->input();
		$master_remarks = '';
		$app_decision = '';
		$application_status = '';
		$cda_descision = '';
		$seller_id = '';
		$start_date_master = '';
		$end_date_master = '';
		$team_master = '';
		$emp_id_master = '';
		$ref_no_master = '';
		$applicationid_master = '';
		$sales_processor_master = '';

		if(@isset($requestParameters['sales_processor_master']))
		{
			$sales_processor_master = @$requestParameters['sales_processor_master'];
		}

		if(@isset($requestParameters['ref_no_master']))
		{
			$ref_no_master = @$requestParameters['ref_no_master'];
		}

		if(@isset($requestParameters['applicationid_master']))
		{
			$applicationid_master = @$requestParameters['applicationid_master'];
		}

		if(@isset($requestParameters['team_master']))
		{
			$team_master = @$requestParameters['team_master'];
		}

		if(@isset($requestParameters['emp_id_master']))
		{
			$emp_id_master = @$requestParameters['emp_id_master'];
		}
		
		if(@isset($requestParameters['master_remarks']))
		{
			$master_remarks = @$requestParameters['master_remarks'];
		}		
		if(isset($requestParameters['app_decision']))
		{
			$app_decision = @$requestParameters['app_decision'];
		}
		if(isset($requestParameters['application_status']))
		{
			$application_status = @$requestParameters['application_status'];
		}
		if(isset($requestParameters['cda_descision']))
		{
			$cda_descision = @$requestParameters['cda_descision'];
		}
		if(isset($requestParameters['seller_id']))
		{
			$seller_id = @$requestParameters['seller_id'];
		}
		if(isset($requestParameters['start_date_master']))
		{
			$start_date_master = @$requestParameters['start_date_master'];
		}
		if(isset($requestParameters['end_date_master']))
		{
			$end_date_master = @$requestParameters['end_date_master'];
		}
		
		$request->session()->put('team_master',$team_master);
		$request->session()->put('sales_processor_master',$sales_processor_master);
		$request->session()->put('ref_no_master',$ref_no_master);
		$request->session()->put('applicationid_master',$applicationid_master);
		$request->session()->put('emp_id_master',$emp_id_master);
		$request->session()->put('master_remarks',$master_remarks);
		$request->session()->put('app_decision',$app_decision);
		$request->session()->put('application_status',$application_status);
		$request->session()->put('cda_descision',$cda_descision);
		$request->session()->put('seller_id',$seller_id);
		$request->session()->put('start_date_master',$start_date_master);
		$request->session()->put('end_date_master',$end_date_master);
		return redirect("loadBankContentsMashreqCardMaster");
	}

	public function searchLoginInner(Request $request)
	{
		$requestParameters = $request->input();
		
		$app_decision = '';
		$application_status = '';
		$cda_descision = '';
		$booked_flag = '';
		$bureau_segmentation = '';
		$employee_category_desc = '';
		$mrs_score = '';
		$bureau_score = '';
		$applicationid = '';
		$start_date_login = '';
		$end_date_login = '';
		$team_login = '';
		$emp_id_login = '';
		$ref_no_login = '';
		$cif_login = '';
		$sales_processor_login = '';

		if(@isset($requestParameters['ref_no_login']))
		{
			$ref_no_login = @$requestParameters['ref_no_login'];
		}
		if(@isset($requestParameters['sales_processor_login']))
		{
			$sales_processor_login = @$requestParameters['sales_processor_login'];
		}

		if(@isset($requestParameters['cif_login']))
		{
			$cif_login = @$requestParameters['cif_login'];
		}

		if(isset($requestParameters['team_login']))
		{
			$team_login = @$requestParameters['team_login'];
		}

		if(isset($requestParameters['emp_id_login']))
		{
			$emp_id_login = @$requestParameters['emp_id_login'];
		}	
				
		if(isset($requestParameters['app_decision']))
		{
			$app_decision = @$requestParameters['app_decision'];
		}
		if(isset($requestParameters['application_status']))
		{
			$application_status = @$requestParameters['application_status'];
		}
		if(isset($requestParameters['cda_descision']))
		{
			$cda_descision = @$requestParameters['cda_descision'];
		}
		if(isset($requestParameters['booked_flag']))
		{
			$booked_flag = @$requestParameters['booked_flag'];
		}
		if(isset($requestParameters['bureau_segmentation']))
		{
			$bureau_segmentation = @$requestParameters['bureau_segmentation'];
		}
		if(isset($requestParameters['employee_category_desc']))
		{
			$employee_category_desc = @$requestParameters['employee_category_desc'];
		}

		if(@isset($requestParameters['mrs_score']))
		{
			$mrs_score = @$requestParameters['mrs_score'];
		}
		if(@isset($requestParameters['bureau_score']))
		{
			$bureau_score = @$requestParameters['bureau_score'];
		}
		if(@isset($requestParameters['applicationid']))
		{
			$applicationid = @$requestParameters['applicationid'];
		}
		
		if(isset($requestParameters['start_date_login']))
		{
			$start_date_login = @$requestParameters['start_date_login'];
		}
		if(isset($requestParameters['end_date_login']))
		{
			$end_date_login = @$requestParameters['end_date_login'];
		}
		
		$request->session()->put('team_login',$team_login);
		$request->session()->put('sales_processor_login',$sales_processor_login);
		$request->session()->put('ref_no_login',$ref_no_login);
		$request->session()->put('cif_login',$cif_login);
		$request->session()->put('emp_id_login',$emp_id_login);
		$request->session()->put('app_decision',$app_decision);
		$request->session()->put('application_status',$application_status);
		$request->session()->put('cda_descision',$cda_descision);
		$request->session()->put('booked_flag',$booked_flag);
		$request->session()->put('bureau_segmentation',$bureau_segmentation);
		$request->session()->put('employee_category_desc',$employee_category_desc);
		$request->session()->put('mrs_score',$mrs_score);
		$request->session()->put('bureau_score',$bureau_score);
		$request->session()->put('applicationid',$applicationid);
		$request->session()->put('start_date_login',$start_date_login);
		$request->session()->put('end_date_login',$end_date_login);
		$request->session()->put('search_login_flag','1');
		return redirect("loadBankContentsMashreqCardLogin");
	}

	public function searchLoginInnerCM(Request $request)
	{
		$requestParameters = $request->input();
		
		$app_decision_cm = '';
		$application_status_cm = '';
		$cda_descision_cm = '';
		$booked_flag_cm = '';
		$bureau_segmentation_cm = '';
		$employee_category_desc_cm = '';
		$mrs_score_cm = '';
		$bureau_score_cm = '';
		$applicationid_cm = '';
		$start_date_login_cm = '';
		$end_date_login_cm = '';
		$team_login_cm = '';
		$emp_id_login_cm = '';
		$ref_no_login_cm = '';
		$cif_login_cm = '';
		$sales_processor_login_cm = '';

		if(@isset($requestParameters['ref_no_login_cm']))
		{
			$ref_no_login_cm = @$requestParameters['ref_no_login_cm'];
		}
		if(@isset($requestParameters['sales_processor_login_cm']))
		{
			$sales_processor_login_cm = @$requestParameters['sales_processor_login_cm'];
		}

		if(@isset($requestParameters['cif_login_cm']))
		{
			$cif_login_cm = @$requestParameters['cif_login_cm'];
		}

		if(isset($requestParameters['team_login_cm']))
		{
			$team_login_cm = @$requestParameters['team_login_cm'];
		}

		if(isset($requestParameters['emp_id_login_cm']))
		{
			$emp_id_login_cm = @$requestParameters['emp_id_login_cm'];
		}	
				
		if(isset($requestParameters['app_decision_cm']))
		{
			$app_decision_cm = @$requestParameters['app_decision_cm'];
		}
		if(isset($requestParameters['application_status_cm']))
		{
			$application_status_cm = @$requestParameters['application_status_cm'];
		}
		if(isset($requestParameters['cda_descision_cm']))
		{
			$cda_descision_cm = @$requestParameters['cda_descision_cm'];
		}
		if(isset($requestParameters['booked_flag_cm']))
		{
			$booked_flag_cm = @$requestParameters['booked_flag_cm'];
		}
		if(isset($requestParameters['bureau_segmentation_cm']))
		{
			$bureau_segmentation_cm = @$requestParameters['bureau_segmentation_cm'];
		}
		if(isset($requestParameters['employee_category_desc_cm']))
		{
			$employee_category_desc_cm = @$requestParameters['employee_category_desc_cm'];
		}

		if(@isset($requestParameters['mrs_score_cm']))
		{
			$mrs_score_cm = @$requestParameters['mrs_score_cm'];
		}
		if(@isset($requestParameters['bureau_score_cm']))
		{
			$bureau_score_cm = @$requestParameters['bureau_score_cm'];
		}
		if(@isset($requestParameters['applicationid_cm']))
		{
			$applicationid_cm = @$requestParameters['applicationid_cm'];
		}
		
		if(isset($requestParameters['start_date_login_cm']))
		{
			$start_date_login_cm = @$requestParameters['start_date_login_cm'];
		}
		if(isset($requestParameters['end_date_login_cm']))
		{
			$end_date_login_cm = @$requestParameters['end_date_login_cm'];
		}
		
		$request->session()->put('team_login_cm',$team_login_cm);
		$request->session()->put('sales_processor_login_cm',$sales_processor_login_cm);
		$request->session()->put('ref_no_login_cm',$ref_no_login_cm);
		$request->session()->put('cif_login_cm',$cif_login_cm);
		$request->session()->put('emp_id_login_cm',$emp_id_login_cm);
		$request->session()->put('app_decision_cm',$app_decision_cm);
		$request->session()->put('application_status_cm',$application_status_cm);
		$request->session()->put('cda_descision_cm',$cda_descision_cm);
		$request->session()->put('booked_flag_cm',$booked_flag_cm);
		$request->session()->put('bureau_segmentation_cm',$bureau_segmentation_cm);
		$request->session()->put('employee_category_desc_cm',$employee_category_desc_cm);
		$request->session()->put('mrs_score_cm',$mrs_score_cm);
		$request->session()->put('bureau_score_cm',$bureau_score_cm);
		$request->session()->put('applicationid_cm',$applicationid_cm);
		$request->session()->put('start_date_login_cm',$start_date_login_cm);
		$request->session()->put('end_date_login_cm',$end_date_login_cm);
		$request->session()->put('search_login_flag_cm','1');
		return redirect("loadBankContentsMashreqCardLoginCurrentMonth");
	}

	public function searchBookingInner(Request $request)
	{	
		$requestParameters = $request->input();

		$start_date_booking = '';
		$end_date_booking = '';
		$team_booking = '';
		$emp_id_booking = '';
		$ref_no_booking = '';
		$card_status_booking = '';
		$application_id_booking = '';
		$cif_cis_number = '';
		$sellerid = '';
		$missing_internal_booking ='';

		if(@isset($requestParameters['ref_no_booking']))
		{
			$ref_no_booking = @$requestParameters['ref_no_booking'];
		}
		if(@isset($requestParameters['card_status_booking']))
		{
			$card_status_booking = @$requestParameters['card_status_booking'];
		}
		if(@isset($requestParameters['missing_internal_booking']))
		{
			$missing_internal_booking = @$requestParameters['missing_internal_booking'];
		}
		if(@isset($requestParameters['application_id_booking']))
		{
			$application_id_booking = @$requestParameters['application_id_booking'];
		}
		if(@isset($requestParameters['cif_cis_number']))
		{
			$cif_cis_number = @$requestParameters['cif_cis_number'];
		}
		if(@isset($requestParameters['sellerid']))
		{
			$sellerid = @$requestParameters['sellerid'];
		}

		if(isset($requestParameters['team_booking']))
		{
			$team_booking = @$requestParameters['team_booking'];
		}

		if(isset($requestParameters['emp_id_booking']))
		{
			$emp_id_booking = @$requestParameters['emp_id_booking'];
		}

		if(isset($requestParameters['start_date_booking']))
		{
			$start_date_booking = @$requestParameters['start_date_booking'];
		}
		if(isset($requestParameters['end_date_booking']))
		{
			$end_date_booking = @$requestParameters['end_date_booking'];
		}
		
		$request->session()->put('team_booking',$team_booking);
		$request->session()->put('missing_internal_booking',$missing_internal_booking);
		$request->session()->put('ref_no_booking',$ref_no_booking);
		$request->session()->put('card_status_booking',$card_status_booking);
		$request->session()->put('application_id_booking',$application_id_booking);
		$request->session()->put('emp_id_booking',$emp_id_booking);
		$request->session()->put('start_date_booking',$start_date_booking);
		$request->session()->put('end_date_booking',$end_date_booking);
		$request->session()->put('cif_cis_number',$cif_cis_number);
		$request->session()->put('sellerid',$sellerid);
		$request->session()->put('search_booking_flag','1');
		return redirect("loadBankContentsMashreqCardBooking");
	}

	public function searchBookingInnerCM(Request $request)
	{	
		$requestParameters = $request->input();

		$start_date_booking_cm = '';
		$end_date_booking_cm = '';
		$team_booking_cm = '';
		$emp_id_booking_cm = '';
		$ref_no_booking_cm = '';
		$card_status_booking_cm = '';
		$application_id_booking_cm = '';
		$cif_cis_number_cm = '';
		$sellerid_cm = '';
		$missing_internal_booking_cm ='';

		if(@isset($requestParameters['ref_no_booking_cm']))
		{
			$ref_no_booking_cm = @$requestParameters['ref_no_booking_cm'];
		}
		if(@isset($requestParameters['card_status_booking_cm']))
		{
			$card_status_booking_cm = @$requestParameters['card_status_booking_cm'];
		}
		if(@isset($requestParameters['missing_internal_booking_cm']))
		{
			$missing_internal_booking_cm = @$requestParameters['missing_internal_booking_cm'];
		}
		if(@isset($requestParameters['application_id_booking_cm']))
		{
			$application_id_booking_cm = @$requestParameters['application_id_booking_cm'];
		}
		if(@isset($requestParameters['cif_cis_number_cm']))
		{
			$cif_cis_number_cm = @$requestParameters['cif_cis_number_cm'];
		}
		if(@isset($requestParameters['sellerid_cm']))
		{
			$sellerid_cm = @$requestParameters['sellerid_cm'];
		}

		if(isset($requestParameters['team_booking_cm']))
		{
			$team_booking_cm = @$requestParameters['team_booking_cm'];
		}

		if(isset($requestParameters['emp_id_booking_cm']))
		{
			$emp_id_booking_cm = @$requestParameters['emp_id_booking_cm'];
		}

		if(isset($requestParameters['start_date_booking_cm']))
		{
			$start_date_booking_cm = @$requestParameters['start_date_booking_cm'];
		}
		if(isset($requestParameters['end_date_booking_cm']))
		{
			$end_date_booking_cm = @$requestParameters['end_date_booking_cm'];
		}
		
		$request->session()->put('team_booking_cm',$team_booking_cm);
		$request->session()->put('missing_internal_booking_cm',$missing_internal_booking_cm);
		$request->session()->put('ref_no_booking_cm',$ref_no_booking_cm);
		$request->session()->put('card_status_booking_cm',$card_status_booking_cm);
		$request->session()->put('application_id_booking_cm',$application_id_booking_cm);
		$request->session()->put('emp_id_booking_cm',$emp_id_booking_cm);
		$request->session()->put('start_date_booking_cm',$start_date_booking_cm);
		$request->session()->put('end_date_booking_cm',$end_date_booking_cm);
		$request->session()->put('cif_cis_number_cm',$cif_cis_number_cm);
		$request->session()->put('sellerid_cm',$sellerid_cm);
		$request->session()->put('search_booking_flag_cm','1');
		return redirect("loadBankContentsMashreqCardBookingCurrentMonth");
	}

	public function searchMTDInner(Request $request)
	{	
		$requestParameters = $request->input();

		$start_date_mtd = '';
		$end_date_mtd = '';
		$team_mtd = '';
		$emp_id_mtd = '';
		$ref_no_mtd = '';
		$application_id_mtd = '';

		if(@isset($requestParameters['ref_no_mtd']))
		{
			$ref_no_mtd = @$requestParameters['ref_no_mtd'];
		}

		if(@isset($requestParameters['application_id_mtd']))
		{
			$application_id_mtd = @$requestParameters['application_id_mtd'];
		}

		if(isset($requestParameters['team_mtd']))
		{
			$team_mtd = @$requestParameters['team_mtd'];
		}

		if(isset($requestParameters['emp_id_mtd']))
		{
			$emp_id_mtd = @$requestParameters['emp_id_mtd'];
		}

		if(isset($requestParameters['start_date_mtd']))
		{
			$start_date_mtd = @$requestParameters['start_date_mtd'];
		}
		if(isset($requestParameters['end_date_mtd']))
		{
			$end_date_mtd = @$requestParameters['end_date_mtd'];
		}

		$request->session()->put('team_mtd',$team_mtd);
		$request->session()->put('ref_no_mtd',$ref_no_mtd);
		$request->session()->put('application_id_mtd',$application_id_mtd);
		$request->session()->put('emp_id_mtd',$emp_id_mtd);
		$request->session()->put('start_date_mtd',$start_date_mtd);
		$request->session()->put('end_date_mtd',$end_date_mtd);
		return redirect("loadBankContentsMashreqCardMTD");
	}

	public function searchMTDInnerCM(Request $request)
	{	
		$requestParameters = $request->input();

		$start_date_mtd_cm = '';
		$end_date_mtd_cm = '';
		$team_mtd_cm = '';
		$emp_id_mtd_cm = '';
		$ref_no_mtd_cm = '';
		$application_id_mtd_cm = '';

		if(@isset($requestParameters['ref_no_mtd_cm']))
		{
			$ref_no_mtd_cm = @$requestParameters['ref_no_mtd_cm'];
		}

		if(@isset($requestParameters['application_id_mtd_cm']))
		{
			$application_id_mtd_cm = @$requestParameters['application_id_mtd_cm'];
		}

		if(isset($requestParameters['team_mtd_cm']))
		{
			$team_mtd_cm = @$requestParameters['team_mtd_cm'];
		}

		if(isset($requestParameters['emp_id_mtd_cm']))
		{
			$emp_id_mtd_cm = @$requestParameters['emp_id_mtd_cm'];
		}

		if(isset($requestParameters['start_date_mtd_cm']))
		{
			$start_date_mtd_cm = @$requestParameters['start_date_mtd_cm'];
		}
		if(isset($requestParameters['end_date_mtd_cm']))
		{
			$end_date_mtd_cm = @$requestParameters['end_date_mtd_cm'];
		}

		$request->session()->put('team_mtd_cm',$team_mtd_cm);
		$request->session()->put('ref_no_mtd_cm',$ref_no_mtd_cm);
		$request->session()->put('application_id_mtd_cm',$application_id_mtd_cm);
		$request->session()->put('emp_id_mtd_cm',$emp_id_mtd_cm);
		$request->session()->put('start_date_mtd_cm',$start_date_mtd_cm);
		$request->session()->put('end_date_mtd_cm',$end_date_mtd_cm);
		return redirect("loadBankContentsMashreqCardMTDCurrentMonth");
	}

	public function searchFinalMTDInner(Request $request)
	{	
		$requestParameters = $request->input();

		$start_date_Finalmtd = '';
		$end_date_Finalmtd = '';
		$team_Finalmtd = '';
		$emp_id_Finalmtd = '';
		$ref_no_Finalmtd = '';
		$application_id_Finalmtd = '';

		if(@isset($requestParameters['ref_no_Finalmtd']))
		{
			$ref_no_Finalmtd = @$requestParameters['ref_no_Finalmtd'];
		}

		if(@isset($requestParameters['application_id_Finalmtd']))
		{
			$application_id_Finalmtd = @$requestParameters['application_id_Finalmtd'];
		}

		if(isset($requestParameters['team_Finalmtd']))
		{
			$team_Finalmtd = @$requestParameters['team_Finalmtd'];
		}

		if(isset($requestParameters['emp_id_Finalmtd']))
		{
			$emp_id_Finalmtd = @$requestParameters['emp_id_Finalmtd'];
		}

		if(isset($requestParameters['emp_id_mtd']))
		{
			$emp_id_mtd = @$requestParameters['emp_id_mtd'];
		}

		if(isset($requestParameters['start_date_Finalmtd']))
		{
			$start_date_Finalmtd = @$requestParameters['start_date_Finalmtd'];
		}
		if(isset($requestParameters['end_date_Finalmtd']))
		{
			$end_date_Finalmtd = @$requestParameters['end_date_Finalmtd'];
		}

		$request->session()->put('emp_id_Finalmtd',$emp_id_Finalmtd);
		$request->session()->put('team_Finalmtd',$team_Finalmtd);
		$request->session()->put('ref_no_Finalmtd',$ref_no_Finalmtd);
		$request->session()->put('application_id_Finalmtd',$application_id_Finalmtd);		
		$request->session()->put('start_date_Finalmtd',$start_date_Finalmtd);
		$request->session()->put('end_date_Finalmtd',$end_date_Finalmtd);
		$request->session()->put('search_final_mtd_flag','1');
		return redirect("loadBankContentsMashreqCardFinalMTD");
	}

	public function searchreProcessInner(Request $request)
	{	
		$requestParameters = $request->input();

		$scheme_group_reProcess = '';
		$scheme_reProcess = '';
		$statistics_reProcess = '';
		$start_date_reProcess = '';
		$end_date_reProcess = '';
		$eligibility_date_minus = '';
		$cif_reProcess = '';
		$ref_no_reProcess = '';
		$team_reProcess = '';

		if(@isset($requestParameters['cif_reProcess']))
		{
			$cif_reProcess = @$requestParameters['cif_reProcess'];
		}
		if(@isset($requestParameters['ref_no_reProcess']))
		{
			$ref_no_reProcess = @$requestParameters['ref_no_reProcess'];
		}
		if(@isset($requestParameters['team_reProcess']))
		{
			$team_reProcess = @$requestParameters['team_reProcess'];
		}
		if(@isset($requestParameters['scheme_group_reProcess']))
		{
			$scheme_group_reProcess = @$requestParameters['scheme_group_reProcess'];
		}
		if(@isset($requestParameters['scheme_reProcess']))
		{
			$scheme_reProcess = @$requestParameters['scheme_reProcess'];
		}
		if(@isset($requestParameters['statistics_reProcess']))
		{
			$statistics_reProcess = @$requestParameters['statistics_reProcess'];
		}
		if(isset($requestParameters['start_date_reProcess']))
		{
			$start_date_reProcess = @$requestParameters['start_date_reProcess'];
		}
		if(isset($requestParameters['end_date_reProcess']))
		{
			$end_date_reProcess = @$requestParameters['end_date_reProcess'];
		}
		if(isset($requestParameters['eligibility_date_minus']))
		{
			$eligibility_date_minus = @$requestParameters['eligibility_date_minus'];
		}

		$request->session()->put('scheme_group_reProcess',$scheme_group_reProcess);
		$request->session()->put('team_reProcess',$team_reProcess);
		$request->session()->put('cif_reProcess',$cif_reProcess);
		$request->session()->put('ref_no_reProcess',$ref_no_reProcess);
		$request->session()->put('scheme_reProcess',$scheme_reProcess);
		$request->session()->put('statistics_reProcess',$statistics_reProcess);
		$request->session()->put('start_date_reProcess',$start_date_reProcess);
		$request->session()->put('end_date_reProcess',$end_date_reProcess);
		$request->session()->put('eligibility_date_minus',$eligibility_date_minus);
		return redirect("load_reProcess");
	}
	
	
	public function resetBankInner(Request $request)
	{
		$request->session()->put('team_bank','');
		$request->session()->put('ref_no_bank','');
		$request->session()->put('missing_internal_bank','');
		$request->session()->put('application_id_bank','');
		$request->session()->put('emp_id_bank','');
		$request->session()->put('all_cda_deviation','');		
		$request->session()->put('booked_flag_bank','');
		$request->session()->put('status_bank','');
		$request->session()->put('disbured_date_from_bank','');
		$request->session()->put('disbured_date_to_bank','');
		$request->session()->put('application_date_from_bank','');
		$request->session()->put('application_date_to_bank','');
		$request->session()->put('start_date_bank','');
		$request->session()->put('end_date_bank','');
		$request->session()->put('search_bank_flag','');
		return redirect("loadBankContentsMashreqCardBank");
	}

	public function resetBankInnerCM(Request $request)
	{
		$request->session()->put('team_bank_cm','');
		$request->session()->put('ref_no_bank_cm','');
		$request->session()->put('missing_internal_bank_cm','');
		$request->session()->put('application_id_bank_cm','');
		$request->session()->put('emp_id_bank_cm','');
		$request->session()->put('all_cda_deviation_cm','');		
		$request->session()->put('booked_flag_bank_cm','');
		$request->session()->put('status_bank_cm','');
		$request->session()->put('application_date_from_bank_cm','');
		$request->session()->put('application_date_to_bank_cm','');
		$request->session()->put('start_date_bank_cm','');
		$request->session()->put('end_date_bank_cm','');
		$request->session()->put('search_bank_flag_cm','');
		return redirect("loadBankContentsMashreqCardBankCurrentMonth");
	}

	public function resetBookingInner(Request $request)
	{	
		$request->session()->put('ref_no_booking','');
		$request->session()->put('card_status_booking','');
		$request->session()->put('application_id_booking','');
		$request->session()->put('cif_cis_number','');
		$request->session()->put('sellerid','');
		$request->session()->put('team_booking','');
		$request->session()->put('emp_id_booking','');
		$request->session()->put('start_date_booking','');
		$request->session()->put('end_date_booking','');
		$request->session()->put('missing_internal_booking','');
		$request->session()->put('search_booking_flag','');
		return redirect("loadBankContentsMashreqCardBooking");
	}

	public function resetBookingInnerCM(Request $request)
	{	
		$request->session()->put('ref_no_booking_cm','');
		$request->session()->put('card_status_booking_cm','');
		$request->session()->put('application_id_booking_cm','');
		$request->session()->put('cif_cis_number_cm','');
		$request->session()->put('sellerid_cm','');
		$request->session()->put('team_booking_cm','');
		$request->session()->put('emp_id_booking_cm','');
		$request->session()->put('start_date_booking_cm','');
		$request->session()->put('end_date_booking_cm','');
		$request->session()->put('missing_internal_booking_cm','');
		$request->session()->put('search_booking_flag_cm','');
		return redirect("loadBankContentsMashreqCardBookingCurrentMonth");
	}

	public function resetMTDInner(Request $request)
	{
		$request->session()->put('ref_no_mtd','');
		$request->session()->put('application_id_mtd','');
		$request->session()->put('team_mtd','');
		$request->session()->put('emp_id_mtd','');
		$request->session()->put('start_date_mtd','');
		$request->session()->put('end_date_mtd','');
		return redirect("loadBankContentsMashreqCardMTD");
	}

	public function resetMTDInnerCM(Request $request)
	{
		$request->session()->put('ref_no_mtd_cm','');
		$request->session()->put('application_id_mtd_cm','');
		$request->session()->put('team_mtd_cm','');
		$request->session()->put('emp_id_mtd_cm','');
		$request->session()->put('start_date_mtd_cm','');
		$request->session()->put('end_date_mtd_cm','');
		return redirect("loadBankContentsMashreqCardMTDCurrentMonth");
	}

	public function resetFinalMTDInner(Request $request)
	{
		$request->session()->put('ref_no_Finalmtd','');
		$request->session()->put('application_id_Finalmtd','');
		$request->session()->put('team_Finalmtd','');
		$request->session()->put('emp_id_Finalmtd','');
		$request->session()->put('start_date_Finalmtd','');
		$request->session()->put('end_date_Finalmtd','');
		$request->session()->put('search_final_mtd_flag');
		return redirect("loadBankContentsMashreqCardFinalMTD");
	}

	public function resetInternalInner(Request $request)
	{
		$request->session()->put('ref_no_internal','');
		$request->session()->put('ref_no_internal_bulk','');
		$request->session()->put('application_id_internal','');
		$request->session()->put('remarks','');
		$request->session()->put('form_status','');
		$request->session()->put('emp_id_internal','');
		$request->session()->put('sales_processor_internal','');
		$request->session()->put('missing_login_internal','');
		$request->session()->put('team_internal','');
		$request->session()->put('start_date_internal','');
		$request->session()->put('end_date_internal','');
		$request->session()->put('search_internal_flag','');
		$request->session()->put('CurrentMonthFilter','');
		return redirect("loadBankContentsMashreqCard/1");
	}

	public function resetMasterInner(Request $request)
	{
		$request->session()->put('ref_no_master','');
		$request->session()->put('applicationid_master','');
		$request->session()->put('master_remarks','');
		$request->session()->put('team_master','');
		$request->session()->put('emp_id_master','');
		$request->session()->put('sales_processor_master','');
		$request->session()->put('app_decision','');
		$request->session()->put('application_status','');
		$request->session()->put('cda_descision','');
		$request->session()->put('seller_id','');
		$request->session()->put('start_date_master','');
		$request->session()->put('end_date_master','');
		return redirect("loadBankContentsMashreqCardMaster");
	}

	public function resetLoginInner(Request $request)
	{	
		$request->session()->put('ref_no_login','');
		$request->session()->put('cif_login','');
		$request->session()->put('team_login','');
		$request->session()->put('emp_id_login','');
		$request->session()->put('sales_processor_login','');
		$request->session()->put('app_decision','');
		$request->session()->put('application_status','');
		$request->session()->put('cda_descision','');
		$request->session()->put('booked_flag','');
		$request->session()->put('bureau_segmentation','');
		$request->session()->put('employee_category_desc','');
		$request->session()->put('mrs_score','');
		$request->session()->put('bureau_score','');
		$request->session()->put('applicationid','');
		$request->session()->put('start_date_login','');
		$request->session()->put('end_date_login','');
		$request->session()->put('search_login_flag','');
		return redirect("loadBankContentsMashreqCardLogin");
	}

	public function resetLoginInnerCM(Request $request)
	{

		$request->session()->put('team_login_cm','');
		$request->session()->put('sales_processor_login_cm','');
		$request->session()->put('ref_no_login_cm','');
		$request->session()->put('cif_login_cm','');
		$request->session()->put('emp_id_login_cm','');
		$request->session()->put('app_decision_cm','');
		$request->session()->put('application_status_cm','');
		$request->session()->put('cda_descision_cm','');
		$request->session()->put('booked_flag_cm','');
		$request->session()->put('bureau_segmentation_cm','');
		$request->session()->put('employee_category_desc_cm','');
		$request->session()->put('mrs_score_cm','');
		$request->session()->put('bureau_score_cm','');
		$request->session()->put('applicationid_cm','');
		$request->session()->put('start_date_login_cm','');
		$request->session()->put('end_date_login_cm','');
		$request->session()->put('search_login_flag_cm','');
		return redirect("loadBankContentsMashreqCardLoginCurrentMonth");
	}

	public function resetreProcessInner(Request $request)
	{	
		$request->session()->put('scheme_group_reProcess','');
		$request->session()->put('team_reProcess','');
		$request->session()->put('cif_reProcess','');
		$request->session()->put('ref_no_reProcess','');
		$request->session()->put('scheme_reProcess','');
		$request->session()->put('statistics_reProcess','');
		$request->session()->put('start_date_reProcess','');
		$request->session()->put('end_date_reProcess','');
		$request->session()->put('eligibility_date_minus','');
		return redirect("load_reProcess");
	}


	
	
	
	public function departmentFormEntry($form_id=NULL)
    {
		
		  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();
		  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->get(['form_section']);

		  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("Attribute/departmentFormEntry",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
    }

	public function addDepartmentFormData($form_id=NULL)
    {
		  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();
		  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		  $Employee_details = Employee_details::where('offline_status','1')->orderby('first_name','ASC')->get();
        
		  return view("Attribute/addDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
    }

	public function editDepartmentFormData($parent_id=NULL,$form_id=NULL)
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

		  $Employee_details = Employee_details::where('offline_status','1')->orderby('first_name','ASC')->get();
        
		  return view("Attribute/editDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
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

		  $Employee_details = Employee_details::where('offline_status','1')->orderby('first_name','ASC')->get();
        
		  return view("Attribute/viewDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
    }

	public function departmentFormDataEdit($parent_id=NULL,$form_id=NULL)
    {
		  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->get(['form_section']);

		  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		  $departmentFormParentDetails = DB::table('department_form_parent_entry')->where('id', $parent_id)->first();

		  $departmentFormChildDetails = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		  $Employee_details = Employee_details::orderby('first_name','ASC')->get();
        
		  return view("Attribute/departmentFormDataEdit",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
    }

	public function addDepartmentFormEntryPost(Request $req)
    {			
			$entry_obj = new DepartmentFormEntry();	
			
			$user_id = $req->session()->get('EmployeeId');

			$entry_obj->user_id = $user_id;
			$entry_obj->form_id = $req->input('form_id');
			$entry_obj->form_title = $req->input('form_title');
			/*$entry_obj->application_id = $req->input('application_id');
			$entry_obj->submission_date = $req->input('submission_date');
			$entry_obj->team = $req->input('team');
			$emp_id_exp = explode('~',$req->input('emp_id'));
			$emp_id = $emp_id_exp[0];
			$emp_name = $emp_id_exp[1];
			$entry_obj->emp_id = $emp_id;
			$entry_obj->emp_name = $emp_name;*/
			//$entry_obj->status = $req->input('status');
            $entry_obj->save();
			$parent_id = $entry_obj->id;

			$postData = $req->input();
			$attribute_value = $postData['attribute_value'];

			
		   
			$application_id = '';		
			$submission_date = '';
			$ref_no = '';
			$team = '';
			$emp_id = '';
			$emp_name = '';
			foreach($attribute_value as $k=>$v)
			{
				if($k=='application_id')
				{
					$application_id = $v;
				}
				if($k=='ref_no')
				{
					$ref_no = $v;					
					$check = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->get();
					if(count($check)>0)
					{	
						$req->session()->flash('message','Record already exists with this Ref. No.');
						return redirect('departmentFormData/'.$entry_obj->form_id);
						//$delete1 = DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->delete();
						//$delete2 = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->delete();				
						
					}
				}
				if($k=='submission_date')
				{
					$submission_date = date('Y-m-d',strtotime($v));
				}
				if($k=='team')
				{
					$team = $v;
				}
				if($k=='form_status')
				{
					$form_status = $v;
				}
				if($k=='remarks')
				{
					$remarks = $v;
				}
				if($k=='emp_id')
				{					
					$emp_id_exp = explode('~',$v);
					$emp_id = @$emp_id_exp[0];
					$emp_name = @$emp_id_exp[1];	

					$values_emp = array('parent_id' => $parent_id,'form_id' => $req->input('form_id'),'attribute_code' => 'emp_name','attribute_value' => $emp_name);
					DB::table('department_form_child_entry')->insert($values_emp);
				}
				$values = array('parent_id' => $parent_id,'form_id' => $req->input('form_id'),'attribute_code' => $k,'attribute_value' => $v);
				DB::table('department_form_child_entry')->insert($values);
				
			}
			if(count($_FILES)>0)
		   {
			   $target_path =public_path('uploads/formFiles/');
			   $file_attribute_value = $_FILES['attribute_value'];  
			  
			   foreach($file_attribute_value as $k=>$v)
				{
				   if($k=='name')
					{
					   foreach($v as $file_key=>$file_val)
						{
						   //echo $file_key.'=='.$file_val;						   
						   //echo '<br>';
						   $tmp_name = $file_attribute_value['tmp_name'][$file_key];
						   
						   $ext = explode('.', basename( $file_val));
						   $filename = "F_".md5(uniqid()) . "." . $ext[count($ext)-1];
						   $target_path = $target_path.$filename; 						

							if(move_uploaded_file($tmp_name, $target_path)) 
							{
								$file_values = array('attribute_value' => $filename);
								DB::table('department_form_child_entry')->where('form_id', $req->input('form_id'))->where('attribute_code', $file_key)->where('parent_id', $parent_id)->update($file_values);
								
							}						

						}					   
					}				  
					
				}
			}
			DepartmentFormEntry::where('id', $parent_id)
				->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => str_replace("'","",$attribute_value['customer_mobile']),'application_id' => $application_id, 'ref_no' => $ref_no, 'submission_date' => $submission_date,'team' => $team,'form_status' => $form_status,'remarks' => addslashes(str_replace("'","`",$remarks)),'emp_id' => $emp_id,'emp_name' => $emp_name
				]);

			/*MashreqLoginMIS::where('ref_no', $ref_no)
				->update(['team' => $team,'emp_id' => $emp_id,'emp_name' => $emp_name]);

			MashreqBookingMIS::where('ref_no', $ref_no)
				->update(['team' => $team,'emp_id' => $emp_id,'emp_name' => $emp_name]);

			MashreqBankMIS::where('application_ref_no', $ref_no)
				->update(['team' => $team,'emp_id' => $emp_id,'emp_name' => $emp_name]);

			MashreqMTDMIS::where('ref_no', $ref_no)
				->update(['team' => $team,'emp_id' => $emp_id,'emp_name' => $emp_name]);*/
           
            $req->session()->flash('message','Record added Successfully.');
            return redirect('departmentFormData/'.$entry_obj->form_id);
    }

	public function editDepartmentFormEntryPost(Request $req)
    {	
		
			$form_id = $req->input('form_id');
			$parent_id = $req->input('parent_id');
			$form_title = $req->input('form_title');
			/*$application_id = $req->input('application_id');
			$submission_date = $req->input('submission_date');
			$team = $req->input('team');
			$emp_id_exp = explode('~',$req->input('emp_id'));
			$emp_id = $emp_id_exp[0];
			$emp_name = $emp_id_exp[1];*/
			

			$postData = $req->input();
			$attribute_value = $postData['attribute_value'];


			$application_id = '';		
			$submission_date = '';
			$ref_no = '';
			$team = '';
			$emp_id = '';
			$emp_name = '';	
			foreach($attribute_value as $k=>$v)
			{
				if($k=='application_id')
				{
					$application_id = $v;
				}
				if($k=='ref_no')
				{
					$ref_no = $v;
				}
				if($k=='submission_date')
				{
					$submission_date = date('Y-m-d',strtotime($v));
				}
				if($k=='team')
				{
					$team = $v;
				}
				if($k=='form_status')
				{
					$form_status = $v;
				}
				if($k=='remarks')
				{
					$remarks = $v;
				}
				if($k=='emp_id')
				{					
					$emp_id_exp = explode('~',$v);
					$emp_id = @$emp_id_exp[0];
					$emp_name = @$emp_id_exp[1];	
					
					$values_emp = array('attribute_value' => $emp_name);
					DB::table('department_form_child_entry')->where('form_id', $req->input('form_id'))->where('attribute_code', 'emp_name')->where('parent_id', $parent_id)->update($values_emp);
				}
				$values = array('attribute_value' => $v);
				DB::table('department_form_child_entry')->where('form_id', $req->input('form_id'))->where('attribute_code', $k)->where('parent_id', $parent_id)->update($values);
				
			}

		   if(count($_FILES)>0)
		   {
			   $target_path =public_path('uploads/formFiles/');
			   $file_attribute_value = $_FILES['attribute_value'];  
			  
			   foreach($file_attribute_value as $k=>$v)
				{
				   if($k=='name')
					{
					   foreach($v as $file_key=>$file_val)
						{
						   //echo $file_key.'=='.$file_val;						   
						   //echo '<br>';
						   $tmp_name = $file_attribute_value['tmp_name'][$file_key];
						   
						   $ext = explode('.', basename( $file_val));
						   $filename = "F_".md5(uniqid()) . "." . $ext[count($ext)-1];
						   $target_path = $target_path.$filename; 						

							if(move_uploaded_file($tmp_name, $target_path)) 
							{
								$file_values = array('attribute_value' => $filename);
								DB::table('department_form_child_entry')->where('form_id', $req->input('form_id'))->where('attribute_code', $file_key)->where('parent_id', $parent_id)->update($file_values);
								
							}						

						}					   
					}				  
					
				}
			}

			DepartmentFormEntry::where('id', $parent_id)
				->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => $attribute_value['customer_mobile'], 'emp_id'=>$emp_id, 'emp_name'=>$emp_name, 'application_id'=>$application_id, 'ref_no'=>$ref_no, 'submission_date'=>$submission_date,'form_status' => $form_status,'remarks' => addslashes(str_replace("'","`",$remarks)),'team'=>$team
				]);
           
            $req->session()->flash('message','Record updated Successfully.');
            return redirect('departmentFormData/'.$form_id);
    }

	public function departmentFormDataDelete(Request $req)
    {
		$ravi_data_obj = DepartmentFormEntry::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('departmentFormData/'.$req->form_id);
    }



	public static function getAttributeName($id=NULL)
    {
      return $attributeTypeDetails =   AttributeType::where("attribute_type_id",$id)->first();
    }

	public static function getMasterAttributeName($id=NULL)
    {
      return $attributeDetails =   MasterAttribute::where("id",$id)->first();
    }

	public static function getDepartmentName($id=NULL)
    {
      return $DepartmentNameDetails =   Department::where("id",$id)->first();
    }

	public static function getFormSection($id=NULL)
    {
		return $FormSectionDetails =   FormSection::where("id",$id)->first();
    }

	public static function getEmployeeDetails($id=NULL)
    {
		return $Employee_details =  Employee_details::where("emp_id",$id)->first();
    }

	public static function getEmployeeDetailsBySorceCode($source_code=NULL)
    {
		return $Employee_details =  Employee_details::where("source_code",$source_code)->first();
    }

	public static function getDepartmentFormAttribute($form_id=NULL)
    {
		return $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->get();
	}

	public static function getDepartment_form_attribute($form_id=NULL,$attribute_id=NULL)
    {
		return $getDepartment_form_attributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->where('attribute_id', $attribute_id)->first();
	}

	public static function getDepartment_form_child_data($form_id=NULL,$parent_id=NULL)
    {
		return $getDepartment_form_attributeDetails = DB::table('department_form_child_entry')->where('form_id', $form_id)->where('parent_id', $parent_id)->get();
	}

	public static function getLoginInfo($ref_no=NULL)
    {
		return $MashreqLoginMIS = MashreqLoginMIS::where('ref_no', $ref_no)->get();
	}

	public static function checkLoginInfo($ref_no=NULL)
    {
		return $MashreqLoginMIS = MashreqLoginMIS::where('ref_no', $ref_no)->get(['id']);
	}

	public static function getLoginInfoFirst($ref_no=NULL)
    {
		return $MashreqLoginMIS = MashreqLoginMIS::where('ref_no', $ref_no)->first();
	}

	public static function getInternalInfo($ref_no=NULL)
    {
		return $DepartmentFormEntry = DepartmentFormEntry::where('ref_no', $ref_no)->get();
	}

	public static function getrecruiterInfo($id=NULL)
    {
		return $getrecruiterInfo = DB::table('recruiter_details')->where('id', $id)->first();
	}

	public static function getrecruiterCategoryInfo($id=NULL)
    {
		return $getrecruiterCategoryInfo = DB::table('recruiter_category')->where('id', $id)->first();
	}

	public static function importCSV()
	{

		$file = public_path('uploads/formFiles/Internal.csv');
		// Open uploaded CSV file with read-only mode
            $csvFile = fopen($file, 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
			$count = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE)
			{				
				$ref_no = $line[10];
				if(trim($ref_no)=='')
				{
					continue;
				}
				$emp_id = $line[4];
				if(trim($emp_id)=='')
				{
					$whereC = " first_name LIKE '%".$line[3]."%' and dept_id='36'";
					$check1 = DB::table('employee_details')->whereRaw($whereC)->first();
					//print_r($check1);exit;
					$emp_id = @$check1->emp_id;
				}
				

				$whereRaw = " ref_no ='".$ref_no."'";
				$check = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get();

				if(count($check)>0)
				{
					$parent_id = $check[0]->id;
					$delete1 = DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->delete();
					$delete2 = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->delete();

					
					
				}
				
				$sub_date = explode('/',$line[1]); 
				if(count($sub_date)>1)
				{
					$y=$sub_date[2];
					$d=$sub_date[0];
					$m=$sub_date[1];
					
					if(strlen($sub_date[0])<2)
					{
						$d='0'.$sub_date[0];
					}
					if(strlen($sub_date[1])<2)
					{
						$m='0'.$sub_date[1];
					}
					$submission_date = $y."-".$m."-".$d;
				}
				else
				{
					$submission_date = $line[1]?$line[1]:'0000-00-00';
				}
				/*$whereref = " application_ref_no='".$line[10]."'";
				$checkRef = DB::table('mashreq_bank_mis')->whereRaw($whereref)->get();
				$application_date = @$checkRef[0]->application_date;
				$submission_date = $application_date?$application_date:'0000-00-00';*/

				
				

				$values = array('form_id' => '1','form_title' => 'Credit Card Submission Form','ref_no'=>$line[10], 'emp_name' => $line[3], 'emp_id' => $emp_id, 'team'=>ucfirst(strtolower($line[0])), 'customer_name'=>$line[5], 'customer_mobile'=>$line[6], 'form_status'=>$line[11], 'remarks'=>addslashes(str_replace("'","`",$line[12])), 'submission_date'=>$submission_date);
				//print_r($values);exit;


				$parent_id = DB::table('department_form_parent_entry')->insertGetId($values);
				

				

				$team = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'team','attribute_value' => ucfirst(strtolower($line[0])));
				DB::table('department_form_child_entry')->insert($team);

				$submission_date_val = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'submission_date','attribute_value' => $submission_date);
				DB::table('department_form_child_entry')->insert($submission_date_val);

				$seller_id = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'seller_id','attribute_value' => $line[2]);
				DB::table('department_form_child_entry')->insert($seller_id);

				$emp_name = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'emp_name','attribute_value' => $line[3]);
				DB::table('department_form_child_entry')->insert($emp_name);

				$emp_id = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'emp_id','attribute_value' => $emp_id);
				DB::table('department_form_child_entry')->insert($emp_id);

				$customer_name = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'customer_name','attribute_value' => $line[5]);
				DB::table('department_form_child_entry')->insert($customer_name);

				$customer_mobile = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'customer_mobile','attribute_value' => $line[6]);
				DB::table('department_form_child_entry')->insert($customer_mobile);				

				$product_type = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'product_type','attribute_value' => $line[7]);
				DB::table('department_form_child_entry')->insert($product_type);

				$salary = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'salary','attribute_value' => $line[8]);
				DB::table('department_form_child_entry')->insert($salary);

				$category = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'category','attribute_value' => $line[9]);
				DB::table('department_form_child_entry')->insert($category);
				
				$ref_no = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'ref_no','attribute_value' => $line[10]);
				DB::table('department_form_child_entry')->insert($ref_no);

				$form_status = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'form_status','attribute_value' => $line[11]);
				DB::table('department_form_child_entry')->insert($form_status);

				$remarks = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'remarks','attribute_value' => addslashes(str_replace("'","`",$line[12])));
				DB::table('department_form_child_entry')->insert($remarks);

				
				
				//exit;


                $count++;
                
            }
            
            // Close opened CSV file
            fclose($csvFile);

			/*
			Array
			(
				[0] => Sahir
				[1] => 17-Jul-2023
				[2] => 91784
				[3] => Suhel
				[4] => Muhammad Umair Nawaz Muhammad Nawaz
				[5] => 0567255705
				[6] => 7000
				[7] => 5460417
				[8] => N1354950
				[9] => Booked
				[10] => Booked
				[11] => CB
			)
			*/

	}




	

 public function exportDocReportMasterMisMashreqCards(Request $request)
 {
	 $parameterInput = $request->input();
	$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Master_MIS_Mashreq_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AG1');
			$sheet->setCellValue('A1', 'Master MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('agent name'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('all_cda_deviation'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('app_decision'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('app_decisiondetails'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('application_date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('application_status'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('applicationid'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('booked_flag'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('bureau_score'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('bureau_segmentation'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('card_status'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('card_type'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('cda_descision'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('cif'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('customer_name'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('disbursed_date'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('employee_category_desc'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('employer_name'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('last_comment'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('min_startdate'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('mis_date'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('mrs_score'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('ref_no'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('remarks'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('seller_id'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('sellername'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('status'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('team'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('submission_date'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('customer_mobile'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('salary'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AG'.$indexCounter, strtoupper('form_status'))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqMasterMIS::where("id",$sid)->first();
				 
				 
				 $indexCounter++; 	
				
				 $sheet->setCellValue('A'.$indexCounter, $mis->agent_name)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->all_cda_deviation)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->app_decision)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->app_decisiondetails)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, date("d-m-Y",strtotime($mis->application_date)))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->application_status)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->applicationid)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->booked_flag)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->bureau_score)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->bureau_segmentation)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->card_status)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->card_type)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->cda_descision)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->cdafinalsalary)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->cif)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->customer_name)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->disbursed_date)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->employee_category_desc)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->employer_name)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->last_comment)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->min_startdate)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->mis_date)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->mrs_score)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->ref_no)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $mis->remarks)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $mis->seller_id)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $mis->sellername)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $mis->status)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $mis->team)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $mis->submission_date)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $mis->customer_mobile)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AF'.$indexCounter, $mis->salary)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AG'.$indexCounter, $mis->form_status)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'AG'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AG1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AG') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Master";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 } 



 


public function model5UpdateMobile(Request $req)
{
	$parameters = $req->input();
	$rowID = $parameters['rowV1'];
	$updateDepart = DepartmentFormEntry::find($rowID);
	$updateDepart->customer_mobile = $parameters['mobileV1'];
	if($updateDepart->save())
	{
		$existCheck = DepartmentFormChildEntry::where("parent_id",$rowID)->where("attribute_code","customer_mobile")->first();
		if($existCheck != '')
		{
			$update = DepartmentFormChildEntry::find($existCheck->id);
			$update->attribute_value = $parameters['mobileV1'];
			$update->save();
		}
		else
		{
			$createModel = new DepartmentFormChildEntry();
			$createModel->parent_id = $rowID;
			$createModel->form_id = 1;
			$createModel->attribute_code = "customer_mobile";
			$createModel->attribute_value = $parameters['mobileV1'];
			$createModel->status = 1;
			$createModel->save();
		}
	}
	echo "done";
	exit;
}


public function myModel6Start(Request $req)
{
		$parameters = $req->input();
		$mainData = DepartmentFormEntry::where('id',$rowID)->first();
		$emp_name = $mainData->emp_name;
		$emp_id = $mainData->emp_id;
		$rowID = $parameters['rowV6'];
	$updateDepart = DepartmentFormEntry::find($rowID);
	$updateDepart->remarks = trim($parameters['update_remark_6']);
	if($updateDepart->save())
	{
		$existCheck = DepartmentFormChildEntry::where("parent_id",$rowID)->where("attribute_code","remarks")->first();
		if($existCheck != '')
		{
			$update = DepartmentFormChildEntry::find($existCheck->id);
			$update->attribute_value = trim($parameters['update_remark_6']);
			$update->save();
		}
		else
		{
			$createModel = new DepartmentFormChildEntry();
			$createModel->parent_id = $rowID;
			$createModel->form_id = 1;
			$createModel->attribute_code = "remarks";
			$createModel->attribute_value = trim($parameters['update_remark_6']);
			$createModel->status = 1;
			$createModel->save();
		}
	}
	$objSave = new MashreqRemarkLogs();
	$objSave->remark = trim($parameters['update_remark_6']);
	$objSave->internal_id = $rowID;
	$objSave->updated_by = $req->session()->get('EmployeeId');
	$objSave->save();
	NotificatonController::sendMeNotification($emp_id,'Update on '.$emp_name,'Update on '.$emp_name.' -'.trim($parameters['update_remark_6']),'SubmissionList');
	echo "done";
	exit;
}

public function openRemarkDetails(Request $request)
{
	$idSend =  $request->idSend;
	$remarkLogs = MashreqRemarkLogs::where("internal_id",$idSend)->orderBy("id","DESC")->get();
	 return view("Attribute/Mashreq/openRemarkDetails",compact('remarkLogs'));
}

public static function getNameUser($uid)
{
	 $data = Employee::where('id',$uid)->orderBy("id","DESC")->first();
		  //print_r($data);
		  if($data != '')
		  {
		  return $data->fullname;
		  }
		  else
		  {
		  return '';
		  }
}
public function loadBankContentsMashreqCardCurrentMonth(Request $request)
	{
		$form_id = $request->form_id;
		$searchValues = array();
		$search_internal_flag = '';
		$CurrentMonthFilter = '';

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');

		$paginationValue = 6000;		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";

		
		if(@$request->session()->get('ref_no_string_cm') != '' && @$request->session()->get('form_id') != '')
		{
			$ref_no_string = $request->session()->get('ref_no_string_cm');
			if(strlen($ref_no_string)>4)
			{
				$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
			}
			else
			{
				$whereRaw .= " AND ref_no IN ('0')";	
			}	
		}

		if(@$request->session()->get('team_internal_cm') != '')
		{
			$team = $request->session()->get('team_internal_cm');
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

		if(@$request->session()->get('sales_processor_internal_cm') != '')
		{
			$team = array();
			$team_Mahwish_130 = array('Ajay','Anas','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');

			$sales_processor_internal = $request->session()->get('sales_processor_internal_cm');
			
			foreach($sales_processor_internal as $sales_processor_internal_value)
			{				
				if($sales_processor_internal_value=='Mahwish')
				{
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_internal_value=='Arsalan')
				{
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_internal_value=='Umer')
				{
					$team = array_merge($team,$team_Umar_168);
				}
			}
			
			
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
			$searchValues['sales_processor_internal'] = $sales_processor_internal;
			
		}

		if(@$request->session()->get('emp_id_internal_cm') != '')
		{
			$emp_id = $request->session()->get('emp_id_internal_cm');
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

		if(@$request->session()->get('form_status_cm') != '')
		{
			$form_status = $request->session()->get('form_status_cm');
			$form_status_str = '';
			foreach($form_status as $form_status_value)
			{
				if($form_status_str == '')
				{
					$form_status_str = "'".$form_status_value."'";
				}
				else
				{
					$form_status_str = $form_status_str.","."'".$form_status_value."'";
				}
			}
			$whereRaw .= " AND form_status IN (".$form_status_str.")";			
		}

		if(@$request->session()->get('application_id_internal_cm') != '')
		{
			$application_id_internal = $request->session()->get('application_id_internal_cm');						
			$whereRaw .= " AND application_id = '".$application_id_internal."'";			
		}

		if(@$request->session()->get('missing_login_internal_cm') != '')
		{
			$missing_login_internal = $request->session()->get('missing_login_internal_cm');
			if($missing_login_internal=='Missing in Login (Current Month)')
			{
				$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
				$whereRaw .= " AND submission_date >='".date('Y-m-01')."'";
				$whereRaw .= " AND submission_date <='".date('Y-m-d')."'";
			}
			else if($missing_login_internal=='Linked From Booking')
			{
				$whereRaw .= " AND missing_booking_link_status = '1'";
				
			}
			else if($missing_login_internal=='Missing in Login')
			{
				$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
			}
			else if($missing_login_internal=='Missing Mobile Number')
			{
				$whereRaw .= " AND (customer_mobile = '' OR customer_mobile IS NULL)";
			}
			else
			{
				$whereRaw .= " AND (customer_mobile != '' AND customer_mobile IS NOT NULL)";
			}
		}

		if(@$request->session()->get('ref_no_internal_cm') != '')
		{
			$ref_no_internal = $request->session()->get('ref_no_internal_cm');						
			$whereRaw .= " AND ref_no = '".$ref_no_internal."'";			
		}

		if(@$request->session()->get('remarks_cm') != '')
		{
			$remarks = $request->session()->get('remarks_cm');						
			$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
		}

		if($request->session()->get('start_date_internal_cm') != '')
		{
			$start_date_internal = $request->session()->get('start_date_internal_cm');			
		
			$searchValues['start_date_internal'] = $start_date_internal;			
		}

		if($request->session()->get('end_date_internal_cm') != '')
		{
			$end_date_internal = $request->session()->get('end_date_internal_cm');			
			
			$searchValues['end_date_internal'] = $end_date_internal;			
		}

		if($request->session()->get('search_internal_flag_cm') != '')
		{
			$search_internal_flag = $request->session()->get('search_internal_flag_cm');
		}

		if($request->session()->get('CurrentMonthFilter_cm') != '')
		{
			$CurrentMonthFilter = $request->session()->get('CurrentMonthFilter_cm');
		}
	$start_date_internal = '01'.'-'.date("m").'-'.date("Y");
	$end_date_internal = date("t").'-'.date("m").'-'.date("Y");
	$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
	$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
		$departmentFormParentID = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get(['id']);

		$tableID = array();
		foreach($departmentFormParentID as $departmentFormParentIDData)
		{
			$tableID[] = $departmentFormParentIDData->id;
		}

		$departmentFormParentTotal = count($departmentFormParentID);

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		

		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$form_status = DepartmentFormEntry::where("form_status","!=",'')->where("form_id",'1')->groupBy('form_status')
		->selectRaw('count(*) as total, form_status')
		->get();

		

        return view("Attribute/Mashreq/loadBankContentsMashreqCardCurrentMonth",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues','form_status','user_id','username','tableID','search_internal_flag','CurrentMonthFilter'));
		//$request->session()->put('CurrentMonthFilter','');
	}
	
	
	public function searchInternalInnerCM(Request $request)
	{
		$requestParameters = $request->input();
		$remarks = '';
		$ref_no_internal = '';
		$application_id_internal = '';
		$form_status = '';
		$team_internal = '';
		$emp_id_internal = '';
		$start_date_internal ='';
		$end_date_internal ='';
		$sales_processor_internal = '';
		$missing_login_internal = '';

		if(@isset($requestParameters['ref_no_internal']))
		{
			$ref_no_internal = @$requestParameters['ref_no_internal'];
		}
		if(@isset($requestParameters['sales_processor_internal']))
		{
			$sales_processor_internal = @$requestParameters['sales_processor_internal'];
		}
		if(@isset($requestParameters['missing_login_internal']))
		{
			$missing_login_internal = @$requestParameters['missing_login_internal'];
		}

		if(@isset($requestParameters['application_id_internal']))
		{
			$application_id_internal = @$requestParameters['application_id_internal'];
		}

		if(@isset($requestParameters['remarks']))
		{
			$remarks = @$requestParameters['remarks'];
		}
		if(@isset($requestParameters['team_internal']))
		{
			$team_internal = @$requestParameters['team_internal'];
		}
		if(@isset($requestParameters['emp_id_internal']))
		{
			$emp_id_internal = @$requestParameters['emp_id_internal'];
		}
		if(isset($requestParameters['form_status']))
		{
			$form_status = @$requestParameters['form_status'];
		}
		if(isset($requestParameters['start_date_internal']))
		{
			$start_date_internal = @$requestParameters['start_date_internal'];
		}
		if(isset($requestParameters['end_date_internal']))
		{
			$end_date_internal = @$requestParameters['end_date_internal'];
		}
		
		
		$request->session()->put('ref_no_internal_cm',$ref_no_internal);
		$request->session()->put('sales_processor_internal_cm',$sales_processor_internal);
		$request->session()->put('missing_login_internal_cm',$missing_login_internal);
		$request->session()->put('application_id_internal_cm',$application_id_internal);
		$request->session()->put('remarks_cm',$remarks);
		$request->session()->put('team_internal_cm',$team_internal);
		$request->session()->put('emp_id_internal_cm',$emp_id_internal);
		$request->session()->put('form_status_cm',$form_status);
		$request->session()->put('start_date_internal_cm',$start_date_internal);
		$request->session()->put('end_date_internal_cm',$end_date_internal);
		$request->session()->put('search_internal_flag_cm','1');

		if(isset($requestParameters['CurrentMonthFilter']) && $requestParameters['CurrentMonthFilter']!='')
		{
			$request->session()->put('CurrentMonthFilter_cm','1');
			if($request->session()->get('start_date_internal_cm') == '')
			{
			$request->session()->put('start_date_internal_cm',date('01-m-Y'));
			}
			if($request->session()->get('end_date_internal_cm') == '')
			{
			$request->session()->put('end_date_internal_cm',date('d-m-Y'));
			}
		}
		else
		{
			$request->session()->put('CurrentMonthFilter_cm','');
		}
		
		return redirect("loadBankContentsMashreqCardCurrentMonth/1");
	}
	
	
	public function resetInternalInnerCM(Request $request)
	{
		$request->session()->put('ref_no_internal_cm','');
		$request->session()->put('application_id_internal_cm','');
		$request->session()->put('remarks_cm','');
		$request->session()->put('form_status_cm','');
		$request->session()->put('emp_id_internal_cm','');
		$request->session()->put('sales_processor_internal_cm','');
		$request->session()->put('missing_login_internal_cm','');
		$request->session()->put('team_internal_cm','');
		$request->session()->put('start_date_internal_cm','');
		$request->session()->put('end_date_internal_cm','');
		$request->session()->put('search_internal_flag_cm','');
		$request->session()->put('CurrentMonthFilter_cm','');
		return redirect("loadBankContentsMashreqCardCurrentMonth/1");
	}
	
	
	
}
