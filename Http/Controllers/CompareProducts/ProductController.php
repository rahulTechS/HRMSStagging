<?php 
namespace App\Http\Controllers\CompareProducts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompareCategories\CompareCategories;
use App\Models\Firebase\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use App\Models\Company\category;
use Session;
class ProductController extends Controller{
	
	 public function index()
    {
		 
		 $categoriesDetails = CompareCategories::where("status",1)->orderBy('id','DESC')->get();
       //$categoriesDetails =  category::orderBy('id','DESC')->get();
       return view("CompareProducts/CompareProductList",compact('categoriesDetails'));
    }
	
}