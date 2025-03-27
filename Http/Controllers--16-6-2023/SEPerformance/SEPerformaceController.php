<?php
namespace App\Http\Controllers\SEPerformance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Logs\EndJonusEnbdCardsSubmission;

use App\Models\MIS\MainMisReport;
use App\Models\SEPerformance\SEAvgRecords1;
use App\Models\SEPerformance\SEAvgProcess1;
use App\Models\SEPerformance\SEPerformanceModel;
use App\Models\SEPerformance\SEPerformanceModelProcess;
use App\Models\Logs\EnbdTabResultProcess;
use App\Models\WatchList\SEWatchList;

use Session;


class SEPerformaceController extends Controller
{
	
	public function allowProcess1()
	{
		$currentDate = date("Y-m-d");
		$currentDay = date("d");
		if($currentDay > 20)
		{
			$currentMonth = date("M");
			$currentYear = date("Y");
			$process = SEAvgProcess1::where("month",$currentMonth)->where("year",$currentYear)->first();
			if($process == '')
			{
				echo "allow";
				exit;
			}
			else
			{
				echo "not allow";
				exit;
			}
		}
		echo "not allow";
		exit;
	}
	
	
	public function allowProcessPerformance()
	{
		$currentDate = date("Y-m-d");
		
		$process = SEPerformanceModelProcess::where("date_process",$currentDate)->first();
			if($process == '')
			{
				echo "allow";
				exit;
			}
			else
			{
				echo "not allow";
				exit;
			}
		
		echo "not allow";
		exit;
	}
	 public function readyModeltoProcessSEperformance()
	{
		$detailsSE = SEPerformanceModel::get();
		foreach($detailsSE as $_se)
		{
			$updateSe = SEPerformanceModel::find($_se->id);
			$updateSe->active = 2;
			$updateSe->save();
		}
	}
    public function readyModeltoProcessSE()
	{
		$detailsSE = SEAvgRecords1::get();
		foreach($detailsSE as $_se)
		{
			$updateSe = SEAvgRecords1::find($_se->id);
			$updateSe->active = 2;
			$updateSe->save();
		}
	}
     public function createDataforAllSE()
	 {
		 
		 /*
		 *get ALL SE END Number
		 */
		/*  echo "work";
		 exit; */
		$currentDate = date("Y-m-d");
		$currentDay = date("d");
		
		if($currentDay > 20)
		{
			$mainMonth = date("m");
			$mainYear = date("Y");
			
		}
		else
		{
			
			$mainMonth = date("m");
			$mainYear = date("Y");
			$mainMonth = $mainMonth-1;
			if($mainMonth == 0)
			{
				$mainMonth = 12;
				$mainYear = $mainYear-1;
			}
		}
		$todate = $mainYear.'-'.$mainMonth.'-20';
		
		$mainMonthFrom = date("m",strtotime($todate.'-3 months'));
		$mainYearFrom = date("Y",strtotime($todate.'-3 months'));
		$fromdate = $mainYearFrom.'-'.$mainMonthFrom.'-21';
		$monthEndArray = array();
		for($i=0;$i<3;$i++)
		{
			$monthEnd = date("M",strtotime($todate.'-'.$i.' months'));
			$monthEndYear = date("Y",strtotime($todate.'-'.$i.' months'));
			
			$monthEndArray[] = $monthEnd.' '.$monthEndYear;
		}
			
		 /*
		 *get ALL SE END Number
		 */
		 
		 /*
		 *get End from Jonus Manual
		 *
		 */
		
		 
		 $endDataAsPerSEManual = EndJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromdate)->whereDate("action_date","<=",$todate)->selectRaw('count(*) as total, se_id')->groupBy('se_id')->get();
				 
		 /*
		 *get End from Jonus Manual
		 *
		 */	 
		 /*
		 *get End from Jonus Tab
		 *
		 */
		
				 
		$endDataAsPerSETab = EnbdTabResultProcess::whereIn('close_month',$monthEndArray)->where('status_id_internal',3)->where("show_on_page",1)->selectRaw('count(*) as total, se_id')->groupBy('se_id')->get();
		/*
		 *get End from Jonus Tab
		 *
		 */
		
		
		
		
		
		
		
		foreach($endDataAsPerSEManual as $_endData)
		{
			$seAvgModel = new SEAvgRecords1();
			
			$seAvgModel->SE_id = $_endData->se_id;
			$seAvgModel->total_end_manual = $_endData->total;
			$seAvgModel->cards_per_day_manual = round($_endData->total/90,2);
			$SEId = $_endData->se_id;
			$totalLoginManual = MainMisReport::whereDate("submission_format",">=",$fromdate)->whereDate("submission_format","<=",$todate)->where("employee_id",$SEId)->where("file_source","manual")->get()->count();
			$seAvgModel->total_login_manual = $totalLoginManual;
			$seAvgModel->total_avg_login_end_manual = round($_endData->total/$totalLoginManual,2);
			$seAvgModel->location = $this->getLocation($_endData->se_id);
			$seAvgModel->department_id = 9;
			$seAvgModel->from_date = $fromdate;
			$seAvgModel->to_date = $todate;
			$seAvgModel->active = 1;
			/*total Work*/
			$seAvgModel->total_end = $_endData->total;
				$seAvgModel->total_login =  $totalLoginManual;
				$seAvgModel->cards_per_day_total =round($_endData->total/90,2);
				$seAvgModel->total_avg_login_end = round($_endData->total/$totalLoginManual,2);
			/*total Worl*/
			$seAvgModel->save();
			
		}	
	
		foreach($endDataAsPerSETab as $_tabSE)
		{
			$checkManualExist = 1;
			$tabSEEndDetails[$_tabSE->se_id] = $_tabSE->total;
			$SEExist = SEAvgRecords1::where("SE_id",$_tabSE->se_id)->where("active",1)->orderBy("id","DESC")->first();
			if($SEExist != '')
			{
				$tabModel = SEAvgRecords1::find($SEExist->id);
				
				$checkManualExist = 2;
			}
			else
			{
				$tabModel = new SEAvgRecords1();
				$tabModel->SE_id = $_tabSE->se_id;
				$tabModel->department_id = 9;
				$tabModel->active = 1;
				$tabModel->from_date = $fromdate;
				$tabModel->to_date = $todate;

			}
			$tabModel->total_end_tab = $_tabSE->total;
			$tabModel->cards_per_day_tab = round($_tabSE->total/90,2);
			$SEId = $_tabSE->se_id;
			$totalLoginTab = MainMisReport::whereDate("submission_format",">=",$fromdate)->whereDate("submission_format","<=",$todate)->where("employee_id",$SEId)->where("file_source","Tab")->get()->count();
			$tabModel->total_login_tab = $totalLoginTab;
			$tabModel->total_avg_login_end_tab = round($_tabSE->total/$totalLoginTab,2);
			
			/*
			*calculate Total
			*
			*/
			if($checkManualExist == 2)
			{
				$mEnd = $SEExist->total_end_manual;
				$mLogin = $SEExist->total_login_manual;
				$cardPerDayManual = $SEExist->cards_per_day_manual;
				$total_avg_login_endManual = $SEExist->total_avg_login_end_manual;
				$tabModel->total_end = $mEnd+$_tabSE->total;
				$tabModel->total_login = $mLogin+$totalLoginTab;
				$tabModel->cards_per_day_total = $cardPerDayManual+round($_tabSE->total/90,2);
				$tabModel->total_avg_login_end = $total_avg_login_endManual+round($_tabSE->total/$totalLoginTab,2);
			}
			else
			{
				$tabModel->total_end = $_tabSE->total;
				$tabModel->total_login = $totalLoginTab;
				$tabModel->cards_per_day_total = round($_tabSE->total/90,2);
				$tabModel->total_avg_login_end = round($_tabSE->total/$totalLoginTab,2);
			}
			$tabModel->save();
			/*
			*calculate Total
			*
			*/
			
		}
		
		$updateProduct = new SEAvgProcess1();
		$updateProduct->month = date("M");
		$updateProduct->year = date("Y");
		$updateProduct->save();
		echo "done";
			exit;
			
		 
	 }
	 
	 
	 protected  function getLocation($id)
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
			
	public function SEPerformanceAction()
	{
		$currentDate = date("Y-m-d");
		$day = date("d");
		
		$monthCurrent = date("m");
		$monthyear = date("Y");
		if($day <20)
		{
			$monthCurrent = $monthCurrent-1;
			if($monthCurrent == 0)
			{
				$monthCurrent = 12;
				$monthyear = $monthyear-1;
			}
			$monthCurrentTo = date("m");
			$monthyearTo = date("Y");
		
		}
		
		
			$fromDate = $monthyear.'-'.$monthCurrent.'-21';
			$toDate = date("Y").'-'.date("m").'-'.$day;
			$daysDiff = $this->daysDiff($toDate,$fromDate);
			$SEMainData = SEAvgRecords1::where("active",1)->get();
			foreach($SEMainData as $SEData)
			{
				$sePerformanceMod = new SEPerformanceModel();
				$sePerformanceMod->SE_id = $SEData->SE_id;
				$sePerformanceMod->histroy_total_end_days_manual = $SEData->cards_per_day_manual;
				$sePerformanceMod->histroy_total_end_days_tab = $SEData->cards_per_day_tab;
				$sePerformanceMod->histroy_total_end_days = $SEData->cards_per_day_total;
				$sePerformanceMod->from_date = $fromDate;
				$sePerformanceMod->to_date = $toDate;
				$sePerformanceMod->active = 1;
				$SEId = $SEData->SE_id;
				/*
				*manual Cards
				*/
				$totalLoginM = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("file_source","manual")->get()->count();
				
				
				$totalEndM = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved_internal",3)->where("file_source","manual")->get()->count();
				
				$sePerformanceMod->total_login_manual = $totalLoginM;
				$sePerformanceMod->total_end_manual = $totalEndM;
				
				
				
				$sePerformanceMod->total_end_per_day_manual = round($totalEndM/$daysDiff,2);
				$perDaysCardManual = round($totalEndM/$daysDiff,2);
				/*
				*manual Cards
				*/
				
				/*
				*Tab Cards
				*/
				$totalLoginT = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("file_source","Tab")->get()->count();
				
				
				$totalEndT = MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved",3)->where("file_source","Tab")->get()->count();
				
				$sePerformanceMod->total_login_tab = $totalLoginT;
				$sePerformanceMod->total_end_tab = $totalEndT;
				
				
				
				$sePerformanceMod->total_end_per_day_tab = round($totalEndT/$daysDiff,2);
				$totalEndPerDayTab = round($totalEndT/$daysDiff,2);
				
				/*
				*Tab Cards
				*/
				
				/*
				*total Login
				*/
				$total_end_per_day =  round($totalEndM/$daysDiff,2)+round($totalEndT/$daysDiff,2);
				$sePerformanceMod->total_login = $totalLoginM+$totalLoginT;
				$sePerformanceMod->total_end = $totalEndM+$totalEndT;
				$sePerformanceMod->total_end_per_day = round($totalEndM/$daysDiff,2)+round($totalEndT/$daysDiff,2);
				/*
				*total Login
				*/
				$sePerformanceMod->total_avg_login_end = $SEData->total_avg_login_end;
				$sePerformanceMod->total_avg_login_end_manual = $SEData->total_avg_login_end_manual;
				$sePerformanceMod->total_avg_login_end_tab = $SEData->total_avg_login_end_tab;
				
				if($total_end_per_day <$SEData->cards_per_day_total)
				{
					$sePerformanceMod->performance = 'Negative';
					$sePerformanceMod->performance_no = $SEData->cards_per_day_total - $total_end_per_day;
				}
				else
				{
					$sePerformanceMod->performance = 'Positive';
					$sePerformanceMod->performance_no = $total_end_per_day -$SEData->cards_per_day_total;
				}
				
				
				if($totalEndPerDayTab <$SEData->cards_per_day_tab)
				{
					$sePerformanceMod->performance_tab = 'Negative';
					$sePerformanceMod->performance_no_tab = $SEData->cards_per_day_tab - $totalEndPerDayTab;
				}
				else
				{
					$sePerformanceMod->performance_tab = 'Positive';
					$sePerformanceMod->performance_no_tab = $totalEndPerDayTab -$SEData->cards_per_day_tab;
				}
				
				
				if($perDaysCardManual <$SEData->cards_per_day_manual)
				{
					$sePerformanceMod->performance_manual = 'Negative';
					$sePerformanceMod->performance_no_manual = $SEData->cards_per_day_manual - $perDaysCardManual;
				}
				else
				{
					$sePerformanceMod->performance_manual = 'Positive';
					$sePerformanceMod->performance_no_manual = $perDaysCardManual -$SEData->cards_per_day_manual;
				}
				$sePerformanceMod->days_calculate = $daysDiff;
				$sePerformanceMod->save();
			}
			$updateProduct = new SEPerformanceModelProcess();
			$updateProduct->date_process = date("Y-m-d");
			
			$updateProduct->save();
			
			echo "Done";
			exit;
	}
	
	protected function daysDiff($toDate,$fromDate)
	{
	
		$datediff = strtotime($toDate)-strtotime($fromDate);

return round($datediff / (60 * 60 * 24));
	}
	
	public function SEPerfomer()
	{
		/*
		*top 10 worst SE
		* type ALL
		*/
		$worstSEList = SEPerformanceModel::where("performance","Negative")->where("total_end_per_day","!=","0")->where("active",1)->orderBy("total_end_per_day", "ASC")->limit(10)->get();
		/*
		*top 10 worst SE
		* type ALL
		*/
		
		
		/*
		*top 10 worst SE
		* type ALL
		*/
		$bestSEList = SEPerformanceModel::where("performance","Positive")->where("active",1)->orderBy("total_end_per_day","DESC")->limit(10)->get();
		/*
		*top 10 worst SE
		* type ALL
		*/
		return view("SEPerformance/SEPerfomer",compact('worstSEList','bestSEList'));
	}
	
	
	public function SEPerformerAsPerType($type,$SEType)
	{
		if($SEType == 'Best')
		{
			if($type== 'manual')
			{
				$SEList = SEPerformanceModel::where("performance_manual","Positive")->where("active",1)->orderBy("total_end_per_day_manual","DESC")->limit(10)->get();
			}
			else
			{
				
				$SEList = SEPerformanceModel::where("performance_tab","Positive")->where("active",1)->orderBy("total_end_per_day_tab","DESC")->limit(10)->get();
			}
		}
		else
			{
				if($type== 'manual')
					{
						$SEList = SEPerformanceModel::where("performance_manual","Negative")->where("total_end_per_day_manual","!=","0")->where("active",1)->orderBy("total_end_per_day_manual", "ASC")->limit(10)->get();
		
						
					}
					else
					{
						$SEList = SEPerformanceModel::where("performance_tab","Negative")->where("total_end_per_day_tab","!=","0")->where("active",1)->orderBy("total_end_per_day_tab", "ASC")->limit(10)->get();
						
					}
			}
			
			return view("SEPerformance/SEPerformerAsPerType",compact('SEList','type','SEType'));
	}
	
	public function bestSEPerformer()
	{
		/*
		*top 10 worst SE
		* type ALL
		*/
		$bestSEList = SEPerformanceModel::where("performance","Positive")->where("active",1)->orderBy("total_end_per_day","DESC")->limit(10)->get();
		/*
		*top 10 worst SE
		* type ALL
		*/
		return view("SEPerformance/bestSEPerformer",compact('bestSEList'));
	}
	
	
	public function worstSEPerformer()
	{
		/*
		*top 10 worst SE
		* type ALL
		*/
		$worstSEList = SEPerformanceModel::where("performance","Negative")->where("total_end_per_day","!=","0")->where("active",1)->orderBy("total_end_per_day", "ASC")->limit(10)->get();
		/*
		*top 10 worst SE
		* type ALL
		*/
		return view("SEPerformance/worstSEPerformer",compact('worstSEList'));
	}
	
	public static function getTLNameBySE($SEId)
	{
		$tl_id = Employee_details::where("id",$SEId)->first()->tl_id;
		if($tl_id != '' && $tl_id != NULL)
		{
			$tlName = Employee_details::where("id",$tl_id)->first();
			if($tlName != '')
			{
				return $tlName->first_name;
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
		public static function getTLIdBySE($SEId)
		{
			$tl_id = Employee_details::where("id",$SEId)->first()->tl_id;
			if($tl_id != '' && $tl_id != NULL)
			{
				return $tl_id;
				
			}
			else
			{
				return "";
			}
		}
	
	public function addToWatchList(Request $request)
	{
		 $SEId = $request->SEId;
	
		 $type = $request->type;
		
		 $mainid = $request->mainid;
	
		/*
		*get SE Data of performance
		*start code
		*/
		$sePerformanceData = SEPerformanceModel::where("id",$mainid)->where("active",1)->first();
		/*
		*get SE Data of performance
		*start code
		*/
		$watchListObj = new SEWatchList();
		$watchListObj->SE_id = $SEId;
		$watchListObj->TL_id = $this->getTLIdBySE($SEId);
		$watchListObj->type = $type;
		$watchListObj->status = 1;
		$watchListObj->total_login_manual = $sePerformanceData->total_login_manual;
		$watchListObj->total_login_tab = $sePerformanceData->total_login_tab;
		$watchListObj->total_end_manual = $sePerformanceData->total_end_manual;
		$watchListObj->total_end_tab = $sePerformanceData->total_end_tab;
		$watchListObj->total_end_per_day_manual = $sePerformanceData->total_end_per_day_manual;
		$watchListObj->total_end_per_day_tab = $sePerformanceData->total_end_per_day_tab;
		$watchListObj->total_login = $sePerformanceData->total_login;
		$watchListObj->total_end = $sePerformanceData->total_end;
		$watchListObj->total_end_per_day = $sePerformanceData->total_end_per_day;
		$watchListObj->histroy_total_end_days_manual = $sePerformanceData->histroy_total_end_days_manual;
		$watchListObj->histroy_total_end_days_tab = $sePerformanceData->histroy_total_end_days_tab;
		$watchListObj->histroy_total_end_days = $sePerformanceData->histroy_total_end_days;
		$watchListObj->performance = $sePerformanceData->performance;
		$watchListObj->performance_no = $sePerformanceData->performance_no;
		$watchListObj->from_date = $sePerformanceData->from_date;
		$watchListObj->to_date = $sePerformanceData->to_date;
		$watchListObj->total_avg_login_end = $sePerformanceData->total_avg_login_end;
		$watchListObj->total_avg_login_end_manual = $sePerformanceData->total_avg_login_end_manual;
		$watchListObj->total_avg_login_end_tab = $sePerformanceData->total_avg_login_end_tab;
		$watchListObj->performance_manual = $sePerformanceData->performance_manual;
		$watchListObj->performance_tab = $sePerformanceData->performance_tab;
		$watchListObj->performance_no_manual = $sePerformanceData->performance_no_manual;
		$watchListObj->performance_no_tab = $sePerformanceData->performance_no_tab;
		$watchListObj->days_calculate = $sePerformanceData->days_calculate;
		$watchListObj->date_of_performance = date("Y-m-d");
		if($watchListObj->save())
		{
			$updateSEPerformance = SEPerformanceModel::find($mainid);
			$updateSEPerformance->watch_list_status = 2;
			$updateSEPerformance->save();
		}
		
		echo "done";
		exit;
		
		
	}
	
	
	
	public function processToWatchList(Request $request)
	{
		
		$seForWatchList = SEPerformanceModel::where("watch_list_status",2)->where("active",1)->get();
		if(count($seForWatchList) >0)
		{
			foreach($seForWatchList as $SE)
			{
		 
		
		 $mainid = $SE->id;
		$type = SEWatchList::where('SE_id',$SE->SE_id)->orderBy("id","DESC")->first()->type;
		/*
		*get SE Data of performance
		*start code
		*/
		$sePerformanceData = SEPerformanceModel::where("id",$mainid)->where("active",1)->first();
		/*
		*get SE Data of performance
		*start code
		*/
		$watchListObj = new SEWatchList();
		$watchListObj->SE_id = $SE->SE_id;
		$watchListObj->TL_id = $this->getTLIdBySE($SE->SE_id);
		$watchListObj->type = $type;
		$watchListObj->status = 1;
		$watchListObj->total_login_manual = $sePerformanceData->total_login_manual;
		$watchListObj->total_login_tab = $sePerformanceData->total_login_tab;
		$watchListObj->total_end_manual = $sePerformanceData->total_end_manual;
		$watchListObj->total_end_tab = $sePerformanceData->total_end_tab;
		$watchListObj->total_end_per_day_manual = $sePerformanceData->total_end_per_day_manual;
		$watchListObj->total_end_per_day_tab = $sePerformanceData->total_end_per_day_tab;
		$watchListObj->total_login = $sePerformanceData->total_login;
		$watchListObj->total_end = $sePerformanceData->total_end;
		$watchListObj->total_end_per_day = $sePerformanceData->total_end_per_day;
		$watchListObj->histroy_total_end_days_manual = $sePerformanceData->histroy_total_end_days_manual;
		$watchListObj->histroy_total_end_days_tab = $sePerformanceData->histroy_total_end_days_tab;
		$watchListObj->histroy_total_end_days = $sePerformanceData->histroy_total_end_days;
		$watchListObj->performance = $sePerformanceData->performance;
		$watchListObj->performance_no = $sePerformanceData->performance_no;
		$watchListObj->from_date = $sePerformanceData->from_date;
		$watchListObj->to_date = $sePerformanceData->to_date;
		$watchListObj->total_avg_login_end = $sePerformanceData->total_avg_login_end;
		$watchListObj->total_avg_login_end_manual = $sePerformanceData->total_avg_login_end_manual;
		$watchListObj->total_avg_login_end_tab = $sePerformanceData->total_avg_login_end_tab;
		$watchListObj->performance_manual = $sePerformanceData->performance_manual;
		$watchListObj->performance_tab = $sePerformanceData->performance_tab;
		$watchListObj->performance_no_manual = $sePerformanceData->performance_no_manual;
		$watchListObj->performance_no_tab = $sePerformanceData->performance_no_tab;
		$watchListObj->days_calculate = $sePerformanceData->days_calculate;
		$watchListObj->date_of_performance = date("Y-m-d");
		$watchListObj->save();
			}
		echo "done";
		exit;
		
		}
		else
		{
			echo "not done";
		exit;
		}
		
		
	}

  public function goToWatchList(Request $request)
  {
	   $SEId = $request->SEId;
	   $seWatchList =  SEWatchList::where("SE_id",$SEId)->where("status",1)->get();
	   return view("SEPerformance/goToWatchList",compact('seWatchList'));
  }  
  
   public function goToWatchListMain(Request $request)
  {
	   $SEId = $request->SEId;
	   $seWatchList =  SEWatchList::where("SE_id",$SEId)->where("status",1)->get();
	   return view("SEPerformance/goToWatchListMain",compact('seWatchList'));
  }   
  
  public function SEWatchListPanel(Request $request)
  {
	  $currentDate = date("Y-m-d");
	   $seWatchList =  SEWatchList::where("status",1)->whereDate('date_of_performance',$currentDate)->orderBy("id","DESC")->get();
	   
	   return view("SEPerformance/SEWatchListPanel",compact('seWatchList'));
  }
  
  public static function getSEWatchListDetails($SEId)
  {
	  return SEWatchList::where("SE_id",$SEId)->where("status",1)->orderBy("id","DESC")->first();
  }
  
  public static function getWatchListSE($SEId)
  {
	   $seList =  SEWatchList::where("SE_id",$SEId)->where("status",1)->orderBy("id","DESC")->first();
	   if($seList == '')
	   {
		   return 'NONE';
	   }
	   else{
		   return $seList->type;
	   }
  }
  public function SEGraph()
  {
	  return view("SEPerformance/SEGraph");
  }
}