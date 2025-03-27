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
use App\Models\Employee\Employee_attribute;
use App\Models\TrainingQuestion\TrainingQuestion;
use App\Models\TrainingQuestion\TrainingAnswer;
use App\Models\TrainingQuestion\TrainingRating;

class TrainingEMPDetailsController extends Controller
{

	public function TrainingEMPDetails(Request $request)
		{
			$tid=$request->tid;
			 
				
				
					$reportsdata = EmpTraining::where("id",$tid)->first();
					$reports= explode(",",$reportsdata->emp_present_ids);
				
				
				
				return view("TrainingCategory/TrainingEMPDeatils/TrainingEMPDetails",compact('reports','tid','reportsdata'));
		}
	public function TrainingEMPDetailsQuestions(Request $request)
		{
		 $uId=$request->uId;
		 $tid=$request->tid;
		 $tcatId=$request->tcatId;
		 $answerdata=TrainingAnswer::where("u_id",$uId)->where("t_id",$tid)->orderBy('id','DESC')->first();
		 //print_r($answerdata);exit;
		 $attributeDetail =TrainingQuestion::where('training_type_cat',$tcatId)->get();
		return view("TrainingCategory/TrainingEMPDeatils/TrainingQuestionForm",compact('uId','tid','tcatId','attributeDetail','answerdata'));  
	   
		}
		
		
		public function TrainingEMPDetailsQuestionsPost(Request $rq)
		{
			
		   $uId = $rq->input('userId');
		   $tId = $rq->input('trainingId');
		   $tcatId = $rq->input('trainingcatId');
		
			$question_id=$rq->input('question_id');
			
			foreach($question_id as $_question){
				$ans=$rq->input($_question);
				
				if($ans!=''){					
					$questinObj = new TrainingAnswer();
					$questinObj->question_answer = $ans;
					$questinObj->question_id = $_question;
					$questinObj->u_id = $uId;
					$questinObj->t_id = $tId;
					$questinObj->tcat_id = $tcatId;
					$questinObj->status = 1;
					$questinObj->link_expired = 1;
					$questinObj->save();
					
				}
				
			}
			
			
				
			session()->flash('message', 'Data Save Successfully '); 
			return redirect("/TrainingEMPDetailsQuestions/$uId/$tId/$tcatId");
			

		}
		public function getTrainingresponsedata(Request $req)
		   {
			  
			$uid =  $req->uid;
			$tid =  $req->tid;
			$answerdata=TrainingAnswer::where("u_id",$uid)->where("t_id",$tid)->get();
			
			return view("TrainingCategory/TrainingEMPDeatils/TrainingAnswer",compact('answerdata'));
		   }
		   
		
		 public static function getQuestionTitle($qid)
	   {
		   $trainingModel =  TrainingQuestion::where("id",$qid)->first();
		   if($trainingModel != '')
		   {
			   return $trainingModel->question;
		   }
		   else
		   {
			   return "-";
		   }
	   }
		public function getTrainingresponseratingdata(Request $req)
		   {
			  
			$uid =  $req->uid;
			$tid =  $req->tid;
			//$answerdata=TrainingAnswer::where("u_id",$uid)->where("t_id",$tid)->get();
			
			return view("TrainingCategory/TrainingEMPDeatils/TrainingUserRating",compact('uid','tid'));
		   }
	public function getTrainingresponseratingdataPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new TrainingRating();
			$jobOpeningMod->user_id = $parameterInput['user_id'];			
			$jobOpeningMod->t_id = $parameterInput['tid'];
			$jobOpeningMod->rating = $parameterInput['rating'];
			$jobOpeningMod->comment = $parameterInput['comment'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Training Category Saved.');
			return redirect('TrainingEMPDetails/'.$parameterInput['tid']);
	   }		   
		
		public static function getratingdata($uid,$tid){
			
		$trainingModel =  TrainingRating::where("user_id",$uid)->where("t_id",$tid)->first();
		   if($trainingModel != '')
		   {
			   return $trainingModel->rating;
		   }
		   else
		   {
			   return "";
		   }	
		}
		public static function getEmpNameListId($id){
			$nameEmp = Employee_details::where("id",$id)->first();
			if($nameEmp!=''){
				return $nameEmp->emp_id;
			}
			else{
				return "";
			}
		}
		public static function getAttributeListValue($id, $email){
			$nameEmp = Employee_details::where("id",$id)->first();
			
			$existempattribute = Employee_attribute::where("emp_id",$nameEmp->emp_id)->where("attribute_code",'email')->first();
			if($existempattribute!=''){
			return $existempattribute->attribute_value;	
			}
			else{
				return "";
			}
			
		}
		public static function getmastercomment($id){
			$nameEmp = EmpTraining::where("id",$id)->first();
			//print_r($nameEmp);
			if($nameEmp!=''){
				return $nameEmp->master_comment;
			}
			else{
				return "";
			}
		}
		
		public function getTrainingMasterCommentPost(Request $request){
			$parameterMeters = $request->input();
		  
		    $id = $parameterMeters['tid'];
		    $TrainingCategoryUpdateMod = EmpTraining::find($id);
		    $TrainingCategoryUpdateMod->master_comment = $parameterMeters['comment'];
		   
			$TrainingCategoryUpdateMod->save();
			
			return redirect('TrainingEMPDetails/'.$id);
		}
		
		
		
		
		
		
    public function editEmpTraining($trainingId)
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingCategory::where("status",1)->get();
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
		   $trainingModel =  TrainingCategory::where("id",$tId)->first();
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
