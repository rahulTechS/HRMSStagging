<?php

namespace App\Http\Controllers\MIS;
//use App\Reports\MyReport;
require_once "/srv/www/htdocs/core/autoload.php";
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
use App\Models\LoanMis\ENDBLoanMis;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\PDFMarge\FPDF_Merge;
use App\Models\Logs\EndJonusEnbdCardsSubmission;
use App\Models\Logs\WipJonusEnbdCardsSubmission;
use App\Models\Logs\CancelJonusEnbdCardsSubmission;
use App\Models\Logs\RejectedJonusEnbdCardsSubmission;
use App\Models\Logs\EnbdTabResultProcess;
use App\Models\DataCut\TLAnalysisPerformanceEnbd;
use App\Models\Onboarding\DocumentCollectionDetails;

class ReportingController extends Controller
{
  
			
			
			public static function getReportMisCards($monthSelected,$yearSelected,$status,$leaderId,$type )
			{
				
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				 $dateFrom = $currentYear.'-'.$newMonth.'-21';
				//echo '<br />';
				 $dateTo = $yearSelected.'-'.$monthSelected.'-20';
				//echo '<br />';
				//echo $leaderId;
				//echo '<br />';
				//echo $type;
				//exit;
				if($status == 'login')
				{
					if($type == 'all')
					{
					return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$leaderId)->get()->count();
					}
					else
					{
					return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("file_source",$type)->where("TL",$leaderId)->get()->count();	
					}
				}
				else
				{
					if($type == 'all')
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$leaderId)->where("approved_notapproved",$status)->get()->count();
					}
					else
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$leaderId)->where("file_source",$type)->where("approved_notapproved",$status)->get()->count();
					}
				}
			}
			
			
			public static function getReportMisCardsAsPerAgent($monthSelected,$yearSelected,$status,$agentId,$type )
			{
				
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				 $dateFrom = $currentYear.'-'.$newMonth.'-21';
				//echo '<br />';
				 $dateTo = $yearSelected.'-'.$monthSelected.'-20';
				//echo '<br />';
				//echo $leaderId;
				//echo '<br />';
				//echo $type;
				//exit;
				if($status == 'login')
				{
					if($type == 'all')
					{
					return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("employee_id",$agentId)->get()->count();
					}
					else
					{
					return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("file_source",$type)->where("employee_id",$agentId)->get()->count();	
					}
				}
				else
				{
					if($type == 'all')
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("employee_id",$agentId)->where("approved_notapproved",$status)->get()->count();
					}
					else
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("employee_id",$agentId)->where("file_source",$type)->where("approved_notapproved",$status)->get()->count();
					}
				}
			}
			
			
			
			
			public static function getWaiting($monthSelected,$yearSelected,$leaderId,$type)
			{
				
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				$dateFrom = $currentYear.'-'.$newMonth.'-21';
				$dateTo = $yearSelected.'-'.$monthSelected.'-20';
				if($type == 'all')
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$leaderId)->whereNull("approved_notapproved")->get()->count();
					}
					else
					{
						return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$leaderId)->where("file_source",$type)->whereNull("approved_notapproved")->get()->count();
					}
			}
			
			
			public static function getReportMisLoan($monthSelected,$yearSelected,$status,$leaderId,$product)
			{
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				$dateFrom = $currentYear.'-'.$newMonth.'-21';
				$dateTo = $yearSelected.'-'.$monthSelected.'-20';
				
				if($status == 'login')
				{
					return ENDBLoanMis::whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->where("TL_NAME",$leaderId)->where("PRODUCT",$product)->get()->count();
				}
				else
				{
					return ENDBLoanMis::whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->where("TL_NAME",$leaderId)->where("PRODUCT",$product)->where("STATUS",$status)->get()->count();
					
				}
			}
			
			public static function getLeaderName($id)
			{
				$name = Employee_details::where("id",$id)->first();
				if($name != '')
				{
					return $name->first_name.' '.$name->middle_name.' '.$name->last_name;
				}
				else
				{
					return '--';
				}
				 
			}
			
			
			public function generateReportPDF()
			{
				$this->fpdf = new Fpdf;
				
				$this->fpdf->AddPage();
				$this->fpdf->SetFont('helvetica','',7);
				$x = 10;
				$y = 10;
				$this->fpdf->rect($x,$y,$x+180,90);
				$this->fpdf->line($x+10,$y,$x+10,100);
				$this->fpdf->line($x+10,$y+10,$x+190,$y+10);
				$y = $y+10;
				$this->fpdf->line($x+10,$y+10,$x+190,$y+10);
				
				$this->fpdf->line($x+10,$y+15,$x+190,$y+15);
				/*cards*/
				$this->fpdf->Text($x+32,$y+13,strtoupper("Credit Card"));
				/*logins*/
				$this->fpdf->Text($x+13,$y+20,strtoupper("S"));
				$this->fpdf->Text($x+13,$y+25,strtoupper("N"));
				$this->fpdf->Text($x+13,$y+30,strtoupper("I"));
				$this->fpdf->Text($x+13,$y+35,strtoupper("G"));
				$this->fpdf->Text($x+13,$y+40,strtoupper("O"));
				$this->fpdf->Text($x+13,$y+45,strtoupper("L"));
				/*logins*/
				/*approved*/
				$this->fpdf->Text($x+23,$y+20,strtoupper("D"));
				$this->fpdf->Text($x+23,$y+24,strtoupper("E"));
				$this->fpdf->Text($x+23,$y+28,strtoupper("V"));
				$this->fpdf->Text($x+23,$y+32,strtoupper("O"));
				$this->fpdf->Text($x+23,$y+36,strtoupper("R"));
				$this->fpdf->Text($x+23,$y+40,strtoupper("P"));
				$this->fpdf->Text($x+23,$y+44,strtoupper("P"));
				$this->fpdf->Text($x+23,$y+48,strtoupper("A"));
				/*approved*/
				
				/*PHYISCAL END*/
				$this->fpdf->Text($x+33,$y+18,strtoupper("D"));
				$this->fpdf->Text($x+33,$y+21,strtoupper("N"));
				$this->fpdf->Text($x+33,$y+24,strtoupper("E"));
				$this->fpdf->Text($x+33,$y+25,strtoupper(" "));
				$this->fpdf->Text($x+33,$y+28,strtoupper("L"));
				$this->fpdf->Text($x+33,$y+31,strtoupper("A"));
				$this->fpdf->Text($x+33,$y+34,strtoupper("C"));
				$this->fpdf->Text($x+33,$y+37,strtoupper("S"));
				$this->fpdf->Text($x+33,$y+40,strtoupper("I"));
				$this->fpdf->Text($x+33,$y+43,strtoupper("Y"));
				$this->fpdf->Text($x+33,$y+46,strtoupper("H"));
				$this->fpdf->Text($x+33,$y+49,strtoupper("P"));
				/*PHYISCAL END*/
				$this->fpdf->line($x+10,$y+15,$x+10,100);
				$this->fpdf->line($x+20,$y+15,$x+20,100);
				$this->fpdf->line($x+30,$y+15,$x+30,100);
				$this->fpdf->line($x+40,$y+15,$x+40,100);
				$this->fpdf->line($x+50,$y+15,$x+50,100);
				$this->fpdf->line($x+60,$y+15,$x+60,100);
				$this->fpdf->line($x+70,$y+10,$x+70,100);
				
				$this->fpdf->line($x+10,$y+50,$x+70,$y+50);
				$this->fpdf->line($x+10,$y+60,$x+70,$y+60);
				$this->fpdf->line($x+10,$y+70,$x+70,$y+70);
				/*cards*/
				$this->fpdf->line($x+100,$y+10,$x+100,100);
				$this->fpdf->line($x+150,$y+10,$x+150,100);
				$this->fpdf->Output(); 
			}
			
			
			
			public static function getReportMisCardsAsPerTLAle($monthSelected,$yearSelected,$status,$tL,$type )
			{
				
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				 $dateFrom = $currentYear.'-'.$newMonth.'-21';
				//echo '<br />';
				 $dateTo = $yearSelected.'-'.$monthSelected.'-20';
				//echo '<br />';
				//echo $leaderId;
				//echo '<br />';
				//echo $type;
				//exit;
				return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("TL",$tL)->where("ALE_NALE",$status)->where("file_source",$type)->get()->count();
			}
			
			
			public static function getReportMisCardsAsPerAgentAle($monthSelected,$yearSelected,$status,$agentId,$type )
			{
				
				$newMonth = $monthSelected-1;
				$currentYear = $yearSelected;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
				 $dateFrom = $currentYear.'-'.$newMonth.'-21';
				//echo '<br />';
				 $dateTo = $yearSelected.'-'.$monthSelected.'-20';
				//echo '<br />';
				//echo $leaderId;
				//echo '<br />';
				//echo $type;
				//exit;
				return MainMisReport::whereDate("submission_format",">=",$dateFrom)->whereDate("submission_format","<=",$dateTo)->where("employee_id",$agentId)->where("ALE_NALE",$status)->where("file_source",$type)->get()->count();
				
			}
			
			public static function getTimeFromJoining($empid)
			{
				$empId = Employee_details::where("id",$empid)->first()->emp_id;
				$empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empId)->first();
				if($empDOJObj != '')
				{
					$doj = $empDOJObj->attribute_values;
					if($doj == NULL || $doj == '')
					{
						return "Not Decleared";
					}
					else
					{
						$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 return  $returnData;
					}
					
				}
				else
				{
					return "Not Decleared";
				}
			}
			
			public static function totalSubmissions($leader,$type_sub_ageing,$status)
			{
				
		
					if($type_sub_ageing != 'all')
					{
						if($status == 'wip')
						{
							$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->where("approved_notapproved",7)->where("TL",$leader)->count();
						}
						else
						{
							$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->where("approved_notapproved",array(1,6))->where("TL",$leader)->count();
						}
					}
					else
					{
						if($status == 'wip')
						{
							$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",7)->where("TL",$leader)->count();
						}
						else
						{
							$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",array(1,6))->where("TL",$leader)->count();
						}
					}
					return $listOfMis;
			}
			
			public static function getAge($lastDate)
			{
						$date1 = date("Y-m-d",strtotime($lastDate));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 return  $returnData;
			}
			
			public static function getEndCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  EndJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("tl_id",$tlId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getEndCountTab($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				$date = date("d",strtotime($toDate));
				if($date == 31)
				{
					$toDate = date("Y-m-d",strtotime($toDate. ' - 1 days'));
				}
				if($date <= 20)
				{
				$closeData = date("M Y",strtotime($toDate));
				}
				else
				{
					
					$closeData = date("M Y",strtotime($toDate. ' + 1 months'));
				}
				return  EnbdTabResultProcess::where("close_month",$closeData)->where("tl_id",$tlId)->where("location","DUBAI")->where("show_on_page",1)->distinct('app_id')->count();
				
			}
			
			
			public static function getWipCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  WipJonusEnbdCardsSubmission::where("tl_id",$tlId)->where("show_status",1)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getWipCountTab($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  EnbdTabResultProcess::where("tl_id",$tlId)->where("status_id_bank",7)->where("show_on_page",1)->distinct('app_id')->count();
				
			}
			public static function getCancelCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  CancelJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("tl_id",$tlId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getCancelCountTab($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved",2)->where("file_source","Tab")->get()->count();
			}
			
			public static function getRejectedCount($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  RejectedJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("tl_id",$tlId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getRejectedCountTab($tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("TL",$tlId)->where("approved_notapproved",5)->where("file_source","Tab")->get()->count();
			}
			
			
			
			
			
			public static function getEndCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  EndJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("se_id",$SEId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getEndCountSETab($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				$date = date("d",strtotime($toDate));
				if($date == 31)
				{
					$toDate = date("Y-m-d",strtotime($toDate. ' - 1 days'));
				}
				if($date <= 20)
				{
					$closeData = date("M Y",strtotime($toDate));
				}
				else
				{
					
					$closeData = date("M Y",strtotime($toDate. ' + 1 months'));
				}
				return  EnbdTabResultProcess::where("close_month",$closeData)->where("se_id",$SEId)->where("location","DUBAI")->where("show_on_page",1)->distinct('app_id')->count();
				
			}
			
			
			public static function getWipCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  WipJonusEnbdCardsSubmission::where("se_id",$SEId)->where("show_status",1)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			public static function getWipCountSETab($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return  EnbdTabResultProcess::where("se_id",$SEId)->where("status_id_bank",7)->where("show_on_page",1)->distinct('app_id')->count();
			}
			public static function getCancelCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  CancelJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("se_id",$SEId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			public static function getCancelCountSETab($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved",2)->where("file_source","Tab")->get()->count();
			}
			public static function getRejectedCountSE($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				return  RejectedJonusEnbdCardsSubmission::whereDate("action_date",">=",$fromDate)->whereDate("action_date","<=",$toDate)->where("se_id",$SEId)->where("location","DUBAI")->distinct('app_id')->count();
				
			}
			
			public static function getRejectedCountSETab($SEId,$tlId,$fromDate,$toDate)
			{
				/* echo $tlId;
				echo '<br />';
				echo $fromDate;
				echo '<br />';
				echo $toDate;
				exit; */
				
				return MainMisReport::whereDate("submission_format",">=",$fromDate)->whereDate("submission_format","<=",$toDate)->where("employee_id",$SEId)->where("approved_notapproved",5)->where("file_source","Tab")->get()->count();
			}
			  public function showDemo()
				{
					/* $tlAnalysis = TLAnalysisPerformanceEnbd::where("tl_name","MANOJ")->whereIn("sales_count",[1,2,3])->get();
					$data = array();
					$index=0;
					foreach($tlAnalysis as $_analysis)
					{
						$data[$index]['Sales Time'] = $_analysis->sales_time;
						$data[$index]['Total Card Head Count'] = $_analysis->TL_Card_HC;
						$data[$index]['Total Cards'] = $_analysis->TC_by_Cards_Team;
						$data[$index]['Over all Total Cards'] = $_analysis->TC_Overall;
						$index++;
						
					} */
					$data = array();
					
					for($index=0;$index<7;$index++)
					{
						$date = date("Y-m-d");
					$prev_date = date('Y-m-d', strtotime($date .' -'.$index.' day'));
					$ProcessCandidate = DocumentCollectionDetails::whereDate("created_at",$prev_date)->get();
					$ProcessCandidateCount = count($ProcessCandidate);
					$ProcessCandidateOfferIncomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOffercomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardIncomplete = DocumentCollectionDetails::where("onboard_status",1)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardcomplete = DocumentCollectionDetails::where("onboard_status",2)->whereDate("created_at",$prev_date)->get()->count();
					if($ProcessCandidateOfferIncomplete != 0)
					{
					$ProcessCandidateOfferIncompletePercentage = round($ProcessCandidateOfferIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOfferIncompletePercentage = 0;
					}
					
					if($ProcessCandidateOffercomplete != 0)
					{
					$ProcessCandidateOffercompletePercentage = round($ProcessCandidateOffercomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOffercompletePercentage = 0;
					}
					
					if($ProcessCandidateOnboardIncomplete != 0)
					{
					$ProcessCandidateOnboardIncompletePercentage = round($ProcessCandidateOnboardIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
					$ProcessCandidateOnboardIncompletePercentage = 0;	
					}
					
					if($ProcessCandidateOnboardcomplete != 0)
					{
					$ProcessCandidateOnboardcompletePercentage = round($ProcessCandidateOnboardcomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOnboardcompletePercentage = 0;
					}
						$data[$index]['First Interview Date'] = $prev_date;
						//$data[$index]['Total Onboarding Candidate'] = $ProcessCandidateCount;
						$data[$index]['OfferLetter InComplete'] = $ProcessCandidateOfferIncomplete;
						$data[$index]['OfferLetter Complete'] = $ProcessCandidateOffercomplete;
						//$data[$index]['Onboarding InComplete'] = $ProcessCandidateOnboardIncomplete;
						//$data[$index]['Onboarding Complete'] = $ProcessCandidateOnboardcomplete;
					}
					return view("MIS/report",compact('data'));
				
					
				}
				
				 public function showDemo1()
				{
					/* $tlAnalysis = TLAnalysisPerformanceEnbd::where("tl_name","MANOJ")->whereIn("sales_count",[1,2,3])->get();
					$data = array();
					$index=0;
					foreach($tlAnalysis as $_analysis)
					{
						$data[$index]['Sales Time'] = $_analysis->sales_time;
						$data[$index]['Total Card Head Count'] = $_analysis->TL_Card_HC;
						$data[$index]['Total Cards'] = $_analysis->TC_by_Cards_Team;
						$data[$index]['Over all Total Cards'] = $_analysis->TC_Overall;
						$index++;
						
					} */
					$data = array();
					
					for($index=0;$index<7;$index++)
					{
						$date = date("Y-m-d");
					$prev_date = date('Y-m-d', strtotime($date .' -'.$index.' day'));
					$ProcessCandidate = DocumentCollectionDetails::whereDate("created_at",$prev_date)->get();
					$ProcessCandidateCount = count($ProcessCandidate);
					$ProcessCandidateOfferIncomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOffercomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardIncomplete = DocumentCollectionDetails::where("onboard_status",1)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardcomplete = DocumentCollectionDetails::where("onboard_status",2)->whereDate("created_at",$prev_date)->get()->count();
					if($ProcessCandidateOfferIncomplete != 0)
					{
					$ProcessCandidateOfferIncompletePercentage = round($ProcessCandidateOfferIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOfferIncompletePercentage = 0;
					}
					
					if($ProcessCandidateOffercomplete != 0)
					{
					$ProcessCandidateOffercompletePercentage = round($ProcessCandidateOffercomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOffercompletePercentage = 0;
					}
					
					if($ProcessCandidateOnboardIncomplete != 0)
					{
					$ProcessCandidateOnboardIncompletePercentage = round($ProcessCandidateOnboardIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
					$ProcessCandidateOnboardIncompletePercentage = 0;	
					}
					
					if($ProcessCandidateOnboardcomplete != 0)
					{
					$ProcessCandidateOnboardcompletePercentage = round($ProcessCandidateOnboardcomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOnboardcompletePercentage = 0;
					}
						$data[$index]['First Interview Date'] = $prev_date;
						$data[$index]['Total Onboarding Candidate'] = $ProcessCandidateCount;
						$data[$index]['OfferLetter InComplete'] = $ProcessCandidateOfferIncomplete;
						$data[$index]['OfferLetter Complete'] = $ProcessCandidateOffercomplete;
						$data[$index]['Onboarding InComplete'] = $ProcessCandidateOnboardIncomplete;
						$data[$index]['Onboarding Complete'] = $ProcessCandidateOnboardcomplete;
					}
					return view("MIS/report1",compact('data'));
				
					
				}
				
				public function showDemo2()
				{
					/* $tlAnalysis = TLAnalysisPerformanceEnbd::where("tl_name","MANOJ")->whereIn("sales_count",[1,2,3])->get();
					$data = array();
					$index=0;
					foreach($tlAnalysis as $_analysis)
					{
						$data[$index]['Sales Time'] = $_analysis->sales_time;
						$data[$index]['Total Card Head Count'] = $_analysis->TL_Card_HC;
						$data[$index]['Total Cards'] = $_analysis->TC_by_Cards_Team;
						$data[$index]['Over all Total Cards'] = $_analysis->TC_Overall;
						$index++;
						
					} */
					$data = array();
					
					for($index=0;$index<7;$index++)
					{
						$date = date("Y-m-d");
					$prev_date = date('Y-m-d', strtotime($date .' -'.$index.' day'));
					$ProcessCandidate = DocumentCollectionDetails::whereDate("created_at",$prev_date)->get();
					$ProcessCandidateCount = count($ProcessCandidate);
					$ProcessCandidateOfferIncomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOffercomplete = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardIncomplete = DocumentCollectionDetails::where("onboard_status",1)->where("offer_letter_onboarding_status",2)->whereDate("created_at",$prev_date)->get()->count();
					$ProcessCandidateOnboardcomplete = DocumentCollectionDetails::where("onboard_status",2)->whereDate("created_at",$prev_date)->get()->count();
					if($ProcessCandidateOfferIncomplete != 0)
					{
					$ProcessCandidateOfferIncompletePercentage = round($ProcessCandidateOfferIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOfferIncompletePercentage = 0;
					}
					
					if($ProcessCandidateOffercomplete != 0)
					{
					$ProcessCandidateOffercompletePercentage = round($ProcessCandidateOffercomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOffercompletePercentage = 0;
					}
					
					if($ProcessCandidateOnboardIncomplete != 0)
					{
					$ProcessCandidateOnboardIncompletePercentage = round($ProcessCandidateOnboardIncomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
					$ProcessCandidateOnboardIncompletePercentage = 0;	
					}
					
					if($ProcessCandidateOnboardcomplete != 0)
					{
					$ProcessCandidateOnboardcompletePercentage = round($ProcessCandidateOnboardcomplete/$ProcessCandidateCount,2)*100;
					}
					else
					{
						$ProcessCandidateOnboardcompletePercentage = 0;
					}
						$data[$index]['First Interview Date'] = $prev_date;
						$data[$index]['Total Onboarding Candidate'] = $ProcessCandidateCount;
						
						
					}
					return view("MIS/report2",compact('data'));
				
					
				}

}