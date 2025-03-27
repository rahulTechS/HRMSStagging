<?php

namespace App\Http\Controllers\SEPayout;

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
use App\Models\SEPayout\AgentPayout;
use App\Models\SEPayout\AgentPayoutMidPoint;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\AgentPayoutDeem;




class VintageController extends Controller
{
  
			
			
			/* public function calculateVintageAgent()
			{
				$agentPayoutMod = AgentPayout::where("agent_bank_code","!=","Not Generated")->get();
				
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
					$sourceCode = trim($payout->agent_bank_code);
					if($sourceCode != '' && $sourceCode != NULL)
					{
						$employeeData = Employee_details::where("source_code",$sourceCode)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("dept_id",$deptId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTime = $payout->sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									
									
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = AgentPayout::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->match_employee = 2;
									$agentPUpdate->save();
									
								}
							}								
							
						}
					}
				}
				echo "done";
				exit;
			} */
			
			
			public function calculateVintageAgent()
			{
				$agentPayoutMod = AgentPayoutDeem::where("employee_id_status",2)->get();
				
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
							$empId = $payout->emp_id;
							
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTime = $payout->sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									
									
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = AgentPayoutDeem::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->match_employee = 2;
									$agentPUpdate->save();
									
								}
							}								
							
					
					
				}
				echo "done";
				exit;
			} 
			
			
			
			public function calculateVintageAgentMashreq()
			{
				
				//$agentPayoutMod = AgentPayoutMashreq::where("doj","!=","not found")->get();
				$agentPayoutMod = AgentPayoutMashreq::where("dob_get_status",4)->get();
/* 				echo count($agentPayoutMod);
				exit;*
				 echo '<pre>';
				print_r($agentPayoutMod);
				exit;   */
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
					$doj = $payout->doj;
					if($doj != '' && $doj != NULL)
					{
						
							
								$salesTime = $payout->end_sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								
									//$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = AgentPayoutMashreq::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									/* echo $payout->id;
									echo "<br/>";
									echo $daysInterval;
									exit; */
									
									$agentPUpdate->match_employee = 2;
									$agentPUpdate->save();
									
								
															
							
						
					}
				}
				echo "done";
				exit;
			}
					
			public function calculateVintageAgentDeem()
			{
				$agentPayoutMod = AgentPayoutDeem::where("doj","!=","not_found")->get();
				/* echo '<pre>';
				print_r($agentPayoutMod);
				exit;  */
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
					$doj = $payout->doj;
					if($doj != '' && $doj != NULL)
					{
						
							
								$salesTime = $payout->sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								
									//$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = AgentPayoutDeem::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									/* echo $payout->id;
									echo "<br/>";
									echo $daysInterval;
									exit; */
									
									$agentPUpdate->match_employee = 2;
									$agentPUpdate->save();
									
								
															
							
						
					}
				}
				echo "done";
				exit;
			}
}