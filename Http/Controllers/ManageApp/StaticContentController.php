<?php
namespace App\Http\Controllers\ManageApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Recruiter\Designation;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\Employee_Attendance\EmpAttendance;
use App\Models\Employee_Attendance\Attendance;
use Illuminate\Support\Facades\Validator;
use App\Models\EmpOffline\EmpOffline;
use DateTime;
use Session;
use App\User;
use App\Models\Entry\Employee;
use App\Models\ManageApp\AppScreens;

class StaticContentController extends Controller
{
    public function Index(Request $request)
    {
        $empDetailsIndex = AppScreens::where('status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $designationLists=Designation::where("status",1)->get();




        $empsessionId=$request->session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			//return "Hello".$empDetails->dept_id;
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				//return "Hello".$empDetails->dept_id;47
				$design=Designation::where("tlsm",2)->where("department_id",$empDetails->dept_id)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);				
				$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$empDetails->dept_id)->where("offline_status",1)->get();
			}
		}
		else
		{
			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
		}
        //return view("ManageApp/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
        return view("ManageApp/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
    }


    public static function getLoggedinUser($loggedinUserid)
	{
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
				$employeeData=2;
            }
        }
        else
        {
			$employeeData=1;		
        }
		return $employeeData;
	}


    public function allScreensListing(Request $request)
    {
        $whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('screens_page_name')) && $request->session()->get('screens_page_name') != 'All')
        {
            $fname = $request->session()->get('screens_page_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'page_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And page_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('screens_page_title')) && $request->session()->get('screens_page_title') != 'All')
        {
            


            $fname = $request->session()->get('screens_page_title');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'title IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And title IN('.$finalcname.')';
                }
            }
        }



        if(!empty($request->session()->get('screens_sub_title')) && $request->session()->get('screens_sub_title') != 'All')
        {
            $fname = $request->session()->get('screens_sub_title');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'subtitle IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And subtitle IN('.$finalcname.')';
                }
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'subtitle  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And subtitle  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            
                
                
                
                $requestDetails = AppScreens::whereRaw($whereraw)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AppScreens::whereRaw($whereraw)->orderBy('id','desc')
                ->get()->count();
                
     
        }
        else
        {
            
            
                $requestDetails = AppScreens::orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);	
                
                $reportsCount = AppScreens::orderBy('id','desc')
                ->get()->count();
            
            
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("ManageApp/listingAll",compact('requestDetails','paginationValue','reportsCount'));
    }


    public static function addrequestPopData(Request $request)
    {
        $loggedinUserid=$request->session()->get('EmployeeId');
        

        return view("ManageApp/addRequest");
    }

    public function addPageRequestPostSubmit(Request $request)
    {
        
        $validator = Validator::make($request->all(), 
        [			
			'pageName' => 'required',
            'pageTitle' => 'required',
            'subTitle' => 'required', 
            
            'filearea_upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ],
		[
			'pageName.required'=> 'Please Enter Page Name',
		 	'pageTitle.required'=> 'Please Enter Page Title',
			'subTitle.required'=> 'Please Enter Sub Title',
           // 'image.required'=> 'Please Upload Image',
            
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            // print_r($_FILES);
            // return $request->all();
            // $image = $request->file('filearea_upload');
            // print_r($image);
            // exit;


            $keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($request->file($key)))
						{
						$filenameWithExt = $request->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$request->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
						if(file_exists(public_path('screenimages/'.$newFileName)))
						{

							  unlink(public_path('screenimages/'.$newFileName));

						}
						/*
						*Updating File Name
						*/
						$filesAttributeInfo[$vKey] = $newFileName;
						$listOfAttribute[] = $vKey;
						/*
						*Updating File Name
						*/
						// Get just Extension
						$extension = $request->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						$request->file($key)->move(public_path('screenimages/'), $newFileName);
						$fileIndex++;
						}
						else
						{
							$vKey = $keys[$fileIndex];
							$filesAttributeInfo[$vKey] = '';
							$listOfAttribute[] = $vKey;
							$fileIndex++;
						}
					}	





            $usersessionId=$request->session()->get('EmployeeId');


            
            
            

            $newPageRequest = new AppScreens();
            $newPageRequest->page_name = $request->pageName;
            $newPageRequest->title = $request->pageTitle;
            $newPageRequest->subtitle = $request->subTitle;
            $newPageRequest->created_by = $usersessionId; 
            $newPageRequest->bullet_points = $request->bulletPoints;
            $newPageRequest->imageurl = $newFileName;
            $newPageRequest->created_at = date('Y-m-d H:i:s');
            $newPageRequest->updated_at = date('Y-m-d H:i:s');
            $newPageRequest->status = 1;
            $newPageRequest->save();




            return response()->json(['success'=>'New Page Added Successfully.']);
        }
    }





    public function getAdvancedPayRequestContent(Request $request)
    {
        
        $rowid=$request->rowid;

        

        $requestData = AppScreens::where('id',$rowid)->orderBy('id', 'desc')->first();

        return view("ManageApp/EditPageRequestContent",compact('requestData'));
    }


    public function editPageRequestUpdatePost(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'editPageName' => 'required',
            'editPageTitle' => 'required',
            'editSubTitle' => 'required', 
            'editfilearea_upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ],
		[
			'editPageName.required'=> 'Please Enter Page Name',
            'editPageTitle.required'=> 'Please Enter Page Title.',
		 	'editSubTitle.required'=> 'Please Enter SubTitle',
			
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();



            $keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($request->file($key)))
						{
						$filenameWithExt = $request->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$request->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
						if(file_exists(public_path('screenimages/'.$newFileName)))
						{

							  unlink(public_path('screenimages/'.$newFileName));

						}
						/*
						*Updating File Name
						*/
						$filesAttributeInfo[$vKey] = $newFileName;
						$listOfAttribute[] = $vKey;
						/*
						*Updating File Name
						*/
						// Get just Extension
						$extension = $request->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						$request->file($key)->move(public_path('screenimages/'), $newFileName);
						$fileIndex++;
						}
						else
						{
							$vKey = $keys[$fileIndex];
							$filesAttributeInfo[$vKey] = '';
							$listOfAttribute[] = $vKey;
							$fileIndex++;
						}
					}	








            $usersessionId=$request->session()->get('EmployeeId');
            $pageRequest = AppScreens::where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            //$advancedPayRequest->emp_id = $request->addRequestEmp;
            $pageRequest->page_name = $request->editPageName;
            $pageRequest->title = $request->editPageTitle;
            $pageRequest->subtitle = $request->editSubTitle;
            $pageRequest->imageurl = $newFileName; 
            $pageRequest->bullet_points = $request->editBulletPoints;
            $pageRequest->updated_at = date('Y-m-d H:i:s');
            $pageRequest->updated_by = $usersessionId;
            $pageRequest->save();



            return response()->json(['success'=>'Page Content Updated Successfully.']);
        }
    }



    public function deletePages($id,Request $request)
    {
        //return $id;
        $flagRules = AppScreens::find($id)->delete();


        $empDetailsIndex = AppScreens::where('status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $designationLists=Designation::where("status",1)->get();




        $empsessionId=$request->session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			//return "Hello".$empDetails->dept_id;
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				//return "Hello".$empDetails->dept_id;47
				$design=Designation::where("tlsm",2)->where("department_id",$empDetails->dept_id)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);				
				$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$empDetails->dept_id)->where("offline_status",1)->get();
			}
		}
		else
		{
			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
		}

        



        return view("ManageApp/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
    }




    public function searchPagesFilterData(Request $request)
	{
			$subtitle='';
			if($request->input('subtitle')!=''){
			 
			 $subtitle=implode(",", $request->input('subtitle'));
			}

            $newDepartment='';
			if($request->input('newDepartment')!=''){
			 
			 $newDepartment=implode(",", $request->input('newDepartment'));
			}


			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
			$name='';
			if($request->input('page_name')!=''){
			 
			 $name=implode(",", $request->input('page_name'));
			}
			//$name = $request->input('emp_name');
			$pagetitle='';
			if($request->input('pagetitle')!=''){
			 
			 $pagetitle=implode(",", $request->input('pagetitle'));
			}
			$design='';
			if($request->input('designationdata')!=''){
			 
			 $design=implode(",", $request->input('designationdata'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			//02-9-2023
			$ReasonofAttrition='';
			if($request->input('ReasonofAttrition')!=''){
			 
			 $ReasonofAttrition=implode(",", $request->input('ReasonofAttrition'));
			}
			$offboardstatus='';
			if($request->input('offboardstatus')!=''){
			 
			 $offboardstatus=implode(",", $request->input('offboardstatus'));
			}
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}

			$rangeid='';
			if($request->input('rangeid')!=''){
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}

			$request->session()->put('screens_page_name',$name);
            $request->session()->put('screens_page_title',$pagetitle);
            $request->session()->put('screens_sub_title',$subtitle);
            $request->session()->put('transfer_requests_new_dept',$newDepartment);
            $request->session()->put('advancedpay_requests_designation',$design);
            $request->session()->put('advancedpay_requests_tl',$teamlaed);




            $request->session()->put('emp_leaves_fromdate',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


			$request->session()->put('range_filter_inner_list',$rangeid);
			//$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			//$request->session()->put('departmentId_filter_inner_list',$department);
			
			
			
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetPagesFilterData(Request $request)
    {
        $request->session()->put('screens_page_name','');
        $request->session()->put('screens_page_title','');
        $request->session()->put('screens_sub_title','');
        $request->session()->put('transfer_requests_new_dept','');
        $request->session()->put('advancedpay_requests_designation','');
        $request->session()->put('advancedpay_requests_tl','');



        


        $request->session()->put('emp_leaves_fromdate','');
		$request->session()->put('emp_leaves_todate','');
        
        
    }



  
}