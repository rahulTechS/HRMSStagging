<?php

namespace App\Http\Controllers\TrainingCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Job\JobOpening;
use App\Models\Job\JobOpeningTarget;
use App\Models\JobFunction\JobFunction;
use App\Models\Company\Department;
use App\Models\Recruiter\Designation;
use App\Models\TrainingCategory\TrainingCategory;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;
use App\Models\Onboarding\TrainingType;

class TrainingCategoryController extends Controller
{
    
	 public function manageEmpTraining(Request $request)
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingCategory::where("status",1)->get();
			$departmentF = array();
			$trainingF = array();
			if(!empty($request->session()->get('department_training_filter_inner_list')))
				{
					$deptTran = $request->session()->get('department_training_filter_inner_list');
					$departmentF = explode(",",$deptTran);
				}
				if(!empty($request->session()->get('trainingC_training_filter_inner_list')))
				{
					$TrTran = $request->session()->get('trainingC_training_filter_inner_list');
					$trainingF = explode(",",$TrTran);
					
				}
			return view("TrainingCategory/manageEmpTraining",compact('departmentF','trainingF','departmentArray','trainingCategoryArray'));
		}
	public function listingEmpTraining(Request $request)
		{
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			$departmentF = array();
			$trainingF = array();
				if(!empty($request->session()->get('offset_training')))
				{
					
					$paginationValue = $request->session()->get('offset_training');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('cname_training_filter_inner_list')))
				{
					
					$nameTran = $request->session()->get('cname_training_filter_inner_list');
					if($whereraw == '')
					{
						$whereraw = 'name like "%'.$nameTran.'%"';
					}
					else
					{
						$whereraw .= 'AND name like "%'.$nameTran.'%"';
					}
				}
				if(!empty($request->session()->get('department_training_filter_inner_list')))
				{
					$deptTran = $request->session()->get('department_training_filter_inner_list');
					$departmentF = explode(",",$deptTran);
					if($whereraw == '')
					{
						$whereraw = 'department IN ('.$deptTran.')';
					}
					else
					{
						$whereraw .= 'AND department IN ('.$deptTran.')';
					}
				}
				if(!empty($request->session()->get('trainingC_training_filter_inner_list')))
				{
					$TrTran = $request->session()->get('trainingC_training_filter_inner_list');
					$trainingF = explode(",",$TrTran);
					if($whereraw == '')
					{
						$whereraw = 'training_id IN ('.$TrTran.')';
					}
					else
					{
						$whereraw .= 'AND training_id IN ('.$TrTran.')';
					}
				}
				//echo $whereraw;exit;
				if($whereraw != '')
				{
					$reports = EmpTraining::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
				
					$reports = EmpTraining::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/listingEmpTraining'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = EmpTraining::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = EmpTraining::get()->count();
				}
				
				
				
				return view("TrainingCategory/listingEmpTraining",compact('departmentF','trainingF','reports','reportsCount','paginationValue','selectedFilter'));
		}
	public function addTrainingPanel()
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingType::where("status",1)->get();
			$empArray = Employee_details::where("status",1)->get();
			return view("TrainingCategory/addTrainingPanel",compact('departmentArray','trainingCategoryArray','empArray'));
		}
		
    public function editEmpTraining($trainingId)
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingType::where("status",1)->get();
			$empArray = Employee_details::where("status",1)->get();
			$empTraining = EmpTraining::where("id",$trainingId)->first();
			return view("TrainingCategory/editEmpTraining",compact('departmentArray','trainingCategoryArray','empArray','empTraining'));
		}
       public function TrainingCategory(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $TrainingCategoryDetails = TrainingCategory::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$TrainingCategoryDetails = $TrainingCategoryDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$TrainingCategoryDetails = $TrainingCategoryDetails->where("status",$status);
			}
			
		  $TrainingCategoryDetails = $TrainingCategoryDetails->get();
		  return view("TrainingCategory/TrainingCategory",compact('TrainingCategoryDetails','filterList'));
	   }
	   
	   public function addTrainingCategory()
	   {
		   return view("TrainingCategory/addTrainingCategory");
	   }
	   
	   public function addTrainingCategoryPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new TrainingCategory();
			$jobOpeningMod->name = $parameterInput['TrainingCategory']['name'];			
			$jobOpeningMod->status = $parameterInput['TrainingCategory']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Training Category Saved.');
			return redirect('TrainingCategory');
	   }
	   
	 
	   	   
	   public function updateTrainingCategory(Request $request)
	   {
		    $TrainingCategoryId = $request->id;
		    $TrainingCategoryDetails = TrainingCategory::where("id",$TrainingCategoryId)->first();
			
			return view("TrainingCategory/updateTrainingCategory",compact('TrainingCategoryDetails'));
	   }
	   
	   public function updateTrainingCategoryPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['TrainingCategory'];
		    $TrainingCategoryUpdateMod = TrainingCategory::find($datas['id']);
		    $TrainingCategoryUpdateMod->name = $datas['name'];
		    $TrainingCategoryUpdateMod->status = $datas['status'];
			$TrainingCategoryUpdateMod->save();
			
			$request->session()->flash('message','Training Category Updated.');
			return redirect('TrainingCategory');
	   }
	   
	   public function deleteTrainingCategory(Request $request)
	   {
		     $TrainingCategoryId = $request->id;
			 $TrainingCategoryUpdateMod = TrainingCategory::find($TrainingCategoryId);
			 $TrainingCategoryUpdateMod->status = 3;
			 $TrainingCategoryUpdateMod->save();
			 $request->session()->flash('message','Training Category Deleted.');
			 return redirect('TrainingCategory');
	   }
	   
	   public function appliedFilterOnTrainingCategory(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('TrainingCategory');
	   }
	   
	   public function resetTrainingCategoryFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('TrainingCategory');
	   }
	   public function saveEmployeeTraining(Request $request)
	   {
		   $parameterInput = $request->input();
		  /*  echo '<pre>';
		   print_r($parameterInput);
		   exit; */
		   $name = $parameterInput['name'];
		   $training_id = $parameterInput['training_id'];
		  
		   $emp_present_ids = implode(",",$parameterInput['emp_present_ids']);
		   $emp_present = count($parameterInput['emp_present_ids']);
		   $training_date = $parameterInput['training_date'];
		   $content_of_training = $parameterInput['content_of_training'];
		   $EmpTrainingModel = new EmpTraining();
		   $EmpTrainingModel->name = $name;
		   $EmpTrainingModel->training_id = $training_id;
		   
		   $EmpTrainingModel->emp_present_ids = $emp_present_ids;
		   $EmpTrainingModel->emp_present = $emp_present;
		   $EmpTrainingModel->training_date = date("Y-m-d",strtotime($training_date));
		   $EmpTrainingModel->content_of_training = $content_of_training;
		   $EmpTrainingModel->save();
		   $request->session()->put('departmentId_emptraining_filter_inner_list','');
		   echo "<p class='messT'>Training Save Successfully.</p>";
		   exit;
	   }
	   
	   public function updateEmployeeTraining(Request $request)
	   {
		   $parameterInput = $request->input();
		   $name = $parameterInput['name'];
		   $training_id = $parameterInput['training_id'];
		  
		   $emp_present_ids = implode(",",$parameterInput['emp_present_ids']);
		   $emp_present = count($parameterInput['emp_present_ids']);
		   $training_date = $parameterInput['training_date'];
		   $content_of_training = $parameterInput['content_of_training'];
		   $id = $parameterInput['id'];
		   $EmpTrainingModelUpdate = EmpTraining::find($id);
		   $EmpTrainingModelUpdate->name = $name;
		   $EmpTrainingModelUpdate->training_id = $training_id;
		  
		   $EmpTrainingModelUpdate->emp_present_ids = $emp_present_ids;
		   $EmpTrainingModelUpdate->emp_present = $emp_present;
		   $EmpTrainingModelUpdate->training_date = date("Y-m-d",strtotime($training_date));
		   $EmpTrainingModelUpdate->content_of_training = $content_of_training;
		   $EmpTrainingModelUpdate->save();
		   echo "<p class='messT'>Training Updated Successfully.</p>";
		   exit;
	   }
	   
	   public static function getEmpNameList($eids)
	   {
		   $eidArray = explode(",",$eids);
		   $nameEmp = '';
		   foreach($eidArray as $eid)
		   {
			   if($nameEmp != '')
			   {
				$nameEmp = $nameEmp.','.Employee_details::where("id",$eid)->first()->first_name;
			   }
			   else
			   {
				   $nameEmp = Employee_details::where("id",$eid)->first()->first_name;
			   }
		   }
		   return $nameEmp;
	   }
	   
	   public static function getTrainingCategory($tId)
	   {
		   $trainingModel =  TrainingType::where("id",$tId)->first();
		   if($trainingModel != '')
		   {
			   return $trainingModel->name;
		   }
		   else
		   {
			   return "-";
		   }
	   }
	   
	   public static function getDepartment($did)
	   {
		
		    $dModel =  Department::where("id",$did)->first();
		   if($dModel != '')
		   {
			   return $dModel->department_name;
		   }
		   else
		   {
			   return "-";
		   }
	   }
		public function setOffSetTraining(Request $request,$setc)
		{
			$request->session()->put('offset_training',$setc);
			 return redirect('listingEmpTraining');
		}
		public function trainingbyfilter(Request $request)
		{
			$parametersInput = $request->input();
			/* echo '<pre>';
			print_r($parametersInput);
			exit; */
			if(isset($parametersInput['tname']))
			{
			$tName = $parametersInput['tname'];
			}
			else
			{
				$tName ='';
			}
			if(isset($parametersInput['departmentT']))
			{
			$departmentT = $parametersInput['departmentT'];
			}
			else
			{
				$department ='';
				$departmentT ='';
			}
			if(isset($parametersInput['trainingC']))
			{
			$trainingC = $parametersInput['trainingC'];
			}
			else
			{
				$training ='';
				$trainingC ='';
			}
			
			if($departmentT != '')
			{
				$departmentTArray  = array_filter($departmentT);
				$department = implode(",",$departmentTArray);
			}
			if($trainingC != '')
			{
				$trainingCArray  = array_filter($trainingC);
				$training = implode(",",$trainingCArray);
			}
			
			/*  echo $tName;
			echo "<br />";
			echo $department;
			echo "<br />";
			echo $training;exit; */
			$request->session()->put('cname_training_filter_inner_list',$tName);
			$request->session()->put('department_training_filter_inner_list',$department);
			$request->session()->put('trainingC_training_filter_inner_list',$training);
			
			 return redirect('listingEmpTraining');
		}
		
		public function trainingbyfilterReset(Request $request)
		{
			$request->session()->put('cname_training_filter_inner_list','');
			$request->session()->put('department_training_filter_inner_list','');
			$request->session()->put('trainingC_training_filter_inner_list','');
			
			 return redirect('listingEmpTraining');
		}
}
