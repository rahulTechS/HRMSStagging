<?php

namespace App\Http\Controllers\MIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\MIS\ProductMis;
use App\Models\MIS\ENBDCardsImportFiles;
use App\Models\MIS\ENBDCardsMisReport;
use App\Models\MIS\MainMisImportFiles;
use App\Models\MIS\JonusReportLog;
use App\Models\Entry\Employee;
use App\Models\MIS\MainMisReport;
use App\Models\MIS\CurrentActivity;
use App\Models\MIS\ENDBCARDStatus;
use App\Models\MIS\MonthlyEnds;
use App\Models\Attribute\Attributes;
use App\Models\MIS\BankDetailsUAE;
use App\Models\MIS\MainMisImportENBDCardsTabFiles;
use App\Models\MIS\MainMisReportTab;
use App\Models\MIS\HandsOnMisReport;
use App\Models\MIS\HandsOnFinal;
use App\Models\MIS\HandsOnFinalTab;
use App\Models\Logs\ExportReportLogs;
use App\Models\Logs\ENBDCardsLogs;

class MargingController extends Controller
{
  
			
			
			public function findAppId(Request $request)
			{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				if(!empty($request->session()->get('margingofferset')))
				{
					
					$paginationValue = $request->session()->get('margingofferset');
				}
				else
				{
					$paginationValue = 10;
				}
				return view("MIS/Marging/findAppId",compact('paginationValue','agent_details'));
			}
			
			public function listingInternalMisMarge(Request $request)
			{
				$se = $request->se;
				 $whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  $selectedFilter['report'] = '';
			  $whereraw = "(application_id = '' OR application_id IS NULL) And (over_ride_status != 1)";
			  if($se != 'All')
			  {
				  $agentArray = explode("_",$se);
				  $agentBackCode = $agentArray[1];
				 $employeeDetail =  Employee_details::where("source_code",$agentBackCode)->first();
				 if($employeeDetail != '')
				 {
				  $whereraw .= ' AND (employee_id='.$employeeDetail->id.')';
				 }
			  }
			/* 	echo $whereraw;exit; */
				if(!empty($request->session()->get('margingofferset')))
				{
					$paginationValue = $request->session()->get('margingofferset');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = MainMisReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
					$reports = MainMisReport::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingInternalMisMarge'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = MainMisReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = MainMisReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				return view("MIS/Marging/listingInternalMisMarge",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function listingENBDMisMarge(Request $request)
			{
				$se = $request->se;
				$whereraw = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['report'] = '';
			  $whereraw = 'match_status in (1,3)';
			   if($se != 'All')
			  {
				  $agentArray = explode("_",$se);
				  $agentBackCode = $agentArray[1];
				 
				  $whereraw .= ' AND P1CODE="'.$agentBackCode.'"';
				 
			  }
			if(!empty($request->session()->get('marge_enbd_submission_from')))
				{
					$submissionForm = $request->session()->get('marge_enbd_submission_from');
					$selectedFilter['submission_from'] = $submissionForm;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing >= '".$submissionForm."'";
					}
					else
					{
						$whereraw .= " And date_sourcing  >= '".$submissionForm."'";
					}
				}
				else
				{
					$submissionForm = date("Y-m-d",strtotime("-60 days"));
					$selectedFilter['submission_from'] = $submissionForm;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing >= '".$submissionForm."'";
					}
					else
					{
						$whereraw .= " And date_sourcing  >= '".$submissionForm."'";
					}
				}
				
				
				if(!empty($request->session()->get('marge_enbd_submission_to')))
				{
					$submissionTo = $request->session()->get('marge_enbd_submission_to');
					$selectedFilter['submission_to'] = $submissionTo;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing <= '".$submissionTo."'";
					}
					else
					{
						$whereraw .= " And date_sourcing <= '".$submissionTo."'";
					}
				}
				else
				{
					$submissionTo = date("Y-m-d");
					$selectedFilter['submission_to'] = $submissionTo;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing <= '".$submissionTo."'";
					}
					else
					{
						$whereraw .= " And date_sourcing <= '".$submissionTo."'";
					}
				}
				
			//echo $whereraw;exit;
				if(!empty($request->session()->get('margingofferset')))
				{
					
					$paginationValue = $request->session()->get('margingofferset');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = enbdCardsMISReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = enbdCardsMISReport::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDMisMarge'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = enbdCardsMISReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = enbdCardsMISReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Marging/listingENBDMisMarge",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function margeConfirmation(Request $request)
			{
				$result = array();
				$internalMis = $request->internalMis;
				$enbdMis = $request->enbdMis;
				$enbdMisA = explode("_",$enbdMis);
				$internalMisA = explode("_",$internalMis);
				$result['appId'] = enbdCardsMISReport::where("id",$enbdMisA[2])->first()->application_id;
				$result['cm_name'] = MainMisReport::where("id",$internalMisA[2])->first()->cm_name;
				$result['employee_id'] = $this->getEmployeeName(MainMisReport::where("id",$internalMisA[2])->first()->employee_id);
				echo json_encode($result);
				exit;
				
				
			}
			public  function getEmployeeName($id)
			{
				$empData =Employee_details::where("id",$id)->first();
				if($empData != '')
				{
				return $empData->first_name.'&nbsp;'.$empData->middle_name.'&nbsp;'.$empData->last_name;
				}
				else
				{
				return '--';
				}
			}
			public function margeOffSet(Request $request)
			{
				$marge_offset = $request->marge_offset;
				$request->session()->put('margingofferset',$marge_offset);
				return  redirect('listingENBDMisMarge');
			}
			
			public function submissionRangeFilterMarging(Request $request)
			{
				$subFrom = $request->subFrom;
				$subTo = $request->subTo;
				$bank_code = $request->bank_code;
				$request->session()->put('marge_enbd_submission_from', date("Y-m-d",strtotime($subFrom)));
				$request->session()->put('marge_enbd_submission_to',date("Y-m-d",strtotime($subTo)));
				return  redirect('listingENBDMisMarge/'.$bank_code);
			}
		   
		   public function mergeENBDToMIS(Request $request)
		   {
			  $jonus = enbdCardsMISReport::where("match_status",1)->first();
				if($jonus != '')
				{
				  $appID = $jonus->application_id;
				  if($appID != '' && $appID != NULL)
				  {
					$misReport = MainMisReport::where("application_id",$appID)->where("over_ride_status","!=",1)->first();
					if($misReport != '')
					{
						
								$misReportId = $misReport->id;
								$updateMisObj = MainMisReport::find($misReportId);
								if(trim($jonus->CURRENTACTIVITY) == 'End')
								{
								$updateMisObj->current_activity = 2;
								$updateMisObj->approved_notapproved = 3;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Reject Review')
								{
								$updateMisObj->current_activity = 6;
								$updateMisObj->approved_notapproved = 5;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Single Data Entry')
								{
								$updateMisObj->current_activity = 7;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Document Verification')
								{
								$updateMisObj->current_activity = 12;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Underwriting')
								{
								$updateMisObj->current_activity = 8;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Detail Data Entry')
								{
								$updateMisObj->current_activity = 11;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'CANCEL')
								{
								$updateMisObj->current_activity = 1;
								$updateMisObj->approved_notapproved = 2;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'VERIFICATION DETAIL')
								{
								$updateMisObj->current_activity = 13;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'HOLD RCC')
								{
								$updateMisObj->current_activity = 3;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'HOLD SOURCING')
								{
								$updateMisObj->current_activity = 4;
								$updateMisObj->approved_notapproved = 7;
								}
								
								
								$updateMisObj->Offer = $jonus->OFFER;
								$updateMisObj->Scheme = $jonus->SCHEME;
								$updateMisObj->ev_status = $jonus->EVSTATUS;
								$updateMisObj->last_updated = $jonus->LASTUPDATED;
								$updateMisObj->last_updated_date = date("Y-m-d",strtotime($jonus->LASTUPDATED));
								$updateMisObj->approve_limit = $jonus->APPROVEDLIMIT;
								$updateMisObj->last_remarks_added = strtoupper($jonus->LASTREMARKSADDED);
								$updateMisObj->match_status = 2;
								$updateMisObj->hand_on_status = 2;
								
								$updateMisObj->save();
								
								$jonusUpdate = enbdCardsMISReport::find($jonus->id);
								$jonusUpdate->match_status =2;
								if($jonusUpdate->save())
								{
									/**
								  *Making Logs
								  *start code
								  */
								  $maintainedMis =  MainMisReport::where("id",$updateMisObj->id)->first();
								  if($maintainedMis->approved_notapproved != '')
									  {
									  $logsENBDCards = new ENBDCardsLogs();
									  $logsENBDCards->type = $maintainedMis->file_source;
									  $logsENBDCards->action = $maintainedMis->approved_notapproved;
									  $logsENBDCards->action_date = date("Y-m-d");
									  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
									  $logsENBDCards->action_area = "Jonus Update Status";
									  $logsENBDCards->mis_id = $updateMisObj->id;
									  $logsENBDCards->source = 'Entry';
									  $logsENBDCards->save();
									  }
								  
								   if($maintainedMis->current_activity != '')
									  {
									  $logsENBDCards = new ENBDCardsLogs();
									  $logsENBDCards->type = $maintainedMis->file_source;
									  $logsENBDCards->action = $maintainedMis->current_activity;
									  $logsENBDCards->action_date = date("Y-m-d");
									  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
									  $logsENBDCards->action_area = "Jonus Update Current Activity";
									  $logsENBDCards->mis_id = $updateMisObj->id;
									  $logsENBDCards->source = 'Entry';
									  $logsENBDCards->save();
									  }
								  
								   /**
								  *Making Logs
								  *end code
								  */
									echo "Updated";
								}
								else
								{
									echo "Issue to Update";
								}
								exit;
								
								
								
					}
					else
					{
								$jonusUpdate = enbdCardsMISReport::find($jonus->id);
								$jonusUpdate->match_status =3;
								$jonusUpdate->save();
						echo "APP ID NOT Found";
					exit;
					}
				  }
				}
				else
				{
					echo "not data";
					exit;
				}
			  echo "Null";
			  exit;
		   }
    public function mergeAppIdWithMIS(Request $request)
	{
		$misId = explode("_",$request->misId);
		$jonusId = explode("_",$request->jonusId);
		$jonus = enbdCardsMISReport::where("id",$jonusId[2])->first();
		
		$updateMisObj = MainMisReport::find($misId[2]);
							if(trim($jonus->CURRENTACTIVITY) == 'End')
								{
								$updateMisObj->current_activity = 2;
								$updateMisObj->approved_notapproved = 3;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Reject Review')
								{
								$updateMisObj->current_activity = 6;
								$updateMisObj->approved_notapproved = 5;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Single Data Entry')
								{
								$updateMisObj->current_activity = 7;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Document Verification')
								{
								$updateMisObj->current_activity = 12;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Underwriting')
								{
								$updateMisObj->current_activity = 8;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'Detail Data Entry')
								{
								$updateMisObj->current_activity = 11;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'CANCEL')
								{
								$updateMisObj->current_activity = 1;
								$updateMisObj->approved_notapproved = 2;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'VERIFICATION DETAIL')
								{
								$updateMisObj->current_activity = 13;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'HOLD RCC')
								{
								$updateMisObj->current_activity = 3;
								$updateMisObj->approved_notapproved = 7;
								}
								else if(trim($jonus->CURRENTACTIVITY) == 'HOLD SOURCING')
								{
								$updateMisObj->current_activity = 4;
								$updateMisObj->approved_notapproved = 7;
								}
								
								
								$updateMisObj->Offer = $jonus->OFFER;
								$updateMisObj->Scheme = $jonus->SCHEME;
								$updateMisObj->ev_status = $jonus->EVSTATUS;
								$updateMisObj->last_updated = $jonus->LASTUPDATED;
								$updateMisObj->last_updated_date = date("Y-m-d",strtotime($jonus->LASTUPDATED));
								$updateMisObj->approve_limit = $jonus->APPROVEDLIMIT;
								$updateMisObj->last_remarks_added = strtoupper($jonus->LASTREMARKSADDED);
								$updateMisObj->match_status = 2;
								$updateMisObj->hand_on_status = 2;
								
								
								$updateMisObj->application_id = $jonus->application_id;
								$updateMisObj->save();
								
								$jonusUpdate = enbdCardsMISReport::find($jonus->id);
								$jonusUpdate->match_status =2;
								$jonusUpdate->save();
								
								
								 /**
								  *Making Logs
								  *start code
								  */
								   $maintainedMis =  MainMisReport::where("id",$updateMisObj->id)->first();
								   if($maintainedMis->approved_notapproved != '')
								  {
								 
								  $logsENBDCards = new ENBDCardsLogs();
								  $logsENBDCards->type = $maintainedMis->file_source;
								  $logsENBDCards->action = $maintainedMis->approved_notapproved;
								  $logsENBDCards->action_date = date("Y-m-d");
								  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
								  $logsENBDCards->action_area = "Jonus Update Status";
								  $logsENBDCards->mis_id = $updateMisObj->id;
								  $logsENBDCards->source = 'Entry';
								  $logsENBDCards->save();
								  }
								  
								  if($maintainedMis->current_activity != '')
								  {
								  $logsENBDCards = new ENBDCardsLogs();
								  $logsENBDCards->type = $maintainedMis->file_source;
								  $logsENBDCards->action = $maintainedMis->current_activity;
								  $logsENBDCards->action_date = date("Y-m-d");
								  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
								  $logsENBDCards->action_area = "Jonus Update Current Activity";
								  $logsENBDCards->mis_id = $updateMisObj->id;
								  $logsENBDCards->source = 'Entry';
								  $logsENBDCards->save();
								  }
								  
								   /**
								  *Making Logs
								  *end code
								  */
								echo "DONE";
								exit;
	}
	
public function exportMisReport(Request $request)
{
	$parameters = $request->input(); 
	
	         $selectedId = $parameters['selectedIds'];
	        $filename = 'mis_report_'.date("d-m-Y-h-i-s").'.csv';
			/*
			*Export Logs
			*/
			$exportLogsObj = new ExportReportLogs();
			$exportLogsObj->download_area = 'MIS Cards Report Internal';
			$exportLogsObj->download_filename = $filename;
			$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
			$exportLogsObj->save();
			/*
			*export Logs
			*/
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$header = array();
			$header[] = strtoupper('Data of Submission');
			$header[] = strtoupper('Application ID');
			$header[] = strtoupper('Application Type');
			$header[] = strtoupper('Lead Source');
			$header[] = strtoupper('PRODUCT');
			$header[] = strtoupper('Current Activity');
			$header[] = strtoupper('Status');
			$header[] = strtoupper('Monthly End');
			$header[] = strtoupper('Customer Name');
			$header[] = strtoupper('Customer Company Name');
			$header[] = strtoupper('Customer Name As Per Visa');
			$header[] = strtoupper('Customer Mobile');
			$header[] = strtoupper('Customer Office');
			$header[] = strtoupper('Customer Email');
			$header[] = strtoupper('SALARIED');
			$header[] = strtoupper('Salary');
			$header[] = strtoupper('LOS');
			$header[] = strtoupper('ACCOUNT NO');
			$header[] = strtoupper('NATIONALITY');
			$header[] = strtoupper('PASSPORT NO');
			$header[] = strtoupper('DOB');
			$header[] = strtoupper('VISA Expiry DATE');
			$header[] = strtoupper('DESIGNATION');
			$header[] = strtoupper('Agent Name');
			$header[] = strtoupper('Agent Location');
			$header[] = strtoupper('Offer');
			$header[] = strtoupper('Scheme');
			$header[] = strtoupper('EV Status');
			$header[] = strtoupper('Approve Limit');
			$header[] = strtoupper('Last Remarks Added');
			$header[] = strtoupper('Last Updated Date');
			$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			fputcsv($f, $header, ',');
			foreach ($selectedId as $sid) {
				 $misData = MainMisReport::where("id",$sid)->first();
				$values = array();
				$values[] = date("d M Y",strtotime($misData->submission_format));
				$values[] = $misData->application_id;
				$values[] = strtoupper($misData->application_type);
				$values[] = strtoupper($misData->lead_source);
				$values[] = strtoupper($misData->PRODUCT);
				$values[] = strtoupper($this->getCurrentActivity($misData->current_activity));
				$values[] = strtoupper($this->getapproved_notapproved($misData->approved_notapproved));
				$values[] = strtoupper($this->getmonthly_ends($misData->monthly_ends));
				$values[] = strtoupper($misData->cm_name);
				$values[] = strtoupper($misData->fv_company_name);
				$values[] = strtoupper($misData->company_name_as_per_visa);
				$values[] = strtoupper($misData->CV_MOBILE_NUMBER);
				$values[] = strtoupper($misData->EV_DIRECT_OFFICE_NO);
				$values[] = strtoupper($misData->E_MAILADDRESS);
				$values[] = strtoupper($misData->SALARIED);
				$values[] = strtoupper($misData->SALARY);
				$values[] = strtoupper($misData->LOS);
				$values[] = strtoupper($misData->ACCOUNT_NO);
				$values[] = strtoupper($misData->NATIONALITY);
				$values[] = strtoupper($misData->PASSPORT_NO);
				$values[] = strtoupper($misData->DOB);
				$values[] = strtoupper($misData->VISA_Expiry_DATE);
				$values[] = strtoupper($misData->DESIGNATION);
				$values[] = strtoupper($misData->SE_CODE_NAME);
				$values[] = strtoupper($this->getLocation($misData->employee_id));
				$values[] = strtoupper($misData->Offer);
				$values[] = strtoupper($misData->Scheme);
				$values[] = strtoupper($misData->ev_status);
				$values[] = strtoupper($misData->approve_limit);
				$values[] = strtoupper($misData->last_remarks_added);
				if(!empty($misData->last_updated_date))
				{
				$values[] = date("d M Y",strtotime($misData->last_updated_date));
				}
				fputcsv($f, $values, ',');
			}
			
	echo $filename;
	exit;
}
 public  function getCurrentActivity($id)
		   {
			   if(!empty($id))
			   {
				 $currentActObj =  CurrentActivity::where("id",$id)->first();
				 if($currentActObj  != '')
				 {
					  return $currentActObj->name;
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
		   
		   public  function getapproved_notapproved($id)
		   {
			    if(!empty($id))
			   {
				   $statusObj = ENDBCARDStatus::where("id",$id)->first();
				   if($statusObj != '' )
				   {
						return $statusObj->status_name;
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
		   public  function getmonthly_ends($id)
		   {
			    if(!empty($id))
			   {
				   $monthlyEnds = MonthlyEnds::where("id",$id)->first();
				   if($monthlyEnds != '')
				   {
					return $monthlyEnds->name;
				   }
				   else
				   {
					   return '--';
				   }
			   }
			   else
			   {
				   return "-";
			   }
		   }

public  function getLocation($id)
			{
				$empData =Employee_details::where("id",$id)->first();
				
				if($empData != '')
				{
				$empId = $empData->emp_id;
				return Employee_attribute::where("emp_id",$empId)->where("attribute_code","work_location")->first()->attribute_values;
				}
				else
				{
				return '--';
				}
				
			}
			
public function createhandsOnReports()
{
	$_mis = MainMisReport::where("match_status",2)->where('hand_on_status',2)->where("file_source","manual")->first();
	if($_mis != '')
	{
		$misId = $_mis->id;
		$appID = $_mis->application_id;
		$enbdCardsData = enbdCardsMISReport::where("application_id",$appID)->first();
		
		
		$handsOnMis = HandsOnMisReport::where("APPLICATIONSID",$appID)->first();
		if($handsOnMis != '')
		{
			$objHand = HandsOnMisReport::find($handsOnMis->id);
		}
		else
		{
			$objHand = new HandsOnMisReport();
			
		}
		$objHand->APPLICATIONSID = $appID;
		$objHand->NAME = $_mis->cm_name;
		$objHand->FILERECEIPTDTTIME = $enbdCardsData->FILERECEIPTDTTIME;
		$objHand->OFFER = $enbdCardsData->OFFER;
		$objHand->CURRENTACTIVITY = $enbdCardsData->CURRENTACTIVITY;
		$objHand->DATEOFSOURCING = $enbdCardsData->DATEOFSOURCING;
		$objHand->SCHEME = $enbdCardsData->SCHEME;
		$objHand->DME_RBE = $enbdCardsData->DME_RBE;
		$objHand->LASTREMARKSADDED = $_mis->last_remarks_added;
		$objHand->EVSTATUS = $_mis->ev_status;
		$objHand->EVACTIONDATE = $enbdCardsData->EVACTIONDATE;
		$objHand->CVSTATUS = $enbdCardsData->CVSTATUS;
		$objHand->WCSTATUS = $enbdCardsData->WCSTATUS;
		$objHand->LASTUPDATED = $enbdCardsData->LASTUPDATED;
		$objHand->PRI_SUPP_STANDALONE = $enbdCardsData->PRI_SUPP_STANDALONE;
		$objHand->APPROVEDLIMIT = $_mis->approve_limit;
		$objHand->P1CODE = $enbdCardsData->P1CODE;
		$objHand->save();
		$misUpdateObj = MainMisReport::find($misId);
		$misUpdateObj->hand_on_status = 3;
		if($misUpdateObj->save())
		{
			echo "updated";
			exit;
		}
		else
		{
			echo "issue to update";
			exit;
		}
	}
	else
	{
		echo "not update";
		exit;
	}
	echo "NULL";
	exit;
}


public function createhandsOnReportsFinal()
{
	$_mis = MainMisReport::where("match_status",1)->where("file_source","manual")->where("hand_on_status_final",1)->WhereNull('application_id')->first();
	
	if($_mis != '')
	{
		
		$misId = $_mis->id;
		
		$handsOnMis = HandsOnFinal::where("mis_id",$misId)->first();
		if($handsOnMis == '')
		{
		$objHand = new HandsOnFinal();
		$objHand->login_date = date("d/m/Y",strtotime($_mis->submission_format));
		$objHand->login_date_format = $_mis->submission_format;
		$objHand->application_type = $_mis->application_type;
		
		$objHand->customer_name = $_mis->cm_name;
		$objHand->mobile_no = $_mis->CV_MOBILE_NUMBER;
		$objHand->nationality = $_mis->NATIONALITY;
		$objHand->product_type = $_mis->PRODUCT;
		$objHand->se_code = $_mis->SE_CODE_NAME;
		$objHand->mobile_no = $_mis->CV_MOBILE_NUMBER;
		$objHand->file_source = $_mis->file_source;
		$objHand->mis_id = $misId;
		
		$objHand->save();
		$misUpdateObj = MainMisReport::find($misId);
		$misUpdateObj->hand_on_status_final = 3;
		if($misUpdateObj->save())
		{
			echo "updated";
			exit;
		}
		else
		{
			echo "issue to update";
			exit;
		} 
		}
		else
		{
			echo "not update";
			exit;
		}
	}
	else
	{
		echo "not update";
		exit;
	}
	echo "NULL";
	exit;
}

public function createhandsOnReportsFinalTab()
{
	$_mis = MainMisReportTab::where("handsOnReport",1)->first();
	
	if($_mis != '')
	{
		
		$application_number = $_mis->application_number;
		
		$handsOnMis = HandsOnFinalTab::where("appid",$application_number)->first();
		if($handsOnMis == '')
		{
			$objHand = new HandsOnFinalTab();
			$objHand->login_date = date("d/m/Y",strtotime($_mis->application_created));
			$objHand->login_date_format =date("Y-m-d",strtotime($_mis->application_created));
		
			
			$objHand->customer_name = $_mis->customer_name;
			$objHand->appid = $_mis->application_number;
			$objHand->PRODUCT = $_mis->Card_Name;
			$employee_id = $_mis->employee_id;
		if($_mis->Employee_status == 'Verified')
		{
			$empDetails = Employee_details::where("id",$employee_id)->first();
			$objHand->se_code = $empDetails->first_name.' '.$empDetails->middle_name.' '.$empDetails->last_name.'_'.$empDetails->source_code;
		}
			$objHand->Status = $_mis->application_status;
			$objHand->save();
			$misUpdateObj = MainMisReportTab::find($_mis->id);
			$misUpdateObj->handsOnReport = 2;
		if($misUpdateObj->save())
		{
			echo "updated";
			exit;
		}
		else
		{
			echo "issue to update";
			exit;
		} 
		}
		else
		{
			echo "not update";
			exit;
		}
	}
	else
	{
		echo "not update";
		exit;
	}
	echo "NULL";
	exit;
}
}
