<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Performance\Performance;
use App\Models\Employee\Employee_details;
use App\Models\Company\Department;
use App\Models\Company\category;
use App\Models\Company\Product;

class PerformanceController extends Controller
{
   
    public function performanceSetting()
    {
		$performanceLists = array();
		$p_obj = new Performance();
      $performanceLists = $p_obj->where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
        return view("Performance/performanceSetting",compact('performanceLists'));
    }

   
 public function addPerformance()
    {
		 /* $employeeLists = array();
		 $empObj = new Employee_details();
		 $employeeLists = $empObj->where("status",1)->orderBy("id","DESC")->get(); */
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
        return view("Performance/addPerformance",compact('departmentDetails'));
    }
	
	public function showPerformanceContent($deptId=NULL)
	{
		$employeeLists = array();
		 $empObj = new Employee_details();
		 $employeeLists = $empObj->where("status",1)->where("dept_id",$deptId)->orderBy("id","DESC")->get();
		 
		  $categoryObj = new category();
		 $categoriesDetails = $categoryObj->where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
		 
		return view("Performance/showPerformanceContent",compact('employeeLists','categoriesDetails'));
	}
	
	public function showPerformanceContentperCategory($catId=NULL)
	{

		$productDetails =  Product::where("status",1)->where("category_id",$catId)->orderBy('id','DESC')->get();
		 $months = array();
		 $months['jan'] = 'January';
		 $months['feb'] = 'February';
		 $months['mar'] = 'March';
		 $months['apr'] = 'April';
		 $months['may'] = 'May';
		 $months['jun'] = 'June';
		 $months['jul'] = 'July';
		 $months['aug'] = 'August';
		 $months['sep'] = 'September';
		 $months['oct'] = 'October';
		 $months['nov'] = 'November';
		 $months['dec'] = 'December';
		return view("Performance/showPerformanceContentperCategory",compact('productDetails','months'));
	}
	
	public function  addperformancePost(Request $req)
	{
		
		
			$p_obj = new Performance();
            $p_obj->emp_id = $req->input('emp_id');
            $p_obj->department_id = $req->input('department_id');
            $p_obj->category_id = $req->input('cat_id');
            $p_obj->product_id = $req->input('p_id');
            $p_obj->month = $req->input('month');
            $p_obj->year = $req->input('year');
            $p_obj->perf_value = $req->input('perf_value');
            $p_obj->status = $req->input('status');
            
            
            $p_obj->save();
            $req->session()->flash('message','Performance Saved Successfully.');
            return redirect('performanceSetting');
	}
	
	
	public function deletePerformance(Request $req)
	{
		$Performance_obj = Performance::find($req->id);
       
        $Performance_obj->status =3;
        $Performance_obj->save();
        $req->session()->flash('message','Performance Deleted Successfully.');
        return redirect('performanceSetting');
	}
	
	public function editperformance(Request $req)
	{
		$performance_data = Performance::where('id',$req->id)->first();
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		$result = array();
		$result['performance_data'] = $performance_data;
		$result['departmentDetails'] = $departmentDetails;
		
		return view("Performance/editperformance",compact('result'));
	}


	public function showPerformanceContentEdit($deptId=NULL)
	{
		$employeeLists = array();
		 $empObj = new Employee_details();
		 $employeeLists = $empObj->where("status",1)->where("dept_id",$deptId)->orderBy("id","DESC")->get();
		 
		  $categoryObj = new category();
		 $categoriesDetails = $categoryObj->where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
		 
		return view("Performance/showPerformanceContentEdit",compact('employeeLists','categoriesDetails'));
	}
	
	
	public function showPerformanceContentEditAuto($pId=NULL)
	{
		$performance_data = Performance::where('id',$pId)->first();
		
		
		$employeeLists = array();
		 $empObj = new Employee_details();
		 $employeeLists = $empObj->where("status",1)->where("dept_id",$performance_data->department_id)->orderBy("id","DESC")->get();
		 
		  $categoryObj = new category();
		 $categoriesDetails = $categoryObj->where("status",1)->where("department_id",$performance_data->department_id)->orderBy('id','DESC')->get();
		 $result = array();
		 $result['employeeLists'] = $employeeLists;
		 $result['categoriesDetails'] = $categoriesDetails;
		 $result['performance_data'] = $performance_data;
		return view("Performance/showPerformanceContentEditAuto",compact('result'));
	}
	
	
	
	public function showPerformanceContentperCategoryEdit($catId=NULL)
	{

		$productDetails =  Product::where("status",1)->where("category_id",$catId)->orderBy('id','DESC')->get();
		 $months = array();
		 $months['jan'] = 'January';
		 $months['feb'] = 'February';
		 $months['mar'] = 'March';
		 $months['apr'] = 'April';
		 $months['may'] = 'May';
		 $months['jun'] = 'June';
		 $months['jul'] = 'July';
		 $months['aug'] = 'August';
		 $months['sep'] = 'September';
		 $months['oct'] = 'October';
		 $months['nov'] = 'November';
		 $months['dec'] = 'December';
		return view("Performance/showPerformanceContentperCategoryEdit",compact('productDetails','months'));
	}
	
	
	
	public function showPerformanceContentperCategoryEditAuto($pId=NULL)
	{
		$performance_data = Performance::where('id',$pId)->first();
		
		$productDetails =  Product::where("status",1)->where("category_id",$performance_data->category_id)->orderBy('id','DESC')->get();
		 $months = array();
		 $months['jan'] = 'January';
		 $months['feb'] = 'February';
		 $months['mar'] = 'March';
		 $months['apr'] = 'April';
		 $months['may'] = 'May';
		 $months['jun'] = 'June';
		 $months['jul'] = 'July';
		 $months['aug'] = 'August';
		 $months['sep'] = 'September';
		 $months['oct'] = 'October';
		 $months['nov'] = 'November';
		 $months['dec'] = 'December';
		 $result = array();
		 $result['performance_data'] = $performance_data;
		 $result['productDetails'] = $productDetails;
		 $result['months'] = $months;
		return view("Performance/showPerformanceContentperCategoryEditAuto",compact('result'));
	}
	public function  editperformancePost(Request $req)
	{
		
			$p_obj =  Performance::find($req->input('mainId'));
			
            $p_obj->emp_id = $req->input('emp_id');
            $p_obj->department_id = $req->input('department_id');
            $p_obj->category_id = $req->input('cat_id');
            $p_obj->product_id = $req->input('p_id');
            $p_obj->month = $req->input('month');
            $p_obj->year = $req->input('year');
            $p_obj->perf_value = $req->input('perf_value');
            $p_obj->status = $req->input('status');
            
            
            $p_obj->save();
            $req->session()->flash('message','Performance Updated Successfully.');
            return redirect('performanceSetting');
	}
}
