<?php 
namespace App\Http\Controllers\Firebase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Firebase\Firebase;
use App\Models\Firebase\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

use Session;
define('FIREBASE_SERVICE_ACCOUNT_PATH', URL::to('/'). '/firebase/surani-group-hrm-firebase-adminsdk-2y1b2-c375dcb59b.json');
class FirebaseController extends Controller{
	
	public function firebaseButton_event(Request $request){
		
$user_id = Session::get('EmployeeId');//$_SESSION['user_id']; // Assume user_id is stored in session
$action = $request->action; // e.g., 'page_view', 'button_click'
$page = $request->event; // e.g., 'home_page', 'profile_page'
$time_spent = $request->page; // Time spent on the page in seconds
$dep_id = $request->dep_id; 
$data = [
    'emp_id' => $user_id,
    'action' => $action,
    'event_name' => $page,
	'department_id' => $dep_id,
    'page' => $time_spent,
    'timestamp' => date('Y-m-d H:i:s')
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];

$url = 'https://surani-group-hrm-default-rtdb.firebaseio.com/buttons.json'; // Firebase endpoint for user actions
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    // Handle error
    echo 'Error logging event';
} else {
    echo 'Event logged successfully';
}
	}
	
	public function firebasePage_event(Request $request){
		
$user_id = Session::get('EmployeeId');//$_SESSION['user_id']; // Assume user_id is stored in session
$action = $request->action; // e.g., 'page_view', 'button_click'
$page = $request->page; // e.g., 'home_page', 'profile_page'
$time_spent = $request->time_spent; // Time spent on the page in seconds
$dep_id = $request->dep_id;
$data = [
    'emp_id' => $user_id,
    'action' => $action,
    'event_name' => $page,
	'department_id' => $dep_id,
    'time_spent' => $time_spent,
    'timestamp' => date('Y-m-d H:i:s')
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];

$url = 'https://surani-group-hrm-default-rtdb.firebaseio.com/events.json'; // Firebase endpoint for user actions
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    // Handle error
    echo 'Error logging event';
} else {
    echo 'Event logged successfully';
}
	}
	
	public function loadFirebaseContents(Request $request)
	{
		$depart_id = $request->id;
		$user_id = $request->user_id;
		$searchValues=array();
		$paginationValue = 20;
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		if(isset($request->filter) && $request->filter!='default'){
		if($request->filter=='week'){
			$startdate=date('Y-m-d',strtotime('last week monday'));
			$enddate=date('Y-m-d',strtotime('last week sunday'));
			
			
		}elseif($request->filter=='month'){
			$startdate=date('Y-m-d',strtotime('first day of last month'));
			$enddate=date('Y-m-d',strtotime('last day of last month'));
			
			
		}
		elseif($request->filter=='yesterday'){
			$startdate=date('Y-m-d',strtotime('-1 day'));
			$enddate=date('Y-m-d',strtotime('-1 day'));
			
			
		}elseif($request->filter=='custom'){
			$startdate=date('Y-m-d',strtotime($request->startdate));
			$enddate=date('Y-m-d',strtotime($request->enddate));
			
			
		}
		//$request->session()->put('searchValuesfirebase',$request->filter);
		$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59" AND event_name!="undefined" ')->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = 	Firebase::where('user_id', $user_id)->where('event_type', 2)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59" AND event_name!="undefined" ')->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		
		}	else{
			$datefrom=date('Y-m-d',strtotime('-5 week'));
		$dateto=date('Y-m-d');
		//$firebase_details = Firebase::where("department_id",$depart_id)->orderBy("id","DESC")->paginate($paginationValue);        
		$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined" ')->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = Firebase::where('user_id', $user_id)->where('event_type', 2)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined" ')->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		}
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->where("event_type",1)->whereRaw('event_name!="undefined"')->groupBy("action")->get();  
		$action_details_button = Firebase::where("department_id",$depart_id)->where("event_type",2)->whereRaw('event_name!="undefined"')->groupBy("action")->get();  
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $user_id)->get();  
		foreach($usergrpby as $userids){
			$ids[]=$userids->user_id;
		}
		$getstring=implode(",",$ids);
		$users = User::whereRaw("id IN (".$getstring.")")->orderBy("fullname","ASC")->get();        

		$form_status = Firebase::where("department_id",$depart_id)->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($form_status);
        return view("Firebase/loadFirebaseContents",compact('firebase_details','form_status','action_details_button','event_details','users','action_details','depart_id','searchValues','buttonDetails','username'));
		
	}
	public function loadFirebaseContentsDashboard(Request $request)
	{
		$depart_id = $request->id;
		$user_id = $request->user_id;
		$searchValues=array();
		$paginationValue = 20;
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		if(isset($request->filter) && $request->filter!='default'){
		if($request->filter=='week'){
			$startdate=date('Y-m-d',strtotime('last week monday'));
			$enddate=date('Y-m-d',strtotime('last week sunday'));
			
			
		}elseif($request->filter=='month'){
			$startdate=date('Y-m-d',strtotime('first day of last month'));
			$enddate=date('Y-m-d',strtotime('last day of last month'));
			
			
		}
		elseif($request->filter=='yesterday'){
			$startdate=date('Y-m-d',strtotime('-1 day'));
			$enddate=date('Y-m-d',strtotime('-1 day'));
			
			
		}elseif($request->filter=='custom'){
			$startdate=date('Y-m-d',strtotime($request->startdate));
			$enddate=date('Y-m-d',strtotime($request->enddate));
			
			
		}
		$datefromshow=$startdate;
		$datetoshow=$enddate;
		//$request->session()->put('searchValuesfirebase',$request->filter);
		$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59" AND event_name!="undefined"')->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = 	Firebase::where('user_id', $user_id)->where('event_type', 2)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59" AND event_name!="undefined"')->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		
		}	else{
			$datefrom=date('Y-m-d',strtotime('-5 week'));
		$dateto=date('Y-m-d');
		$datefromshow=$datefrom;
		$datetoshow=$dateto;
		//$firebase_details = Firebase::where("department_id",$depart_id)->orderBy("id","DESC")->paginate($paginationValue);        
		$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined"')->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = Firebase::where('user_id', $user_id)->where('event_type', 2)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined"')->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		}
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->where("event_type",1)->whereRaw('event_name!="undefined"')->groupBy("action")->get();  
		$action_details_button = Firebase::where("department_id",$depart_id)->where("event_type",2)->whereRaw('event_name!="undefined"')->groupBy("action")->get();  
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $user_id)->get();  
		foreach($usergrpby as $userids){
			$ids[]=$userids->user_id;
		}
		$getstring=implode(",",$ids);
		$users = User::whereRaw("id IN (".$getstring.")")->orderBy("fullname","ASC")->get();        

		$form_status = Firebase::where("department_id",$depart_id)->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($form_status);
        return view("Firebase/loadFirebaseContentsDashboard",compact('datefromshow','datetoshow','firebase_details','form_status','action_details_button','event_details','users','action_details','depart_id','searchValues','buttonDetails','username'));
		
	}
	public function getEmployeepic($empid=null){
		$userspic = DB::table('employee_attribute')
                ->where('attribute_code', '=', 'EMP_Photo')
                ->where('emp_id', '=', $empid)
                ->get();
		
	}
	public function loadFirebaseanalytics(Request $request)
	{
		$buttonarr=$actionarr=array();
		$depart_id = $request->id;
		$user_id = $request->user_id;
		$searchValues=array();
		$paginationValue = 20;
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	

		//$firebase_details = Firebase::where("department_id",$depart_id)->orderBy("id","DESC")->paginate($paginationValue);        
		
		
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->where('user_id',$user_id)->where('event_type', 1)->groupBy("action")->get();  
		$action_detailsButton = Firebase::where("department_id",$depart_id)->where('user_id',$user_id)->where('event_type', 2)->groupBy("action")->get();  
		
		if(isset($request->filter) && $request->filter!='default'){
		if($request->filter=='currentMonth'){
			foreach($action_details as $actions){
			
			for($i=1; $i<=31; $i++){
				//$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				$datefrom=date('Y-m').'-'.$i;
				//$dateto=date('Y-m-d',strtotime(-$i.' day'));
				$dateto=date('Y-m').'-'.$i;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m').'-'.$i;
				$dateto=date('Y-m').'-'.$i;
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
			
		}elseif($request->filter=='LastMonth'){
			foreach($action_details as $actions){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m',strtotime('-1 month')).'-'.$i;
				$dateto=date('Y-m',strtotime('-1 month')).'-'.$i;;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m',strtotime('-1 month')).'-'.$i;
				$dateto=date('Y-m',strtotime('-1 month')).'-'.$i;
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
		}
		
		}else{
		foreach($action_details as $actions){
			
			for($i=0; $i<=7; $i++){
				$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				//$datefrom=date('Y-m').'-'.$i;
				$dateto=date('Y-m-d',strtotime(-$i.' day'));
				//$dateto=date('Y-m').'-'.$i;;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=0; $i<=7; $i++){
				$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				$dateto=date('Y-m-d',strtotime(-$i.' day'));
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59"')->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
		}
//echo "<pre>";
//		print_r($actionarr);echo "</pre>"; 
//		exit;
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $user_id)->get();  
		foreach($usergrpby as $userids){
			$ids[]=$userids->user_id;
		}
		$getstring=implode(",",$ids);
		$users = User::whereRaw("id IN (".$getstring.")")->orderBy("fullname","ASC")->get();        

		$form_status = Firebase::where("department_id",$depart_id)->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($form_status);
        return view("Firebase/loadFirebaseanalytics",compact('actionarr','form_status','event_details','users','action_details','depart_id','searchValues','buttonarr','username'));
		
	}
	public function loadFirebaseanalyticsDashboard(Request $request)
	{
		$buttonarr=$actionarr=$buttonDetails=array();
		$depart_id = $request->id;
		$user_id = $request->user_id;
		$searchValues=array();
		$paginationValue = 20;
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
	
		//$firebase_details = Firebase::where("department_id",$depart_id)->orderBy("id","DESC")->paginate($paginationValue);        
		
		
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->where('user_id',$user_id)->whereRaw('event_name!="undefined"')->where('event_type', 1)->groupBy("action")->get();  
		$action_detailsButton = Firebase::where("department_id",$depart_id)->where('user_id',$user_id)->whereRaw('event_name!="undefined"')->where('event_type', 2)->groupBy("action")->get();
		
		if(isset($request->filter) && $request->filter!='default'){
		if($request->filter=='currentMonth'){
			foreach($action_details as $actions){
			
			for($i=1; $i<=31; $i++){
				//$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				$datefrom=date('Y-m').'-'.$i;
				//$dateto=date('Y-m-d',strtotime(-$i.' day'));
				$dateto=date('Y-m').'-'.$i;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m').'-'.$i;
				$dateto=date('Y-m').'-'.$i;
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
			
		}elseif($request->filter=='LastMonth'){
			foreach($action_details as $actions){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m',strtotime('-1 month')).'-'.$i;
				$dateto=date('Y-m',strtotime('-1 month')).'-'.$i;;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=1; $i<=31; $i++){
				$datefrom=date('Y-m',strtotime('-1 month')).'-'.$i;
				$dateto=date('Y-m',strtotime('-1 month')).'-'.$i;
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
		}
		
		}else{
		foreach($action_details as $actions){
			
			for($i=0; $i<=7; $i++){
				$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				//$datefrom=date('Y-m').'-'.$i;
				$dateto=date('Y-m-d',strtotime(-$i.' day'));
				//$dateto=date('Y-m').'-'.$i;;
				
		$firebase_details[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 1)->where('action',$actions->action)->whereRaw('event_name!="undefined" AND action_time >= ? AND action_time <= ?', [$datefrom . ' 00:00:01', $dateto . ' 23:59:59'])->selectRaw('SUM(time_spent) as total_time')->get();        
		}
		$actionarr[$actions->action]=$firebase_details;
		}
		foreach($action_detailsButton as $actionsbutton){
			
			for($i=0; $i<=7; $i++){
				$datefrom=date('Y-m-d',strtotime(-$i.' day'));
				$dateto=date('Y-m-d',strtotime(-$i.' day'));
				
		$buttonDetails[$dateto] = Firebase::where('user_id', $user_id)->where('event_type', 2)->where('action',$actionsbutton->action)->whereRaw('event_name!="undefined" AND action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59"')->selectRaw('count(*) as totalcount, event_name, action, department_id')->get();       
		}
		$buttonarr[$actionsbutton->action]=$buttonDetails;
		}
		}
//echo "<pre>";
//		print_r($actionarr);echo "</pre>"; 
//		exit;
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $user_id)->get();  
		foreach($usergrpby as $userids){
			$ids[]=$userids->user_id;
		}
		$getstring=implode(",",$ids);
		$users = User::whereRaw("id IN (".$getstring.")")->orderBy("fullname","ASC")->get();        

		$form_status = Firebase::where("department_id",$depart_id)->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($form_status);
        return view("Firebase/loadFirebaseanalyticsDashboard",compact('actionarr','form_status','event_details','users','action_details','depart_id','searchValues','buttonarr','username'));
		
	}
	public function loadFirebaseContentsDetailedView(Request $request)
	{
		$requestParameters = $request->input();
		$depart_id = $request->dep_id;
		$user_id = $request->user_id;
		$action = $request->action;
		$searchValues=array();
		$paginationValue = 20;
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		
		if($request->isMethod('POST')){
			Session::forget('eventdetailinfo');
			Session::forget('whereRaw');
			$request->session()->put('eventdetailinfo', [
			'userid' => $user_id,
			'department_id' => $depart_id,
			'action' => $action,
			]);
		}
		$storedData = $request->session()->get('eventdetailinfo');
		$whereRawcon=$whereRaw="";
		if(@isset($requestParameters['start_date']) && $requestParameters['start_date']!='0')
		{	
			Session::forget('whereRaw');
			$start_date = @$requestParameters['start_date'];
			$whereRawcon .= " action_time >='".date('Y-m-d 00:00:01',strtotime($start_date))."'";
			$searchValues['start_date']=$start_date ;
		}
		if(@isset($requestParameters['end_date']) && $requestParameters['end_date']!='0')
		{
			$end_date = @$requestParameters['end_date'];
			$whereRawcon .= " AND action_time <='".date('Y-m-d 23:59:59',strtotime($end_date))."'";
			$searchValues['end_date']=$end_date ;
			$request->session()->put('whereRaw', [
			'whereRaw' => $whereRawcon,
			]);
		}
		$whereRawstore = $request->session()->get('whereRaw');
		if(!empty($whereRawstore)){
		$whereRaw = $whereRawstore['whereRaw'];
		}else{
			$datefrom=date('Y-m-d',strtotime('-5 week'));
		$dateto=date('Y-m-d');
		$whereRaw='action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined" ';
		}
$firebase_details = Firebase::where("department_id", $storedData['department_id'])
    ->where('user_id', $storedData['userid'])
    ->where('action', $storedData['action'])
    ->when(!empty($whereRaw), function ($query) use ($whereRaw) {
        return $query->whereRaw($whereRaw);
    })
    ->orderBy("action_time", "DESC")
    ->paginate($paginationValue);
		//print_r($firebase_details);exit;
		//$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->selectRaw('SUM(time_spent) as total_time, action, event_name')->groupBy('action')->get();        
		$buttonDetails = Firebase::where('user_id', $user_id)->where('event_type', 2)->selectRaw('count(*) as count, event_name, action')->groupBy('action')->get();       
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->groupBy("action")->get();  
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $storedData['userid'])->get();  
		
		$form_status = Firebase::where("department_id", $storedData['department_id'])
    ->where('user_id', $storedData['userid'])
    ->where('action', $storedData['action'])
    ->whereRaw('time_spent > 0')
    ->when(!empty($whereRaw), function ($query) use ($whereRaw) {
        return $query->whereRaw($whereRaw);
    })
    ->selectRaw('count(*) as total, department_id')
    ->get();

		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($requestParameters['start_date']);exit;
         if(@isset($requestParameters['start_date']) && $requestParameters['start_date']!='0')
		{
			return view("Firebase/loadFirebaseFilterContent",compact('firebase_details','form_status', 'storedData','event_details','action_details','depart_id','searchValues','buttonDetails','username'));
			}else{
		return view("Firebase/loadFirebaseContentsDetailedView",compact('firebase_details','form_status', 'storedData','event_details','action_details','depart_id','searchValues','buttonDetails','username'));
			}
		
	}
	public function loadFirebaseContentsDetailedViewButton(Request $request)
	{
		
		$depart_id = $request->dep_id;
		$user_id = $request->user_id;
		$action = $request->action;
		$searchValues=array();
		$paginationValue = 20;
		
		if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}
		if($request->isMethod('POST')){
			Session::forget('eventdetailinfo');
			$request->session()->put('eventdetailinfo', [
			'userid' => $user_id,
			'department_id' => $depart_id,
			'action' => $action,
			]);
		}
		$storedData = $request->session()->get('eventdetailinfo');
		$datefrom=date('Y-m-d',strtotime('-5 week'));
		$dateto=date('Y-m-d');
		$firebase_details = Firebase::where("department_id",$storedData['department_id'])->where('user_id',$storedData['userid'])->where('action',$storedData['action'])->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined" ')->orderBy("action_time","DESC")->paginate($paginationValue);        
		//$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->selectRaw('SUM(time_spent) as total_time, action, event_name')->groupBy('action')->get();        
		$buttonDetails = Firebase::where('user_id', $user_id)->where('event_type', 2)->selectRaw('count(*) as count, event_name, action')->groupBy('action')->get();       
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->groupBy("action")->get();  
		$usergrpby = Firebase::where("department_id",$depart_id)->groupBy("user_id")->get();  
		$username = User::where('id', $storedData['userid'])->get();  
		
		$form_status = Firebase::where("department_id",$storedData['department_id'])->where('user_id',$storedData['userid'])->where('action',$storedData['action'])->whereRaw('action_time >= "' . $datefrom . ' 00:00:01" AND action_time <= "' . $dateto . ' 23:59:59" AND event_name!="undefined" ')->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		

        return view("Firebase/loadFirebaseContentsDetailedViewButton",compact('firebase_details','form_status','event_details','action_details','depart_id','searchValues','buttonDetails','username','storedData'));
		
	}
	public function loadFirebasefilterContents(Request $request)
	{
		$event=$action=$emp_id=$start_date=$end_date=$content=$dep_id='';
		$requestParameters = $request->input();
		$whereRaw = "department_id=".$requestParameters['depart_id'];	

		if(@isset($requestParameters['event']) && $requestParameters['event']!='0')
		{
			$event = @$requestParameters['event'];
			$whereRaw.= " AND event_name='".$event."'";
		}
		if(@isset($requestParameters['action']) && $requestParameters['action'] != '0')
		{
			$action = @$requestParameters['action'];
			$whereRaw.= " AND action='".$action."'";
		}
		if(@isset($requestParameters['emp_id']) && $requestParameters['emp_id']!='0')
		{
			$emp_id = @$requestParameters['emp_id'];
			$whereRaw .= " AND user_id='".$emp_id."'";
			
		}
		if(@isset($requestParameters['start_date']) && $requestParameters['start_date']!='0')
		{
			$start_date = @$requestParameters['start_date'];
			$whereRaw .= " AND action_time >='".date('Y-m-d 00:00:01',strtotime($start_date))."'";
		}
		if(@isset($requestParameters['end_date']) && $requestParameters['end_date']!='0')
		{
			$end_date = @$requestParameters['end_date'];
			$whereRaw .= " AND action_time <='".date('Y-m-d 23:59:59',strtotime($end_date))."'";
		}
$firebasefiltercount = DB::table('firebase_data')->whereRaw($whereRaw)->orderby('action_time','DESC')->get()->count();
$firebasefilter = DB::table('firebase_data')->whereRaw($whereRaw)->orderby('action_time','DESC')->get();
		
foreach($firebasefilter as $key=>$value){
if($value->department_id==36){
		$dep_id ='Mashreq';
	}elseif($value->department_id==49){
		$dep_id ='CBD';
	}if($value->department_id==1){
		$dep_id ='Dashboard';
	}

	
	$username = DB::table('users')->where('id', $value->user_id)->first();
	
	$content.= '<tr class="docClassCollection">
                 <td>'.$username->fullname.'</td>
                  <td>'.$value->event_name.'</td>
                  <td>'.$value->action.'</td>
                  <td>'.$value->time_spent.'</td>
                  <td >'.$value->action_time.'</td>
                  <td>'.$dep_id.'</td>
                                 </tr>';
}

		$array =json_encode(array('table'=>$content,'count'=>$firebasefiltercount)) ;
		echo $array;
		
	}

	public function setPaginationValueFirebase(Request $request)
	{
		$offSetValue = $request->offSetValue;
		$request->session()->put('firebasePaginationValue',$offSetValue);
		echo "success";
		//return redirect("loadFirebaseContents/1");
	}
	
	 public function updateFirebaseDB(Request $request)
    {
		 // Path to the service account JSON file
        $serviceAccountPath = __DIR__ . '/firebase/surani-group-hrm-firebase-adminsdk-2y1b2-c375dcb59b.json';

        try {
            $accessToken = $this->getAccessToken(FIREBASE_SERVICE_ACCOUNT_PATH);

            $databaseUrl = 'https://surani-group-hrm-default-rtdb.firebaseio.com/events';
			$start= date('Y-m-d',strtotime('-90 day'));
			
            $filters = [
                'orderBy' => '"timestamp"',
                'startAt' => '"'.$start.' 00:00:01"',  // Start date/time (inclusive)
                'endAt' => '"'.$start.' 23:59:59"',    // End date/time (exclusive)
            ];


			$data = $this->getFirebaseData($databaseUrl, $accessToken, $filters);
            $this->insertFirebaseData($data);

            return response()->json(['message' => 'Data fetched and inserted successfully!'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
	public function updateFirebaseDB_button(Request $request)
    {
		
		 // Path to the service account JSON file
        $serviceAccountPath = __DIR__ . '/firebase/surani-group-hrm-firebase-adminsdk-2y1b2-c375dcb59b.json';

        try {
            $accessToken = $this->getAccessToken(FIREBASE_SERVICE_ACCOUNT_PATH);

            $databaseUrl = 'https://surani-group-hrm-default-rtdb.firebaseio.com/buttons';
			$start= date('Y-m-d',strtotime('-1 day'));
			
            $filters = [
                'orderBy' => '"timestamp"',
                'startAt' => '"'.$start.' 00:00:01"',  // Start date/time (inclusive)
                'endAt' => '"'.$start.' 23:59:59"',    // End date/time (exclusive)
            ];


			$data = $this->getFirebaseData($databaseUrl, $accessToken, $filters);
            $this->insertFirebaseDatabutton($data);

            return response()->json(['message' => 'Data fetched and inserted successfully!'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   public function getFirebaseData($databaseUrl, $accessToken, $filters = []) {
    $queryParams = http_build_query($filters);
    $url = $databaseUrl . '.json?access_token=' . $accessToken . '&' . $queryParams;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result === false) {
        throw new Exception('Failed to fetch data from Firebase');
    }
    
    return json_decode($result, true);
}

    public function getAccessToken($serviceAccountPath)
    {
       $jsonKey = json_decode(file_get_contents($serviceAccountPath), true);
    $now = time();
    $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $jwtClaimSet = base64_encode(json_encode([
        'iss' => $jsonKey['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/userinfo.email',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]));
    
    $jwtUnsigned = $jwtHeader . '.' . $jwtClaimSet;
    $signature = '';
    openssl_sign($jwtUnsigned, $signature, $jsonKey['private_key'], 'sha256WithRSAEncryption');
    $jwtSigned = $jwtUnsigned . '.' . base64_encode($signature);
    
    $postFields = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwtSigned
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result === false) {
        throw new Exception('Failed to get access token');
    }
    
    $resultData = json_decode($result, true);
    return $resultData['access_token'];

    }

    public function insertFirebaseData($data)
    {
		
        foreach ($data as $key => $forDB) {
			
			$checkExist=false;
  $checkExist = Firebase::where('firebase_unique_id', $key)->get();  
 // print_r($forDB);exit;
  	if($checkExist->first()){
		continue;
	}else{
		if(isset($forDB['department_id'])){
DB::table('firebase_data')->insert([
    'user_id' => $forDB['emp_id'],
    'emp_id' => $forDB['emp_id'],
    'event_name' => $forDB['event_name'],
    'event_type' => 1,
    'department_id' => $forDB['department_id'],
    'action' => $forDB['action'],
    'time_spent' => isset($forDB['time_spent'])?$forDB['time_spent']:0,
    'firebase_unique_id' => $key,
    'action_time' => $forDB['timestamp'],
    'action_date' => date('Y-m-d', strtotime($forDB['timestamp']))]);
		}
	}
		}
    }
	public function insertFirebaseDatabutton($data)
    {
		//print_r($data);exit;
        foreach ($data as $key => $forDB) {
			
			$checkExist=false;
  $checkExist = Firebase::where('firebase_unique_id', $key)->get();  
 // print_r($forDB);exit;
  	if($checkExist->first()){
		continue;
	}else{
		if(isset($forDB['department_id'])){
DB::table('firebase_data')->insert([
    'user_id' => $forDB['emp_id'],
    'emp_id' => $forDB['emp_id'],
    'event_name' => $forDB['event_name'],
    'event_type' => 2,
    'department_id' => $forDB['department_id'],
    'action' => $forDB['action'],
    'time_spent' => 0,
    'firebase_unique_id' => $key,
    'action_time' => $forDB['timestamp'],
    'action_date' => date('Y-m-d', strtotime($forDB['timestamp']))]);
		}
	}
		}
    }
	public function CommonTrackingView(Request $request)
	{
		$depart_id = $request->id;
		$user_id = $request->user_id;
		$searchValues=array();
		$paginationValue = 20;
	/*	if(@$request->session()->get('firebasePaginationValue') != '')
		{
			$paginationValue = $request->session()->get('firebasePaginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}*/	
		if(isset($request->filter) && $request->filter!='default'){
	/*	if($request->filter=='week'){
			$startdate=date('Y-m-d',strtotime('last week monday'));
			$enddate=date('Y-m-d',strtotime('last week sunday'));
			
			
		}elseif($request->filter=='month'){
			$startdate=date('Y-m-d',strtotime('first day of last month'));
			$enddate=date('Y-m-d',strtotime('last day of last month'));
			
			
		}
		elseif($request->filter=='yesterday'){
			$startdate=date('Y-m-d',strtotime('-1 day'));
			$enddate=date('Y-m-d',strtotime('-1 day'));
			
			
		}elseif($request->filter=='custom'){
			$startdate=date('Y-m-d',strtotime($request->startdate));
			$enddate=date('Y-m-d',strtotime($request->enddate));
			
			
		}*/
		//$request->session()->put('searchValuesfirebase',$request->filter);
		$firebase_details = Firebase::where('user_id', $user_id)->where('event_type', 1)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59"')->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = 	Firebase::where('user_id', $user_id)->where('event_type', 2)->where("department_id",$depart_id)->whereRaw('action_time >= "' . $startdate . ' 00:00:01" AND action_time <= "' . $enddate . ' 23:59:59"')->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		
	}else{
		//Session::forget('searchValuesfirebase');
		$firebase_details = Firebase::where('user_id', 1)->where('event_type', 1)->where("department_id",$depart_id)->selectRaw('SUM(time_spent) as total_time, action, event_name, department_id')->groupBy('action')->get();        
		$buttonDetails = Firebase::where('user_id', 1)->where('event_type', 2)->where("department_id",$depart_id)->selectRaw('count(*) as count, event_name, action, department_id')->groupBy('action')->get();       
		}
		$event_details = Firebase::where("department_id",$depart_id)->groupBy("event_name")->get();        
		$action_details = Firebase::where("department_id",$depart_id)->where("event_type",1)->groupBy("action")->get();  
		$action_details_button = Firebase::where("department_id",$depart_id)->where("event_type",2)->groupBy("action")->get();  
		$usergrpby = Firebase::where("department_id",36)->groupBy("user_id")->get();  
		$username = User::where('id', $user_id)->get();  
		foreach($usergrpby as $userids){
			$ids[]=$userids->user_id;
		}
		$getstring=implode(",",$ids);
		$users = User::whereRaw("id IN (".$getstring.")")->orderBy("fullname","ASC")->get();        

		$form_status = Firebase::where("department_id",$depart_id)->selectRaw('count(*) as total, department_id')->get();
		//$firebasepage = Firebase::where("department_id",$depart_id)->orderby('id','DESC');
		
//print_r($form_status);
        return view("Firebase/CommonTrackingView",compact('firebase_details','form_status','action_details_button','event_details','users','action_details','depart_id','searchValues','buttonDetails','username'));
		
	}
}