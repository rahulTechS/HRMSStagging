<?php
namespace App\Http\Controllers\SearchEngineWidget;

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
use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Recruiter\RecruiterCategory;

use App\Models\cronWork\CronRunLogs;
use App\Models\SearchEngineWidget\SearchEngineRules;
use App\Models\SearchEngineWidget\SearchResultWidget;
use App\Models\SearchEngine\PreAgentPayoutCBDCard;
use App\Models\SearchEngine\PreAgentPayoutEIBCard;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Bank\EIB\EibBankMis;

class SearchEngineWidgetController extends Controller
{
    
	
	public function SearchEngineWidget(Request $request){
		
		//$rulesList=SearchEngineRules::
		$user=$request->session()->get('EmployeeId');
		$widgetlist=SearchResultWidget::where("user_id",$user)->get();
		return view("SearchEngineWidget/SearchEngineWidget" ,compact('widgetlist'));
	   
	   
	}
	public function loadRulesListingData($deptId,$tabname){
		$rulesList=SearchEngineRules::where("dept_id",$deptId)->get();
		return view("SearchEngineWidget/rulesList",compact('rulesList','deptId','tabname'));
	}
	Public function ListingSearchEngine(Request $request){
		
		return view("SearchEngine/listingSearchEngine");
	}
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
				return $agentData->tl_name;
			}
			else
			{
				return '';
			}
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
		$request->session()->put('submission_type_search','');
		$request->session()->put('Card_type_search','');
		$request->session()->put('bureau_score_search','');
		$request->session()->put('bureau_segmentation_search','');
		$request->session()->put('mrs_score_search','');
		$request->session()->put('innter_engine_tl','');
		$request->session()->put('best_worse_engine_tl','');
		return redirect("SearchEngine");
		}	
		
		
		
		
			
	public function setKeywordXYWidget(Request $request)
	{
		$keywordId =  $request->keywordId;
		$dept_id=$request->dept_id;
		$tabname=$request->tabname;//exit;
		
		$collectionCardType = array();
		$bureauSegmentationArray = array();
		if($keywordId == 6)
		{
			
			$collectionCardType = PreAgentPayoutMashreqCard::groupBy('card_type')
							->selectRaw('count(*) as total, card_type')
							->get();
			
			return view("SearchEngineWidget/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray','dept_id','tabname'));
		}
		else if($keywordId == 9)
		{
			
			$bureauSegmentationArray = PreAgentPayoutMashreqCard::groupBy('bureau_segmentation')
							->selectRaw('count(*) as total, bureau_segmentation')
							->get();
			
			return view("SearchEngineWidget/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray','dept_id'));
		} 
		else
		{
			return view("SearchEngineWidget/setKeywordXY",compact('keywordId','collectionCardType','bureauSegmentationArray','dept_id'));
		}
		
	}	
	
	
	
	function submitSearchEgnWidgetData(Request $request)
	{
		$postParameters = $request->input();
		 /*echo "<pre>";
		print_r($postParameters);
		exit;*/
		if(isset($postParameters['more_less'])){
		$more_less=	$postParameters['more_less'];
		}else{
		$more_less='';	
		}
		if(isset($postParameters['x_value'])){
		$x_value=	$postParameters['x_value'];
		}
		else{
			$x_value='';
		}
		if(isset($postParameters['submission_type'])){
		$submission_type=	$postParameters['submission_type'];
		}
		else{
			$submission_type='';
		}
		if(isset($postParameters['y_value'])){
		$y_value=	$postParameters['y_value'];
		}
		else{
			$y_value='';
		}
		if(isset($postParameters['keywordIdValue'])){
		$keywordIdValue=	$postParameters['keywordIdValue'];
		}
		else{
			$keywordIdValue='';
		}
		if(isset($postParameters['dept_id'])){
		$dept_id=	$postParameters['dept_id'];
		}
		else{
			$dept_id='';
		}
		if(isset($postParameters['card_type'])){
		$card_type=	implode(",",$postParameters['card_type']);
		}
		else{
			$card_type='';
		}
		if(isset($postParameters['bureau_segmentation'])){
		$bureau_segmentation=	implode(",",$postParameters['bureau_segmentation']);
		}
		else{
			$bureau_segmentation='';
		}
		if(isset($postParameters['more_less1'])){
		$more_less1=	$postParameters['more_less1'];
		}
		else{
			$more_less1='';
		}
		if(isset($postParameters['xyz_value'])){
		$xyz_value=	$postParameters['xyz_value'];
		}
		else{
			$xyz_value='';
		}
		if(isset($postParameters['tabname'])){
		$tabname=	$postParameters['tabname'];
		}
		else{
			$tabname='';
		}
		
		
		
			$SearchEgnWidget = new SearchResultWidget();
		   
		   $SearchEgnWidget->rules_id=$keywordIdValue;
		   $SearchEgnWidget->user_id=$request->session()->get('EmployeeId');
		   $SearchEgnWidget->dept_id=$dept_id;
		   $SearchEgnWidget->more_less=$more_less;
		   $SearchEgnWidget->x_value=$x_value;
		   $SearchEgnWidget->submission_type=$submission_type;
		   $SearchEgnWidget->y_value=$y_value;
		   $SearchEgnWidget->card_type=$card_type;
		   $SearchEgnWidget->bureau_segmentation=$bureau_segmentation;
		   $SearchEgnWidget->more_less1=$more_less1;
		   $SearchEgnWidget->xyz_value=$xyz_value;
		   $SearchEgnWidget->tabname=$tabname;
		  $SearchEgnWidget->save();		
		return redirect('parentdashboard');
	}
	public function loadResultSEListingWidget(Request $request)
	{
		$user=$request->session()->get('EmployeeId');
		$widgetid=$request->widgetId;
		$_widget=SearchResultWidget::where("id",$request->widgetId)->first();
		if($_widget!=''){
			
				if($_widget->dept_id==36){
					
					$empsessionIdGet=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",36)->first();
						if($empid!=''){
							$sales_name=$empid->sales_name;
						}
						else{
							$sales_name='';
						}
					}
			//print_r($empid);exit;
					$detailsAgents = array();
					 $keywordIdValue = $_widget->rules_id;
					 $search_keywordIdValue= $_widget->rules_id;
					if($keywordIdValue == 1)
					{
						$more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						$y_value = $_widget->y_value;
						$submission_type = $_widget->submission_type;
						
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
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							//->toSql();
						}
						else
						{
							$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
						}
						$searchEng_caption = "RMs with ".$more_less." than ".$x_value." number of ".$submission_type." in last ".$y_value." days.";
						//echo count($detailsAgents);
					 //print_r($detailsAgents);exit;	
						
					}
					else if($keywordIdValue == 2)
					{
						$startDate = date("Y")."-".date("m")."-01";
						$currentDate = date("Y-m-d");
						$totalCardsSubmissionEmployee = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
						$totalCardsBookedEmployee = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							
							$more_less =$_widget->more_less;
							$approvalRate =$_widget->x_value;
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
							elseif($more_less == 'moreequal')
							{
								if($approval >= $approvalRate)
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
						}
						$searchEng_caption = "Approval rate ".$more_less." than ".$approvalRate."%";
						
								
					}
					
					else if($keywordIdValue == 3)
					{
						  $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						
						$submission_type = $_widget->submission_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search',$submission_type);
						if($submission_type == 'submissions')
						{
							$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							
						}
						else
						{
							$detailsAgents = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
						  $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
						
						
						$submission_type = $_widget->submission_type;
						$card_type = $_widget->card_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search',$submission_type);
						$request->session()->put('Card_type_search',$card_type);
						$cardArray = explode(",",$card_type);
						 $card_typearray=array();
						 foreach($cardArray as $namearray){
							 $card_typearray[]="'".$namearray."'";
							 
							 
						 }
						 //print_r($namefinalarray);exit;
						 $finalcard=implode(",", $card_typearray);
						if($submission_type == 'submissions')
						{
							$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
						$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in ".$card_type.".";
						
								
					}
					else if($keywordIdValue == 7)
					{
						$more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
						$more_less = $_widget->more_less;
						$more_less1 = $_widget->more_less1;
						$x_value = $_widget->x_value;
						$xyz_value = $_widget->xyz_value;
						$submission_type = $_widget->submission_type;
						
						
						
						if($more_less1 == 'more')
						{
							$moreless1Html = 'bureau_score >'.$xyz_value;
						}
						elseif($more_less1 == 'moreequal')
						{
							$moreless1Html = 'bureau_score >='.$xyz_value;
						}
						elseif($more_less1 == 'less')
						{
							$moreless1Html = 'bureau_score <'.$xyz_value;
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
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							
							$more_less = $_widget->more_less;
							$more_less1 = $_widget->more_less1;
							$x_value = $_widget->x_value;
							$xyz_value = $_widget->xyz_value;
							$submission_type = $_widget->submission_type;
							
							
							
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
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->where("booking_status",2)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								  $more_less = $_widget->more_less;
								  $x_value = $_widget->x_value;
								
								
								$submission_type = $_widget->submission_type;
								$bureau_segmentation = $_widget->bureau_segmentation;
								
								$currentDate = date("Y-m-d");
								$startDate = date("Y")."-".date("m")."-01";
								$request->session()->put('submission_type_search',$submission_type);
								$request->session()->put('bureau_segmentation_search',$bureau_segmentation);
								$bureauArray = explode(",",$bureau_segmentation);
								 $bureau_typearray=array();
								 foreach($bureauArray as $namearray){
									 $bureau_typearray[]="'".$namearray."'";
									 
									 
								 }
								 //print_r($namefinalarray);exit;
								 $finalbureau=implode(",", $bureau_typearray);
								if($submission_type == 'submissions')
								{
									$submissionDONE = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->where("booking_status",2)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
								$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with ".$bureau_segmentation.".";
								
									
							}
							else if($keywordIdValue ==10)
							{
							$best_worse = $_widget->more_less;
							$submission_type = $_widget->submission_type;
							
							
							$request->session()->put('best_worse_engine_tl',$best_worse);
							$request->session()->put('submission_type_search',$submission_type);
							
							$currentDate = date("Y-m-d");
							$currentstartDate = date("Y")."-".date("m")."-01";
							
							$lasttoDate = date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
							$lastfromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';
							
							if($submission_type == 'submissions')
							{
								$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutMashreqCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
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
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
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
					$search_keywordIdValue=$keywordIdValue;
					$employeeLists=$detailsAgents;
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
					//echo $search_keywordIdValue;
					$restagent='';
					if($search_keywordIdValue == 1 || $search_keywordIdValue ==3 || $search_keywordIdValue ==4 || $search_keywordIdValue == 5 || $search_keywordIdValue == 6 || $search_keywordIdValue == 7 || $search_keywordIdValue == 8 || $search_keywordIdValue == 9)
					{
					//echo $submission_type;
					if(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'submissions'){
						//echo "h1";
					$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();	
					}
					elseif(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'bookings'){
						//echo "h2";
					$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							//->havingRaw($morelessHtml)
							->get();	
					}
					
					
					}
					elseif($search_keywordIdValue == 2){
						$restagent = PreAgentPayoutMashreqCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
						
					}
					
					$restdataarrya=array();
					$agentempid=array();
					if($restagent!=''){
					foreach($restagent as $ragent){
						$restdataarrya[]=$ragent->employee_id;
					}
					
					$empsessionId=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionId)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",36)->first();
						if($empid!=''){
							$id=$empid->id;
							$tlagentdata=Employee_details::where("tl_id",$id)->whereNotIn('emp_id',$restdataarrya)->where("offline_status",1)->get();
							if($tlagentdata!=''){
								
								foreach($tlagentdata as $tlids){
								$agentempid[]=$tlids->emp_id;	
								}
							}
							
						}
					}
					}
					//print_r($agentempid);
					//echo $more_less;//exit;
					return view("SearchEngineWidget/loadResultSEListing",compact('agentempid','widgetid','employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));
								
					//return view("SearchEngine/loadResultSEListing",compact('employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));

					
				}
				elseif($_widget->dept_id==49){
					$empsessionIdGet=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",49)->first();
						if($empid!=''){
							$sales_name=$empid->sales_name;
						}
						else{
							$sales_name='';
						}
					}
					$detailsAgents = array();
					 $keywordIdValue = $_widget->rules_id;
					 $search_keywordIdValue= $_widget->rules_id;
					if($keywordIdValue == 1)
					{
						
						$more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						$y_value = $_widget->y_value;
						$submission_type = $_widget->submission_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
						$request->session()->put('y_value_search_CBD',$y_value);
						$request->session()->put('submission_type_search_CBD',$submission_type);
						if($submission_type == 'submissions')
						{
						/*  echo $startDate;
							echo "<br />";
							echo $currentDate;
							exit;  */
							
							$detailsAgents = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							//print_r($detailsAgents);exit;
						}
						else
						{
							$detailsAgents = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
						$totalCardsSubmissionEmployee = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])
							->groupBy("employee_id")
							->get();
							
						$totalCardsBookedEmployee = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
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
							$more_less = $_widget->more_less;
							$approvalRate =$_widget->x_value;
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
							elseif($more_less == 'moreequal')
							{
								if($approval >= $approvalRate)
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
								if($approval < $approvalRate)
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
						  $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						
						$submission_type = $_widget->submission_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_CBD',$submission_type);
						if($submission_type == 'submissions')
						{
							$detailsAgents = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							
						}
						else
						{
							$detailsAgents = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
						 $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
						
						
						$submission_type = $_widget->submission_type;
						$card_type = $_widget->card_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_CBD',$submission_type);
						$request->session()->put('Card_type_search_CBD',$card_type);
						$cardArray = explode(",",$card_type);
						 $card_typearray=array();
						 foreach($cardArray as $namearray){
							 $card_typearray[]="'".$namearray."'";
							 
							 
						 }
						 //print_r($namefinalarray);exit;
						 $finalcard=implode(",", $card_typearray);
						if($submission_type == 'submissions')
						{
							$submissionDONE = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
							$submissionDONE = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
						$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in ".$card_type.".";
						
								
					}
					else if($keywordIdValue == 7)
					{
						
						$more_less = $_widget->more_less;
						$more_less1 = $_widget->more_less1;
						$x_value = $_widget->x_value;
						$xyz_value = $_widget->xyz_value;
						$submission_type = $_widget->submission_type;
						
						
						
						if($more_less1 == 'more')
						{
							$moreless1Html = 'bureau_score >'.$xyz_value;
						}
						elseif($more_less1 == 'moreequal')
						{
							$moreless1Html = 'bureau_score >='.$xyz_value;
						}
						elseif($more_less1 == 'less')
						{
							$moreless1Html = 'bureau_score <'.$xyz_value;
						}
						elseif($more_less1 == 'lessequal')
						{
							$moreless1Html = 'bureau_score <'.$xyz_value;
						}
						
						
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_CBD',$submission_type);
						$request->session()->put('bureau_score_search_CBD',$moreless1Html);
						if($submission_type == 'submissions')
						{
							$detailsAgentsSubmissionTotal = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							$detailsAgentsSubmissionTotal = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							$more_less = $_widget->more_less;
							$more_less1 = $_widget->more_less1;
							$x_value = $_widget->x_value;
							$xyz_value = $_widget->xyz_value;
							$submission_type = $_widget->submission_type;
							
							
							
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
							
							$request->session()->put('submission_type_search_CBD',$submission_type);
							$request->session()->put('mrs_score_search_CBD',$moreless1Html);
							
							$currentDate = date("Y-m-d");
							$startDate = date("Y")."-".date("m")."-01";
							
							if($submission_type == 'submissions')
							{
								$detailsAgentsSubmissionTotal = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								$detailsAgentsSubmissionTotal = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->where("booking_status",2)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								  $more_less = $_widget->more_less;
								$x_value = $_widget->x_value;
								
								
								$submission_type = $_widget->submission_type;
								$bureau_segmentation = $_widget->bureau_segmentation;
								
								$currentDate = date("Y-m-d");
								$startDate = date("Y")."-".date("m")."-01";
								$request->session()->put('submission_type_search_CBD',$submission_type);
								$request->session()->put('bureau_segmentation_search_CBD',$bureau_segmentation);
								$bureauArray = explode(",",$bureau_segmentation);
								 $bureau_typearray=array();
								 foreach($bureauArray as $namearray){
									 $bureau_typearray[]="'".$namearray."'";
									 
									 
								 }
								 //print_r($namefinalarray);exit;
								 $finalbureau=implode(",", $bureau_typearray);
								if($submission_type == 'submissions')
								{
									$submissionDONE = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
									$submissionDONE = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->where("booking_status",2)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
								$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with ".$bureau_segmentation.".";
								
									
							}
							else if($keywordIdValue ==10)
							{
							$best_worse = $_widget->more_less;
							$submission_type = $_widget->submission_type;
							
							
							$request->session()->put('best_worse_engine_tl_CBD',$best_worse);
							$request->session()->put('submission_type_search_CBD',$submission_type);
							
							$currentDate = date("Y-m-d");
							$currentstartDate = date("Y")."-".date("m")."-01";
							
							$lasttoDate = date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
							$lastfromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';;
							
							if($submission_type == 'submissions')
							{
								$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
								
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
									$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutCBDCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
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
								$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutCBDCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
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
									$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutCBDCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
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
							
						
						$request->session()->put('searchEng_data_CBD',$detailsAgents);
						$request->session()->put('searchEng_caption_CBD',$searchEng_caption);
						
					
					
					
					$request->session()->put('search_keywordIdValue_CBD',$keywordIdValue);	
					$request->session()->put('innter_engine_tl_CBD','');
					$request->session()->put('innter_engine_processor_CBD','');
					$request->session()->put('innter_engine_rangeId_CBD','');
					
					$innter_engine_rangeId = array();
					if(@$request->session()->get('innter_engine_rangeId_CBD') != '')
					{
						$innter_engine_rangeId = $request->session()->get('innter_engine_rangeId_CBD');
						/* echo "<pre>";
						print_r($innter_engine_rangeId);
						exit;  */
					}
					$empIdList = array();
					$tl_details = array();
					$search_keywordIdValue=$keywordIdValue;
					$employeeLists=$detailsAgents;
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
								$range_idEmp = PreAgentPayoutCBDCard::where("employee_id",$emp_id)->orderby("sort_order","DESC")->first()->range_id;
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
								
									$range_idEmp = PreAgentPayoutCBDCard::where("employee_id",$emp->employee_id)->orderby("sort_order","DESC")->first()->range_id;
								
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
					//print_r($employeeLists);exit;
					if(count($employeeLists) >0)
					{
					$tl_details = PreAgentPayoutCBDCard::selectRaw("tl_name , count(tl_name) as total_TL")
									
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
					$restagent='';
					if($search_keywordIdValue == 1 || $search_keywordIdValue ==3 || $search_keywordIdValue ==4 || $search_keywordIdValue == 5 || $search_keywordIdValue == 6 || $search_keywordIdValue == 7 || $search_keywordIdValue == 8 || $search_keywordIdValue == 9)
					{
					if(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'submissions'){
						//echo "h1";
					$restagent = PreAgentPayoutCBDCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();	
					}
					elseif(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'bookings'){
						//echo "h2";
					$restagent = PreAgentPayoutCBDCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							//->havingRaw($morelessHtml)
							->get();	
					}
					}
					elseif($search_keywordIdValue == 2){
						$restagent = PreAgentPayoutCBDCard::selectRaw("employee_id")
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
					
					$empsessionId=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionId)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",49)->first();
						if($empid!=''){
							$id=$empid->id;
							$tlagentdata=Employee_details::where("tl_id",$id)->whereNotIn('emp_id',$restdataarrya)->where("offline_status",1)->get();
							if($tlagentdata!=''){
								
								foreach($tlagentdata as $tlids){
								$agentempid[]=$tlids->emp_id;	
								}
							}
							
						}
					}
					}
					
					return view("SearchEngineWidget/loadResultSEListingCBD",compact('agentempid','widgetid','employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));
							
					
				}
				
				
			elseif($_widget->dept_id==52){
					$empsessionIdGet=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",52)->first();
						if($empid!=''){
							$sales_name=$empid->sales_name;
						}
						else{
							$sales_name='';
						}
					}
					$detailsAgents = array();
					 $keywordIdValue = $_widget->rules_id;
					 $search_keywordIdValue= $_widget->rules_id;
					if($keywordIdValue == 1)
					{
						
						$more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						$y_value = $_widget->y_value;
						$submission_type = $_widget->submission_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime("-".$y_value." days ".$currentDate));
						$request->session()->put('y_value_search_EIB',$y_value);
						$request->session()->put('submission_type_search_EIB',$submission_type);
						if($submission_type == 'submissions')
						{
						/*  echo $startDate;
							echo "<br />";
							echo $currentDate;
							exit;  */
							
							$detailsAgents = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							//print_r($detailsAgents);exit;
						}
						else
						{
							$detailsAgents = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
						$totalCardsSubmissionEmployee = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])
							->groupBy("employee_id")
							->get();
							
						$totalCardsBookedEmployee = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
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
							$more_less = $_widget->more_less;
							$approvalRate =$_widget->x_value;
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
							elseif($more_less == 'moreequal')
							{
								if($approval >= $approvalRate)
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
								if($approval < $approvalRate)
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
						  $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
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
						
						$submission_type = $_widget->submission_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_EIB',$submission_type);
						if($submission_type == 'submissions')
						{
							$detailsAgents = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->havingRaw($morelessHtml)
							->get();
							
						}
						else
						{
							$detailsAgents = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
						 $more_less = $_widget->more_less;
						$x_value = $_widget->x_value;
						
						
						$submission_type = $_widget->submission_type;
						$card_type = $_widget->card_type;
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_EIB',$submission_type);
						$request->session()->put('Card_type_search_EIB',$card_type);
						$cardArray = explode(",",$card_type);
						 $card_typearray=array();
						 foreach($cardArray as $namearray){
							 $card_typearray[]="'".$namearray."'";
							 
							 
						 }
						 //print_r($namefinalarray);exit;
						 $finalcard=implode(",", $card_typearray);
						if($submission_type == 'submissions')
						{
							$submissionDONE = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
							$submissionDONE = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$submissionDONECardType = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->whereIn("card_type",$card_typearray)
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
						$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in ".$card_type.".";
						
								
					}
					else if($keywordIdValue == 7)
					{
						
						$more_less = $_widget->more_less;
						$more_less1 = $_widget->more_less1;
						$x_value = $_widget->x_value;
						$xyz_value = $_widget->xyz_value;
						$submission_type = $_widget->submission_type;
						
						
						
						if($more_less1 == 'more')
						{
							$moreless1Html = 'bureau_score >'.$xyz_value;
						}
						elseif($more_less1 == 'moreequal')
						{
							$moreless1Html = 'bureau_score >='.$xyz_value;
						}
						elseif($more_less1 == 'less')
						{
							$moreless1Html = 'bureau_score <'.$xyz_value;
						}
						elseif($more_less1 == 'lessequal')
						{
							$moreless1Html = 'bureau_score <'.$xyz_value;
						}
						
						
						
						$currentDate = date("Y-m-d");
						$startDate = date("Y")."-".date("m")."-01";
						$request->session()->put('submission_type_search_EIB',$submission_type);
						$request->session()->put('bureau_score_search_EIB',$moreless1Html);
						if($submission_type == 'submissions')
						{
							$detailsAgentsSubmissionTotal = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							$detailsAgentsSubmissionTotal = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							->get();
							
							$detailsAgentsSubmissionTotalB = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
							$more_less = $_widget->more_less;
							$more_less1 = $_widget->more_less1;
							$x_value = $_widget->x_value;
							$xyz_value = $_widget->xyz_value;
							$submission_type = $_widget->submission_type;
							
							
							
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
							
							$request->session()->put('submission_type_search_EIB',$submission_type);
							$request->session()->put('mrs_score_search_EIB',$moreless1Html);
							
							$currentDate = date("Y-m-d");
							$startDate = date("Y")."-".date("m")."-01";
							
							if($submission_type == 'submissions')
							{
								$detailsAgentsSubmissionTotal = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								$detailsAgentsSubmissionTotal = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
								->where("booking_status",2)
								->groupBy("employee_id")
								->get();
								
								$detailsAgentsSubmissionTotalB = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
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
								  $more_less = $_widget->more_less;
								$x_value = $_widget->x_value;
								
								
								$submission_type = $_widget->submission_type;
								$bureau_segmentation = $_widget->bureau_segmentation;
								
								$currentDate = date("Y-m-d");
								$startDate = date("Y")."-".date("m")."-01";
								$request->session()->put('submission_type_search_EIB',$submission_type);
								$request->session()->put('bureau_segmentation_search_EIB',$bureau_segmentation);
								$bureauArray = explode(",",$bureau_segmentation);
								 $bureau_typearray=array();
								 foreach($bureauArray as $namearray){
									 $bureau_typearray[]="'".$namearray."'";
									 
									 
								 }
								 //print_r($namefinalarray);exit;
								 $finalbureau=implode(",", $bureau_typearray);
								if($submission_type == 'submissions')
								{
									$submissionDONE = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
									$submissionDONE = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->where("booking_status",2)
									->groupBy("employee_id")
									->get();
									
									$submissionDONECardType = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
									->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
									->whereIn("bureau_segmentation",$bureau_typearray)
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
								$searchEng_caption = "RMs with ".$more_less." than ".$x_value."% ".$submission_type." in customer with ".$bureau_segmentation.".";
								
									
							}
							else if($keywordIdValue ==10)
							{
							$best_worse = $_widget->more_less;
							$submission_type = $_widget->submission_type;
							
							
							$request->session()->put('best_worse_engine_tl_EIB',$best_worse);
							$request->session()->put('submission_type_search_EIB',$submission_type);
							
							$currentDate = date("Y-m-d");
							$currentstartDate = date("Y")."-".date("m")."-01";
							
							$lasttoDate = date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
							$lastfromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';;
							
							if($submission_type == 'submissions')
							{
								$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
								
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
									$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutEIBCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
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
								$detailsAgentsSubmissionTotalCurrent = PreAgentPayoutEIBCard::selectRaw("employee_id , count(employee_id) as total_card")
								->whereBetween("submission_date",[$currentstartDate,$currentDate])->where("tl_name",$sales_name)
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
									$detailsAgentsSubmissionTotalLastmonth = PreAgentPayoutEIBCard::whereBetween("submission_date",[$lastfromDate,$lasttoDate])
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
							
						
						$request->session()->put('searchEng_data_EIB',$detailsAgents);
						$request->session()->put('searchEng_caption_EIB',$searchEng_caption);
						
					
					
					
					$request->session()->put('search_keywordIdValue_EIB',$keywordIdValue);	
					$request->session()->put('innter_engine_tl_EIB','');
					$request->session()->put('innter_engine_processor_EIB','');
					$request->session()->put('innter_engine_rangeId_EIB','');
					
					$innter_engine_rangeId = array();
					if(@$request->session()->get('innter_engine_rangeId_EIB') != '')
					{
						$innter_engine_rangeId = $request->session()->get('innter_engine_rangeId_EIB');
						/* echo "<pre>";
						print_r($innter_engine_rangeId);
						exit;  */
					}
					$empIdList = array();
					$tl_details = array();
					$search_keywordIdValue=$keywordIdValue;
					$employeeLists=$detailsAgents;
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
								$range_idEmp = PreAgentPayoutEIBCard::where("employee_id",$emp_id)->orderby("sort_order","DESC")->first()->range_id;
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
								
									$range_idEmp = PreAgentPayoutEIBCard::where("employee_id",$emp->employee_id)->orderby("sort_order","DESC")->first()->range_id;
								
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
					//print_r($employeeLists);exit;
					if(count($employeeLists) >0)
					{
					$tl_details = PreAgentPayoutEIBCard::selectRaw("tl_name , count(tl_name) as total_TL")
									
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
					$restagent='';
					if($search_keywordIdValue == 1 || $search_keywordIdValue ==3 || $search_keywordIdValue ==4 || $search_keywordIdValue == 5 || $search_keywordIdValue == 6 || $search_keywordIdValue == 7 || $search_keywordIdValue == 8 || $search_keywordIdValue == 9)
					{
					if(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'submissions'){
						//echo "h1";
					$restagent = PreAgentPayoutEIBCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->groupBy("employee_id")
							->get();	
					}
					elseif(($more_less == 'less' || $more_less == 'lessequal') && $submission_type == 'bookings'){
						//echo "h2";
					$restagent = PreAgentPayoutEIBCard::selectRaw("employee_id")
							->whereBetween("submission_date",[$startDate,$currentDate])->where("tl_name",$sales_name)
							->where("booking_status",2)
							->groupBy("employee_id")
							//->havingRaw($morelessHtml)
							->get();	
					}
					}
					elseif($search_keywordIdValue == 2){
						$restagent = PreAgentPayoutEIBCard::selectRaw("employee_id")
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
					
					$empsessionId=$request->session()->get('EmployeeId');
					$empDataGetting = Employee::where("id",$empsessionId)->first();
					if($empDataGetting!=''){
						$empid=Employee_details::where("emp_id",$empDataGetting->employee_id)->where("job_function",3)->where("dept_id",52)->first();
						if($empid!=''){
							$id=$empid->id;
							$tlagentdata=Employee_details::where("tl_id",$id)->whereNotIn('emp_id',$restdataarrya)->where("offline_status",1)->get();
							if($tlagentdata!=''){
								
								foreach($tlagentdata as $tlids){
								$agentempid[]=$tlids->emp_id;	
								}
							}
							
						}
					}
					}
					
					return view("SearchEngineWidget/loadResultSEListingEIB",compact('agentempid','widgetid','employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));
							
					
				}	
				
				
				
			
		}
	
	
	}
	public function loadResultSEListing(Request $request)
	{
		
		
		
		
		
		$employeeLists = array();
		$searchEng_caption = "No Search Keyword Selected.";
		$search_keywordIdValue = '';
		if(@$request->session()->get('searchEng_data') != '')
		{
			$employeeLists = $request->session()->get('searchEng_data');
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
		
		return view("SearchEngine/loadResultSEListing",compact('employeeLists','searchEng_caption','search_keywordIdValue','tl_details','rangeIds','empIdList'));
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
	public function deleteWidgetTable($widgetId)
	  {
		 
			 $getVintageData=SearchResultWidget::where("id",$widgetId)->first();
				if( $getVintageData != '')
				{
				   $empattributes = SearchResultWidget::find($getVintageData->id);
			
					$empattributes->delete();
					return redirect('SearchEngineWidget');
				}
				
	  }	 
}