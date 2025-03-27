<?php

namespace App\Http\Controllers\Components;
require_once "/srv/www/htdocs/core/autoload.php";
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;

use Carbon\Carbon;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\Onboarding\DocumentCollectionDetails;
use File;
class ComponentsController extends Controller
{
  public function targetComponent()
  {
	   return view("comTest/targetComponent");
  }
}
