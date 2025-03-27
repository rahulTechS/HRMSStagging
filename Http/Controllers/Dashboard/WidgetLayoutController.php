<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Dashboard\DashboardCreation;
use App\Models\Dashboard\WidgetCreation;
use App\Models\Dashboard\DashboardParentMenu;
use App\Models\Dashboard\WidgetLayout;
use App\Models\Dashboard\WidgetLeadershipDetails;
use App\Models\Dashboard\Widgetlayouts\WidgetOnboardingHiring;
use App\Models\Dashboard\Widgetlayouts\WidgetBarOnboarded;
use App\Models\Dashboard\Widgetlayouts\WidgetBarMol;
use App\Models\Dashboard\Widgetlayouts\WidgetBarShortlisted;
use App\Models\Dashboard\Widgetlayouts\WidgetBarEvisa;

use App\Models\Entry\Employee;
use App\Models\SEPayout\AgentPayout;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\AgentPayoutDeem;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\Dashboard\Widgetlayouts\WidgetBarOnboardedInComplete;


class WidgetLayoutController extends Controller
{
  
	public function loadLayout(Request $request)
	{
		
		$lId = $request->lId;
		
		if($lId == 1)
		{
			return view("dashboard/widget/layout/leadership"); 
		}
		else if($lId == 2)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/hiring-onboarding/hiring_onboarding",compact('jobopeningList')); 
		}
		else if($lId == 3)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Shortlisted",compact('jobopeningList')); 
		}
		else if($lId == 4)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/MOL",compact('jobopeningList')); 
		}
		else if($lId == 35)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/EVisa",compact('jobopeningList')); 
		}
		else if($lId == 5)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Onboarded",compact('jobopeningList')); 
		}
		else if($lId == 45)
		{
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Onboardedincomplete",compact('jobopeningList')); 
		}
		else if($lId == 6)
		{

			return view("dashboard/widget/layout/masterpayout"); 
		}
		else
		{
			echo "not created";
			exit;
		}
		
	}
	
	
	public static function getJobOpeningName($jobOpeningId)
	{
		return JobOpening::where("id",$jobOpeningId)->first()->name;
	}
	public static function getDepartmentName($id)
	{	
	
	  $data = Department::where('id',$id)->first();
	  if($data != '')
	  {
		
	  return $data->department_name;
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getLocation($id)
	{	
	
	  $data = JobOpening::where('id',$id)->first();
	  if($data != '')
	  {
		
	  return $data->location;
	  }
	  else
	  {
	  return '';
	  }
	}
	
	
	
	public function loadBank(Request $request)
	{
		$bankLeaderId = $request->bankLeaderId;
		$salesTimeList = array();
		if($bankLeaderId == 'ENBD')
		{
			
			$collection = AgentPayout::groupBy('sales_time')
			->selectRaw('count(*) as total, sales_time')->orderBy("sort_order","DESC")
			->get();
			foreach($collection as $col)
			{
				if($col->end_sales_time != '' && $col->end_sales_time != NULL)
				{
					$salesTimeList[$col->sales_time] = $this->ProperTime($col->sales_time);
				}
			}
		}
		elseif($bankLeaderId == 'Mashreq')
		{
			$collection = AgentPayoutMashreq::groupBy('end_sales_time')
			->selectRaw('count(*) as total, end_sales_time')->orderBy("sort_order","DESC")
			->get();
			foreach($collection as $col)
			{
				if($col->end_sales_time != '' && $col->end_sales_time != NULL)
				{
					$salesTimeList[$col->end_sales_time] = $this->ProperTime($col->end_sales_time);
				}
			}
		}
		else
		{
			$collection = AgentPayoutDeem::groupBy('sales_time')
			->selectRaw('count(*) as total, sales_time')->orderBy("sort_order","DESC")
			->get();
			
		}
		foreach($collection as $col)
			{
				if($col->sales_time != '' && $col->sales_time != NULL)
				{
					$salesTimeList[$col->sales_time] = $this->ProperTime($col->sales_time);
				}
			}
		return view("dashboard/widget/layout/loadBank",compact('salesTimeList')); 
		
		
	}
	
	
	
	public function loadBankUpdate(Request $request)
	{
		$bankLeaderId = $request->bankLeaderId;
		$wid = $request->wid;
		$salesTimeList = array();
		if($bankLeaderId == 'ENBD')
		{
			
			$collection = AgentPayout::groupBy('sales_time')
			->selectRaw('count(*) as total, sales_time')->orderBy("sort_order","DESC")
			->get();
			foreach($collection as $col)
			{
				if($col->end_sales_time != '' && $col->end_sales_time != NULL)
				{
					$salesTimeList[$col->sales_time] = $this->ProperTime($col->sales_time);
				}
			}
		}
		elseif($bankLeaderId == 'Mashreq')
		{
			$collection = AgentPayoutMashreq::groupBy('end_sales_time')
			->selectRaw('count(*) as total, end_sales_time')->orderBy("sort_order","DESC")
			->get();
			foreach($collection as $col)
			{
				if($col->end_sales_time != '' && $col->end_sales_time != NULL)
				{
					$salesTimeList[$col->end_sales_time] = $this->ProperTime($col->end_sales_time);
				}
			}
		}
		else
		{
			$collection = AgentPayoutDeem::groupBy('sales_time')
			->selectRaw('count(*) as total, sales_time')->orderBy("sort_order","DESC")
			->get();
			
		}
		foreach($collection as $col)
			{
				if($col->sales_time != '' && $col->sales_time != NULL)
				{
					$salesTimeList[$col->sales_time] = $this->ProperTime($col->sales_time);
				}
			}
			$widgetDtails = WidgetLeadershipDetails::where("widget_id",$wid)->first();
			
		return view("dashboard/widget/layout/loadBankUpdate",compact('salesTimeList','widgetDtails')); 
		
		
	}
	
	protected function ProperTime($saleTime)
	{
		$salesTimeArray = explode("-",$saleTime);
		$monthName = date('F', mktime(0, 0, 0, $salesTimeArray[0], 10));
		return $monthName.' '.$salesTimeArray[1];
	}
	
	public function layoutWidgetPost(Request $request)
	{
		$parametersInput = $request->input();
		/* echo "<pre>";
		print_r($parametersInput);
		exit; */
		$layout_id = $parametersInput['layout_id'];
		$widget_id = $parametersInput['widget_id'];
		if($layout_id  == 1)
		{
		
		
			$bank = $parametersInput['bank'];
			
			
			$saveLeaderShip = new WidgetLeadershipDetails();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->bank = $bank;
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		else if($layout_id  == 2)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetOnboardingHiring();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		
		else if($layout_id  == 3)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetBarShortlisted();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		
		else if($layout_id  == 4)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetBarMol();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		else if($layout_id  == 35)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetBarEvisa();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		
		else if($layout_id  == 5)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetBarOnboarded();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		else if($layout_id  == 45)
		{
			$job_opening = $parametersInput['job_opening'];
			
			
			$saveLeaderShip = new WidgetBarOnboardedInComplete();
			$saveLeaderShip->widget_id = $widget_id;
			
			$saveLeaderShip->job_opening = implode(",",$job_opening);
			if($saveLeaderShip->save())
			{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			}
			else
			{
				$request->session()->flash('message','issue to add Widget Layout.');
			}
		}
		else if($layout_id  == 6)
		{
			
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			
			
		}
		else if($layout_id  == 7)
		{
			
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			
			
		}
		else if($layout_id  == 8)
		{
			
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
			
			
		}
		else{
			$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
				$request->session()->flash('message','Widget Layout Added Successfully.');
		}
		
        return redirect('widgetCreation');
	}
	public static function getlayoutName($layoutId)
	{
		return WidgetLayout::where("id",$layoutId)->first()->name;
	}
	
	public function updateLoadLayout(Request $request)
	{
		$lId = $request->lId;
		//echo $lId;exit;
		
		if($lId == 1)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetLeadershipDetails::where("id",$wDId)->first();
			
			return view("dashboard/widget/layout/leadershipedit",compact('detailsWidget')); 
		}
		else if($lId == 2)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetOnboardingHiring::where("id",$wDId)->first();
			
			
			
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/hiring-onboarding/hiring_onboarding_edit",compact('jobopeningList','detailsWidget')); 
		}
		else if($lId == 3)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetBarShortlisted::where("id",$wDId)->first();
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Shortlisted_edit",compact('jobopeningList','detailsWidget')); 
		}
		else if($lId == 4)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetBarMol::where("id",$wDId)->first();
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/MOL_edit",compact('jobopeningList','detailsWidget')); 
		}
		else if($lId == 35)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetBarEvisa::where("id",$wDId)->first();
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/EVisa_edit",compact('jobopeningList','detailsWidget')); 
		}
		else if($lId == 5)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetBarOnboarded::where("id",$wDId)->first();
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Onboarded_edit",compact('jobopeningList','detailsWidget')); 
		}
		else if($lId == 45)
		{
			$wDId = $request->wDId;
			$detailsWidget = WidgetBarOnboardedInComplete::where("id",$wDId)->first();
			$jobopeningList = InterviewProcess::groupBy('job_opening')
							->selectRaw('count(*) as total, job_opening')
							->get();
			return view("dashboard/widget/layout/BarChart/Onboardedincomplete_edit",compact('jobopeningList','detailsWidget')); 
		}
		else
		{
			echo "not created";
			exit;
		}
	}
	
	public function updateLayoutWidgetPost(Request $request)
	{
		$parameterRequest = $request->input();
		$widget_detail_id = $parameterRequest['widget_detail_id'];
		$widget_id = $parameterRequest['widget_id'];
		$layout_id = $parameterRequest['layout_id'];
		//print_r($layout_id);exit;
		if($layout_id == 1)
		{
			$bank = $parameterRequest['bank'];
			/*
			*checking 
			*/
			$widgetExsit = WidgetLeadershipDetails::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$widgetLUpdate = new WidgetLeadershipDetails();
			}
			else
			{
				$widgetLUpdate = WidgetLeadershipDetails::find($widget_detail_id);
			}
		$widgetLUpdate->widget_id = $widget_id;
		$widgetLUpdate->bank = $bank;
		$widgetLUpdate->save();
		}
		else if($layout_id  == 2)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetOnboardingHiring::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetOnboardingHiring();
			}
			else
			{
				$updateLeaderShip = WidgetOnboardingHiring::find($widget_detail_id);
			}
			
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		else if($layout_id  == 3)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetBarShortlisted::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetBarShortlisted();
			}
			else
			{
				$updateLeaderShip = WidgetBarShortlisted::find($widget_detail_id);
			}
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		else if($layout_id  == 4)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetBarMol::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetBarMol();
			}
			else
			{
			$updateLeaderShip = WidgetBarMol::find($widget_detail_id);
			}
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		else if($layout_id  == 35)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetBarEvisa::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetBarEvisa();
			}
			else
			{
			$updateLeaderShip = WidgetBarEvisa::find($widget_detail_id);
			}
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		else if($layout_id  == 5)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetBarOnboarded::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetBarOnboarded();
			}
			else
			{
				$updateLeaderShip = WidgetBarOnboarded::find($widget_detail_id);
			}
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		else if($layout_id  == 45)
		{
			 $job_opening = $parameterRequest['job_opening'];
			
			/*
			*checking 
			*/
			$widgetExsit = WidgetBarOnboardedInComplete::where("id",$widget_detail_id)->first();
			/*
			*checking 
			*/
			if($widgetExsit == '')
			{
				$updateLeaderShip = new WidgetBarOnboardedInComplete();
			}
			else
			{
				$updateLeaderShip = WidgetBarOnboardedInComplete::find($widget_detail_id);
			}
			$updateLeaderShip->widget_id = $widget_id;
			
			$updateLeaderShip->job_opening = implode(",",$job_opening);
			$updateLeaderShip->save();
			
		}
		/*
		*update layout in widget table
		*/
		else{
				$updateWidget = WidgetCreation::find($widget_id);
				$updateWidget->widget_layout = 2;
				$updateWidget->widget_layout_id = $layout_id;
				$updateWidget->save();
		}
		/*
		*update layout in widget table
		*/
		
		$request->session()->flash('message','Widget Layout Updated.');
		return redirect('widgetCreation');
	}
	
	
}