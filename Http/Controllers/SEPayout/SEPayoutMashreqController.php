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
use App\Models\SEPayout\AgentPayoutDeem;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\SEPayout\AgentPayoutByRange;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\AgentPayoutMashreqRange;




class SEPayoutMashreqController extends Controller
{
  
			
			
			
			
			public function UpdateMashreqTime()
			{
				$agentData=AgentPayoutMashreq::get();
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				
				$updateMod = AgentPayoutMashreq::find($payoutId);
					$time=$_agent->month.'-'.$_agent->year;
					$updateMod->sales_time = $time;
					$updateMod->save();
				
				}
				exit;
			}
			/* public function UpdateMashreqTimeRange(){
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
			
			} */
			public function UpdateMashreqTimeRange(){
				$data=WorkTimeRange::get();
				foreach($data as $_time){
						$range=$_time->range;
						$rangedata=explode('-',$range);
						//print_r($rangedata);

						$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
						$PayoutData =AgentPayoutDeem::whereRaw($whereraw)->get();
						foreach($PayoutData as $_newdata){
							$updateMod = AgentPayoutDeem::find($_newdata->id);
							$updateMod->range_id=$_time->id;
							$updateMod->save();
						}
						
					
				}
				echo "done";
				exit;
			
			}
			public function AgentMidPointMashreqbyRange(){
				
				
				$salestimeUniq = AgentPayoutMashreq::groupBy('range_id')->selectRaw('count(*) as total, range_id')->get();
				$rangeArray = array();
								
				foreach($salestimeUniq as $saletime)
				{
					
				if($saletime->range_id!=NULL || $saletime->range_id!='' ){
				$rangeArray[] = $saletime->range_id;
				$tc= array();
				
					$saletimeList =AgentPayoutMashreq::where("range_id",$saletime->range_id)->get();
					if(count($saletimeList)>0)
					{
					foreach($saletimeList as $allList){
						$rangedata=WorkTimeRange::where("id",$allList->range_id)->first();
						$range=$rangedata->range;
						if($allList->cards_mashreq=="-" || $allList->cards_mashreq==""){
							$tc[] = 0;
						}
						else{
						$tc[] = $allList->cards_mashreq;
						
						}
					}
					sort($tc);	
					
					$tcdatamin = min($tc);
					$tcdatamax = max($tc);
					if(count($tc) > 1)
					{
					$tcmid=round(count($tc)/2);
					$tcmiddata=$tc[$tcmid];	
					}
					else{
						$tcmiddata=1;	
					}
										
					
					
					$payoutData = new AgentPayoutMashreqRange();
					$payoutData->range_id=$saletime->range_id;
					$payoutData->range_title=$range;
					$payoutData->maximum_cards=$tcdatamax;
					$payoutData->minimum_cards=$tcdatamin;
					$payoutData->middle_cards=$tcmiddata;
					$payoutData->total_agent=count($saletimeList);
					$payoutData->respone_data_card=json_encode($tc);
					$payoutData->save();
					}
					
					
					
					
					
				}
			}
			echo "done";
				exit;
				
			}
			
			
}
