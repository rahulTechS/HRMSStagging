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
use App\Models\MIS\JonusReportLog;

use Session;


class InternalReportController extends Controller
{
    
      public static function getEndCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				$dayValue = date("d",strtotime($toDate));
				if($dayValue == 31)
				{
					$toDate = date("Y-m-d",strtotime($toDate. ' - 1 days'));
				}
				if($dayValue <= 20)
				{
				$monthValue = date("M",strtotime($toDate));
				$monthYears = date("Y",strtotime($toDate));
				}
				else
				{
					$monthValue = date("M",strtotime($toDate. ' + 1 months'));
					$monthYears = date("Y",strtotime($toDate. ' + 1 months'));
				}
			/* 	echo $monthValue;
				echo '<br />';
				echo $monthYears;exit; */
				
				return  MainMisReport::where("TL",$tlId)->where("approved_notapproved_internal",3)->where("mothly_end_internal","LIKE","%".$monthValue."%")->where("mothly_end_internal","LIKE","%".$monthYears."%")->where("file_source","manual")->distinct("application_id")->get()->count();
				
			}
	  public static function getWipCount($tlId,$fromDate,$toDate)
	  {
		 
		  //$wip1 = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
		  $wip2 =  MainMisReport::where("TL",$tlId)->where("approved_notapproved_internal",7)->where("file_source","manual")->distinct("application_id")->count();
		 // $wip = $wip1+$wip2;
		  return $wip2;
	  }
			
	public static function getnewWIP($tlId,$fromDate,$toDate)
	{
		return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
	}
	
	public static function getnewWIPSE($SEId,$tlId,$fromDate,$toDate)
	{
		return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
	}
	
			public static function getCancelCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved_internal",2)->where("file_source","manual")->get()->count();
			}			
	  public static function getRejectedCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved_internal",5)->where("file_source","manual")->get()->count();
			}	
			
			
	public static function getCountAsPerStatus($tlId,$fromDate,$toDate,$statusId,$type)
			{
				
				if($type == 'm')
				{
					
					if($statusId == 11)
					{
						
						return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
					}
					else
					{
						
					return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved_internal",$statusId)->where("file_source","manual")->get()->count();
					}
				}
				else
				{
					if($statusId == 11)
					{
						return '0';
					}
					else
					{
					return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved",$statusId)->where("file_source","Tab")->get()->count();
					}
				}
			}
			
	public static function getEndCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				$dayValue = date("d",strtotime($toDate));
				if($dayValue == 31)
				{
					$toDate = date("Y-m-d",strtotime($toDate. ' - 1 days'));
				}
				if($dayValue <= 20)
				{
				$monthValue = date("M",strtotime($toDate));
				$monthYears = date("Y",strtotime($toDate));
				}
				else
				{
					$monthValue = date("M",strtotime($toDate. ' + 1 months'));
					$monthYears = date("Y",strtotime($toDate. ' + 1 months'));
				}
				
				
				return  MainMisReport::where("employee_id",$SEId)->where("approved_notapproved_internal",3)->where("mothly_end_internal","LIKE","%".trim($monthValue)."%")->where("mothly_end_internal","LIKE","%".trim($monthYears)."%")->where("file_source","manual")->distinct("application_id")->get()->count();
				
			}
	public static function getWipCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				//$wip1 = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
				 $wip2 =  MainMisReport::where("employee_id",$SEId)->where("approved_notapproved_internal",7)->where("file_source","manual")->distinct("application_id")->get()->count();
				return $wip2;
			}	
public static function getCancelCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved_internal",2)->where("file_source","manual")->get()->count();
				
			}
public static function getRejectedCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved_internal",5)->where("file_source","manual")->get()->count();
				
			}
	public static function getCountAsPerStatusSE($SEId,$tlId,$fromDate,$toDate,$statusId,$type)
			{
				
				if($type == 'm')
				{
					
					if($statusId == 11)
					{
						
						return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("mothly_end_internal","AWATING REPORT")->where("file_source","manual")->get()->count();
					}
					else
					{
						
					return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved_internal",$statusId)->where("file_source","manual")->get()->count();
					}
				}
				else
				{
					if($statusId == 11)
					{
						return '0';
					}
					else
					{
					return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved",$statusId)->where("file_source","Tab")->get()->count();
					}
				}
			}

   public static function getlastUpdatedJonus()
   {
	   $uploaded_date = JonusReportLog::where("type","cards-m")->orderBy("id","DESC")->first()->uploaded_date;
	   return date("d M Y",strtotime($uploaded_date));
   }   
}
