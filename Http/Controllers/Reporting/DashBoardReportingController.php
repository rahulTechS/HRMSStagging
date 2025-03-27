<?php
namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company\Department;
use App\Models\MIS\ENDBCARDStatus;

use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Logs\ShiftingLogs;

use App\Models\MIS\MainMisReport;
use App\Models\TeamTarget\Team_Leader;
use App\Models\MIS\ENBDCardsMisReport;
use App\Models\Logs\EndJonusEnbdCardsSubmission;
use App\Models\Logs\WipJonusEnbdCardsSubmission;
use App\Models\Logs\RejectedJonusEnbdCardsSubmission;
use App\Models\Logs\CancelJonusEnbdCardsSubmission;
use App\Models\MIS\MainMisReportTab;
use App\Models\Logs\EnbdTabResultProcess;

use Session;


class DashBoardReportingController extends Controller
{
    
      public function loadTLData(Request $request)
	  {
		  $deptId =  $request->dept_id;
		 
		  
		  $tL_details = Employee_details::where("job_role",'Team Leader')->where("status",1)->where("dept_id",$deptId)->get();
		  return view("Home/DashBoardReporting/loadTLData",compact('tL_details','deptId'));
	  }
	  
	  public function loadSEData(Request $request)
	  {
		  $deptId =  $request->dept_id;
		   $agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("job_role",'Sales Executive')->where("dept_id",$deptId)->get();
		   return view("Home/DashBoardReporting/LoadSEData",compact('agent_details'));
	  }
	  
	  public function loadAgentData(Request $request)
	  {
		  $tl =  $request->tl;
		  $dept_id =  $request->dept_id;
		  $agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",$dept_id)->where("tl_id",$tl)->get();
		   return view("Home/DashBoardReporting/loadAgentData",compact('agent_details','tl'));
	  }
	  
	  public function loadStatusData(Request $request)
	  {
		   $dept_id =  $request->dept_id;
		   $enbdStatus =  ENDBCARDStatus::where("status",1)->get();
		   return view("Home/DashBoardReporting/loadStatusData",compact('enbdStatus'));
	  }
	  public function firstLoadContent(Request $request)
	  {
		 
		  $parameters = $request->input();
	
	  $deptId =  $parameters['deptId'];
		   $fromDate =  $parameters['fromVal'];
		   $toDate =  $parameters['toVal'];
		   $TL_list =   $parameters['TL_list'];
		   $SubmissionStatus =  $parameters['SubmissionStatus'];
		   $salesAvg =  $parameters['salesAvg'];
		   $showMis =  $parameters['showMis'];
		   $tL_SE =  $parameters['tL_SE'];
		   $typeId =  $parameters['typeId'];
		   $listedValue =  $parameters['listedValue'];
		   $ageing =  $parameters['ageing'];
		   $load_target =  $parameters['load_target'];
		   $lengthOfService =  $parameters['lengthOfService'];
		   $fromDate = date("Y-m-d",strtotime($fromDate));
		
		  
		    $toDate = date("Y-m-d",strtotime($toDate));
		//$fromDate = '2022-12-21';
		//$toDate = '2023-01-20';
		$typeSource = array();
		if($typeId == 'All')
		{
			$typeSource[] = 'manual';
			$typeSource[] = 'Tab';
			$typeSource[] = 'NONE';
			
		}
		else
		{
			$typeSource[] = $typeId;
		}
		$listedValueArray = array(); 
		if($listedValue == 'All')
		{
			$listedValueArray[] = 'Ale';
			$listedValueArray[] = 'Nale';
			$listedValueArray[] = 'NONE';
			
		}
		else
		{
			$listedValueArray[] = $listedValue;
		}
		
		   /*
		   *getting all TL
		   */
		    
		    if($TL_list == 'All')
			   {
			   $tL_details = Employee_details::where("job_role",'Team Leader')->where("status",1)->where("dept_id",$deptId)->get();
			   }
			else
			   {
				    $tl_array = explode(",",$TL_list);
					$tL_details = Employee_details::where("job_role",'Team Leader')->where("status",1)->where("dept_id",$deptId)->whereIn("id",$tl_array)->get();
			   }
			   
			   if($SubmissionStatus == 'All')
			   {
					$enbdStatus =  ENDBCARDStatus::where("status",1)->get();
			   }
			   else
			   {
				    $status_array = explode(",",$SubmissionStatus);
					$enbdStatus =  ENDBCARDStatus::where("status",1)->whereIn("id",$status_array)->get();
			   }
		   /*
		   *getting all TL
		   */
		   $detailsAsPerTL = array();
		   $TLlistValues = array();
		   $StatusValues = array();
		   
				if($showMis == 1)
				{
					$totalSubmissions = 0;
					$listOfSE= array();
					
					 foreach($tL_details as $TL)
					   {
						   $TLlistValues[] = $TL->first_name;
						  
						   foreach($enbdStatus as $status)
						   {
							   if($tL_SE == 'All')
							   {
									$misLists = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$TL->id)->where("approved_notapproved",$status->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get();
							   }
							   else
							   {
								   $TLSEArray = explode(",",$tL_SE);
								   $misLists = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$TL->id)->where("approved_notapproved",$status->id)->whereIn("employee_id",$TLSEArray)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get(); 
							   }
							   $detailsAsPerTL[$TL->id][$status->id][] =$misLists;	
							   
							   $totalSubmissions = $totalSubmissions+count($misLists);
							   
							   foreach($misLists as $misSE)
							   {
								   if($misSE->Employee_status == 'Verified')
								   {
									$listOfSE[$misSE->employee_id] = $misSE->SE_CODE_NAME;
								   }
							   }
						   }
					   }
				}
				else
				{
				   foreach($tL_details as $TL)
				   {
					 /*   echo $fromDate;
					   echo '<br />';
					   echo $toDate;
					   exit; */ 
					   $TLlistValues[] = $TL->first_name;
					   $detailsAsPerTL[$TL->id]['login'] =	MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$TL->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get()->count();
					   foreach($enbdStatus as $status)
					   {
						 
						   $detailsAsPerTL[$TL->id][$status->id] =	MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$TL->id)->where("approved_notapproved",$status->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get()->count();
					   }
				   }
				   
				}
				
				    foreach($enbdStatus as $status)
					   {
						     $StatusValues[] = $status->status_name;
					   }
		  
		   if($TL_list == 'All')
			   {
				   $tls = 'All';
			   }
		   else
			   {
				   $tls = implode(',',$TLlistValues);
			   }
			   
		   if($SubmissionStatus == 'All')
			   {
				   $statusListing = 'All';
			   }
		   else
			   {
				   $statusListing = implode(',',$StatusValues);
			   }
			   $tltarget = array();
			 if($load_target == 2)
			 {
			    
				$datediff = strtotime($toDate) - strtotime($fromDate);

				$totalDays = round($datediff / (60 * 60 * 24));
				$firstMonth = date("m",strtotime($fromDate));
				$firstYear = date("Y",strtotime($fromDate));
				$lastMonth = date("m",strtotime($toDate));
				$lastYear = date("Y",strtotime($toDate));
				foreach($tL_details as $tl)
				{
					$totaltarget = 0;
					$teamLeaderModelFirst = Team_Leader::where("month",$firstMonth)->where("year",$firstYear)->where("product",1)->where("team_id",$tl->id)->first();
					if($teamLeaderModelFirst != '')
					{
						$totaltarget = $totaltarget+$teamLeaderModelFirst->team_target;
					}
					
					if($firstMonth != $lastMonth)
					{
						
						$teamLeaderModelLast = Team_Leader::where("month",$lastMonth)->where("year",$lastYear)->where("product",1)->where("team_id",$tl->id)->first();
					if($teamLeaderModelLast != '')
					{
						$totaltarget = $totaltarget+$teamLeaderModelLast->team_target;
					}
						
					}
					$tltarget[$tl->id]	= $totaltarget;
					
				}
				
			 }
/* echo '<pre>';			 
print_r($detailsAsPerTL);
exit; */			 
			 if($showMis == 1)
				{
					return view("Home/DashBoardReporting/firstLoadContentListing",compact('detailsAsPerTL','fromDate','toDate','enbdStatus','tls','statusListing','salesAvg','totalSubmissions','listOfSE','tL_SE','typeId','listedValue','ageing'));
				}
				else
				{
					return view("Home/DashBoardReporting/firstLoadContent",compact('detailsAsPerTL','fromDate','toDate','enbdStatus','tls','statusListing','salesAvg','showMis','typeId','listedValue','tltarget','load_target','lengthOfService'));
				}
	  }
	  
	  
	  
	  
	   public function firstLoadContentSE(Request $request)
	  {
		 
		  $parameters = $request->input();
	 $TLID = $parameters['TLID'];
	  $deptId =  $parameters['deptId'];
		   $fromDate =  $parameters['fromVal'];
		   $toDate =  $parameters['toVal'];
		   $SE_list =   $parameters['SE_list'];
		   $SubmissionStatus =  $parameters['SubmissionStatus'];
		   $salesAvg =  $parameters['salesAvg'];
		   $showMis =  $parameters['showMis'];
		  
		   $typeId =  $parameters['typeId'];
		   $listedValue =  $parameters['listedValue'];
		   $ageing =  $parameters['ageing'];
		   $load_target_SE =  $parameters['load_target_SE'];
		   $lengthOfService =  $parameters['lengthOfService'];
		   $fromDate = date("Y-m-d",strtotime($fromDate));
		
		  
		    $toDate = date("Y-m-d",strtotime($toDate));
		//$fromDate = '2022-12-21';
		//$toDate = '2023-01-20';
		$typeSource = array();
		if($typeId == 'All')
		{
			$typeSource[] = 'manual';
			$typeSource[] = 'Tab';
			$typeSource[] = 'NONE';
		}
		else
		{
			$typeSource[] = $typeId;
		}
		$listedValueArray = array(); 
		if($listedValue == 'All')
		{
			$listedValueArray[] = 'Ale';
			$listedValueArray[] = 'Nale';
			$listedValueArray[] = 'NONE';
		}
		else
		{
			$listedValueArray[] = $listedValue;
		}
		
		   /*
		   *getting all TL
		   */
		    
		    if($SE_list == 'All')
			   {
					$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("job_role",'Sales Executive')->where("dept_id",$deptId)->get();
			   }
			else
			   {
				    $SE_array = explode(",",$SE_list);
					$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("job_role",'Sales Executive')->where("dept_id",$deptId)->whereIn("id",$SE_array)->get();
			   }
			   
			   if($SubmissionStatus == 'All')
			   {
					$enbdStatus =  ENDBCARDStatus::where("status",1)->get();
			   }
			   else
			   {
				    $status_array = explode(",",$SubmissionStatus);
					$enbdStatus =  ENDBCARDStatus::where("status",1)->whereIn("id",$status_array)->get();
			   }
		   /*
		   *getting all TL
		   */
		   $detailsAsPerTL = array();
		   $TLlistValues = array();
		   $StatusValues = array();
		   
				if($showMis == 1)
				{
					$totalSubmissions = 0;
					
					
					 foreach($agent_details as $Agent)
					   {
						   $AgentlistValues[] = $Agent->first_name;
						  
						   foreach($enbdStatus as $status)
						   {
							   if($SE_list == 'All')
							   {
									$misLists = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$Agent->id)->where("approved_notapproved",$status->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get();
							   }
							   else
							   {
								   $SE_array = explode(",",$SE_list);
								   $misLists = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("approved_notapproved",$status->id)->whereIn("employee_id",$SE_array)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get(); 
							   }
							   $detailsAsPerTL[$Agent->id][$status->id][] =$misLists;	
							   
							   $totalSubmissions = $totalSubmissions+count($misLists);
							   
							 
						   }
					   }
				}
				else
				{
				   foreach($agent_details as $Agent)
				   {
					   $AgentlistValues[] = $Agent->first_name;
					   $detailsAsPerTL[$Agent->id]['login'] =	MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$Agent->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get()->count();
					   foreach($enbdStatus as $status)
					   {
						 
						   $detailsAsPerTL[$Agent->id][$status->id] =	MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$Agent->id)->where("approved_notapproved",$status->id)->whereIn("file_source",$typeSource)->whereIn("ALE_NALE",$listedValueArray)->get()->count();
					   }
				   }
				   
				}
				
				    foreach($enbdStatus as $status)
					   {
						     $StatusValues[] = $status->status_name;
					   }
		  
		   if($SE_list == 'All')
			   {
				   $agents = 'All';
			   }
		   else
			   {
				   $agents = implode(',',$AgentlistValues);
			   }
			   
		   if($SubmissionStatus == 'All')
			   {
				   $statusListing = 'All';
			   }
		   else
			   {
				   $statusListing = implode(',',$StatusValues);
			   }
			    $setarget = array();
			 if($load_target_SE == 2)
			 {
			    
				$datediff = strtotime($toDate) - strtotime($fromDate);

				$totalDays = round($datediff / (60 * 60 * 24));
				$firstMonth = date("m",strtotime($fromDate));
				$firstYear = date("Y",strtotime($fromDate));
				$lastMonth = date("m",strtotime($toDate));
				$lastYear = date("Y",strtotime($toDate));
				foreach($agent_details as $agent)
				{
					$totaltarget = 0;
					$teamLeaderModelFirst = Team_Leader::where("month",$firstMonth)->where("year",$firstYear)->where("product",1)->where("agent_id",$agent->id)->first();
					if($teamLeaderModelFirst != '')
					{
						$totaltarget = $totaltarget+$teamLeaderModelFirst->team_target;
					}
					
					if($firstMonth != $lastMonth)
					{
						
						$teamLeaderModelLast = Team_Leader::where("month",$lastMonth)->where("year",$lastYear)->where("product",1)->where("agent_id",$agent->id)->first();
					if($teamLeaderModelLast != '')
					{
						$totaltarget = $totaltarget+$teamLeaderModelLast->team_target;
					}
						
					}
					$setarget[$agent->id]	= $totaltarget;
					
				}
				
			 }	
			 if($showMis == 1)
				{
					return view("Home/DashBoardReporting/firstLoadContentListingSE",compact('detailsAsPerTL','fromDate','toDate','enbdStatus','agents','statusListing','salesAvg','totalSubmissions','typeId','listedValue','ageing','TLID'));
				}
				else
				{
					return view("Home/DashBoardReporting/firstLoadContentSE",compact('detailsAsPerTL','fromDate','toDate','enbdStatus','agents','statusListing','salesAvg','typeId','listedValue','setarget','load_target_SE','lengthOfService','TLID'));
				}
	  }
	  
	 public static function getMISStatus($statusId)
	 {
		 return ENDBCARDStatus::where("id",$statusId)->first()->status_name;
	 }
	  
	 public static function getSEName($seId)
	 {
		 return Employee_details::where("id",$seId)->first()->first_name;
	 }
	 
	 public function runProcessEndSubmissionUpdate(Request $request)
	 {
		    $_enbdCards = ENBDCardsMisReport::where("CURRENTACTIVITY","End")
						->where("end_report_status",1)
						->where("Employee_status","Verified")
						->where("match_status",2)->first();
						
				if($_enbdCards != '')
				{					
		
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
			$appId = $_enbdCards->application_id;
			$checkAPPIDEXIST = EndJonusEnbdCardsSubmission::where("app_id",$appId)->first();
			if($checkAPPIDEXIST == '')
			{
			$endJonusManualCards = new EndJonusEnbdCardsSubmission();
				/*
				*getting MIS for this APPID
				*/
				$misInternalData  =  MainMisReport::where("application_id",$appId)->where("approved_notapproved",3)->first();
				/*  echo '<pre>';
				print_r($misInternalData);
				exit;  */
				if($misInternalData != '')
				{
					$endJonusManualCards->app_id = $_enbdCards->application_id;
					$endJonusManualCards->action_date = date("Y-m-d",strtotime($_enbdCards->LASTUPDATED));
					$endJonusManualCards->action_date_text = $_enbdCards->LASTUPDATED;
					$endJonusManualCards->tl_id = $misInternalData->TL;
					$endJonusManualCards->se_id = $misInternalData->employee_id;
					$endJonusManualCards->location = $this->getLocation($misInternalData->employee_id);
					$endJonusManualCards->mis_id = $misInternalData->id;
					$endJonusManualCards->type = 'manual';
					$endJonusManualCards->submission_date = $misInternalData->submission_format;
					$endJonusManualCards->signed_date = date("Y-m-d",strtotime($_enbdCards->SIGNEDDATE));
					$endJonusManualCards->signed_date_text = $_enbdCards->SIGNEDDATE;
					if($endJonusManualCards->save())
					{
						/*
						*check id exist APP ID in wip Then update Status
						*/
						$wipAppIDCheck = WipJonusEnbdCardsSubmission::where("app_id",$_enbdCards->application_id)->first();
						if($wipAppIDCheck != '')
						{
							$wipId = $wipAppIDCheck->id;
							$updateWIP = WipJonusEnbdCardsSubmission::find($wipId);
							$updateWIP->show_status = 2;
							$updateWIP->move_to = 'End';
							$updateWIP->save();
							
						}
						/*
						*check id exist APP ID in wip Then update Status
						*/
						
						/*
						*if action is successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->end_report_status = 2;
						$updateEnbdCardsStatus->save();
						/*
						*if action is successfull
						*/
						echo "updated";
						exit;
					}
					else
					{
						/*
						*if action is not successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->end_report_status = 4;
						$updateEnbdCardsStatus->save();
						/*
						*if action is not successfull
						*/
						echo "not updated";
						exit;
					}
					
					
				}
				else
				{
						/*
						*if App not found successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->end_report_status = 3;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "not found";
						exit;
				}
				
				/*
				*getting MIS for this APPID
				*/
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
		
	
						echo "not found";
						exit;
			}
			else
			{
					$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->end_report_status = 5;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "already found";
						exit;
			}
				}
				else
				{
					echo "All DONE";
						exit;
				}
	 }
	 
	 
	 
	 public function runProcessWipSubmissionUpdate(Request $request)
	 {
		$_enbdCards = ENBDCardsMisReport::whereIn("CURRENTACTIVITY",array("Single Data Entry","Document Verification","Underwriting","Detail Data Entry","HOLD RCC","VERIFICATION DETAIL","HOLD SOURCING"))
						->where("wip_report_status",1)
						->where("Employee_status","Verified")
						->where("match_status",2)->first();
				/* echo '<pre>';
				print_r($_enbdCards);
				exit; */
				if($_enbdCards != '')
				{					
		
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
			$appId = $_enbdCards->application_id;
			/*
			*check App ID is exist
			*/
			$checkWIPAPPIDEXIST= WipJonusEnbdCardsSubmission::where("app_id",$appId)->first();
			if($checkWIPAPPIDEXIST != '')
			{
				$updateWIP = WipJonusEnbdCardsSubmission::find($checkWIPAPPIDEXIST->id);
				$updateWIP->show_status = 2;
				$updateWIP->move_to = 'Wip';
				$updateWIP->save();
			}
			/*
			*check App ID is exist
			*/
			$wipJonusManualCards = new WipJonusEnbdCardsSubmission();
				/*
				*getting MIS for this APPID
				*/
				$misInternalData  =  MainMisReport::where("application_id",$appId)->where("approved_notapproved",7)->first();
				/*  echo '<pre>';
				print_r($misInternalData);
				exit;  */
				if($misInternalData != '')
				{
					$wipJonusManualCards->app_id = $_enbdCards->application_id;
					$wipJonusManualCards->action_date = date("Y-m-d",strtotime($_enbdCards->LASTUPDATED));
					$wipJonusManualCards->action_date_text = $_enbdCards->LASTUPDATED;
					$wipJonusManualCards->tl_id = $misInternalData->TL;
					$wipJonusManualCards->se_id = $misInternalData->employee_id;
					$wipJonusManualCards->location = $this->getLocation($misInternalData->employee_id);
					$wipJonusManualCards->mis_id = $misInternalData->id;
					$wipJonusManualCards->type = 'manual';
					$wipJonusManualCards->submission_date = $misInternalData->submission_format;
					$wipJonusManualCards->signed_date = date("Y-m-d",strtotime($_enbdCards->SIGNEDDATE));
					$wipJonusManualCards->signed_date_text = $_enbdCards->SIGNEDDATE;
					$wipJonusManualCards->show_status = 1;
					$wipJonusManualCards->current_wip = $_enbdCards->CURRENTACTIVITY;
					if($wipJonusManualCards->save())
					{
						/*
						*if action is successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->wip_report_status = 2;
						$updateEnbdCardsStatus->save();
						/*
						*if action is successfull
						*/
						echo "updated";
						exit;
					}
					else
					{
						/*
						*if action is not successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->wip_report_status = 4;
						$updateEnbdCardsStatus->save();
						/*
						*if action is not successfull
						*/
						echo "not updated";
						exit;
					}
					
					
				}
				else
				{
						/*
						*if App not found successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->wip_report_status = 3;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "not found";
						exit;
				}
				
				/*
				*getting MIS for this APPID
				*/
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
		
	
						echo "not found";
						exit;
				}
				else
				{
					echo "All DONE";
						exit;
				}
	 }
	 
	 
	 
	 
	 
	 
	 
	 public function runProcessRejectedSubmissionUpdate(Request $request)
	 {
		$_enbdCards = ENBDCardsMisReport::where("CURRENTACTIVITY",array("Reject Review"))
						->where("rejected_report_status",1)
						->where("Employee_status","Verified")
						->where("match_status",2)->first();
				
				if($_enbdCards != '')
				{					
		
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
			$appId = $_enbdCards->application_id;
			/*
			*check if already Exist
			*/
			$checkAPPIDEXIST = RejectedJonusEnbdCardsSubmission::where("app_id",$appId)->first();
			/*
			*check if already Exist
			*/
			if($checkAPPIDEXIST == '')
			{
			$rejectedJonusManualCards = new RejectedJonusEnbdCardsSubmission();
				/*
				*getting MIS for this APPID
				*/
				$misInternalData  =  MainMisReport::where("application_id",$appId)->where("approved_notapproved",5)->first();
				/*  echo '<pre>';
				print_r($misInternalData);
				exit;  */
				if($misInternalData != '')
				{
					$rejectedJonusManualCards->app_id = $_enbdCards->application_id;
					$rejectedJonusManualCards->action_date = date("Y-m-d",strtotime($_enbdCards->LASTUPDATED));
					$rejectedJonusManualCards->action_date_text = $_enbdCards->LASTUPDATED;
					$rejectedJonusManualCards->tl_id = $misInternalData->TL;
					$rejectedJonusManualCards->se_id = $misInternalData->employee_id;
					$rejectedJonusManualCards->location = $this->getLocation($misInternalData->employee_id);
					$rejectedJonusManualCards->mis_id = $misInternalData->id;
					$rejectedJonusManualCards->type = 'manual';
					$rejectedJonusManualCards->submission_date = $misInternalData->submission_format;
					$rejectedJonusManualCards->signed_date = date("Y-m-d",strtotime($_enbdCards->SIGNEDDATE));
					$rejectedJonusManualCards->signed_date_text = $_enbdCards->SIGNEDDATE;
					if($rejectedJonusManualCards->save())
					{
						
						/*
						*check id exist APP ID in wip Then update Status
						*/
						$wipAppIDCheck = WipJonusEnbdCardsSubmission::where("app_id",$_enbdCards->application_id)->first();
						if($wipAppIDCheck != '')
						{
							$wipId = $wipAppIDCheck->id;
							$updateWIP = WipJonusEnbdCardsSubmission::find($wipId);
							$updateWIP->show_status = 2;
							$updateWIP->move_to = 'Rejected';
							$updateWIP->save();
							
						}
						/*
						*check id exist APP ID in wip Then update Status
						*/
						/*
						*if action is successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->rejected_report_status = 2;
						$updateEnbdCardsStatus->save();
						/*
						*if action is successfull
						*/
						echo "updated";
						exit;
					}
					else
					{
						/*
						*if action is not successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->rejected_report_status = 4;
						$updateEnbdCardsStatus->save();
						/*
						*if action is not successfull
						*/
						echo "not updated";
						exit;
					}
					
					
				}
				else
				{
						/*
						*if App not found successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->rejected_report_status = 3;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "not found";
						exit;
				}
				
				/*
				*getting MIS for this APPID
				*/
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
		
	
						echo "not found";
						exit;
			}
			else
			{
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->rejected_report_status = 5;
						$updateEnbdCardsStatus->save();
						echo "Already found";
						exit;
			}
				}
				else
				{
					echo "All DONE";
						exit;
				}
	 }
	 
	 
	 
	 public function runProcessCancelSubmissionUpdate(Request $request)
	 {
		$_enbdCards = ENBDCardsMisReport::where("CURRENTACTIVITY",array("CANCEL"))
						->where("cancel_report_status",1)
						->where("Employee_status","Verified")
						->where("match_status",2)->first();
				
				if($_enbdCards != '')
				{					
		
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
			$appId = $_enbdCards->application_id;
			$checkAPPIDExist = CancelJonusEnbdCardsSubmission::where("app_id",$appId)->first();
			if($checkAPPIDExist == '')
			{
			$cancelJonusManualCards = new CancelJonusEnbdCardsSubmission();
				/*
				*getting MIS for this APPID
				*/
				$misInternalData  =  MainMisReport::where("application_id",$appId)->where("approved_notapproved",2)->first();
				/*  echo '<pre>';
				print_r($misInternalData);
				exit;  */
				if($misInternalData != '')
				{
					$cancelJonusManualCards->app_id = $_enbdCards->application_id;
					$cancelJonusManualCards->action_date = date("Y-m-d",strtotime($_enbdCards->LASTUPDATED));
					$cancelJonusManualCards->action_date_text = $_enbdCards->LASTUPDATED;
					$cancelJonusManualCards->tl_id = $misInternalData->TL;
					$cancelJonusManualCards->se_id = $misInternalData->employee_id;
					$cancelJonusManualCards->location = $this->getLocation($misInternalData->employee_id);
					$cancelJonusManualCards->mis_id = $misInternalData->id;
					$cancelJonusManualCards->type = 'manual';
					$cancelJonusManualCards->submission_date = $misInternalData->submission_format;
					$cancelJonusManualCards->signed_date = date("Y-m-d",strtotime($_enbdCards->SIGNEDDATE));
					$cancelJonusManualCards->signed_date_text = $_enbdCards->SIGNEDDATE;
					if($cancelJonusManualCards->save())
					{
						/*
						*check id exist APP ID in wip Then update Status
						*/
						$wipAppIDCheck = WipJonusEnbdCardsSubmission::where("app_id",$_enbdCards->application_id)->first();
						if($wipAppIDCheck != '')
						{
							$wipId = $wipAppIDCheck->id;
							$updateWIP = WipJonusEnbdCardsSubmission::find($wipId);
							$updateWIP->show_status = 2;
							$updateWIP->move_to = 'Cancel';
							$updateWIP->save();
							
						}
						/*
						*check id exist APP ID in wip Then update Status
						*/
						/*
						*if action is successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->cancel_report_status = 2;
						$updateEnbdCardsStatus->save();
						/*
						*if action is successfull
						*/
						echo "updated";
						exit;
					}
					else
					{
						/*
						*if action is not successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->cancel_report_status = 4;
						$updateEnbdCardsStatus->save();
						/*
						*if action is not successfull
						*/
						echo "not updated";
						exit;
					}
					
					
				}
				else
				{
						/*
						*if App not found successfull
						*/
						$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->cancel_report_status = 3;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "not found";
						exit;
				}
				
				/*
				*getting MIS for this APPID
				*/
			/*
			*Inserting End mis to New Model for reporting
			*start Coding
			*
			*/
			
		
	
						echo "not found";
						exit;
			}
			else
			{
				$updateEnbdCardsStatus = ENBDCardsMisReport::find($_enbdCards->id);
						$updateEnbdCardsStatus->cancel_report_status = 5;
						$updateEnbdCardsStatus->save();
						/*
						*if App not found successfull
						*/
						echo "Already found";
						exit;
			}
				}
				else
				{
					echo "All DONE";
						exit;
				}
	 }
	 
	 
	 protected function getLocation($id)
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
			
		public function runProcessTabUpdate(Request $request)
			{
				$_internalMis = MainMisReport::where("tab_process_status",1)->where("file_source","Tab")->first();
				/* echo '<pre>';
				print_r($_internalMis);
				exit; */
				if($_internalMis != '')
				{		
				/*
				*getting MIS for this APPID
				*/
				$appId = $_internalMis->application_id;
				
				/*  echo '<pre>';
				print_r($misInternalData);
				exit;  */
					/*
					*check APP ID Already Exist
					*/
					$checkEnbdTabExist =	EnbdTabResultProcess::where("app_id",$appId)->first();
					/*
					*check APP ID Already Exist
					*/
					
					
						$enbdTabModel = new EnbdTabResultProcess();
					
					
					$enbdTabModel->app_id = $_internalMis->application_id;
					
					$enbdTabModel->tl_id = $_internalMis->TL;
					$enbdTabModel->se_id = $_internalMis->employee_id;
					$enbdTabModel->location = $this->getLocation($_internalMis->employee_id);
					$enbdTabModel->mis_id = $_internalMis->id;
					$enbdTabModel->type = 'Tab';
					$enbdTabModel->submission_date = $_internalMis->submission_format;
				
					if($_internalMis->current_activity_tab != '' && $_internalMis->current_activity_tab !=NULL)
					{
							$enbdTabModel->internal_status_name = $_internalMis->current_activity_tab;
							$currentACTTAb = $_internalMis->current_activity_tab;
							$currentACTTAbArray = explode("-",$currentACTTAb);
							
							$statusInternal = trim($currentACTTAbArray[0]);
							
							if($statusInternal == 'End')
							{
								$closeMonth = trim($currentACTTAbArray[1]);
								$enbdTabModel->status_id_internal = 3;
								$enbdTabModel->close_month = $closeMonth;
							}
							else if($statusInternal == 'REJECTED')
							{
								$enbdTabModel->status_id_internal = 5;
							}
							else if($statusInternal == 'CANCEL')
							{
								$enbdTabModel->status_id_internal = 2;
							}
							else if($statusInternal == 'CANCELLED')
							{
								$enbdTabModel->status_id_internal = 2;
							}
							else if($statusInternal == 'TERMINATED')
							{
								$enbdTabModel->status_id_internal = 2;
							}
							else if($statusInternal == 'ERROR')
							{
								$enbdTabModel->status_id_internal = 2;
							}
							else if($statusInternal == 'ACCOUNT')
							{
								$enbdTabModel->status_id_internal = 7;
							}
							
					}
					else
					{
						    $enbdTabModel->internal_status_name = 'NONE';
					}
					
					/* $enbdTabModel->signed_date = date("Y-m-d",strtotime($_enbdCardsTab->application_created));
					$enbdTabModel->signed_date_text = $_enbdCardsTab->application_created;
					$enbdTabModel->Application_status = $_enbdCardsTab->application_status;
					$enbdTabModel->DMS_Outcome = $_enbdCardsTab->DMS_Outcome; */
					
					$enbdTabModel->match_status = $_internalMis->match_status;
					$matchStatus = $_internalMis->match_status;
					if($matchStatus == 2)
					{
						$_enbdCardsTab = MainMisReportTab::where("application_number",$appId)->first();
						$enbdTabModel->signed_date = date("Y-m-d",strtotime($_enbdCardsTab->application_created));
					$enbdTabModel->signed_date_text = $_enbdCardsTab->application_created;
					$enbdTabModel->Application_status = $_enbdCardsTab->application_status;
					$enbdTabModel->DMS_Outcome = $_enbdCardsTab->DMS_Outcome;
					if($_enbdCardsTab->application_status == 'COMPLETED')
							{
								$enbdTabModel->status_id_bank = 3;
							}
							elseif($_enbdCardsTab->application_status == 'REJECTED')
							{
								$enbdTabModel->status_id_bank = 5;
							}
							elseif($_enbdCardsTab->application_status == 'CANCELLED')
							{
								$enbdTabModel->status_id_bank = 2;
							}
							elseif($_enbdCardsTab->application_status == 'SENT_TO_CHECKER')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'AO_COMPLETED_REJECT_REVIEW')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'SENT_TO_COMPLIANCE')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'SUBMITTED')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'REJECT_REVIEW')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'REJECTED_BY_CHECKER')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'SENT_TO_RCC')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'AO_COMPLETED_REFERRED_BY_RCC')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'REFERRED_BY_RCC')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'ERROR_IN_PROCESSING')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'AO_COMPLETED_SENT_TO_RCC')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'REFERRED_BY_COMPLIANCE')
							{
								$enbdTabModel->status_id_bank = 7;
							}
							elseif($_enbdCardsTab->application_status == 'CANCELLED_FSK_HIT')
							{
								$enbdTabModel->status_id_bank = 2;
							}
							
					}
					$enbdTabModel->show_on_page = 1;
					$enbdTabModel->existing_current_activity = $_internalMis->current_activity_tab;
					if($checkEnbdTabExist != '')
					{
						$enbdTabModelUpdate = EnbdTabResultProcess::find($checkEnbdTabExist->id);
						$enbdTabModelUpdate->show_on_page = 2;
						$enbdTabModelUpdate->save();
						
					}
							if($enbdTabModel->save())
							{
								$tabMisModelUpdate = MainMisReport::find($_internalMis->id);
								$tabMisModelUpdate->tab_process_status = 2;
								$tabMisModelUpdate->save(); 
								echo "Updated";exit;
							}
							else
							{
								 $tabMisModelUpdate = MainMisReport::find($_internalMis->id);
								$tabMisModelUpdate->tab_process_status = 4;
								$tabMisModelUpdate->save(); 
								echo "Issue In update";exit;
							}
					
				}
				else
				{
					
								echo "ALL Updated";exit;
				}
			}
	  
}
