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
use App\Models\SEPayout\WorkTimeRange;
use App\Models\SEPayout\AgentPayoutByRange;




class SEPayoutController extends Controller
{
  
			
			
			public function SECodeUpdate()
			{
				$agentData=AgentPayout::get();
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				$agentName = $_agent->agent_name;
				$updateMod = AgentPayout::find($payoutId);
				$agentNameArr = explode("_",$agentName);
				if(count($agentNameArr) >1)
				{
					$updateMod->agent_bank_code = $agentNameArr[1];
					$updateMod->save();
				}
				}
				exit;
			}
			public function UpdateloanAmount()
			{
				$agentData=AgentPayout::get();
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				if($_agent->personal_loan=='-' || $_agent->personal_loan=='' || $_agent->personal_loan==NULL ){
				$p_loan =0;
				}
				else{
					$p_loan =$_agent->personal_loan;
				}
				if($_agent->auto_loan=='-' || $_agent->auto_loan=='' || $_agent->personal_loan==NULL  ){
				$a_loan = 0;
				}
				else{
					$a_loan = $_agent->auto_loan;
				}
				$finalamount=str_replace(",","",$p_loan)+str_replace(",","",$a_loan);//exit;
				$updateMod = AgentPayout::find($payoutId);
				
					$updateMod->final_loan_amount = $finalamount;
					$updateMod->save();
				
				}
				exit;
			}
			public function UpdateloanData(){
				
				$loanData=AgentPayout::get();
				//$index = 0;
				foreach($loanData as $_loan){
				$loanProduct=$_loan->agent_product;
				
				$id=$_loan->id;
				$updatecard = AgentPayout::find($id);
				
					
					$loanData=$_loan->final_loan_amount;
					if($loanData > 0)
					{
					//$loanData=3600;
					$array['mass_final']=0;
					$array['premium_final']=0;
					$array['super_premium_final']=0;
					$array['left_loan_amount']=0;
					if($loanData<=50000){
						$array['left_loan_amount']=$loanData;
					}
					$fdata=$this->checkLoanData($loanData,$array);
					$mass=$fdata['mass_final'];
					$premium=$fdata['premium_final'];
					$super_premium=$fdata['super_premium_final'];
					$leftamount=$fdata['left_loan_amount'];
				
						$existingValue = AgentPayout::where("id",$id)->first();
						if($existingValue->mass=='-' || $existingValue->mass=='' || $existingValue->mass==NULL){
						$mass = $mass + 0;
						}
						else{
						$mass = $mass + $existingValue->mass;
						}
						if($existingValue->premium=='-' ||$existingValue->premium=='' ||$existingValue->premium==NULL){
							$premium = $premium + 0;
						}
						else{
						$premium = $premium + $existingValue->premium;
						}
						if($existingValue->super_premium=='-' || $existingValue->super_premium=='' || $existingValue->super_premium==NULL){
							$super_premium = $super_premium + 0;
						}
						else{
						$super_premium = $super_premium + $existingValue->super_premium;
						}
					
					$updatecard->mass_final=$mass;
					$updatecard->premium_final=$premium;
					$updatecard->super_premium_final=$super_premium;
					$updatecard->tc_final=$mass+$premium+$super_premium;
					$updatecard->left_loan_amount=$leftamount;
					$updatecard->save();
					}
					else
					{
						$id=$_loan->id;
						$updatecard = AgentPayout::find($id);
						if($_loan->mass=='-' || $_loan->mass=='' || $_loan->mass==NULL ){
							$mass=0;
							}
							else{
								$mass=$_loan->mass;
							}
							if($_loan->premium=='-' || $_loan->premium=='' || $_loan->premium==NULL){
							$premium=0;
							}
							else{
								$premium=$_loan->premium;
							}
							if($_loan->super_premium=='-' || $_loan->super_premium=='' || $_loan->super_premium==NULL ){
							$super_premium=0;
							}
							else{
								$super_premium=$_loan->super_premium;
							}
						$updatecard->mass_final=$mass;
					$updatecard->premium_final=$premium;
					$updatecard->super_premium_final=$super_premium;
					$updatecard->tc_final=$mass+$premium+$super_premium;
					$updatecard->left_loan_amount=0;
					$updatecard->save();
					}
					
				
				
			
			
			}
			
			}	
			
			protected function checkLoanData($loanData,$array){
			
				while($loanData >= 50000) {
					if($loanData>=150000){			
					$loanData=$loanData-150000;
					$array['super_premium_final'] =$array['super_premium_final']+1;;
					}
					elseif($loanData>=100000){
					$loanData=$loanData-100000;
					$array['premium_final'] =$array['premium_final']+1;
					}
					else{
					$loanData=$loanData-50000;
					$array['mass_final'] =$array['mass_final']+1;
					
					}					
					if($loanData >= 50000){
						$this->checkLoanData($loanData ,$array);

					}
					else{
						
						$array['left_loan_amount'] =$loanData;
						return $array;
					
					}
				}
				return $array;
			}
			
			
			public function UpdateTime()
			{
				$agentData=AgentPayout::get();
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				
				$updateMod = AgentPayout::find($payoutId);
					$time=$_agent->month.'-'.$_agent->year;
					$updateMod->sales_time = $time;
					$updateMod->save();
				
				}
				exit;
			}
			public function AgentMidPoint(){
				
				
				$salestimeUniq = AgentPayout::groupBy('sales_time')->selectRaw('count(*) as total, sales_time')->get();
				$tc= array();
				$tc_final = array();

				$tc_d= array();
				$tc_final_d = array();

				$tc_abhu= array();
				$tc_final_abhu = array();
				
				foreach($salestimeUniq as $saletime)
				{
					$saletimeList =AgentPayout::where("agent_product","CARD")->where("sales_time",$saletime->sales_time)->get();
					foreach($saletimeList as $allList){
						if($allList->tc=="-" || $allList->tc==""){
							
						}
						else{
						$tc[] = $allList->tc;
						
						}
						if($allList->tc_final=="-" || $allList->tc_final==""){
							
						}
						else{
							$tc_final[] = $allList->tc_final;
						}
					}
					sort($tc);					
					$tcdatamin = min($tc);
					$tcdatamax = max($tc);
					$tcmid=round(count($tc)/2);
					$tcmiddata=$tc[$tcmid];					 
					sort($tc_final);
					$tc_finalmin = min($tc_final);
					$tc_finalmax = max($tc_final);
					$tc_finalmid=round(count($tc_final)/2);
					$finalmiddata=$tc_final[$tc_finalmid];
					$payoutData = new AgentPayoutMidPoint();
					$payoutData->sales_time=$saletime->sales_time;
					$payoutData->maximum_cards=$tcdatamax;
					$payoutData->minimum_cards=$tcdatamin;
					$payoutData->middle_cards=$tcmiddata;
					$payoutData->maximum_cards_loan=$tc_finalmax;
					$payoutData->minimum_cards_loan=$tc_finalmin;
					$payoutData->middle_cards_loan=$finalmiddata;
					$payoutData->location="Both";
					$payoutData->save();
					
					$saletimelocationd =AgentPayout::where("agent_product","CARD")->where("sales_time",$saletime->sales_time)->where("location",'DUBAI')->get();
					foreach($saletimelocationd as $allListd){
					if($allListd->tc=="-" || $allListd->tc==""){
						
					}
					else{
					$tc_d[] = $allListd->tc;
					
					}
					if($allListd->tc_final=="-" || $allListd->tc_final==""){
						
					}
					else{
						$tc_final_d[] = $allListd->tc_final;
					}
					}
					//dubai data
					sort($tc_d);
					$tc_d_min = min($tc_d);
					$tc_d_max = max($tc_d);
					$tc_d_mid=round(count($tc_d)/2);
					$tc_d_middata=$tc_d[$tc_d_mid];	
					
					sort($tc_final_d);
					$tc_final_d_min = min($tc_final_d);
					$tc_final_d_max = max($tc_final_d);
					$tc_final_d_mid=round(count($tc_final_d)/2);
					$tc_final_d_middata=$tc_final_d[$tc_final_d_mid];
					
					$payoutDatad = new AgentPayoutMidPoint();
					$payoutDatad->sales_time=$saletime->sales_time;
					$payoutDatad->maximum_cards=$tc_d_max;
					$payoutDatad->minimum_cards=$tc_d_min;
					$payoutDatad->middle_cards=$tc_d_middata;
					$payoutDatad->maximum_cards_loan=$tc_final_d_max;
					$payoutDatad->minimum_cards_loan=$tc_final_d_min;
					$payoutDatad->middle_cards_loan=$tc_final_d_middata;
					$payoutDatad->location="DUBAI";
					$payoutDatad->save();
					
					$saletimelocationab =AgentPayout::where("agent_product","CARD")->where("sales_time",$saletime->sales_time)->where("location",'ABU DHABI')->get();
					foreach($saletimelocationab as $allListab){
					if($allListab->tc=="-" || $allListab->tc==""){
					
					}
					else{
					$tc_abhu[] = $allListab->tc;
					
					}
					if($allListab->tc_final=="-" || $allListab->tc_final==""){
					
					}
					else{
						$tc_final_abhu[] = $allListab->tc_final;
					}
					}
					
					//Abu
					sort($tc_abhu);
					$tc_abhu_min = min($tc_abhu);
					$tc_abhu_max = max($tc_abhu);
					$tc_abhu_mid=round(count($tc_abhu)/2);
					$tc_abhu_middata=$tc_abhu[$tc_abhu_mid];
					sort($tc_final_abhu);
					$tc_final_abhu_min = min($tc_final_abhu);
					$tc_final_abhu_max = max($tc_final_abhu);
					$tc_final_abhu_mid=round(count($tc_final_abhu)/2);
					$tc_final_abhu_middata=$tc_final_abhu[$tc_final_abhu_mid];
					$payoutDataabu = new AgentPayoutMidPoint();
					$payoutDataabu->sales_time=$saletime->sales_time;
					$payoutDataabu->maximum_cards=$tc_abhu_max;
					$payoutDataabu->minimum_cards=$tc_abhu_min;
					$payoutDataabu->middle_cards=$tc_abhu_middata;
					$payoutDataabu->maximum_cards_loan=$tc_final_abhu_max;
					$payoutDataabu->minimum_cards_loan=$tc_final_abhu_min;
					$payoutDataabu->middle_cards_loan=$tc_final_abhu_middata;
					$payoutDataabu->location="ABU DHABI";
					$payoutDataabu->save();
					
					//exit;
					
					
					
				}
				
				
			}
			public function UpdateTimeRange(){
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
					$PayoutData =AgentPayout::whereRaw($whereraw)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = AgentPayout::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						$updateMod->save();
					}
					
				
			}
			
			}
			public function UpdatecardData(){
				
				$loanData=AgentPayout::where("agent_product","CARD")->get();
				//$index = 0;
				foreach($loanData as $_loan){
				$loanProduct=$_loan->agent_product;
				
				$id=$_loan->id;
				$updatecard = AgentPayout::find($id);
				
					
					$loanData=$_loan->final_loan_amount;
					if($loanData > 0)
					{
					//$loanData=3600;
					$array['mass_final']=0;
					$array['premium_final']=0;
					$array['super_premium_final']=0;
					$array['left_loan_amount']=0;
					if($loanData<=50000){
						$array['left_loan_amount']=$loanData;
					}
					$fdata=$this->checkCardsData($loanData,$array);
					$mass=$fdata['mass_final'];
					$premium=$fdata['premium_final'];
					$super_premium=$fdata['super_premium_final'];
					$leftamount=$fdata['left_loan_amount'];
				
						$existingValue = AgentPayout::where("id",$id)->first();
						if($existingValue->mass=='-' || $existingValue->mass=='' || $existingValue->mass==NULL){
						$mass = $mass + 0;
						}
						else{
						$mass = $mass + $existingValue->mass;
						}
						if($existingValue->premium=='-' ||$existingValue->premium=='' ||$existingValue->premium==NULL){
							$premium = 0;
						}
						else{
						$premium = $existingValue->premium;
						}
						if($existingValue->super_premium=='-' || $existingValue->super_premium=='' || $existingValue->super_premium==NULL){
							$super_premium = 0;
						}
						else{
						$super_premium =$existingValue->super_premium;
						}
					
					$updatecard->mass_final=$mass;
					$updatecard->premium_final=$premium;
					$updatecard->super_premium_final=$super_premium;
					$updatecard->tc_final=$mass+$premium+$super_premium;
					$updatecard->left_loan_amount=$leftamount;
					$updatecard->save();
					}
					else
					{
						$id=$_loan->id;
						$updatecard = AgentPayout::find($id);
						if($_loan->mass=='-' || $_loan->mass=='' || $_loan->mass==NULL ){
							$mass=0;
							}
							else{
								$mass=$_loan->mass;
							}
							if($_loan->premium=='-' || $_loan->premium=='' || $_loan->premium==NULL ){
							$premium=0;
							}
							else{
								$premium=$_loan->premium;
							}
							if($_loan->super_premium=='-' || $_loan->super_premium=='' || $_loan->super_premium==NULL ){
							$super_premium=0;
							}
							else{
								$super_premium=$_loan->super_premium;
							}
						$updatecard->mass_final=$mass;
					$updatecard->premium_final=$premium;
					$updatecard->super_premium_final=$super_premium;
					$updatecard->tc_final=$mass+$premium+$super_premium;
					$updatecard->left_loan_amount=0;
					$updatecard->save();
					}
			
			
			}
			
			}
			protected function checkCardsData($loanData,$array){
			
				while($loanData >= 50000) {
				
					
				
					$loanData=$loanData-50000;
					$array['mass_final'] =$array['mass_final']+1;
					
								
					if($loanData >= 50000){
						$this->checkLoanData($loanData ,$array);

					}
					else{
						
						$array['left_loan_amount'] =$loanData;
						return $array;
					
					}
				}
				return $array;
			}
			public function AgentMidPointbyRange(){
				
				
				$salestimeUniq = AgentPayout::groupBy('range_id')->selectRaw('count(*) as total, range_id')->get();
				$rangeArray = array();
								
				foreach($salestimeUniq as $saletime)
				{
					
				if($saletime->range_id!=NULL || $saletime->range_id!='' ){
					$rangeArray[] = $saletime->range_id;
				$tc= array();
				$tc_card= array();
				$tc_final = array();
				$tc_d= array();
				$tc_card_d= array();
				$tc_final_d = array();
				$tc_abhu= array();
				$tc_card_abhu= array();
				$tc_final_abhu = array();
					$saletimeList =AgentPayout::where("agent_product","CARD")->where("range_id",$saletime->range_id)->get();
					if(count($saletimeList)>0)
					{
					foreach($saletimeList as $allList){
						$rangedata=WorkTimeRange::where("id",$allList->range_id)->first();
						$range=$rangedata->range;
						if($allList->tc=="-" || $allList->tc==""){
							$tc[] = 0;
						}
						else{
						$tc[] = $allList->tc;
						
						}
						if($allList->tc_final=="-" || $allList->tc_final==""){
							$tc_final[] = 0;
						}
						else{
						$tc_final[] = $allList->tc_final;
						}
						if($allList->tc_card=="-" || $allList->tc_card=="" || $allList->tc_card==NULL){
							$tc_card[] = 0;
						}
						else{
						$tc_card[] = $allList->tc_card;
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
										
					sort($tc_final);
					$tc_finalmin = min($tc_final);
					$tc_finalmax = max($tc_final);
					if(count($tc_final) > 1)
					{
					$tc_finalmid=round(count($tc_final)/2);
					$finalmiddata=$tc_final[$tc_finalmid];	
					}
					else{
						$tc_finalmid=1;	
					}
					sort($tc_card);
					$tc_card_min = min($tc_card);
					$tc_card_max = max($tc_card);
					if(count($tc_card) > 1)
					{
					$tc_card_mid=round(count($tc_card)/2);
					$tc_card_mid_data=$tc_card[$tc_card_mid];	
					}
					else{
						$tc_card_mid_data=1;	
					}
					
					$payoutData = new AgentPayoutByRange();
					$payoutData->range_id=$saletime->range_id;
					$payoutData->range_title=$range;
					$payoutData->maximum_cards=$tcdatamax;
					$payoutData->minimum_cards=$tcdatamin;
					$payoutData->middle_cards=$tcmiddata;
					$payoutData->maximum_cards_loan=$tc_finalmax;
					$payoutData->minimum_cards_loan=$tc_finalmin;
					$payoutData->middle_cards_loan=$finalmiddata;
					$payoutData->maximum_tc_cards=$tc_card_max;
					$payoutData->minimum_tc_cards=$tc_card_min;
					$payoutData->middle_tc_cards=$tc_card_mid_data;
					$payoutData->location="Both";
					$payoutData->total_agent=count($saletimeList);
					$payoutData->respone_data_tc=json_encode($tc);
					$payoutData->respone_data_tcf=json_encode($tc_final);
					$payoutData->respone_data_tc_card=json_encode($tc_card);
					$payoutData->save();
					}
					
					$saletimelocationd =AgentPayout::where("agent_product","CARD")->where("range_id",$saletime->range_id)->where("location",'DUBAI')->get();
					//print_r($saletimelocationd);exit;
					
					if(count($saletimelocationd) >0)
					{						
					foreach($saletimelocationd as $allListd){
						$rangedatad=WorkTimeRange::where("id",$allListd->range_id)->first();
						$ranged=$rangedatad->range;
					if($allListd->tc=="-" || $allListd->tc==""){
						$tc_d[] = 0;
					}
					else{
					$tc_d[] = $allListd->tc;
					
					}
					if($allListd->tc_final=="-" || $allListd->tc_final==""){
						$tc_final_d[] = 0;
					}
					else{
						$tc_final_d[] = $allListd->tc_final;
					}
					
					if($allListd->tc_card=="-" || $allListd->tc_card=="" || $allListd->tc_card==NULL){
						$tc_card_d[] = 0;
					}
					else{
						$tc_card_d[] = $allListd->tc_card;
					}
					}
					//dubai data
					sort($tc_d);
					//print_r($tc_d);//exit;
					$tc_d_min = min($tc_d);
					$tc_d_max = max($tc_d);
					
					if(count($tc_d) > 1)
					{
					$tc_d_mid=round(count($tc_d)/2);
					
					$tc_d_middata=$tc_d[$tc_d_mid];		
					}
					else{
						$tc_d_middata=1;	
					}
					
					
					sort($tc_final_d);
					$tc_final_d_min = min($tc_final_d);
					$tc_final_d_max = max($tc_final_d);
					if(count($tc_final_d) > 1)
					{
					$tc_final_d_mid=round(count($tc_final_d)/2);
					$tc_final_d_middata=$tc_final_d[$tc_final_d_mid];		
					}
					else{
						$tc_final_d_middata=1;	
					}
					
					sort($tc_card_d);
					$tc_card_d_min = min($tc_card_d);
					$tc_card_d_max = max($tc_card_d);
					if(count($tc_card_d) > 1)
					{
					$tc_card_d_mid=round(count($tc_card_d)/2);
					$tc_card_d_middata=$tc_card_d[$tc_card_d_mid];		
					}
					else{
						$tc_card_d_middata=1;	
					}
					
					
					$payoutDatad = new AgentPayoutByRange();
					$payoutDatad->range_id=$saletime->range_id;
					$payoutDatad->range_title=$ranged;
					$payoutDatad->maximum_cards=$tc_d_max;
					$payoutDatad->minimum_cards=$tc_d_min;
					$payoutDatad->middle_cards=$tc_d_middata;
					$payoutDatad->maximum_cards_loan=$tc_final_d_max;
					$payoutDatad->minimum_cards_loan=$tc_final_d_min;
					$payoutDatad->middle_cards_loan=$tc_final_d_middata;
					$payoutDatad->maximum_tc_cards=$tc_card_d_max;
					$payoutDatad->minimum_tc_cards=$tc_card_d_min;
					$payoutDatad->middle_tc_cards=$tc_card_d_middata;
					$payoutDatad->location="DUBAI";
					$payoutDatad->total_agent=count($saletimelocationd);
					$payoutDatad->respone_data_tc=json_encode($tc_d);
					$payoutDatad->respone_data_tcf=json_encode($tc_final_d);
					$payoutDatad->respone_data_tc_card=json_encode($tc_card_d);
					
					$payoutDatad->save();
					}
					$saletimelocationab =AgentPayout::where("agent_product","CARD")->where("range_id",$saletime->range_id)->where("location",'ABU DHABI')->get();
					if(count($saletimelocationab)>0)
					{
					foreach($saletimelocationab as $allListab){
						$rangedataab=WorkTimeRange::where("id",$allListab->range_id)->first();
						$rangeab=$rangedataab->range;
					if($allListab->tc=="-" || $allListab->tc==""){
					$tc_abhu[] =0;
					}
					else{
					$tc_abhu[] = $allListab->tc;
					
					}
					if($allListab->tc_final=="-" || $allListab->tc_final==""){
					$tc_final_abhu[] = 0;
					}
					else{
						$tc_final_abhu[] = $allListab->tc_final;
					}
					
					if($allListab->tc_card=="-" || $allListab->tc_card==""){
					$tc_card_abhu[] = 0;
					}
					else{
						$tc_card_abhu[] = $allListab->tc_card;
					}
					}
					
					//Abu
					sort($tc_abhu);
					
					
					$tc_abhu_min = min($tc_abhu);
					$tc_abhu_max = max($tc_abhu);
					if(count($tc_abhu) > 1)
					{
					$tc_abhu_mid=round(count($tc_abhu)/2);//exit;
					$tc_abhu_middata=$tc_abhu[$tc_abhu_mid];		
					}
					else{
						$tc_abhu_middata=1;	
					}
					
					sort($tc_final_abhu);
					$tc_final_abhu_min = min($tc_final_abhu);
					$tc_final_abhu_max = max($tc_final_abhu);
					if(count($tc_final_abhu) > 1)
					{
					$tc_final_abhu_mid=round(count($tc_final_abhu)/2);
					$tc_final_abhu_middata=$tc_final_abhu[$tc_final_abhu_mid];		
					}
					else{
						$tc_final_abhu_middata=1;	
					}
					
					sort($tc_card_abhu);
					$tc_card_abhu_min = min($tc_card_abhu);
					$tc_card_abhu_max = max($tc_card_abhu);
					if(count($tc_card_abhu) > 1)
					{
					$tc_card_abhu_mid=round(count($tc_card_abhu)/2);
					$tc_card_abhu_middata=$tc_card_abhu[$tc_card_abhu_mid];		
					}
					else{
						$tc_card_abhu_middata=1;	
					}
					
					$payoutDataabu = new AgentPayoutByRange();
					$payoutDataabu->range_id=$saletime->range_id;
					$payoutDataabu->range_title=$rangeab;
					$payoutDataabu->maximum_cards=$tc_abhu_max;
					$payoutDataabu->minimum_cards=$tc_abhu_min;
					$payoutDataabu->middle_cards=$tc_abhu_middata;
					$payoutDataabu->maximum_cards_loan=$tc_final_abhu_max;
					$payoutDataabu->minimum_cards_loan=$tc_final_abhu_min;
					$payoutDataabu->middle_cards_loan=$tc_final_abhu_middata;
					$payoutDataabu->maximum_tc_cards=$tc_card_abhu_max;
					$payoutDataabu->minimum_tc_cards=$tc_card_abhu_min;
					$payoutDataabu->middle_tc_cards=$tc_card_abhu_middata;
					$payoutDataabu->location="ABU DHABI";
					$payoutDataabu->total_agent=count($saletimelocationab);
					$payoutDataabu->respone_data_tc=json_encode($tc_abhu);
					$payoutDataabu->respone_data_tcf=json_encode($tc_final_abhu);
					$payoutDataabu->respone_data_tc_card=json_encode($tc_card_abhu);
					$payoutDataabu->save();
					}
					//exit;
					
					
					
				}
			}
			echo "done";
				exit;
				
			}
			public function UpdateloanAmountData()
			{
				$agentData=AgentPayout::where("agent_product","!=","CARD")->get();
				
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				$loanamout=$_agent->final_loan_amount;
				if($_agent->mass=='-' || $_agent->mass=='' ){
				$mass =0;
				}
				else{
					$mass =$_agent->mass*50000;
				}
				if($_agent->premium=='-' || $_agent->premium=='' ){
				$premium = 0;
				}
				else{
					$premium = $_agent->premium*100000;
				}
				if($_agent->super_premium=='-' || $_agent->super_premium=='' ){
				$super_premium = 0;
				}
				else{
					$super_premium = $_agent->super_premium*150000;
				}
				$finalamount=$loanamout+$mass+$premium+$super_premium;
				$updateMod = AgentPayout::find($payoutId);
				
					$updateMod->final_loan_amount = $finalamount;
					$updateMod->mass_final = 0;
					$updateMod->premium_final = 0;
					$updateMod->super_premium_final = 0;
					$updateMod->save();
				
				}
				exit;
			}
			
			public function sePayoutGhraph(Request $request){
				
				$totalData=AgentPayoutByRange::where("location","Both")->orderBy("range_id","ASC")->get();
				$totalDatadubai=AgentPayoutByRange::where("location","DUBAI")->orderBy("range_id","ASC")->get();
				$totalDataabu=AgentPayoutByRange::where("location","ABU DHABI")->orderBy("range_id","ASC")->get();
				return view("SEPayout/SEPayoutGraph",compact('totalData','totalDatadubai','totalDataabu'));
				
			}
			public static function getRangeValueData($code)
			{
				
				$name =WorkTimeRange::where("id",$code)->first();
				if($name != '')
				{
					return $name->formatted_range;
				}
				else
				{
					return '--';
				}
				 
			}
			public function UpdateTcCardData()
			{
				$agentData=AgentPayout::where("agent_product","CARD")->get();
				
				foreach($agentData as $_agent){
				$payoutId=$_agent->id;
				if($_agent->mass=='-' || $_agent->mass=='' || $_agent->mass==NULL ){
				$mass =0;
				}
				else{
					$mass =$_agent->mass;
				}
				if($_agent->premium=='-' || $_agent->premium=='' || $_agent->premium==NULL ){
				$premium = 0;
				}
				else{
					$premium = $_agent->premium;
				}
				if($_agent->super_premium=='-' || $_agent->super_premium=='' || $_agent->super_premium==NULL ){
				$super_premium = 0;
				}
				else{
					$super_premium = $_agent->super_premium;
				}
				$finalTC=$mass+$premium+$super_premium;
				$updateMod = AgentPayout::find($payoutId);
				
					$updateMod->tc_card = $finalTC;
					$updateMod->save();
				
				}
				echo "Done";
				exit;
			}
			
}
