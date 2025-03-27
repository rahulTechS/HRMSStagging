<?php

namespace App\Http\Controllers\DataCut;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\DataCut\ENBDCardsDatacutImportFiles;
use App\Models\DataCut\ENBDDataCutImportFiles;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\DataCut\ENBDDataCut;
use App\Models\MIS\JonusReportLog;
use App\Models\DataCut\EnbdMisCardsPhysicalDatacut;
use App\Models\DataCut\EnbdMisCardsTabDatacut;
use App\Models\DataCut\enbdFinalCompleteMISDatacutTab;
use App\Models\DataCut\DatacutInformation;
use App\Models\DataCut\EnbdFinalMISCompletePhysical;
use App\Models\DataCut\EnbdRMCompletePerformance;
use App\Models\DataCut\EnbdFinalMisCompletebothCreditCards;
use App\Models\DataCut\EnbdRMCompletePerformanceLoan;
use App\Models\DataCut\AbudhabiProductInfo;
use App\Models\DataCut\TLAnalysisPerformanceEnbd;
use App\Models\DataCut\CreditCardEndReport;
use App\Models\MIS\MainMisReport;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\SEPayout\AgentPayout;
use App\Models\Industry\CompanyListComplete;
class DataCutTLController extends Controller
{
    public function TLAnalysis()
	{
			$enbdAgentList = AgentPayout::where("performace_tl_status",1)->where("year",2023)->orderBy("month","ASC")->first();
			
			if($enbdAgentList != '')
			{
				$tlName = $enbdAgentList->tl_name;
				$salesTimeTL = $enbdAgentList->sales_time;
				/* echo $tlName;
				echo '<br />';
				echo $salesTimeTL;exit; */ 
				$getDetails = AgentPayout::where("tl_name",$tlName)->where("sales_time",$salesTimeTL)->orderBy("vintage","ASC")->get();
				/* echo '<pre>';
				print_r($getDetails);
				exit; */
				/* 
				*get Current Positions
				*/
				$existDetails = TLAnalysisPerformanceEnbd::where("tl_name",$tlName)->first();
				$Sales_Time_Count = 1;
				if($existDetails != '')
				{
					$existDetailsList = TLAnalysisPerformanceEnbd::where("tl_name",$tlName)->get();
					$existSalesCount = $existDetailsList->count();
					$Sales_Time_Count = $existSalesCount+1;
				}
				
				/* 
				*get Current Positions
				*/
				/*
				*inserting Data
				*/
				$rmProfile = new TLAnalysisPerformanceEnbd();
				$rmProfile->tl_name = $tlName;
				
				$rmProfile->sales_time = $salesTimeTL;
				
				$rmProfile->sales_count = $Sales_Time_Count;
				
				/*
				*TL Team Performance
				*/
				$TL_Card_HC = 0;
				$TL_PL_HC = 0;
				$TL_AL_HC = 0;
				$TL_ML_HC = 0;
				$TL_Head_Count = 0;
				$TC_by_Cards_Team = 0;
				$TC_by_Loans_Team = 0;
				$TC_Overall = 0;
				$TR_by_Loans_Team =0;
				$TR_by_Cards_Team =0;
				$TR_Overall =0;
				$TLoan_by_Loans_Team = 0;
				$TLoan_by_Cards_Team = 0;
				$TLoan_overall = 0;
				$SECardsArray = array();
				$Salary = 0;
				$Mass =0;
				$Premium =0;
				$SP =0;
				$totalCard = 0;
				$totalTarget = 0;
				$SalaryCard = 0;
				$excess = 0;
				$submission_count = 0;
				$submissionDetailsEnd = 0;
				$col5 = 0;
					$col5to10 = 0;
					$col10to15 = 0;
					$col15to25 = 0;
					$col25 = 0;
					$col5end = 0;
					$col5to10end = 0;
					$col10to15end = 0;
					$col15to25end = 0;
					$col25end = 0;
						$massSubmissionCountEND = 0;
				$pSubmissionCountEND = 0;
				$SpSubmissionCountEND = 0;
				$massSubmissionCount = 0;
				$pSubmissionCount = 0;
				$SpSubmissionCount = 0;
				$aleSubmissionCount = 0;
				$naleSubmissionCount = 0;
				$aleSubmissionCountEnd = 0;
				$naleSubmissionCountEnd = 0;
				$SECardsArray = array();
				$TC_Overall_converted = 0;
				foreach($getDetails as $tl)
				{
					
					if($tl->agent_product == 'CARD')
					{
						$TL_Card_HC++;
						$TC_by_Cards_Team = $TC_by_Cards_Team+$tl->tc_card;
						$TR_by_Cards_Team = $TR_by_Cards_Team+$tl->total_revenue;
						$TLoan_by_Cards_Team = $TLoan_by_Cards_Team+$tl->final_loan_amount;
						$totalTarget = $totalTarget+$tl->agent_target;
						$SalaryCard = $SalaryCard+$tl->total_salary;
						$SECardsArray[] = $tl->tc_card;
					}
					else if($tl->agent_product == 'ALOAN' || $tl->agent_product == 'AUTOLOANS' || $tl->agent_product == 'AUTOLOAN')
					{
						$TL_AL_HC++;
						$TC_by_Loans_Team = $TC_by_Loans_Team+$tl->tc_card;
						$TR_by_Loans_Team = $TR_by_Loans_Team+$tl->total_revenue;
						$TLoan_by_Loans_Team = $TLoan_by_Loans_Team+$tl->final_loan_amount;
					}
					else if($tl->agent_product == 'LOAN' || $tl->agent_product == 'PLOAN' || $tl->agent_product == 'POSLOANS')
					{
						$TL_PL_HC++;
						$TC_by_Loans_Team = $TC_by_Loans_Team+$tl->tc_card;
						$TR_by_Loans_Team = $TR_by_Loans_Team+$tl->total_revenue;
						$TLoan_by_Loans_Team = $TLoan_by_Loans_Team+$tl->final_loan_amount;
					}
					else if($tl->agent_product == 'MP')
					{
						$TL_ML_HC++;
						$TC_by_Loans_Team = $TC_by_Loans_Team+$tl->tc_card;
						$TR_by_Loans_Team = $TR_by_Loans_Team+$tl->total_revenue;
						$TLoan_by_Loans_Team = $TLoan_by_Loans_Team+$tl->final_loan_amount;
					}
					else
					{
						$TL_PL_HC++;
						$TC_by_Loans_Team = $TC_by_Loans_Team+$tl->tc_card;
						$TR_by_Loans_Team = $TR_by_Loans_Team+$tl->total_revenue;
						$TLoan_by_Loans_Team = $TLoan_by_Loans_Team+$tl->final_loan_amount;
					}
					$TL_Head_Count++;
					$TC_Overall = $TC_Overall+$tl->tc_card;
					$TC_Overall_converted = $TC_Overall_converted+$tl->tc_final;
					$TR_Overall = $TR_Overall+$tl->total_revenue;
					$TLoan_overall = $TLoan_overall+$tl->final_loan_amount;
					$Salary = $Salary+$tl->total_salary;
					$Mass = $Mass+$tl->mass;
					$Premium = $Premium+$tl->premium;
					$SP = $SP+$tl->super_premium;
					$totalCard = $totalCard+$tl->tc_card;
					$excess = $excess+$tl->excess;
					
					/*
					*salary Range
					*/
					
					if($tl->match_employee == 2)
					{
						$bankCode = $tl->agent_bank_code;
						if($bankCode != '' && $bankCode != NULL)
						{
						$employeeData = Employee_details::where("source_code",$bankCode)->first();
						if($employeeData!= '')
						{
							
							$employeeDataID = $employeeData->id;
							$salesTimeArray = explode("-",$tl->sales_time);
							$monthP = sprintf("%02d", $salesTimeArray[0]);
							$salesTimeNew  = $monthP.'-'.$salesTimeArray[1];
							$submissionDetails = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->get();
							$submissionDetailsENDCards = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->get();
							$submission_count = $submission_count+$submissionDetails->count();
							/*
							*get End Submission
							*/
							$submissionDetailsEnd = $submissionDetailsEnd+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->get()->count();
							/*
							*get End Submission
							*/
							if($submission_count != 0)
							{
							$Approval_Rate = round($submissionDetailsEnd/$submission_count,2);
							
							}
							else
							{
								
							$Approval_Rate = 0;
							}
							
							/*  foreach($submissionDetails as $_detailsS)
							{
								if( $_detailsS->SALARY < 5000)
								{
									$col5++;
								}
								elseif($_detailsS->SALARY >= 5000 && $_detailsS->SALARY < 10000)
								{
									$col5to10++;
								}
								elseif($_detailsS->SALARY >= 10000 && $_detailsS->SALARY < 15000)
								{
									$col10to15++;
								}
								elseif($_detailsS->SALARY >= 15000 && $_detailsS->SALARY < 25000)
								{
									$col15to25++;
								}
								elseif($_detailsS->SALARY >= 25000)
								{
									$col25++;
								}
								
							}
							
							foreach($submissionDetailsENDCards as $_detailsS)
							{
								if( $_detailsS->SALARY < 5000)
								{
									$col5end++;
								}
								elseif($_detailsS->SALARY >= 5000 && $_detailsS->SALARY < 10000)
								{
									$col5to10end++;
								}
								elseif($_detailsS->SALARY >= 10000 && $_detailsS->SALARY < 15000)
								{
									$col10to15end++;
								}
								elseif($_detailsS->SALARY >= 15000 && $_detailsS->SALARY < 25000)
								{
									$col15to25end++;
								}
								elseif($_detailsS->SALARY >= 25000)
								{
									$col25end++;
								}
								
							} */ 
								$massSubmissionCountEND = $massSubmissionCountEND+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Mass")->get()->count();
								$pSubmissionCountEND = $pSubmissionCountEND+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Premium")->get()->count();
								$SpSubmissionCountEND = $SpSubmissionCountEND+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Super Premium")->get()->count();
								
								
								$massSubmissionCount = $massSubmissionCount+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Mass")->get()->count();
								$pSubmissionCount = $pSubmissionCount+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Premium")->get()->count();
								$SpSubmissionCount = $SpSubmissionCount+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Super Premium")->get()->count();
						
								$aleSubmissionCount = $aleSubmissionCount+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Ale")->get()->count();
				
								$naleSubmissionCount = $naleSubmissionCount+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Nale")->get()->count();
								
								
								$aleSubmissionCountEnd = $aleSubmissionCountEnd+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Ale")->where("match_datacut",2)->get()->count();
								
								$naleSubmissionCountEnd = $naleSubmissionCountEnd+EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Nale")->where("match_datacut",2)->get()->count();
								
						
						}
						}
					}
					/*
					*salary Range
					*/
					
				}
				
				/*
				*TL Team Performance
				*/
				/*
				*Productivity
				*/
				 $loanHC = $TL_PL_HC+$TL_AL_HC+$TL_ML_HC;
				if($TC_by_Cards_Team != 0)
				{
					$Cards_Team_Card_Productivity = round($TC_by_Cards_Team/$TL_Card_HC,2);
				}
				else
				{
					$Cards_Team_Card_Productivity = 0;
				}
				if($TC_by_Loans_Team != 0)
				{
				$Loans_Team_Card_Productivity = round($TC_by_Loans_Team/$loanHC,2);
				}
				else
				{
					$Loans_Team_Card_Productivity = 0;
				}
				
				$Cards_Overall_Productivity = round($TC_Overall/$TL_Head_Count,2);
				
				if($TLoan_by_Loans_Team != 0)
				{
				$Loans_Team_Loan_Prod = round($TLoan_by_Loans_Team/$loanHC,2);
				}
				else
				{
					$Loans_Team_Loan_Prod = 0;
				}
				if($TLoan_by_Cards_Team != 0)
				{
				$Cards_Team_Loan_Prod = round($TLoan_by_Cards_Team/$TL_Card_HC,2);
				}
				else
				{
					$Cards_Team_Loan_Prod = 0;
				}
				$Loan_Overall_Team_Prod = round($TLoan_overall/$TL_Head_Count,2);
				/*
				*Productivity
				*/
				/*
				*top performacer
				*/
				rsort($SECardsArray);
				/* echo '<pre>';
				print_r($SECardsArray);
				exit; */ 
				
				$totalAgentLengh = count($SECardsArray);
				
				$top20 = round(($totalAgentLengh*20)/100);
				$top20Cards = 0;
				for($i=0;$i<$top20;$i++)
				{
					$top20Cards = $top20Cards+$SECardsArray[$i];
				}
				
				
				$top_20_50 = round(($totalAgentLengh*50)/100);
				$top_20_50Cards = 0;
				for($i=$top20;$i<$top_20_50;$i++)
				{
					$top_20_50Cards = $top_20_50Cards+$SECardsArray[$i];
				}
				/* echo $top_20_50Cards;exit; */
				
				$top_50_80 = round(($totalAgentLengh*80)/100);
				$top_50_80Cards = 0;
				for($i=$top_20_50;$i<$top_50_80;$i++)
				{
					$top_50_80Cards = $top_50_80Cards+$SECardsArray[$i];
				}
				
				
				$top_80_100 = $totalAgentLengh;
				$top_80_100Cards = 0;
				for($i=$top_50_80;$i<$top_80_100;$i++)
				{
					$top_80_100Cards = $top_80_100Cards+$SECardsArray[$i];
				}
				if($top20Cards != 0)
				{
				$top_20Final = round($top20Cards/$TC_by_Cards_Team,2);
				}
				else
				{
					$top_20Final = 0;
				}
				if($top_20_50Cards != 0)
				{
				$top_20_50Final = round($top_20_50Cards/$TC_by_Cards_Team,2);
				}
				else
				{
					$top_20_50Final = 0;
				}
				if($top_50_80Cards != 0)
				{
				$top_50_80Final = round($top_50_80Cards/$TC_by_Cards_Team,2);
				}
				else
				{
					$top_50_80Final = 0;
				}
				if($top_80_100Cards != 0)
				{
				$top_80_100Final = round($top_80_100Cards/$TC_by_Cards_Team,2);
				}
				else
				{
					$top_80_100Final = 0;
				}
				
				/*
				*top performacer
				*/
				
				$rmProfile->TL_Card_HC = $TL_Card_HC;
				$rmProfile->TL_PL_HC = $TL_PL_HC;
				$rmProfile->TL_AL_HC = $TL_AL_HC;
				$rmProfile->TL_ML_HC = $TL_ML_HC;
				$rmProfile->TL_Head_Count = $TL_Head_Count;
				$rmProfile->TC_by_Cards_Team = $TC_by_Cards_Team;
				$rmProfile->TC_by_Loans_Team = $TC_by_Loans_Team;
				$rmProfile->TC_Overall = $TC_Overall;
				$rmProfile->Cards_Team_Card_Productivity = $Cards_Team_Card_Productivity;
				$rmProfile->Loans_Team_Card_Productivity = $Loans_Team_Card_Productivity;
				$rmProfile->Cards_Overall_Productivity = $Cards_Overall_Productivity;
				$rmProfile->TR_by_Loans_Team = $TR_by_Loans_Team;
				$rmProfile->TR_by_Cards_Team = $TR_by_Cards_Team;
				$rmProfile->TR_Overall = $TR_Overall;
				$rmProfile->TLoan_by_Loans_Team = $TLoan_by_Loans_Team;
				$rmProfile->TLoan_by_Cards_Team = $TLoan_by_Cards_Team;
				$rmProfile->TLoan_overall = $TLoan_overall;
				$rmProfile->Loans_Team_Loan_Prod = $Loans_Team_Loan_Prod;
				$rmProfile->Cards_Team_Loan_Prod = $Cards_Team_Loan_Prod;
				$rmProfile->Loan_Overall_Team_Prod = $Loan_Overall_Team_Prod;
				$rmProfile->top_20 = $top_20Final;
				$rmProfile->top_20_50 = $top_20_50Final;
				$rmProfile->top_50_80 = $top_50_80Final;
				$rmProfile->top_80_100 = $top_80_100Final;
				$rmProfile->Mass = $Mass;
				$rmProfile->Premium = $Premium;
				$rmProfile->SP = $SP;
				$rmProfile->Salary = $Salary;
				$rmProfile->timeout_status = 1;
				
				
				
				/*
				*runining  coding TL_Card_HC_r
				*/
				$TL_Card_HC_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TL_Card_HC_r = $TL_Card_HC;
					$TL_Card_HC_r =  $TL_Card_HC;
				}
				else
				{
					$running_TL_Card_HC = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TL_Card_HC =$running_TL_Card_HC+$exist->TL_Card_HC;
					}
					$rmProfile->TL_Card_HC_r = $running_TL_Card_HC+$TL_Card_HC;
					$TL_Card_HC_r = $running_TL_Card_HC+$TL_Card_HC;
				}
				
				/*
				*runining  coding TL_Card_HC_r
				*/
				
				
				/*
				*runining  coding TL_PL_HC
				*/
				$TL_PL_HC_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TL_PL_HC_r = $TL_PL_HC;
					$TL_PL_HC_r =  $TL_PL_HC;
				}
				else
				{
					$running_TL_PL_HC = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TL_PL_HC =$running_TL_PL_HC+$exist->TL_PL_HC;
					}
					$rmProfile->TL_PL_HC_r = $running_TL_PL_HC+$TL_PL_HC;
					$TL_PL_HC_r =$running_TL_PL_HC+$TL_PL_HC;
				}
				
				/*
				*runining  coding TL_PL_HC
				*/
				
				/*
				*runining  coding TL_AL_HC
				*/
				$TL_AL_HC_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TL_AL_HC_r = $TL_AL_HC;
					$TL_AL_HC_r =  $TL_AL_HC;
				}
				else
				{
					$running_TL_AL_HC = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TL_AL_HC =$running_TL_AL_HC+$exist->TL_AL_HC;
					}
					$rmProfile->TL_AL_HC_r = $running_TL_AL_HC+$TL_AL_HC;
					$TL_AL_HC_r =$running_TL_AL_HC+$TL_AL_HC;
				}
				
				/*
				*runining  coding TL_AL_HC
				*/
				
				
				/*
				*runining  coding TL_ML_HC
				*/
				$TL_ML_HC_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TL_ML_HC_r = $TL_ML_HC;
					$TL_ML_HC_r =  $TL_ML_HC;
				}
				else
				{
					$running_TL_ML_HC = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TL_ML_HC =$running_TL_ML_HC+$exist->TL_ML_HC;
					}
					$rmProfile->TL_ML_HC_r = $running_TL_ML_HC+$TL_ML_HC;
					$TL_ML_HC_r =$running_TL_ML_HC+$TL_ML_HC;
				}
				
				/*
				*runining  coding TL_AL_HC
				*/
				
				
				/*
				*runining  coding TL_Head_Count
				*/
				$TL_Head_Count_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TL_Head_Count_r = $TL_Head_Count;
					$TL_Head_Count_r =  $TL_Head_Count;
				}
				else
				{
					$running_TL_Head_Count = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TL_Head_Count =$running_TL_Head_Count+$exist->TL_Head_Count;
					}
					$rmProfile->TL_Head_Count_r = $running_TL_Head_Count+$TL_Head_Count;
					$TL_Head_Count_r = $running_TL_Head_Count+$TL_Head_Count;
				}
				
				/*
				*runining  coding TL_Head_Count
				*/
				
				
				/*
				*runining  coding TC_by_Cards_Team
				*/
				$TC_by_Cards_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TC_by_Cards_Team_r = $TC_by_Cards_Team;
					$TC_by_Cards_Team_r =  $TC_by_Cards_Team;
				}
				else
				{
					$running_TC_by_Cards_Team = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TC_by_Cards_Team =$running_TC_by_Cards_Team+$exist->TC_by_Cards_Team;
					}
					$rmProfile->TC_by_Cards_Team_r = $running_TC_by_Cards_Team+$TC_by_Cards_Team;
					$TC_by_Cards_Team_r = $running_TC_by_Cards_Team+$TC_by_Cards_Team;
				}
				
				/*
				*runining  coding TC_by_Cards_Team
				*/
				
				/*
				*runining  coding TC_by_Loans_Team
				*/
				$TC_by_Loans_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TC_by_Loans_Team_r = $TC_by_Loans_Team;
					$TC_by_Loans_Team_r =  $TC_by_Loans_Team;
				}
				else
				{
					$running_TC_by_Loans_Team = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TC_by_Loans_Team =$running_TC_by_Loans_Team+$exist->TC_by_Loans_Team;
					}
					$rmProfile->TC_by_Loans_Team_r = $running_TC_by_Loans_Team+$TC_by_Loans_Team;
					$TC_by_Loans_Team_r = $running_TC_by_Loans_Team+$TC_by_Loans_Team;
				}
				
				/*
				*runining  coding TC_by_Loans_Team
				*/
				
				/*
				*runining  coding TC_Overall
				*/
				$TC_Overall_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TC_Overall_r = $TC_Overall;
					$TC_Overall_r =  $TC_Overall;
				}
				else
				{
					$running_TC_Overall = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TC_Overall =$running_TC_Overall+$exist->TC_Overall;
					}
					$rmProfile->TC_Overall_r = $running_TC_Overall+$TC_Overall;
					$TC_Overall_r = $running_TC_Overall+$TC_Overall;
				}
				
				/*
				*runining  coding TC_Overall
				*/
				
				
				/*
				*runining  coding TLoan_by_Loans_Team
				*/
				$TLoan_by_Loans_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TLoan_by_Loans_Team_r = $TLoan_by_Loans_Team;
					$TLoan_by_Loans_Team_r =  $TLoan_by_Loans_Team;
				}
				else
				{
					$running_TLoan_by_Loans_Team = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TLoan_by_Loans_Team =$running_TLoan_by_Loans_Team+$exist->TLoan_by_Loans_Team;
					}
					$rmProfile->TLoan_by_Loans_Team_r = $running_TLoan_by_Loans_Team+$TLoan_by_Loans_Team;
					$TLoan_by_Loans_Team_r = $running_TLoan_by_Loans_Team+$TLoan_by_Loans_Team;
				}
				
				/*
				*runining  coding TLoan_by_Loans_Team
				*/
				
				/*
				*runining  coding TLoan_by_Cards_Team
				*/
				$TLoan_by_Cards_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TLoan_by_Cards_Team_r = $TLoan_by_Cards_Team;
					$TLoan_by_Cards_Team_r =  $TLoan_by_Cards_Team;
				}
				else
				{
					$running_TLoan_by_Cards_Team = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TLoan_by_Cards_Team =$running_TLoan_by_Cards_Team+$exist->TLoan_by_Cards_Team;
					}
					$rmProfile->TLoan_by_Cards_Team_r = $running_TLoan_by_Cards_Team+$TLoan_by_Cards_Team;
					$TLoan_by_Cards_Team_r = $running_TLoan_by_Cards_Team+$TLoan_by_Cards_Team;
				}
				
				/*
				*runining  coding TLoan_by_Cards_Team
				*/
				
				/*
				*runining  coding TLoan_overall
				*/
				$TLoan_overall_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TLoan_overall_r = $TLoan_overall;
					$TLoan_overall_r =  $TLoan_overall;
				}
				else
				{
					$running_TLoan_overall = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TLoan_overall =$running_TLoan_overall+$exist->TLoan_overall;
					}
					$rmProfile->TLoan_overall_r = $running_TLoan_overall+$TLoan_overall;
					$TLoan_overall_r = $running_TLoan_overall+$TLoan_overall;
				}
				
				/*
				*runining  coding TLoan_overall
				*/
				/*
				*Card Productivity runining
				*/
				$loanHC_r = $TL_PL_HC_r+$TL_PL_HC_r+$TL_ML_HC_r;
				if($TC_by_Cards_Team_r != 0)
				{
				$Cards_Team_Card_Productivity_r = round($TC_by_Cards_Team_r/$TL_Card_HC_r,2);
				}
				else
				{
					$Cards_Team_Card_Productivity_r = 0;
				}
				if($TC_by_Loans_Team_r != 0)
				{
				$Loans_Team_Card_Productivity_r = round($TC_by_Loans_Team_r/$loanHC_r,2);
				}
				else
				{
					$Loans_Team_Card_Productivity_r = 0;
				}
				$Cards_Overall_Productivity_r = round($TC_Overall_r/$TL_Head_Count_r,2);
				
				$rmProfile->Cards_Team_Card_Productivity_r = $Cards_Team_Card_Productivity_r;
				$rmProfile->Loans_Team_Card_Productivity_r = $Loans_Team_Card_Productivity_r;
				$rmProfile->Cards_Overall_Productivity_r = $Cards_Overall_Productivity_r;
				/*
				*Card Productivity runining
				*/
				
				
				/*
				*runining code TR_by_Loans_Team
				*/
				$TR_by_Loans_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TR_by_Loans_Team_r = $TR_by_Loans_Team;
					$TR_by_Loans_Team_r =  $TR_by_Loans_Team;
				}
				else
				{
					$running_TR_by_Loans_Team_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TR_by_Loans_Team_r =$running_TR_by_Loans_Team_r+$exist->TR_by_Loans_Team;
					}
					$rmProfile->TR_by_Loans_Team_r = $running_TR_by_Loans_Team_r+$TR_by_Loans_Team;
					$TR_by_Loans_Team_r = $running_TR_by_Loans_Team_r+$TR_by_Loans_Team;
				}
				/*
				*runining code TR_by_Loans_Team
				*/
				
				
				/*
				*runining code TR_by_Cards_Team
				*/
				$TR_by_Cards_Team_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TR_by_Cards_Team_r = $TR_by_Cards_Team;
					$TR_by_Cards_Team_r =  $TR_by_Cards_Team;
				}
				else
				{
					$running_TR_by_Cards_Team_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TR_by_Cards_Team_r =$running_TR_by_Cards_Team_r+$exist->TR_by_Cards_Team;
					}
					$rmProfile->TR_by_Cards_Team_r = $running_TR_by_Cards_Team_r+$TR_by_Cards_Team;
					$TR_by_Cards_Team_r = $running_TR_by_Cards_Team_r+$TR_by_Cards_Team;
				}
				/*
				*runining code TR_by_Cards_Team
				*/
				
				
				/*
				*runining code TR_Overall
				*/
				$TR_Overall_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->TR_Overall_r = $TR_Overall;
					$TR_Overall_r =  $TR_Overall;
				}
				else
				{
					$running_TR_Overall_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_TR_Overall_r =$running_TR_Overall_r+$exist->TR_Overall;
					}
					$rmProfile->TR_Overall_r = $running_TR_Overall_r+$TR_Overall;
					$TR_Overall_r = $running_TR_Overall_r+$TR_Overall;
				}
				/*
				*runining code TR_by_Cards_Team
				*/
				if($TLoan_by_Loans_Team_r != 0)
				{
				$Loans_Team_Loan_Prod_r = round($TLoan_by_Loans_Team_r/$loanHC_r,2);
				}
				else
				{
					$Loans_Team_Loan_Prod_r = 0;
				}
				
				if($TLoan_by_Cards_Team_r != 0)
				{
				$Cards_Team_Loan_Prod_r = round($TLoan_by_Cards_Team_r/$TL_Card_HC_r,2);
				}
				else
				{
					$Cards_Team_Loan_Prod_r = 0;
				}
				$Loan_Overall_Team_Prod_r = round($TLoan_overall_r/$TL_Head_Count_r,2);
				$rmProfile->Loans_Team_Loan_Prod_r = $Loans_Team_Loan_Prod_r;
				$rmProfile->Cards_Team_Loan_Prod_r = $Cards_Team_Loan_Prod_r;
				$rmProfile->Loan_Overall_Team_Prod_r = $Loan_Overall_Team_Prod_r;
				/*
				*runining code top_20
				*/
				$top_20_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->top_20_r = $top20;
					$top_20_r =  $top20;
				}
				else
				{
					$running_top_20_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_top_20_r =$running_top_20_r+$exist->top_20;
					}
					$rmProfile->top_20_r = $running_top_20_r+$top20;
					$top_20_r = $running_top_20_r+$top20;
				}
				/*
				*runining code top_20
				*/
				
				/*
				*runining code top_20_50
				*/
				$top_20_50_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->top_20_50_r = $top_20_50;
					$top_20_50_r =  $top_20_50;
				}
				else
				{
					$running_top_20_50_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_top_20_50_r =$running_top_20_50_r+$exist->top_20_50;
					}
					$rmProfile->top_20_50_r = $running_top_20_50_r+$top_20_50;
					$top_20_50_r = $running_top_20_50_r+$top_20_50;
				}
				/*
				*runining code top_20_50
				*/
				
				/*
				*runining code top_50_80
				*/
				$top_50_80_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->top_50_80_r = $top_50_80;
					$top_50_80_r =  $top_50_80;
				}
				else
				{
					$running_top_50_80_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_top_50_80_r =$running_top_50_80_r+$exist->top_50_80;
					}
					$rmProfile->top_50_80_r = $running_top_50_80_r+$top_50_80;
					$top_50_80_r = $running_top_50_80_r+$top_50_80;
				}
				/*
				*runining code top_50_80
				*/
				
				/*
				*runining code top_80_100
				*/
				$top_80_100_r = 0;
				if($Sales_Time_Count == 1)
				{
					$rmProfile->top_80_100_r = $top_80_100;
					$top_80_100_r =  $top_80_100;
				}
				else
				{
					$running_top_80_100_r = 0;
					foreach($existDetailsList as $exist)
					{
						$running_top_80_100_r =$running_top_80_100_r+$exist->top_80_100;
					}
					$rmProfile->top_80_100_r = $running_top_80_100_r+$top_80_100;
					$top_80_100_r = $running_top_80_100_r+$top_80_100;
				}
				/*
				*runining code top_80_100
				*/
				
				$rmProfile->cards_count = $totalCard;
				$rmProfile->Loan_Amt = $TLoan_overall;
				$rmProfile->Converted_performance = $TC_Overall_converted;
				/*
				*running Converted_performance
				*/
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Runining_Converted_performance = $TC_Overall_converted;
						$runingCPerformance = $TC_Overall_converted;
					}
					else
					{
						$running_cP = 0;
						foreach($existDetailsList as $exist)
						{
							$running_cP =$running_cP+$exist->Converted_performance;
						}
						$rmProfile->Runining_Converted_performance = $running_cP+$TC_Overall_converted;
						$runingCPerformance = $running_cP+$TC_Overall_converted;
					}
				
				/*
				*running Converted_performance
				*/
				
				
			/*
			*Cumulative Card coding
			*Start Code
			*/
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Cumulative_Card = $totalCard;
				$Cumulative_Card = $totalCard;
			}
            else
			{
				$running_cc = 0;
				foreach($existDetailsList as $exist)
				{
					$running_cc =$running_cc+$exist->cards_count;
				}
				$rmProfile->Cumulative_Card = $running_cc+$totalCard;
				$Cumulative_Card = $running_cc+$totalCard;
			}
			
			/*
			*Cumulative Card coding
			*End Code
			*/
			
			
			/**
			*target COding
			*/
			$rmProfile->target = $totalTarget;
			$rmProfile->target_achieved = $totalCard;
			if(($totalTarget != 0 && $totalTarget != NULL) && ($totalCard != 0 && $totalCard != NULL))
			{
				$rmProfile->target_achieved_percentage = round(($totalCard/$totalTarget),2);
			}
			else
			{
				$rmProfile->target_achieved_percentage = 0;
			}
			
			
		
			if($Sales_Time_Count == 1)
			{
				$rmProfile->target_r = $totalTarget;
				$rmProfile->target_achieved_r = $totalCard;
				if(($totalTarget != 0 && $totalTarget != NULL) && ($totalCard != 0 && $totalCard != NULL))
				{
					$rmProfile->target_achieved_percentage_r = round(($totalCard/$totalTarget),2);
				}
				else
				{
					$rmProfile->target_achieved_percentage_r = 0;
				}
				
			}
            else
			{
				$runingtarget = 0;
				$runingtargetA = 0;
				foreach($existDetailsList as $exist)
				{
					$runingtarget =$runingtarget+$exist->target;
					$runingtargetA =$runingtargetA+$exist->target_achieved;
				}
				$rmProfile->target_r = $runingtarget+$totalTarget;
				$targetRF = $runingtarget+$totalTarget;
				$rmProfile->target_achieved_r = $runingtargetA+$totalCard;
				$targetRFA = $runingtargetA+$totalCard;
				if($targetRF != 0 && $targetRFA != 0)
				{
					$rmProfile->target_achieved_percentage_r = round(($targetRF/$targetRFA),2);
				}
				else
				{
					$rmProfile->target_achieved_percentage_r = 0;
				}
			}
			/**
			*target Coding
			*/
			
			/**
			*Running Salary Coding
			*/
			
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Running_Salary = $Salary;
				$Running_Salary = $Salary;
			}
            else
			{
				$running_salary = 0;
				foreach($existDetailsList as $exist)
				{
					$running_salary =$running_salary+$exist->Salary;
				}
				$rmProfile->Running_Salary = $running_salary+$Salary;
				$Running_Salary = $running_salary+$Salary;
			}
			/**
			*Running Salary Coding
			*/
				
				/*
			*Distribution_Cost
			*/
			if($totalCard != 0)
			{
			$Distribution_Cost = $Salary/$TC_Overall;
			$rmProfile->Distribution_Cost = round($Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Distribution_Cost = 0;
			}
			
			if($runingCPerformance != 0)
			{
			$Running_Distribution_Cost = $Running_Salary/$TC_Overall_r;
			$rmProfile->Running_Distribution_Cost = round($Running_Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Running_Distribution_Cost = 0;
			}
			/*
			*Distribution_Cost
			*/
			
			
			
			/**
			*Running Salary Coding
			*/
			$rmProfile->excess = $excess;
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Running_Excess = $excess;
				$Running_Excess = $excess;
			}
            else
			{
				$running_excess_r = 0;
				foreach($existDetailsList as $exist)
				{
					$running_excess_r =$running_excess_r+$exist->excess;
				}
				$rmProfile->Running_Excess = $running_excess_r+$excess;
				$Running_Excess = $running_excess_r+$excess;
			}
			/**
			*Running Salary Coding
			*/
			
			
			/*
			*Mass,P and SP logic
			*/
			if($totalCard != 0 && $Mass != 0)
			{
				$rmProfile->Mass_percentage = round($Mass/$totalCard,2);
			}
			else
			{
				$rmProfile->Mass_percentage = 0;
			}
			
			if($totalCard != 0 && $Premium != 0)
			{
				$rmProfile->Premium_percentage = round($Premium/$totalCard,2);
			}
			else
			{
				$rmProfile->Premium_percentage = 0;
			}
			
			if($totalCard != 0 && $SP != 0)
			{
				$rmProfile->SP_percentage = round($SP/$totalCard,2);
			}
			else
			{
				$rmProfile->SP_percentage = 0;
			}
			
			
			
			
					if($Sales_Time_Count == 1)
					{
						if($Mass != 0 && $totalCard !=0)
						{
						$rmProfile->Mass_percentage_r = round($Mass/$totalCard,2);
						}
						else
						{
							$rmProfile->Mass_percentage_r = 0;
						}
					}
					else
					{
						$massPR = 0;
						foreach($existDetailsList as $exist)
						{
							$massPR =$massPR+$exist->Mass;
						}
					  if($Cumulative_Card != 0 && $Mass != 0)
						{
							$completeMass = $massPR+$Mass;
						$rmProfile->Mass_percentage_r = round($completeMass/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->Mass_percentage_r = round($massPR/$Cumulative_Card,2);
							}
							else
							{
									$rmProfile->Mass_percentage_r = 0;
							}
						}
					}
					
					
					if($Sales_Time_Count == 1)
					{
						if($Premium != 0 && $totalCard !=0)
						{
						$rmProfile->Premium_percentange_r = round($Premium/$totalCard,2);
						}
						else
						{
							$rmProfile->Premium_percentange_r =0;
						}
					}
					else
					{
						$premiumPR = 0;
						foreach($existDetailsList as $exist)
						{
							$premiumPR =$premiumPR+$exist->Premium;
						}
						if($Premium != 0 && $Cumulative_Card != 0)
						{
							$completePremium = $premiumPR+$Premium;
							$rmProfile->Premium_percentange_r = round($completePremium/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->Premium_percentange_r = round($premiumPR/$Cumulative_Card,2);
							}
							else
							{
								$rmProfile->Premium_percentange_r = 0;	
							}
						}
					}
					
					
					if($Sales_Time_Count == 1)
					{
						if($SP != 0 && $totalCard !=0)
						{
						$rmProfile->SP_percentage_r = round($SP/$totalCard,2);
						}
						else
						{
							$rmProfile->SP_percentage_r = 0;
						}
					}
					else
					{
						$massPR1 = 0;
						foreach($existDetailsList as $exist)
						{
							$massPR1 =$massPR1+$exist->SP;
						}
						if($SP !=0 && $Cumulative_Card != 0)
						{
							$completeSP = $massPR1+$SP;
						$rmProfile->SP_percentage_r = round($completeSP/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->SP_percentage_r = round($massPR1/$Cumulative_Card,2);
							}
							else
							{
								$rmProfile->SP_percentage_r = 0;
							}
						}
					}
			/*
			*Mass,P and SP logic
			*/
			/*
			*Approval Code
            */
	
			$rmProfile->submission_count = $submission_count;
			$rmProfile->submission_end_count = $submissionDetailsEnd;
			$rmProfile->Approval_Rate = $Approval_Rate;
					
					$rmProfile->Sal_less_5k = $col5;
				$rmProfile->Sal_5_10k = $col5to10;
				$rmProfile->Sal_10k_15k = $col10to15;
				$rmProfile->Sal_15k_25k = $col15to25;
				$rmProfile->Sal_greater_25k = $col25;
				
				$rmProfile->Sal_less_5k_end = $col5end;
				$rmProfile->Sal_5_10k_end = $col5to10end;
				$rmProfile->Sal_10k_15k_end = $col10to15end;
				$rmProfile->Sal_15k_25k_end = $col15to25end;
				$rmProfile->Sal_greater_25k_end = $col25end;
				
				
				if($col5end !=0 && $submissionDetailsEnd != 0)
				{
				$rmProfile->Sal_less_5k_approval = round($col5end/$col5,2);
				}
				else
				{
					$rmProfile->Sal_less_5k_approval = 0;
				}
				
				if($col5to10end !=0 && $submissionDetailsEnd != 0)
				{
				$rmProfile->Sal_5_10k_approval = round($col5to10end/$col5to10,2);
				}
				else
				{
					$rmProfile->Sal_5_10k_approval  =0;
				}
				
				if($col10to15end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_10k_15k_approval = round($col10to15end/$col10to15,2);
				}
				else
				{
					$rmProfile->Sal_10k_15k_approval =0;
				}
				
				if($col15to25end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_15k_25k_approval = round($col15to25end/$col15to25,2);
				}
				else 
				{
					$rmProfile->Sal_15k_25k_approval =0;
				}
				
				if($col25end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_greater_25k_approval = round($col25end/$col25,2);
				}
				else
				{
					$rmProfile->Sal_greater_25k_approval =0;
				}
				
				/*
				*Card Per Appoval Rate
				*
				*/
				
				
					if($massSubmissionCountEND != 0 && $massSubmissionCount != 0)
				{
					$massSubmissionApproval = round($massSubmissionCountEND/$massSubmissionCount,2);
				}
				else
				{
					$massSubmissionApproval = 0;
				}
				
				if($pSubmissionCountEND != 0 && $pSubmissionCount != 0)
				{
				
				$pSubmissionApproval = round($pSubmissionCountEND/$pSubmissionCount,2);
				
				}
				else
				{
					$pSubmissionApproval = 0;
				}
				
				if($SpSubmissionCountEND != 0 && $SpSubmissionCount != 0)
				{
				$SpSubmissionApproval = round($SpSubmissionCountEND/$SpSubmissionCount,2);
				}
				else
				{
					$SpSubmissionApproval =0;
				}
				
				$rmProfile->Mass_submission = $massSubmissionCount;
				$rmProfile->Mass_submission_end = $massSubmissionCountEND;
				$rmProfile->Mass_approval_rate = $massSubmissionApproval;
				
				
				
				$rmProfile->Premium_submission = $pSubmissionCount;
				$rmProfile->Premium_submission_end = $pSubmissionCountEND;
				$rmProfile->Premium_approval_rate = $pSubmissionApproval;
				
				
				
				$rmProfile->SP_submission = $SpSubmissionCount;
				$rmProfile->SP_submission_end = $SpSubmissionCountEND;
				$rmProfile->SP_approval_rate = $SpSubmissionApproval;
				$rmProfile->submission_end_count = $submissionDetailsEnd;
				/*
				*Card Per Appoval Rate
				*
				*/
				$aleApprovalRate = 0;
				if($aleSubmissionCount != 0 && $aleSubmissionCountEnd != 0)
				{
					$aleApprovalRate =  round($aleSubmissionCountEnd/$aleSubmissionCount,2);
				}
				
				
				$naleApprovalRate = 0;
				if($naleSubmissionCount != 0 && $naleSubmissionCountEnd != 0)
				{
					$naleApprovalRate =  round($naleSubmissionCountEnd/$naleSubmissionCount,2);
				}
				
				$rmProfile->ale = $aleSubmissionCount;
				$rmProfile->nale = $naleSubmissionCount;
				$rmProfile->ale_end = $aleSubmissionCountEnd;
				$rmProfile->nale_end = $naleSubmissionCountEnd;
				$rmProfile->ale_approval_rate = $aleApprovalRate;
				$rmProfile->nale_approval_rate = $naleApprovalRate;
			/*
			*Approval Code
            */			
			
			
			/*
				*runing Ale
				*/
				$aleRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->ale_r =  $aleSubmissionCount;
						$aleRuning = $aleSubmissionCount;
						
					}
					else
					{
						$aler = 0;
						foreach($existDetailsList as $exist)
						{
							$aler =$aler+$exist->ale;
						}
						$rmProfile->ale_r =$aler+$aleSubmissionCount;
						$aleRuning = $aler+$aleSubmissionCount;
					}
				
				/*
				*runing Ale
				*/
				/*
				*runing Nale
				*/
				$naleRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->nale_r =  $naleSubmissionCount;
						$naleRuning = $naleSubmissionCount;
						
					}
					else
					{
						$naler = 0;
						foreach($existDetailsList as $exist)
						{
							$naler =$naler+$exist->nale;
						}
						$rmProfile->nale_r =$naler+$naleSubmissionCount;
						$naleRuning = $naler+$naleSubmissionCount;
					}
				
				/*
				*runing Nale
				*/
				
				/*
				*runing Ale End
				*/
				$aleEndRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->ale_end_r =  $aleSubmissionCountEnd;
						$aleEndRuning = $aleSubmissionCountEnd;
						
					}
					else
					{
						$aleEndr = 0;
						foreach($existDetailsList as $exist)
						{
							$aleEndr =$aleEndr+$exist->ale_end;
						}
						$rmProfile->ale_end_r =$aleEndr+$aleSubmissionCountEnd;
						$aleEndRuning = $aleEndr+$aleSubmissionCountEnd;
					}
				
				/*
				*runing Ale End
				*/
				
				
				
				/*
				*runing Nale End
				*/
				$naleEndRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->nale_end_r =  $naleSubmissionCountEnd;
						$naleEndRuning = $naleSubmissionCountEnd;
						
					}
					else
					{
						$naleEndr = 0;
						foreach($existDetailsList as $exist)
						{
							$naleEndr =$naleEndr+$exist->nale_end;
						}
						$rmProfile->nale_end_r =$naleEndr+$naleSubmissionCountEnd;
						$naleEndRuning = $naleEndr+$naleSubmissionCountEnd;
					}
				
				/*
				*runing Nale End
				*/
				
				/*
				*runing Ale approval rate
				*/
				if($aleRuning != 0 && $aleEndRuning != 0)
				{
					$rmProfile->ale_approval_rate_r = round($aleEndRuning/$aleRuning,2);
				}
				else
				{
					$rmProfile->ale_approval_rate_r = 0;
				}
				
				/*
				*runing Ale approval rate
				*/
				
				
				/*
				*runing Nale approval rate
				*/
				if($naleRuning != 0 && $naleEndRuning != 0)
				{
					$rmProfile->nale_approval_rate_r = round($naleEndRuning/$naleRuning,2);
				}
				else
				{
					$rmProfile->nale_approval_rate_r = 0;
				}
				
				/*
				*runing Nale approval rate
				*/
					/*
				*runing salary 
				*submission
				*/
				
				$col5R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_less_5k_r =  $col5;
						$col5R = $col5;
						
					}
					else
					{
						$col5V = 0;
						foreach($existDetailsList as $exist)
						{
							$col5V =$col5V+$exist->Sal_less_5k;
						}
						$rmProfile->Sal_less_5k_r =$col5V+$col5;
						$col5R = $col5V+$col5;
					}
				
				/*
				*
				*/
				
				$col5to10R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_5_10k_r =  $col5to10;
						$col5to10R = $col5to10;
						
					}
					else
					{
						$col5to10V = 0;
						foreach($existDetailsList as $exist)
						{
							$col5to10V =$col5to10V+$exist->Sal_5_10k;
						}
						$rmProfile->Sal_5_10k_r =$col5to10V+$col5to10;
						$col5to10R = $col5to10V+$col5to10;
					}
					
				/*
				*
				*/
				$col10to15R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_10k_15k_r =  $col10to15;
						$col10to15R = $col10to15;
						
					}
					else
					{
						$col10to15V = 0;
						foreach($existDetailsList as $exist)
						{
							$col10to15V =$col10to15V+$exist->Sal_10k_15k;
						}
						$rmProfile->Sal_10k_15k_r =$col10to15V+$col10to15;
						$col10to15R = $col10to15V+$col10to15;
					}
					
				/*
				*
				*/	
					
				$col15to25R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_15k_25k_r =  $col15to25;
						$col15to25R = $col15to25;
						
					}
					else
					{
						$col15to25V = 0;
						foreach($existDetailsList as $exist)
						{
							$col15to25V =$col15to25V+$exist->Sal_15k_25k;
						}
						$rmProfile->Sal_15k_25k_r =$col15to25V+$col15to25;
						$col15to25R = $col15to25V+$col15to25;
					}	
				/*
				*
				*/	
					
				$col25R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_greater_25k_r =  $col25;
						$col25R = $col25;
						
					}
					else
					{
						$col25V = 0;
						foreach($existDetailsList as $exist)
						{
							$col25V =$col25V+$exist->Sal_greater_25k;
						}
						$rmProfile->Sal_greater_25k_r =$col25V+$col25;
						$col25R = $col25V+$col25;
					}	
				/*
				*runing salary Submission
				*/
				
				
				
				
				/*
				*runing salary 
				*End
				*/
				
				$col5Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_less_5k_end_r =  $col5end;
						$col5Rend = $col5end;
						
					}
					else
					{
						$col5Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col5Vend =$col5Vend+$exist->Sal_less_5k_end;
						}
						$rmProfile->Sal_less_5k_end_r =$col5Vend+$col5end;
						$col5Rend = $col5Vend+$col5end;
					}
				
				/*
				*
				*/
				
				$col5to10Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_5_10k_end_r =  $col5to10end;
						$col5to10Rend = $col5to10end;
						
					}
					else
					{
						$col5to10Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col5to10Vend =$col5to10Vend+$exist->Sal_5_10k_end;
						}
						$rmProfile->Sal_5_10k_end_r =$col5to10Vend+$col5to10end;
						$col5to10Rend = $col5to10Vend+$col5to10end;
					}
					
				/*
				*
				*/
				$col10to15Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_10k_15k_end_r =  $col10to15end;
						$col10to15Rend = $col10to15end;
						
					}
					else
					{
						$col10to15Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col10to15Vend =$col10to15Vend+$exist->Sal_10k_15k_end;
						}
						$rmProfile->Sal_10k_15k_end_r =$col10to15Vend+$col10to15end;
						$col10to15Rend = $col10to15Vend+$col10to15end;
					}
					
				/*
				*
				*/	
					
				$col15to25Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_15k_25k_end_r =  $col15to25end;
						$col15to25Rend = $col15to25end;
						
					}
					else
					{
						$col15to25Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col15to25Vend =$col15to25Vend+$exist->Sal_15k_25k_end;
						}
						$rmProfile->Sal_15k_25k_end_r =$col15to25Vend+$col15to25end;
						$col15to25Rend = $col15to25Vend+$col15to25end;
					}	
				/*
				*
				*/	
					
				$col25Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_greater_25k_end_r =  $col25end;
						$col25Rend = $col25end;
						
					}
					else
					{
						$col25Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col25Vend =$col25Vend+$exist->Sal_greater_25k_end;
						}
						$rmProfile->Sal_greater_25k_end_r =$col25Vend+$col25end;
						$col25Rend = $col25Vend+$col25end;
					}	
				/*
				*runing salary less than 5K
				*/
				
				/*
				*runing salary approval 
				*/
				if($col5Rend !=0 )
				{
				$rmProfile->Sal_less_5k_approval_r = round($col5Rend/$col5R,2);
				}
				else
				{
					$rmProfile->Sal_less_5k_approval_r = 0;
				}
				
				if($col5to10Rend !=0 )
				{
				$rmProfile->Sal_5_10k_approval_r = round($col5to10Rend/$col5to10R,2);
				}
				else
				{
					$rmProfile->Sal_5_10k_approval_r  =0;
				}
				
				if($col10to15Rend !=0)
				{
					$rmProfile->Sal_10k_15k_approval_r = round($col10to15Rend/$col10to15R,2);
				}
				else
				{
					$rmProfile->Sal_10k_15k_approval_r =0;
				}
				
				if($col15to25Rend !=0)
				{
					$rmProfile->Sal_15k_25k_approval_r = round($col15to25Rend/$col15to25R,2);
				}
				else 
				{
					$rmProfile->Sal_15k_25k_approval_r =0;
				}
				
				if($col25Rend !=0 )
				{
					$rmProfile->Sal_greater_25k_approval_r = round($col25Rend/$col25R,2);
				}
				else
				{
					$rmProfile->Sal_greater_25k_approval_r =0;
				}
				/*
				*runing salary approval
				*/
				//$rmProfile->Justify_Salary_Status=1;
				$rmProfile->sales_month=$enbdAgentList->month;
				$rmProfile->sales_year=$enbdAgentList->year;
				AgentPayout::where("tl_name",$tlName)->where("sales_time",$salesTimeTL)->update(["performace_tl_status" => 2]);
			
				
				$rmProfile->save();
				echo "DONE";
			
				exit;
				
			}
		else
		{
			echo "all DONE";
			exit;
		}
	}


public function updateLoanAgent()
{
	$enbdAgentList = AgentPayout::where("agent_product","!=","CARD")->get();

	foreach($enbdAgentList as $agent)
	{
		$agentUpdate = AgentPayout::find($agent->id);
		$agentUpdate->tc_card =$agent->mass+$agent->premium+$agent->super_premium;
		$cards = $agent->mass+$agent->premium+$agent->super_premium;
		$final_loan_amount = $agent->final_loan_amount;
		$cardsC = floor($final_loan_amount/50000);
		
		$agentUpdate->tc_final = $cards+$cardsC;
		
		$agentUpdate->save();
	}
	echo "done";
	exit;
}	

public function JustifySalaryUpdate()
	{
		$justifyModel = TLAnalysisPerformanceEnbd::where("Justify_Salary_Status",1)->orderBy("sales_count","ASC")->first();
		
		if($justifyModel != '')
		{
			$Justify_SE = 0;
			$Justify_SE_Not = 0;
			$sales_time = $justifyModel->sales_time;
			$tl_name = $justifyModel->tl_name;
			$getDetails = AgentPayout::where("tl_name",$tl_name)->where("sales_time",$sales_time)->orderBy("vintage","ASC")->get();
			foreach($getDetails as $tl)
				{
					if($tl->agent_product == 'CARD')
					{
						if($tl->agent_target != 0 && $tl->tc_card != 0)
						{
							if($tl->agent_target <= $tl->tc_card)
							{
								$Justify_SE++;
							}
							else
							{
								$Justify_SE_Not++;
							}
						}
						else
						{
							$Justify_SE_Not++;
						}
					}
					else
					{
						if($tl->agent_target != 0 && $tl->final_loan_amount != 0)
						{
							if($tl->agent_target <= $tl->final_loan_amount)
							{
								$Justify_SE++;
							}
							else
							{
								$Justify_SE_Not++;
							}
						}
						else
						{
							$Justify_SE_Not++;
						}
					}
				}
				
				
				/*
				*update $justify SE or Not Justify SE
				*/
				$TL_Head_Count = $justifyModel->TL_Head_Count;
				$sales_count = $justifyModel->sales_count;
				$updateTLA = TLAnalysisPerformanceEnbd::find($justifyModel->id);
				if($Justify_SE != 0)
				{
					$updateTLA->Justify_Salary_SE_percentange = round($Justify_SE/$TL_Head_Count,2);
				}
				else
				{
					$updateTLA->Justify_Salary_SE_percentange = 0;
				}
				
				if($Justify_SE_Not != 0)
				{
					$updateTLA->Not_Justify_Salary_SE_percentange = round($Justify_SE_Not/$TL_Head_Count,2);
				}
				else
				{
					$updateTLA->Not_Justify_Salary_SE_percentange = 0;
				}
				$updateTLA->Justify_Salary_SE = $Justify_SE;
				$updateTLA->Not_Justify_Salary_SE = $Justify_SE_Not;
				/*
				*update $justify SE or Not Justify SE
				*/
				if($sales_count == 1)
				{
					if($Justify_SE != 0)
					{
						$updateTLA->Justify_Salary_SE_percentange_r = round($Justify_SE/$TL_Head_Count,2);
					}
					else
					{
						$updateTLA->Justify_Salary_SE_percentange_r = 0;
					}
					
					if($Justify_SE_Not != 0)
					{
						$updateTLA->Not_Justify_Salary_SE_percentange_r = round($Justify_SE_Not/$TL_Head_Count,2);
					}
					else
					{
						$updateTLA->Not_Justify_Salary_SE_percentange_r = 0;
					}
					$updateTLA->Justify_Salary_SE_r = $Justify_SE;
					$updateTLA->Not_Justify_Salary_SE_r = $Justify_SE_Not;
				}
				else
				{
					$Justify_Salary_SE_r = 0;
					$Not_Justify_Salary_SE_r = 0;
					$TL_Head_Count_r = 0;
					
					for($i= 1;$i<$sales_count;$i++)
					{
						$data = TLAnalysisPerformanceEnbd::where("sales_count",$i)->where("tl_name",$tl_name)->first();
						if($data != '')
						{
							$Justify_Salary_SE_r = $Justify_Salary_SE_r+$data->Justify_Salary_SE;
							$Not_Justify_Salary_SE_r = $Not_Justify_Salary_SE_r+$data->Not_Justify_Salary_SE;
							$TL_Head_Count_r = $TL_Head_Count_r+$data->TL_Head_Count;
						}
					}
					$Justify_Salary_SE_r  = $Justify_Salary_SE_r+$Justify_SE;
					$Not_Justify_Salary_SE_r  = $Not_Justify_Salary_SE_r +$Justify_SE_Not;
					$TL_Head_Count_r = $TL_Head_Count_r+$TL_Head_Count;
					
					if($Justify_Salary_SE_r != 0)
					{
						$updateTLA->Justify_Salary_SE_percentange_r = round($Justify_Salary_SE_r/$TL_Head_Count_r,2);
					}
					else
					{
						$updateTLA->Justify_Salary_SE_percentange_r = 0;
					}
					
					if($Not_Justify_Salary_SE_r != 0)
					{
						$updateTLA->Not_Justify_Salary_SE_percentange_r = round($Not_Justify_Salary_SE_r/$TL_Head_Count_r,2);
					}
					else
					{
						$updateTLA->Not_Justify_Salary_SE_percentange_r = 0;
					}
					
					
					$updateTLA->Justify_Salary_SE_r = $Justify_Salary_SE_r;
					$updateTLA->Not_Justify_Salary_SE_r = $Not_Justify_Salary_SE_r;
					
				}
				$updateTLA->Justify_Salary_Status = 2;
				$updateTLA->save();
				echo "updated";
				exit;
		}
		else
		{
			echo "All Done";
			exit;
		}
		echo '<pre>';
		print_r($justifyModel);
		exit;
	}
	
	public function dataCutEndReport()
	{
		
		$salesTimeDatas = AgentPayout::groupBy('sales_time')->selectRaw('count(*) as total, sales_time')->get();
	/* 	echo '<pre>';
		print_r($salesTimeDatas);
		exit; */
		foreach($salesTimeDatas as $saleTime)
		{
			$_saleTime = $saleTime->sales_time;
			$total_cards_payout = 0;
			$total_card_datacut = 0;
			$total_card_internal_datacut = 0;
			
			$agentDatas = AgentPayout::where("sales_time",$_saleTime)->where("agent_product","CARD")->get();
			foreach($agentDatas as $_agent)
			{
				$total_cards_payout = $total_cards_payout+$_agent->tc_card;
			}
			$salesTimeArray = explode("-",$_saleTime);
				$monthP = sprintf("%02d", $salesTimeArray[0]);
				$salesTimeNew  = $monthP.'-'.$salesTimeArray[1];
			$total_card_datacut = DatacutInformation::where("sales_time",$salesTimeNew)->get()->count();
			$total_card_internal_datacut = EnbdFinalMisCompletebothCreditCards::where("end_sales_time",$salesTimeNew)->where("match_datacut",2)->get()->count();
			$saveCreditCardReport = new CreditCardEndReport();
			$saveCreditCardReport->total_cards_payout = $total_cards_payout;
			$saveCreditCardReport->total_card_datacut = $total_card_datacut;
			$saveCreditCardReport->total_card_internal_datacut = $total_card_internal_datacut;
			$saveCreditCardReport->sales_time = $_saleTime;
			$saveCreditCardReport->agent_count_payout = $saleTime->total;
			$saveCreditCardReport->save();
		}
		echo "DONE";
		exit;
	}
}