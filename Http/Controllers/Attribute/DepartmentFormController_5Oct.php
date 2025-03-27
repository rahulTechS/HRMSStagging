<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
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

use Session;

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
			$start_date = @$_REQUEST['start_date'];
			$end_date = @$_REQUEST['end_date'];

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
			
		$request->session()->put('ref_no','');	
		$request->session()->put('team','');
		$request->session()->put('emp_id','');			
		$request->session()->put('customer','');
		$request->session()->put('start_date','');
		$request->session()->put('end_date','');
		$request->session()->put('paginationValue','');
		
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

		

		if(@$request->session()->get('ref_no') != '')
		{
			$ref_no = $request->session()->get('ref_no');
			$whereRaw .= " AND ref_no ='".$ref_no."'";
			$searchValues['ref_no'] = $ref_no;
		}
		if($request->session()->get('team') != '')
		{
			$team = $request->session()->get('team');
			$whereRaw .= " AND team ='".$team."'";
			$searchValues['team'] = $team;
		}
		if($request->session()->get('emp_id') != '')
		{
			$emp_id = $request->session()->get('emp_id');
			$whereRaw .= " AND emp_id ='".$emp_id."'";
			$searchValues['emp_id'] = $emp_id;
		}
		if($request->session()->get('customer') != '')
		{
			$customer = $request->session()->get('customer');
			$whereRaw .= " AND customer_name LIKE '%".$customer."%'";	
			$searchValues['customer'] = $customer;
		}

		if($request->session()->get('start_date') != '')
		{
			$start_date = $request->session()->get('start_date');
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date))."'";	
			$searchValues['start_date'] = $start_date;
		}

		if($request->session()->get('end_date') != '')
		{
			$end_date = $request->session()->get('end_date');
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date))."'";	
			$searchValues['end_date'] = $end_date;
		}

		$departmentFormParentTotal = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','ASC')->get()->count();

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','ASC')->paginate($paginationValue);

		

		$Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();

        return view("Attribute/departmentFormData",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues','form_id'));
    }
	
	
	public function loadBankContentsMashreqCard(Request $request)
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

		

		if(@$request->session()->get('ref_no') != '')
		{
			$ref_no = $request->session()->get('ref_no');
			$whereRaw .= " AND ref_no ='".$ref_no."'";
			$searchValues['ref_no'] = $ref_no;
		}
		if($request->session()->get('team') != '')
		{
			$team = $request->session()->get('team');
			$whereRaw .= " AND team ='".$team."'";
			$searchValues['team'] = $team;
		}
		if($request->session()->get('emp_id') != '')
		{
			$emp_id = $request->session()->get('emp_id');
			$whereRaw .= " AND emp_id ='".$emp_id."'";
			$searchValues['emp_id'] = $emp_id;
		}
		if($request->session()->get('customer') != '')
		{
			$customer = $request->session()->get('customer');
			$whereRaw .= " AND customer_name LIKE '%".$customer."%'";	
			$searchValues['customer'] = $customer;
		}

		if($request->session()->get('start_date') != '')
		{
			$start_date = $request->session()->get('start_date');
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date))."'";	
			$searchValues['start_date'] = $start_date;
		}

		if($request->session()->get('end_date') != '')
		{
			$end_date = $request->session()->get('end_date');
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date))."'";	
			$searchValues['end_date'] = $end_date;
		}

		$departmentFormParentTotal = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','ASC')->get()->count();

		$departmentFormParentDetails = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','ASC')->paginate($paginationValue);

		

		$Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();

        return view("Attribute/Mashreq/loadBankContentsMashreqCard",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','Employee_details','searchValues'));
	}
	
	
	public function loadBankContentsMashreqCardLogin(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		$datasMashreqLogin = MashreqLoginMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqLogin);
		exit;
		echo "done";
		exit; */ 
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardLogin",compact('datasMashreqLogin','searchValues'));
	}
	
	
	public function loadBankContentsMashreqCardBooking(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		$datasMashreqBooking = MashreqBookingMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqBooking);
		exit;
		echo "done";
		exit;   */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBooking",compact('datasMashreqBooking','searchValues'));
	}

	public function loadBankContentsMashreqCardBank(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		$datasMashreqBank = MashreqBankMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqBank);
		exit;
		echo "done";
		exit;    */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardBank",compact('datasMashreqBank','searchValues'));
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
		$datasMashreqMTD = MashreqMTDMIS::orderby('id','ASC')->paginate($paginationValue);
	   /*  echo "<pre>";
		print_r($datasMashreqMTD);
		exit;
		echo "done";
		exit;     */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMTD",compact('datasMashreqMTD','searchValues'));
	}
	
	public function loadBankContentsMashreqCardMaster(Request $request)
	{
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		$datasMashreqMaster = MashreqMasterMIS::orderby('id','ASC')->paginate($paginationValue);
		/* echo "<pre>";
		print_r($datasMashreqMaster);
		exit;
		echo "done";
		exit;      */
		 return view("Attribute/Mashreq/loadBankContentsMashreqCardMaster",compact('datasMashreqMaster','searchValues'));
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

		  $Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
        
		  return view("Attribute/departmentFormEntry",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
    }

	public function addDepartmentFormData($form_id=NULL)
    {
		  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();
		  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->get(['form_section']);

		  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		  $Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
        
		  return view("Attribute/addDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
    }

	public function editDepartmentFormData($parent_id=NULL,$form_id=NULL)
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

		  $Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
        
		  return view("Attribute/editDepartmentFormData",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
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

		  $Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
        
		  return view("Attribute/departmentFormDataEdit",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
    }

	public function addDepartmentFormEntryPost(Request $req)
    {			
			$entry_obj = new DepartmentFormEntry();			

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
				}
				if($k=='submission_date')
				{
					$submission_date = date('Y-m-d',strtotime($v));
				}
				if($k=='team')
				{
					$team = $v;
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
				->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => $attribute_value['customer_mobile'],'application_id' => $application_id, 'ref_no' => $ref_no, 'submission_date' => $submission_date,'team' => $team,'emp_id' => $emp_id,'emp_name' => $emp_name
				]);
           
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
				->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => $attribute_value['customer_mobile'], 'emp_id'=>$emp_id, 'emp_name'=>$emp_name, 'application_id'=>$application_id, 'ref_no'=>$ref_no, 'submission_date'=>$submission_date, 'team'=>$team
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
		return $Employee_details =  Employee_details::where("offline_status",1)->where("emp_id",$id)->first();
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

	public static function importCSV()
	{

		$file = public_path('uploads/formFiles/MujMIS.csv');
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
				$whereRaw = " ref_no ='".$ref_no."'";
				$check = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get();

				if(count($check)>0)
				{
					$parent_id = $check[0]->id;
					$delete1 = DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->delete();
					$delete2 = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->delete();
					
				}				
				/*$sub_date = explode('/',$line[1]); 
				$y=$sub_date[2];
				$m=$sub_date[0];
				$d=$sub_date[1];
				
				if(strlen($sub_date[0])<2)
				{
					$m='0'.$sub_date[0];
				}
				if(strlen($sub_date[1])<2)
				{
					$d='0'.$sub_date[1];
				}
				$submission_date = $y."-".$m."-".$d;*/
				$whereref = " application_ref_no='".$line[10]."'";
				$checkRef = DB::table('mashreq_bank_mis')->whereRaw($whereref)->get();
				$application_date = @$checkRef[0]->application_date;
				$submission_date = $application_date?$application_date:'0000-00-00';
				

				$values = array('form_id' => '1','form_title' => 'Credit Card Submission Form','ref_no'=>$line[10], 'emp_name' => $line[3], 'emp_id' => $line[4], 'team'=>ucfirst(strtolower($line[0])), 'customer_name'=>$line[5], 'customer_mobile'=>$line[6], 'submission_date'=>$submission_date);
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

				$emp_id = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'emp_id','attribute_value' => $line[4]);
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
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 } 
}
