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
use App\Models\Employee\Employee_attribute;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;

use App\Models\Bank\SCB\SCBDepartmentFormChildEntry;

use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Bank\SCB\SCBImportFile;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Recruiter\Designation;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\ExportDataLog;
use Session;

class SCBBankController extends Controller
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
 public function importBankSCB()
 {
	
	return view("Banks/SCB/BankMIS/importBankSCB");
 }
 
 public function loginCalRenderTabSCB(Request $request)
 {
	 $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Banks/SCB/BankMIS/loginCalRenderTabSCB",compact('monthSelected','yearSelected'));
	 
 }
 
public static function getSCBFileLog($calendar_date=NULL)
		{
		  return $getCBDFileLog = SCBImportFile::where('calendar_date', $calendar_date)->where("type",1)->orderBy('updated_at','DESC')->first();
		}
public function FileUploadExcelSCB(Request $request)
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
					$fileName = 'SCB_Bank_MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/SCBMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/SCBMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'SCB_import_file';

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate,'type'=>1);
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
	
	
	public function SCBFileImport(Request $request)
				{	
			
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('SCB_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/SCBMIS/';
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
							
							if(count($sheetData[$k])!= 11)
							{
								$fileInfo = DB::table('SCB_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							
							
							
							
							$file_values = array(
												'company_name' => trim($sheetData[$k][0]),
												'aloc_noaloc' => trim($sheetData[$k][1]),
												
												'PW_ID' => trim($sheetData[$k][3]),												
												'NBO' => trim($sheetData[$k][4]),
												'TL' => trim($sheetData[$k][5]),
												'Team' => trim($sheetData[$k][6]),
												'Card_Type' => trim($sheetData[$k][7]),
												'Current_Queue_Date' => ($sheetData[$k][8]?date('Y-m-d',strtotime($sheetData[$k][8])):'0000-00-00'),
												'Ageing' => trim($sheetData[$k][9]),
												'Status' => trim($sheetData[$k][10]),
												'update_emp_status' => 1,
												
												
												);
								
/* echo "<pre>";
print_r($file_values);
exit; */								
							
											
							/* 	echo "<pre>";
							print_r($file_values);
							
							exit;		 */		
							 $ref_no = trim($sheetData[$k][2]);
							$whereRaw = " Agency_Reference ='".$ref_no."'";
							$check = DB::table('SCB_bank_mis')->whereRaw($whereRaw)->get();

							if(count($check)>0)
							{			
						
								DB::table('SCB_bank_mis')->where('Agency_Reference', $ref_no)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['Agency_Reference'] = $ref_no;
								
							
								
								DB::table('SCB_bank_mis')->insert($all_values);
							}
							/*
							*change status in main mis
							*/
							$scbDeptMod = SCBDepartmentFormParentEntry::where("ref_no",$ref_no)->first();
							if($scbDeptMod != '')
							{
								$updateSCB = SCBDepartmentFormParentEntry::find($scbDeptMod->id);
								$updateSCB->missing_internal = 1;
								$updateSCB->save();
							}
							/*
							*change status in main mis
							*/
							
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
			
	public function loadBankContentsSCBCardBankSide(Request $request)
	{
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " Agency_Reference!=''";	
		if(@$request->session()->get('master_scb_search_bank') != '' && @$request->session()->get('master_scb_search_bank') == 2)
		{
				  if(@$request->session()->get('ref_no_SCB_master') != '')
					{
						$refNO = $request->session()->get('ref_no_SCB_master');
						
					
						$whereRaw .= " AND Agency_Reference like '%".$refNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_SCB_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_SCB_master');
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
					
						$whereRaw .= " AND NBO IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_SCB_master') != '')
					{
						$SMMod = $request->session()->get('team_SCB_master');
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
					
						$whereRaw .= " AND TL IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_SCB_master') != '')
					{
						$start_date_creation_SCB_bank = $request->session()->get('start_date_application_SCB_master');			
						$whereRaw .= " AND Current_Queue_Date >='".date('Y-m-d',strtotime($start_date_creation_SCB_bank))."'";
						$searchValues['start_date_application_SCB_master'] = $start_date_creation_SCB_bank;			
					}

					if($request->session()->get('end_date_application_SCB_master') != '')
					{
						$end_date_creation_SCB_bank = $request->session()->get('end_date_application_SCB_master');			
						$whereRaw .= " AND Current_Queue_Date <='".date('Y-m-d',strtotime($end_date_creation_SCB_bank))."'";
						$searchValues['end_date_application_SCB_master'] = $end_date_creation_SCB_bank;			
					}
		}
		else
		{
					if(@$request->session()->get('ref_no_SCB_bank') != '')
					{
						$refNO = $request->session()->get('ref_no_SCB_bank');
						
					
						$whereRaw .= " AND Agency_Reference like '%".$refNO."%'";	
						
						
					}
					if(@$request->session()->get('status_SCB_bank') != '')
					{
						$status = $request->session()->get('status_SCB_bank');
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
					
						$whereRaw .= " AND Status IN (".$strStatus.")";	
						
						
					}
					
					
					
					if(@$request->session()->get('employee_id_SCB_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_SCB_bank');
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
					
						$whereRaw .= " AND NBO IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_SCB_bank') != '')
					{
						$SMMod = $request->session()->get('smManager_SCB_bank');
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
					
						$whereRaw .= " AND TL IN (".$smStr.")";	
						
						
					}
					
					if(@$request->session()->get('pwid_SCB_bank') != '')
					{
						$pwidMod = $request->session()->get('pwid_SCB_bank');
						$pwidStr = '';
						foreach($pwidMod  as $PW)
						{
							if($pwidStr == '')
							{
								$pwidStr = "'".$PW."'";
							}
							else
							{
								$pwidStr = $pwidStr.",'".$PW."'";
							}
						}
					
						$whereRaw .= " AND PW_ID IN (".$pwidStr.")";	
						
						
					}
					if($request->session()->get('start_date_creation_SCB_bank') != '')
					{
						$start_date_creation_SCB_bank = $request->session()->get('start_date_creation_SCB_bank');			
						$whereRaw .= " AND Current_Queue_Date >='".date('Y-m-d',strtotime($start_date_creation_SCB_bank))."'";
						$searchValues['start_date_creation_SCB_bank'] = $start_date_creation_SCB_bank;			
					}

					if($request->session()->get('end_date_creation_SCB_bank') != '')
					{
						$end_date_creation_SCB_bank = $request->session()->get('end_date_creation_SCB_bank');			
						$whereRaw .= " AND Current_Queue_Date <='".date('Y-m-d',strtotime($end_date_creation_SCB_bank))."'";
						$searchValues['end_date_creation_SCB_bank'] = $end_date_creation_SCB_bank;			
					}
					if($request->session()->get('submission_type_SCB_Bank') != '')
					{
						
						$submission_type = $request->session()->get('submission_type_SCB_Bank');
						if($submission_type == 'Linked')
						{
							$whereRaw .= " AND update_emp_status =2";
						}
						else if($submission_type == 'Missing')
						{
							$whereRaw .= " AND update_emp_status =1";
						}
						else
						{
							
						}
						
					}
					
					

		}
		//echo $whereRaw;exit;



		$endDate = date("Y-m-d");
		$startDate = date("Y").'-'.date("m").'-'.'01';


		





		$datasCBDMainCount = SCBBankMis::whereRaw($whereRaw)->whereBetween('Current_Queue_Date', [$startDate, $endDate])->get()->count();
		
		$datasCBDMain = SCBBankMis::whereRaw($whereRaw)->whereBetween('Current_Queue_Date', [$startDate, $endDate])->orderBy("Current_Queue_Date","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			$appStatusMod = SCBBankMis::select('Status')->get()->unique('Status');
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			$appAECB_StatusMod = array();
			
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			$employeeIdList = SCBBankMis::select('NBO')->get()->unique('NBO');
			
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			$pwidList = SCBBankMis::select('PW_ID')->get()->unique('PW_ID');
			
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			$smManageData = SCBBankMis::select('TL')->get()->unique('TL');
			
		/*
		*Sm Manager
		*/
		 return view("Banks/SCB/loadBankContentsSCBCardBankSide",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData','pwidList'));
	}

public function updateAgencyRef()
{
	$datas = SCBDepartmentFormParentEntry::where("missing_internal",1)->get();
	
	foreach($datas as $data)
	{
		$ref = $data->ref_no;
		$scbbankCheck = SCBBankMis::where("Agency_Reference",trim($ref))->first();
		if($scbbankCheck != '')
		{
			/*
			*update bank mis
			*start code
			*/
			
			$scbModBank = SCBBankMis::find($scbbankCheck->id);
			$scbModBank->NBO = $data->emp_id;
			$scbModBank->TL = $data->team;
			$scbModBank->customer_name = $data->customer_name;
			$scbModBank->customer_mobile = $data->customer_mobile;
			$scbModBank->update_emp_status = 2;
			$scbModBank->save();
			
			/*
			*update bank mis
			*end code
			*/
			
			
			/*
			*update internal mis
			*start code
			*/
			
			$scbModinternal = SCBDepartmentFormParentEntry::find($data->id);
			$scbModinternal->form_status = $scbbankCheck->Status;
			$scbModinternal->pw_id_scb = $scbbankCheck->PW_ID;
			$scbModinternal->aloc_non_aloc_scb = $scbbankCheck->aloc_noaloc;
			$scbModinternal->Ageing_scb = $scbbankCheck->Ageing;
			$scbModinternal->Card_Type_scb = $scbbankCheck->Card_Type;
			$scbModinternal->team_company = $scbbankCheck->Team;
			$scbModinternal->company_name_scb = $scbbankCheck->company_name;
			$scbModinternal->approved_date = $scbbankCheck->Current_Queue_Date;
			$scbModinternal->missing_internal = 2;
			$scbModinternal->save();
			
			/*
			*update internal mis
			*end code
			*/
			
			/*
			*update Approval in child
			*start code
			*/
				$checkExist = SCBDepartmentFormChildEntry::where("parent_id",$data->id)->where("attribute_code","approval_date_scb")->first();
				if($checkExist != '')
				{
					$deleteObj = SCBDepartmentFormChildEntry::find($checkExist->id);
					$deleteObj->delete();
				}
				$addObjchild = new SCBDepartmentFormChildEntry();
				$addObjchild->parent_id = $data->id;
				$addObjchild->form_id = 3;
				$addObjchild->attribute_code = 'approval_date_scb';
				$addObjchild->attribute_value = $scbbankCheck->Current_Queue_Date;
				$addObjchild->save();
			/*
			*update Approval in child
			*end code
			*/
		}
		else
		{
			
		}
	}
	echo "done";
	exit;
}

public function searchSCBBankInner(Request $request)
{
				$requestParameters = $request->input();
				/* echo "<pre>";
				print_r($requestParameters);
				exit; */
				$start_date_creation = '';
				$end_date_creation = '';
				$pwid = '';
				$status = '';
				$ref_no = '';
				$employee_id = '';
				$sm_manager = '';
				$submission_type = '';

				if(@isset($requestParameters['ref_no']))
				{
					$ref_no = @$requestParameters['ref_no'];
				}

				

				if(isset($requestParameters['status']))
				{
					$status = @$requestParameters['status'];
				}
				
				if(isset($requestParameters['employee_id']))
				{
					$employee_id = @$requestParameters['employee_id'];
				}
				if(isset($requestParameters['sm_manager']))
				{
					$sm_manager = @$requestParameters['sm_manager'];
				}
				if(isset($requestParameters['pw_id']))
				{
					$pwid = @$requestParameters['pw_id'];
				}

				if(isset($requestParameters['start_date_creation']))
				{
					$start_date_creation = @$requestParameters['start_date_creation'];
				}
				if(isset($requestParameters['end_date_creation']))
				{
					$end_date_creation = @$requestParameters['end_date_creation'];
				}
				
				if(isset($requestParameters['submission_type']))
				{
					$submission_type = @$requestParameters['submission_type'];
				}
				
				$request->session()->put('ref_no_SCB_bank',$ref_no);
				
				$request->session()->put('status_SCB_bank',$status);
				$request->session()->put('employee_id_SCB_bank',$employee_id);
				$request->session()->put('smManager_SCB_bank',$sm_manager);
				$request->session()->put('pwid_SCB_bank',$pwid);
				$request->session()->put('start_date_creation_SCB_bank',$start_date_creation);
				$request->session()->put('end_date_creation_SCB_bank',$end_date_creation);
				$request->session()->put('master_scb_search_bank','');
				$request->session()->put('submission_type_SCB_Bank',$submission_type);
				return redirect("loadBankContentsSCBCardBankSide");
}
			
			
public function resetLoginInnerSCB(Request $request)
{
				$request->session()->put('ref_no_SCB_bank','');
			
				$request->session()->put('status_SCB_bank','');
				$request->session()->put('start_date_creation_SCB_bank','');
				$request->session()->put('end_date_creation_SCB_bank','');
				$request->session()->put('employee_id_SCB_bank','');
				$request->session()->put('smManager_SCB_bank','');
				$request->session()->put('pwid_SCB_bank','');
				$request->session()->put('submission_type_SCB_Bank','');
				$request->session()->put('master_scb_search_bank',2);
				return redirect("loadBankContentsSCBCardBankSide");
}
	
	
public function exportDocReportBankMisSCBCards(Request $request)
	{	
			$requestPost = $request->input();
			$parameters = $request->input(); 
/* 			echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Bank_MIS_SCB_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'Bank MIS SCB Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Id'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Employee Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Current Queue Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Agency Reference No'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Card Type'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('PW ID'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Company Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('ALOC / NON ALOC'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Ageing'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Team'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Customer Name'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Customer Mobile'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  SCBBankMis::where("id",$sid)->first();
				
				
				 $indexCounter++; 

				
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->TL)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->NBO)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $this->getEmployeeName($mis->NBO))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, ($mis->Current_Queue_Date?date('d-m-Y',strtotime($mis->Current_Queue_Date)):'00-00-0000'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->Agency_Reference)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->Card_Type)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->PW_ID)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->company_name)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->aloc_noaloc)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->Ageing)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->Status)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->Team)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->customer_name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->customer_mobile)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'O'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','O') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="SCB-Bank";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}
	
public function action_handler_export_internal_cards_SCB_Agents_Performance(Request $request)
{
		$start_date_application_SCB_internal = '';
		$end_date_application_SCB_internal = '';
		$whereRaw = 'form_id = 3';
		$whereRawBank = "ref_no != ''";
	
		if($request->session()->get('start_date_application_SCB_internal') != '')
				{
					$start_date_application_SCB_internal = $request->session()->get('start_date_application_SCB_internal');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					$whereRawBank .= " AND approved_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					
				}
	    else
				{
					$start_date_application_SCB_internal = date("Y")."-".date("m")."-01";			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					$whereRawBank .= " AND approved_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
					
				}
		if($request->session()->get('end_date_application_SCB_internal') != '')
				{
					$end_date_application_SCB_internal = $request->session()->get('end_date_application_SCB_internal');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					$whereRawBank .= " AND approved_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					
				}	
		else
				{
					$end_date_application_SCB_internal = date("Y-m-d");	
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					$whereRawBank .= " AND approved_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
					
				}
		/* echo $start_date_application_SCB_internal;
		echo "<pre>";
		echo $end_date_application_SCB_internal;
		exit; */
			/*
			*-1,-2 month Name
			*start code
			*/
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			$collectionModel = SCBDepartmentFormParentEntry::selectRaw('count(*) as total, emp_id,team,vintage,range_id,doj,agent_code')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRaw)
												  ->get();
		
		    $filename = 'Agent_performance_SCB_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R2');
			$sheet->setCellValue('Q1', 'Agents Performance SCB Cards - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).'to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
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
					$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("emp_id",$model->emp_id)->whereIn("form_status",array("Approved"))->whereRaw($whereRawBank)->get()->count();
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
				$empwithZeroSubmission = Employee_details::where("dept_id",47)
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
					$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("emp_id",$zeroSubmission->emp_id)->whereIn("form_status",array("Approved"))->whereBetween("approved_date",[$startDate,$endDate])->get()->count();
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
				
				
				 $collectionModelMissing = SCBDepartmentFormParentEntry::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereDate('application_date', '>=', $startDateMissing)
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
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="SCB-Final-Report";					
				$logObj->save();
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
}
protected function sheet3FlagDetails($spreadsheet,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
			$sheet->setCellValue('A1','Flag Details')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Employee ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$selectedId = DB::table('master_payout')->whereRaw("dept_id = '47'")->limit(3)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order']);
			
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
		
			
			$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '47' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);
			
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);
			
			foreach ($selectedEmp as $selectedEmpData) 
			{
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				

				$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$FirstData = DB::table('master_payout')->whereRaw("dept_id = '47' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);	
				

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

				$SecondData = DB::table('master_payout')->whereRaw("dept_id = '47' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

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

				$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '47' AND sort_order ='".$exp_sort_orders[2]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

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

protected function sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		
		
			/*
			*-1,-2 month Name
			*start code
			*/
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			$collectionModel = SCBDepartmentFormParentEntry::selectRaw('count(*) as total,team')
												  ->groupBy('team')
												  ->whereRaw($whereRaw)
												  ->get();
				$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:H2');
			$sheet->setCellValue('A1', 'TL Performance SCB Cards - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).'to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
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
				foreach ($collectionModel as $model) {
				if($model->team != '')
				{
				
					
					$teamValue[] = $model->team;
					$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("team",$model->team)->whereIn("form_status",array("Approved"))->whereRaw($whereRawBank)->get()->count();
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
		$collectionModelP = SCBDepartmentFormParentEntry::selectRaw('team')
												  ->groupBy('team')
												  ->whereDate('application_date','>=',$startDatePP)
												  ->whereNotIn("team",$teamValue)
												  ->get();
												  
		foreach ($collectionModelP as $model) {
				if($model->team != '')
				{
				
					
					
					$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("team",$model->team)->whereIn("form_status",array("Approved"))->whereRaw($whereRawBank)->get()->count();
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
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",47)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
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
		$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("emp_id",$empId)->whereIn("form_status",array("Approved"))->whereBetween("approved_date",[$startDate,$endDate])->get()->count();
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
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",47)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
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
		$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("emp_id",$empId)->whereIn("form_status",array("Approved"))->whereBetween("approved_date",[$startDate,$endDate])->get()->count();
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
		return SCBDepartmentFormParentEntry::select("id")->whereDate("application_date","=",$previousDate)->where("emp_id",$empId)->get()->count();
		
	}
	protected function t2Submissions($empId)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return SCBDepartmentFormParentEntry::select("id")->whereBetween("application_date",[$StartDate,$endDate])->where("emp_id",$empId)->get()->count();
		
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
	
	protected function getTLName($empId)
	{
		$empDetailsModel = Employee_details::select("tl_id")->where("emp_id",$empId)->first();
			if($empDetailsModel != '')
			{
				$tlID = $empDetailsModel->tl_id;
				if($tlID != '' && $tlID != NULL)
				{
					return Employee_details::select("export_name")->where("id",$tlID)->first()->export_name;
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
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",47)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
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
		
		$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("team",$team)->whereIn("form_status",array("Approved"))->whereBetween("approved_date",[$startDate,$endDate])->get()->count();
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
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",47)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
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
		
		$totalBankBooking = SCBDepartmentFormParentEntry::select("id")->where("team",$team)->whereIn("form_status",array("Approved"))->whereBetween("approved_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}
	
	protected function t1SubmissionsTeam($team)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return SCBDepartmentFormParentEntry::select("id")->whereDate("application_date","=",$previousDate)->where("team",$team)->get()->count();
		
	}
	protected function t2SubmissionsTeam($team)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return SCBDepartmentFormParentEntry::select("id")->whereBetween("application_date",[$StartDate,$endDate])->where("team",$team)->get()->count();
		
	}
	
	public function updateApprovalDate()
	{
		$dataUpdates = SCBDepartmentFormParentEntry::select("id","approved_date")->get();
		
		foreach($dataUpdates as $data)
		{
			$updateChild = new SCBDepartmentFormChildEntry();
			$updateChild->parent_id = $data->id;
			$updateChild->form_id = 3;
			$updateChild->attribute_code = "approval_date_scb";
			$updateChild->attribute_value = date("Y-m-d",strtotime($data->approved_date));
			$updateChild->status = 1;
			$updateChild->save();
		}
		echo "yes";
		exit;
	
	}






















	public function loadContentSCBCardBankSideCurrentMonthData(Request $request)
	{
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " Agency_Reference!=''";	
		if(@$request->session()->get('master_scb_search_bank') != '' && @$request->session()->get('master_scb_search_bank') == 2)
		{
				  if(@$request->session()->get('ref_no_SCB_master') != '')
					{
						$refNO = $request->session()->get('ref_no_SCB_master');
						
					
						$whereRaw .= " AND Agency_Reference like '%".$refNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_SCB_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_SCB_master');
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
					
						$whereRaw .= " AND NBO IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_SCB_master') != '')
					{
						$SMMod = $request->session()->get('team_SCB_master');
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
					
						$whereRaw .= " AND TL IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_SCB_master') != '')
					{
						$start_date_creation_SCB_bank = $request->session()->get('start_date_application_SCB_master');			
						$whereRaw .= " AND Current_Queue_Date >='".date('Y-m-d',strtotime($start_date_creation_SCB_bank))."'";
						$searchValues['start_date_application_SCB_master'] = $start_date_creation_SCB_bank;			
					}

					if($request->session()->get('end_date_application_SCB_master') != '')
					{
						$end_date_creation_SCB_bank = $request->session()->get('end_date_application_SCB_master');			
						$whereRaw .= " AND Current_Queue_Date <='".date('Y-m-d',strtotime($end_date_creation_SCB_bank))."'";
						$searchValues['end_date_application_SCB_master'] = $end_date_creation_SCB_bank;			
					}
		}
		else
		{
					if(@$request->session()->get('ref_no_SCB_bank_CurrentMonth') != '')
					{
						$refNO = $request->session()->get('ref_no_SCB_bank_CurrentMonth');
						
					
						$whereRaw .= " AND Agency_Reference like '%".$refNO."%'";	
						
						
					}
					if(@$request->session()->get('status_SCB_bank_CurrentMonth') != '')
					{
						$status = $request->session()->get('status_SCB_bank_CurrentMonth');
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
					
						$whereRaw .= " AND Status IN (".$strStatus.")";	
						
						
					}
					
					
					
					if(@$request->session()->get('employee_id_SCB_bank_CurrentMonth') != '')
					{
						$employeeMod = $request->session()->get('employee_id_SCB_bank_CurrentMonth');
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
					
						$whereRaw .= " AND NBO IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_SCB_bank_CurrentMonth') != '')
					{
						$SMMod = $request->session()->get('smManager_SCB_bank_CurrentMonth');
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
					
						$whereRaw .= " AND TL IN (".$smStr.")";	
						
						
					}
					
					if(@$request->session()->get('pwid_SCB_bank_CurrentMonth') != '')
					{
						$pwidMod = $request->session()->get('pwid_SCB_bank_CurrentMonth');
						$pwidStr = '';
						foreach($pwidMod  as $PW)
						{
							if($pwidStr == '')
							{
								$pwidStr = "'".$PW."'";
							}
							else
							{
								$pwidStr = $pwidStr.",'".$PW."'";
							}
						}
					
						$whereRaw .= " AND PW_ID IN (".$pwidStr.")";	
						
						
					}
					if($request->session()->get('start_date_creation_SCB_bank_CurrentMonth') != '')
					{
						$start_date_creation_SCB_bank_CurrentMonth = $request->session()->get('start_date_creation_SCB_bank_CurrentMonth');			
						$whereRaw .= " AND Current_Queue_Date >='".date('Y-m-d',strtotime($start_date_creation_SCB_bank_CurrentMonth))."'";
						$searchValues['start_date_creation_SCB_bank_CurrentMonth'] = $start_date_creation_SCB_bank_CurrentMonth;			
					}

					if($request->session()->get('end_date_creation_SCB_bank_CurrentMonth') != '')
					{
						$end_date_creation_SCB_bank_CurrentMonth = $request->session()->get('end_date_creation_SCB_bank_CurrentMonth');			
						$whereRaw .= " AND Current_Queue_Date <='".date('Y-m-d',strtotime($end_date_creation_SCB_bank_CurrentMonth))."'";
						$searchValues['end_date_creation_SCB_bank_CurrentMonth'] = $end_date_creation_SCB_bank_CurrentMonth;			
					}
					if($request->session()->get('submission_type_SCB_Bank_CurrentMonth') != '')
					{
						
						$submission_type = $request->session()->get('submission_type_SCB_Bank_CurrentMonth');
						if($submission_type == 'Linked')
						{
							$whereRaw .= " AND update_emp_status =2";
						}
						else if($submission_type == 'Missing')
						{
							$whereRaw .= " AND update_emp_status =1";
						}
						else
						{
							
						}
						
					}
					
					

		}
		//echo $whereRaw;exit;



		//$endDate = date("Y-m-d");
		//$startDate = date("Y").'-'.date("m").'-'.'01';


		$datasCBDMainCount = SCBBankMis::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = SCBBankMis::whereRaw($whereRaw)->orderBy("Current_Queue_Date","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			$appStatusMod = SCBBankMis::select('Status')->get()->unique('Status');
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			$appAECB_StatusMod = array();
			
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			$employeeIdList = SCBBankMis::select('NBO')->get()->unique('NBO');
			
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			$pwidList = SCBBankMis::select('PW_ID')->get()->unique('PW_ID');
			
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			$smManageData = SCBBankMis::select('TL')->get()->unique('TL');
			
		/*
		*Sm Manager
		*/
		 return view("Banks/SCB/loadContentSCBCardBankSideHistoric",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData','pwidList'));
	}





	public function searchSCBBankSideInnerFilterCurrentMonth(Request $request)
	{
		$requestParameters = $request->input();
		/* echo "<pre>";
		print_r($requestParameters);
		exit; */
		$start_date_creation = '';
		$end_date_creation = '';
		$pwid = '';
		$status = '';
		$ref_no = '';
		$employee_id = '';
		$sm_manager = '';
		$submission_type = '';

		if(@isset($requestParameters['ref_no_bank_CurrentMonth']))
		{
			$ref_no = @$requestParameters['ref_no_bank_CurrentMonth'];
		}

		

		if(isset($requestParameters['status_bank_CurrentMonth']))
		{
			$status = @$requestParameters['status_bank_CurrentMonth'];
		}
		
		if(isset($requestParameters['employee_id_bank_CurrentMonth']))
		{
			$employee_id = @$requestParameters['employee_id_bank_CurrentMonth'];
		}
		if(isset($requestParameters['sm_manager_bank_CurrentMonth']))
		{
			$sm_manager = @$requestParameters['sm_manager_bank_CurrentMonth'];
		}
		if(isset($requestParameters['pw_id_bank_CurrentMonth']))
		{
			$pwid = @$requestParameters['pw_id_bank_CurrentMonth'];
		}

		if(isset($requestParameters['start_date_creation_bank_CurrentMonth']))
		{
			$start_date_creation = @$requestParameters['start_date_creation_bank_CurrentMonth'];
		}
		if(isset($requestParameters['end_date_creation_bank_CurrentMonth']))
		{
			$end_date_creation = @$requestParameters['end_date_creation_bank_CurrentMonth'];
		}
		
		if(isset($requestParameters['submission_type_bank_CurrentMonth']))
		{
			$submission_type = @$requestParameters['submission_type_bank_CurrentMonth'];
		}
		
		$request->session()->put('ref_no_SCB_bank_CurrentMonth',$ref_no);
		
		$request->session()->put('status_SCB_bank_CurrentMonth',$status);
		$request->session()->put('employee_id_SCB_bank_CurrentMonth',$employee_id);
		$request->session()->put('smManager_SCB_bank_CurrentMonth',$sm_manager);
		$request->session()->put('pwid_SCB_bank_CurrentMonth',$pwid);
		$request->session()->put('start_date_creation_SCB_bank_CurrentMonth',$start_date_creation);
		$request->session()->put('end_date_creation_SCB_bank_CurrentMonth',$end_date_creation);
		$request->session()->put('master_scb_search_bank','');
		$request->session()->put('submission_type_SCB_Bank_CurrentMonth',$submission_type);
		return redirect("loadContentSCBCardBankSideCurrentMonth");
	}
			
			
	public function resetSCBBankSideInnerFilterCurrentMonth(Request $request)
	{
		$request->session()->put('ref_no_SCB_bank_CurrentMonth','');
	
		$request->session()->put('status_SCB_bank_CurrentMonth','');
		$request->session()->put('start_date_creation_SCB_bank_CurrentMonth','');
		$request->session()->put('end_date_creation_SCB_bank_CurrentMonth','');
		$request->session()->put('employee_id_SCB_bank_CurrentMonth','');
		$request->session()->put('smManager_SCB_bank_CurrentMonth','');
		$request->session()->put('pwid_SCB_bank_CurrentMonth','');
		$request->session()->put('submission_type_SCB_Bank_CurrentMonth','');
		$request->session()->put('master_scb_search_bank',2);
		return redirect("loadContentSCBCardBankSideCurrentMonth");
	}




}
