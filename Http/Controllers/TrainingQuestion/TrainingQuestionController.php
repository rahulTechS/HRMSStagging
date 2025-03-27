<?php

namespace App\Http\Controllers\TrainingQuestion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Attribute\Attributes;
use  App\Models\Attribute\AttributeType;
use App\Models\TrainingQuestion\TrainingQuestion;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\TrainingCategory\TrainingCategory;
use App\Models\Onboarding\TrainingType;
class TrainingQuestionController extends Controller
{
    
        public function TrainingQuestionList()
        {
			//echo "wait...";exit;
			
            return view("TrainingQuestion/TrainingQuestionList");
        }
	
		
		public function AddTrainingQuestion()
        {
            $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$trainingtitle=EmpTraining::get();
            $trainingCategoryArray = TrainingType::where("status",1)->get();
            return view("TrainingQuestion/AddTrainingQuestionForm",compact('attributeTypeDetails','trainingtitle','trainingCategoryArray'));
        }

        public function addTrainingQuestionPost(Request $attrReq)
        {
            $attrObj = new TrainingQuestion();
            $attrObj->question = $attrReq->input('question_name');
            $attrObj->question_code = $attrReq->input('question_code');
			//$attrObj->training_name = $attrReq->input('attrbute_training_name');
			$attrObj->training_type_cat = $attrReq->input('training_type_cat');
               $attrOpts =  $attrReq->input('opt');
               $attrObj->answer = json_encode($attrOpts);
           $attrObj->category = $attrReq->input('category');
		   $attrObj->attrbute_type = $attrReq->input('attrbute_type');
		   
            $attrObj->status = $attrReq->input('status');
            
            $attrObj->save();
			$response['code'] = '200';
			$response['message'] = "Attribute Saved Successfully.";
			echo json_encode($response);
			   exit;
            //$attrReq->session()->flash('message','Attribute Saved Successfully.');
            //return redirect('empAttributeList');
            
        }
		public function setOffSetInnerEMPTrainingAttribute(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_emp_attribute_filter',$offset);
			}

        public function TrainingQuestionsList(Request $request)
        {
			if(!empty($request->session()->get('offset_emp_attribute_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_attribute_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				$selectedFilter['Attribute_name'] = '';
				$selectedFilter['Attribute_code'] = '';
				$selectedFilter['Attribute_tab'] = '';
				$selectedFilter['Attribute_type'] = '';
				$selectedFilter['Attribute_dept'] = '';
				//$request->session()->put('name_emp_attribute_filter_inner_list','');
				if(!empty($request->session()->get('question_name_emp_attribute_filter_inner_list')) && $request->session()->get('question_name_emp_attribute_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('question_name_emp_attribute_filter_inner_list');
					 $selectedFilter['question_name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'question_name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And question_name = "'.$name.'"';
					}
				}
				if(!empty($request->session()->get('question_code_emp_attribute_filter_inner_list')) && $request->session()->get('question_code_emp_attribute_filter_inner_list') != 'All')
				{
					$code = $request->session()->get('question_code_emp_attribute_filter_inner_list');
					 $selectedFilter['question_code'] = $code;
					 if($whereraw == '')
					{
						$whereraw = 'question_code = "'.$code.'"';
					}
					else
					{
						$whereraw .= ' And question_code = "'.$code.'"';
					}
				}
				
				
				
				
				$attributeNameArray = array();
				if($whereraw == '')
				{
				$name = Attributes::where("status",array(1,2))->get();
				}
				else
				{					
				$name = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get();					
				}				
				foreach($name as $_name)
				{
					$attributeNameArray[$_name->attribute_name] = $_name->attribute_name;
				}
				$attributeCodeArray = array();
				if($whereraw == '')
				{
				$code = Attributes::where("status",array(1,2))->get();
				}
				else
				{					
				$code = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get();					
				}				
				foreach($code as $_code)
				{
					$attributeCodeArray[$_code->attribute_code] = $_code->attribute_code;
				}
				$attributeTabArray = array();
				if($whereraw == '')
				{
				$tab = Attributes::where("status",array(1,2))->get();
				}
				else
				{					
				$tab = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get();					
				}				
				foreach($tab as $_tab)
				{
					if(!empty($_tab->tab_name)){
					$attributeTabArray[$_tab->tab_name] = $_tab->tab_name;
					}
				}
				$attributeTypeArray = array();
				if($whereraw == '')
				{
				$type = Attributes::where("status",array(1,2))->get();
				}
				else
				{					
				$type = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get();					
				}				
				foreach($type as $_type)
				{
					$attributeTypeArray[$_type->attrbute_type_id] = $_type->attrbute_type_id;
				}
				$attributeDptNameArray = array();
				if($whereraw == '')
				{
				$dept = Attributes::where("status",array(1,2))->get();
				}
				else
				{					
				$dept = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get();					
				}				
				foreach($dept as $_dept)
				{
					$attributeDptNameArray[$_dept->department_id] = $_dept->department_id;
				}
				
				if($whereraw != '')
				{
				
				$attributeTypeDetails = TrainingQuestion::whereRaw($whereraw)->where("status",1)->orderBy('id','DESC')->paginate($paginationValue);
				$reportsCount = TrainingQuestion::whereRaw($whereraw)->where("status",1)->get()->count();
				}
				else{
				$attributeTypeDetails = TrainingQuestion::where("status",1)->orderBy('id','DESC')->paginate($paginationValue);
				$reportsCount = TrainingQuestion::where("status",1)->get()->count();
				}
				
            return view("TrainingQuestion/TrainingQuestionsList",compact('attributeTypeDetails','reportsCount','paginationValue','selectedFilter','attributeNameArray','attributeCodeArray','attributeTabArray','attributeTypeArray','attributeDptNameArray'));
        }
		

        public function editTrainingQuestionData($QuestionId = NULL)
        {
                

                $attributeDetail = TrainingQuestion::where('id',$QuestionId)->first();
				$result['optarr'] = json_decode($attributeDetail->answer);
				$trainingCategoryArray = TrainingType::where("status",1)->get();
                $trainingtitle=EmpTraining::get();
                return view("TrainingQuestion/UpdateTrainingQuestionForm",compact('result','attributeDetail','trainingtitle','trainingCategoryArray'));
        }
		public function deleteTrainingQuestionData(Request $attrReq)
		{
			
			 $attrObj =  TrainingQuestion::find($attrReq->QuestionId);
			 $attrObj->status = 3;
			 $attrObj->save();
             $attrReq->session()->flash('message','Question Deleted Successfully.');
             //return redirect('empAttributeList');
			 $response['code'] = '200';
			$response['message'] = "Question Deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
		}
        public function updateTrainingQuestionDataPost(Request $attrReq)
        {
            $attrObj =  TrainingQuestion::find($attrReq->id);
			
			$attrObj->question = $attrReq->input('question_name');
            $attrObj->question_code = $attrReq->input('question_code');
           $attrObj->category = $attrReq->input('category');
		  // $attrObj->training_name = $attrReq->input('attrbute_training_name');
		   $attrObj->training_type_cat = $attrReq->input('training_type_cat');
		   $attrObj->attrbute_type = $attrReq->input('attrbute_type');
            $attrObj->status = $attrReq->input('status');
			
            
               $attrOpts =  $attrReq->input('opt');
               $attrObj->answer = json_encode($attrOpts);
            
            
            $attrObj->save();
            //$attrReq->session()->flash('message','Attribute Updated Successfully.');
            //return redirect('empAttributeList');
			$response['code'] = '200';
			$response['message'] = "Question Updated Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
        }
		
		public function parentopts($attribute_id = NULL)
		{
			$attrDatas =  Attributes::find($attribute_id);
			$optValues = $attrDatas->opt_option;
			$optValues = json_decode($optValues);
			$optArr = array();
			foreach($optValues as $_opt)
			{
				$optArr[$_opt] = $_opt;
			}
			
			return view("Attribute/parentopts",compact('optArr'));
		}
		
		public function parentoptsselected($parent_attribute_id = NULL,$attribute_id = NULL)
		{
			$attrDatas =  Attributes::find($parent_attribute_id);
			
			$optValues = $attrDatas->opt_option;
			$optValues = json_decode($optValues);
			$optArr = array();
			foreach($optValues as $_opt)
			{
				$optArr[$_opt] = $_opt;
			}
			$result = array();
			$result['allOptions'] = $optArr;
			$selectedAttr = Attributes::find($attribute_id);
			$parentArrOpts =  json_decode($selectedAttr->parent_attr_opt);
			$optArrSelected = array();
			foreach($parentArrOpts as $_optS)
			{
				$optArrSelected[] = $_optS;
			}
			$result['optArrSelected'] = $optArrSelected;
			return view("Attribute/parentoptsselected",compact('result'));
		}
		public static function getAttributeTypeName($typeId)
			{
				//$name = Employee_details::where("id",$id)->first();
				$name =AttributeType::where("attribute_type_id",$typeId)->where("status",1)->first();
				if($name != '')
				{
					return $name->attribute_type_name;
				}
				else
				{
					return '--';
				}
				 
			}
		public static function getDeptName($dept)
			{
				
				$dMod = Department::where('id',$dept)->first();
				if($dMod !=''){
				return $dMod->department_name;
				}else{
					return '--';
				}
		}
			
		public function filterByAttributeName(Request $request)
			{
				$name = $request->name;
				$request->session()->put('question_name_emp_attribute_filter_inner_list',$name);	
			}
		public function filterByAttributeCode(Request $request)
			{
				$code = $request->code;
				$request->session()->put('question_code_emp_attribute_filter_inner_list',$code);	
			}	
		public function filterByAttributeTab(Request $request)
			{
				$tab = $request->tab;
				$request->session()->put('question_tab_emp_attribute_filter_inner_list',$tab);	
			}
		public function filterByAttributeType(Request $request)
			{
				$type = $request->type;
				$request->session()->put('question_type_emp_attribute_filter_inner_list',$type);	
			}
		public function filterByAttributeDptName(Request $request)
			{
				$dept = $request->dept;
				$request->session()->put('question_dept_emp_attribute_filter_inner_list',$dept);	
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
}
