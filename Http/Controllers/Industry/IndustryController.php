<?php

namespace App\Http\Controllers\Industry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Industry\CompanyListComplete;
use App\Models\Industry\IndustryKeywords;

class IndustryController extends Controller
{
    public function getIndustryKeywords()
	{
		$companyListwithIndustry = CompanyListComplete::whereNotNull("Industry")->get();
		/* echo '<pre>';
		print_r($companyListwithIndustry);
		exit; */
		foreach($companyListwithIndustry as $_industry)
		{
			$nameofcompany = $_industry->name_of_company;
			$nameofcompanyArray = explode(" ",$nameofcompany);
			
			/* echo '<pre>';
			print_r($nameofcompanyArray);
			exit; */
			if(count($nameofcompanyArray) >0)
			{
			$n = count($nameofcompanyArray);
			$lastElement = $n-1;
			/*
			*creating keywords for industry
			*/
			for($i=1;$i<$lastElement;$i++)
			{
			$induskeywordModel = new IndustryKeywords();
			$induskeywordModel->keywords_name = $nameofcompanyArray[$i];
			$induskeywordModel->industry = $_industry->Industry;
			$induskeywordModel->save();
			}
			/*
			*creating keywords for industry
			*/
			}
			
		}
		exit;
	}
	
	public function startMarkingIndustry()
	{
		$companyListwithIndustry = CompanyListComplete::where("industry_match",1)->first();
		if($companyListwithIndustry != '')
		{
			$nameofcompany = $companyListwithIndustry->name_of_company;
			$nameofcompanyArray = explode(" ",$nameofcompany);
			
			/* echo '<pre>';
			print_r($nameofcompanyArray);
			exit; */
			if(count($nameofcompanyArray) >0)
			{
			$n = count($nameofcompanyArray);
			$lastElement = $n-1;
			/*
			*creating keywords for industry
			*/
			for($i=1;$i<$lastElement;$i++)
			{
			$induskeywordModel = IndustryKeywords::where("keywords_name",$nameofcompanyArray[$i])->first();
			if($induskeywordModel != '' && strlen($nameofcompanyArray[$i])>2)
			{
				$industry = $induskeywordModel->industry;
				$updateMe = CompanyListComplete::find($companyListwithIndustry->id);
				$updateMe->Industry = $industry;
				$updateMe->keywords_match = $nameofcompanyArray[$i];
				$updateMe->industry_match = 2;
				$updateMe->save();
				echo "updated";
				exit;
			}
			else
			{
				$updateMe = CompanyListComplete::find($companyListwithIndustry->id);
				$updateMe->industry_match = 3;
				$updateMe->save();
				echo "Not match";
				exit;
				
			}
			
			
			}
			$updateMe = CompanyListComplete::find($companyListwithIndustry->id);
				$updateMe->industry_match = 3;
				$updateMe->save();
				echo "Not match";
				exit;
			/*
			*creating keywords for industry
			*/
			}
			else
			{
				$updateMe = CompanyListComplete::find($companyListwithIndustry->id);
				$updateMe->industry_match = 3;
				$updateMe->save();
				echo "Not match";
				exit;
			}
		}
		else
		{
		
				echo "all done";
				exit;
		}
	}
			
}
