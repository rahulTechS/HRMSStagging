<?php

namespace App\Http\Controllers\HiringProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HiringProcess\HiringProcess;
use App\Models\HiringProcess\HiringManager;
use Session;

class HiringProcessController extends Controller
{
    public function hiringProcess()
	{
		
		return view("HiringProcess/HiringProcess");
	}
	public function addHiringProcess()
	{
		$managerList=HiringManager::get();
		
		return view("HiringProcess/addHiringProcess",compact('managerList'));
	}
	public function addHiriingProcessPost(Request $rq)
	{
		$obj = new HiringProcess();
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->interview1 = $rq->input('interview_1');
		$obj->notes1 = $rq->input('notes_1');
		$obj->status1 = $rq->input('status_1');
		$obj->interview2 = $rq->input('interview_2');
		$obj->notes2 = $rq->input('notes_2');
		$obj->status2 = $rq->input('status_2');
		$obj->interview3 = $rq->input('interview_3');
		$obj->notes3 = $rq->input('notes_3');
		$obj->status3 = $rq->input('status_3');
		$obj->referred_by = $rq->input('referred_by');
		$obj->save();
		$rq->session()->flash('message','Hiring manager Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Hiring manager Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
        //return redirect('visaType');
	}
	public function setOffSetForHiringProcess(Request $request)
			{
				echo $offset = $request->offset;
				$request->session()->put('offset_hiring_filter',$offset);
				 return  redirect('visaTypeList');
			}
	public function hiringManagerList(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				//echo $paginationValue;exit;
				$whereraw='';
				
				 
				 $selectedFilter['hiringName'] = '';
				 $selectedFilter['hiringMobile'] = '';
				 $selectedFilter['FirstInterview'] = '';
				 $selectedFilter['FirstStatus'] = '';
				 $selectedFilter['SecondInterview'] = '';
				 $selectedFilter['SecondStatus'] = '';
				 $selectedFilter['ThirdInterview'] = '';
				 $selectedFilter['ThirdStatus'] = '';
				  
				
				
				if(!empty($request->session()->get('hiring_name_filter_inner_list')) && $request->session()->get('hiring_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('hiring_name_filter_inner_list');
					 $selectedFilter['hiringName'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And name = "'.$name.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('hiring_mobile_filter_inner_list')) && $request->session()->get('hiring_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('hiring_mobile_filter_inner_list');
					 $selectedFilter['hiringMobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile = "'.$mobile.'"';
					}
					else
					{
						$whereraw .= ' And mobile = "'.$mobile.'"';
					}
				}
				if(!empty($request->session()->get('hiring_interview1_filter_inner_list')) && $request->session()->get('hiring_interview1_filter_inner_list') != 'All')
				{
					$interview1 = $request->session()->get('hiring_interview1_filter_inner_list');
					 $selectedFilter['FirstInterview'] = $interview1;
					 if($whereraw == '')
					{
						$whereraw = 'interview1 = "'.$interview1.'"';
					}
					else
					{
						$whereraw .= ' And interview1 = "'.$interview1.'"';
					}
				}
				if(!empty($request->session()->get('hiring_status1_filter_inner_list')) && $request->session()->get('hiring_status1_filter_inner_list') != 'All')
				{
					$status1 = $request->session()->get('hiring_status1_filter_inner_list');
					 $selectedFilter['FirstStatus'] = $status1;
					 if($whereraw == '')
					{
						$whereraw = 'status1 = "'.$status1.'"';
					}
					else
					{
						$whereraw .= ' And status1 = "'.$status1.'"';
					}
				}
				if(!empty($request->session()->get('hiring_interview2_filter_inner_list')) && $request->session()->get('hiring_interview2_filter_inner_list') != 'All')
				{
					$interview2 = $request->session()->get('hiring_interview2_filter_inner_list');
					 $selectedFilter['SecondInterview'] = $interview2;
					 if($whereraw == '')
					{
						$whereraw = 'interview2 = "'.$interview2.'"';
					}
					else
					{
						$whereraw .= ' And interview2 = "'.$interview2.'"';
					}
				}
				if(!empty($request->session()->get('hiring_status2_filter_inner_list')) && $request->session()->get('hiring_status2_filter_inner_list') != 'All')
				{
					$status2 = $request->session()->get('hiring_status2_filter_inner_list');
					 $selectedFilter['SecondStatus'] = $status2;
					 if($whereraw == '')
					{
						$whereraw = 'status2 = "'.$status2.'"';
					}
					else
					{
						$whereraw .= ' And status2 = "'.$status2.'"';
					}
				}
				if(!empty($request->session()->get('hiring_interview3_filter_inner_list')) && $request->session()->get('hiring_interview3_filter_inner_list') != 'All')
				{
					$interview3 = $request->session()->get('hiring_interview3_filter_inner_list');
					 $selectedFilter['ThirdInterview'] = $interview3;
					 if($whereraw == '')
					{
						$whereraw = 'interview3 = "'.$interview3.'"';
					}
					else
					{
						$whereraw .= ' And interview3 = "'.$interview3.'"';
					}
				}
				if(!empty($request->session()->get('hiring_status3_filter_inner_list')) && $request->session()->get('hiring_status3_filter_inner_list') != 'All')
				{
					$status3 = $request->session()->get('hiring_status3_filter_inner_list');
					 $selectedFilter['ThirdStatus'] = $status3;
					 if($whereraw == '')
					{
						$whereraw = 'status3 = "'.$status3.'"';
					}
					else
					{
						$whereraw .= ' And status3 = "'.$status3.'"';
					}
				}
				
				
				
				$hiringnameArray = array();
				if($whereraw == '')
				{
				$name = HiringProcess::get();
				}
				else
				{
					
					$name = HiringProcess::whereRaw($whereraw)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$hiringnameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = HiringProcess::get();
				}
				else
				{
					
					$mobile = HiringProcess::whereRaw($whereraw)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				$FirstInterviewArray = array();
				$interview1 =array();
				if($whereraw == '')
				{
				$interview1 = HiringProcess::get();
				$interview1val=array();
				foreach($interview1 as $_data){
				$interview1val[]=$_data->interview1;
				}
				}
				else
				{
					
					$interview1 = HiringProcess::whereRaw($whereraw)->get();
					$interview1val=array();
					foreach($interview1 as $_data){
					$interview1val[]=$_data->interview1;
				}
					
				}
				$interview1=HiringManager::whereIn("id",$interview1val)->get();
				foreach($interview1 as $_interview1)
				{
					//echo $_lname->last_name;exit;
					$FirstInterviewArray[$_interview1->id] = $_interview1->manager_name;
				}
				$FirstStatusArray = array();
				if($whereraw == '')
				{
				$status1 = HiringProcess::get();
				}
				else
				{
					
					$status1 = HiringProcess::whereRaw($whereraw)->get();
					
				}
				
				foreach($status1 as $_status1)
				{
					//echo $_lname->last_name;exit;
					$FirstStatusArray[$_status1->status1] = $_status1->status1;
				}
				
				$SecondInterviewArray = array();
				$interview2 = array();
				if($whereraw == '')
				{
				$interview2 = HiringProcess::get();
				$interview2val=array();
				foreach($interview2 as $_data){
				$interview2val[]=$_data->interview2;
				}
				}
				else
				{
					
					$interview2 = HiringProcess::whereRaw($whereraw)->get();
					$interview2val=array();
					foreach($interview2 as $_data){
					$interview2val[]=$_data->interview2;
				}
					
				}
				$interview2=HiringManager::whereIn("id",$interview2val)->get();
				foreach($interview2 as $_interview2)
				{
					//echo $_lname->last_name;exit;
					$SecondInterviewArray[$_interview2->id] = $_interview2->manager_name;
				}
				
				$SecondStatusArray = array();
				if($whereraw == '')
				{
				$status2 = HiringProcess::get();
				}
				else
				{
					
					$status2 = HiringProcess::whereRaw($whereraw)->get();
					
				}
				
				foreach($status2 as $_status2)
				{
					//echo $_lname->last_name;exit;
					if($_status2->status2!=''){
					$SecondStatusArray[$_status2->status2] = $_status2->status2;
					}
				}
				$ThirdInterviewArray = array();
				$interview3 = array();
				if($whereraw == '')
				{
				$interview3 = HiringProcess::get();
				$interview3val=array();
				foreach($interview3 as $_data){
				$interview3val[]=$_data->interview3;
				}
				}
				else
				{
					
					$interview3 = HiringProcess::whereRaw($whereraw)->get();
					$interview3val=array();
					foreach($interview3 as $_data){
					$interview3val[]=$_data->interview3;
				}
					
				}
				$interview3=HiringManager::whereIn("id",$interview3val)->get();
				foreach($interview3 as $_interview3)
				{
					//echo $_lname->last_name;exit;
					$ThirdInterviewArray[$_interview3->id] = $_interview3->manager_name;
				}
				$ThirdStatusArray = array();
				if($whereraw == '')
				{
				$status3 = HiringProcess::get();
				}
				else
				{
					
					$status3 = HiringProcess::whereRaw($whereraw)->get();
					
				}
				
				foreach($status3 as $_status3)
				{
					//echo $_lname->last_name;exit;
					if($_status3->status3!=''){
					$ThirdStatusArray[$_status3->status3] = $_status3->status3;
					}
				}
				
				if($whereraw != '')
				{
					//echo "h1";exit;
					$hiringList = HiringProcess::whereRaw($whereraw)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = HiringProcess::whereRaw($whereraw)->get()->count();				
				}
				else
				{
					//echo "h2";exit;
					$hiringList = HiringProcess::orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = HiringProcess::get()->count();					
				}
				
				$hiringList->setPath(config('app.url/hiringManagerList'));
				
				
				
				
			
			return view("HiringProcess/HiringProcessList",compact('hiringList','paginationValue','reportsCount','selectedFilter','ThirdStatusArray','ThirdInterviewArray','SecondStatusArray','SecondInterviewArray','FirstStatusArray','FirstInterviewArray','mobileArray','hiringnameArray'));
		}
		public function setFilterbyHiringName(Request $request)
		{
			$name = $request->name;
			$request->session()->put('hiring_name_filter_inner_list',$name);	
		}
		public function setfilterByVMobile(Request $request)
		{
			$mobile = $request->mobile;
			$request->session()->put('hiring_mobile_filter_inner_list',$mobile);	
		}
		public function setfilterByFirstInterview(Request $request)
		{
			$interview1 = $request->interview1;
			$request->session()->put('hiring_interview1_filter_inner_list',$interview1);
		}
		public function setfilterByFirstStatus(Request $request)
		{
			$status1 = $request->status1;
			$request->session()->put('hiring_status1_filter_inner_list',$status1);
		}
		public function setfilterBySecondInterview(Request $request)
		{
			$interview2 = $request->interview2;
			$request->session()->put('hiring_interview2_filter_inner_list',$interview2);
		}
		public function setfilterBySecondStatus(Request $request)
		{
			$status2 = $request->status2;
			$request->session()->put('hiring_status2_filter_inner_list',$status2);
		}
		public function setfilterByThirdInterview(Request $request)
		{
			$interview3 = $request->interview3;
			$request->session()->put('hiring_interview3_filter_inner_list',$interview3);
	
		}
		public function setfilterByThirdStatus(Request $request)
		{
			$status3 = $request->status3;
			$request->session()->put('hiring_status3_filter_inner_list',$status3);	
		}
		
	
	
	
	
	
	public function deleteVisaType(Request $req)
	{
		$visaType_obj = visaType::find($req->id);
       
        $visaType_obj->status = 3;
       
        $visaType_obj->save();
        $req->session()->flash('message','Visa Type deleted Successfully.');
        //return redirect('VisaTypeList');
		$response['code'] = '200';
		  $response['message'] = "Visa Type deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
	}
	public static function getManagerName($name){
		$name =HiringManager::where("id",$name)->first();
			if($name != '')
			{
				return $name->manager_name;
			}
			else
			{
				return '--';
			}
	}
	public function checkStatus($rowId=NULL)
	{
		$getstatus = HiringProcess::where("id",$rowId)->first();
		$response['code'] = '200';
		 $response['status_1'] = $getstatus->status1;
		 $response['status_2'] = $getstatus->status2;
		 $response['status_3'] = $getstatus->status3;
		   
		echo json_encode($response);
		   exit;
	}
	public function editHiriningManager($rowId=NULL)
	{
		$hiringdata = HiringProcess::where("id",$rowId)->first();
		$managerList=HiringManager::get();
		return view("HiringProcess/editHiringProcess",compact('hiringdata','managerList'));
	}
	public function updateHiriingProcessPost(Request $rq)
	{
		
		$obj = HiringProcess::find($rq->input('id'));
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->interview1 = $rq->input('interview_1');
		$obj->notes1 = $rq->input('notes_1');
		$obj->status1 = $rq->input('status_1');
		$obj->interview2 = $rq->input('interview_2');
		$obj->notes2 = $rq->input('notes_2');
		$obj->status2 = $rq->input('status_2');
		$obj->interview3 = $rq->input('interview_3');
		$obj->notes3 = $rq->input('notes_3');
		$obj->status3 = $rq->input('status_3');
		$obj->referred_by = $rq->input('referred_by');
		$obj->save();
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function viewHiringProcess($rowId=NULL){
		$hiringprocess = HiringProcess::where("id",$rowId)->first();
		
		return view("HiringProcess/viewHiringprocess",compact('hiringprocess'));
		
	}
	
}
