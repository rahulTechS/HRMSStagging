<?php

namespace App\Http\Controllers\RedFlags;
require_once "/srv/www/htdocs/core/autoload.php";
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\RedFlags\MashreqRevenueList;
use App\Models\RedFlags\MashreqRevenueRedflag;

class GhaphController extends Controller
{
    
	public function performanceCompare()
	{
		return view("Ghaph/performanceCompare");
	}
	
	
}
