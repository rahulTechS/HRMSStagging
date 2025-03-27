<?php

namespace App\Http\Controllers\RedFlags;
require_once "/srv/www/htdocs/core/autoload.php";
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\RedFlags\MashreqRevenueList;
use App\Models\RedFlags\MashreqRevenueRedflag;

class MashreqController extends Controller
{
    
	public function redFlagsVintange()
	{
		
		$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
					$PayoutData =AgentPayoutMashreq::whereRaw($whereraw)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = AgentPayoutMashreq::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						$updateMod->save();
					}
					
				
			}
		
		echo "done";
		exit;
		
	}
	
	public function revenueList()
	{
		/*
		*overall revenue code
		*/
		/*  $collection = AgentPayoutMashreq::groupBy('total_revenue')
			->selectRaw('count(*) as total, total_revenue')
			->get(); */
			/* $collection = AgentPayoutMashreq::get();
			
			foreach($collection as $data)
			{
					$mashreqRevenueModel = new MashreqRevenueList();
					$mashreqRevenueModel->revenue = $data->total_revenue;
					$mashreqRevenueModel->type = 'OverAll';
					$mashreqRevenueModel->redFlag_status = 1;
					$mashreqRevenueModel->doj = trim($data->doj);
					$mashreqRevenueModel->tl_name = trim($data->tl_name);
					$mashreqRevenueModel->agent_name = trim($data->agent_name);
					$mashreqRevenueModel->agent_product = trim($data->agent_product);
					$mashreqRevenueModel->save();
			}
			echo "done";
			exit;  */  
		/*
		*overall revenue code
		*/
		$collectionSalesTime = AgentPayoutMashreq::groupBy('end_sales_time')
			->selectRaw('count(*) as total, end_sales_time')
			->get();
			foreach($collectionSalesTime as $cSalesTime)
			{
			  $collectionRange = AgentPayoutMashreq::groupBy('range_id')
				->selectRaw('count(*) as total, range_id')->where("end_sales_time",$cSalesTime->end_sales_time)
				->get();
		
				foreach($collectionRange as $range)
				{
						$collection = AgentPayoutMashreq::where("end_sales_time",$cSalesTime->end_sales_time)->where("range_id",$range->range_id)
						->get();
						/* echo '<pre>';
						print_r( $collection);
						exit; */
						foreach($collection as $data)
						{
								$mashreqRevenueModel = new MashreqRevenueList();
								$mashreqRevenueModel->revenue = $data->total_revenue;
								$mashreqRevenueModel->range_id = $range->range_id;
								$mashreqRevenueModel->type = 'range';
								$mashreqRevenueModel->sales_time = $cSalesTime->end_sales_time;
								$mashreqRevenueModel->redFlag_status = 1;
								$mashreqRevenueModel->doj = trim($data->doj);
								$mashreqRevenueModel->tl_name = trim($data->tl_name);
								$mashreqRevenueModel->agent_name = trim($data->agent_name);
								$mashreqRevenueModel->agent_product = trim($data->agent_product);
								$mashreqRevenueModel->save();
						}
				}
			}
			echo "done1";
			exit;  
	}
	
	public function revenuePerformance()
	{
		$allMashreqData = MashreqRevenueList::where("type","OverAll")->get();
		$takeReveue = array();
		$totalReveue = 0;
		foreach($allMashreqData as $_mashreq)
		{
			$takeReveue[] = $_mashreq->revenue;
			$totalReveue = $totalReveue+$_mashreq->revenue;
			
		}
		rsort($takeReveue);
		/* echo "<pre>";
		print_r($takeReveue);
		exit; */
		$countR = count($takeReveue);
		/*
		*avg revenue
		*/
		/* echo $totalReveue;
		echo '<br >';
		echo $countR;
		exit; */
		 $avg = round($totalReveue/$countR,2);
		 $medianIndex = round($countR/2);
		 
		 $rflag = new MashreqRevenueRedflag();
		 $rflag->total_revenue = $totalReveue;
		 $rflag->head_count = $countR;
		 $rflag->mean = $avg;
		 $rflag->median = $takeReveue[$medianIndex];
		 
		 $rflag->type = 'OverAll';
		 $rflag->save();
		 echo "done";
		 exit;
		/*
		*avg revenue
		*/
		
		
		
	}
	
	
	public function revenuePerformanceAsFilters(Request $request)
	{
		$salesMonthFrom =  $request->salesMonthFrom;
		$salesYearFrom =  $request->salesYearFrom;
		$salesMonthTo = $request->salesMonthTo;
		$salesYearTo =  $request->salesYearTo;
		$range_id =  $request->range_id;
		$tl_name =  $request->tl_name;
		$product =  $request->product;
		$qt =  $request->qt;
		$qt1 =  $request->qt1;
		$qt2 =  $request->qt2;
		$agentID =  $request->agentID;
		$salesTimeArray = array();
		$year = $salesYearFrom;
		$conditions = $salesMonthTo;
		if($salesYearFrom < $salesYearTo)
		{
			if($salesMonthFrom != 12)
			{
				$interval = 12-$salesMonthFrom;
				$conditions = $salesMonthFrom+$salesMonthTo+$interval;
			}
			else
			{
			$conditions = $salesMonthFrom+$salesMonthTo;
			}
		}
		//echo $conditions;exit;
		$m = $salesMonthFrom;
		for($i=$salesMonthFrom;$i<=$conditions;$i++)
		{
			
			if($m  > 12)
			{
				$m  = 1;
				$year = $year+1;
			}
			$salesTimeArray[] = $m."-".$year;
			$m++;
		}
		$whereRaw = '';
		if($tl_name != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "tl_name = '".$tl_name."'";
			}
			else
			{
				$whereRaw .= " AND tl_name= '".$tl_name."'";
			}
		}
		
		
		if($product != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "agent_product = '".$product."'";
			}
			else
			{
				$whereRaw .= " AND agent_product= '".$product."'";
			}
		}
		/* echo $whereRaw;
		exit; */
		if($range_id == "No")
		{
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->whereraw($whereRaw)->get();
			}
		}
		else
		{
			$rangeIdArray = explode(",",$range_id);
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->whereraw($whereRaw)->get();
			}
		}
		/*  echo '<pre>';
		print_r($allMashreqData);
		exit;   */
		$takeReveue = array();
		$totalReveue = 0;
		foreach($allMashreqData as $_mashreq)
		{
			$takeReveue[] = $_mashreq->total_revenue;
			$totalReveue = $totalReveue+$_mashreq->total_revenue;
			
		}
		/* echo '<pre>';
		print_r($takeReveue);
		exit; */
		rsort($takeReveue);
		/*  echo "<pre>";
		print_r($takeReveue);
		echo "<br>"; */
		$countR = count($takeReveue);
		/*
		*avg revenue
		*/
		/* echo $totalReveue;
		echo '<br >';
		echo $countR;
		exit; */
		if($countR != 0)
		{
		 $avg = round($totalReveue/$countR,2);
		 $medianIndex = round($countR/2);
		  $q10Index = round(($countR/100)*10);
		 $q25Index = round(($countR/100)*25);
		 $q75Index = round(($countR/100)*75);
		 $q100Index = round(($countR/100)*100);
		 
		  if($qt1 != "No" && $qt2 != "No")
		 {
			   $q1Index = round(($countR/100)*$qt1);
			    $q2Index = round(($countR/100)*$qt2);
		 }
		 echo "Total Revenue - AED".$totalReveue;
		 echo "<br />";
		 foreach($salesTimeArray as $_salesT)
		 {
			 if($whereRaw == '')
			{
				if($range_id != "No")
				{
					$countPerRange = AgentPayoutMashreq::where("end_sales_time",$_salesT)->whereIn("range_id",$rangeIdArray)->count();
				}
				else
				{
					$countPerRange = AgentPayoutMashreq::where("end_sales_time",$_salesT)->count();
				}
			}
			else
			{
				if($range_id != "No")
				{
					$countPerRange = AgentPayoutMashreq::where("end_sales_time",$_salesT)->whereIn("range_id",$rangeIdArray)->whereraw($whereRaw)->count();
				}
				else
				{
					$countPerRange = AgentPayoutMashreq::where("end_sales_time",$_salesT)->whereraw($whereRaw)->count();
				}
			}
			$_salesTA = explode("-",$_salesT);
			echo "Head Count (".$_salesTA[0]."-".$_salesTA[1].") - ".$countPerRange;
			 echo "<br />";
		 }
		 echo "Total Head Count - ".$countR;
		 echo "<br />";
		 echo "Mean - AED".$avg;
		 echo "<br />";
		 
		 if($medianIndex == 1)
		 {
			 $medianIndex = 0;
		 }
		 echo "median - AED".$takeReveue[$medianIndex];
		  echo "<br />";
		  echo "quartilile 10% - AED".$takeReveue[$q10Index-1];
		  echo "<br />";
		  echo "quartilile 25% - AED".$takeReveue[$q25Index-1];
		  echo "<br />";
		  echo "quartilile 75% - AED".$takeReveue[$q75Index-1];
		  echo "<br />";
		  echo "quartilile 100% - AED".$takeReveue[$q100Index-1];
		  echo "<br />";
		  if($qt != "No")
			{
				$quarD = round(($countR/100)*$qt);
				echo "quartilile ".$qt."% - AED".$takeReveue[$quarD-1];
				echo "<br />";
			}
		  
		 if($range_id == "No")
			{
				 //echo "Range Id - ".$range_id;
				 //echo "<br />";
			}
			else
			{
				 echo "Range Id - ".$range_id;
				 echo "<br />";
			}	
			
			
			/*
			*top 10% revenue
			*/
			$top10Revenue = 0;
			
			for($i=0;$i<=$q10Index-1;$i++)
			{
				
				$top10Revenue = $top10Revenue+$takeReveue[$i];
				
			}
			/*
			*top 10% revenue
			*/
			
			/*
			*top 25% revenue
			*/
			$top25Revenue = 0;
			if($q10Index > $q25Index-1)
			{
				$q25Index = $q10Index+1;
			}
			
			for($i=$q10Index;$i<=$q25Index-1;$i++)
			{
				
				$top25Revenue = $top25Revenue+$takeReveue[$i];
				
			}
			/*
			*top 25% revenue
			*//* 
				echo '<br />';
			echo $q10Index;
			echo '<br />';
			echo $q25Index-1;
				echo '<br />'; */
			/*
			*top 75% revenue
			*/
			$top75Revenue = 0;
			
			for($i=$q25Index;$i<=$q75Index-1;$i++)
			{
				
				$top75Revenue = $top75Revenue+$takeReveue[$i];
				
			}
			/*
			*top 75% revenue
			*/
			
			/*
			*top 100% revenue
			*/
			$top100Revenue = 0;
			
			for($i=$q75Index;$i<=$q100Index-1;$i++)
			{
				
				$top100Revenue = $top100Revenue+$takeReveue[$i];
				
			}
			/*
			*top 100% revenue
			*/
			 if($qt1 != "No" && $qt2 != "No")
				 {
					if($q1Index >0)
					 {
						 $q1Index = $q1Index-1;
					 }
					 
					/*
					*top dynamic revenue
					*/
					$topqtRevenue = 0;
					
					for($i=$q1Index;$i<=$q2Index-1;$i++)
					{
						
						$topqtRevenue = $topqtRevenue+$takeReveue[$i];
						
					}
					/* echo '<pre>';
					print_r($takeReveue);
					exit; */
					/*
					*top dynamic revenue
					*/
				 }
			 echo "<br />";
			echo "top10 Revenue  - AED ".$top10Revenue;
			echo "<br />";
			echo "top25 Revenue - AED ".$top25Revenue;
			echo "<br />";
			echo "top75 Revenue - AED ".$top75Revenue;
			echo "<br />";
			echo "top100 Revenue - AED ".$top100Revenue;
			echo "<br />";
			 if($qt1 != "No" && $qt2 != "No")
			{
			echo "top".$qt1."-".$qt2." Revenue - AED ".$topqtRevenue;
			echo "<br />";
			}
			echo "top10 Revenue  - ".round(($top10Revenue/$totalReveue)*100)."%";
			echo "<br />";
			echo "top25 Revenue - ".round(($top25Revenue/$totalReveue)*100)."%";
			echo "<br />";
			echo "top75 Revenue - ".round(($top75Revenue/$totalReveue)*100)."%";
			echo "<br />";
			echo "top100 Revenue - ".round(($top100Revenue/$totalReveue)*100)."%";
			echo "<br />";
			 if($qt1 != "No" && $qt2 != "No")
			{
			echo "top".$qt1."-".$qt2." Revenue - ".round(($topqtRevenue/$totalReveue)*100)."%";
			echo "<br />";
			}
				echo "<br />";
				echo "Total Productivity - ".round($totalReveue/$countR,2);
				
				
				/*
			*agent coding
			*/
			if($agentID != 'No')
			{
				$agentDetails = AgentPayoutMashreq::where("id",$agentID)->first();
				if($agentDetails != '')
				{
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "Agent Name  <b>".$agentDetails->agent_name.'</b>';
					echo "<br />";
					echo "Agent's TL  <b>".$agentDetails->tl_name.'</b>';
					echo "<br />";
					echo "Agent Revenue   <b>AED".$agentDetails->total_revenue.'</b>';
					echo "<br />";
					
					$takeReveueAgent = $takeReveue;
					sort($takeReveueAgent);
					
					$agentRevenue = $agentDetails->total_revenue;
					//$agentRevenue = 13501;
					$allowChecking = 1;
					 $similarRevenue =0;
					 /* echo '<pre>';
					 print_r($takeReveueAgent); */
					foreach($takeReveueAgent as $_r)
					{
						if($_r >= $agentRevenue && $allowChecking ==1)
						{
							
							$similarRevenue = $_r;
							$allowChecking = 2;
						}
					
					}
					//echo $similarRevenue;exit;
					 $keyIndexQ = array_search($similarRevenue, $takeReveue); 
					 
					/*  echo "<pre>";
					print_r($takeReveue);
					exit; */
					if($totalReveue >= $agentRevenue)
					{
						echo "Agent Quartilile  <b>".round(($keyIndexQ/$countR)*100,2)."%</b>";
						echo "<br />";
						$positionpercentageAgent = round(($keyIndexQ/$countR)*100,2);
						if($positionpercentageAgent <= 10)
						{
							echo "Top <b>10</b> Revenue Performer";
						}
						else if($positionpercentageAgent <= 25)
						{
							echo "top <b>10-25</b> Revenue Performer";
						}
						else if($positionpercentageAgent <= 75)
						{
							echo "top <b>25-75</b> Revenue Performer";
						}
						else if($positionpercentageAgent <= 100)
						{
							echo "top <b>75-100</b> Revenue Performer";
						}
						else
						{
							echo "top <b>75-100</b> Revenue Performer";
						}
					}
					else
					{
						echo "Agent Quartilile  <b>1%</b>";
						echo "<br />";
						echo "Top <b>1</b> Revenue Performer";
					}
					
					echo "<br />";
					if($totalReveue >= $agentRevenue)
					{
						echo "Agent's Contribution Percentage in Total Revenue <b>".round(($agentRevenue/$totalReveue)*100,2)." %</b>";
					}
					else
					{
						echo "Agent's Contribution Percentage in Total Revenue <b>100%</b>";
					}
					echo "<br />";
					if($avg > $agentRevenue)
					{
						echo "<b>Agent Revenue less than Average</b>";
					}
					else
					{
						echo "<b>Agent Revenue greater than Average</b>";
					}
					
					/*  echo "<pre>";
					print_r($takeReveue);
					exit; */
				}
				else
				{
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "<br />";
					echo "Agent not found";
				}
			}
			/*
			*agent coding
			*/
		}
		else
		{
			echo "No Data Found";
		}

		
		exit;
		
		 echo "done";
		 exit;
		/*
		*avg revenue
		*/
		
		
		
	}
	
	
	
	
	public function revenuePerformanceAsFiltersAgent(Request $request)
	{
		$salesMonthFrom =  $request->salesMonthFrom;
		$salesYearFrom =  $request->salesYearFrom;
		$salesMonthTo = $request->salesMonthTo;
		$salesYearTo =  $request->salesYearTo;
		$range_id =  $request->range_id;
		$tl_name =  $request->tl_name;
		$product =  $request->product;
		$agentName =  $request->agentName;
		
		$salesTimeArray = array();
		$year = $salesYearFrom;
		$conditions = $salesMonthTo;
		if($salesYearFrom < $salesYearTo)
		{
			if($salesMonthFrom != 12)
			{
				$interval = 12-$salesMonthFrom;
				$conditions = $salesMonthFrom+$salesMonthTo+$interval;
			}
			else
			{
			$conditions = $salesMonthFrom+$salesMonthTo;
			}
		}
		//echo $conditions;exit;
		$m = $salesMonthFrom;
		for($i=$salesMonthFrom;$i<=$conditions;$i++)
		{
			
			if($m  > 12)
			{
				$m  = 1;
				$year = $year+1;
			}
			$salesTimeArray[] = $m."-".$year;
			$m++;
		}
		$whereRaw = '';
		if($tl_name != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "tl_name = '".$tl_name."'";
			}
			else
			{
				$whereRaw .= " AND tl_name= '".$tl_name."'";
			}
		}
		
		
		if($product != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "agent_product = '".$product."'";
			}
			else
			{
				$whereRaw .= " AND agent_product= '".$product."'";
			}
		}
		/* echo $whereRaw;
		exit; */
		if($range_id == "No")
		{
			if($whereRaw == '')
			{
				$allMashreqData = MashreqRevenueList::whereIn("sales_time",$salesTimeArray)->get();
			}
			else
			{
				$allMashreqData = MashreqRevenueList::whereIn("sales_time",$salesTimeArray)->whereraw($whereRaw)->get();
			}
		}
		else
		{
			$rangeIdArray = explode(",",$range_id);
			if($whereRaw == '')
			{
				$allMashreqData = MashreqRevenueList::whereIn("sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->get();
			}
			else
			{
				$allMashreqData = MashreqRevenueList::whereIn("sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->whereraw($whereRaw)->get();
			}
		}
		/* echo '<pre>';
		print_r($allMashreqData);
		exit; */
		$takeReveue = array();
		$totalReveue = 0;
		foreach($allMashreqData as $_mashreq)
		{
			$takeReveue[] = $_mashreq->revenue;
			$totalReveue = $totalReveue+$_mashreq->revenue;
			
		}
		rsort($takeReveue);
		/* echo "<pre>";
		print_r($takeReveue);
		exit; */
		$countR = count($takeReveue);
		/*
		*avg revenue
		*/
		/* echo $totalReveue;
		echo '<br >';
		echo $countR;
		exit; */
		if($countR != 0)
		{
		 $avg = round($totalReveue/$countR,2);
		 $medianIndex = round($countR/2);
		 echo "Total Revenue - AED".$totalReveue;
		 echo "<br />";
		 echo "Head Count - ".$countR;
		 echo "<br />";
		 echo "Mean - AED".$avg;
		 echo "<br />";
		 
		 if($medianIndex == 1)
		 {
			 $medianIndex = 0;
		 }
		 echo "median - AED".$takeReveue[$medianIndex];
		  echo "<br />";
		 if($range_id == "No")
			{
				 //echo "Range Id - ".$range_id;
				 //echo "<br />";
			}
			else
			{
				 echo "Range Id - ".$range_id;
				 echo "<br />";
			}
		/*
		*quartilile of Agent
		*Start Code
		*/
		$whereRaw1 = '';
		if($tl_name != 'No')
		{
			if($whereRaw1 == '')
			{
				$whereRaw1 = "tl_name = '".$tl_name."'";
			}
			else
			{
				$whereRaw1 .= " AND tl_name= '".$tl_name."'";
			}
		}
		if($range_id == "No")
		{
			if($whereRaw1 != '')
			{
				$agentData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->where("agent_name",$agentName)->whereRaw($whereRaw1)->get();
			}
			else
			{
			$agentData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->where("agent_name",$agentName)->get();
			}
		}
		else
		{
			$rangeIdArray = explode(",",$range_id);
			if($whereRaw1 != '')
			{
				$agentData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->where("agent_name",$agentName)->whereIn("range_id",$rangeIdArray)->whereRaw($whereRaw1)->get();
			}
			else
			{
			$agentData = AgentPayoutMashreq::whereIn("end_sales_time",$salesTimeArray)->where("agent_name",$agentName)->whereIn("range_id",$rangeIdArray)->get();
			}
		}
		/* echo '<pre>';
		print_r($agentData);exit; */
		$totalAgentRevenue = 0;
		foreach($agentData as $_agent)
		{
			$totalAgentRevenue = $totalAgentRevenue+$_agent->total_revenue;
		}
		 echo "Total Revenue - ".$totalAgentRevenue;
		 echo "<br />";
		 if($avg>$totalAgentRevenue)
		 {
			 echo "red Flag";
		 }
		 else
		 {
			 echo "OK";
		 }
		/*
		*quartilile of Agent
		*End Code
		*/
			
		}
		else
		{
			echo "No Data Found";
		}

		
		exit;
		
		 echo "done";
		 exit;
		/*
		*avg revenue
		*/
		
		
		
	}
	
	
	
	public function revenuePerformanceAsPerRange()
	{
		
		$collectionTime = MashreqRevenueList::groupBy('sales_time')
			->selectRaw('count(*) as total, sales_time')
			->get();
		foreach($collectionTime as $time)
		{
			$collectionRange = MashreqRevenueList::groupBy('range_id')
				->selectRaw('count(*) as total, range_id')->where("sales_time",$time->sales_time)
				->get();
				/* echo '<pre>';
				print_r($collectionRange);
				exit; */
			foreach($collectionRange as $range)
			{
				if($range->range_id != '')
				{
			$allMashreqData = MashreqRevenueList::where("range_id",$range->range_id)->where("sales_time",$time->sales_time)->get();
			$takeReveue = array();
			$totalReveue = 0;
			foreach($allMashreqData as $_mashreq)
			{
				$takeReveue[] = $_mashreq->revenue;
				$totalReveue = $totalReveue+$_mashreq->revenue;
				
			}
			rsort($takeReveue);
			/* echo "<pre>";
			print_r($takeReveue);
			exit; */
			$countR = count($takeReveue);
			/*
			*avg revenue
			*/
			/* echo $totalReveue;
			echo '<br >';
			echo $countR;
			exit; */
			if($countR >1)
			{
			 $avg = round($totalReveue/$countR,2);
			 $medianIndex = round($countR/2);
			 
			 $rflag = new MashreqRevenueRedflag();
			 $rflag->total_revenue = $totalReveue;
			 $rflag->head_count = $countR;
			 $rflag->mean = $avg;
			 $rflag->median = $takeReveue[$medianIndex];
			 
			 $rflag->type = 'Range';
			 $rflag->range_id = $range->range_id;
			 $rflag->sales_time = $time->sales_time;
			 $rflag->save();
			}
			/*   echo "done";
			 exit; */
				}
			}
		}
		  echo "done1";
		 exit;
		/*
		*avg revenue
		*/
		
		
		
	}
	
	public function doingredFlagM()
	{
		$agentPayoutMDetails = AgentPayoutMashreq::get();
		/* echo '<pre>';
		print_r($agentPayoutMDetails);
		exit; */
		foreach($agentPayoutMDetails as $_mashreq)
		{
			$mId = $_mashreq->id;
			$revenue = $_mashreq->total_revenue;
			$range_id = $_mashreq->range_id;
			$sales_time = $_mashreq->end_sales_time;
			/*
			*overAll setting
			*/
			$mRedFlagDetails = MashreqRevenueRedflag::where("type","OverAll")->first();
			
			$mashreqUpdate = AgentPayoutMashreq::find($mId);
			$mashreqUpdate->avg_overall = $mRedFlagDetails->mean;
			$mashreqUpdate->median_overall = $mRedFlagDetails->median;
			/*
			*mashreq lists
			*/
			$rLists = MashreqRevenueList::where("type","OverAll")->orderBy("revenue","DESC")->get();
			$currentarray = array();
			$position = 0;
			$index = 0;
			foreach($rLists as $list)
			{
				$currentarray[] = $list->revenue;
				if($list->revenue == $revenue)
				{
					$position = $index;
				}
				$index++;
			}
			/* rsort($currentarray);
			echo '<pre>';
			print_r($currentarray);
			echo "<br />";
			echo $position;
			exit;
		 */
			 $quartilile_overall = round(($position/count($currentarray))*100);
			 $mashreqUpdate->quartilile_overall =$quartilile_overall;
			/*
			*mashreq lists
			*/
			
			/*
			*overAll setting
			*/
			
			
			
			/*
			*range setting
			*/
			$mRedFlagDetails = MashreqRevenueRedflag::where("range_id",$range_id)->where("sales_time",$sales_time)->first();
			if($mRedFlagDetails != '')
			{
			
			$mashreqUpdate->avg_range = $mRedFlagDetails->mean;
			$mashreqUpdate->median_range = $mRedFlagDetails->median;
			/*
			*mashreq lists
			*/
			$rLists = MashreqRevenueList::where("range_id",$range_id)->where("sales_time",$sales_time)->orderBy("revenue","DESC")->get();
			
			$currentarray = array();
			$position = 0;
			$index = 0;
			foreach($rLists as $list)
			{
				$currentarray[] = $list->revenue;
				if($list->revenue == $revenue)
				{
					$position = $index;
				}
				$index++;
			}
			/* rsort($currentarray);
			echo '<pre>';
			print_r($currentarray);
			echo "<br />";
			echo $position;
			exit;
		 */
			 $quartilile_range = round(($position/count($currentarray))*100);
			 $mashreqUpdate->quartilile_range =$quartilile_range;
			}
			/*
			*mashreq lists
			*/
			
			/*
			*range setting
			*/
			$mashreqUpdate->save();
			
		}
		echo "done";
			exit;
	}
	
	
	public function salesTimeUpdate()
	{
		$MashreqRevenueRedflagData = MashreqRevenueRedflag::get();
		foreach($MashreqRevenueRedflagData as $mData)
		{
			if($mData->sales_time != '' && $mData->sales_time != NULL)
			{
			$updateMod = MashreqRevenueRedflag::find($mData->id);
			$sales_timeStr = $mData->sales_time;
			$sales_timeArray = explode("-",$sales_timeStr);
			$updateMod->sales_month = $sales_timeArray[0];
			$updateMod->sales_year = $sales_timeArray[1];
			$updateMod->save();
			}
		}
		echo "done";
		exit;
	}
	public function updateENBDTableM()
	{
		$agentMod = AgentPayoutMashreq::get();
		/*  echo '<pre>';
		print_r($agentMod);
		exit;  */
		foreach($agentMod as $mod)
		{
			$updateAgent = AgentPayoutMashreq::find($mod->id);
			
			$final_loan =  $mod->personal_loan+$mod->auto_loan;
			$updateAgent->final_loan = $final_loan;
			
			$updateAgent->save();
		}
		echo "done";
		exit;
	}
	
	
	public function targetPerformanceAsFiltersM(Request $request)
{
		$salesMonthFrom =  $request->salesMonthFrom;
		$salesYearFrom =  $request->salesYearFrom;
		$salesMonthTo = $request->salesMonthTo;
		$salesYearTo =  $request->salesYearTo;
		$range_id =  $request->range_id;
		$tl_name =  $request->tl_name;
		$product =  $request->product;
		$location =  $request->location;
		$qt =  $request->qt;
		$agentId =  $request->agentId;
		$salesTimeArray = array();
		$year = $salesYearFrom;
		$conditions = $salesMonthTo;
		if($salesYearFrom < $salesYearTo)
		{
			if($salesMonthFrom != 12)
			{
				$interval = 12-$salesMonthFrom;
				$conditions = $salesMonthFrom+$salesMonthTo+$interval;
			}
			else
			{
			$conditions = $salesMonthFrom+$salesMonthTo;
			}
		}
		//echo $conditions;exit;
		$m = $salesMonthFrom;
		for($i=$salesMonthFrom;$i<=$conditions;$i++)
		{
			
			if($m  > 12)
			{
				$m  = 1;
				$year = $year+1;
			}
			$salesTimeArray[] = $m."-".$year;
			$m++;
		}
		
		$whereRaw = '';
		if($tl_name != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "tl_name = '".$tl_name."'";
			}
			else
			{
				$whereRaw .= " AND tl_name= '".$tl_name."'";
			}
		}
		
		
		if($product != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "agent_product = '".$product."'";
			}
			else
			{
				$whereRaw .= " AND agent_product= '".$product."'";
			}
		}
		
		if($location != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "location = '".$location."'";
			}
			else
			{
				$whereRaw .= " AND location= '".$location."'";
			}
		}
		/* echo $whereRaw;
		exit; */
		if($range_id == "No")
		{
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereraw($whereRaw)->get();
			}
		}
		else
		{
			$rangeIdArray = explode(",",$range_id);
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->whereraw($whereRaw)->get();
			}
		}
		/* echo '<pre>';
		echo count($allMashreqData);
		exit; */
		if(count($allMashreqData) >0)
		{
		/*
		*count 80% target not achive
		*/
		$targetNotAchive80 = 0;
		$totalCards = 0;
		$totalCardsCS = 0;
		$totalCardsCSOnly = 0;
		$totalCardsCount = 0;
	
		
		$totalLoan = 0;
		$totalLoanCount = 0;
		
		$totalLoanCS = 0;
		$totalLoanCSOnly = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			$agentTarget80 = round(($AgentTarget/100)*80);
			$totalCardsCS = $totalCardsCS+$_tdata->cards_point_m;
			
			
			$totalLoanCS = $totalLoanCS+$_tdata->final_loan;
			
			if($_tdata->agent_product == 'CARDS')
			{
				$totalLoanCSOnly = $totalLoanCSOnly+$_tdata->final_loan;
				$totalCards = $totalCards+$_tdata->cards_point_m;
				$totalCardsCount++;
				if($agentTarget80 > $_tdata->cards_point_m)
				{
					$targetNotAchive80++;
				}
			}
			else
			{
				$totalCardsCSOnly = $totalCardsCSOnly+$_tdata->cards_point_m;
				$totalLoan = $totalLoan+$_tdata->final_loan;
				$totalLoanCount++;
				if($agentTarget80 > $_tdata->final_loan)
				{
					$targetNotAchive80++;
				}
			}
		}
		
		
		
		/*
		*count 80% target not achive
		*/
		
		/*
		*candidate count achived target
		*/
		$achivedCandidate = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			if($_tdata->agent_product == 'CARDS')
			{
				if($AgentTarget <= $_tdata->cards_point_m)
				{
					$achivedCandidate++;
				}
			}
			else
			{
				if($AgentTarget <= $_tdata->final_loan)
				{
					$achivedCandidate++;
				}
			}
		}
		/*
		*candidate count achived target
		*/
		$colorCode = array("#4F5060","#67819D","#ADBD37","#588133","#003B45");
		$dataPie = array();
	/* 	$dataPie[0]['cName']= 'Total Agents Head Count';
		$dataPie[0]['total']= count($allMashreqData);
		$dataPie[0]['color']= $colorCode[0]; */
		$totalHeadCount = count($allMashreqData);
		
		$dataPie[0]['cName']= 'Agent Count who achived their Target';
		$dataPie[0]['total']= $achivedCandidate;
		$dataPie[0]['color']= $colorCode[0];
		
		
		
		if(count($allMashreqData) >0)
		{
			//echo "<br />";
			//echo "Agent Percentage who achived their Target  ".round(($achivedCandidate/count($allMashreqData)*100),2)."%";
			
		}
		else
		{
			//echo "<br />";
			//echo "Agent Percentage who achived their Target  0%";
		}
		
		$dataPie[1]['cName']= 'Agents Achived less than 80% of Target';
		$dataPie[1]['total']= $targetNotAchive80;
		$dataPie[1]['color']= $colorCode[1];
		/* echo "<br />";
		echo "Agents Achived less than 80% of Target  ".$targetNotAchive80; */
		if($qt != 'No')
		{
		/*
		*count dynamic target not achive
		*/
		$targetNotAchiveD = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			$agentTargetD = round(($AgentTarget/100)*$qt);
			if($_tdata->agent_product == 'CARDS')
			{
				if($agentTargetD > $_tdata->cards_point_m)
				{
					$targetNotAchiveD++;
				}
			}
			else
			{
				if($agentTargetD > $_tdata->final_loan)
				{
					$targetNotAchiveD++;
				}
			}
		}
		//echo "<br />";
		//echo "Agents Achived less than ".$qt."% of Target ".$targetNotAchiveD;
		$dataPie[2]['cName']= 'Agents Achived less than '.$qt.'% of Target';
		$dataPie[2]['total']= $targetNotAchiveD;
		$dataPie[2]['color']= $colorCode[2];
		}
		
		$dataBar = array();
		$dataBar[0]['TitleShow'] = 'Total Agents in Card';
		$dataBar[0]['total'] = $totalCardsCount;
		$dataBar[0]['color'] = $colorCode[0];
		
		
		$dataBar[1]['TitleShow'] = 'Total Agents in loan';
		$dataBar[1]['total'] = $totalLoanCount;
		$dataBar[1]['color'] = $colorCode[1];
		
		//echo "<br />";
		//echo "Total Agents in Card ".$totalCardsCount;
		//echo "<br />";
		//echo "Total Agents in loan ".$totalLoanCount;
			
			if($totalCardsCount >0)
			{
				$dataBar[2]['TitleShow'] = 'Core Team Productivity (Cards)';
				$dataBar[2]['total'] = round($totalCards/$totalCardsCount,2);
				$dataBar[2]['color'] = $colorCode[2];
			//	echo "<br />";
				//echo "Core Team Productivity (Cards) ".round($totalCards/$totalCardsCount,2);
		
			}
			else
			{
				$dataBar[2]['TitleShow'] = 'Core Team Productivity (Cards)';
				$dataBar[2]['total'] = 0;
				$dataBar[2]['color'] = $colorCode[2];
				//echo "<br />";
				//echo "Core Team Productivity (Cards) 0";
			}
			if($totalLoanCount > 0)
			{
				$dataBar[3]['TitleShow'] = 'Core Team Productivity (Loan)';
				$dataBar[3]['total'] = round($totalLoan/$totalLoanCount,2);
				$dataBar[3]['color'] = $colorCode[3];
				//echo "<br />";
				//echo "Core Team Productivity (Loan) ".round($totalLoan/$totalLoanCount,2);
			}
			else
			{
				$dataBar[3]['TitleShow'] = 'Core Team Productivity (Loan)';
				$dataBar[3]['total'] = 0;
				$dataBar[3]['color'] = $colorCode[3];
				//echo "<br />";
				//echo "Core Team Productivity (Loan) 0";
			}
				//echo "<br />";
			if($totalCardsCount >0)
			{
				$dataBar[4]['TitleShow'] = 'Team Cards Productivity (Cards including XS)';
				$dataBar[4]['total'] = round($totalCardsCS/$totalCardsCount,2);
				$dataBar[4]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "Team Cards Productivity (Cards including XS) ".round($totalCardsCS/$totalCardsCount,2);
			}
			else
			{
				$dataBar[4]['TitleShow'] = 'Team Cards Productivity (Cards including XS)';
				$dataBar[4]['total'] = 0;
				$dataBar[4]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "Team Cards Productivity (Cards including XS) 0";
			}
			if($totalLoanCount >0)
			{
				$dataBar[5]['TitleShow'] = 'Team Loan Productivity (Loan including XS)';
				$dataBar[5]['total'] = round($totalLoanCS/$totalLoanCount,2);
				$dataBar[5]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "Team Loan Productivity (Loan including XS) ".round($totalLoanCS/$totalLoanCount,2);
			}
			else
			{
				$dataBar[5]['TitleShow'] = 'Team Loan Productivity (Loan including XS)';
				$dataBar[5]['total'] = 0;
				$dataBar[5]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "Team Loan Productivity (Loan including XS) 0";
			}
			if($totalCardsCS >0)
			{
				$dataBar[6]['TitleShow'] = 'XS (Card)';
				$dataBar[6]['total'] = round(($totalCardsCSOnly/$totalCardsCS)*100,2).'%';
				$dataBar[6]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "XS (Card)  ".round(($totalCardsCSOnly/$totalCardsCS)*100,2).'%';
			}
			else
			{
				$dataBar[6]['TitleShow'] = 'XS (Card)';
				$dataBar[6]['total'] = '0%';
				$dataBar[6]['color'] = $colorCode[4];
				   // echo "<br />";
					//echo "XS (Card)  0%";
			}
			if($totalLoanCS >0)
			{
				$dataBar[7]['TitleShow'] = 'XS (Loan)';
				$dataBar[7]['total'] = round(($totalLoanCSOnly/$totalLoanCS)*100,2).'%';
				$dataBar[7]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "XS (Loan)   ".round(($totalLoanCSOnly/$totalLoanCS)*100,2).'%';
			}
			else
			{
				$dataBar[7]['TitleShow'] = 'XS (Loan)';
				$dataBar[7]['total'] = '0%';
				$dataBar[7]['color'] = $colorCode[4];
					//echo "<br />";
					//echo "XS (Loan)   0%";
			}
		/* echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "Total Cards with XS -".$totalCardsCS;
		echo "<br />";
		echo "Total Cards By Loan Agent -".$totalCardsCSOnly;
		echo "<br />";
		echo "Total Loan with XS -".$totalLoanCS;
		echo "<br />";
		echo "Total Loan By Card Agent -".$totalLoanCSOnly; */
		/*
		*count dynamic target not achive
		*/
			/*
		*count dynamic target not achive
		*/
		$agentName = '';
		$agentProduct = '';
			 if($agentId != "No")
			{
				$dataPieAgent = array();
					$agentDetails = AgentPayoutMashreq::where("id",$agentId)->first();
					if($agentDetails != '')
					{
						$agentName = $agentDetails->agent_name;
						$agentTL = $agentDetails->tl_name;
						
						$agentProduct = $agentDetails->agent_product;
						$dataPieAgent[0]['cName']= 'Agent Target';
							$dataPieAgent[0]['total']= $agentDetails->agent_target;
							$dataPieAgent[0]['color']= $colorCode[0];
					
						
						if($agentDetails->agent_product == 'CARDS')
						{
							//echo "Agent CARD Point  <b>".$agentDetails->cards_point_m.'</b>';
							//echo "<br />";
							$dataPieAgent[1]['cName']= 'Agent CARD Point';
							$dataPieAgent[1]['total']= $agentDetails->cards_point_m;
							$dataPieAgent[1]['color']= $colorCode[0];
							if($agentDetails->agent_target > 0)
							{
								/* $dataPieAgent[1]['cName']= 'Agent Achived Target';
							$dataPieAgent[1]['total']= round(($agentDetails->cards_point_m/$agentDetails->agent_target)*100,2).'%';
							$dataPieAgent[1]['color']= $colorCode[1]; */
								//echo "Agent Achived Target ".round(($agentDetails->cards_point_m/$agentDetails->agent_target)*100,2).'%';
							}
							else
							{
								/* $dataPieAgent[1]['cName']= 'Agent Achived Target';
							$dataPieAgent[1]['total']= '0%';
							$dataPieAgent[1]['color']= $colorCode[1]; */
								//echo "Agent Achived Target 0%";
							}
						}
						else
						{
							$dataPieAgent[1]['cName']= 'Agent Loan AED';
							$dataPieAgent[1]['total']= $agentDetails->final_loan;
							$dataPieAgent[1]['color']= $colorCode[0];
							//echo "Agent Loan AED<b>".$agentDetails->final_loan.'</b>';
							//echo "<br />";
							if($agentDetails->agent_target > 0)
							{
								/* $dataPieAgent[1]['cName']= 'Agent Achived Target';
								$dataPieAgent[1]['total']= round(($agentDetails->final_loan/$agentDetails->agent_target)*100,2).'%';
								$dataPieAgent[1]['color']= $colorCode[1]; */
								//echo "Agent Achived Target ".round(($agentDetails->final_loan/$agentDetails->agent_target)*100,2).'%';
							}
							else
							{
								/* $dataPieAgent[1]['cName']= 'Agent Achived Target';
								$dataPieAgent[1]['total']= '0%';
								$dataPieAgent[1]['color']= $colorCode[1]; */
								//echo "Agent Achived Target 0%";
							}
						}
					}
					else
					{
						//echo "Agent not found";
						$dataPieAgent = array();
					}
					
			} 
			else
			{
				$dataPieAgent = array();
			}
			/*
			*count dynamic target not achive
			*/
		}
		else
		{
			//echo "No Data Found";
			$dataPieAgent = array();
		}
		return view("presentation/SalesReport/targetPresentation",compact('dataPie','dataBar','dataPieAgent','agentName','agentProduct','totalHeadCount'));
		 
}

public function getDOJM()
	{
		$agentMDetails = AgentPayoutMashreq::where("dob_get_status",3)->get();
		/* echo "<pre>";
		print_r($agentMDetails);
		exit; */
		foreach($agentMDetails as $agentM)
		{
			$name = $agentM->agent_name;
			$agentId = $agentM->id;
			$agentexist = AgentPayoutMashreq::where("agent_name",$name)->where("dob_get_status",2)->first();
			if($agentexist != '')
			{
			$doj = $agentexist->doj;
			$agentUpdateMod = AgentPayoutMashreq::find($agentId);
			$agentUpdateMod->doj = $doj;
			$agentUpdateMod->dob_get_status = 4;
			$agentUpdateMod->save();
			}
			else
			{
				$agentUpdateMod = AgentPayoutMashreq::find($agentId);
				$agentUpdateMod->dob_get_status = 5;
				$agentUpdateMod->save();
			}
			
		}
		echo "yes";
		exit;
	}
	
	
	
	public function targetPerformanceAsFiltersM1(Request $request)
{
		$salesMonthFrom =  $request->salesMonthFrom;
		$salesYearFrom =  $request->salesYearFrom;
		$salesMonthTo = $request->salesMonthTo;
		$salesYearTo =  $request->salesYearTo;
		$range_id =  $request->range_id;
		$tl_name =  $request->tl_name;
		$product =  $request->product;
		$location =  $request->location;
		$qt =  $request->qt;
		$agentId =  $request->agentId;
		$salesTimeArray = array();
		$year = $salesYearFrom;
		$conditions = $salesMonthTo;
		if($salesYearFrom < $salesYearTo)
		{
			if($salesMonthFrom != 12)
			{
				$interval = 12-$salesMonthFrom;
				$conditions = $salesMonthFrom+$salesMonthTo+$interval;
			}
			else
			{
			$conditions = $salesMonthFrom+$salesMonthTo;
			}
		}
		//echo $conditions;exit;
		$m = $salesMonthFrom;
		for($i=$salesMonthFrom;$i<=$conditions;$i++)
		{
			
			if($m  > 12)
			{
				$m  = 1;
				$year = $year+1;
			}
			$salesTimeArray[] = $m."-".$year;
			$m++;
		}
		
		$whereRaw = '';
		if($tl_name != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "tl_name = '".$tl_name."'";
			}
			else
			{
				$whereRaw .= " AND tl_name= '".$tl_name."'";
			}
		}
		
		
		if($product != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "agent_product = '".$product."'";
			}
			else
			{
				$whereRaw .= " AND agent_product= '".$product."'";
			}
		}
		
		if($location != 'No')
		{
			if($whereRaw == '')
			{
				$whereRaw = "location = '".$location."'";
			}
			else
			{
				$whereRaw .= " AND location= '".$location."'";
			}
		}
		/* echo $whereRaw;
		exit; */
		if($range_id == "No")
		{
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereraw($whereRaw)->get();
			}
		}
		else
		{
			$rangeIdArray = explode(",",$range_id);
			if($whereRaw == '')
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->get();
			}
			else
			{
				$allMashreqData = AgentPayoutMashreq::where("agent_target","!=",0)->whereIn("end_sales_time",$salesTimeArray)->whereIn("range_id",$rangeIdArray)->whereraw($whereRaw)->get();
			}
		}
		/* echo '<pre>';
		echo count($allMashreqData);
		exit; */
		if(count($allMashreqData) >0)
		{
		/*
		*count 80% target not achive
		*/
		$targetNotAchive80 = 0;
		$totalCards = 0;
		$totalCardsCS = 0;
		$totalCardsCSOnly = 0;
		$totalCardsCount = 0;
	
		
		$totalLoan = 0;
		$totalLoanCount = 0;
		
		$totalLoanCS = 0;
		$totalLoanCSOnly = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			$agentTarget80 = round(($AgentTarget/100)*80);
			$totalCardsCS = $totalCardsCS+$_tdata->cards_point_m;
			
			
			$totalLoanCS = $totalLoanCS+$_tdata->final_loan;
			
			if($_tdata->agent_product == 'CARDS')
			{
				$totalLoanCSOnly = $totalLoanCSOnly+$_tdata->final_loan;
				$totalCards = $totalCards+$_tdata->cards_point_m;
				$totalCardsCount++;
				if($agentTarget80 > $_tdata->cards_point_m)
				{
					$targetNotAchive80++;
				}
			}
			else
			{
				$totalCardsCSOnly = $totalCardsCSOnly+$_tdata->cards_point_m;
				$totalLoan = $totalLoan+$_tdata->final_loan;
				$totalLoanCount++;
				if($agentTarget80 > $_tdata->final_loan)
				{
					$targetNotAchive80++;
				}
			}
		}
		
		
		
		/*
		*count 80% target not achive
		*/
		
		/*
		*candidate count achived target
		*/
		$achivedCandidate = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			if($_tdata->agent_product == 'CARDS')
			{
				if($AgentTarget <= $_tdata->cards_point_m)
				{
					$achivedCandidate++;
				}
			}
			else
			{
				if($AgentTarget <= $_tdata->final_loan)
				{
					$achivedCandidate++;
				}
			}
		}
		/*
		*candidate count achived target
		*/
		
		echo "Total Agents Head Count ".count($allMashreqData);
		echo "<br />";
		echo "Agent Count who achived their Target  ".$achivedCandidate;
		if(count($allMashreqData) >0)
		{
			echo "<br />";
			echo "Agent Percentage who achived their Target  ".round(($achivedCandidate/count($allMashreqData)*100),2)."%";
		}
		else
		{
			echo "<br />";
			echo "Agent Percentage who achived their Target  0%";
		}
		echo "<br />";
		echo "Agents Achived less than 80% of Target  ".$targetNotAchive80;
		if($qt != 'No')
		{
		/*
		*count dynamic target not achive
		*/
		$targetNotAchiveD = 0;
		foreach($allMashreqData as $_tdata)
		{
			$AgentTarget = $_tdata->agent_target;
			$agentTargetD = round(($AgentTarget/100)*$qt);
			if($_tdata->agent_product == 'CARDS')
			{
				if($agentTargetD > $_tdata->cards_point_m)
				{
					$targetNotAchiveD++;
				}
			}
			else
			{
				if($agentTargetD > $_tdata->final_loan)
				{
					$targetNotAchiveD++;
				}
			}
		}
		echo "<br />";
		echo "Agents Achived less than ".$qt."% of Target ".$targetNotAchiveD;
		}
		echo "<br />";
		echo "Total Agents in Card ".$totalCardsCount;
		echo "<br />";
		echo "Total Agents in loan ".$totalLoanCount;
			
			if($totalCardsCount >0)
			{
				echo "<br />";
				echo "Core Team Productivity (Cards) ".round($totalCards/$totalCardsCount,2);
		
			}
			else
			{
				echo "<br />";
				echo "Core Team Productivity (Cards) 0";
			}
			if($totalLoanCount > 0)
			{
				echo "<br />";
				echo "Core Team Productivity (Loan) ".round($totalLoan/$totalLoanCount,2);
			}
			else
			{
				echo "<br />";
				echo "Core Team Productivity (Loan) 0";
			}
				echo "<br />";
			if($totalCardsCount >0)
			{
					echo "<br />";
					echo "Team Cards Productivity (Cards including XS) ".round($totalCardsCS/$totalCardsCount,2);
			}
			else
			{
					echo "<br />";
					echo "Team Cards Productivity (Cards including XS) 0";
			}
			if($totalLoanCount >0)
			{
					echo "<br />";
					echo "Team Loan Productivity (Loan including XS) ".round($totalLoanCS/$totalLoanCount,2);
			}
			else
			{
					echo "<br />";
					echo "Team Loan Productivity (Loan including XS) 0";
			}
			if($totalCardsCS >0)
			{
					echo "<br />";
					echo "XS (Card)  ".round(($totalCardsCSOnly/$totalCardsCS)*100,2).'%';
			}
			else
			{
				    echo "<br />";
					echo "XS (Card)  0%";
			}
			if($totalLoanCS >0)
			{
					echo "<br />";
					echo "XS (Loan)   ".round(($totalLoanCSOnly/$totalLoanCS)*100,2).'%';
			}
			else
			{
					echo "<br />";
					echo "XS (Loan)   0%";
			}
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "Total Cards with XS -".$totalCardsCS;
		echo "<br />";
		echo "Total Cards By Loan Agent -".$totalCardsCSOnly;
		echo "<br />";
		echo "Total Loan with XS -".$totalLoanCS;
		echo "<br />";
		echo "Total Loan By Card Agent -".$totalLoanCSOnly;
		/*
		*count dynamic target not achive
		*/
			/*
		*count dynamic target not achive
		*/
		
			if($agentId != "No")
			{
					$agentDetails = AgentPayoutMashreq::where("id",$agentId)->first();
					if($agentDetails != '')
					{
						echo "<br />";
						echo "<br />";
						echo "<br />";
						echo "<br />";
						echo "<br />";
						echo "Agent Name  <b>".$agentDetails->agent_name.'</b>';
						echo "<br />";
						echo "Agent's TL  <b>".$agentDetails->tl_name.'</b>';
						echo "<br />";
						echo "Agent Target   <b>".$agentDetails->agent_target.'</b>';
						echo "<br />";
						echo "Agent Product   <b>".$agentDetails->agent_product.'</b>';
						echo "<br />";
						
						if($agentDetails->agent_product == 'CARDS')
						{
							echo "Agent CARD Point  <b>".$agentDetails->cards_point_m.'</b>';
							echo "<br />";
							if($agentDetails->agent_target > 0)
							{
								echo "Agent Achived Target ".round(($agentDetails->cards_point_m/$agentDetails->agent_target)*100,2).'%';
							}
							else
							{
								echo "Agent Achived Target 0%";
							}
						}
						else
						{
							echo "Agent Loan AED<b>".$agentDetails->final_loan.'</b>';
							echo "<br />";
							if($agentDetails->agent_target > 0)
							{
								echo "Agent Achived Target ".round(($agentDetails->final_loan/$agentDetails->agent_target)*100,2).'%';
							}
							else
							{
								echo "Agent Achived Target 0%";
							}
						}
					}
					else
					{
						echo "Agent not found";
					}
					
			}
			/*
			*count dynamic target not achive
			*/
		}
		else
		{
			echo "No Data Found";
		}
		exit;
		 
}
}
