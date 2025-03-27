<?php

namespace App\Http\Controllers\Banks\ENBD\CARDS;

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
use App\Models\Bank\ENBD\BankJonusEnbdCardsPhysical;
use App\Models\Bank\ENBD\ENBDCardsPhysicalImportFile;
use App\Models\Bank\ENBD\JonusEnbdCardsPhysicalReportLog;


use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;

use Session;

class ENBDJonusMISCardsPhysicalController extends Controller
{
   
 public function importJonusENBDCardsPhysical()
 {
	
	return view("Banks/ENBD/JonusMISCardsPhysical/importJonusENBDCardsPhysical");
 }
 
			
	public function enbdCardsPhysicalCalRenderTab(Request $request)
			{
				$monthSelected = $request->m;
			    $yearSelected = $request->y;
			    return view("Banks/ENBD/JonusMISCardsPhysical/enbdCardsPhysicalCalRenderTab",compact('monthSelected','yearSelected'));
			}	
			
	
 public static function getENBDPhysicalCardsFileLog($calendar_date=NULL)
    {
      return $getCBDFileLog = ENBDCardsPhysicalImportFile::where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }	


 public static function getjonusCardsLogsStatus($date,$type)
		   {
			   $array1 = array();
			   $uploadDate = date("Y-m-d",strtotime($date));
			   $reports = JonusEnbdCardsPhysicalReportLog::whereDate("uploaded_date",$uploadDate)->where("type",$type)->first();
			   $array1['status'] = 'NO';
			   if($reports != '')
			   {
				   $adminId = $reports->created_by;
				   $admin =Employee::where("id",$adminId)->first();
			
				   $array1['status'] = 'YES'; 
				   $array1['time_values'] = $reports->time_values; 
				   $array1['created_by'] = $admin->fullname; 
				   $array1['file_name'] = $reports->file_name; 
			   }
			   return $array1;
		   }	
		   
public function FileUploadExcelENBDCardsPhysical(Request $request)
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
					$fileName = 'ENBD_Jonus_Cards_Manual_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDCARDSMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDCARDSMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'ENBD_Cards_Physical_import_file';

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
				
				
				
				public function ENBDCardsPhysicalFileImport(Request $request)
				{					
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('ENBD_Cards_Physical_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDCARDSMIS/';
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
							
							if(count($sheetData[$k])!= 44)
							{
								$fileInfo = DB::table('ENBD_Cards_Physical_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);
							
							$file_values = array(
												'CARDID' => trim($sheetData[$k][0]),
												
												'NAME' => trim($sheetData[$k][2]),
												'FILERECEIPTDTTIME' => ($sheetData[$k][3]?date('Y-m-d',strtotime($sheetData[$k][3])):'0000-00-00'),												
												'OFFER' => trim($sheetData[$k][4]),
												'CURRENTACTIVITY' => trim($sheetData[$k][5]),
												'STATUS' => trim($sheetData[$k][6]),
												'APPLICATIONTYPE' => trim($sheetData[$k][7]),
												'DATEOFSOURCING' => ($sheetData[$k][8]?date('Y-m-d',strtotime($sheetData[$k][8])):'0000-00-00'),												
												'SIGNEDDATE' => ($sheetData[$k][9]?date('Y-m-d',strtotime($sheetData[$k][9])):'0000-00-00'),
												'PRODUCT' => trim($sheetData[$k][10]),
												'SCHEMEGROUP' => trim($sheetData[$k][11]),
												'SCHEME' => trim($sheetData[$k][12]),
												'CHANNELCODE' => ($sheetData[$k][13]?trim(str_replace(",","",$sheetData[$k][13])):'0'),
												'DSA_BRANCH' => trim($sheetData[$k][14]),	
												
												'DME_RBE' => trim($sheetData[$k][15]),	
												'APP_REJ_CANDATE_TIME' => trim($sheetData[$k][16]),	
												'LASTREMARKSADDED' => trim($sheetData[$k][17]),	
												'CHANNELCODEPERV' => trim($sheetData[$k][18]),	
												'EVSTATUS' => trim($sheetData[$k][19]),	
												'EVACTIONDATE' => ($sheetData[$k][20]?date('Y-m-d',strtotime($sheetData[$k][20])):'0000-00-00'),
												'EVUSER' => trim($sheetData[$k][21]),	
												'CVSTATUS' => trim($sheetData[$k][22]),	
												'CVACTIONDATE' => ($sheetData[$k][23]?date('Y-m-d',strtotime($sheetData[$k][23])):'0000-00-00'),
												'WCSTATUS' => trim($sheetData[$k][24]),	
												'WCACTIONDATE' => ($sheetData[$k][25]?date('Y-m-d',strtotime($sheetData[$k][25])):'0000-00-00'),
												'WCREMARKS' => trim($sheetData[$k][26]),	
												'APPLICATIONCREDITSTATUS' => trim($sheetData[$k][27]),	
												'CARDAPPROVALSTATUS' => trim($sheetData[$k][28]),	
												'LASTUPDATED' => ($sheetData[$k][29]?date('Y-m-d',strtotime($sheetData[$k][29])):'0000-00-00'),	
												'PRI_SUPP_STANDALONE' => trim($sheetData[$k][30]),	
												'PRIMARYCARD_STAND_ALONE' => trim($sheetData[$k][31]),	
												'PRIMARY_ACC_NO_STANDALONE' => trim($sheetData[$k][32]),	
												'CARDTYPE' => trim($sheetData[$k][33]),	
												'BILLINGCYCLE' => trim($sheetData[$k][34]),	
												'REQUESTEDLIMIT' => trim($sheetData[$k][35]),	
												'APPROVEDLIMIT' => trim($sheetData[$k][36]),	
												'SOURCED_ON' => ($sheetData[$k][37]?date('Y-m-d',strtotime($sheetData[$k][37])):'0000-00-00'),	
												'REPORTGENDATE' => ($sheetData[$k][38]?date('Y-m-d',strtotime($sheetData[$k][38])):'0000-00-00'),	
												'REFERRAL_GROUP' => trim($sheetData[$k][39]),	
												'REFERRAL_CODE' => trim($sheetData[$k][40]),	
												'REFERRALNAME' => trim($sheetData[$k][41]),	
												'P1CODE' => trim($sheetData[$k][42]),	
												'CASSTATUS' => trim($sheetData[$k][43]),	
												
												);
												
							
							$APPLICATIONSID = trim($sheetData[$k][1]);
							$whereRaw = " APPLICATIONSID ='".$APPLICATIONSID."'";
							$check = DB::table('bank_jonus_enbd_cards_physical')->whereRaw($whereRaw)->get();

							if(count($check)>0)
							{			
								
									$file_values['update_status'] = 1;
								
								DB::table('bank_jonus_enbd_cards_physical')->where('APPLICATIONSID', $APPLICATIONSID)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['APPLICATIONSID'] = $APPLICATIONSID;
								$all_values['update_status'] = 1;
							
								
								DB::table('bank_jonus_enbd_cards_physical')->insert($all_values);
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
}
