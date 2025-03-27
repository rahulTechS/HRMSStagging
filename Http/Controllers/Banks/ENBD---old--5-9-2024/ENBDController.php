<?php

namespace App\Http\Controllers\Banks\ENBD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
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
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Bank\CBD\BankCBDMTD;

use App\Models\Bank\EIB\EibImportFile;
use App\Models\Bank\EIB\EibBankMis;


use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\Attribute\EIBDepartmentFormChildEntry;


use App\Models\Attribute\ENBDDepartmentFormEntry;
use App\Models\Attribute\ENBDDepartmentFormChildEntry;
use Illuminate\Support\Facades\Validator;
use App\Models\MIS\MonthlyEnds;
use App\Models\MIS\CurrentActivity;
use App\Models\MIS\ENBDCardsMisReport;
use App\Models\MIS\MainMisReportTab;
use App\Models\Bank\ENBD\ENBDDataCut;
use App\Models\MIS\MainMisReport;





use Session;

class ENBDController extends Controller
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
 
 protected function getEmployeeNamelocal($empid)
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
 public function updateCodeAgentCBD(Request $request)
 {
	 $empid = $request->empid;
	 $empName = Employee_details::select("source_code")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			echo $empName->source_code;
		}
		else
		{
			echo  '';
		}
		exit;
 }
 
 public static function getEmployeeNameWithCode($empid)
 {
	 if($empid != '' && $empid != NULL)
	 {
		$empName = Employee_details::select("emp_name","source_code")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			return $empName->emp_name.'('.$empName->source_code.')';
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
 
 public static function getEmployeeNameByCode($empCode)
 {
	 
	 if($empCode != '' && $empCode != NULL)
	 {
		$empName = Employee_details::select("emp_name")->where("source_code",$empCode)->first();
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

 


 
 
 public function addCBDEntryPost(Request $request)
 {
			$postData = $request->input();
			
			$postDataInput = $postData['attribute_value'];
			$entry_obj = new DepartmentFormEntry();			
	
			/*
			*parent entry 
			*start code
			*/
			$entry_obj->ref_no = $postDataInput['ref_no'];
			$entry_obj->form_id = 2;
			$entry_obj->form_title = 'CBD Internal MIS';
			$entry_obj->form_status = $postDataInput['status_cbd'];
			$entry_obj->team = $postDataInput['sm_name_cbd'];
			$entry_obj->customer_name = $postDataInput['customer_name'];
			$entry_obj->customer_mobile = $postDataInput['customer_mobile'];
			$entry_obj->remarks = $postDataInput['CBD_remark'];
			$entry_obj->agent_code = $postDataInput['agent_code_cbd'];
			
				$sourceCode = $postDataInput['agent_code_cbd'];
				$empMod = Employee_details::select("emp_id")->where("source_code",$sourceCode)->first();
				if($empMod != '')
				{
					$entry_obj->emp_id = $empMod->emp_id;
				}
				
			
			
			$entry_obj->channel_cbd = $postDataInput['channel_cbd'];
			$entry_obj->status_AECB_cbd = $postDataInput['aecb_status'];
			$entry_obj->card_type_cbd = $postDataInput['card_type_cbd'];
			$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['app_date']));
			$entry_obj->status = 1;
			$entry_obj->cbd_marging_status = 1;	
			$entry_obj->missing_internal = 3;	
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
			$child_obj = new DepartmentFormChildEntry();
			foreach($postDataInput as $key=>$value)
			{
				$child_obj = new DepartmentFormChildEntry();
				$child_obj->parent_id = $insertID;
				$child_obj->form_id = 2;
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
			return redirect('cbdCardsManagement');
	 
 }
 
 
 public function setPaginationValueCBDCard(Request $request)
 {
	 $offset = $request->offset;
	 $request->session()->put('paginationValue',$offset);
	 return redirect('loadBankContentsCBDCard');
 }
 
 
	
	
	public function searchCBDMTDInner(Request $request)
	{
				$requestParameters = $request->input();
				
				$ref_no_mtd = '';
				$sm_manager_mtd = '';
				$employee_id_mtd = '';
				$start_cd_opn = '';
				$end_cd_opn = '';
				$submission_type_mtd = '';
				
				
				if(isset($requestParameters['ref_no_mtd']))
				{
					$ref_no_mtd = @$requestParameters['ref_no_mtd'];
				}
				if(isset($requestParameters['sm_manager_mtd']))
				{
					$sm_manager_mtd = @$requestParameters['sm_manager_mtd'];
				}
				if(isset($requestParameters['employee_id_mtd']))
				{
					$employee_id_mtd = @$requestParameters['employee_id_mtd'];
				}
				if(isset($requestParameters['start_cd_opn']))
				{
					$start_cd_opn = @$requestParameters['start_cd_opn'];
				}
				if(isset($requestParameters['end_cd_opn']))
				{
					$end_cd_opn = @$requestParameters['end_cd_opn'];
				}
				
				if(isset($requestParameters['submission_type_mtd']))
				{
					$submission_type_mtd = @$requestParameters['submission_type_mtd'];
				}
			
				$request->session()->put('master_cbd_search_MTD','');
				$request->session()->put('ref_no_CBD_mtd',$ref_no_mtd);
				$request->session()->put('smManager_CBD_mtd',$sm_manager_mtd);
				$request->session()->put('employee_id_CBD_mtd',$employee_id_mtd);
				$request->session()->put('start_CD_OPN_DT_CBD_bank',$start_cd_opn);
				$request->session()->put('end_CD_OPN_DT_CBD_bank',$end_cd_opn);
				$request->session()->put('submission_type_CBD_MTD',$submission_type_mtd);
			
				return redirect("loadBankContentsCBDCardBankSideMTD");
	}
	

	
	
	public function resetCBDMTDInnerFilter(Request $request)
	{
				$request->session()->put('ref_no_CBD_mtd','');
				$request->session()->put('smManager_CBD_mtd','');
				$request->session()->put('employee_id_CBD_mtd','');
				$request->session()->put('start_CD_OPN_DT_CBD_bank','');
				$request->session()->put('end_CD_OPN_DT_CBD_bank','');
				$request->session()->put('submission_type_CBD_MTD','');
				$request->session()->put('master_cbd_search_MTD',2);
				return redirect("loadBankContentsCBDCard");
	}
	
	
	
	
	
	
	
	
	
	public static function importCSVENBDInternal()
	{

		$file = public_path('uploads/formFiles/enbd_internal_import.csv');
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
				exit; */
				/*
				*check for existance
				*start code
				*/
					$appNo = $line[7];
					if($appNo != '')
					{
					$existanceCheck = ENBDDepartmentFormEntry::where("application_no",$appNo)->first();
				/*
				*check for existance
				*start code
				*/
				/*
				*import data
				*/
						if($existanceCheck != '')
						{
							$entry_obj = ENBDDepartmentFormEntry::find($existanceCheck->id);	
						}
						else
						{
							$entry_obj = new ENBDDepartmentFormEntry();	
							//$entry_obj->cbd_marging_status = 1;			
						}
						/*
						*parent entry 
						*start code
						*/
						$entry_obj->application_no = $line[7];
						$entry_obj->form_id = 7;
						$entry_obj->form_title = 'ENBD Internal MIS Tab';
						//$entry_obj->form_status = trim($line[16]);
						$entry_obj->submission_date = date("Y-m-d",strtotime($line[1]));
						$entry_obj->application_type = trim($line[4]);
						$entry_obj->product_type = trim($line[6]);
						$entry_obj->bidaya = trim($line[8]);
						$entry_obj->current_activity = trim($line[9]);
						$entry_obj->customer_name = trim($line[11]);

						$entry_obj->dob = trim($line[12]);
						$entry_obj->nationality = trim($line[17]);
						$entry_obj->designation = trim($line[24]);
						$entry_obj->company_name = trim($line[23]);
						

						$entry_obj->mobile = trim($line[13]);
						$entry_obj->tl_name = trim($line[15]);
						$entry_obj->se_name = trim($line[16]);
						$entry_obj->salary = trim($line[18]);
						$entry_obj->product_name = trim($line[19]);
						$entry_obj->enbd_status = trim($line[27]);
						$entry_obj->status = trim($line[22]);
						$entry_obj->submission_type = 'Tab';						
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
								$existAttrMod = ENBDDepartmentFormChildEntry::where("parent_id",$insertID)->get();
								foreach($existAttrMod as $attr)
								{
									$attr->delete();
								}
							}
							
						
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'applicaion_id_enbd';
							$child_obj->attribute_value = $line[7];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'application_type_enbd';
							$child_obj->attribute_value = $line[4];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'product_enbd';
							$child_obj->attribute_value = $line[19];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'company_enbd';
							$child_obj->attribute_value = $line[23];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'customer_name_enbd';
							$child_obj->attribute_value = $line[11];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'customer_mobile_enbd';
							$child_obj->attribute_value = $line[13];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'salary_enbd';
							$child_obj->attribute_value = $line[18];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'nationality_enbd';
							$child_obj->attribute_value = $line[17];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'dob_enbd';
							$child_obj->attribute_value = $line[12];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'designation_enbd';
							$child_obj->attribute_value = $line[24];
							$child_obj->status = 1;
							$child_obj->save();
						
						
						
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'current_activity_enbd';
							$child_obj->attribute_value = $line[9];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'ale_nale_enbd';
							$child_obj->attribute_value = $line[25];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'submission_type_enbd';
							$child_obj->attribute_value = 'Tab';
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'product_type_enbd';
							$child_obj->attribute_value = $line[6];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'bidaya_enbd';
							$child_obj->attribute_value = $line[8];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'data_cut_enbd';
							$child_obj->attribute_value = $line[10];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'team_leader_enbd';
							$child_obj->attribute_value = $line[15];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'se_name_enbd';
							$child_obj->attribute_value = $line[16];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'card_type_enbd';
							$child_obj->attribute_value = $line[20];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'submission_date_enbd';
							$child_obj->attribute_value = $line[1];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'customer_office_number_enbd';
							$child_obj->attribute_value = $line[14];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'office_number_enbd';
							$child_obj->attribute_value = $line[14];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'supplementary_enbd';
							$child_obj->attribute_value = $line[21];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'status_enbd';
							$child_obj->attribute_value = $line[22];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'gcl_enbd';
							$child_obj->attribute_value = $line[26];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'enbd_status_enbd';
							$child_obj->attribute_value = $line[27];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 7;
							$child_obj->attribute_code = 'pfot_enbd';
							$child_obj->attribute_value = $line[5];
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




	public static function importCSVENBDInternalNEWNEW()
	{

		$file = public_path('uploads/formFiles/enbd_internal_import_physical.csv');
		// Open uploaded CSV file with read-only mode
            $csvFile = fopen($file, 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
			$count = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE)
			{				
				// echo "<pre>";
				// print_r($line);
				// exit; 
				/*
				*check for existance
				*start code
				*/
					$appNo = $line[5];
					if($appNo != '')
					{
					$existanceCheck = ENBDDepartmentFormEntry::where("application_no",$appNo)->first();
				/*
				*check for existance
				*start code
				*/
				/*
				*import data
				*/
						if($existanceCheck != '')
						{
							$entry_obj = ENBDDepartmentFormEntry::find($existanceCheck->id);	
						}
						else
						{
							$entry_obj = new ENBDDepartmentFormEntry();	
							//$entry_obj->cbd_marging_status = 1;			
						}
						/*
						*parent entry 
						*start code
						*/
						$entry_obj->application_no = $line[5];
						$entry_obj->form_id = 6;
						$entry_obj->form_title = 'ENBD Internal MIS Manual';
						//$entry_obj->form_status = trim($line[16]);
						$entry_obj->submission_date = date("Y-m-d",strtotime($line[3]));
						$entry_obj->application_type = trim($line[4]);
						//$entry_obj->product_type = trim($line[6]);
						//$entry_obj->bidaya = trim($line[8]);
						$entry_obj->current_activity = trim($line[6]);
						$entry_obj->customer_name = trim($line[20]);

						$entry_obj->dob = date("Y-m-d",strtotime($line[28]));
						$entry_obj->nationality = trim($line[26]);
						$entry_obj->designation = trim($line[30]);
						$entry_obj->company_name = trim($line[8]);
						

						$entry_obj->mobile = trim($line[21]);
						$entry_obj->tl_name = trim($line[1]);
						$entry_obj->se_name = trim($line[2]);
						$entry_obj->salary = trim($line[25]);
						$entry_obj->product_name = trim($line[16]);
						$entry_obj->account_number = trim($line[13]);
						$entry_obj->account_status = trim($line[12]);
						$entry_obj->submission_type = 'Manual';	
						$entry_obj->remarks = trim($line[19]);
						$entry_obj->monthly_ends = trim($line[7]);	
						$entry_obj->ale_nale_status = trim($line[10]);	
										
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
								$existAttrMod = ENBDDepartmentFormChildEntry::where("parent_id",$insertID)->get();
								foreach($existAttrMod as $attr)
								{
									$attr->delete();
								}
							}
							
						
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'customer_name_enbd';
							$child_obj->attribute_value = $line[20];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'customer_mobile_enbd';
							$child_obj->attribute_value = $line[21];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'customer_office_number_enbd';
							$child_obj->attribute_value = $line[22];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'customer_email_enbd';
							$child_obj->attribute_value = $line[23];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'salaried_status_enbd';
							$child_obj->attribute_value = $line[24];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'salary_enbd';
							$child_obj->attribute_value = $line[25];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'nationality_enbd';
							$child_obj->attribute_value = $line[26];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'passport_enbd';
							$child_obj->attribute_value = $line[27];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'designation_enbd';
							$child_obj->attribute_value = $line[30];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'dob_enbd';
							$child_obj->attribute_value = date("Y-m-d",strtotime($line[28]));
							$child_obj->status = 1;
							$child_obj->save();
						
						
						
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'eida_enbd';
							$child_obj->attribute_value = $line[32];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'reference_mobile_enbd';
							$child_obj->attribute_value = $line[34];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'reference_enbd';
							$child_obj->attribute_value = $line[33];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'pre_calling_enbd';
							$child_obj->attribute_value = date("Y-m-d",strtotime($line[35]));
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'ev_done_enbd';
							$child_obj->attribute_value = $line[36];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'type_of_incomeproof_enbd';
							$child_obj->attribute_value = $line[37];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'visa_expiry_enbd';
							$child_obj->attribute_value = date("Y-m-d",strtotime($line[29]));
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'applicaion_id_enbd';
							$child_obj->attribute_value = $line[5];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'application_type_enbd';
							$child_obj->attribute_value = $line[4];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'submission_date_enbd';
							$child_obj->attribute_value = date("Y-m-d",strtotime($line[3]));
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'product_enbd';
							$child_obj->attribute_value = $line[16];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'card_enbd';
							$child_obj->attribute_value = $line[17];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'company_enbd';
							$child_obj->attribute_value = $line[8];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'company_name_visa_enbd';
							$child_obj->attribute_value = $line[9];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'account_no_enbd';
							$child_obj->attribute_value = $line[13];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'account_status_enbd';
							$child_obj->attribute_value = $line[12];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'iban_enbd';
							$child_obj->attribute_value = $line[14];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'remarks_enbd';
							$child_obj->attribute_value = $line[19];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'ale_nale_enbd';
							$child_obj->attribute_value = $line[10];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'current_activity_enbd';
							$child_obj->attribute_value = $line[6];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'monthly_ends_enbd';
							$child_obj->attribute_value = $line[7];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'load_source_enbd';
							$child_obj->attribute_value = $line[15];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'team_leader_enbd';
							$child_obj->attribute_value = $line[1];
							$child_obj->status = 1;
							$child_obj->save(); 


							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'se_name_enbd';
							$child_obj->attribute_value = $line[2];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new ENBDDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 6;
							$child_obj->attribute_code = 'submission_type_enbd';
							$child_obj->attribute_value = 'Manual';
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
	
	
	public function exportDocReportinternalMisCBDCards(Request $request)
	{
		$requestPost = $request->input();
		 $parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Internal_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', 'Internal MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent Code'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Agent Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Application Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Application Reference No'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Customer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Mobile No'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Employer Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Card Type'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Bureau Score'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('App Score'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('MOB'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Declared salary'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Eligible Income'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('AECB Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Channel'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Status'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('CBD  Remark'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('SUCB Remarks'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  DepartmentFormEntry::where("id",$sid)->first();
				
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				$agentNameMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_name_cbd")->first();
					$agentName = '';
					if($agentNameMod != '')
					{
						$agentName = $agentNameMod->attribute_value;
					}
				/*
				*agent name
				*/
				
				/*
				*agent code
				*/			
				$agentCodeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_code_cbd")->first();
					$agentCode = '';
					if($agentCodeMod != '')
					{
						$agentCode = $agentCodeMod->attribute_value;
					}
				/*
				*agent code
				*/
				
				
				/*
				*employeer name
				*/			
				$employeeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","employer_name")->first();
					$employeerName = '';
					if($employeeMod != '')
					{
						$employeerName = $employeeMod->attribute_value;
					}
				/*
				*employeer name
				*/
				
				/*
				*Card Type
				*/			
				$cardTypeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_type_cbd")->first();
					$cardType = '';
					if($cardTypeMod != '')
					{
						$cardType = $cardTypeMod->attribute_value;
					}
				/*
				*Card Type
				*/
				
				/*
				*bureau_score
				*/			
				$bureauMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","bureau_score")->first();
					$bureauS = '';
					if($bureauMod != '')
					{
						$bureauS = $bureauMod->attribute_value;
					}
				/*
				*bureau_score
				*/
				
				/*
				*app_score
				*/			
				$appSMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","app_score")->first();
					$appS = '';
					if($appSMod != '')
					{
						$appS = $appSMod->attribute_value;
					}
				/*
				*app_score
				*/
				
				/*
				*MOB
				*/			
				$MOBMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","MOB")->first();
					$MOB = '';
					if($MOBMod != '')
					{
						$MOB = $MOBMod->attribute_value;
					}
				/*
				*MOB
				*/
				
				/*
				*declared_salary_cbd
				*/			
				$declared_salary_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","declared_salary_cbd")->first();
					$declared_salary_cbd = '';
					if($declared_salary_cbdMod != '')
					{
						$declared_salary_cbd = $declared_salary_cbdMod->attribute_value;
					}
				/*
				*declared_salary_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$eligible_income_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","eligible_income_cbd")->first();
					$eligible_income_cbd = '';
					if($eligible_income_cbdMod != '')
					{
						$eligible_income_cbd = $eligible_income_cbdMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$aecb_statusMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","aecb_status")->first();
					$aecb_status = '';
					if($aecb_statusMod != '')
					{
						$aecb_status = $aecb_statusMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$channel_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","channel_cbd")->first();
					$channel_cbd = '';
					if($channel_cbdMod != '')
					{
						$channel_cbd = $channel_cbdMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*status_cbd
				*/			
				$status_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","status_cbd")->first();
					$status_cbd = '';
					if($status_cbdMod != '')
					{
						$status_cbd = $status_cbdMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				
				/*
				*CBD_remark
				*/			
				$CBD_remarkMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","CBD_remark")->first();
					$CBD_remark = '';
					if($CBD_remarkMod != '')
					{
						$CBD_remark = $CBD_remarkMod->attribute_value;
					}
				/*
				*CBD_remark
				*/
				
				/*
				*status_cbd
				*/			
				$SUCB_remarksMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","SUCB_remarks")->first();
					$SUCB_remarks = '';
					if($SUCB_remarksMod != '')
					{
						$SUCB_remarks = $SUCB_remarksMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $agentName)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $agentCode)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->application_date)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->ref_no)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->customer_name)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->customer_mobile)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $employeerName)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $cardType)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $bureauS)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $appS)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $MOB)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $declared_salary_cbd)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $eligible_income_cbd)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $aecb_status)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $channel_cbd)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $status_cbd)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $CBD_remark)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $SUCB_remarks)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'T'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:T1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','T') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}
	
	
	public function updateFinalRemarksCBD(Request $request)
	{
	
		 $postParameters = $request->input();
		 $row_id = $postParameters['row_id'];
	  
	     $oldValue = DepartmentFormEntry::where("id",$row_id)->first()->remarks;
	 
	     $updateMod = DepartmentFormEntry::find($row_id);
	     $updateMod->remarks = $postParameters['remarks'];
		  if($updateMod->save())
		  {
			  $childData = DepartmentFormChildEntry::where("parent_id",$row_id)->where("attribute_code","remarks")->first();
			  if($childData != '')
			  {
				  $updateChild =DepartmentFormChildEntry::find($childData->id);
				   $updateChild->attribute_value = $postParameters['remarks'];
				   $updateChild->save();
				   
			  }
			  
			  /**
			  *log
			  */
			  $empsessionIdGet=$request->session()->get('EmployeeId');
			  $logAddMod = new MashreqCardsLogs();
			   $logAddMod->internal_mis_id = $row_id;
			   $logAddMod->value = 'remarks';
			   $logAddMod->old_value = $oldValue;
			   $logAddMod->new_value = $postParameters['remarks'];
			   $logAddMod->updated_by = $empsessionIdGet;
			   $logAddMod->save();
			  /**
			  *log
			  */
		  }
	echo "done";
	exit;
	}
	
	
	
	
	
	
	

	
	public static function getChannel($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","channel_cbd")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	public static function getCardType($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","card_type_cbd")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	public static function getAECBStatus($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","aecb_status")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	
	public function linkMISCBD(Request $request)
	{
		$whereRaw = " form_id=2 AND (status='1' OR status='2')";
						
					
				if(@$request->session()->get('team_CBD_master_links') != '')
				{
					$teamL = $request->session()->get('team_CBD_master_links');
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
				
				if(@$request->session()->get('rm_CBD_master_links') != '')
				{
					$rmL = $request->session()->get('rm_CBD_master_links');
					$rmLstr = '';
					foreach($rmL  as $lS)
					{
						if($rmLstr == '')
						{
							$rmLstr = "'".$lS."'";
						}
						else
						{
							$rmLstr = $rmLstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$rmLstr.")";	
					
					
				}
				
				
				if(@$request->session()->get('card_type_links') != '')
				{
					$card_type_linksL = $request->session()->get('card_type_links');
					$card_type_linksstr = '';
					foreach($card_type_linksL  as $lS)
					{
						if($card_type_linksstr == '')
						{
							$card_type_linksstr = "'".$lS."'";
						}
						else
						{
							$card_type_linksstr = $card_type_linksstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND card_type_cbd IN (".$card_type_linksstr.")";	
					
					
				}
				
				
				
				if($request->session()->get('start_date_application_CBD_master_links') != '')
				{
					$start_date_application_CBD_internal = $request->session()->get('start_date_application_CBD_master_links');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$searchValues['start_date_application_CBD_master_links'] = $start_date_application_CBD_internal;			
				}

				if($request->session()->get('end_date_application_CBD_master_links') != '')
				{
					$end_date_application_CBD_internal = $request->session()->get('end_date_application_CBD_master_links');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$searchValues['end_date_application_CBD_master_links'] = $end_date_application_CBD_internal;			
				}
		/* echo $whereRaw;exit; */
		$collectionAgentNameInternalMis = DepartmentFormEntry::where("cbd_marging_status",1)
		->whereRaw($whereRaw)
		->groupBy('emp_id')
		->selectRaw('count(*) as totalData, emp_id,agent_code')
		->get();
		
			
				$whereRaw1 = " ref_no != ''";
				if($request->session()->get('start_date_application_CBD_master_links') != '')
				{
					$start_date_application_CBD_internal = $request->session()->get('start_date_application_CBD_master_links');			
					$whereRaw1 .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$searchValues['start_date_application_CBD_master_links'] = $start_date_application_CBD_internal;			
				}

				if($request->session()->get('end_date_application_CBD_master_links') != '')
				{
					$end_date_application_CBD_internal = $request->session()->get('end_date_application_CBD_master_links');			
					$whereRaw1 .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$searchValues['end_date_application_CBD_master_links'] = $end_date_application_CBD_internal;			
				}
				
				if(@$request->session()->get('card_type_links') != '')
				{
					$card_type_linksL = $request->session()->get('card_type_links');
					$card_type_linksstr = '';
					foreach($card_type_linksL  as $lS)
					{
						if($card_type_linksstr == '')
						{
							$card_type_linksstr = "'".$lS."'";
						}
						else
						{
							$card_type_linksstr = $card_type_linksstr.",'".$lS."'";
						}
					}
				
					$whereRaw1 .= " AND card_type IN (".$card_type_linksstr.")";	
					
					
				}
				
				if(@$request->session()->get('rm_CBD_master_links') != '')
				{
					$rmL = $request->session()->get('rm_CBD_master_links');
					
					$rmLstr = '';
					foreach($rmL  as $lS)
					{
						/*
						*getsource code
						*/
						
						$empName = Employee_details::select("source_code")->where("emp_id",$lS)->first();
						if($empName->source_code != NULL && $empName->source_code != '')
						{
							/*
							*getsource code
							*/
							if($rmLstr == '')
							{
								$rmLstr = "'".$empName->source_code."'";
							}
							else
							{
								$rmLstr = $rmLstr.",'".$empName->source_code."'";
							}
						}
					}
				
					$whereRaw1 .= " AND Created_User IN (".$rmLstr.")";	
					
					
				}
		$collectionAgentNameBankMis = CBDBankMis::where("cbd_marging_status",1)
		->whereRaw($whereRaw1)
		->groupBy('Created_User')
		->selectRaw('count(*) as totalData, Created_User')
		->get();
	/* 	echo "<pre>";
		print_r($collectionAgentNameInternalMis);
		exit; */
		/*
		*get SM List
		*/
		$collectionSMNameInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('team')
		->selectRaw('team')
		->get();
		/*
		*get SM List
		*/	
		/*
		*get Card Type
		*/
		$collectionCardTypeInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('card_type_cbd')
		->selectRaw('card_type_cbd')
		->get();
		/*
		*get Card Type
		*/
		/*
		*get Employee Id
		*/
		$collectionEmployeeIdsInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('emp_id')
		->selectRaw('emp_id')
		->get();
		/*
		*get Employee Id
		*/		
		
		return view("Banks/CBD/matchingMIS/linkMISCBD",compact('collectionAgentNameInternalMis','collectionAgentNameBankMis','collectionSMNameInternalMis','collectionCardTypeInternalMis','collectionEmployeeIdsInternalMis'));
	}
	
	
	public function interCBDMisMargeFunc(Request $request)
	{
		
		$empID = $request->empID;
		
				
				
		$reports = DepartmentFormEntry::where("cbd_marging_status",1)->where("emp_id",$empID)->get();
		$reportsCount = DepartmentFormEntry::where("cbd_marging_status",1)->where("emp_id",$empID)->get()->count();
		return view("Banks/CBD/matchingMIS/interCBDMisMargeFunc",compact('reports','reportsCount'));
	}
	
	public function bankCBDMisMargeFunc(Request $request)
	{
		$createdUser = $request->createdUser;
		
		$reports = CBDBankMis::where("cbd_marging_status",1)->where("Created_User",$createdUser)->get();
		$reportsCount = CBDBankMis::where("cbd_marging_status",1)->get()->count();
		return view("Banks/CBD/matchingMIS/bankCBDMisMargeFunc",compact('reports','reportsCount'));
	}
	
	public function margeConfirmationCBD(Request $request)
	{
		$internalMis = explode("_",$request->internalMis);
		$cbdMis =  explode("_",$request->cbdMis);
		$data = array();
		$data['cm_name'] = DepartmentFormEntry::where("id",$internalMis[2])->first()->customer_name;
		$data['employee_id'] = DepartmentFormEntry::where("id",$internalMis[2])->first()->emp_id;
		$data['appId'] = CBDBankMis::where("id",$cbdMis[2])->first()->ref_no;
		echo json_encode($data);
		exit;
	}
	
	public function mergeAppIdWithMISCBD(Request $request)
	{
		
		$misId = explode("_",$request->misId);
		$jonusId =  explode("_",$request->jonusId);
		
		
		/*
		*marging from bank to mis
		*/
		$bankData = CBDBankMis::where("id",$jonusId[2])->first();
		$updateInternalMis = DepartmentFormEntry::find($misId[2]);
		$updateInternalMis->ref_no = $bankData->ref_no;
		$updateInternalMis->customer_name = $bankData->customer_name;
		$updateInternalMis->channel_cbd = $bankData->Channel;
		$updateInternalMis->status_AECB_cbd = $bankData->AECB_Status;
		$updateInternalMis->form_status = $bankData->Status;
		$updateInternalMis->card_type_cbd = $bankData->card_type;
		
		$updateInternalMis->cbd_marging_status = 2;
		$updateInternalMis->cbd_update_status = 2;
		$updateInternalMis->save();
		
			/*
			*update in child
			*/
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","customer_name")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->customer_name;
				$updateChild->save();
			}
			/*
			*update in child
			*/
			
			/*
			*update in child
			*/
			
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","status_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->Status;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","channel_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->Channel;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","card_type_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->card_type;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","aecb_status")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->AECB_Status;
				$updateChild->save();
			}
			/*
			*update in child
			*/
		
		/*
		*marging from bank to mis
		*/
		
		/*
		*marging from internal to bank
		*/
		$misInternalData = DepartmentFormEntry::where("id",$misId[2])->first();
		
			$updateBankMis = CBDBankMis::find($jonusId[2]);
			$updateBankMis->sm_manager = $misInternalData->team;
			$updateBankMis->employee_id = $misInternalData->emp_id;
			$updateBankMis->cbd_marging_status = 2;
			$updateBankMis->update_emp_status = 2;
			$updateBankMis->save();
		/*
		*marging from internal to bank
		*/
		
	}
	
	
	public function CBDCardsmapped(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$rm = '';
			
				$cardtype = '';

				

				if(isset($requestParameters['team']))
				{
					$team = @$requestParameters['team'];
				}
				if(isset($requestParameters['rm']))
				{
					$rm = @$requestParameters['rm'];
				}
				if(isset($requestParameters['cardtype']))
				{
					$cardtype = @$requestParameters['cardtype'];
				}
				
				
				if(isset($requestParameters['start_date']))
				{
					$start_date_application = @$requestParameters['start_date'];
				}
				if(isset($requestParameters['end_date']))
				{
					$end_date_application = @$requestParameters['end_date'];
				}
				
				
				$request->session()->put('team_CBD_master_links',$team);
				$request->session()->put('rm_CBD_master_links',$rm);
				$request->session()->put('card_type_links',$cardtype);
				
				$request->session()->put('start_date_application_CBD_master_links',$start_date_application);
				$request->session()->put('end_date_application_CBD_master_links',$end_date_application);
				
				return redirect("linkMISCBD");
	}
	
	public function resetCBDMISLink(Request $request)
	{
				$request->session()->put('team_CBD_master_links','');
				$request->session()->put('rm_CBD_master_links','');
				
				$request->session()->put('start_date_application_CBD_master_links','');
				$request->session()->put('end_date_application_CBD_master_links','');
				$request->session()->put('card_type_links','');
				
				return redirect("linkMISCBD");
	}
	
	public function updateAgentCode()
	{
		
		$internalMisMod = DepartmentFormEntry::where("form_id",2)->get();
		foreach($internalMisMod as $internalMis)
		{
			/* echo "<pre>";
			print_r($internalMis);
			exit; */
			$getData = DepartmentFormChildEntry::where("parent_id",$internalMis->id)->where("attribute_code","agent_code_cbd")->first();
			
				if($getData != '')
				{
					$updateMain = DepartmentFormEntry::find($internalMis->id);
					$updateMain->agent_code = $getData->attribute_value;
					$updateMain->save();
				}
		}
		echo "done";
		exit;
	}
	
	public function exportDocReportinternalMisCBDCardsLinking(Request $request)
	{
		$requestPost = $request->input();
		 $parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Link_Internal_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'Internal MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Row Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent Code'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Agent Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Application Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Card Type'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Customer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Mobile No'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Application Reference No'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				
				
				$mis =  DepartmentFormEntry::where("id",$sid)->first();
				if($mis->ref_no == NULL && $mis->ref_no == '')
				{
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				$agentNameMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_name_cbd")->first();
					$agentName = '';
					if($agentNameMod != '')
					{
						$agentName = $agentNameMod->attribute_value;
					}
				/*
				*agent name
				*/
				
				/*
				*agent code
				*/			
				$agentCodeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_code_cbd")->first();
					$agentCode = '';
					if($agentCodeMod != '')
					{
						$agentCode = $agentCodeMod->attribute_value;
					}
				/*
				*agent code
				*/
				
				
				/*
				*employeer name
				*/			
				$employeeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","employer_name")->first();
					$employeerName = '';
					if($employeeMod != '')
					{
						$employeerName = $employeeMod->attribute_value;
					}
				/*
				*employeer name
				*/
				
				/*
				*Card Type
				*/			
				$cardTypeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_type_cbd")->first();
					$cardType = '';
					if($cardTypeMod != '')
					{
						$cardType = $cardTypeMod->attribute_value;
					}
				/*
				*Card Type
				*/
				
				
				
				/*
				*status_cbd
				*/			
				$status_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","status_cbd")->first();
					$status_cbd = '';
					if($status_cbdMod != '')
					{
						$status_cbd = $status_cbdMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				
				
				
				
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeNamelocal($agentName))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $agentCode)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->application_date)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $cardType)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->customer_name)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->customer_mobile)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $status_cbd)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->ref_no)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				}
				
			}
			
			
			  for($col = 'A'; $col !== 'J'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('J3:J'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
			$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','J') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}




















// 19-6-2024 Start new code

public function enbdCardsManagement()
{
	$formidarr = array(6,7);
	
	$form_id = 6;
	$employeeIdList = DepartmentFormEntry::select('emp_id')->where("form_id",$form_id)->get()->unique('emp_id');	  
	$teamData = DepartmentFormEntry::select('team')->where("form_id",$form_id)->get()->unique('team');	

	$monthly_ends_data = MonthlyEnds::select('name')->where("status",1)->get();
	$current_activity_data = CurrentActivity::select('name')->where("status",1)->get();
	

	
	
	
   	return view("Banks/ENBD/enbdCardsManagement",compact("employeeIdList","teamData","monthly_ends_data","current_activity_data"));
}

public function addENBDCards(Request $request)
{
	$form_id =  $request->form_id;
	
	//$form_id = 6;
	$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();
	$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
	$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
	$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
	$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

	$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

	$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

	//$Employee_details = Employee_details::where("offline_status",1)->where("dept_id",49)->where("job_function",2)->orderby('first_name','ASC')->get();

	$idarr = array(2,17,24,84);

	//$Employee_details = Employee_details::where("dept_id",9)->where("offline_status",1)->where("job_function",2)->whereIn("designation_by_doc_collection",$idarr)->orderby('first_name','ASC')->get();


	$Employee_details = Employee_details::where("dept_id",9)->where("offline_status",1)->where("job_function",2)->orderby('first_name','ASC')->get();
	
	return view("Banks/ENBD/addENBDCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
}



public function loadBankContentsENBDCards(Request $request)
{
		 //$request->session()->put('paginationValue','');

		 $formidarr = array(6,7);

		//  echo "<pre>";
		//  print_r($formidarr);
		//  exit;
		
		$manual_form_id = 6;
		$tab_form_id = 7;
		$searchValues = array();

		$paginationValue = 20;
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		//$id = $form_id;
		//$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$departmentFormDetails =   DepartmentForm::whereIn("id",$formidarr)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		//$where_array = array('form_id'=> $form_id);
		//$whereRaw = " (form_id='".$manual_form_id."' OR form_id='".$tab_form_id."') AND (status='1' OR status='2')";
		$whereRaw = " (form_id='".$manual_form_id."' OR form_id='".$tab_form_id."')";
		// echo "<pre>";
		// print_r($whereRaw);
		// exit;

		
		

		

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
		
		if(@$request->session()->get('master_cbd_search_internal') != '' && @$request->session()->get('master_cbd_search_internal') == 2)
		{
			if(@$request->session()->get('application_no_ENBD_master') != '')
			{
				$applicationNO = $request->session()->get('application_no_ENBD_master');
				
			
				$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
				
				
			}
				
				
				
				if(@$request->session()->get('submission_type_ENBD_Master') != '')
				{
					$teamL = $request->session()->get('submission_type_ENBD_Master');
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
				
					$whereRaw .= " AND submission_type IN (".$teamstr.")";	
					
					
				}




				if(@$request->session()->get('monthly_ends_ENBD_master') != '')
				{
					$teamL = $request->session()->get('monthly_ends_ENBD_master');
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
				
					$whereRaw .= " AND monthly_ends IN (".$teamstr.")";	
					
					
				}



				if(@$request->session()->get('current_activity_ENBD_master') != '')
				{
					$teamL = $request->session()->get('current_activity_ENBD_master');
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
				
					$whereRaw .= " AND current_activity IN (".$teamstr.")";	
					
					
				}













				
				if(@$request->session()->get('emp_id_CBD_master') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_master');
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
				
				if($request->session()->get('start_date_application_ENBD_master') != '')
				{
					$start_date_application_EIB_internal = $request->session()->get('start_date_application_ENBD_master');			
					$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
					$searchValues['start_date_application_ENBD_master'] = $start_date_application_EIB_internal;			
				}

				if($request->session()->get('end_date_application_ENBD_master') != '')
				{
					$end_date_application_EIB_internal = $request->session()->get('end_date_application_ENBD_master');			
					$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
					$searchValues['end_date_application_ENBD_master'] = $end_date_application_EIB_internal;			
				}
		}
		else
		{
				if(@$request->session()->get('app_no_ENBD_internal') != '')
				{
					$appNO = $request->session()->get('app_no_ENBD_internal');
					
				
					$whereRaw .= " AND application_no like '%".$appNO."%'";	
					
					
				}
				if(@$request->session()->get('form_status_CBD_internal') != '')
				{
					$status = $request->session()->get('form_status_CBD_internal');
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
				
				
				if(@$request->session()->get('team_leader_enbd_internal') != '')
				{
					$teamL = $request->session()->get('team_leader_enbd_internal');
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
				
					$whereRaw .= " AND tl_name IN (".$teamstr.")";	
					
					
				}
				
				if(@$request->session()->get('emp_id_CBD_internal') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_internal');
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
				
				if(@$request->session()->get('emp_name_ENBD_internal') != '')
				{
					$channelInternalL = $request->session()->get('emp_name_ENBD_internal');
					$channelInternalstr = '';
					foreach($channelInternalL  as $cL)
					{
						if($channelInternalstr == '')
						{
							$channelInternalstr = "'".$cL."'";
						}
						else
						{
							$channelInternalstr = $channelInternalstr.",'".$cL."'";
						}
					}
				
					$whereRaw .= " AND emp_name IN (".$channelInternalstr.")";	
					
					
				}


				if(@$request->session()->get('source_code_internal') != '')
				{
					$channelInternalL = $request->session()->get('source_code_internal');
					$channelInternalstr = '';
					foreach($channelInternalL  as $cL)
					{
						if($channelInternalstr == '')
						{
							$channelInternalstr = "'".$cL."'";
						}
						else
						{
							$channelInternalstr = $channelInternalstr.",'".$cL."'";
						}
					}
				
					$whereRaw .= " AND source_code IN (".$channelInternalstr.")";	
					
					
				}
				if(@$request->session()->get('product_enbd_internal') != '')
				{
					$statusAECBL = $request->session()->get('product_enbd_internal');
					$statusAECBstr = '';
					foreach($statusAECBL  as $sL)
					{
						if($statusAECBstr == '')
						{
							$statusAECBstr = "'".$sL."'";
						}
						else
						{
							$statusAECBstr = $statusAECBstr.",'".$sL."'";
						}
					}
				
					$whereRaw .= " AND product_name IN (".$statusAECBstr.")";	
					
					
				}
				
				if(@$request->session()->get('card_type_EIB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_type_EIB_internal');
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
				
					$whereRaw .= " AND card_name IN (".$cardTypeInterbalstr.")";	
					
					
				}


				if(@$request->session()->get('current_activity_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('current_activity_internal');
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
				
					$whereRaw .= " AND current_activity IN (".$cardTypeInterbalstr.")";	
					
					
				}







				if(@$request->session()->get('submission_type_internal_enbd') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_enbd');
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
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}




				if(@$request->session()->get('submission_type_internal_type') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_type');
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
				
					$whereRaw .= " AND submission_type IN (".$submission_type_internalStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_ENBD_internal') != '')
				{
					$start_date_application_ENBD_internal = $request->session()->get('start_date_application_ENBD_internal');			
					$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_ENBD_internal))."'";
					$searchValues['start_date_application_ENBD_internal'] = $start_date_application_ENBD_internal;			
				}

				if($request->session()->get('end_date_application_ENBD_internal') != '')
				{
					$end_date_application_ENBD_internal = $request->session()->get('end_date_application_ENBD_internal');			
					$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_ENBD_internal))."'";
					$searchValues['end_date_application_ENBD_internal'] = $end_date_application_ENBD_internal;			
				}
		
		}
		

		$departmentFormParentTotal = DB::table('enbd_department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('enbd_department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		

		

		
		/*
		*employee Id
		*/
			//$employeeIdList = DepartmentFormEntry::select('emp_id')->where("form_id",$form_id)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		/*
		*status
		*/
			$formStatusData = DepartmentFormEntry::select('form_status')->whereIn("form_id",$formidarr)->get()->unique('form_status');
			
		/*
		*status
		*/
		
		/*
		*Team
		*/
			$teamData = ENBDDepartmentFormEntry::select('tl_name')->wherein("form_id",$formidarr)->get()->unique('tl_name');
			
		/*
		*Team
		*/
		
		
		/*
		*channel_cbd
		*/
			$channel_cbd = DepartmentFormEntry::select('channel_cbd')->whereIn("form_id",$formidarr)->get()->unique('channel_cbd');
			
		/*
		*channel_cbd
		*/
		
		/*
		*status_AECB_cbd
		*/
			$status_AECB_cbd = DepartmentFormEntry::select('status_AECB_cbd')->whereIn("form_id",$formidarr)->get()->unique('status_AECB_cbd');
			
		/*
		*status_AECB_cbd
		*/
		
		
		/*
		*card_type_cbd
		*/
			$card_type_cbd = ENBDDepartmentFormEntry::select('card_name')->whereIn("form_id",$formidarr)->get()->unique('card_name');
			$productsData = ENBDDepartmentFormEntry::select('product_name')->whereIn("form_id",$formidarr)->get()->unique('product_name');

			$empNameData = ENBDDepartmentFormEntry::select('emp_name')->whereIn("form_id",$formidarr)->get()->unique('emp_name');
			$sourceCodeData = ENBDDepartmentFormEntry::select('source_code')->whereIn("form_id",$formidarr)->get()->unique('source_code');

			$current_activityData = CurrentActivity::select('name')->where("status",1)->get()->unique('name');
			
			
		/*
		*card_type_cbd
		*/
		//return $card_type_cbd;

        //return view("Banks/ENBD/loadBankContentsENBDCards",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','employeeIdList','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_cbd','card_status'));

		return view("Banks/ENBD/loadBankContentsENBDCards",compact('departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_cbd','current_activityData','productsData','empNameData','sourceCodeData'));
	}


	public function addENBDEntryPost(Request $request)
 	{
			//return $request->all();

			$form_id = $request->form_id;
			$form_title = $request->form_title;
		
			$postData = $request->input();
			
			$postDataInput = $postData['attribute_value'];
			$seempdata = explode("~",$postDataInput['se_name_enbd']);
			$Employee_details_data = Employee_details::where("emp_id",$seempdata[0])->orderby('id','desc')->first();
			$entry_obj = new ENBDDepartmentFormEntry();			
	
			/*
			*parent entry 
			*start code
			*/
			//$entry_obj->ref_no = $postDataInput['ref_no'];
			$entry_obj->form_id = $form_id;
			$entry_obj->form_title = $form_title;



			if($form_id==6)
			{
				$entry_obj->submission_type = 'Manual';


				$entry_obj->customer_name = $postDataInput['customer_name_enbd'];
				$entry_obj->mobile = $postDataInput['customer_mobile_enbd'];
				$entry_obj->email = $postDataInput['customer_email_enbd'];
				$entry_obj->salary = $postDataInput['salary_enbd'];
				$entry_obj->nationality = $postDataInput['nationality_enbd'];
				$entry_obj->passport_no = $postDataInput['passport_enbd'];
				$entry_obj->designation = $postDataInput['designation_enbd'];
				$entry_obj->dob = date("Y-m-d",strtotime($postDataInput['dob_enbd']));

				$entry_obj->emp_id = $seempdata[0];
				$entry_obj->emp_name = $seempdata[1];
				$entry_obj->source_code = $Employee_details_data->source_code;


				$entry_obj->application_no = $postDataInput['applicaion_id_enbd'];
				$entry_obj->application_type = $postDataInput['application_type_enbd'];
				$entry_obj->submission_date = date("Y-m-d",strtotime($postDataInput['submission_date_enbd']));
				$entry_obj->product_name = $postDataInput['product_enbd'];
				$entry_obj->company_name = $postDataInput['company_enbd'];
				$entry_obj->account_number = $postDataInput['account_no_enbd'];
				$entry_obj->account_status = $postDataInput['account_status_enbd'];
				$entry_obj->current_activity = $postDataInput['current_activity_enbd'];
				$entry_obj->monthly_ends = $postDataInput['monthly_ends_enbd'];
				$entry_obj->remarks = $postDataInput['remarks_enbd'];
				$entry_obj->remarks = date("Y-m-d",strtotime($postDataInput['length_service_enbd']));
				$entry_obj->approved_status = $postDataInput['approved_enbd'];
				$entry_obj->card_name = $postDataInput['card_enbd'];
				$entry_obj->tl_name = $postDataInput['team_leader_enbd'];
				
				
				
				
				
				
			}
			else if($form_id==7)
			{
				$entry_obj->submission_type = 'Tab';
				$entry_obj->application_no = $postDataInput['applicaion_id_enbd'];
				$entry_obj->submission_date = date("Y-m-d",strtotime($postDataInput['submission_date_enbd']));
				//$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['app_date_enbd']));
				$entry_obj->application_type = $postDataInput['application_type_enbd'];
				$entry_obj->product_type = $postDataInput['product_type_enbd'];
				$entry_obj->bidaya = $postDataInput['bidaya_enbd'];
				$entry_obj->company_name = $postDataInput['company_enbd'];
				$entry_obj->current_activity = $postDataInput['current_activity_enbd'];

				$entry_obj->emp_id = $seempdata[0];
				$entry_obj->emp_name = $seempdata[1];
				$entry_obj->source_code = $Employee_details_data->source_code;
				
				
				$entry_obj->customer_name = $postDataInput['customer_name_enbd'];
				$entry_obj->mobile = $postDataInput['customer_mobile_enbd'];
				$entry_obj->product_name = $postDataInput['product_enbd'];
				$entry_obj->nationality = $postDataInput['nationality_enbd'];
				$entry_obj->se_name = $postDataInput['se_name_enbd'];
				$entry_obj->salary = $postDataInput['salary_enbd'];
				$entry_obj->tl_name = $postDataInput['team_leader_enbd'];
				$entry_obj->designation = $postDataInput['designation_enbd'];	
				$entry_obj->enbd_status = $postDataInput['enbd_status_enbd'];				
				$entry_obj->card_type = $postDataInput['card_type_enbd'];
				if($postDataInput['dob_enbd']!='' || $postDataInput['dob_enbd']!=NULL)
				{
					$entry_obj->dob = date("Y-m-d",strtotime($postDataInput['dob_enbd']));
				}
								
				$entry_obj->status = $postDataInput['status_enbd'];
				$entry_obj->ale_nale_status = $postDataInput['ale_nale_enbd'];
				
			}
			else
			{
				$entry_obj->submission_type = '';
			}







			
			
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
			$child_obj = new ENBDDepartmentFormChildEntry();
			foreach($postDataInput as $key=>$value)
			{
				$child_obj = new ENBDDepartmentFormChildEntry();
				$child_obj->parent_id = $insertID;
				$child_obj->form_id = $form_id;
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
			return redirect('enbdCardsManagement');
	 
 	}



	 public function viewPanelAsperFileSourceENBD($parent_id=NULL,$form_id=NULL)
	 {
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		$departmentFormParentDetails = DB::table('enbd_department_form_parent_entry')->where('id', $parent_id)->first();

		$departmentFormChildDetails = DB::table('enbd_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		$Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
		
		return view("Banks/ENBD/viewPanelAsperFileSourceENBD",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
	 }


	 public function editENBDCards($parent_id=NULL,$form_id=NULL)
	 {
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		$departmentFormParentDetails = DB::table('enbd_department_form_parent_entry')->where('id', $parent_id)->first();

		$departmentFormChildDetails = DB::table('enbd_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		//return $departmentFormChildDetails;

		//$Employee_details = Employee_details::where("offline_status",1)->where("dept_id",49)->where("job_function",2)->orderby('first_name','ASC')->get();

		$idarr = array(2,17,24,84);

	//$Employee_details = Employee_details::where("dept_id",9)->where("job_function",2)->whereIn("designation_by_doc_collection",$idarr)->orderby('first_name','ASC')->get();


	$Employee_details = Employee_details::where("dept_id",9)->where("offline_status",1)->where("job_function",2)->orderby('first_name','ASC')->get();
	
		return view("Banks/ENBD/editEnbdCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
	 }


	public function editENBDFormEntryPostData(Request $request)
 	{
			

		
			$form_id = $request->form_id;
			$form_title = $request->form_title;
			//return $request->all();
			$postData = $request->input();
			$postDataInput = $postData['attribute_value'];
			$seempdata = explode("~",$postDataInput['se_name_enbd']);
			$Employee_details_data = Employee_details::where("emp_id",$seempdata[0])->orderby('id','desc')->first();
			$entry_objUpdate = ENBDDepartmentFormEntry::find($postData['parent_id']);
			
			



			if($form_id==6)
			{
				$entry_objUpdate->submission_type = 'Manual';


				$entry_objUpdate->customer_name = $postDataInput['customer_name_enbd'];
				$entry_objUpdate->mobile = $postDataInput['customer_mobile_enbd'];
				$entry_objUpdate->email = $postDataInput['customer_email_enbd'];
				$entry_objUpdate->salary = $postDataInput['salary_enbd'];
				$entry_objUpdate->nationality = $postDataInput['nationality_enbd'];
				$entry_objUpdate->passport_no = $postDataInput['passport_enbd'];
				$entry_objUpdate->designation = $postDataInput['designation_enbd'];
				$entry_objUpdate->dob = date("Y-m-d",strtotime($postDataInput['dob_enbd']));

				$entry_objUpdate->emp_id = $seempdata[0];
				$entry_objUpdate->emp_name = $Employee_details_data->emp_name;
				$entry_objUpdate->source_code = $Employee_details_data->source_code;


				$entry_objUpdate->application_no = $postDataInput['applicaion_id_enbd'];
				$entry_objUpdate->application_type = $postDataInput['application_type_enbd'];
				$entry_objUpdate->submission_date = date("Y-m-d",strtotime($postDataInput['submission_date_enbd']));
				$entry_objUpdate->product_name = $postDataInput['product_enbd'];
				$entry_objUpdate->company_name = $postDataInput['company_enbd'];
				$entry_objUpdate->account_number = $postDataInput['account_no_enbd'];
				$entry_objUpdate->account_status = $postDataInput['account_status_enbd'];
				$entry_objUpdate->current_activity = $postDataInput['current_activity_enbd'];
				$entry_objUpdate->monthly_ends = $postDataInput['monthly_ends_enbd'];
				$entry_objUpdate->remarks = $postDataInput['remarks_enbd'];
				$entry_objUpdate->remarks = date("Y-m-d",strtotime($postDataInput['length_service_enbd']));
				$entry_objUpdate->approved_status = $postDataInput['approved_enbd'];
				$entry_objUpdate->card_name = $postDataInput['card_enbd'];
				$entry_objUpdate->se_name = $postDataInput['se_name_enbd'];
				$entry_objUpdate->tl_name = $postDataInput['team_leader_enbd'];
				
				
				
				
				
				
			}
			else if($form_id==7)
			{
				$entry_objUpdate->submission_type = 'Tab';
				$entry_objUpdate->application_no = $postDataInput['applicaion_id_enbd'];
				$entry_objUpdate->submission_date = date("Y-m-d",strtotime($postDataInput['submission_date_enbd']));
				//$entry_objUpdate->application_date = date("Y-m-d",strtotime($postDataInput['app_date_enbd']));
				$entry_objUpdate->application_type = $postDataInput['application_type_enbd'];
				$entry_objUpdate->product_type = $postDataInput['product_type_enbd'];
				$entry_objUpdate->bidaya = $postDataInput['bidaya_enbd'];
				$entry_objUpdate->company_name = $postDataInput['company_enbd'];
				$entry_objUpdate->current_activity = $postDataInput['current_activity_enbd'];


				$entry_objUpdate->emp_id = $seempdata[0];
				$entry_objUpdate->emp_name = $Employee_details_data->emp_name;
				$entry_objUpdate->source_code = $Employee_details_data->source_code;
				
				
				$entry_objUpdate->customer_name = $postDataInput['customer_name_enbd'];
				$entry_objUpdate->mobile = $postDataInput['customer_mobile_enbd'];
				$entry_objUpdate->product_name = $postDataInput['product_enbd'];
				$entry_objUpdate->nationality = $postDataInput['nationality_enbd'];
				$entry_objUpdate->se_name = $postDataInput['se_name_enbd'];
				$entry_objUpdate->salary = $postDataInput['salary_enbd'];
				$entry_objUpdate->tl_name = $postDataInput['team_leader_enbd'];
				$entry_objUpdate->designation = $postDataInput['designation_enbd'];	
				$entry_objUpdate->enbd_status = $postDataInput['enbd_status_enbd'];				
				$entry_objUpdate->card_type = $postDataInput['card_type_enbd'];
				if($postDataInput['dob_enbd']!='' || $postDataInput['dob_enbd']!=NULL)
				{
					$entry_objUpdate->dob = date("Y-m-d",strtotime($postDataInput['dob_enbd']));
				}
								
				$entry_objUpdate->status = $postDataInput['status_enbd'];
				$entry_objUpdate->ale_nale_status = $postDataInput['ale_nale_enbd'];
			}
			else
			{
				$entry_objUpdate->submission_type = '';
			}


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
				$existChild = ENBDDepartmentFormChildEntry::where("parent_id",$postData['parent_id'])->where("attribute_code",$key)->first();
				if($existChild != '')
				{
					$child_obj = ENBDDepartmentFormChildEntry::find($existChild->id);
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = $form_id;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
				}
				else
				{
					$child_obj = new ENBDDepartmentFormChildEntry();
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = $form_id;
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
			return redirect('enbdCardsManagement');
			
 	}




	 public function ENBDSearchMasterData(Request $request)
	 {
		 $requestParameters = $request->input();
				 
				 $start_date_application = '';
				 $end_date_application = '';
				 $submission = '';
				 $monthly_ends = '';
				 $current_activity = '';
			 
				 $application_no = '';
				 $emp_id = '';
 
				 if(@isset($requestParameters['application_no']))
				 {
					 $application_no = @$requestParameters['application_no'];
				 }
 
				 if(isset($requestParameters['submission_type_enbd']))
				 {
					 $submission = @$requestParameters['submission_type_enbd'];
				 }


				 if(isset($requestParameters['monthly_ends_enbd']))
				 {
					 $monthly_ends = @$requestParameters['monthly_ends_enbd'];
				 }
				 if(isset($requestParameters['current_activity_enbd']))
				 {
					 $current_activity = @$requestParameters['current_activity_enbd'];
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
				 
				 $request->session()->put('application_no_ENBD_master',$application_no);
				 $request->session()->put('submission_type_ENBD_Master',$submission);
				 $request->session()->put('monthly_ends_ENBD_master',$monthly_ends);
				 $request->session()->put('current_activity_ENBD_master',$current_activity);
				 $request->session()->put('emp_id_CBD_master',$emp_id);
				 $request->session()->put('start_date_application_ENBD_master',$start_date_application);
				 $request->session()->put('end_date_application_ENBD_master',$end_date_application);
				 $request->session()->put('master_cbd_search_internal',2);
				 $request->session()->put('master_cbd_search_bank',2);
				 return redirect("enbdCardsManagement");
		 
				 
	 }

	 public function resetENBDMasterData(Request $request)
	 {
				 $request->session()->put('application_no_ENBD_master','');
				 $request->session()->put('submission_type_ENBD_Master','');
				 $request->session()->put('emp_id_CBD_master','');
				 $request->session()->put('monthly_ends_ENBD_master','');
				 $request->session()->put('current_activity_ENBD_master','');
				 $request->session()->put('start_date_application_ENBD_master','');
				 $request->session()->put('end_date_application_ENBD_master','');
				 $request->session()->put('master_cbd_search_internal','');
				 $request->session()->put('master_cbd_search_bank','');
				 return redirect("enbdCardsManagement");
	 }




	public function loadJonusENBDCardsContentData(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " application_no!=''";	
		if(@$request->session()->get('master_cbd_search_bank') != '' && @$request->session()->get('master_cbd_search_bank') == 2)
		{
				  if(@$request->session()->get('application_no_ENBD_master') != '')
					{
						$applicationNO = $request->session()->get('application_no_ENBD_master');
						
					
						//$whereRaw .= " AND ref_no like '%".$refNO."%'";	
						$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_CBD_master') != '')
					{
						$SMMod = $request->session()->get('team_CBD_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_ENBD_master') != '')
					{
						$start_date_application_EIB_internal = $request->session()->get('start_date_application_ENBD_master');			
						$whereRaw .= " AND application_created_at >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
						$searchValues['start_date_application_ENBD_master'] = $start_date_application_EIB_internal;			
					}

					if($request->session()->get('end_date_application_ENBD_master') != '')
					{
						$end_date_application_EIB_internal = $request->session()->get('end_date_application_ENBD_master');			
						$whereRaw .= " AND application_created_at <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
						$searchValues['end_date_application_ENBD_master'] = $end_date_application_EIB_internal;			
					}
		}
		else
		{
					if(@$request->session()->get('app_no_ENBD_jonusPhysical') != '')
					{
						$refNO = $request->session()->get('app_no_ENBD_jonusPhysical');
						
					
						$whereRaw .= " AND application_no like '%".$refNO."%'";	
						
						
					}
					if(@$request->session()->get('status_CBD_bank') != '')
					{
						$status = $request->session()->get('status_CBD_bank');
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
					
						$whereRaw .= " AND status IN (".$strStatus.")";	
						
						
					}
					
					
					if(@$request->session()->get('AECB_Status_CBD_bank') != '')
					{
						$statusAECB = $request->session()->get('AECB_Status_CBD_bank');
						$strStatusAECB = '';
						foreach($statusAECB  as $sAECB)
						{
							if($strStatusAECB == '')
							{
								$strStatusAECB = "'".$sAECB."'";
							}
							else
							{
								$strStatusAECB = $strStatusAECB.",'".$sAECB."'";
							}
						}
					
						$whereRaw .= " AND AECB_Status IN (".$strStatusAECB.")";	
						
						
					}
					if(@$request->session()->get('employee_id_CBD_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_bank');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_CBD_bank') != '')
					{
						$SMMod = $request->session()->get('smManager_CBD_bank');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_JonusPhysical') != '')
					{
						$start_date_application_JonusPhysical = $request->session()->get('start_date_application_JonusPhysical');			
						$whereRaw .= " AND date_sourcing >='".date('Y-m-d',strtotime($start_date_application_JonusPhysical))."'";
						$searchValues['start_date_application_JonusPhysical'] = $start_date_application_JonusPhysical;			
					}

					if($request->session()->get('end_date_application_JonusPhysiacal') != '')
					{
						$end_date_application_JonusPhysiacal = $request->session()->get('end_date_application_JonusPhysiacal');			
						$whereRaw .= " AND date_sourcing <='".date('Y-m-d',strtotime($end_date_application_JonusPhysiacal))."'";
						$searchValues['end_date_application_JonusPhysiacal'] = $end_date_application_JonusPhysiacal;			
					}
					if(@$request->session()->get('submission_type_internal_jonusPhysical') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_jonusPhysical');
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
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}
					
					

		}
		//echo $whereRaw;exit;
		$datasCBDMainCount = ENBDCardsMisReport::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = ENBDCardsMisReport::whereRaw($whereRaw)->orderBy("id","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			//$appStatusMod = EibBankMis::select('status')->get()->unique('status');
			$appStatusMod = '';
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			//$appAECB_StatusMod = EibBankMis::select('AECB_Status')->get()->unique('AECB_Status');
			$appAECB_StatusMod = '';
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			//$employeeIdList = EibBankMis::select('employee_id')->get()->unique('employee_id');
			$employeeIdList = '';
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			//$smManageData = EibBankMis::select('sm_manager')->get()->unique('sm_manager');
			$smManageData = '';
		/*
		*Sm Manager
		*/
		 return view("Banks/ENBD/loadBankContentsENBDCardBankSide",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData'));
	}



	public function searchENBDInternalInnerFilter(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$emp_name = '';
				$form_status = '';
				$app_no = '';
				$emp_id = '';
				
				$source_code = '';
				$status_AECB_cbd = '';
				$card_type_eib = '';
				$card_status_eib = '';
				$submission_type_inner = '';
				$submission_type_inner_type = '';
				
				if(isset($requestParameters['source_code_enbd']))
				{
					$source_code = @$requestParameters['source_code_enbd'];
				}
				
				if(isset($requestParameters['status_AECB_cbd']))
				{
					$status_AECB_cbd = @$requestParameters['status_AECB_cbd'];
				}
				
				if(isset($requestParameters['team_leader_enbd']))
				{
					$card_type_eib = @$requestParameters['team_leader_enbd'];
				}
				if(isset($requestParameters['current_activity_enbd']))
				{
					$card_status_eib = @$requestParameters['current_activity_enbd'];
				}

				if(@isset($requestParameters['app_no']))
				{
					$app_no = @$requestParameters['app_no'];
				}

				if(isset($requestParameters['emp_name_enbd']))
				{
					$emp_name = @$requestParameters['emp_name_enbd'];
				}

				if(isset($requestParameters['product_enbd']))
				{
					$form_status = @$requestParameters['product_enbd'];
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
				if(isset($requestParameters['submission_type_inner_enbd']))
				{
					$submission_type_inner = @$requestParameters['submission_type_inner_enbd'];
				}


				if(isset($requestParameters['submission_type_inner_type']))
				{
					$submission_type_inner_type = @$requestParameters['submission_type_inner_type'];
				}
				$request->session()->put('master_cbd_search_internal','');
				$request->session()->put('app_no_ENBD_internal',$app_no);
				$request->session()->put('product_enbd_internal',$form_status);
				$request->session()->put('emp_name_ENBD_internal',$emp_name);
				$request->session()->put('emp_id_CBD_internal',$emp_id);
				$request->session()->put('start_date_application_ENBD_internal',$start_date_application);
				$request->session()->put('end_date_application_ENBD_internal',$end_date_application);
				$request->session()->put('source_code_internal',$source_code);
				$request->session()->put('status_AECB_CBD_internal',$status_AECB_cbd);
				$request->session()->put('team_leader_enbd_internal',$card_type_eib);
				$request->session()->put('current_activity_internal',$card_status_eib);
				$request->session()->put('submission_type_internal_enbd',$submission_type_inner);
				$request->session()->put('submission_type_internal_type',$submission_type_inner_type);
				return redirect("loadBankContentENBDCard");
	}



	public function resetInnerENBDInternalFilter(Request $request)
	{
			$request->session()->put('app_no_ENBD_internal','');
				$request->session()->put('product_enbd_internal','');
				$request->session()->put('emp_name_ENBD_internal','');
				$request->session()->put('emp_id_CBD_internal','');
				$request->session()->put('start_date_application_ENBD_internal','');
				$request->session()->put('end_date_application_ENBD_internal','');
				$request->session()->put('source_code_internal','');
				$request->session()->put('status_AECB_CBD_internal','');
				$request->session()->put('team_leader_enbd_internal','');
				$request->session()->put('current_activity_internal','');
				$request->session()->put('submission_type_internal_enbd','');
				$request->session()->put('submission_type_internal_type','');
				$request->session()->put('master_cbd_search_internal',2);
				return redirect("loadBankContentENBDCard");
	}


	public function getSubmissionTypePopData(Request $request)
	{
		return view("Banks/ENBD/addRequestContent");
	}

	public function submissionTypeRequestPost(Request $request)
	{
		//return $request->all();
		$validator = Validator::make($request->all(), 
        [			
			'submissionType' => 'required',            
        ],
		[
			'submissionType.required'=> 'Please Select Submission Type',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			return response()->json(['success'=>$request->submissionType]);
		}
	}




	public function exportDocReportinternalMisENBDCards(Request $request)
	{
			$requestPost = $request->input();
		 	$parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Internal_MIS_ENBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'Internal MIS ENBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Application No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Submission Date'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Nationality'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Mobile'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Designation'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Product'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('SE Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('TL Name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Account Number'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Account Status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Passport'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Application Mode'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Application Type'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Email'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Salary'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Current Activity'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Monthly Ends'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Submission Type'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			//$sheet->setCellValue('H'.$indexCounter, strtoupper('Product'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			//$sheet->setCellValue('U'.$indexCounter, strtoupper('Passport'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  ENBDDepartmentFormEntry::where("id",$sid)->first();
				
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				$application_num = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","applicaion_id_enbd")->first();
					$appnum = '';
					if($application_num != '')
					{
						$appnum = $application_num->attribute_value;
					}
				/*
				*agent name
				*/
				
				/*
				*agent code
				*/			
				$creation_date = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","date_eib")->first();
					$creationDate = '';
					if($creation_date != '')
					{
						$creationDate = $creation_date->attribute_value;
					}
				/*
				*agent code
				*/
				
				
				/*
				*employeer name
				*/			
				$custmer_name = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","customer_name_enbd")->first();
					$custmerName = '';
					if($custmer_name != '')
					{
						$custmerName = $custmer_name->attribute_value;
					}
				/*
				*employeer name
				*/
				
				/*
				*Card Type
				*/			
				$national = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","nationality_enbd")->first();
					$nationaltyVal = '';
					if($national != '')
					{
						$nationaltyVal = $national->attribute_value;
					}
				/*
				*Card Type
				*/
				
				/*
				*bureau_score
				*/			
				$mobile = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","customer_mobile_enbd")->first();
					$mobileNum = '';
					if($mobile != '')
					{
						$mobileNum = $mobile->attribute_value;
					}
				/*
				*bureau_score
				*/
				
				/*
				*app_score
				*/			
				$desig = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","designation_enbd")->first();
					$desigName = '';
					if($desig != '')
					{
						$desigName = $desig->attribute_value;
					}
				/*
				*app_score
				*/
				
				/*
				*MOB
				*/			
				$product = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","product_enbd")->first();
					$productName = '';
					if($product != '')
					{
						$productName = $product->attribute_value;
					}
				/*
				*MOB
				*/
				
				/*
				*declared_salary_cbd
				*/			
				$passport = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","passport_enbd")->first();
					$passportno = '';
					if($passport != '')
					{
						$passportno = $passport->attribute_value;
					}
				/*
				*declared_salary_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$submission = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","submission_date_enbd")->first();
					$submissionDate = '';
					if($submission != '')
					{
						$submissionDate = $submission->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$card_statusMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_status_eib")->first();
					$card_status = '';
					if($card_statusMod != '')
					{
						$card_status = $card_statusMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$company_statusMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","company_status_eib")->first();
					$company_status = '';
					if($company_statusMod != '')
					{
						$company_status = $company_statusMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*status_cbd
				*/			
				$se_codeMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_code_eib")->first();
					$se_code = '';
					if($se_codeMod != '')
					{
						$se_code = $se_codeMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				
				/*
				*CBD_remark
				*/			
				$se_nameMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_name_eib")->first();
					$se_name = '';
					if($se_nameMod != '')
					{
						$se_name = $se_nameMod->attribute_value;
					}
				/*
				*CBD_remark
				*/
				
				/*
				*status_cbd
				*/			
				$tl_nameMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","tl_name_eib")->first();
					$tl_name = '';
					if($tl_nameMod != '')
					{
						$tl_name = $tl_nameMod->attribute_value;
					}


					$occuMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","occupation_eib")->first();
					$occupation = '';
					if($occuMod != '')
					{
						$occupation = $occuMod->attribute_value;
					}


					$bpmMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","bpm_id_eib")->first();
					$bpm_id = '';
					if($bpmMod != '')
					{
						$bpm_id = $bpmMod->attribute_value;
					}


					$remarkMod = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","remarks_eib")->first();
					$remark = '';
					if($remarkMod != '')
					{
						$remark = $remarkMod->attribute_value;
					}



					$accountnum = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","account_no_enbd")->first();
					$accountno = '';
					if($accountnum != '')
					{
						$accountno = $accountnum->attribute_value;
					}


					$accountstatus = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","account_status_enbd")->first();
					$account_status = '';
					if($accountstatus != '')
					{
						$account_status = $accountstatus->attribute_value;
					}


					$passportdata = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","passport_enbd")->first();
					$passport = '';
					if($passportdata != '')
					{
						$passport = $passportdata->attribute_value;
					}



					$apptype = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","application_type_enbd")->first();
					$applicationType = '';
					if($apptype != '')
					{
						$applicationType = $apptype->attribute_value;
					}



					$custemail = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","customer_email_enbd")->first();
					$customeremail = '';
					if($custemail != '')
					{
						$customeremail = $custemail->attribute_value;
					}



					$salarydata = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","salary_enbd")->first();
					$salary = '';
					if($salarydata != '')
					{
						$salary = $salarydata->attribute_value;
					}



					$current_activity_enbd = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","current_activity_enbd")->first();
					$current_activity = '';
					if($current_activity_enbd != '')
					{
						$current_activity = $current_activity_enbd->attribute_value;
					}


					$monthly_ends_enbd = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","monthly_ends_enbd")->first();
					$monthly_ends = '';
					if($monthly_ends_enbd != '')
					{
						$monthly_ends = $monthly_ends_enbd->attribute_value;
					}


					$submissionDate = date("d M, Y", strtotime($mis->submission_date));
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $appnum)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $submissionDate)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $custmerName)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $nationaltyVal)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mobileNum)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $desigName)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $productName)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->se_name)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->tl_name)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $accountno)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $account_status)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $passport)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->application_mode)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $applicationType)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $customeremail)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $salary)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $current_activity)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $monthly_ends)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->submission_type)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('U'.$indexCounter, $mis->actual_se_name)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'U'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','U') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}










	
	public function loadJonusTabENBDCardsData(Request $request)
	{
		

		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " application_no!=''";	
		if(@$request->session()->get('master_cbd_search_bank') != '' && @$request->session()->get('master_cbd_search_bank') == 2)
		{
				  if(@$request->session()->get('application_no_ENBD_master') != '')
					{
						$applicationNO = $request->session()->get('application_no_ENBD_master');
						
					
						//$whereRaw .= " AND ref_no like '%".$refNO."%'";	
						$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_CBD_master') != '')
					{
						$SMMod = $request->session()->get('team_CBD_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_ENBD_master') != '')
					{
						$start_date_application_EIB_internal = $request->session()->get('start_date_application_ENBD_master');			
						$whereRaw .= " AND application_created_at >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
						$searchValues['start_date_application_ENBD_master'] = $start_date_application_EIB_internal;			
					}

					if($request->session()->get('end_date_application_ENBD_master') != '')
					{
						$end_date_application_EIB_internal = $request->session()->get('end_date_application_ENBD_master');			
						$whereRaw .= " AND application_created_at <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
						$searchValues['end_date_application_ENBD_master'] = $end_date_application_EIB_internal;			
					}
		}
		else
		{
					if(@$request->session()->get('app_no_ENBD_jonusTab') != '')
					{
						$refNO = $request->session()->get('app_no_ENBD_jonusTab');
						
					
						$whereRaw .= " AND application_no like '%".$refNO."%'";	
						
						
					}
					if(@$request->session()->get('status_CBD_bank') != '')
					{
						$status = $request->session()->get('status_CBD_bank');
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
					
						$whereRaw .= " AND status IN (".$strStatus.")";	
						
						
					}
					
					
					if(@$request->session()->get('AECB_Status_CBD_bank') != '')
					{
						$statusAECB = $request->session()->get('AECB_Status_CBD_bank');
						$strStatusAECB = '';
						foreach($statusAECB  as $sAECB)
						{
							if($strStatusAECB == '')
							{
								$strStatusAECB = "'".$sAECB."'";
							}
							else
							{
								$strStatusAECB = $strStatusAECB.",'".$sAECB."'";
							}
						}
					
						$whereRaw .= " AND AECB_Status IN (".$strStatusAECB.")";	
						
						
					}
					if(@$request->session()->get('employee_id_CBD_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_bank');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_CBD_bank') != '')
					{
						$SMMod = $request->session()->get('smManager_CBD_bank');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_jonusTab') != '')
					{
						$start_date_application_jonusTab = $request->session()->get('start_date_application_jonusTab');			
						$whereRaw .= " AND application_created >='".date('Y-m-d',strtotime($start_date_application_jonusTab))."'";
						$searchValues['start_date_application_jonusTab'] = $start_date_application_jonusTab;			
					}

					if($request->session()->get('end_date_application_jonusTab') != '')
					{
						$end_date_application_jonusTab = $request->session()->get('end_date_application_jonusTab');			
						$whereRaw .= " AND application_created <='".date('Y-m-d',strtotime($end_date_application_jonusTab))."'";
						$searchValues['end_date_application_jonusTab'] = $end_date_application_jonusTab;			
					}
					if(@$request->session()->get('submission_type_internal_jonusTab') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_jonusTab');
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
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}
					
					

		}
		//echo $whereRaw;exit;
		$datasCBDMainCount = MainMisReportTab::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = MainMisReportTab::whereRaw($whereRaw)->orderBy("id","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			//$appStatusMod = EibBankMis::select('status')->get()->unique('status');
			$appStatusMod = '';
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			//$appAECB_StatusMod = EibBankMis::select('AECB_Status')->get()->unique('AECB_Status');
			$appAECB_StatusMod = '';
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			//$employeeIdList = EibBankMis::select('employee_id')->get()->unique('employee_id');
			$employeeIdList = '';
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			//$smManageData = EibBankMis::select('sm_manager')->get()->unique('sm_manager');
			$smManageData = '';
		/*
		*Sm Manager
		*/
		 return view("Banks/ENBD/loadBankContentsENBDJonusTab",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData'));










		 //return view("Banks/CBD/loadBankContentsCBDCardBankSideMTD",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','employeeIdList','smManageData'));
	}




// 3-6-2024 End new code



public function exportJonusPhysiacalENBDCards(Request $request)
	{
			$requestPost = $request->input();
		 	$parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Jonus_Physical_ENBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'Jonus Physical ENBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Application No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Application Type'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Scheme'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Card Type'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('P1 Code'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Product'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Offer'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Card id'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Current Activity'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Date of Sourcing'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Signed Date'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Card Approval Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Application Credit Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Channel code'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Remarks'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('DSA Barnch'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('CAS Status'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			//$sheet->setCellValue('H'.$indexCounter, strtoupper('Product'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			//$sheet->setCellValue('U'.$indexCounter, strtoupper('Passport'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  ENBDCardsMisReport::where("id",$sid)->first();
				
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				// $application_num = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","applicaion_id_enbd")->first();
				// 	$appnum = '';
				// 	if($application_num != '')
				// 	{
				// 		$appnum = $application_num->attribute_value;
				// 	}
				// /*
				// *agent name
				// */
				
				// /*
				// *agent code
				// */			
				// $creation_date = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","date_eib")->first();
				// 	$creationDate = '';
				// 	if($creation_date != '')
				// 	{
				// 		$creationDate = $creation_date->attribute_value;
				// 	}
				// /*
				// *agent code
				// */
				
				
				// /*
				// *employeer name
				// */			
				// $custmer_name = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","customer_name_enbd")->first();
				// 	$custmerName = '';
				// 	if($custmer_name != '')
				// 	{
				// 		$custmerName = $custmer_name->attribute_value;
				// 	}
				// /*
				// *employeer name
				// */
				
				// /*
				// *Card Type
				// */			
				// $national = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","nationality_enbd")->first();
				// 	$nationaltyVal = '';
				// 	if($national != '')
				// 	{
				// 		$nationaltyVal = $national->attribute_value;
				// 	}
				// /*
				// *Card Type
				// */
				
				// /*
				// *bureau_score
				// */			
				// $mobile = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","customer_mobile_enbd")->first();
				// 	$mobileNum = '';
				// 	if($mobile != '')
				// 	{
				// 		$mobileNum = $mobile->attribute_value;
				// 	}
				// /*
				// *bureau_score
				// */
				
				// /*
				// *app_score
				// */			
				// $desig = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","designation_enbd")->first();
				// 	$desigName = '';
				// 	if($desig != '')
				// 	{
				// 		$desigName = $desig->attribute_value;
				// 	}
				// /*
				// *app_score
				// */
				
				// /*
				// *MOB
				// */			
				// $product = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","product_enbd")->first();
				// 	$productName = '';
				// 	if($product != '')
				// 	{
				// 		$productName = $product->attribute_value;
				// 	}
				// /*
				// *MOB
				// */
				
				// /*
				// *declared_salary_cbd
				// */			
				// $passport = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","passport_enbd")->first();
				// 	$passportno = '';
				// 	if($passport != '')
				// 	{
				// 		$passportno = $passport->attribute_value;
				// 	}
				// /*
				// *declared_salary_cbd
				// */
				
				// /*
				// *eligible_income_cbd
				// */			
				// $submission = 	ENBDDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","submission_date_enbd")->first();
				// 	$submissionDate = '';
				// 	if($submission != '')
				// 	{
				// 		$submissionDate = $submission->attribute_value;
				// 	}
				// /*
				// *eligible_income_cbd
				// */
				
				// /*
				// *eligible_income_cbd
				// */			
				// $card_statusMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_status_eib")->first();
				// 	$card_status = '';
				// 	if($card_statusMod != '')
				// 	{
				// 		$card_status = $card_statusMod->attribute_value;
				// 	}
				// /*
				// *eligible_income_cbd
				// */
				
				// /*
				// *eligible_income_cbd
				// */			
				// $company_statusMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","company_status_eib")->first();
				// 	$company_status = '';
				// 	if($company_statusMod != '')
				// 	{
				// 		$company_status = $company_statusMod->attribute_value;
				// 	}
				// /*
				// *eligible_income_cbd
				// */
				
				// /*
				// *status_cbd
				// */			
				// $se_codeMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_code_eib")->first();
				// 	$se_code = '';
				// 	if($se_codeMod != '')
				// 	{
				// 		$se_code = $se_codeMod->attribute_value;
				// 	}
				// /*
				// *status_cbd
				// */
				
				// /*
				// *CBD_remark
				// */			
				// $se_nameMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_name_eib")->first();
				// 	$se_name = '';
				// 	if($se_nameMod != '')
				// 	{
				// 		$se_name = $se_nameMod->attribute_value;
				// 	}
				// /*
				// *CBD_remark
				// */
				
				// /*
				// *status_cbd
				// */			
				// $tl_nameMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","tl_name_eib")->first();
				// 	$tl_name = '';
				// 	if($tl_nameMod != '')
				// 	{
				// 		$tl_name = $tl_nameMod->attribute_value;
				// 	}


				// 	$occuMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","occupation_eib")->first();
				// 	$occupation = '';
				// 	if($occuMod != '')
				// 	{
				// 		$occupation = $occuMod->attribute_value;
				// 	}


				// 	$bpmMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","bpm_id_eib")->first();
				// 	$bpm_id = '';
				// 	if($bpmMod != '')
				// 	{
				// 		$bpm_id = $bpmMod->attribute_value;
				// 	}


				// 	$remarkMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","remarks_eib")->first();
				// 	$remark = '';
				// 	if($remarkMod != '')
				// 	{
				// 		$remark = $remarkMod->attribute_value;
				// 	}


				$sourcingDate = date("d M, Y", strtotime($mis->DATEOFSOURCING));
				$signedDate = date("d M, Y", strtotime($mis->SIGNEDDATE));
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->application_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->APPLICATIONTYPE)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->customer_name)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->SCHEME)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->CARDTYPE)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->P1CODE)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->PRODUCT)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->OFFER)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->STATUS)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->CARDID)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->CURRENTACTIVITY)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $sourcingDate)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $signedDate)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->CARDAPPROVALSTATUS)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->APPLICATIONCREDITSTATUS)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->CHANNELCODEPERV)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->LASTREMARKSADDED)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->DSA_BRANCH)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->CASSTATUS)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('U'.$indexCounter, $mis->actual_se_name)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'U'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','U') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}


















	public function loadENBDDataCutData(Request $request)
	{
		

		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " application_no!=''";	
		if(@$request->session()->get('master_cbd_search_bank') != '' && @$request->session()->get('master_cbd_search_bank') == 2)
		{
				  if(@$request->session()->get('application_no_ENBD_master') != '')
					{
						$applicationNO = $request->session()->get('application_no_ENBD_master');
						
					
						//$whereRaw .= " AND ref_no like '%".$refNO."%'";	
						$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_CBD_master') != '')
					{
						$SMMod = $request->session()->get('team_CBD_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_ENBD_master') != '')
					{
						$start_date_application_EIB_internal = $request->session()->get('start_date_application_ENBD_master');			
						$whereRaw .= " AND issued_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
						$searchValues['start_date_application_ENBD_master'] = $start_date_application_EIB_internal;			
					}

					if($request->session()->get('end_date_application_ENBD_master') != '')
					{
						$end_date_application_EIB_internal = $request->session()->get('end_date_application_ENBD_master');			
						$whereRaw .= " AND issued_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
						$searchValues['end_date_application_ENBD_master'] = $end_date_application_EIB_internal;			
					}
		}
		else
		{
					if(@$request->session()->get('app_no_ENBD_DataCut') != '')
					{
						$refNO = $request->session()->get('app_no_ENBD_DataCut');
						
					
						$whereRaw .= " AND application_no like '%".$refNO."%'";	
						
						
					}
					if(@$request->session()->get('status_CBD_bank') != '')
					{
						$status = $request->session()->get('status_CBD_bank');
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
					
						$whereRaw .= " AND status IN (".$strStatus.")";	
						
						
					}
					
					
					if(@$request->session()->get('AECB_Status_CBD_bank') != '')
					{
						$statusAECB = $request->session()->get('AECB_Status_CBD_bank');
						$strStatusAECB = '';
						foreach($statusAECB  as $sAECB)
						{
							if($strStatusAECB == '')
							{
								$strStatusAECB = "'".$sAECB."'";
							}
							else
							{
								$strStatusAECB = $strStatusAECB.",'".$sAECB."'";
							}
						}
					
						$whereRaw .= " AND AECB_Status IN (".$strStatusAECB.")";	
						
						
					}
					if(@$request->session()->get('employee_id_CBD_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_bank');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_CBD_bank') != '')
					{
						$SMMod = $request->session()->get('smManager_CBD_bank');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_DataCut') != '')
					{
						$start_date_application_DataCut = $request->session()->get('start_date_application_DataCut');			
						$whereRaw .= " AND issued_date >='".date('Y-m-d',strtotime($start_date_application_DataCut))."'";
						$searchValues['start_date_application_DataCut'] = $start_date_application_DataCut;			
					}

					if($request->session()->get('end_date_application_DataCut') != '')
					{
						$end_date_application_DataCut = $request->session()->get('end_date_application_DataCut');			
						$whereRaw .= " AND issued_date <='".date('Y-m-d',strtotime($end_date_application_DataCut))."'";
						$searchValues['end_date_application_DataCut'] = $end_date_application_DataCut;			
					}
					if(@$request->session()->get('submission_type_internal_datacut') != '')
				{
					$submission_type_internalL = $request->session()->get('submission_type_internal_datacut');
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
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}
					
					
					

		}
		//echo $whereRaw;exit;
		$datasCBDMainCount = ENBDDataCut::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = ENBDDataCut::whereRaw($whereRaw)->orderBy("id","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			//$appStatusMod = EibBankMis::select('status')->get()->unique('status');
			$appStatusMod = '';
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			//$appAECB_StatusMod = EibBankMis::select('AECB_Status')->get()->unique('AECB_Status');
			$appAECB_StatusMod = '';
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			//$employeeIdList = EibBankMis::select('employee_id')->get()->unique('employee_id');
			$employeeIdList = '';
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			//$smManageData = EibBankMis::select('sm_manager')->get()->unique('sm_manager');
			$smManageData = '';
		/*
		*Sm Manager
		*/
		 return view("Banks/ENBD/loadENBDDataCut",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData'));










		 //return view("Banks/CBD/loadBankContentsCBDCardBankSideMTD",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','employeeIdList','smManageData'));
	}





	public function changeDatesAction(Request $request)
	{

		

		// $enbdDatesDatainfo = MainMisReportTab::where('id','<', 6141)->orderBy("id","DESC")->get();


		// foreach($enbdDatesDatainfo as $enbddata)
		// {
		// 	$enbdDatesData = MainMisReportTab::where('id',$enbddata->id)->orderBy("id","DESC")->first();

		// 	$unixTime = strtotime($enbddata->application_created1);
		// 	$appcreatedDate = date("Y-m-d", $unixTime);

		// 	$unixTime1 = strtotime($enbddata->submitted_date1);
		// 	$submitDate = date("Y-m-d", $unixTime1);


		// 	$enbdDatesData->application_created = $appcreatedDate;	
		// 	$enbdDatesData->submitted_date = $submitDate;			
		// 	$enbdDatesData->save();		
		// }


		
		// $enbdDatesDatainfo = ENBDCardsMisReport::where('id','>', 3)->orderBy("id","DESC")->get();

		

		// foreach($enbdDatesDatainfo as $enbddata)
		// {
		// 	//echo $enbddata->DATEOFSOURCING1;
		// 	$enbdDatesData = ENBDCardsMisReport::where('id',$enbddata->id)->orderBy("id","DESC")->first();


		// 	$unixTime = strtotime($enbddata->DATEOFSOURCING1);
		// 	$sourcingDate = date("Y-m-d", $unixTime);

		// 	$unixTime1 = strtotime($enbddata->FILERECEIPTDTTIME1);
		// 	$pidDate = date("Y-m-d", $unixTime1);

		// 	$unixTime2 = strtotime($enbddata->SIGNEDDATE1);
		// 	$signDate = date("Y-m-d", $unixTime2);

		// 	$unixTime3 = strtotime($enbddata->WCACTIONDATE1);
		// 	$wcDate = date("Y-m-d", $unixTime3);

		// 	$unixTime4 = strtotime($enbddata->LASTUPDATED1);
		// 	$lastupDate = date("Y-m-d", $unixTime4);

		// 	$unixTime5 = strtotime($enbddata->SOURCED_ON1);
		// 	$souecedDate = date("Y-m-d", $unixTime5);

		// 	$unixTime6 = strtotime($enbddata->REPORTGENDATE1);
		// 	$reportDate = date("Y-m-d", $unixTime6);


		// 	$enbdDatesData->DATEOFSOURCING = $sourcingDate;	
		// 	$enbdDatesData->FILERECEIPTDTTIME = $pidDate;
		// 	$enbdDatesData->SIGNEDDATE = $signDate;	
		// 	$enbdDatesData->WCACTIONDATE = $wcDate;
		// 	$enbdDatesData->LASTUPDATED = $lastupDate;	
		// 	$enbdDatesData->SOURCED_ON = $souecedDate;
		// 	$enbdDatesData->REPORTGENDATE = $reportDate;
		// 	$enbdDatesData->save();		
			
		// }
		
		// exit;
		// return "Hello";
		
		


		echo "done";


	}


	public function recordsInsertedAction(Request $request)
	{
			
		$enbdDatesDatainfo = MainMisReport::where('id','>', 41388)->where('file_source','manual')->orderBy("id","DESC")->get();


		//$row_values = array();
		//echo "<pre>";

		foreach($enbdDatesDatainfo as $enbdman)
		{
			//$enbdman->VISA_Expiry_DATE;


			$enbdparenttblData = ENBDDepartmentFormEntry::where('application_no', $enbdman->application_id)->orderBy("id","DESC")->first();

			if($enbdparenttblData)
			{

			}
			else
			{
				$visadate = str_replace("/","-",$enbdman->VISA_Expiry_DATE);
				$dobdate = str_replace("/","-",$enbdman->DOB);
				//print_r (explode("-",$dobdate));

				//print_r($vdate);


				$unixTime = strtotime($enbdman->date_of_submission);
				$submissionDate = date("Y-m-d", $unixTime);

				$unixTime1 = strtotime($dobdate);
				$birthDate = date("Y-m-d", $unixTime1);

				$unixTime2 = strtotime($enbdman->PRE_CALLING);
				$callDate = date("Y-m-d", $unixTime2);

				$unixTime3 = strtotime($visadate);
				$visaexpireDate = date("Y-m-d", $unixTime3);

				$enbdCurrentActivityData = CurrentActivity::where('id', $enbdman->current_activity)->orderBy("id","DESC")->first();

				if($enbdCurrentActivityData)
				{
					$connectivityName = $enbdCurrentActivityData->name;
				}
				else
				{
					$connectivityName = NULL;
				}

				$enbdMonthlyEndsData = MonthlyEnds::where('id', $enbdman->monthly_ends)->orderBy("id","DESC")->first();


				if($enbdMonthlyEndsData)
				{
					$enbdMonthlyEndsDataName = $enbdMonthlyEndsData->name;
				}
				else
				{
					$enbdMonthlyEndsDataName = NULL;
				}






				$row_values = array(
														
					'customer_name_enbd' => $enbdman->cm_name,
					'customer_mobile_enbd' => $enbdman->CV_MOBILE_NUMBER,
					'customer_office_number_enbd' => $enbdman->EV_DIRECT_OFFICE_NO,
					'customer_email_enbd' => $enbdman->E_MAILADDRESS,
					'salaried_status_enbd' => $enbdman->SALARIED,
					'salary_enbd' => $enbdman->SALARY,
					'nationality_enbd' => $enbdman->NATIONALITY,
					'passport_enbd' => $enbdman->PASSPORT_NO,
					'designation_enbd' => $enbdman->DESIGNATION,
					'mother_maiden_enbd' => NULL,
					'dob_enbd' => $birthDate,
					'eida_enbd' => $enbdman->EIDA,
					'ev_done_enbd' => $enbdman->EV,
					'reference_mobile_enbd' => $enbdman->REFERENCE_MOBILE_NO,
					'reference_enbd' => $enbdman->REFERENCE_NAME,
					'pre_calling_enbd' => $callDate,
					'type_of_incomeproof_enbd' => $enbdman->Type_of_Income_Proof,
					'visa_expiry_enbd' => $visaexpireDate,
					'applicaion_id_enbd' => $enbdman->application_id,
					'application_type_enbd' => $enbdman->application_type,
					'submission_date_enbd' => $submissionDate,
					'product_enbd' => $enbdman->PRODUCT,
					'card_enbd' => $enbdman->Card_Name,
					'company_enbd' => $enbdman->fv_company_name,
					'company_name_visa_enbd' => $enbdman->company_name_as_per_visa,
					'account_no_enbd' => $enbdman->ACCOUNT_NO,
					'account_status_enbd' => $enbdman->ACCOUNT_STATUS,
					'approved_enbd' => $enbdman->approved_notapproved,
					'iban_enbd' => $enbdman->IBAN,
					'remarks_enbd' => $enbdman->last_remarks_added,
					'length_service_enbd' => NULL,
					'ale_nale_enbd' => $enbdman->ALE_NALE,
					'current_activity_enbd' => $connectivityName,
					'monthly_ends_enbd' => $enbdMonthlyEndsDataName,
					'load_source_enbd' => $enbdman->lead_source,
					'team_leader_enbd' => $enbdman->TL,
					'se_name_enbd' => $enbdman->SE_CODE_NAME,
					'submission_type_enbd' => 'Manual',
					
						
					
				);

				
				



				// $row_values['customer_name_enbd'][]=$enbdman->cm_name;
				// $row_values['customer_mobile_enbd'][]=$enbdman->CV_MOBILE_NUMBER;
				// $row_values['customer_office_number_enbd'][]=$enbdman->EV_DIRECT_OFFICE_NO;
				// $row_values['customer_email_enbd'][]=$enbdman->E_MAILADDRESS;
				// $row_values['salaried_status_enbd'][]=$enbdman->SALARIED;
				// $row_values['salary_enbd'][]=$enbdman->SALARY;
				// $row_values['nationality_enbd'][]=$enbdman->NATIONALITY;
				// $row_values['passport_enbd'][]=$enbdman->PASSPORT_NO;
				// $row_values['designation_enbd'][]=$enbdman->DESIGNATION;
				// $row_values['mother_maiden_enbd'][]=NULL;
				// $row_values['dob_enbd'][]=$birthDate;
				// $row_values['eida_enbd'][]=$enbdman->EIDA;
				// $row_values['ev_done_enbd'][]=$enbdman->EV;

				// $row_values['reference_mobile_enbd'][]=$enbdman->REFERENCE_MOBILE_NO;
				// $row_values['reference_enbd'][]=$enbdman->REFERENCE_NAME;
				// $row_values['pre_calling_enbd'][]=$callDate;
				// $row_values['type_of_incomeproof_enbd'][]=$enbdman->Type_of_Income_Proof;
				// $row_values['visa_expiry_enbd'][]=$visaexpireDate;
				// $row_values['applicaion_id_enbd'][]=$enbdman->application_id;
				// $row_values['application_type_enbd'][]=$enbdman->application_type;
				// $row_values['submission_date_enbd'][]=$submissionDate;
				// $row_values['product_enbd'][]=$enbdman->PRODUCT;
				// $row_values['card_enbd'][]=$enbdman->Card_Name;
				// $row_values['company_enbd'][]=$enbdman->fv_company_name;
				// $row_values['company_name_visa_enbd'][]=$enbdman->company_name_as_per_visa;
				// $row_values['account_no_enbd'][]=$enbdman->ACCOUNT_NO;
				// $row_values['account_status_enbd'][]=$enbdman->ACCOUNT_STATUS;
				// $row_values['approved_enbd'][]=$enbdman->approved_notapproved;
				// $row_values['iban_enbd'][]=$enbdman->IBAN;
				// $row_values['remarks_enbd'][]=$enbdman->last_remarks_added;
				// $row_values['length_service_enbd'][]=NULL;
				// $row_values['ale_nale_enbd'][]=$enbdman->ALE_NALE;
				// $row_values['current_activity_enbd'][]=$enbdCurrentActivityData->name;
				// $row_values['monthly_ends_enbd'][]=$enbdMonthlyEndsData->name;
				// $row_values['load_source_enbd'][]=$enbdman->lead_source;
				// $row_values['team_leader_enbd'][]=$enbdman->TL;
				// $row_values['se_name_enbd'][]=$enbdman->SE_CODE_NAME;
				// $row_values['submission_type_enbd'][]='Manual';


				$form_id =6;
				$entry_obj = new ENBDDepartmentFormEntry();
				$entry_obj->form_id = $form_id;
				$entry_obj->form_title = 'ENBD Internal MIS Manual';
				if($form_id==6)
				{
					$entry_obj->submission_type = 'Manual';
					$entry_obj->customer_name = $enbdman->cm_name;
					$entry_obj->mobile = $enbdman->CV_MOBILE_NUMBER;
					$entry_obj->email = $enbdman->E_MAILADDRESS;
					$entry_obj->salary = $enbdman->SALARY;
					$entry_obj->nationality = $enbdman->NATIONALITY;
					$entry_obj->passport_no = $enbdman->PASSPORT_NO;
					$entry_obj->designation = $enbdman->DESIGNATION;
					$entry_obj->dob = date("Y-m-d",strtotime($birthDate));
					$entry_obj->application_no = $enbdman->application_id;
					$entry_obj->application_type = $enbdman->application_type;
					$entry_obj->submission_date = date("Y-m-d",strtotime($submissionDate));
					$entry_obj->product_name = $enbdman->PRODUCT;
					$entry_obj->company_name = $enbdman->fv_company_name;
					$entry_obj->account_number = $enbdman->ACCOUNT_NO;
					$entry_obj->account_status = $enbdman->ACCOUNT_STATUS;
					$entry_obj->current_activity = $connectivityName;
					$entry_obj->monthly_ends = $enbdMonthlyEndsDataName;
					$entry_obj->remarks = $enbdman->last_remarks_added;
					//$entry_obj->remarks = date("Y-m-d",strtotime($postDataInput['length_service_enbd']));
					$entry_obj->approved_status = $enbdman->approved_notapproved;
					$entry_obj->card_name = $enbdman->Card_Name;
					$entry_obj->tl_name = $enbdman->TL;
					$entry_obj->se_name = $enbdman->SE_CODE_NAME;
				}
				
				$entry_obj->save();
				$insertID = $entry_obj->id;
				
				$child_obj = new ENBDDepartmentFormChildEntry();
				foreach($row_values as $key=>$value)
				{
					$child_obj = new ENBDDepartmentFormChildEntry();
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = $form_id;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
				}
			}


			
			
			
			
		}


		// print_r($row_values);

		
		// foreach($row_values as $key=>$value)
		// {
		// 	echo $key;
			
		// }

		// exit;
		// return "Hello";
















		
		
			
			
			
			/*
			*child entry 
			*end code
			*/
            $request->session()->flash('message','Record added Successfully.');
			//return redirect('enbdCardsManagement');
	}

















	
public function exportENBDDataCutData(Request $request)
{
		$requestPost = $request->input();
		 $parameters = $request->input(); 
		/* echo "<pre>";
		print_r($parameters);
		exit; */
		 $selectedId = $parameters['selectedIds'];
		 
		$filename = 'ENBD_DataCut_Cards_'.date("d-m-Y").'.xlsx';
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A1:U1');
		$sheet->setCellValue('A1', 'ENBD DataCut - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
		$indexCounter = 2;
		$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('B'.$indexCounter, strtoupper('App Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('C'.$indexCounter, strtoupper('Card Type'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('D'.$indexCounter, strtoupper('Product'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('E'.$indexCounter, strtoupper('Customer Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('F'.$indexCounter, strtoupper('Customer Mobile'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('G'.$indexCounter, strtoupper('RBE Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('H'.$indexCounter, strtoupper('Company Name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('I'.$indexCounter, strtoupper('P1 Code'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('J'.$indexCounter, strtoupper('Issued Date'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('K'.$indexCounter, strtoupper('Card id'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('L'.$indexCounter, strtoupper('Current Activity'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('M'.$indexCounter, strtoupper('Date of Sourcing'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('N'.$indexCounter, strtoupper('Signed Date'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('O'.$indexCounter, strtoupper('Card Approval Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('P'.$indexCounter, strtoupper('Application Credit Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('Q'.$indexCounter, strtoupper('Channel code'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('R'.$indexCounter, strtoupper('Remarks'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('S'.$indexCounter, strtoupper('DSA Barnch'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		// $sheet->setCellValue('T'.$indexCounter, strtoupper('CAS Status'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

		//$sheet->setCellValue('H'.$indexCounter, strtoupper('Product'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
		//$sheet->setCellValue('U'.$indexCounter, strtoupper('Passport'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
		
		$sn = 1;
		foreach ($selectedId as $sid) {
			
			$mis =  ENBDDataCut::where("id",$sid)->first();
			
			
			   
			 $indexCounter++; 

			
			
		
			

			$issueDate = date("d M, Y", strtotime($mis->issued_date));
			$signedDate = date("d M, Y", strtotime($mis->SIGNEDDATE));
			/*
			*status_cbd
			*/
			$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, $mis->application_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $mis->card_type)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $mis->product)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $mis->customer_name)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $mis->mobile)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $mis->rbe_name)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $mis->company_name)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, $mis->p1_code)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, $issueDate)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('K'.$indexCounter, $mis->CARDID)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('L'.$indexCounter, $mis->CURRENTACTIVITY)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('M'.$indexCounter, $sourcingDate)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('N'.$indexCounter, $signedDate)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('O'.$indexCounter, $mis->CARDAPPROVALSTATUS)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('P'.$indexCounter, $mis->APPLICATIONCREDITSTATUS)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('Q'.$indexCounter, $mis->CHANNELCODEPERV)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('R'.$indexCounter, $mis->LASTREMARKSADDED)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('S'.$indexCounter, $mis->DSA_BRANCH)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('T'.$indexCounter, $mis->CASSTATUS)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('U'.$indexCounter, $mis->actual_se_name)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn++;
			
		}
		
		
		  for($col = 'A'; $col !== 'U'; $col++) {
		   $sheet->getColumnDimension($col)->setAutoSize(true);
		}
		
		$spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
			for($index=1;$index<=$indexCounter;$index++)
			{
				  foreach (range('A','U') as $col) {
						$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
				  }
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save(public_path('uploads/exportEmp/'.$filename));	
			echo $filename;
			exit;
}





public function getSourceCode(Request $request)
{
	$enbdParenttblData = ENBDDepartmentFormEntry::orderBy("id","DESC")->get();

	//return $enbdParenttblData;
	//return "Hello";
	//echo "<pre>";

	foreach($enbdParenttblData as $enbdData)
	{
		$enbdData->se_name;
		$sourceCode = explode("_",$enbdData->se_name);

		//print_r($sourceCode);

		if (count($sourceCode) === 0) 
		{
			// list is empty.
	   	}
		else
		{
				if(count($sourceCode)==2)
				{
					$enbdParentData = ENBDDepartmentFormEntry::where('id',$enbdData->id)->orderBy("id","DESC")->first();
					$empDetailsData = Employee_details::where('source_code',$sourceCode[1])->orderBy("id","DESC")->first();


					if($empDetailsData)
					{
						$enbdParentData->emp_id = $empDetailsData->emp_id;
						$enbdParentData->emp_name = $empDetailsData->emp_name;	
					}		
					$enbdParentData->source_code = $sourceCode[1];				
					$enbdParentData->save();
					//echo "WHELLO";
				}
				else
				{

				}

				
		}

		


	


	}

	echo "Done";

}



	

}
