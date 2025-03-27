<?php

namespace App\Http\Controllers\Profile;
require_once "/srv/www/htdocs/core/autoload.php";
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;

use Carbon\Carbon;

use File;
class AgentController extends Controller
{
   public function agentProfile()
   {
	  return view("profile/agentProfile");
   }
   
   public function questionsList()
   {
	   return view("profile/questionsList");
   }
   public function htmlDesign()
   {
	   return view("profile/htmlDesign");
   }
}
