<?php

namespace App\View\Components\Leadership;

use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Illuminate\Http\Request;
use App\Models\Dashboard\WidgetLeadershipDetails;
use App\Models\Dashboard\WidgetCreation;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\AgentPayout;
use Session;
class LeaderPanel extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

	public $topAgentDetails;
	public $widgetName;
	public $widgetId;
	public $dataType;
	public $from_salesTime;
	public $to_salesTime;
	public $bank;
    public function __construct($widgetId,$dataType,Request $request)
    {
        $widgetData = WidgetLeadershipDetails::where("widget_id",$widgetId)->first();
		$widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
	
		$fromSalesTime = '';
		$toSalesTime = '';
			if($request->session()->get('widgetFilter['.$widgetId.'][from_salesTime]') != '')
						{
							  $this->from_salesTime =  $request->session()->get('widgetFilter['.$widgetId.'][from_salesTime]');
							  $fromSalesTime  =  $this->from_salesTime;
							
						}
						else
						{
							$this->from_salesTime = '';
						}
						
						if($request->session()->get('widgetFilter['.$widgetId.'][to_salesTime]') != '')
						{
							$this->to_salesTime  =  $request->session()->get('widgetFilter['.$widgetId.'][to_salesTime]');
							$toSalesTime = $this->to_salesTime;
							
						}
						else
						{
							$this->to_salesTime = '';
						}
						  	$topAgents = $this->TopAgent($widgetData->bank,$dataType,$fromSalesTime,$toSalesTime);
        $this->topAgentDetails = $topAgents;
        $this->widgetName = $widget_name;
        $this->widgetId = $widgetId;
        $this->dataType = $dataType;
        $this->bank = $widgetData->bank;
		
				
    
     
		
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Leadership.leaderpanel');
    }
	
	protected function TopAgent($bank,$dataType,$fromSalesTime=NULL,$toSalesTime=NULL)
	{
		$top10Agent = array();
		if($dataType == 'current_month')
		{
					if($bank == 'ENBD')
					{
					$lastSortOrder = MasterPayout::where("bank_name","ENBD")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARD';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					$agentListLastMonth = MasterPayout::where("bank_name","ENBD")->where("sort_order",$lastSortOrderValue)->where("agent_product",$productAgent)->orderBy("tc_card","DESC")->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
								$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							if($productAgent == 'CARD' || $productAgent == 'CARDS')
							{
							$top10Agent[$index]['product'] = 'Credit Card';
							}
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = $_agentList->tc_card;
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					else if($bank == 'Mashreq')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Mashreq")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARDS';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					$agentListLastMonth = MasterPayout::where("bank_name","Mashreq")->where("sort_order",$lastSortOrderValue)->where("agent_product",$productAgent)->orderBy("cards_mashreq","DESC")->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
							$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							if($productAgent == 'CARD' || $productAgent == 'CARDS')
							{
							$top10Agent[$index]['product'] = 'Credit Card';
							}
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = $_agentList->cards_mashreq;
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					
					else if($bank == 'Deem')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Deem")->orderBy("sort_order","DESC")->first();
					
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					$agentListLastMonth = MasterPayout::where("bank_name","Deem")->where("sort_order",$lastSortOrderValue)->orderBy("no_cards_deem","DESC")->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
							$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							
							$top10Agent[$index]['product'] = 'Credit Card';
							
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = $_agentList->no_cards_deem;
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
		
		}
		
		
		if($dataType == 'month_3')
		{
					if($bank == 'ENBD')
					{
					$lastSortOrder = MasterPayout::where("bank_name","ENBD")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARD';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","ENBD")->whereIn("sort_order",$lastSortOrderValueArray)->where("agent_product",$productAgent)
								->get();
					
					$top10Agent = array();
					
					$index = 0;
					
					
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							
							if($index <10)
							{
								
								$empId = $_agentList->employee_id;
								$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
								if($productAgent == 'CARD' || $productAgent == 'CARDS')
								{
								$top10Agent[$index]['product'] = 'Credit Card';
								}
								$top10Agent[$index]['designation'] = 'Relationship Office';
								$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","ENBD")->whereIn("sort_order",$lastSortOrderValueArray)->sum('tc_card');
								$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
								
								$index++;
							}
						}
					}
					
					
					
					
					}
					else if($bank == 'Mashreq')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Mashreq")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARDS';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					
					$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","Mashreq")->whereIn("sort_order",$lastSortOrderValueArray)
								->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
								$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							if($productAgent == 'CARD' || $productAgent == 'CARDS')
							{
							$top10Agent[$index]['product'] = 'Credit Card';
							}
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","Mashreq")->whereIn("sort_order",$lastSortOrderValueArray)->sum('cards_mashreq');
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					
					else if($bank == 'Deem')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Deem")->orderBy("sort_order","DESC")->first();
					
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					
						$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","Deem")->whereIn("sort_order",$lastSortOrderValueArray)
								->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
								$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							
							$top10Agent[$index]['product'] = 'Credit Card';
							
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","Deem")->whereIn("sort_order",$lastSortOrderValueArray)->sum('no_cards_deem');
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					usort($top10Agent, array($this,'cmp'));

			/* 	print_r($arr);
							$top10AgentSort = array();
							$indexSort = 0;
							for($i=0;$i<count($top10Agent);$i++)
									{
										
										for($j=0;$j<count($top10Agent);$j++)
										{
											if($top10Agent[$i]['total_card'] >$top10Agent[$j]['total_card'])
											{
												$top10AgentSort[$indexSort] = $top10Agent[$i];
											}
											else
											{
												$top10AgentSort[$indexSort] = $top10Agent[$j];
											}
											
										}
										$indexSort++;
									} */
					return $top10Agent;			
		}
		
		
		
		if($dataType == 'custom')
		{
			
			
			
				$from_dateSI = $fromSalesTime."-10";;
				$to_dateSI = $toSalesTime."-10";;
				
				$salesMonthFrom =  date("m",strtotime($from_dateSI));
				$salesYearFrom =  date("Y",strtotime($from_dateSI));
				$salesMonthTo = date("m",strtotime($to_dateSI));
				$salesYearTo =  date("Y",strtotime($to_dateSI));
				
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
					$salesTimeArray[] = (int)$m."-".$year;
					$m++;
				}
			
				//exit;
					if($bank == 'ENBD')
					{
					$lastSortOrder = MasterPayout::where("bank_name","ENBD")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARD';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","ENBD")->whereIn("sales_time",$salesTimeArray)->where("agent_product",$productAgent)
								->get();
					
					$top10Agent = array();
					
					$index = 0;
					
					
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							
							if($index <10)
							{
								
								$empId = $_agentList->employee_id;
								$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
								if($productAgent == 'CARD' || $productAgent == 'CARDS')
								{
								$top10Agent[$index]['product'] = 'Credit Card';
								}
								$top10Agent[$index]['designation'] = 'Relationship Office';
								$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","ENBD")->whereIn("sales_time",$salesTimeArray)->sum('tc_card');
								$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
								
								$index++;
							}
						}
					}
					
					
					
					
					}
					else if($bank == 'Mashreq')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Mashreq")->orderBy("sort_order","DESC")->first();
					$productAgent = 'CARDS';
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					
					$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","Mashreq")->whereIn("sales_time",$salesTimeArray)
								->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
								$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							if($productAgent == 'CARD' || $productAgent == 'CARDS')
							{
							$top10Agent[$index]['product'] = 'Credit Card';
							}
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","Mashreq")->whereIn("sales_time",$salesTimeArray)->sum('cards_mashreq');
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					
					else if($bank == 'Deem')
					{
						$lastSortOrder = MasterPayout::where("bank_name","Deem")->orderBy("sort_order","DESC")->first();
					
					
					$lastSortOrderValue = $lastSortOrder->sort_order;
					$salesTime = $lastSortOrder->sales_time;
					
						$lastSortOrderValueArray = array();
					$lastSortOrderValueArray[] = $lastSortOrderValue;
					$lastSortOrderValueArray[] = $lastSortOrderValue-1;
					$lastSortOrderValueArray[] = $lastSortOrderValue-2;
					$agentListLastMonth = MasterPayout::groupBy('employee_id')
								->selectRaw('count(*) as total,agent_name, employee_id')->where("bank_name","Deem")->whereIn("sales_time",$salesTimeArray)
								->get();
					$top10Agent = array();
					
					$index = 0;
					foreach($agentListLastMonth as $_agentList)
					{
						if($_agentList->employee_id != NULL && $_agentList->employee_id != '')
						{
							if($index <10)
							{
								$empId = $_agentList->employee_id;
							$top10Agent[$index]['agent_name'] = $_agentList->agent_name.' ('.$empId.')';
							
							$top10Agent[$index]['product'] = 'Credit Card';
							
							$top10Agent[$index]['designation'] = 'Relationship Office';
							$top10Agent[$index]['total_card'] = MasterPayout::where("employee_id",$empId)->where("bank_name","Deem")->whereIn("sales_time",$salesTimeArray)->sum('no_cards_deem');
							$top10Agent[$index]['employee_id'] = $_agentList->employee_id;
							$index++;
							}
						}
					}
					}
					usort($top10Agent, array($this,'cmp'));

			/* 	print_r($arr);
							$top10AgentSort = array();
							$indexSort = 0;
							for($i=0;$i<count($top10Agent);$i++)
									{
										
										for($j=0;$j<count($top10Agent);$j++)
										{
											if($top10Agent[$i]['total_card'] >$top10Agent[$j]['total_card'])
											{
												$top10AgentSort[$indexSort] = $top10Agent[$i];
											}
											else
											{
												$top10AgentSort[$indexSort] = $top10Agent[$j];
											}
											
										}
										$indexSort++;
									} */
					return $top10Agent;			
		}
		
		return $top10Agent;
	}
	
	
	private  function cmp($a, $b){
    $key = 'total_card';
    if($a[$key] < $b[$key]){
        return 1;
    }else if($a[$key] > $b[$key]){
        return -1;
    }
    return 0;
}


}
