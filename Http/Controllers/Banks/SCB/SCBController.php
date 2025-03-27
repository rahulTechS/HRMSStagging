<?php

namespace App\Http\Controllers\Banks\SCB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;

use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;


use App\Models\Company\Department;
use App\Models\Employee\Employee_details;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;

use App\Models\Bank\SCB\SCBDepartmentFormChildEntry;

use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Employee\ExportDataLog;
use Session;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Employee\Employee_attribute;

class SCBController extends Controller
{
	 public static function getEmployeeName($empid)
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
 
 public function ScbCardsManagement()
 {
		/*
		*employee Id
		*/
			$employeeIdList = SCBDepartmentFormParentEntry::select('emp_id')->where("form_id",3)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		
		
		/*
		*status
		*/
			$teamData = SCBDepartmentFormParentEntry::select('team')->where("form_id",3)->get()->unique('team');
			
		/*
		*status
		*/
	return view("Banks/SCB/scbCardsManagement",compact("employeeIdList","teamData"));
 }
 
  	public function loadBankContentsSCBCard(Request $request)
	{
		 //$request->session()->put('paginationValue','');
		$form_id = 3;
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

		
		

		

		/* if(@$request->session()->get('form_status') != '')
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
		} */
		
		if(@$request->session()->get('master_scb_search_internal') != '' && @$request->session()->get('master_scb_search_internal') == 2)
		{
			if(@$request->session()->get('ref_no_SCB_master') != '')
				{
					$refNO = $request->session()->get('ref_no_SCB_master');
					
				
					$whereRaw .= " AND ref_no like '%".$refNO."%'";	
					
					
				}
				
				
				
				if(@$request->session()->get('team_SCB_master') != '')
				{
					$teamL = $request->session()->get('team_SCB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND team IN (".$teamstr.")";	
					
					
				}
				
				
				
				if(@$request->session()->get('emp_id_SCB_master') != '')
				{
					$empIds = $request->session()->get('emp_id_SCB_master');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_SCB_master') != '')
				{
					$start_date_application_SCB_internal = $request->session()->get('start_date_application_SCB_master');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					$searchValues['start_date_application_SCB_master'] = $start_date_application_SCB_internal;			
				}

				if($request->session()->get('end_date_application_SCB_master') != '')
				{
					$end_date_application_SCB_internal = $request->session()->get('end_date_application_SCB_master');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					$searchValues['end_date_application_SCB_master'] = $end_date_application_SCB_internal;			
				}
		}
		else
		{
				if(@$request->session()->get('ref_no_SCB_internal') != '')
				{
					$refNO = $request->session()->get('ref_no_SCB_internal');
					
				
					$whereRaw .= " AND ref_no like '%".$refNO."%'";	
					
					
				}
				if(@$request->session()->get('form_status_SCB_internal') != '')
				{
					$status = $request->session()->get('form_status_SCB_internal');
					$strStatus = '';
					foreach($status  as $s)
					{
						if($strStatus == '')
						{
							$strStatus = "'".$s."'";
						}
						else
						{
							$strStatus = $strStatus.",'".$s."'";
						}
					}
				
					$whereRaw .= " AND form_status IN (".$strStatus.")";	
					
					
				}
				
				
				if(@$request->session()->get('team_SCB_internal') != '')
				{
					$teamL = $request->session()->get('team_SCB_internal');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND team IN (".$teamstr.")";	
					
					
				}
				
				if(@$request->session()->get('emp_id_SCB_internal') != '')
				{
					$empIds = $request->session()->get('emp_id_SCB_internal');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				
				
				if(@$request->session()->get('card_type_SCB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_type_SCB_internal');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND Card_Type_scb IN (".$cardTypeInterbalstr.")";	
					
					
				}
				
				if(@$request->session()->get('pwId_SCB_internal') != '')
				{
					$pwidInterbalL = $request->session()->get('pwId_SCB_internal');
					$pwidInterbalstr = '';
					foreach($pwidInterbalL  as $CY)
					{
						if($pwidInterbalstr == '')
						{
							$pwidInterbalstr = "'".$CY."'";
						}
						else
						{
							$pwidInterbalstr = $pwidInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND pw_id_scb IN (".$pwidInterbalstr.")";	
					
					
				}
				if(@$request->session()->get('submission_type_internal') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal');
					$submission_type_internalStr = '';
					foreach($submission_type_internalL  as $ST)
					{
						if($submission_type_internalStr == '')
						{
							$submission_type_internalStr = "'".$ST."'";
						}
						else
						{
							$submission_type_internalStr = $submission_type_internalStr.",'".$ST."'";
						}
					}
				
					$whereRaw .= " AND missing_internal IN (".$submission_type_internalStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_SCB_internal') != '')
				{
					$start_date_application_SCB_internal = $request->session()->get('start_date_application_SCB_internal');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					$searchValues['start_date_application_SCB_internal'] = $start_date_application_SCB_internal;			
				}

				if($request->session()->get('end_date_application_SCB_internal') != '')
				{
					$end_date_application_SCB_internal = $request->session()->get('end_date_application_SCB_internal');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					$searchValues['end_date_application_SCB_internal'] = $end_date_application_SCB_internal;			
				}
				
				if($request->session()->get('start_date_application_SCB_internal_a') != '')
				{
					$start_date_application_SCB_internal_a = $request->session()->get('start_date_application_SCB_internal_a');			
					$whereRaw .= " AND approved_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal_a))."'";
					$searchValues['start_date_application_SCB_internal_a'] = $start_date_application_SCB_internal_a;			
				}

				if($request->session()->get('end_date_application_SCB_internal_a') != '')
				{
					$end_date_application_SCB_internal_a = $request->session()->get('end_date_application_SCB_internal_a');			
					$whereRaw .= " AND approved_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal_a))."'";
					$searchValues['end_date_application_SCB_internal_a'] = $end_date_application_SCB_internal_a;			
				}
		
		}



		$endDate = date("Y-m-d");
		$startDate = date("Y").'-'.date("m").'-'.'01';

		
		

		$departmentFormParentTotal = DB::table('scb_department_form_parent_entry')->whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->orderby('application_date','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('scb_department_form_parent_entry')->whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->orderby('application_date','DESC')->paginate($paginationValue);

		

		

		
		/*
		*employee Id
		*/
			$employeeIdList = SCBDepartmentFormParentEntry::select('emp_id')->where("form_id",3)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		/*
		*status
		*/
			$formStatusData = SCBDepartmentFormParentEntry::select('form_status')->where("form_id",3)->get()->unique('form_status');
			
		/*
		*status
		*/
		
		/*
		*Team
		*/
			$teamData = SCBDepartmentFormParentEntry::select('team')->where("form_id",3)->get()->unique('team');
			
		/*
		*Team
		*/
		
		
		/*
		*channel_cbd
		*/
			$channel_cbd = array();
			
		/*
		*channel_cbd
		*/
		
		/*
		*status_AECB_cbd
		*/
			$status_AECB_cbd = array();
			
		/*
		*status_AECB_cbd
		*/
		
		
		/*
		*card_type_cbd
		*/
			$card_type_scb =SCBDepartmentFormParentEntry::select('Card_Type_scb')->where("form_id",3)->get()->unique('Card_Type_scb');
			
		/*
		*card_type_cbd
		*/
		
		
		
		/*
		*card_type_cbd
		*/
			$pwIdList =SCBDepartmentFormParentEntry::select('pw_id_scb')->where("form_id",3)->get()->unique('pw_id_scb');
			
		/*
		*card_type_cbd
		*/
		
		/*
		*complete Status CBD
		*/
		
			$masterAttributesStatus = MasterAttribute::select("option_values")->where("attribute_code","status_scb")->first();
		/*
		*complete Status CBD
		*/
		
        return view("Banks/SCB/loadBankContentsSCBCard",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','employeeIdList','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_scb','masterAttributesStatus','pwIdList'));
	}
 
	public function addscbCards()
	 {
				$departmentFormDetails =   DepartmentForm::where("id",3)->first();
			  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
			  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
			  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
			  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

			  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', 3)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

			  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', 3)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

			  $Employee_details = Employee_details::where("offline_status",1)->where("dept_id",47)->where("job_function",2)->orderby('first_name','ASC')->get();
			
			  return view("Banks/SCB/addscbCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
	 }
	 
	 
	 public function addSCBEntryPost(Request $request)
	 {
				$postData = $request->input();
				
				$postDataInput = $postData['attribute_value'];
				/* echo "<pre>";
				print_r($postDataInput);
				exit; */
				$entry_obj = new SCBDepartmentFormParentEntry();			
		
				/*
				*parent entry 
				*start code
				*/
				$entry_obj->ref_no = $postDataInput['agency_reference_scb'];
				$entry_obj->form_id = 3;
				$entry_obj->form_title = 'SCB Internal MIS';
				$entry_obj->form_status = $postDataInput['status_scb'];
				$entry_obj->team = $postDataInput['tl_scb'];
				$entry_obj->team_company = $postDataInput['Team_scb'];
				$entry_obj->emp_id = $postDataInput['NBO_scb'];
				if($postDataInput['approval_date_scb'] != '' && $postDataInput['approval_date_scb'] != NULL)
				{
				$entry_obj->approved_date = date("Y-m-d",strtotime($postDataInput['approval_date_scb']));
				}
				$entry_obj->remarks = $postDataInput['Comments_scb'];
				
				
				$entry_obj->Card_Type_scb = $postDataInput['Card_Type_scb'];
				$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['Current_Queue_Date_scb']));
				$entry_obj->user_id  = $request->session()->get('EmployeeId');
				$entry_obj->Ageing_scb = $postDataInput['Ageing_scb'];
				$entry_obj->company_name_scb = $postDataInput['company_name_scb'];
				$entry_obj->aloc_non_aloc_scb = $postDataInput['aloc_non_aloc_scb'];
				$entry_obj->pw_id_scb = $postDataInput['pw_id_scb'];
				$entry_obj->customer_name = $postDataInput['customer_name_scb'];
				$entry_obj->customer_mobile = $postDataInput['customer_mobile_scb'];
				$entry_obj->status = 1;
				$entry_obj->missing_internal = 1;
			
				$entry_obj->save();
				$insertID = $entry_obj->id;
				/*
				*parent entry 
				*end code
				*/
				
				
				/*
				*child entry 
				*start code
				*/
				$child_obj = new SCBDepartmentFormChildEntry();
				foreach($postDataInput as $key=>$value)
				{
					$child_obj = new SCBDepartmentFormChildEntry();
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = 3;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
				}
				
				/*
				*child entry 
				*end code
				*/
				$request->session()->flash('message','Record added Successfully.');
				return redirect('ScbCardsManagement');
		 
	 }


public static function importCSVSCB()
	{
		/* echo "yes";
		exit; */
		$file = public_path('uploads/formFiles/SCB_Internal_MIS_3.csv');
		// Open uploaded CSV file with read-only mode
            $csvFile = fopen($file, 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
			$count = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE)
			{				
				 /* echo "<pre>";
				print_r($line);
				exit;  */
				/*
				*check for existance
				*start code
				*/
					$refNo = $line[2];
					if($refNo != '')
					{
					$existanceCheck = SCBDepartmentFormParentEntry::where("ref_no",$refNo)->first();
				/*
				*check for existance
				*start code
				*/
				/*
				*import data
				*/
						if($existanceCheck != '')
						{
							$entry_obj = SCBDepartmentFormParentEntry::find($existanceCheck->id);	
						}
						else
						{
							$entry_obj = new SCBDepartmentFormParentEntry();	
							
						}
						/*
						*parent entry 
						*start code
						*/
						$entry_obj->ref_no = $line[2];
						$entry_obj->form_id = 3;
						$entry_obj->form_title = 'SCB Internal MIS';
						$entry_obj->form_status = trim($line[10]);
						$entry_obj->team = trim($line[5]);
						
						$entry_obj->remarks = $line[11];
						$entry_obj->emp_id = trim($line[4]);
						
						$entry_obj->application_date = date("Y-m-d",strtotime($line[8]));
						$entry_obj->status = 1;
						$entry_obj->emp_name = trim($line[4]);
						$entry_obj->team_company = trim($line[6]);
						$entry_obj->company_name_scb = trim($line[0]);
						$entry_obj->aloc_non_aloc_scb = trim($line[1]);
						$entry_obj->pw_id_scb = trim($line[3]);
						$entry_obj->Ageing_scb = trim($line[9]);
						$entry_obj->Card_Type_scb = trim($line[7]);
						$entry_obj->missing_internal = 1;
						$entry_obj->customer_name = trim($line[12]);
						$entry_obj->customer_mobile = trim($line[13]);
						$entry_obj->save();
						
						if($existanceCheck != '')
						{
							$insertID = $existanceCheck->id;
						}
						else
						{
							$insertID = $entry_obj->id;		
						}
						/*
						*parent entry 
						*end code
						*/
						
						
						/*
						*child entry 
						*start code
						*/
						 if($existanceCheck != '')
							{
								$existAttrMod = SCBDepartmentFormChildEntry::where("parent_id",$insertID)->get();
								foreach($existAttrMod as $attr)
								{
									$attr->delete();
								}
							}
							
						
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'tl_scb';
							$child_obj->attribute_value = $line[5];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'Team_scb';
							$child_obj->attribute_value = $line[6];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'NBO_scb';
							$child_obj->attribute_value = $line[4];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'company_name_scb';
							$child_obj->attribute_value = $line[0];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'aloc_non_aloc_scb';
							$child_obj->attribute_value = $line[1];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'status_scb';
							$child_obj->attribute_value = $line[10];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'pw_id_scb';
							$child_obj->attribute_value = $line[3];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'Current_Queue_Date_scb';
							$child_obj->attribute_value = $line[8];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'Comments_scb';
							$child_obj->attribute_value = $line[11];
							$child_obj->status = 1;
							$child_obj->save();
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'Card_Type_scb';
							$child_obj->attribute_value = $line[7];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'agency_reference_scb';
							$child_obj->attribute_value = $line[2];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'Ageing_scb';
							$child_obj->attribute_value = $line[9];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'customer_name_scb';
							$child_obj->attribute_value = $line[12];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new SCBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 3;
							$child_obj->attribute_code = 'customer_mobile_scb';
							$child_obj->attribute_value = $line[13];
							$child_obj->status = 1;
							$child_obj->save();
							
							
						
						/*
						*child entry 
						*end code
						*/
				
				/*
				*import data
				*/
               /*  echo "done";
			exit;  */
					}
            }
            echo "done";
			exit;
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
 public function setPaginationValueSCBCard(Request $request)
 {
	 $offset = $request->offset;
	 $request->session()->put('paginationValue',$offset);
	 return redirect('loadBankContentsSCBCard');
 }
 	
	
public function SCBCardsSearchMaster(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
			
				$ref_no = '';
				$emp_id = '';
				
				if(@isset($requestParameters['ref_no']))
				{
					$ref_no = @$requestParameters['ref_no'];
				}

				if(isset($requestParameters['team']))
				{
					$team = @$requestParameters['team'];
				}

				
				if(isset($requestParameters['emp_id']))
				{
					$emp_id = @$requestParameters['emp_id'];
				}

				if(isset($requestParameters['start_date']))
				{
					$start_date_application = @$requestParameters['start_date'];
				}
				if(isset($requestParameters['end_date']))
				{
					$end_date_application = @$requestParameters['end_date'];
				}
				
				$request->session()->put('ref_no_SCB_master',$ref_no);
				$request->session()->put('team_SCB_master',$team);
				$request->session()->put('emp_id_SCB_master',$emp_id);
				$request->session()->put('start_date_application_SCB_master',$start_date_application);
				$request->session()->put('end_date_application_SCB_master',$end_date_application);
				$request->session()->put('master_scb_search_internal',2);
				$request->session()->put('master_scb_search_bank',2);
				return redirect("ScbCardsManagement");
	}
	
	public function resetSCBMaster(Request $request)
	{
				$request->session()->put('ref_no_SCB_master','');
				$request->session()->put('team_SCB_master','');
				$request->session()->put('emp_id_SCB_master','');
				$request->session()->put('start_date_application_SCB_master','');
				$request->session()->put('end_date_application_SCB_master','');
				$request->session()->put('master_scb_search_internal','');
				$request->session()->put('master_scb_search_bank','');
				return redirect("ScbCardsManagement");
	}
	
	public function searchSCBInternalInner(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$form_status = '';
				$ref_no = '';
				$emp_id = '';
				$pwid_scb = '';
				$start_date_application_a = '';
				$end_date_application_a = '';
				
				$card_type_scb = '';
				$submission_type_inner = '';
				
			
				
				if(isset($requestParameters['card_type_scb']))
				{
					$card_type_scb = @$requestParameters['card_type_scb'];
				}
				if(isset($requestParameters['pwid_scb']))
				{
					$pwid_scb = @$requestParameters['pwid_scb'];
				}

				if(@isset($requestParameters['ref_no']))
				{
					$ref_no = @$requestParameters['ref_no'];
				}

				if(isset($requestParameters['team']))
				{
					$team = @$requestParameters['team'];
				}

				if(isset($requestParameters['form_status']))
				{
					$form_status = @$requestParameters['form_status'];
				}
				
				if(isset($requestParameters['emp_id']))
				{
					$emp_id = @$requestParameters['emp_id'];
				}

				if(isset($requestParameters['start_date_application']))
				{
					$start_date_application = @$requestParameters['start_date_application'];
				}
				if(isset($requestParameters['end_date_application']))
				{
					$end_date_application = @$requestParameters['end_date_application'];
				}
				
				if(isset($requestParameters['start_date_application_a']))
				{
					$start_date_application_a = @$requestParameters['start_date_application_a'];
				}
				if(isset($requestParameters['end_date_application_a']))
				{
					$end_date_application_a = @$requestParameters['end_date_application_a'];
				}
				if(isset($requestParameters['submission_type_inner']))
				{
					$submission_type_inner = @$requestParameters['submission_type_inner'];
				}
				$request->session()->put('master_scb_search_internal','');
				$request->session()->put('ref_no_SCB_internal',$ref_no);
				$request->session()->put('form_status_SCB_internal',$form_status);
				$request->session()->put('team_SCB_internal',$team);
				$request->session()->put('emp_id_SCB_internal',$emp_id);
				$request->session()->put('start_date_application_SCB_internal',$start_date_application);
				$request->session()->put('end_date_application_SCB_internal',$end_date_application);
				$request->session()->put('start_date_application_SCB_internal_a',$start_date_application_a);
				$request->session()->put('end_date_application_SCB_internal_a',$end_date_application_a);
				
				
				$request->session()->put('card_type_SCB_internal',$card_type_scb);
				$request->session()->put('pwId_SCB_internal',$pwid_scb);
				$request->session()->put('submission_type_internal',$submission_type_inner);
				return redirect("loadBankContentsSCBCard");
	}
	
	
	public function resetLoginInnerSCBInternal(Request $request)
	{
			$request->session()->put('ref_no_SCB_internal','');
				$request->session()->put('form_status_SCB_internal','');
				$request->session()->put('team_SCB_internal','');
				$request->session()->put('emp_id_SCB_internal','');
				$request->session()->put('start_date_application_SCB_internal','');
				$request->session()->put('end_date_application_SCB_internal','');
			
				$request->session()->put('start_date_application_SCB_internal_a','');
				$request->session()->put('end_date_application_SCB_internal_a','');
				$request->session()->put('card_type_SCB_internal','');
				$request->session()->put('pwId_SCB_internal','');
				$request->session()->put('submission_type_internal','');
				
				$request->session()->put('master_scb_search_internal',2);
				return redirect("loadBankContentsSCBCard");
	}
	
	
	public function editscbCards($parent_id=NULL,$form_id=NULL)
	 {
			  $departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
			  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
			  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
			  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
			  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

			  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

			  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

			  $departmentFormParentDetails = DB::table('scb_department_form_parent_entry')->where('id', $parent_id)->first();

			  $departmentFormChildDetails = DB::table('scb_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

			   $Employee_details = Employee_details::where("offline_status",1)->where("dept_id",47)->where("job_function",2)->orderby('first_name','ASC')->get();
			
			  return view("Banks/SCB/editscbCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
	 }
	 
	 
	 public function editSCBFormEntryPost(Request $request)
	 {
		 
				$postData = $request->input();
				$postDataInput = $postData['attribute_value'];
				$entry_objUpdate = SCBDepartmentFormParentEntry::find($postData['parent_id']);			
		
				/*
				*parent entry 
				*start code
				*/
				
				$entry_objUpdate->ref_no = $postDataInput['agency_reference_scb'];
				$entry_objUpdate->form_id = 3;
				$entry_objUpdate->form_title = 'SCB Internal MIS';
				$entry_objUpdate->form_status = $postDataInput['status_scb'];
				$entry_objUpdate->team = $postDataInput['tl_scb'];
				if($postDataInput['approval_date_scb'] != '' && $postDataInput['approval_date_scb'] != NULL)
				{
				$entry_objUpdate->approved_date = date("Y-m-d",strtotime($postDataInput['approval_date_scb']));
				}
				$entry_objUpdate->application_date = date("Y-m-d",strtotime($postDataInput['Current_Queue_Date_scb']));
				
				$entry_objUpdate->emp_id = $postDataInput['NBO_scb'];
				$entry_objUpdate->company_name_scb = $postDataInput['company_name_scb'];
				$entry_objUpdate->aloc_non_aloc_scb = $postDataInput['aloc_non_aloc_scb'];
				$entry_objUpdate->pw_id_scb = $postDataInput['pw_id_scb'];
				$entry_objUpdate->Ageing_scb = $postDataInput['Ageing_scb'];
				$entry_objUpdate->Card_Type_scb = $postDataInput['Card_Type_scb'];
				$entry_objUpdate->team_company = $postDataInput['Team_scb'];
				$entry_objUpdate->remarks = $postDataInput['Comments_scb'];
				$entry_objUpdate->customer_name = $postDataInput['customer_name_scb'];
				$entry_objUpdate->customer_mobile = $postDataInput['customer_mobile_scb'];
			
				
				
				$entry_objUpdate->save();
				$insertID = $entry_objUpdate->id;
				/*
				*parent entry 
				*end code
				*/
				
				
				/*
				*child entry 
				*start code
				*/
				
				foreach($postDataInput as $key=>$value)
				{
					$existChild = SCBDepartmentFormChildEntry::where("parent_id",$postData['parent_id'])->where("attribute_code",$key)->first();
					if($existChild != '')
					{
					$child_obj = SCBDepartmentFormChildEntry::find($existChild->id);
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = 3;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
					}
					else
					{
						$child_obj = new SCBDepartmentFormChildEntry();
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = 3;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
					}
				}
				
				/*
				*child entry 
				*end code
				*/
				$request->session()->flash('message','Record Updated Successfully.');
				return redirect('ScbCardsManagement');
				
	 }
	 
	 
	 public function viewPanelAsperFileSourceSCB($parent_id=NULL,$form_id=NULL)
		{
			$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
			  $DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
			  $masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
			  $DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
			  $FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

			  $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

			  $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

			  $departmentFormParentDetails = DB::table('scb_department_form_parent_entry')->where('id', $parent_id)->first();

			  $departmentFormChildDetails = DB::table('scb_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

			  $Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
			
			  return view("Banks/SCB/viewPanelAsperFileSourceSCB",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
		}
		
		
public function exportDocReportSCBCardsFinalReport(Request $request)
	{	
			$requestPost = $request->input();
			$parameters = $request->input(); 
			
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Internal_MIS_SCB_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:P1');
			$sheet->setCellValue('A1', 'Internal MIS SCB Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Id'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Employee Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Current Queue Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Approval Date'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Agency Reference No'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Card Type'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('PW ID'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Company Name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('ALOC / NON ALOC'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Ageing'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Status'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Comments'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Customer Name'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Customer Mobile'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  SCBDepartmentFormParentEntry::where("id",$sid)->first();
				
				
				 $indexCounter++; 

				
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->emp_id)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $this->getEmployeeName($mis->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, ($mis->application_date?date('d-m-Y',strtotime($mis->application_date)):'00-00-0000'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, ($mis->approved_date?date('d-m-Y',strtotime($mis->approved_date)):'00-00-0000'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->ref_no)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->Card_Type_scb)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->pw_id_scb)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->company_name_scb)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->aloc_non_aloc_scb)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->Ageing_scb)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->form_status)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->remarks)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->customer_name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->customer_mobile)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'P'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:P1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','P') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="SCB-Inernal";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}


























	public function loadBankContentsSCBCardCurrentMonthData(Request $request)
	{
		 //$request->session()->put('paginationValue','');
		$form_id = 3;
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

		
		

		

		/* if(@$request->session()->get('form_status') != '')
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
		} */
		
		if(@$request->session()->get('master_scb_search_internal') != '' && @$request->session()->get('master_scb_search_internal') == 2)
		{
			if(@$request->session()->get('ref_no_SCB_master') != '')
				{
					$refNO = $request->session()->get('ref_no_SCB_master');
					
				
					$whereRaw .= " AND ref_no like '%".$refNO."%'";	
					
					
				}
				
				
				
				if(@$request->session()->get('team_SCB_master') != '')
				{
					$teamL = $request->session()->get('team_SCB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND team IN (".$teamstr.")";	
					
					
				}
				
				
				
				if(@$request->session()->get('emp_id_SCB_master') != '')
				{
					$empIds = $request->session()->get('emp_id_SCB_master');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_SCB_master') != '')
				{
					$start_date_application_SCB_internal = $request->session()->get('start_date_application_SCB_master');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					$searchValues['start_date_application_SCB_master'] = $start_date_application_SCB_internal;			
				}

				if($request->session()->get('end_date_application_SCB_master') != '')
				{
					$end_date_application_SCB_internal = $request->session()->get('end_date_application_SCB_master');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					$searchValues['end_date_application_SCB_master'] = $end_date_application_SCB_internal;			
				}
		}
		else
		{
				if(@$request->session()->get('ref_no_SCB_internal_CurrentMonth') != '')
				{
					$refNO = $request->session()->get('ref_no_SCB_internal_CurrentMonth');
					
				
					$whereRaw .= " AND ref_no like '%".$refNO."%'";	
					
					
				}
				if(@$request->session()->get('form_status_SCB_internal_CurrentMonth') != '')
				{
					$status = $request->session()->get('form_status_SCB_internal_CurrentMonth');
					$strStatus = '';
					foreach($status  as $s)
					{
						if($strStatus == '')
						{
							$strStatus = "'".$s."'";
						}
						else
						{
							$strStatus = $strStatus.",'".$s."'";
						}
					}
				
					$whereRaw .= " AND form_status IN (".$strStatus.")";	
					
					
				}
				
				
				if(@$request->session()->get('team_SCB_internal_CurrentMonth') != '')
				{
					$teamL = $request->session()->get('team_SCB_internal_CurrentMonth');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND team IN (".$teamstr.")";	
					
					
				}
				
				if(@$request->session()->get('emp_id_SCB_internal_CurrentMonth') != '')
				{
					$empIds = $request->session()->get('emp_id_SCB_internal_CurrentMonth');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				
				
				if(@$request->session()->get('card_type_SCB_internal_CurrentMonth') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_type_SCB_internal_CurrentMonth');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND Card_Type_scb IN (".$cardTypeInterbalstr.")";	
					
					
				}
				
				if(@$request->session()->get('pwId_SCB_internal_CurrentMonth') != '')
				{
					$pwidInterbalL = $request->session()->get('pwId_SCB_internal_CurrentMonth');
					$pwidInterbalstr = '';
					foreach($pwidInterbalL  as $CY)
					{
						if($pwidInterbalstr == '')
						{
							$pwidInterbalstr = "'".$CY."'";
						}
						else
						{
							$pwidInterbalstr = $pwidInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND pw_id_scb IN (".$pwidInterbalstr.")";	
					
					
				}
				if(@$request->session()->get('submission_type_internal_CurrentMonth') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_CurrentMonth');
					$submission_type_internalStr = '';
					foreach($submission_type_internalL  as $ST)
					{
						if($submission_type_internalStr == '')
						{
							$submission_type_internalStr = "'".$ST."'";
						}
						else
						{
							$submission_type_internalStr = $submission_type_internalStr.",'".$ST."'";
						}
					}
				
					$whereRaw .= " AND missing_internal IN (".$submission_type_internalStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_SCB_internal_CurrentMonth') != '')
				{
					$start_date_application_SCB_internal_CurrentMonth = $request->session()->get('start_date_application_SCB_internal_CurrentMonth');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal_CurrentMonth))."'";
					$searchValues['start_date_application_SCB_internal_CurrentMonth'] = $start_date_application_SCB_internal_CurrentMonth;			
				}

				if($request->session()->get('end_date_application_SCB_internal_CurrentMonth') != '')
				{
					$end_date_application_SCB_internal_CurrentMonth = $request->session()->get('end_date_application_SCB_internal_CurrentMonth');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal_CurrentMonth))."'";
					$searchValues['end_date_application_SCB_internal_CurrentMonth'] = $end_date_application_SCB_internal;			
				}
				
				if($request->session()->get('start_date_application_SCB_internal_a_CurrentMonth') != '')
				{
					$start_date_application_SCB_internal_a_CurrentMonth = $request->session()->get('start_date_application_SCB_internal_a_CurrentMonth');			
					$whereRaw .= " AND approved_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal_a_CurrentMonth))."'";
					$searchValues['start_date_application_SCB_internal_a_CurrentMonth'] = $start_date_application_SCB_internal_a_CurrentMonth;			
				}

				if($request->session()->get('end_date_application_SCB_internal_a_CurrentMonth') != '')
				{
					$end_date_application_SCB_internal_a_CurrentMonth = $request->session()->get('end_date_application_SCB_internal_a_CurrentMonth');			
					$whereRaw .= " AND approved_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal_a_CurrentMonth))."'";
					$searchValues['end_date_application_SCB_internal_a_CurrentMonth'] = $end_date_application_SCB_internal_a_CurrentMonth;			
				}
		
		}
		
		//$endDate = date("Y-m-d");
		//$startDate = date("Y").'-'.date("m").'-'.'01';

		$departmentFormParentTotal = DB::table('scb_department_form_parent_entry')->whereRaw($whereRaw)->orderby('application_date','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('scb_department_form_parent_entry')->whereRaw($whereRaw)->orderby('application_date','DESC')->paginate($paginationValue);

		

		

		
		/*
		*employee Id
		*/
			$employeeIdList = SCBDepartmentFormParentEntry::select('emp_id')->where("form_id",3)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		/*
		*status
		*/
			$formStatusData = SCBDepartmentFormParentEntry::select('form_status')->where("form_id",3)->get()->unique('form_status');
			
		/*
		*status
		*/
		
		/*
		*Team
		*/
			$teamData = SCBDepartmentFormParentEntry::select('team')->where("form_id",3)->get()->unique('team');
			
		/*
		*Team
		*/
		
		
		/*
		*channel_cbd
		*/
			$channel_cbd = array();
			
		/*
		*channel_cbd
		*/
		
		/*
		*status_AECB_cbd
		*/
			$status_AECB_cbd = array();
			
		/*
		*status_AECB_cbd
		*/
		
		
		/*
		*card_type_cbd
		*/
			$card_type_scb =SCBDepartmentFormParentEntry::select('Card_Type_scb')->where("form_id",3)->get()->unique('Card_Type_scb');
			
		/*
		*card_type_cbd
		*/
		
		
		
		/*
		*card_type_cbd
		*/
			$pwIdList =SCBDepartmentFormParentEntry::select('pw_id_scb')->where("form_id",3)->get()->unique('pw_id_scb');
			
		/*
		*card_type_cbd
		*/
		
		/*
		*complete Status CBD
		*/
		
			$masterAttributesStatus = MasterAttribute::select("option_values")->where("attribute_code","status_scb")->first();
		/*
		*complete Status CBD
		*/
		
        return view("Banks/SCB/loadBankContentsSCBCardHistoric",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','employeeIdList','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_scb','masterAttributesStatus','pwIdList'));
	}


	public function searchSCBInternalInnerCurrentMonthFilter(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$form_status = '';
				$ref_no = '';
				$emp_id = '';
				$pwid_scb = '';
				$start_date_application_a = '';
				$end_date_application_a = '';
				
				$card_type_scb = '';
				$submission_type_inner = '';
				
			
				
				if(isset($requestParameters['card_type_scb_CurrentMonth']))
				{
					$card_type_scb = @$requestParameters['card_type_scb_CurrentMonth'];
				}
				if(isset($requestParameters['pwid_scb_CurrentMonth']))
				{
					$pwid_scb = @$requestParameters['pwid_scb_CurrentMonth'];
				}

				if(@isset($requestParameters['ref_no_CurrentMonth']))
				{
					$ref_no = @$requestParameters['ref_no_CurrentMonth'];
				}

				if(isset($requestParameters['team_CurrentMonth']))
				{
					$team = @$requestParameters['team_CurrentMonth'];
				}

				if(isset($requestParameters['form_status_CurrentMonth']))
				{
					$form_status = @$requestParameters['form_status_CurrentMonth'];
				}
				
				if(isset($requestParameters['emp_id_CurrentMonth']))
				{
					$emp_id = @$requestParameters['emp_id_CurrentMonth'];
				}

				if(isset($requestParameters['start_date_application_CurrentMonth']))
				{
					$start_date_application = @$requestParameters['start_date_application_CurrentMonth'];
				}
				if(isset($requestParameters['end_date_application_CurrentMonth']))
				{
					$end_date_application = @$requestParameters['end_date_application_CurrentMonth'];
				}
				
				if(isset($requestParameters['start_date_application_a_CurrentMonth']))
				{
					$start_date_application_a = @$requestParameters['start_date_application_a_CurrentMonth'];
				}
				if(isset($requestParameters['end_date_application_a_CurrentMonth']))
				{
					$end_date_application_a = @$requestParameters['end_date_application_a_CurrentMonth'];
				}
				if(isset($requestParameters['submission_type_inner_CurrentMonth']))
				{
					$submission_type_inner = @$requestParameters['submission_type_inner_CurrentMonth'];
				}
				$request->session()->put('master_scb_search_internal','');
				$request->session()->put('ref_no_SCB_internal_CurrentMonth',$ref_no);
				$request->session()->put('form_status_SCB_internal_CurrentMonth',$form_status);
				$request->session()->put('team_SCB_internal_CurrentMonth',$team);
				$request->session()->put('emp_id_SCB_internal_CurrentMonth',$emp_id);
				$request->session()->put('start_date_application_SCB_internal_CurrentMonth',$start_date_application);
				$request->session()->put('end_date_application_SCB_internal_CurrentMonth',$end_date_application);
				$request->session()->put('start_date_application_SCB_internal_a_CurrentMonth',$start_date_application_a);
				$request->session()->put('end_date_application_SCB_internal_a_CurrentMonth',$end_date_application_a);
				
				
				$request->session()->put('card_type_SCB_internal_CurrentMonth',$card_type_scb);
				$request->session()->put('pwId_SCB_internal_CurrentMonth',$pwid_scb);
				$request->session()->put('submission_type_internal_CurrentMonth',$submission_type_inner);
				return redirect("loadBankContentsSCBCardCurrentMonth");
	}
	
	
	public function resetLoginInnerSCBInternalCurrentMonthFilter(Request $request)
	{
			$request->session()->put('ref_no_SCB_internal_CurrentMonth','');
				$request->session()->put('form_status_SCB_internal_CurrentMonth','');
				$request->session()->put('team_SCB_internal_CurrentMonth','');
				$request->session()->put('emp_id_SCB_internal_CurrentMonth','');
				$request->session()->put('start_date_application_SCB_internal_CurrentMonth','');
				$request->session()->put('end_date_application_SCB_internal_CurrentMonth','');
			
				$request->session()->put('start_date_application_SCB_internal_a_CurrentMonth','');
				$request->session()->put('end_date_application_SCB_internal_a_CurrentMonth','');
				$request->session()->put('card_type_SCB_internal_CurrentMonth','');
				$request->session()->put('pwId_SCB_internal_CurrentMonth','');
				$request->session()->put('submission_type_internal_CurrentMonth','');
				
				$request->session()->put('master_scb_search_internal',2);
				return redirect("loadBankContentsSCBCardCurrentMonth");
	}












	// new code for cron for SCb Start (18-07-2024)


	public function preMasterPayoutCronSCBBackupOLD(Request $request)
	{
		// $toDate = date("Y-m-d");
		// $fromDate = date("Y").'-'.date("m").'-'.'01';

		$dateC = date("Y-m-d");
		
		$fromDate = '2024-06-01';
		$toDate = '2024-06-30';

		$salesTime = date("n-Y", strtotime($fromDate));	
		$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";

		

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->groupby('emp_id')->get();

		$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->where('emp_id','102027')->get();
		$successarr = array();
		$tlName =array();

		//echo "<pre>";
		//return $empData;

		foreach($empData as $emp)
		{
			$cardData = SCBBankMis::where('Agency_Reference',$emp->ref_no)->where('Status','Approved')->first();

			if($cardData)
			{
				$successarr[$emp->emp_id][] = $cardData->id;
				$tlName[] = $cardData->TL;
			}
		}
		// print_r($successarr);		
		//exit;

		foreach($successarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($tlName); 
			$acheivedCount = count($value); 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}
		
		
		//print_r($activeemp);

		return response()->json(['success'=>'SCB Data Added Successfully for the month of '.$salesTime.'.']);
	}




	public function preMasterPayoutCronSCB(Request $request)
	{
		// $toDate = date("Y-m-d");
		// $fromDate = date("Y").'-'.date("m").'-'.'01';

		$dateC = date("Y-m-d");
		
		$fromDate = '2024-06-01';
		$toDate = '2024-06-30';

		$salesTime = date("n-Y", strtotime($fromDate));	
		$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";

		

		$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->groupby('emp_id')->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->where('emp_id','102027')->get();
		$successarr = array();
		$failedarr = array();
		

		// echo "<pre>";
		// print_r($empData);
		// exit;

		foreach($empData as $emp)
		{
			$cardData = SCBBankMis::where('Agency_Reference',$emp->ref_no)->where('Status','Approved')->first();

			if($cardData)
			{
				$successarr[$emp->emp_id][] = $cardData->TL;
				//$successtlName[] = $cardData->TL;
			}
			else
			{
				$failedarr[$emp->emp_id][] = $emp->team;
				//$failedtlName[] = $emp->team;
			}
		}

		// print_r($successarr);
		// print_r($failedarr);
		// exit;
		

		foreach($successarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = count($value); 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		foreach($failedarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = 0; 
			
			$agentData = DB::table('master_payout_pre')->where('agent_id', $agentid)->where('sales_time', $salesTime)->first();
			
			if(!$agentData)
			{
				$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
				if($empattributesMod)
				{
					$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
				}
				else
				{
					$empdoj= NULL;
				}
	
				if(!empty($empattributesMod)){				 
				$createdAT = $empattributesMod->attribute_values;
				}else{
				$createdAT=0;
				}
				
				$doj = str_replace("/","-",$createdAT);				
				$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
				
				$empInfo = Employee_details::where('emp_id',$agentid)->first();
	
				if($empInfo)
				{
					$empDept = $empInfo->dept_id;
					$empName = $empInfo->emp_name;
				}
				else
				{
					$empDept = NULL;
					$empName = NULL;
				}
	
	
				DB::table('master_payout_pre')->insert(
					array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
				);	
			}
			
			
			
			

					
		}

		
		
		//print_r($activeemp);

		return response()->json(['success'=>'SCB Data Added Successfully for the month of '.$salesTime.'.']);
	}


	public function preMasterPayoutCronSCBNEWONE(Request $request)
	{
		// $toDate = date("Y-m-d");
		// $fromDate = date("Y").'-'.date("m").'-'.'01';

		$dateC = date("Y-m-d");
		
		$fromDate = '2024-06-01';
		$toDate = '2024-06-30';

		$salesTime = date("n-Y", strtotime($fromDate));	
		$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";

		

		$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->groupby('emp_id')->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->where('emp_id','102027')->get();
		$successarr = array();
		$failedarr = array();
		$successtlName =array();
		$failedtlName =array();

		echo "<pre>";
		//print_r($empData);
		//exit;

		foreach($empData as $emp)
		{
			echo $emp->emp_id;
			echo "<br/>";
			foreach($emp as $empData)
			{
				//echo $empData; echo "<br/>";
				//$empData = SCBDepartmentFormParentEntry::where('emp_id',)->get();

			}
			
			
			
			
			// $cardData = SCBBankMis::where('Agency_Reference',$emp->ref_no)->where('Status','Approved')->first();

			// if($cardData)
			// {
			// 	$successarr[$emp->emp_id][] = $cardData->TL;
			// 	//$successtlName[] = $cardData->TL;
			// }
			// else
			// {
			// 	$failedarr[$emp->emp_id][] = $emp->team;
			// 	//$failedtlName[] = $emp->team;
			// }
		}
		//print_r($successarr);		
		// print_r($successtlName);
		exit;

		foreach($successarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = count($value); 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		foreach($failedarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = 0; 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		
		
		//print_r($activeemp);

		return response()->json(['success'=>'SCB Data Added Successfully for the month of '.$salesTime.'.']);
	}



	// new code for cron for SCb End (18-07-2024)

}
