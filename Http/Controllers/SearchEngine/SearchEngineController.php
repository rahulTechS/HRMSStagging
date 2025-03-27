<?php
namespace App\Http\Controllers\SearchEngine;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\EmpProcess\Emp_joining_data;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\MIS\WpCountries;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Recruiter\Designation;
use App\Models\Job\JobOpening;
use Session;
use App\Models\EmpProcess\EmpChangeLog;
use App\Models\Entry\Employee;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\JobFunction\JobFunction;
use App\Models\SEPayout\AgentPayoutByRange;
use App\Models\SearchEngine\PreAgentPayoutMashreqCard;
use App\Models\SearchEngine\SearchEngineLogs;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Recruiter\RecruiterCategory;

use App\Models\cronWork\CronRunLogs;

class SearchEngineController extends Controller
{
    public static function getAgentFullName($empid)
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
	 public static function getTLName($empid)
		{
			$agentData = PreAgentPayoutMashreqCard::where("employee_id",$empid)->orderby("id","DESC")->first(); 
			if($agentData != '')
			{
				if($agentData->tl_name=="Mohammed Sahir"){
					//echo $empid;exit;
					return "Sahir";
				}else{
				return $agentData->tl_name;
				}
			}
			else
			{
				return '';
			}
		}
	
	public function SearchEngine(Request $request){
		
		return view("SearchEngine/SearchEngine");
	   
	}
	Public function ListingSearchEngine(Request $request){
		
		return view("SearchEngine/listingSearchEngine");
	}
	
	public function SearchEngineFilterData(Request $request)
		{
			
			$rmslessthen=$request->input('rmslessthen');
			$submissionday=$request->input('submissionday');
			$Approvalratepercentage = $request->input('Approvalratepercentage');
			$rmsbookingparday = $request->input('rmsbookingparday');
			$RMsbookings = $request->input('RMsbookings');
			
			$request->session()->put('rmslessthen_filter_inner_list',$rmslessthen);
			$request->session()->put('submissionday_filter_inner_list',$submissionday);
			$request->session()->put('Approvalratepercentage_filter_inner_list',$Approvalratepercentage);
			$request->session()->put('rmsbookingparday_filter_inner_list',$rmsbookingparday);
			$request->session()->put('RMsbookings_filter_inner_list',$RMsbookings);
			
			
		}
		public function resetSearchEngineFilterData(Request $request){
			$request->session()->put('searchEng_data','');
			$request->session()->put('searchEng_caption','');
			
		
		
		
		$request->session()->put('search_keywordIdValue','');
		$request->session()->put('search_rest_agentdata','');		
		$request->session()->put('submission_type_search','');
		$request->session()->put('Card_type_search','');
		$request->session()->put('bureau_score_search','');
		$request->session()->put('bureau_segmentation_search','');
		$request->session()->put('mrs_score_search','');
		$request->session()->put('innter_engine_tl','');
		$request->session()->put('best_worse_engine_tl','');
		return redirect("SearchEngine");
		}	
		
		
		
		
			
	public function setKeywordXY(Request $request)
	{
		$keywordId =  $request->keywordId;
		$collectionCardType = array();
		$bureauSegmentationArray = array();
		if($keywordId == 6)
		{
			
			$collectionCardType = PreAgentPayoutMashreqCard::groupBy('card_type')
							->selectRaw('count(*) as total, card_type')
							->get();
			
			return view("SearchEngine/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray'));
		}
		else if($keywordId == 9)
		{
			
			$bureauSegmentationArray = PreAgentPayoutMashreqCard::groupBy('bureau_segmentation')
							->selectRaw('count(*) as total, bureau_segmentation')
							->get();
			
			return view("SearchEngine/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray'));
		} 
		else
		{
			return view("SearchEngine/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray'));
		}
		
	}	
	
	public function updateDataFromLogin()
	{
		
		/*
		*Cron Logs works
		*/
		$createCronLogs = new CronRunLogs();
		$createCronLogs->title = 'SearchEngine-UpdatefromLogin';
		$createCronLogs->save();
		
		/*
		*Cron Logs works
		*/
		$datas = PreAgentPayoutMashreqCard::whereNull("update_by_login")->get();
		
		foreach($datas as $data)
		{
			$appId = $data->application_id;
			$detailsLogin = MashreqLoginMIS::where("applicationid",$appId)->first();
			/* echo "<pre>";
			print_r($detailsLogin);
			exit; */
			if($detailsLogin !='')
			{
				$updateMe = PreAgentPayoutMashreqCard::find($data->id);
				$updateMe->card_type = $detailsLogin->card_type;
				$updateMe->bureau_score = $detailsLogin->bureau_score;
				$updateMe->bureau_segmentation = $detailsLogin->bureau_segmentation;
				$updateMe->mrs_score = $detailsLogin->mrs_score;
				$updateMe->update_by_login = 2;
				$updateMe->save();
			}
		
			
		}
		echo "done";
			exit;
	}
	
	function submitSearchEgn(Request $request)
	{
		$postParameters = $request->input();
		/* echo "<pre>";
		print_r($postParameters);
		exit;  */
		$detailsAgents = array();
		 $keywordIdValue = $postParameters['keywordIdValue'];
		if($keywordIdValue == 1)
		{
			$more_less = $postParameters['more_less'];
			$x_value = $postParameters['x_value'];
			if($more_less == 'more')
			{
				$morelessHtml = 'count(employee_id) >'.$x_value;
			}
			elseif($more_less == 'less')
			{
				$morelessHtml = 'count(employee_id) <'.$x_value;
			}
			elseif($more_less == 'moreequal')
			{
				$morelessHtml = 'count(employee_id) >='.$x_value;
			}
			elseif($more_less == 'lessequal')
			{
				$morelessHtml = 'count(employee_id) <='.$x_value;
			}
			else{
				$morelessHtml ='';
			}
			$y_value = $postParameters['y_value'];
			$submission_type = $postParameters['submission_type'];
			
			$currentDate = date("Y-m-d");
			$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
			$request->session()->put('y_value_search',$y_value);
			$request->session()->put('submission_type_search',$submission_type);
			if($submission_type == 'submissions')
			{
			/*  echo $startDate;
				echo "<br />";
				echo $currentDate;
				exit;  */
				$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->groupBy("employee_id")
				->havingRaw($morelessHtml)
				->get();
			}
			else
			{
				$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->where("booking_status",2)
				->groupBy("employee_id")
				->havingRaw($morelessHtml)
				->get();
			}
			$searchEng_caption = "RMs with ".$more_less." than ".$x_value." number of ".$submission_type." in last ".$y_value." days.";
			
		// print_r($detailsAgents);exit;	
			
		}
		else if($keywordIdValue == 2)
		{
			$startDate = date("Y")."-".date("m")."-01";
			$currentDate = date("Y-m-d");
			$totalCardsSubmissionEmployee = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->groupBy("employee_id")
				->get();
				
			$totalCardsBookedEmployee = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->where("booking_status",2)
				->groupBy("employee_id")
				->get();
			$employeeBookedLoginList = array();
			foreach($totalCardsSubmissionEmployee as $_submission)
			{
				$employeeBookedLoginList[$_submission->employee_id]['submissions'] = $_submission->total_card;
				$employeeBookedLoginList[$_submission->employee_id]['booked'] = 0;
			}			
			foreach($totalCardsBookedEmployee as $_booked)
			{
				$employeeBookedLoginList[$_booked->employee_id]['booked'] = $_booked->total_card;
			}	
				 /* echo "<pre>";
				print_r($postParameters);
				exit; */
				$more_less =$postParameters['more_less'];
				$approvalRate =$postParameters['x_value'];
			foreach($employeeBookedLoginList as $empId=>$empApproveRate)
			{
				/* echo "<pre>";
				print_r($empApproveRate);
				exit; */
				$submisions = $empApproveRate['submissions'];
				$booked = $empApproveRate['booked'];
				$approval = round(($booked/$submisions)*100);
				if($more_less == 'more')
				{
					if($approval > $approvalRate)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $booked;
						
					}
					
					
				}
				elseif($more_less == 'less')
				{
					if($approval < $approvalRate)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $booked;
					}
				}
				elseif($more_less == 'lessequal')
				{
					if($approval <= $approvalRate)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $booked;
					}
				}
				elseif($more_less == 'moreequal')
				{
					if($approval >= $approvalRate)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $booked;
					}
				}
				
			}
			$searchEng_caption = "Approval rate ".$more_less." than ".$approvalRate."%";
			
					
		}
		
		else if($keywordIdValue == 3)
		{
			  $more_less = $postParameters['more_less'];
			$x_value = $postParameters['x_value'];
			if($more_less == 'more')
			{
				$morelessHtml = 'count(employee_id) >'.$x_value;
			}
			elseif($more_less == 'less')
			{
				$morelessHtml = 'count(employee_id) <'.$x_value;
			}
			elseif($more_less == 'moreequal')
			{
				$morelessHtml = 'count(employee_id) >='.$x_value;
			}
			elseif($more_less == 'lessequal')
			{
				$morelessHtml = 'count(employee_id) <='.$x_value;
			}
			else{
				$morelessHtml ='';
			}
			
			$submission_type = $postParameters['submission_type'];
			
			$currentDate = date("Y-m-d");
			$startDate = date("Y")."-".date("m")."-01";
			$request->session()->put('submission_type_search',$submission_type);
			if($submission_type == 'submissions')
			{
				$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->groupBy("employee_id")
				->havingRaw($morelessHtml)
				->get();
				
			}
			else
			{
				$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->where("booking_status",2)
				->groupBy("employee_id")
				->havingRaw($morelessHtml)
				->get();
			}
			
		    $searchEng_caption = "RMs with ".$more_less."  than ".$x_value." ".$submission_type." as of today.";
			/* echo "<br />";
			echo "<pre>";
			print_r($detailsAgents);
			exit; */
			
		}
		else if($keywordIdValue == 6)
		{
			  $more_less = $postParameters['more_less'];
			$x_value = $postParameters['x_value'];
			
			
			$submission_type = $postParameters['submission_type'];
			$card_type = $postParameters['card_type'];
			
			$currentDate = date("Y-m-d");
			$startDate = date("Y")."-".date("m")."-01";
			$request->session()->put('submission_type_search',$submission_type);
			$request->session()->put('Card_type_search',implode(",",$card_type));
			if($submission_type == 'submissions')
			{
				$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->groupBy("employee_id")
				->get();
				
				$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->whereIn("card_type",$card_type)
				->groupBy("employee_id")
				->get();
				$submissionMix = array();
				foreach($submissionDONE as $sub)
				{
					$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
					$submissionMix[$sub->employee_id]['total_submission_cardtype'] = 0;
				}
				
				foreach($submissionDONECardType as $cardtype)
				{
					
					$submissionMix[$cardtype->employee_id]['total_submission_cardtype'] = $cardtype->total_card;
				}
				
			}
			else
			{
				$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->where("booking_status",2)
				->groupBy("employee_id")
				->get();
				
				$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->whereIn("card_type",$card_type)
				->where("booking_status",2)
				->groupBy("employee_id")
				->get();
				$submissionMix = array();
				foreach($submissionDONE as $sub)
				{
					$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
					$submissionMix[$sub->employee_id]['total_submission_cardtype'] = 0;
				}
				
				foreach($submissionDONECardType as $cardtype)
				{
					
					$submissionMix[$cardtype->employee_id]['total_submission_cardtype'] = $cardtype->total_card;
				}
			}
			
		   foreach($submissionMix as $empId=>$empApproveRate)
			{
				/* echo "<pre>";
				print_r($empApproveRate);
				exit; */
				$submisions = $empApproveRate['total_submission'];
				$total_submission_cardtype = $empApproveRate['total_submission_cardtype'];
				$approval = round(($total_submission_cardtype/$submisions)*100);
				if($more_less == 'more')
				{
					if($approval > $x_value)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
					}
					
					
				}
				elseif($more_less == 'moreequal')
				{
					if($approval >= $x_value)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
					}
					
					
				}
				elseif($more_less == 'less')
				{
					if($approval < $x_value)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
					}
				}
				elseif($more_less == 'lessequal')
				{
					if($approval <= $x_value)
					{
						$detailsAgents[$empId]['total_submission'] = $submisions;
						$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
					}
				}
			}
			$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in ".implode(",",$card_type).".";
			
					
		}
		else if($keywordIdValue == 7)
		{
			
			$more_less = $postParameters['more_less'];
			$more_less1 = $postParameters['more_less1'];
			$x_value = $postParameters['x_value'];
			$xyz_value = $postParameters['xyz_value'];
			$submission_type = $postParameters['submission_type'];
			
			
			
			if($more_less1 == 'more')
			{
				$moreless1Html = 'bureau_score >'.$xyz_value;
			}
			elseif($more_less1 == 'less')
			{
				$moreless1Html = 'bureau_score <'.$xyz_value;
			}
			elseif($more_less1 == 'moreequal')
			{
				$moreless1Html = 'bureau_score >='.$xyz_value;
			}
			elseif($more_less1 == 'lessequal')
			{
				$moreless1Html = 'bureau_score <='.$xyz_value;
			}
			
			
			$currentDate = date("Y-m-d");
			$startDate = date("Y")."-".date("m")."-01";
			$request->session()->put('submission_type_search',$submission_type);
			$request->session()->put('bureau_score_search',$moreless1Html);
			if($submission_type == 'submissions')
			{
				$detailsAgentsSubmissionTotal = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->groupBy("employee_id")
				->get();
				
				$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->whereRaw($moreless1Html)
				->groupBy("employee_id")
				->get();
				
				
				
				
				
				
				$submissionMix = array();
				foreach($detailsAgentsSubmissionTotal as $sub)
				{
					$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
					$submissionMix[$sub->employee_id]['total_submission_B'] = 0;
				}
				
				foreach($detailsAgentsSubmissionTotalB as $subb)
				{
					
					$submissionMix[$subb->employee_id]['total_submission_B'] = $subb->total_card;
				}
			}
			
			else
			{
				$detailsAgentsSubmissionTotal = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->where("booking_status",2)
				->groupBy("employee_id")
				->get();
				
				$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
				->whereBetween("submission_date",[$startDate,$currentDate])
				->whereRaw($moreless1Html)
				->where("booking_status",2)
				->groupBy("employee_id")
				->get();
				
				
				
				
				
				
				$submissionMix = array();
				foreach($detailsAgentsSubmissionTotal as $sub)
				{
					$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
					$submissionMix[$sub->employee_id]['total_submission_B'] = 0;
				}
				
				foreach($detailsAgentsSubmissionTotalB as $subb)
				{
					
					$submissionMix[$subb->employee_id]['total_submission_B'] = $subb->total_card;
				}
			}
			
			   foreach($submissionMix as $empId=>$empApproveRate)
				{
					/* echo "<pre>";
					print_r($empApproveRate);
					exit; */
					$submisions = $empApproveRate['total_submission'];
					$total_submission_b = $empApproveRate['total_submission_B'];
					$approval = round(($total_submission_b/$submisions)*100);
					if($more_less == 'more')
					{
						if($approval > $x_value)
						{
							$detailsAgents[$empId]['total_submission'] = $submisions;
							$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
						}
						
						
					}
					elseif($more_less == 'moreequal')
					{
						if($approval >= $x_value)
						{
							$detailsAgents[$empId]['total_submission'] = $submisions;
							$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
						}
						
						
					}
					elseif($more_less == 'less')
					{
						if($approval < $x_value)
						{
							$detailsAgents[$empId]['total_submission'] = $submisions;
							$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
						}
					}
					elseif($more_less == 'lessequal')
					{
						if($approval <= $x_value)
						{
							$detailsAgents[$empId]['total_submission'] = $submisions;
							$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
						}
					}
				}
				$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with bureau score ".$more_less1." than ".$xyz_value.".";
			
			
			}
			
			else if($keywordIdValue == 8)
			{
				$more_less = $postParameters['more_less'];
				$more_less1 = $postParameters['more_less1'];
				$x_value = $postParameters['x_value'];
				$xyz_value = $postParameters['xyz_value'];
				$submission_type = $postParameters['submission_type'];
				
				
				
				if($more_less1 == 'more')
				{
					$moreless1Html = 'mrs_score >'.$xyz_value;
				}
				elseif($more_less1 == 'moreequal')
				{
					$moreless1Html = 'mrs_score >='.$xyz_value;
				}
				elseif($more_less1 == 'less')
				{
					$moreless1Html = 'mrs_score <'.$xyz_value;
				}
				elseif($more_less1 == 'lessequal')
				{
					$moreless1Html = 'mrs_score <='.$xyz_value;
				}
				
				$request->session()->put('submission_type_search',$submission_type);
				$request->session()->put('mrs_score_search',$moreless1Html);
				
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				
				if($submission_type == 'submissions')
				{
					$detailsAgentsSubmissionTotal = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$startDate,$currentDate])
					->groupBy("employee_id")
					->get();
					
					$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$startDate,$currentDate])
					->whereRaw($moreless1Html)
					->groupBy("employee_id")
					->get();
					
					
					
					
					
					
					$submissionMix = array();
					foreach($detailsAgentsSubmissionTotal as $sub)
					{
						$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
						$submissionMix[$sub->employee_id]['total_submission_B'] = 0;
					}
					
					foreach($detailsAgentsSubmissionTotalB as $subb)
					{
						
						$submissionMix[$subb->employee_id]['total_submission_B'] = $subb->total_card;
					}
				}
				
				else
				{
					$detailsAgentsSubmissionTotal = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$startDate,$currentDate])
					->where("booking_status",2)
					->groupBy("employee_id")
					->get();
					
					$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$startDate,$currentDate])
					->whereRaw($moreless1Html)
					->where("booking_status",2)
					->groupBy("employee_id")
					->get();
					
					
					
					
					
					
					$submissionMix = array();
					foreach($detailsAgentsSubmissionTotal as $sub)
					{
						$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
						$submissionMix[$sub->employee_id]['total_submission_B'] = 0;
					}
					
					foreach($detailsAgentsSubmissionTotalB as $subb)
					{
						
						$submissionMix[$subb->employee_id]['total_submission_B'] = @$subb->total_card;
					}
				}
				
				   foreach($submissionMix as $empId=>$empApproveRate)
					{
						/* echo "<pre>";
						print_r($empApproveRate);
						exit; */
						$submisions = $empApproveRate['total_submission'];
						$total_submission_b = $empApproveRate['total_submission_B'];
						$approval = round(($total_submission_b/$submisions)*100);
						if($more_less == 'more')
						{
							if($approval > $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
							}
							
							
						}
						elseif($more_less == 'moreequal')
						{
							if($approval >= $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
							}
							
							
						}
						elseif($more_less == 'less')
						{
							if($approval < $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
							}
						}
						elseif($more_less == 'lessequal')
						{
							if($approval <= $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_b;
							}
						}
					}
					$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with MRS Score ".$more_less1." than ".$xyz_value.".";
				
				
				}
				
				else if($keywordIdValue == 9)
				{
					/* echo "<pre>";
					print_r($postParameters);
					exit; */
					  $more_less = $postParameters['more_less'];
					$x_value = $postParameters['x_value'];
					
					
					$submission_type = $postParameters['submission_type'];
					$bureau_segmentation = $postParameters['bureau_segmentation'];
					
					$currentDate = date("Y-m-d");
					$startDate = date("Y")."-".date("m")."-01";
					$request->session()->put('submission_type_search',$submission_type);
				    $request->session()->put('bureau_segmentation_search',implode(",",$bureau_segmentation));
					if($submission_type == 'submissions')
					{
						$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
						->whereBetween("submission_date",[$startDate,$currentDate])
						->groupBy("employee_id")
						->get();
						
						$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
						->whereBetween("submission_date",[$startDate,$currentDate])
						->whereIn("bureau_segmentation",$bureau_segmentation)
						->groupBy("employee_id")
						->get();
						$submissionMix = array();
						foreach($submissionDONE as $sub)
						{
							$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
							$submissionMix[$sub->employee_id]['total_submission_cardtype'] = 0;
						}
						
						foreach($submissionDONECardType as $cardtype)
						{
							
							$submissionMix[$cardtype->employee_id]['total_submission_cardtype'] = $cardtype->total_card;
						}
						
					}
					else
					{
						$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
						->whereBetween("submission_date",[$startDate,$currentDate])
						->where("booking_status",2)
						->groupBy("employee_id")
						->get();
						
						$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
						->whereBetween("submission_date",[$startDate,$currentDate])
						->whereIn("bureau_segmentation",$bureau_segmentation)
						->where("booking_status",2)
						->groupBy("employee_id")
						->get();
						$submissionMix = array();
						foreach($submissionDONE as $sub)
						{
							$submissionMix[$sub->employee_id]['total_submission'] = $sub->total_card;
							$submissionMix[$sub->employee_id]['total_submission_cardtype'] = 0;
						}
						
						foreach($submissionDONECardType as $cardtype)
						{
							
							$submissionMix[$cardtype->employee_id]['total_submission_cardtype'] = $cardtype->total_card;
						}
					}
					
				   foreach($submissionMix as $empId=>$empApproveRate)
					{
						/* echo "<pre>";
						print_r($empApproveRate);
						exit; */
						$submisions = $empApproveRate['total_submission'];
						$total_submission_cardtype = $empApproveRate['total_submission_cardtype'];
						$approval = round(($total_submission_cardtype/$submisions)*100);
						if($more_less == 'more')
						{
							if($approval > $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
							}
							
							
						}
						elseif($more_less == 'moreequal')
						{
							if($approval >= $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
							}
							
							
						}
						elseif($more_less == 'less')
						{
							if($approval < $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
							}
						}
						elseif($more_less == 'lessequal')
						{
							if($approval <= $x_value)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = $total_submission_cardtype;
							}
						}
					}
					$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with ".implode(",",$bureau_segmentation).".";
					
						
				}
				else if($keywordIdValue ==10)
				{
				$best_worse = $postParameters['best_worse'];
				$submission_type = $postParameters['submission_type'];
				
				
				$request->session()->put('best_worse_engine_tl',$best_worse);
				$request->session()->put('submission_type_search',$submission_type);
				
				$currentDate = date("Y-m-d");
				$currentstartDate = date("Y")."-".date("m")."-01";
				
				$lasttoDate =date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
				$lastfromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';;
				
				if($submission_type == 'submissions')
				{
					$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$currentstartDate,$currentDate])
					
					->groupBy("employee_id")
					->get();
					//print_r($detailsAgentsSubmissionTotalCurrent);exit;
					foreach($detailsAgentsSubmissionTotalCurrent as $empApproveRate)
					{
						/* echo "<pre>";
						print_r($empApproveRate);
						exit; */
						$empId=$empApproveRate->employee_id;
						$submisions = $empApproveRate->total_card;
						$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
					->where("employee_id",$empId)->get()->count();
					//print_r($detailsAgentsSubmissionTotalLastmonth);exit;
						if($best_worse == 'best')
						{
							if($submisions>$detailsAgentsSubmissionTotalLastmonth)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = '';
								
							}
							
							
						}
						else
						{
							if($submisions<=$detailsAgentsSubmissionTotalLastmonth)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = '';
							}
						}
					}
				}
				
				else
				{
					$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
					->whereBetween("submission_date",[$currentstartDate,$currentDate])
					->where("booking_status",2)
					->groupBy("employee_id")
					->get();
					foreach($detailsAgentsSubmissionTotalCurrent as $empApproveRate)
					{
						/* echo "<pre>";
						print_r($empApproveRate);
						exit; */
						$empId=$empApproveRate->employee_id;
						$submisions = $empApproveRate->total_card;
						$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
					->where("employee_id",$empId)->where("booking_status",2)->get()->count();
						if($best_worse == 'best')
						{
							if($submisions>$detailsAgentsSubmissionTotalLastmonth)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = '';
								
							}
							
							
						}
						else
						{
							if($submisions<=$detailsAgentsSubmissionTotalLastmonth)
							{
								$detailsAgents[$empId]['total_submission'] = $submisions;
								$detailsAgents[$empId]['total_submission_B'] = '';
							}
						}
					}
					
				}
				
				 //print_r($detailsAgents);exit;  
					$searchEng_caption = $best_worse." agent ".$submission_type." from last month as of today.";
				
				
				}
				
				else if($keywordIdValue == 4)
				{
					echo "<pre>";
					print_r($postParameters);
					exit;
				}
				
			
			$request->session()->put('searchEng_data',$detailsAgents);
			$request->session()->put('searchEng_caption',$searchEng_caption);
			
		
		
		
		$request->session()->put('search_keywordIdValue',$keywordIdValue);	
		$request->session()->put('innter_engine_tl','');
		$request->session()->put('innter_engine_processor','');
		$request->session()->put('innter_engine_rangeId','');
		
		/*
		*making log
		*/
		$engLogs = new SearchEngineLogs();
		$engLogs->caption = $searchEng_caption;
		$engLogs->user_id = $request->session()->get('EmployeeId');
		$engLogs->search_id = $keywordIdValue;
		$engLogs->look_date = date("Y-m-d");
		$engLogs->save();
		/*
		*making log
		*/
		
		
		//echo $more_less;exit;
		$restagent='';
		if($keywordIdValue == 1 || $keywordIdValue ==3 || $keywordIdValue ==4 || $keywordIdValue == 5 || $keywordIdValue == 6 || $keywordIdValue == 7 || $keywordIdValue == 8 || $keywordIdValue == 9)
			{
					if(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'submissions'){
						//echo "h1";
					$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])
							->groupBy("employee_id")
							->get();	
					}
					elseif(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'bookings'){
						//echo "h2";
					$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
						->whereBetween("submission_date",[$startDate,$currentDate])
						->where("booking_status",2)
						->groupBy("employee_id")
						->get();
					}
			}
			elseif($keywordIdValue == 2){
						$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])
							->groupBy("employee_id")
							->get();
			}					
			$restdataarrya=array();
					$agentempid=array();
					if($restagent!=''){
					foreach($restagent as $ragent){
						$restdataarrya[]=$ragent->employee_id;
					}
					
					
						
							$tlagentdata=Employee_details::whereNotIn('emp_id',$restdataarrya)->where("job_function",2)->where("dept_id",36)->where("offline_status",1)->get();
							if($tlagentdata!=''){
								
								foreach($tlagentdata as $tlids){
								$agentempid[]=$tlids->emp_id;	
								}
							}
							
						
					
					}
					//print_r($agentempid);exit;
			$request->session()->put('search_rest_agentdata',$agentempid);			
		return redirect('SearchEngine');
	}
	
	public function loadResultSEListing(Request $request)
	{
		$employeeLists = array();
		$agentdata=array();
		$searchEng_caption = "No Search Keyword Selected.";
		$search_keywordIdValue = '';
		if(@$request->session()->get('searchEng_data') != '')
		{
			$employeeLists = $request->session()->get('searchEng_data');
		}
		if(@$request->session()->get('search_rest_agentdata') != '')
		{
			$agentdata = $request->session()->get('search_rest_agentdata');
		}
		if(@$request->session()->get('searchEng_caption') != '')
		{
			$searchEng_caption = $request->session()->get('searchEng_caption');
		}
		if(@$request->session()->get('search_keywordIdValue') != '')
		{
			$search_keywordIdValue = $request->session()->get('search_keywordIdValue');
		}
		//print_r($employeeLists);
		/*
		*get TL List
		*/
		$innter_engine_rangeId = array();
		if(@$request->session()->get('innter_engine_rangeId') != '')
		{
			$innter_engine_rangeId = $request->session()->get('innter_engine_rangeId');
			/* echo "<pre>";
			print_r($innter_engine_rangeId);
			exit;  */
		}
		$empIdList = array();
		$tl_details = array();
		if($search_keywordIdValue == 2 || $search_keywordIdValue ==6 || $search_keywordIdValue ==7 || $search_keywordIdValue == 8 || $search_keywordIdValue == 9 || $search_keywordIdValue == 10)
		{
			$empIdList = array();
			//print_r($employeeLists);exit;
			foreach($employeeLists as $emp_id=>$emp)
			{
				
				//print_r($emp_id);exit;
				if(count($innter_engine_rangeId) >0)
				{
					//echo "h1";exit;
					$range_idEmp = PreAgentPayoutMashreqCard::where("employee_id",$emp_id)->orderby("sort_order","DESC")->first()->range_id;
					if(in_array($range_idEmp,$innter_engine_rangeId))
					{
					$empIdList[]= $emp_id;
					}
				}
				else
				{
					//echo "h2";exit;
					$empIdList[]= $emp_id;
				}
			}
			
		}
		else
		{
			foreach($employeeLists as $emp)
			{
				
				if(count($innter_engine_rangeId) >0)
				{
					
						$range_idEmp = PreAgentPayoutMashreqCard::where("employee_id",$emp->employee_id)->orderby("sort_order","DESC")->first()->range_id;
					
					if(in_array($range_idEmp,$innter_engine_rangeId))
					{
						$empIdList[]= $emp->employee_id;
					}
				}
				else
				{
						$empIdList[]= $emp->employee_id;
				}
			}
		}
		if(count($employeeLists) >0)
		{
		$tl_details = PreAgentPayoutMashreqCard::selectRaw("tl_name , count(tl_name) as total_TL")
						
						->whereIn("employee_id",$empIdList)
						
						->groupBy("tl_name")
						->get();
		}
		/*
		*get TL List
		*/
		
		/*
		*get Range Id List
		*/
		
				$rangeIds =		WorkTimeRange::get();
		/*
		*get Range Id List
		*/
		
		return view("SearchEngine/loadResultSEListing",compact('agentdata','employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));
	}
	
	public static function getVintageData($empid = NULL)
	   {
		  
			
			$getVintageData=Employee_attribute::where("emp_id",$empid)->where("attribute_code","DOJ")->first();
			
			
				if($getVintageData != '')
				{
				$doj = $getVintageData->attribute_values;	
				
				if($doj !=''){
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
						 //echo   $returnData;


					 return $returnData;
				}
				else{
					return "--";
				}
				}
				else
				{
					return "--";
				}
			
   
	   }
	   
	  public static function getRangeId($empid = NULL)
	  {
		 
			 $getVintageData=PreAgentPayoutMashreqCard::where("employee_id",$empid)->orderby("id","DESC")->first();
				if( $getVintageData != '')
				{
				   return $getVintageData->range_id;
				}
				else
				{
					return "--";
				}
	  }	  


	public static function getrecruiterName($empid = NULL)
	{
		$recruiter = Employee_details::where("emp_id",$empid)->first()->recruiter;
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
	
	public static function getrecruiterCat($empid = NULL)
	{
		$recruiterdata = Employee_details::where("emp_id",$empid)->first();
		if($recruiterdata!=''){
			$recruiter=$recruiterdata->recruiter;
		
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
		}
		else
		{
			return ''; 
		}
	}	

	public function exportEmpSearchEngineReport(Request $request)
		{
			
			$searchEng_caption = $request->session()->get('searchEng_caption');
			$search_keywordIdValue = $request->session()->get('search_keywordIdValue');	
			$employeeLists = $request->session()->get('searchEng_data');
			/* echo "<pre>";
			print_r($employeeLists);
			exit; */
		
			$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Search_Engine_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'Agent List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent Id'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Processor'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('TL Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				if(($search_keywordIdValue == 1 ||$search_keywordIdValue == 3 ||$search_keywordIdValue == 6 ||$search_keywordIdValue == 7 ||$search_keywordIdValue == 8 ||$search_keywordIdValue == 9) && Session::get('submission_type_search') == 'submissions')
				{	
					$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Submission'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}
				elseif(($search_keywordIdValue == 1 ||$search_keywordIdValue == 3 ||$search_keywordIdValue == 6 ||$search_keywordIdValue == 7 ||$search_keywordIdValue == 8 ||$search_keywordIdValue == 9) && Session::get('submission_type_search') == 'bookings')
				{	
					$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Bookings'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}
				elseif($search_keywordIdValue == 2)
				{
					$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Submissions'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}	
				else
				{	
					$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Cards'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}
			
				
				
				if($search_keywordIdValue == 2)
				{
					$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Bookings'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}					
				elseif($search_keywordIdValue == 6 && Session::get('submission_type_search') == 'submissions')
				{				
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Submissions('.Session::get('Card_type_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				}				
				elseif($search_keywordIdValue == 6 && Session::get('submission_type_search') == 'bookings')
				{
				
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Bookings('.Session::get('Card_type_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				}
				elseif($search_keywordIdValue == 7 && Session::get('submission_type_search') == 'submissions')
				{
					$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Submissions('.Session::get('bureau_score_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				}
				elseif($search_keywordIdValue == 7 && Session::get('submission_type_search') == 'bookings')
				{	
					$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Bookings('.Session::get('bureau_score_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
								
				
				}
				elseif($search_keywordIdValue == 8 && Session::get('submission_type_search') == 'submissions')
				{
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Submissions('.Session::get('mrs_score_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
										
				
				}				
				elseif($search_keywordIdValue == 8 && Session::get('submission_type_search') == 'bookings')
				{				
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Bookings('.Session::get('mrs_score_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				}				 
				elseif($search_keywordIdValue == 9 && Session::get('submission_type_search') == 'submissions')
				{
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Submissions('.Session::get('bureau_segmentation_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				 
				}
				elseif($search_keywordIdValue == 9 && Session::get('submission_type_search') == 'bookings')
				{
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Bookings('.Session::get('bureau_segmentation_search').')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
									
				
				}				 
				elseif($search_keywordIdValue == 8 || $search_keywordIdValue == 9)
				{
					$sheet->setCellValue('G'.$indexCounter, strtoupper('Total Cards(As Per Keywords)'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				}
			
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Vintage'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$processor=array();
			$processor['Ajay'] = 'Mahwish';
			$processor['Mujahid'] = 'Mahwish';
			$processor['Shahnawaz'] = 'Mahwish';
			$processor['Zubair'] = 'Umar';
			$processor['Arsalan'] = 'Umar';
			$processor['Mohsin'] = 'Arsalan';
			$processor['Sahir'] = 'Umar';
			
			$sn = 1;
			foreach ($selectedId as $sid) {
			    $indexCounter++; 	
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				if($search_keywordIdValue == 2 || $search_keywordIdValue ==6 || $search_keywordIdValue ==7 || $search_keywordIdValue == 8 || $search_keywordIdValue == 9)
				{
					$sheet->setCellValue('B'.$indexCounter, SearchEngineController::getAgentFullName($sid))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $sid)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$tlName = SearchEngineController::getTLName($sid);
					$sheet->setCellValue('D'.$indexCounter, $processor[$tlName])->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
					$sheet->setCellValue('E'.$indexCounter, SearchEngineController::getTLName($sid))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
					foreach($employeeLists as $emp_id=>$emp)
					{
						if($emp_id == $sid)
						{
							$sheet->setCellValue('F'.$indexCounter, $emp['total_submission'])->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
							$sheet->setCellValue('G'.$indexCounter, $emp['total_submission_B'])->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
						}
					}
					/* <td>{{$emp['total_submission']}}</td>
					<td>{{$emp['total_submission_B']}}</td>
					<td><?php echo App\Http\Controllers\SearchEngine\SearchEngineController::getVintageData($emp_id);?></td>
					<td><?php echo App\Http\Controllers\SearchEngine\SearchEngineController::getrecruiterName($emp_id);?></td>
					<td><?php echo App\Http\Controllers\SearchEngine\SearchEngineController::getrecruiterCat($emp_id);?></td> */
					
				}	

				else
				{	
				
					$sheet->setCellValue('B'.$indexCounter, SearchEngineController::getAgentFullName($sid))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $sid)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$tlName = SearchEngineController::getTLName($sid);
					$sheet->setCellValue('D'.$indexCounter, $processor[$tlName])->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
					$sheet->setCellValue('E'.$indexCounter, SearchEngineController::getTLName($sid))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$data = array();
			
					foreach($employeeLists as $emp)
					{
						if($emp->employee_id == $sid)
						{
							$sheet->setCellValue('F'.$indexCounter, $emp->total_card)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
						}
					}
					
                }				
				
				
				
				
				
				
				$sheet->setCellValue('H'.$indexCounter, SearchEngineController::getVintageData($sid))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, SearchEngineController::getrecruiterName($sid))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, SearchEngineController::getrecruiterCat($sid))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'J'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
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
		
		
	public function SearchStartEngine(Request $request)
	{
		$parametersInput = $request->input();
		
		$tl_name = @$parametersInput['tl_name'];
		$processor = @$parametersInput['processor'];
		$range_id = @$parametersInput['range_id'];
		
		if($tl_name != '')
		{
			$request->session()->put('innter_engine_tl',$tl_name);
		}
		else
		{
			$request->session()->put('innter_engine_tl','');
		}
		
		
		if($processor != '')
		{
			$request->session()->put('innter_engine_processor',$processor);
		}
		else
		{
			$request->session()->put('innter_engine_processor','');
		}
		
		
		if($range_id != '')
		{
			$request->session()->put('innter_engine_rangeId',$range_id);
		}
		else
		{
			$request->session()->put('innter_engine_rangeId','');
		}
		
		return redirect("SearchEngine");
	}
	
	public function ResetStartEngine(Request $request)
	{
		$request->session()->put('innter_engine_tl','');
		$request->session()->put('innter_engine_processor','');
		$request->session()->put('innter_engine_rangeId','');
		return redirect("SearchEngine");
	}
	
	public function detailsSearchResult(Request $request)
	{
		 $emp_id = $request->emp_id;
		
		  $search_keywordIdValue = $request->search_keywordIdValue;
		
		
			if($search_keywordIdValue == 1)
			{
				$y_value = $request->session()->get('y_value_search');
				$submission_type = $request->session()->get('submission_type_search');
				$currentDate = date("Y-m-d");
				$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->get();
				}
				else
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->get();
				}
				
			}	
			else if($search_keywordIdValue == 2)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->get();
			}
			else if($search_keywordIdValue == 3)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$submission_type = $request->session()->get('submission_type_search');
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->get();
				}
				else
				{
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->get();	
				}
				
			}
			
			else if($search_keywordIdValue == 6)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$submission_type = $request->session()->get('submission_type_search');
				$Card_type_search =	$request->session()->get('Card_type_search');
				$Card_type_searchArr = explode(",",$Card_type_search);
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->whereIn("card_type",$Card_type_searchArr)->get();
				}
				else
				{
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->whereIn("card_type",$Card_type_searchArr)->get();	
				}
				
			}
			else if($search_keywordIdValue == 7)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$submission_type = $request->session()->get('submission_type_search');
				$moreless1Html = $request->session()->get('bureau_score_search');
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->whereRaw($moreless1Html)->get();
				}
				else
				{
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->whereRaw($moreless1Html)->get();	
				}
				
			}
			else if($search_keywordIdValue == 8)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$submission_type = $request->session()->get('submission_type_search');
				$moreless1Html = $request->session()->get('mrs_score_search');
			
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->whereRaw($moreless1Html)->get();
				}
				else
				{
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->whereRaw($moreless1Html)->get();	
				}
			}
			else if($search_keywordIdValue == 9)
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$submission_type = $request->session()->get('submission_type_search');
				$bureau_segmentation_search = $request->session()->get('bureau_segmentation_search');
				$bureau_segmentation_searchArr = explode(",",$bureau_segmentation_search);
				if($submission_type == 'bookings')
				{
					$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("booking_status",2)->where("employee_id",$emp_id)->whereIn("bureau_segmentation",$bureau_segmentation_searchArr)->get();
				}
				else
				{
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->whereIn("bureau_segmentation",$bureau_segmentation_searchArr)->get();	
				}
			}
			else
			{
				$currentDate = date("Y-m-d");
				$startDate = date("Y")."-".date("m")."-01";
				$detailsSubmissions = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])->where("employee_id",$emp_id)->get();
			}
			
			return view("SearchEngine/detailsSearchResult",compact('detailsSubmissions'));
	}
	
	public static function getCustomerName($appId = null)
	{
		$details = MashreqLoginMIS::where("applicationid",$appId)->first();
		if($details != '')
		{
			return $details->customer_name;
		}
		else
		{
			return "--";
		}
	}
	
	public static function getBookingSearch1($empId=NULL)
		{
					$y_value = Session::get('y_value_search');
					$currentDate = date("Y-m-d");
					$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
					return $detailsAgents = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])
						->where("employee_id",$empId)
						->where("booking_status",2)
						->get()->count();
		}



	public static function getSubmissionSearch1($empId=NULL)
		{
					$y_value = Session::get('y_value_search');
					$currentDate = date("Y-m-d");
					$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
					return $detailsAgents = PreAgentPayoutMashreqCard::whereBetween("submission_date",[$startDate,$currentDate])
						->where("employee_id",$empId)
						->get()->count();
		}
}