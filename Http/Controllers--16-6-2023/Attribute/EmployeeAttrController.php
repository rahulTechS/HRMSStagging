<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Attribute\Attributes;
use  App\Models\Attribute\AttributeType;
use App\Models\Company\Department;
class EmployeeAttrController extends Controller
{
    
        public function empAttributeList()
        {
			
            return view("Attribute/EmpAttributeList");
        }
	
		
		public function empAttributeAdd()
        {
            $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();

            $departments = Department::where("status",1)->orderBy('id','DESC')->get();
            $conditionalAttributes = Attributes::where(["parent_attribute"=>0,"attrbute_type_id"=>3,"status"=>1])->get();
            $result = array();
            $result['departments'] = $departments;
            $result['conditionalAttributes'] = $conditionalAttributes;
            return view("Attribute/AttributeAddForm",compact('attributeTypeDetails','result'));
        }

        public function addEmployeeAttr(Request $attrReq)
        {
            $attrObj = new Attributes();
            $attrObj->attribute_name = $attrReq->input('attribute_name');
            $attrObj->attribute_code = $attrReq->input('attribute_code');
            $attrObj->tab_name = $attrReq->input('tab_name');
            $attrObj->attrbute_type_id = $attrReq->input('attrbute_type_id');
            $attrTypeId = $attrReq->input('attrbute_type_id');
            if($attrTypeId == 3)
            {
               
               $attrOpts =  $attrReq->input('opt');
               $attrObj->opt_option = json_encode($attrOpts);
            }
            $attrObj->attribute_requirement = $attrReq->input('attribute_requirement');
            $attrObj->department_id = $attrReq->input('department_id');
            $attrObj->attribute_set = 'Employee';
            $attrObj->status = $attrReq->input('status');
            $attrObj->sort_order = $attrReq->input('sort_order');
            $attrObj->onboarding_status = $attrReq->input('onboarding_status');
            $attrObj->conditional_attribute = $attrReq->input('conditional_attribute');
            $cA =  $attrReq->input('conditional_attribute');;
            if($cA == 1)
            {
                $attrObj->parent_attribute = $attrReq->input('parent_attribute');
				$attrObj->parent_attr_opt = json_encode($attrReq->input('parent_attr_opt'));
				
            }
            
            $attrObj->save();
			$response['code'] = '200';
			$response['message'] = "Attribute Saved Successfully.";
			echo json_encode($response);
			   exit;
            //$attrReq->session()->flash('message','Attribute Saved Successfully.');
            //return redirect('empAttributeList');
            
        }
		public function setOffSetInnerEMPAttribute(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_emp_attribute_filter',$offset);
			}

        public function attributesList(Request $request)
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
				if(!empty($request->session()->get('name_emp_attribute_filter_inner_list')) && $request->session()->get('name_emp_attribute_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('name_emp_attribute_filter_inner_list');
					 $selectedFilter['Attribute_name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'attribute_name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And attribute_name = "'.$name.'"';
					}
				}
				if(!empty($request->session()->get('code_emp_attribute_filter_inner_list')) && $request->session()->get('code_emp_attribute_filter_inner_list') != 'All')
				{
					$code = $request->session()->get('code_emp_attribute_filter_inner_list');
					 $selectedFilter['Attribute_code'] = $code;
					 if($whereraw == '')
					{
						$whereraw = 'attribute_code = "'.$code.'"';
					}
					else
					{
						$whereraw .= ' And attribute_code = "'.$code.'"';
					}
				}
				if(!empty($request->session()->get('tab_emp_attribute_filter_inner_list')) && $request->session()->get('tab_emp_attribute_filter_inner_list') != 'All')
				{
					$tab = $request->session()->get('tab_emp_attribute_filter_inner_list');
					 $selectedFilter['Attribute_tab'] = $tab;
					 if($whereraw == '')
					{
						$whereraw = 'tab_name = "'.$tab.'"';
					}
					else
					{
						$whereraw .= ' And tab_name = "'.$tab.'"';
					}
				}
				if(!empty($request->session()->get('type_emp_attribute_filter_inner_list')) && $request->session()->get('type_emp_attribute_filter_inner_list') != 'All')
				{
					$type = $request->session()->get('type_emp_attribute_filter_inner_list');
					 $selectedFilter['Attribute_type'] = $type;
					 if($whereraw == '')
					{
						$whereraw = 'attrbute_type_id = "'.$type.'"';
					}
					else
					{
						$whereraw .= ' And attrbute_type_id = "'.$type.'"';
					}
				}
				if(!empty($request->session()->get('dept_emp_attribute_filter_inner_list')) && $request->session()->get('dept_emp_attribute_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_emp_attribute_filter_inner_list');
					 $selectedFilter['Attribute_dept'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department_id = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department_id = "'.$dept.'"';
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
				
				$attributeTypeDetails = Attributes::whereRaw($whereraw)->where("status",array(1,2))->orderBy('attribute_id','DESC')->paginate($paginationValue);
				$reportsCount = Attributes::whereRaw($whereraw)->where("status",array(1,2))->get()->count();
				}
				else{
				$attributeTypeDetails = Attributes::where("status",array(1,2))->orderBy('attribute_id','DESC')->paginate($paginationValue);
				$reportsCount = Attributes::where("status",array(1,2))->get()->count();
				}
				
            return view("Attribute/AttributesList",compact('attributeTypeDetails','reportsCount','paginationValue','selectedFilter','attributeNameArray','attributeCodeArray','attributeTabArray','attributeTypeArray','attributeDptNameArray'));
        }
		

        public function editEmpAttribute($attributeId = NULL)
        {
                

                $attributeDetail = Attributes::where('attribute_id',$attributeId)->first();
                $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
                $departments = Department::where("status",1)->orderBy('id','DESC')->get();
				$conditionalAttributes = Attributes::where(["parent_attribute"=>0,"attrbute_type_id"=>3])->get();
                $result = array();
                
                $result['departments'] = $departments;
                $result['attributeTypeDetails'] = $attributeTypeDetails;
                $result['optarr'] = json_decode($attributeDetail->opt_option);
				$result['conditionalAttributes'] = $conditionalAttributes;
                return view("Attribute/UpdateAttributeForm",compact('result'),compact('attributeDetail'));
        }
		public function deleteEmpAttribute(Request $attrReq)
		{
			
			 $attrObj =  Attributes::find($attrReq->attribute_id);
			 $attrObj->status = 3;
			 $attrObj->save();
             $attrReq->session()->flash('message','Attribute Deleted Successfully.');
             //return redirect('empAttributeList');
			 $response['code'] = '200';
			$response['message'] = "Attribute Deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
		}
        public function updateEmployeeAttr(Request $attrReq)
        {
            $attrObj =  Attributes::find($attrReq->attribute_id);
            $attrObj->attribute_name = $attrReq->input('attribute_name');
            $attrObj->attribute_code = $attrReq->input('attribute_code');
			 $attrObj->tab_name = $attrReq->input('tab_name');
            $attrObj->attrbute_type_id = $attrReq->input('attrbute_type_id');
            $attrTypeId = $attrReq->input('attrbute_type_id');
            if($attrTypeId == 3)
            {
               $attrOpts =  $attrReq->input('opt');
               $attrObj->opt_option = json_encode($attrOpts);
            }
            else
            {
                $attrObj->opt_option = '';
            }
            $attrObj->attribute_requirement = $attrReq->input('attribute_requirement');
            $attrObj->department_id = $attrReq->input('department_id');
            $attrObj->attribute_set = 'Employee';
            $attrObj->status = $attrReq->input('status');
            $attrObj->sort_order = $attrReq->input('sort_order');
			 $attrObj->onboarding_status = $attrReq->input('onboarding_status');
			$attrObj->conditional_attribute = $attrReq->input('conditional_attribute');
            $cA =  $attrReq->input('conditional_attribute');;
            if($cA == 1)
            {
                $attrObj->parent_attribute = $attrReq->input('parent_attribute');
				$attrObj->parent_attr_opt = json_encode($attrReq->input('parent_attr_opt'));
				
            }
			else
			{
				
				$attrObj->parent_attribute = 0;
				$attrObj->parent_attr_opt = '';
			}
            
            $attrObj->save();
            //$attrReq->session()->flash('message','Attribute Updated Successfully.');
            //return redirect('empAttributeList');
			$response['code'] = '200';
			$response['message'] = "Attribute Updated Successfully.";
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
				$request->session()->put('name_emp_attribute_filter_inner_list',$name);	
			}
		public function filterByAttributeCode(Request $request)
			{
				$code = $request->code;
				$request->session()->put('code_emp_attribute_filter_inner_list',$code);	
			}	
		public function filterByAttributeTab(Request $request)
			{
				$tab = $request->tab;
				$request->session()->put('tab_emp_attribute_filter_inner_list',$tab);	
			}
		public function filterByAttributeType(Request $request)
			{
				$type = $request->type;
				$request->session()->put('type_emp_attribute_filter_inner_list',$type);	
			}
		public function filterByAttributeDptName(Request $request)
			{
				$dept = $request->dept;
				$request->session()->put('dept_emp_attribute_filter_inner_list',$dept);	
			}
}
