<?php 
namespace App\Http\Controllers\CompareCategories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompareCategories\CompareCategories;
use App\Models\Firebase\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use App\Models\Company\category;
use Session;
class CompareController extends Controller{
	
	 public function index()
    {
		 
		 $categoriesDetails = CompareCategories::where("status",1)->orWhere("status",2)->orderBy('id','DESC')->get();
       //$categoriesDetails =  category::orderBy('id','DESC')->get();
       return view("CompareCategories/categorieslist",compact('categoriesDetails'));
    }
	public function delete(Request $request)
    {
		 $id=$request->id;
		 $updatecategories = CompareCategories::where("id", $id)
		->update([
        'status' => 2,
        'updated_at' => now() // Update timestamp
    ]);

       //$categoriesDetails =  category::orderBy('id','DESC')->get();
       return redirect("manage-compare-categories");
    }
	public function add(Request $request)
    {
		 $name=$request->name;
		 $updatecategories = CompareCategories::insert([
    'name' => $name,
    'Status' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

       //$categoriesDetails =  category::orderBy('id','DESC')->get();
       return redirect("manage-compare-categories");
    }
	public function edit(Request $request)
    {
		 $id=$request->id;
		 $getcategories = CompareCategories::where('id',$id)->get();
    foreach($getcategories as $cate){
       echo ' <form action="javascript:void(0)">
  <div class="form-group">
    <label for="name">Category Name</label>
    <input type="text" class="form-control" id="name" value="'.$cate->name.'">
    <input type="hidden" class="form-control" id="id" value="'.$cate->id.'">
  </div>
 
 
  <button type="button" onclick="updatecate();" class="btn btn-default">Update Category</button> 
</form>'; 
	}
    }
	public function update(Request $request)
    {
		 $id=$request->id;
		 $name=$request->name;
		 
		 $getcategories = CompareCategories::where('id', $id) 
    ->update([
        'name' => $name,
        'Status' => 1, 
        'updated_at' => now()
    ]);
    }
}