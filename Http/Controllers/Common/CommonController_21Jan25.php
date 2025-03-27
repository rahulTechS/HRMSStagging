<?php

namespace App\Http\Controllers\Common;

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
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\ENBDLoanMIS\ENBDLoanMIS;
use App\Models\PerformanceFlagRules\MasterPayoutPre;
use App\Http\Controllers\Attribute\DepartmentFormController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use Illuminate\Support\Facades\DB;

use Session;
ini_set("max_execution_time", 0);
class CommonController extends Controller
{
   
    public function commonTabs($fileType=NULL,Request $request)
    {		
		$fileType = $fileType;
		if($fileType=='ENBD')
		{
			return view("Common/commonTabsENBD",compact('fileType'));
		}
		else if($fileType=='MissingBooking')
		{
			return view("Common/commonTabsMissingBooking",compact('fileType'));
		}
		else if($fileType=='ENBDPL')
		{
			return view("Common/commonTabsENBDPL",compact('fileType'));
		}
		else
		{
			return view("Common/commonTabs",compact('fileType'));
		}
    }

	public function commonCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/commonCalRender",compact('monthSelected','yearSelected'));
    }

	public function loginCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/loginCalRender",compact('monthSelected','yearSelected'));
    }

	public function ENBDCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/ENDBJonusCalRender",compact('monthSelected','yearSelected'));
    }

	public function MissingBookingCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/MissingBookingCalRender",compact('monthSelected','yearSelected'));
    }

	public function ENBDPLCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/ENBDPLCalRender",compact('monthSelected','yearSelected'));
    }

	public function bankCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/bankCalRender",compact('monthSelected','yearSelected'));
    }

	public function bookingCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/bookingCalRender",compact('monthSelected','yearSelected'));
    }

	public function mtdCalRenderTab(Request $request)
    {
	   $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Common/mtdCalRender",compact('monthSelected','yearSelected'));
    }


				public function FileUploadExcel(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
							//echo '<pre>';
							//print_r($request->uploadedDate);exit;
					$response = array();
				  /* $request->validate([

					'file' => 'required|mimes:csv,txt|max:10000',

				]); */
				 /* $validator = Validator::make($request->only('file'), [
					'file' => 'required|mimes:csv,xlsx|max:100000000000',
				]);

				   if ($validator->fails()) {
					   $response['code'] = '300';
					   $response['message'] = $validator;
					  
					}
					else
					{ */
					$fileType = $request->fileType;
					$fileName = $fileType.'_MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/MashreqMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						if($fileType=='Login')
						{
							$tableName = 'mashreq_login_import_file';
						}
						if($fileType=='Bank')
						{
							$tableName = 'mashreq_bank_import_file';
						}
						if($fileType=='Booking')
						{
							$tableName = 'mashreq_booking_import_file';
						}
						if($fileType=='MTD')
						{
							$tableName = 'mashreq_mtd_import_file';
						}
						

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
				}

				public function FileUploadExcelENBD(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
							
					$response = array();
				  
					$fileType = $request->fileType;
					$fileName = $fileType.'_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDJonus/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
							$tableName = 'enbd_jonus_import_file';
						
						

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
				}


				public function ENDBJonusFileImport(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
				
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('enbd_jonus_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{							
							if(count($sheetData[$k])!= 62)
							{
								$fileInfo = DB::table('enbd_jonus_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							$applicationsid_value = array('applicationsid' => $sheetData[$k][0]);
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$applicationsid = $sheetData[$k][0];
							


							$file_values = array(	'name' => $sheetData[$k][1],
													'asset_details' => $sheetData[$k][2],
													'constitution' => $sheetData[$k][3],
													'cust_category' => $sheetData[$k][4],
													'prof_qualification' => $sheetData[$k][5],
													'signeddatetime' => ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00'),
													'asset_cost' => str_replace(',','',$sheetData[$k][7]),
													'loan_amount' => str_replace(',','',$sheetData[$k][8]),
													'tenure' => $sheetData[$k][9],
													'channel' => $sheetData[$k][10],
													'product' => $sheetData[$k][11],
													'scheme_group' => $sheetData[$k][12],
													'scheme_name' => $sheetData[$k][13],
													'promotion_scheme' => $sheetData[$k][14],
													'loan_type' => $sheetData[$k][15],
													'branch_name' => $sheetData[$k][16],
													'rbe_name' => $sheetData[$k][17],
													'cpv_fired' => $sheetData[$k][18],
													'cpv_status' => $sheetData[$k][19],
													'dsa' => $sheetData[$k][20],
													'credit_status' => $sheetData[$k][21],
													'employer_name' => $sheetData[$k][22],
													'employer_catg' => $sheetData[$k][23],
													'previous_liability' => $sheetData[$k][24],
													'topup_exist_loan_no' => $sheetData[$k][25],
													'topup_amount_req' => str_replace(',','',$sheetData[$k][26]),
													'topup_os_principal' => str_replace(',','',$sheetData[$k][27]),
													'topup_total_os' => str_replace(',','',$sheetData[$k][28]),
													'to_bank' => $sheetData[$k][29],
													'to_branch' => $sheetData[$k][30],
													'to_acc_no' => $sheetData[$k][31],
													'to_os_amt' => str_replace(',','',$sheetData[$k][32]),
													'to_add_amt' => str_replace(',','',$sheetData[$k][33]),
													'auth_status' => $sheetData[$k][34],
													'disbursal_status' => $sheetData[$k][35],
													'disbursal_datetime' => ($sheetData[$k][36]?date('Y-m-d',strtotime($sheetData[$k][36])):'0000-00-00'),
													'cas_status' => $sheetData[$k][37],
													'manufacturer_name' => $sheetData[$k][38],
													'showroom_name' => $sheetData[$k][39],
													'dealer_sales_rep_name' => $sheetData[$k][40],
													'rate' => str_replace(',','',$sheetData[$k][41]),
													'rate_type' => $sheetData[$k][42],
													'margin_money' => $sheetData[$k][43],
													'customer_type' => $sheetData[$k][44],
													'dob_doi' => ($sheetData[$k][45]?date('Y-m-d',strtotime($sheetData[$k][45])):'0000-00-00'),
													'loan_nature' => $sheetData[$k][46],
													'nationality' => $sheetData[$k][47],
													'uae_national' => $sheetData[$k][48],
													'interest_start_datetime' => $sheetData[$k][49],
													'payment_mode' => $sheetData[$k][50],
													'loan_purpose' => $sheetData[$k][51],
													'last_updatetimed' => ($sheetData[$k][52]?date('Y-m-d',strtotime($sheetData[$k][52])):'0000-00-00'),
													'discrepancy_flag' => $sheetData[$k][53],
													'filereceiptdttime' => ($sheetData[$k][54]?date('Y-m-d',strtotime($sheetData[$k][54])):'0000-00-00'),
													'sourced_on' => ($sheetData[$k][55]?date('Y-m-d',strtotime($sheetData[$k][55])):'0000-00-00'),
													'referral_group' => $sheetData[$k][56],
													'referral_code' => $sheetData[$k][57],
													'referral_name' => $sheetData[$k][58],
													'p1code' => $sheetData[$k][59],
													'laa_product_id_c' => $sheetData[$k][60],
													'last_remarks_added' =>$sheetData[$k][61],
													'created_at' => date('Y-m-d H:i:s'),
													'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							
							$whereRaw = " applicationsid ='".$applicationsid."'";
							$check = DB::table('enbd_jonus_import_file_data')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('enbd_jonus_import_file_data')->where('applicationsid', $applicationsid)->update($file_values);
								
							}
							else
							{
								$all_values = $file_values + $applicationsid_value;
								DB::table('enbd_jonus_import_file_data')->insert($all_values);
							}
							$Updates = array('jonus_status' => 1);
							DB::table('enbd_loan_mis')->where('app_id', $applicationsid)->where('jonus_status', 0)->update($Updates);
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}

			public function FileUploadExcelMashreqMissingBookingLink(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
							
					$response = array();
				  
					$fileType = $request->fileType;
					$fileName = $fileType.'_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/MashreqMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
							$tableName = 'mashreq_missing_link_import_file';
						
						

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
				}


				public function MashreqMissingBookingLinkFileImport(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
				
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_missing_link_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{							
							if(count($sheetData[$k])!= 12)
							{
								$fileInfo = DB::table('mashreq_missing_link_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}
							$instanceid = $sheetData[$k][0];
							$ref_no = $sheetData[$k][8];
							$emp_id = $sheetData[$k][9];
							$team = $sheetData[$k][10];
							if(trim($ref_no)=='')
							{
								continue;
							}

							if(trim($emp_id)=='')
							{
								continue;
							}

							if(trim($team)=='')
							{
								continue;
							}

							
							$Employee_details_data = DepartmentFormController::getEmployeeDetails($emp_id);	

							$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

							if($emp_name=='')
							{
								continue;
							}

							

							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							


							$whereRawParent = " ref_no ='".$ref_no."'";
							$checkParent = DB::table('department_form_parent_entry')->whereRaw($whereRawParent)->get();

							$submission_date = ($sheetData[$k][11]?date('Y-m-d',strtotime($sheetData[$k][11])):'0000-00-00');

							if(count($checkParent)==0)
							{	
								$file_valuesParent = array(
													'user_id' =>$user_id,					
													'application_id' => $instanceid,
													'ref_no' => $ref_no,
													'form_id' => 1,
													'form_title' => 'Credit Card Submission Form Import',
													'customer_name' => $sheetData[$k][2],
													'submission_date' => $submission_date,
													'emp_id' => $emp_id,
													'emp_name' => $emp_name,
													'team' => $team,
													'missing_booking_link_status' => 1);
									
								$parent_id = DB::table('department_form_parent_entry')->insertGetId($file_valuesParent);

								$submission_date_val = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'submission_date','attribute_value' => $submission_date);
								DB::table('department_form_child_entry')->insert($submission_date_val);

								$seller_id = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'seller_id','attribute_value' => $sheetData[$k][4]);
								DB::table('department_form_child_entry')->insert($seller_id);

								$customer_name = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'customer_name','attribute_value' => $sheetData[$k][2]);
								DB::table('department_form_child_entry')->insert($customer_name);

								$salary = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'salary','attribute_value' => $sheetData[$k][7]);
								DB::table('department_form_child_entry')->insert($salary);

								$ref_noVal = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'ref_no','attribute_value' => $ref_no);
								DB::table('department_form_child_entry')->insert($ref_noVal);

								$ref_noVal = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'team','attribute_value' => $team);
								DB::table('department_form_child_entry')->insert($ref_noVal);

								$ref_noVal = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'emp_id','attribute_value' => $emp_id);
								DB::table('department_form_child_entry')->insert($ref_noVal);

								$ref_noVal = array('form_id'=>'1', 'parent_id' => $parent_id,'attribute_code' => 'emp_name','attribute_value' => $emp_name);
								DB::table('department_form_child_entry')->insert($ref_noVal);

								MashreqBookingMIS::where('instanceid', $instanceid)
										->update(['ref_no' => $ref_no,'emp_id' => $emp_id,'emp_name' => $emp_name,'team' => $team]);
								
							}
							else
							{			

									MashreqBookingMIS::where('instanceid', $instanceid)
										->update(['ref_no' => $ref_no,'emp_id' => $emp_id,'emp_name' => $emp_name,'team' => $team]);

									DepartmentFormEntry::where('ref_no', $sheetData[$k][8])
										->update(['application_id' => $instanceid,'emp_id' => $emp_id,'emp_name' => $emp_name,'team' => $team, 'form_title' => 'Credit Card Submission Form Import Update',]);
									
								
							}
							///////////////////////////////////
							


							
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}


				public function mashreqLoginFileImport(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
				
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_login_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							if(count($sheetData[$k])!= 27)
							{
								$fileInfo = DB::table('mashreq_login_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							$ref_value = array('ref_no' => $sheetData[$k][7]);
							//echo date('Y-m-d',strtotime(PHPExcel_Shared_Date::ExcelToPHP($sheetData[$k][4])));
							//echo "<pre>";
							//echo print_r($sheetData);exit;
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$ref_no = $sheetData[$k][7];
							$team = '';
							$emp_id = '';
							$emp_name = '';
							
							
							if(@$ref_no !='' && @$ref_no !='N')
							{
								$Internal_info = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->first();

								$customer_mobile = @$Internal_info->customer_mobile;
								$team = @$Internal_info->team;
								$emp_id = @$Internal_info->emp_id;
								$emp_name = @$Internal_info->emp_name;
							}
							///////////////////////////////////////
							///////////////////////////////////////

							$min_startdate = str_replace(" 00:00:00.0","",$sheetData[$k][26]);
							$min_startdate = str_replace("/","-",$min_startdate);
							if($min_startdate=='')
							{
								$min_startdate = '0000-00-00';
							}


							$file_values = array('agent_full_name' => $sheetData[$k][0],
												'all_cda_deviation' => $sheetData[$k][1],
												'app_decision' => $sheetData[$k][2],
												'app_decisiondetails' => $sheetData[$k][3],
												'application_date' => ($sheetData[$k][4]?date('Y-m-d',strtotime($sheetData[$k][4])):'0000-00-00'),
												'application_status' => $sheetData[$k][5],
												'applicationid' => $sheetData[$k][6],											
												'booked_flag' => $sheetData[$k][8],
												'bureau_score' => $sheetData[$k][9],
												'bureau_segmentation' => $sheetData[$k][10],
												'card_type' => $sheetData[$k][11],
												'cda_descision' => $sheetData[$k][12],
												'cdafinalsalary' => $sheetData[$k][13],
												'cif' => $sheetData[$k][14],
												'customer_name' => $sheetData[$k][15],
												'disbursed_date' => $sheetData[$k][16],
												'employee_category_desc' => $sheetData[$k][17],
												'employer_name' => $sheetData[$k][18],
												'last_comment' => $sheetData[$k][19],
												'mis_date' => ($sheetData[$k][20]?date('Y-m-d',strtotime($sheetData[$k][20])):'0000-00-00'),
												'mrs_score' => $sheetData[$k][21],
												'seller_id' => $sheetData[$k][22],
												'stl_format' => $sheetData[$k][23],
												'nationality' => $sheetData[$k][24],
												'seller_channel_name' => $sheetData[$k][25],
												'min_startdate' => $min_startdate,
												'team' => $team,
												'emp_id' => $emp_id,
												'emp_name' => $emp_name,
												'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							
							$whereRaw = " ref_no ='".$ref_no."' and ref_no!='N'";
							$check = DB::table('mashreq_login_data')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('mashreq_login_data')->where('ref_no', $ref_no)->update($file_values);
								$app_id_array = array('application_id' => $sheetData[$k][6],
									'all_cda_deviation' => $sheetData[$k][1],
									'app_decision' => $sheetData[$k][2],
									'status_login' => $sheetData[$k][5],
									'bureau_score' => $sheetData[$k][9],
									'bureau_segmentation' => $sheetData[$k][10],
									'card_type' => $sheetData[$k][11],
									'cda_descision' => $sheetData[$k][12],
									'cdafinalsalary' => $sheetData[$k][13],
									'employee_category_desc' => $sheetData[$k][17],
									'employer_name' => $sheetData[$k][18],
									'last_comment' => $sheetData[$k][19],
									'mrs_score' => $sheetData[$k][21]);
								DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->update($app_id_array);
								
							}
							else
							{
								$all_values = $file_values + $ref_value;
								DB::table('mashreq_login_data')->insert($all_values);

								$app_id_array = array('application_id' => $sheetData[$k][6],
									'all_cda_deviation' => $sheetData[$k][1],
									'app_decision' => $sheetData[$k][2],
									'status_login' => $sheetData[$k][5],
									'bureau_score' => $sheetData[$k][9],
									'bureau_segmentation' => $sheetData[$k][10],
									'card_type' => $sheetData[$k][11],
									'cda_descision' => $sheetData[$k][12],
									'cdafinalsalary' => $sheetData[$k][13],
									'employee_category_desc' => $sheetData[$k][17],
									'employer_name' => $sheetData[$k][18],
									'last_comment' => $sheetData[$k][19],
									'mrs_score' => $sheetData[$k][21]);
								DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->update($app_id_array);
							}
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}


			public function mashreqBankFileImport(Request $request)
				{

					$user_id = $request->session()->get('EmployeeId');
					
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_bank_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							if(count($sheetData[$k])!= 24)
							{
								$fileInfo = DB::table('mashreq_bank_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							$ref_value = array('application_ref_no' => $sheetData[$k][4]);
							//echo date('Y-m-d',strtotime(PHPExcel_Shared_Date::ExcelToPHP($sheetData[$k][4])));
							//echo "<pre>";
							//echo print_r($sheetData);exit;
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$ref_no = $sheetData[$k][4];
							$team = '';
							$emp_id = '';
							$emp_name = '';
							
							
							if(@$ref_no !='')
							{
								$Internal_info = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->first();

								$customer_mobile = @$Internal_info->customer_mobile;
								$team = @$Internal_info->team;
								$emp_id = @$Internal_info->emp_id;
								$emp_name = @$Internal_info->emp_name;
							}
							///////////////////////////////////////
							///////////////////////////////////////


							$file_values = array('rcms_id' => $sheetData[$k][0],
												'agent_full_name' => $sheetData[$k][1],
												'all_cda_deviation' => $sheetData[$k][2],												
												'application_date' => ($sheetData[$k][3]?date('Y-m-d',strtotime($sheetData[$k][3])):'0000-00-00'),
												'booked_flag' => $sheetData[$k][5],
												'bureau_score' => $sheetData[$k][6],											
												'bureau_segmentation' => $sheetData[$k][7],
												'card_type' => $sheetData[$k][8],
												'cda_descision' => $sheetData[$k][9],
												'cdafinalsalary' => $sheetData[$k][10],
												'customer_name' => $sheetData[$k][11],
												'disbursed_date' => ($sheetData[$k][12]?date('Y-m-d',strtotime($sheetData[$k][12])):'0000-00-00'),
												'employee_category_desc' => $sheetData[$k][13],
												'employer_name' => $sheetData[$k][14],
												'final_dsr' => $sheetData[$k][15],
												'last_comment' => $sheetData[$k][16],
												'mrs_score' => $sheetData[$k][17],
												'seller_id' => $sheetData[$k][18],
												'status' => $sheetData[$k][19],
												'remarks' => $sheetData[$k][20],
												'app_decision' => $sheetData[$k][21],
												'app_decisiondetails' => $sheetData[$k][22],
												'stl_yn' => $sheetData[$k][23],
												'team' => $team,
												'emp_id' => $emp_id,
												'emp_name' => $emp_name,
												'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							$application_ref_no = $sheetData[$k][4];
							$whereRaw = " application_ref_no ='".$application_ref_no."'";
							$check = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('mashreq_bank_mis')->where('application_ref_no', $application_ref_no)->update($file_values);
								
							}
							else
							{
								$all_values = $file_values + $ref_value;
								DB::table('mashreq_bank_mis')->insert($all_values);
							}
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}


			public function mashreqBookingFileImport(Request $request)
				{					
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_booking_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							if(count($sheetData[$k])!= 16)
							{
								$fileInfo = DB::table('mashreq_booking_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							$ref_value = array('instanceid' => $sheetData[$k][0]);
							//echo date('Y-m-d',strtotime(PHPExcel_Shared_Date::ExcelToPHP($sheetData[$k][4])));
							//echo "<pre>";
							//echo print_r($sheetData);exit;
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$ref_no = '';
							$team = '';
							$emp_id = '';
							$emp_name = '';
							$applicationidcheck = DB::table('mashreq_login_data')->whereRaw("applicationid ='".$sheetData[$k][0]."'")->get();

							if(count($applicationidcheck)>0)
							{
								$ref_no = $applicationidcheck[0]->ref_no;
							}

							
							if(@$ref_no !='')
							{
								$Internal_info = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->first();

								$customer_mobile = @$Internal_info->customer_mobile;
								$team = @$Internal_info->team;
								$emp_id = @$Internal_info->emp_id;
								$emp_name = @$Internal_info->emp_name;
							}
							///////////////////////////////////////instanceid

							$file_values = array('ref_no' => $ref_no,
												'cif_cis_number' => $sheetData[$k][1],
												'customername' => $sheetData[$k][2],
												'plastictype' => $sheetData[$k][3],
												'sellerid' => $sheetData[$k][4],												
												'sellername' => $sheetData[$k][5],
												'dateofdisbursal' => ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00'),
												'cdafinalsalary' => str_replace('.000','.00',$sheetData[$k][7]),
												'card_status' => $sheetData[$k][8],
												'payout' => $sheetData[$k][9],
												'agent_name' => $sheetData[$k][10],
												'product' => $sheetData[$k][11],
												'team_manager' => $sheetData[$k][12],
												'points' => $sheetData[$k][13],
												'kiosk' => $sheetData[$k][14],
												'vertical' => $sheetData[$k][15],
												'team' => $team,
												'emp_id' => $emp_id,
												'emp_name' => $emp_name,
												'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							$instanceid = $sheetData[$k][0];
							$whereRaw = " instanceid ='".$instanceid."'";
							$check = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('mashreq_booking_mis')->where('instanceid', $instanceid)->update($file_values);
								
							}
							else
							{
								$all_values = $file_values + $ref_value;
								DB::table('mashreq_booking_mis')->insert($all_values);
							}
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}

			public function mashreqMTDFileImport(Request $request)
				{					
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_mtd_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							if(count($sheetData[$k])!= 15)
							{
								$fileInfo = DB::table('mashreq_mtd_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}
							if(trim($sheetData[$k][0])=='')
							{
								continue;
							}
							$ref_value = array('instanceid' => $sheetData[$k][0]);
							//echo date('Y-m-d',strtotime(PHPExcel_Shared_Date::ExcelToPHP($sheetData[$k][4])));
							//echo "<pre>";
							//echo print_r($sheetData);exit;
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$ref_no = '';
							$team = '';
							$emp_id = '';
							$emp_name = '';
							$applicationidcheck = DB::table('mashreq_login_data')->whereRaw("applicationid ='".$sheetData[$k][0]."'")->get();

							if(count($applicationidcheck)>0)
							{
								$ref_no = $applicationidcheck[0]->ref_no;
							}

							
							if(@$ref_no !='')
							{
								$Internal_info = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->first();

								$customer_mobile = @$Internal_info->customer_mobile;
								$team = @$Internal_info->team;
								$emp_id = @$Internal_info->emp_id;
								$emp_name = @$Internal_info->emp_name;
							}
							///////////////////////////////////////
							///////////////////////////////////////

							$file_values = array('ref_no' => $ref_no,
												'cif_cis_number' => $sheetData[$k][1],
												'customername' => $sheetData[$k][2],
												'plastictype' => $sheetData[$k][3],
												'sellerid' => $sheetData[$k][4],												
												'sellername' => $sheetData[$k][5],
												'dateofdisbursal' => ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00'),
												'cdafinalsalary' => $sheetData[$k][7],
												'card_status' => $sheetData[$k][8],
												'payout' => $sheetData[$k][9],
												'agents_name' => $sheetData[$k][10],
												'points' => $sheetData[$k][11],
												'product' => $sheetData[$k][12],
												'team_manager' => $sheetData[$k][13],
												'vertical' => $sheetData[$k][14],	
												'team' => $team,
												'emp_id' => $emp_id,
												'emp_name' => $emp_name,
												'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							$instanceid = $sheetData[$k][0];
							$whereRaw = " instanceid ='".$instanceid."'";
							$check = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('mashreq_mtd_mis')->where('instanceid', $instanceid)->update($file_values);
								
							}
							else
							{
								$all_values = $file_values + $ref_value;
								DB::table('mashreq_mtd_mis')->insert($all_values);
							}
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}

			public function mashreqFinalMTDFileImport(Request $request)
				{					
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('mashreq_mtd_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/MashreqMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							if(count($sheetData[$k])!= 16)
							{
								$fileInfo = DB::table('mashreq_mtd_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}	
							if(trim($sheetData[$k][0])=='')
							{
								continue;
							}
							$ref_value = array('instanceid' => $sheetData[$k][0]);
							//echo date('Y-m-d',strtotime(PHPExcel_Shared_Date::ExcelToPHP($sheetData[$k][4])));
							//echo "<pre>";
							//echo print_r($sheetData);exit;
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							$ref_no = '';
							$team = $sheetData[$k][13];
							$emp_id = '';
							$emp_name = '';
							$applicationidcheck = DB::table('mashreq_login_data')->whereRaw("applicationid ='".$sheetData[$k][0]."'")->get();

							if(count($applicationidcheck)>0)
							{
								$ref_no = $applicationidcheck[0]->ref_no;
							}

							
							if(@$ref_no !='')
							{
								$Internal_info = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->first();

								$customer_mobile = @$Internal_info->customer_mobile;
								if($team=='')
								{
									$team = @$Internal_info->team;
								}
								//$emp_id = @$Internal_info->emp_id;
								$emp_name = @$Internal_info->emp_name;
							}
							///////////////////////////////////////
							///////////////////////////////////////

							$file_values = array('ref_no' => $ref_no,
												'cif_cis_number' => $sheetData[$k][1],
												'customername' => $sheetData[$k][2],
												'plastictype' => $sheetData[$k][3],
												'sellerid' => $sheetData[$k][4],												
												'sellername' => $sheetData[$k][5],
												'dateofdisbursal' => ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00'),
												'cdafinalsalary' => $sheetData[$k][7],
												'card_status' => $sheetData[$k][8],
												'payout' => $sheetData[$k][9],												
												'points' => $sheetData[$k][10],
												'product' => $sheetData[$k][11],
												'agents_name' => $sheetData[$k][12],
												'team_manager' => $sheetData[$k][13],
												'emp_id' => $sheetData[$k][14],												
												'vertical' => $sheetData[$k][15],	
												'team' => $team,												
												'emp_name' => $emp_name,
												'updated_at' => date('Y-m-d H:i:s')
												
												);
							
							$instanceid = $sheetData[$k][0];
							$whereRaw = " instanceid ='".$instanceid."'";
							$check = DB::table('mashreq_final_mtd_mis')->whereRaw($whereRaw)->get(['id'])->count();

							if($check>0)
							{							
								DB::table('mashreq_final_mtd_mis')->where('instanceid', $instanceid)->update($file_values);
								
							}
							else
							{
								$all_values = $file_values + $ref_value;
								DB::table('mashreq_final_mtd_mis')->insert($all_values);
							}
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}



	public function masterMis($fileType=NULL,Request $request)
    {		
		$fileType = $fileType;
		$whereRaw = " id ='1'";
		$LoginFields = DB::table('mashreq_login_data')->whereRaw($whereRaw)->first();
		$BankFields = DB::table('mashreq_bank_mis')->whereRaw($whereRaw)->first();
		$BookingFields = DB::table('mashreq_booking_mis')->whereRaw($whereRaw)->first();
		$MTDFields = DB::table('mashreq_mtd_mis')->whereRaw($whereRaw)->first();
        return view("Common/masterMis",compact('fileType','LoginFields','BankFields','BookingFields','MTDFields'));
    }

	public function masterMisPost(Request $req)
    {	
		
			//$form_id = $req->input('form_id');			
			$postData = $req->input();
			$Login_value = @$postData['Login'];
			$Bank_value = @$postData['Bank'];
			$Booking_value = @$postData['Booking'];
			$MTD_value = @$postData['MTD'];

			$All_fields_request = array_merge($Login_value,$Bank_value,$Booking_value,$MTD_value);

			echo '<pre>';
			print_r($Login_value);
			print_r($Bank_value);
			print_r($Booking_value);
			print_r($MTD_value);

			//print_r($All_fields_request);

			if(count($Login_value)>0)
			{
				$login_fields = "";
				foreach($Login_value as $Login_value_data)
				{
					$login_fields.=$Login_value_data.",";
				}
				
				$login_fields = substr($login_fields,0,-1);				
				$LoginFieldsQuery = MashreqLoginMIS::select(explode(",",$login_fields))->where('application_date', '>=','2023-03-01')->where('application_date','<=', '2023-03-08')->get()->toArray();
				//print_r($LoginFieldsQuery);
			}

			$filename = 'Master_MIS_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			//$sheet->mergeCells('A1:O1');

			$indexCounter = 1;
			//$sheet->setCellValue('A'.$indexCounter, strtoupper('REF_NO'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			//$sheet->setCellValue('B'.$indexCounter, strtoupper('agent_full_name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			
			
			
			$a = 1;
			$indexCounter = 2;
			
			foreach($LoginFieldsQuery as $LoginFieldsQueryValue)
			{	
				$columnNumber = 0;								
				foreach($LoginFieldsQueryValue as $k=>$value)
				{
					if($k=='applicationid')
					{
						$applicationid = $value;
					}

					$cols = $this->getColumnLetter($columnNumber);
					
					if($indexCounter==2)
					{
						$sheet->setCellValue($cols.($indexCounter-1), strtoupper($k))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					
					
					$sheet->setCellValue($cols.$indexCounter, $value)->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');	
					
					$columnNumber++;

				}				
				
				if(count($Bank_value)>0)
				{
					$bank_fields = "";
					foreach($Bank_value as $Bank_value_data)
					{
						$bank_fields.=$Bank_value_data.",";
					}
					
					$bank_fields = substr($bank_fields,0,-1);				
					$BankFieldsQuery = MashreqBankMIS::select(explode(",",$bank_fields))->where('rcms_id',$applicationid)->get()->toArray();
					
					if(count($BankFieldsQuery)>0)
					{
						$BankFieldsQuery = $BankFieldsQuery[0];						
						foreach($BankFieldsQuery as $k=>$value)
						{							
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($k))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, $value)->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
					else
					{
						
						foreach($Bank_value as $k=>$value)
						{											
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($value))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, '')->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
				}

				if(count($Booking_value)>0)
				{
					$booking_fields = "";
					foreach($Booking_value as $Booking_value_data)
					{
						$booking_fields.=$Booking_value_data.",";
					}
					
					$booking_fields = substr($booking_fields,0,-1);				
					$BookingFieldsQuery = MashreqBookingMIS::select(explode(",",$booking_fields))->where('instanceid',$applicationid)->get()->toArray();
					
					if(count($BookingFieldsQuery)>0)
					{
						$BookingFieldsQuery = $BookingFieldsQuery[0];						
						foreach($BookingFieldsQuery as $k=>$value)
						{							
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($k))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, $value)->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
					else
					{						
						foreach($Booking_value as $k=>$value)
						{											
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($value))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, '')->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
				}

				if(count($MTD_value)>0)
				{
					$mtd_fields = "";
					foreach($MTD_value as $MTD_value_data)
					{
						$mtd_fields.=$MTD_value_data.",";
					}
					
					$mtd_fields = substr($mtd_fields,0,-1);				
					$MTDFieldsQuery = MashreqBookingMIS::select(explode(",",$mtd_fields))->where('instanceid',$applicationid)->get()->toArray();
					
					if(count($MTDFieldsQuery)>0)
					{
						$MTDFieldsQuery = $MTDFieldsQuery[0];						
						foreach($MTDFieldsQuery as $k=>$value)
						{							
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($k))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, $value)->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
					else
					{
						
						foreach($MTD_value as $k=>$value)
						{											
							$cols = $this->getColumnLetter($columnNumber);
					
							if($indexCounter==2)
							{
								$sheet->setCellValue($cols.($indexCounter-1), strtoupper($value))->getStyle($cols.($indexCounter-1))->getAlignment()->setHorizontal('center')->setVertical('top');
							}
							$sheet->setCellValue($cols.$indexCounter, '')->getStyle($cols.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
							$columnNumber++;

						}
					}
				}

				
				$indexCounter++;
				
			}

			
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/MashreqMIS/'.$filename));
				echo $filename;
				exit;
			
	}
	
	public static function getENBDJonusFileLog($calendar_date=NULL)
    {
      return $getENBDJonusFileLog = DB::table('enbd_jonus_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getmashreq_missing_link_import_FileLog($calendar_date=NULL)
    {
      return $getENBDJonusFileLog = DB::table('mashreq_missing_link_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getenbd_loan_link_import_FileLog($calendar_date=NULL)
    {
      return $getENBDPLFileLog = DB::table('enbd_loan_link_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getLoginFileLog($calendar_date=NULL)
    {
      return $getLoginFileLog = DB::table('mashreq_login_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getBankFileLog($calendar_date=NULL)
    {
      return $getBankFileLog = DB::table('mashreq_bank_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getBookingFileLog($calendar_date=NULL)
    {
      return $getBookingFileLog = DB::table('mashreq_booking_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getMTDFileLog($calendar_date=NULL)
    {
      return $getMTDFileLog = DB::table('mashreq_mtd_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }

	public static function getAllFileLog(Request $request)
    {
	  $d = $request->d;
	  $m = $request->m;
	  $y = $request->y;

	  $calendar_date = $y.'-'.$m.'-'.$d;

      $getLoginFileLog = DB::table('mashreq_login_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  $getBankFileLog = DB::table('mashreq_bank_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  $getBookingFileLog = DB::table('mashreq_booking_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  $getMTDFileLog = DB::table('mashreq_mtd_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();

	  return view("Common/allFileLog",compact('getLoginFileLog','getBankFileLog','getBookingFileLog','getMTDFileLog'));

    }
	public static function getAllMonthFileLog(Request $request)
    {	  
	  $m = $request->m;
	  $y = $request->y;

	  $calendar_start_date = $y.'-'.$m.'-01';
	  $calendar_end_date = $y.'-'.$m.'-31';

	  $whereRaw = " calendar_date >='".$calendar_start_date."' and calendar_date <='".$calendar_end_date."'";

      $getLoginFileLog = DB::table('mashreq_login_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();
	  $getBankFileLog = DB::table('mashreq_bank_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();
	  $getBookingFileLog = DB::table('mashreq_booking_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();
	  $getMTDFileLog = DB::table('mashreq_mtd_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();

	  return view("Common/allMonthFileLog",compact('getLoginFileLog','getBankFileLog','getBookingFileLog','getMTDFileLog'));

    }
	public static function getUserById($id=NULL)
    {
      return $getUserById = DB::table('users')->where('id', $id)->first();
    }



	public static function getAllFileLogENBDJonus(Request $request)
    {
	  $d = $request->d;
	  $m = $request->m;
	  $y = $request->y;

	  $calendar_date = $y.'-'.$m.'-'.$d;

      $getENBDJonusFileLog = DB::table('enbd_jonus_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  

	  return view("Common/allFileLogENBDJonus",compact('getENBDJonusFileLog'));

    }

	public static function getAllFileLogMashreqMissingBooking(Request $request)
    {
	  $d = $request->d;
	  $m = $request->m;
	  $y = $request->y;

	  $calendar_date = $y.'-'.$m.'-'.$d;

      $getENBDJonusFileLog = DB::table('mashreq_missing_link_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  

	  return view("Common/allFileLogMissingBooking",compact('getENBDJonusFileLog'));

    }

	public static function getAllFileLogENBDPL(Request $request)
    {
	  $d = $request->d;
	  $m = $request->m;
	  $y = $request->y;

	  $calendar_date = $y.'-'.$m.'-'.$d;

      $getENBDPLFileLog = DB::table('enbd_loan_link_import_file')->where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->get();
	  

	  return view("Common/allFileLogENBDPL",compact('getENBDPLFileLog'));

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
				$check = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get(['id'])->count();

				if($check>0)
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

	}

	public function getColumnLetter( $number )
	{
		$prefix = '';
		$suffix = '';
		$prefNum = intval( $number/26 );
		if( $number > 25 ){
			$prefix = $this->getColumnLetter( $prefNum - 1 );
		}
		$suffix = chr( fmod( $number, 26 )+65 );
		return $prefix.$suffix;
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

public function mashreqcurrentMonthCount()
{
	//echo "not execute now";
	//exit;
	//$masterPayoutDetails = MasterPayoutPre::get();
	$start_date = date("Y-m-d");
	$previousdateMissingEmpDateFormat =  date('Y-m-d', strtotime($start_date." -1 month"));
	$previousdateMissingEmpDateFormatMonth = date("m",strtotime($previousdateMissingEmpDateFormat));
	$previousdateMissingEmpDateFormatYear = date("Y",strtotime($previousdateMissingEmpDateFormat));
	$d=date("t",strtotime($previousdateMissingEmpDateFormat));
	$start_date_application_Mashrequ_internal = '01-'.$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
	$end_date_application_Mashrequ_internal = $d.'-'.$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
	
	$whereRawBankCarryForward = "submission_date >='".date('Y-m-d',strtotime($start_date_application_Mashrequ_internal))."'";
	$whereRawBankCarryForward .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_Mashrequ_internal))."'";

	$whereRawBankCarryForwardBooking = "dateofdisbursal >='".date('Y-m-d',strtotime($start_date_application_Mashrequ_internal))."'";
	$whereRawBankCarryForwardBooking .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_application_Mashrequ_internal))."'";
	
	 $collectionModelMissing = DepartmentFormEntry::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRawBankCarryForward)
												 
												  ->where("form_id",1)
												  ->get();
				
				
				foreach($collectionModelMissing as $missing)
				{
					
					$totalBankBooking = MashreqBookingMIS::select("id")->where("emp_id",$missing->emp_id)->whereRaw($whereRawBankCarryForwardBooking)->get()->count();
					
					$objCreate = new MasterPayoutPre();
					$objCreate->agent_product = 'Card';
					$objCreate->agent_id = $missing->emp_id;
					$objCreate->TL = $missing->team;
					$objCreate->agent_name = $this->getEmployeeName($missing->emp_id);
					$objCreate->sales_time = (int)$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
					$objCreate->dept_id = 36;
					$objCreate->bank_name = 'Mashreq';
					$objCreate->tc = $totalBankBooking;
					$objCreate->save();
				}
	
	echo "done";
	exit;
}




public function FileUploadExcelENBDPLLink(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
							
					$response = array();
				  
					$fileType = $request->fileType;
					$fileName = $fileType.'_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
							$tableName = 'enbd_loan_link_import_file';
						
						

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
				}


				public function ENBDPLLinkFileImport(Request $request)
				{
					$user_id = $request->session()->get('EmployeeId');
				
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('enbd_loan_link_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDMIS/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{							
							if(count($sheetData[$k])!= 26)
							{
								$fileInfo = DB::table('enbd_loan_link_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}
							$app_id = $sheetData[$k][2];							
							$emp_id = $sheetData[$k][13];
							$emp_name = $sheetData[$k][14];
							$team = $sheetData[$k][15];
							if(trim($app_id)=='')
							{
								continue;
							}

							if(trim($emp_id)=='')
							{
								continue;
							}

							if(trim($team)=='')
							{
								continue;
							}

							
							$Employee_details_data = DepartmentFormController::getEmployeeDetails($emp_id);	

							$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

							if($emp_name=='')
							{
								continue;
							}

							

							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);

							/////////// Get from Internal /////////
							


							$whereRawParent = " app_id ='".$app_id."'";
							$checkParent = DB::table('enbd_loan_mis')->whereRaw($whereRawParent)->get();

							$submission_date = ($sheetData[$k][1]?date('Y-m-d',strtotime($sheetData[$k][1])):'0000-00-00');
							$date_of_birth = ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00');
							$first_payment_date = ($sheetData[$k][11]?date('Y-m-d',strtotime($sheetData[$k][11])):'0000-00-00');
							$disbursal_date = ($sheetData[$k][23]?date('Y-m-d',strtotime($sheetData[$k][23])):'0000-00-00');
							$approval_date = ($sheetData[$k][24]?date('Y-m-d',strtotime($sheetData[$k][24])):'0000-00-00');

							if(count($checkParent)==0)
							{	
								$file_valuesParent = array(
													'user_id' =>$user_id,									
													'aecb_id' => $sheetData[$k][0],
													'date_of_submission' => $submission_date,
													'app_id' => $app_id,
													'cm_full_name' => $sheetData[$k][3],
													'mobile' => $sheetData[$k][4],
													'gender' => $sheetData[$k][5],
													'date_of_birth' => $date_of_birth,
													'marital_status' => $sheetData[$k][7],
													'nationality' => $sheetData[$k][8],
													'salary' => $sheetData[$k][9],
													'company_name' => $sheetData[$k][10],
													'first_payment_date' => $first_payment_date,
													'account_no' => $sheetData[$k][12],
													'emp_id' => $emp_id,
													'se_name' => $emp_name,
													'team' => $team,
													'scheme_name' => $sheetData[$k][16],
													'scheme_name_val' => $sheetData[$k][17],
													'loan_amount' => $sheetData[$k][18],
													'roi' => $sheetData[$k][19],
													'tenure' => $sheetData[$k][20],
													'aecb_score' => $sheetData[$k][21],
													'status' => $sheetData[$k][22],
													'disbursal_date' => $disbursal_date,
													'approval_date' => $approval_date,
													'comment' => $sheetData[$k][25],											
													'import_status' => 1);
									
								$parent_id = DB::table('enbd_loan_mis')->insertGetId($file_valuesParent);
								
								
							}
							else
							{			

									ENBDLoanMIS::where('app_id', $app_id)
										->update(['user_id' =>$user_id,									
													'aecb_id' => $sheetData[$k][0],
													'date_of_submission' => $submission_date,
													'cm_full_name' => $sheetData[$k][3],
													'mobile' => $sheetData[$k][4],
													'gender' => $sheetData[$k][5],
													'date_of_birth' => $date_of_birth,
													'marital_status' => $sheetData[$k][7],
													'nationality' => $sheetData[$k][8],
													'salary' => $sheetData[$k][9],
													'company_name' => $sheetData[$k][10],
													'first_payment_date' => $first_payment_date,
													'account_no' => $sheetData[$k][12],
													'emp_id' => $emp_id,
													'se_name' => $emp_name,
													'team' => $team,
													'scheme_name' => $sheetData[$k][16],
													'scheme_name_val' => $sheetData[$k][17],
													'loan_amount' => $sheetData[$k][18],
													'roi' => $sheetData[$k][19],
													'tenure' => $sheetData[$k][20],
													'aecb_score' => $sheetData[$k][21],
													'status' => $sheetData[$k][22],
													'disbursal_date' => $disbursal_date,
													'approval_date' => $approval_date,
													'comment' => $sheetData[$k][25],											
													'import_status' => 1
										
									]);
									
								
							}
							///////////////////////////////////
							


							
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}




	

    
}
