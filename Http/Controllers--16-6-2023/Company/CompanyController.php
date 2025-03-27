<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company\ParentCompany;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use App\Models\Company\category;
use App\Models\Company\Product;
use Session;

class CompanyController extends Controller
{
   
    public function addParentCompany()
    {
        return view("Company/addParentCompany");
    }

    public function addParentCPost(Request $req)
    {
       
       $pCompanydata = new ParentCompany;
       $pCompanydata->parent_companyname = $req->input('parent_companyname');
       $pCompanydata->Status = $req->input('status');
       $pCompanydata->save();
        $req->session()->flash('message','Parent Company Saved Successfully.');
        return redirect('ParentCompanyList');
    }

    public function editPCompany($id=NULL)
    {
       

        $ParentCompanyData =   ParentCompany::where("id",$id)->first();
       
        return view("Company/editPCompany",compact('ParentCompanyData'));
    }

    public function editParentCategoryPost(Request $req)
    {
        
        $parentCompany_obj = ParentCompany::find($req->input('id'));
        $parentCompany_obj->parent_companyname = $req->input('parent_companyname');
        $parentCompany_obj->Status = $req->input('status');
        $parentCompany_obj->save();
        $req->session()->flash('message','Parent Company updated Successfully.');
        return redirect('ParentCompanyList');
    }
	public function deletePCompany(Request $req)
    {
        
        $parentCompany_obj = ParentCompany::find($req->id);
       
        $parentCompany_obj->Status =3;
        $parentCompany_obj->save();
        $req->session()->flash('message','Parent Company Deleted Successfully.');
        return redirect('ParentCompanyList');
    }
    public function pCompanyList()
    {
        
		$pCom_obj = new ParentCompany();
      $parentCompanyDetails = $pCom_obj->where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
        return view('Company/listParentCompany',compact('parentCompanyDetails'));
    }

    public function SubsidiaryAddition()
    {

        $parentCompanyDetails = ParentCompany::where("status",1)->orderBy("id","DESC")->get();  
       
        return view('Company/subsidiaryAddition',compact('parentCompanyDetails'));  
    }

    public function addSubsidiaryPost(Request $req)
    {
            $subsidiary_obj = new Subsidiary();
            $subsidiary_obj->parent_company_id = $req->input('p_company');
            $subsidiary_obj->s_name = $req->input('s_name');
            $subsidiary_obj->s_status = $req->input('s_status');
            $subsidiary_obj->save();
            $req->session()->flash('message','Subsidiary Saved Successfully.');
            return redirect('SubsidiaryList');

    }

    public function SubsidiaryList()
    {
        
    
		$subsidiary_obj = new Subsidiary();
		 $subsidiaryDatas = $subsidiary_obj->where("s_status",1)->orWhere("s_status",2)->orderBy("id","DESC")->get();  
        return view("Company/subsidiaryList",compact('subsidiaryDatas'));
    }

    public function editSubsidiary($id=NULL)
    {
      $SubsidiaryData =   Subsidiary::where("id",$id)->first();
      $parentCompanyDetails = ParentCompany::where("status",1)->orderBy("id","DESC")->get();
      return view("Company/editSubsidiary",compact('SubsidiaryData'),compact('parentCompanyDetails'));
    }
	
	
	public function deleteSubsidiary(Request $req)
    {
		$subsidiary_obj = Subsidiary::find($req->id);
       
        $subsidiary_obj->s_status = 3;
       
        $subsidiary_obj->save();
        $req->session()->flash('message','Subsidiary deleted Successfully.');
        return redirect('SubsidiaryList');
    }

    public function editSubsidiaryPost(Request $req)
    {
        $subsidiary_obj = Subsidiary::find($req->input('id'));
        $subsidiary_obj->parent_company_id = $req->input('p_company');
        $subsidiary_obj->s_name = $req->input('s_name');
        $subsidiary_obj->s_status = $req->input('s_status');
       
        $subsidiary_obj->save();
        $req->session()->flash('message','Subsidiary updated Successfully.');
        return redirect('SubsidiaryList');
    }

    public function adddivison()
    {
        $subsidiaryDetails = Subsidiary::where("s_status",1)->orderBy("id","DESC")->get();  
        return view('Company/adddivison',compact('subsidiaryDetails'));
    }

    public function addDivisonPost(Request $req)
    {
            $divison_obj = new Divison();
            $divison_obj->subsidiary_id = $req->input('subsidiary_id');
            $divison_obj->divison_name = $req->input('divison_name');
            $divison_obj->status = $req->input('status');
            $divison_obj->save();
            $req->session()->flash('message','Divison added Successfully.');
            return redirect('divisonList');
    }

    public function divisonList()
    {
        $divisonDetails = Divison::where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
        return view("Company/divisonList",compact('divisonDetails'));
    }

    public function editDivison($id=NULL)
    {
        $divisonData =   Divison::where("id",$id)->first();
        $subsidiaryDetails = Subsidiary::where("s_status",1)->orderBy("id","DESC")->get();
        return view("Company/editDivison",compact('divisonData'),compact('subsidiaryDetails'));
    }
	public function deleteDivison(Request $req)
    {
        $divisonObj = Divison::find($req->id);
        $divisonObj->status = 3;
        $divisonObj->save();
        $req->session()->flash('message','Divison Deleted Successfully.');
        return redirect('divisonList');
    }
    public function editDivisonPost(Request $req)
    {
        $divisonObj = Divison::find($req->id);
        $divisonObj->subsidiary_id = $req->subsidiary_id;
        $divisonObj->divison_name = $req->divison_name;
        $divisonObj->status = $req->status;
        $divisonObj->save();
        $req->session()->flash('message','Divison Updated Successfully.');
        return redirect('divisonList');
    }

    public function addDepartment()
    {

        $divisonDetails = Divison::where("status",1)->orderBy("id","DESC")->get();
        return view("Company/addDepartment",compact('divisonDetails'));
    }

    public function addDepartmentPost(Request $req)
    {
            
            $departmentObj = new Department();
            $departmentObj->divison_id = $req->divison_id;
            $departmentObj->department_name = $req->department_name;
            $departmentObj->status = $req->status;
            $departmentObj->save();
            $req->session()->flash('message','Department Added Successfully.');
            return redirect('departmentList');

    }

    public function departmentList(Request $request)
    {
		$filterValue = array(); 
		if(!empty($request->session()->get('divisonID')))
		{
			$divisonID = $request->session()->get('divisonID');
			$departmentDetails =  Department::where("divison_id",$divisonID)->where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
			$filterValue['divisonID'] = $divisonID;
		}
		else
		{
			$departmentDetails =  Department::where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
			$filterValue['divisonID'] = '';
		}
		if(!empty($request->session()->get('subsidiaryID')))
		{
			$filterValue['subsidiaryID'] = $request->session()->get('subsidiaryID');
			$subsidiary_id = $request->session()->get('subsidiaryID');
			$divisonDetails = Divison::where('subsidiary_id',$subsidiary_id)->where("status",1)->orderBy("id","DESC")->get();
		}
		else
		{
			$filterValue['subsidiaryID'] = '';
			$divisonDetails = array();
		}
		
	   /*
	   *get Subsidiary values
	   *start coding
	   */
	   $subsidiaryDetails = Subsidiary::where("s_status",1)->orderBy("id","DESC")->get();
	   
	   /*
	   *get Subsidiary values
	   *start coding
	   */
       return view("Company/departmentlist",compact('departmentDetails','subsidiaryDetails','divisonDetails','filterValue'));
    }

    public function editDepartment($id = NULL)
    {
        
       $departmentObj =  Department::where("id",$id)->first();
       $divisonDetails = Divison::where("status",1)->orderBy("id","DESC")->get();
       return view("Company/editdepartment",compact('departmentObj'),compact('divisonDetails'));

    }

    public function editDepartmentPost(Request $req)
    {
        $departmentObj = Department::find($req->id);
        $departmentObj->divison_id = $req->divison_id;
        $departmentObj->department_name = $req->department_name;
        $departmentObj->status = $req->status;
        $departmentObj->save();
        $req->session()->flash('message','Department Updated Successfully.');
        return redirect('departmentList');
    }
	
	public function deleteDepartment(Request $req)
    {
        $departmentObj = Department::find($req->id);
       
        $departmentObj->status = 3;
        $departmentObj->save();
        $req->session()->flash('message','Department Deleted Successfully.');
        return redirect('departmentList');
    }

    public function categories()
    {
		 $categoryObj = new category();
		 $categoriesDetails = $categoryObj->where("status",1)->orWhere("status",2)->orderBy('id','DESC')->get();
       //$categoriesDetails =  category::orderBy('id','DESC')->get();
       return view("Company/categorieslist",compact('categoriesDetails'));
    }
    public function AddCategory()
    {
        $departmentDetails = Department::where("status",1)->orderBy("id","DESC")->get();
        return view("Company/addcategory",compact('departmentDetails'));
    }
    public function addCategoryPost(Request $req)
    {
            $categoryObj = new category();
            $categoryObj->department_id = $req->department_id;
            $categoryObj->category_name = $req->category_name;
            $categoryObj->status = $req->status;
            $categoryObj->save();
            $req->session()->flash('message','Category Added Successfully.');
            return redirect('categories');
    }
    public function editCategory($id = NULL)
    {

        $categoryDetail =  category::where("id",$id)->first();
        $departmentdetails = Department::where("status",1)->orderBy("id","DESC")->get();
        return view("Company/editcategory",compact('categoryDetail'),compact('departmentdetails'));
        
    }
	
	public function deleteCategory(Request $req)
	{
		$categoryObj = category::find($req->id);
        
        $categoryObj->status = 3;
        $categoryObj->save();
        $req->session()->flash('message','Category Deleted Successfully.');
        return redirect('categories');
	}

    public function editCategoryPost(Request $req)
    {
        $categoryObj = category::find($req->id);
        $categoryObj->department_id = $req->department_id;
        $categoryObj->category_name = $req->category_name;
        $categoryObj->status = $req->status;
        $categoryObj->save();
        $req->session()->flash('message','Category Updated Successfully.');
        return redirect('categories');
    }

    public function productList()
    {
        $productDetails =  Product::where("status",1)->orWhere("status",2)->orderBy('id','DESC')->get();
        return view("Company/productlist",compact('productDetails'));
    }

    public function addProduct()
    {
        $categories = category::where("status",1)->orderBy("id","DESC")->get();   
        return view("Company/addproduct",compact('categories'));

    }

    public function addProductPost(Request $req)
    {
        $productObj = new Product();
        $productObj->category_id = $req->category_id;
        $productObj->product_name = $req->product_name;
        $productObj->status = $req->status;
        $productObj->save();
        $req->session()->flash('message','Product Saved Successfully.');
        return redirect('productList');
    }

    public function editProduct($id=NULL)
    {
        $categories = category::where("status",1)->orderBy("id","DESC")->get();    
        $productDetails =  Product::find($id);
        return view("Company/editproduct",compact('categories'),compact('productDetails'));
    }

    public function editProductPost(Request $req)
    {
        $productObj =  Product::find($req->id);
        $productObj->category_id = $req->category_id;
        $productObj->product_name = $req->product_name;
        $productObj->status = $req->status;
        $productObj->save();
        $req->session()->flash('message','Product Updated Successfully.');
        return redirect('productList');
    }
	
	public function deleteProduct(Request $req)
    {
        $productObj =  Product::find($req->id);
       
        $productObj->status = 3;
        $productObj->save();
        $req->session()->flash('message','Product Deleted Successfully.');
        return redirect('productList');
    }
	
	public function appliedFilterOnDepartment(Request $request)
	{
		
			$selectedFilter = $request->input();
			
			if(!empty($selectedFilter['divisonID']))
			{
				$request->session()->put('divisonID',$selectedFilter['divisonID']);
			}
			if(!empty($selectedFilter['subsidiaryID']))
			{
				$request->session()->put('subsidiaryID',$selectedFilter['subsidiaryID']);
			}
			return redirect('departmentList');
	}
	
	public function getdivisonList(Request $request)
	{
		$subsidiaryId = $request->subsidiaryId;
		$divisonDetails = Divison::where("subsidiary_id",$subsidiaryId)->where("status",1)->orderBy("id","DESC")->get();
		return view("Company/getdivisonList",compact('divisonDetails'));
	}
	
	public function resetdepartmentFilter(Request $request)
	{
		$request->session()->put('divisonID','');
		$request->session()->put('subsidiaryID','');
		return redirect('departmentList');
	}

}
