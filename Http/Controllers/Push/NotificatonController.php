<?php

namespace App\Http\Controllers\Push;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\CrossSell\CrossSellScenariosAllocation;
use App\Models\API\V2\DeviceTokenFrontend;
use App\Models\Employee\Employee_details;
use App\Models\Attribute\DepartmentFormEntry;
use Session;

class NotificatonController extends Controller
{
	
	
function base64UrlEncode1($text)
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($text)
    );
}
	public function sendNotificationA($deviceToken,$title,$body,$page)
	{


			// Read service account details
			$authConfigString = file_get_contents("http://34.250.199.95/ANKeys/smarthrm-mobile-firebase-adminsdk-nqypp-79bf9de39e.json");
			/* echo $authConfigString;exit; */
			// Parse service account details
			$authConfig = json_decode($authConfigString);
			/*  echo "<pre>";
			print_R($authConfig);
			exit; 
			 */// Read private key from service account details
			$secret = openssl_get_privatekey($authConfig->private_key);

			// Create the token header
			$header = json_encode([
				'typ' => 'JWT',
				'alg' => 'RS256'
			]);

			// Get seconds since 1 January 1970
			$time = time();

			// Allow 1 minute time deviation between client en server (not sure if this is necessary)
			$start = $time - 60;
			$end = $start + 3600;

			// Create payload
			$payload = json_encode([
				"iss" => $authConfig->client_email,
				"scope" => "https://www.googleapis.com/auth/firebase.messaging",
				"aud" => "https://oauth2.googleapis.com/token",
				"exp" => $end,
				"iat" => $start
			]);

			// Encode Header
			$base64UrlHeader = $this->base64UrlEncode1($header);

			// Encode Payload
			$base64UrlPayload = $this->base64UrlEncode1($payload);

			// Create Signature Hash
			$result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $secret, OPENSSL_ALGO_SHA256);

			// Encode Signature to Base64Url String
			$base64UrlSignature = $this->base64UrlEncode1($signature);

			// Create JWT
			$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

			//-----Request token, with an http post request------
			$options = array('http' => array(
				'method'  => 'POST',
				'content' => 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion='.$jwt,
				'header'  => "Content-Type: application/x-www-form-urlencoded"
			));
			$context  = stream_context_create($options);
			$responseText = file_get_contents("https://oauth2.googleapis.com/token", false, $context);

			$response = json_decode($responseText);
			 /* echo "<pre>";
			print_r($response);
			echo "======================="; 
			exit;
 */


					//$registration_ids = 'esji0LKnSCOghL4aKkD1id:APA91bFXMjMhjxq9Nzo0bSE2z8mrT1pPoy0IYf3irVPML7T5_o8paT79JJxh80TNI83KFNjG_09jMAfFDrDVknR1BEO4riJ4z9Gg7yG0_5bMZMjJgN7xPFaTTKYPHGDTBreuw1zLco4D';
					// $url = 'https://android.googleapis.com/gcm/send';
					$url = 'https://fcm.googleapis.com/v1/projects/smarthrm-mobile/messages:send';
					$message = array(
						'title' => $title,
						'message' => $body,
						'page' => $page
					);
					
					
					$notification = array(
					'title' => $title,
					'body'  => $body
				);
				
					$fields = array(
					'message' => array(
						'token' => $deviceToken,  // Device token passed here
						'notification' => $notification,
						'data' => $message
					)
				);


					$headers = array(
						'Authorization: Bearer '.$response->access_token,
						'Content-Type: application/json'
					);
					
					/* print_r($headers);
					exit; */
					//json_encode($fields);
					 // Initialize cURL
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

				$result = curl_exec($ch);
				if ($result === FALSE) {
					die('Curl failed: ' . curl_error($ch));
				}

				curl_close($ch);
			  print_R($result);
			exit; 
				//return $result;
 
	}
    
	

public function send_ISO_notification($deviceToken,$title,$body,$page)
		{
			/*  echo "ddd";
			exit; */
// Your .p8 file
$p8FilePath = 'https://www.hr-suranigroup.com/Surani_Push-Key.p8';


/* 
$privateKeyContent = file_get_contents($p8FilePath);

$privateKey = openssl_pkey_get_private($privateKeyContent);
if (!$privateKey) {
    die('Invalid private key');
}
echo "ddd";

exit; */



// APNs authentication details
$teamId = 'G4865H7F96'; // Found in the Apple Developer Account
$keyId = '36Z98UNHL7';   // ID of your .p8 key
$bundleId = 'com.smartUnion.suraniGroup'; // The app's bundle ID
//$deviceToken = '77C95588979C85D78E70AD41E386654C58CF063148A49AA95940017D930BD14D'; // Token of the target iOS device

// APNs URL for production or sandbox (development)
 /* echo $deviceToken;exit;  */
$apnsUrl = 'https://api.sandbox.push.apple.com/3/device/' .$deviceToken; // Use api.push.apple.com for production

// Create a JWT token
$header = [
    'alg' => 'ES256',
    'kid' => $keyId
];
$claims = [
    'iss' => $teamId,
    'iat' => time()
];



$jwtHeader = $this->base64UrlEncode(json_encode($header));
$jwtClaims = $this->base64UrlEncode(json_encode($claims));

// Sign the token using ES256 (Elliptic Curve Digital Signature Algorithm)
$privateKeyContent = file_get_contents($p8FilePath);
$privateKey =openssl_pkey_get_private($privateKeyContent);
openssl_sign($jwtHeader . '.' . $jwtClaims, $signature, $privateKey, 'sha256');

$jwt = $jwtHeader . '.' . $jwtClaims . '.' . $this->base64UrlEncode($signature);

// The payload (message) of the notification
$payload = [
    'aps' => [
        'alert' => [
            'title' => $title,
            'body' => $body,
			'page' =>$page
        ],
        'sound' => 'default'
    ]
];

// Prepare the curl request to APNs
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apnsUrl);
curl_setopt($ch, CURLOPT_PORT, 443);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'authorization: bearer ' . $jwt,
    'apns-topic: ' . $bundleId,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the curl request
echo $response = curl_exec($ch);
exit;
// Check for errors
if ($response === false) {
    $error = curl_error($ch);
    echo "Curl error: " . $error . "\n";
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
        echo "Notification sent successfully.\n";
    } else {
        echo "Failed to send notification. HTTP status code: $httpCode\n";
        echo "Response: $response\n";
    }
}
exit;
curl_close($ch);


		}
		
		
public  function base64UrlEncode($data) {
    return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
}


public function ManageNotificationforLead()
{
	$notifyData = CrossSellScenariosAllocation::whereNull("notify_status")->where("allocate_to","Agent")->get();
	
	foreach($notifyData as $data)
	{
		
		$empId = $data->emp_id;
		
		$deviceTokenMod = DeviceTokenFrontend::where("emp_id",$empId)->first();
		if($deviceTokenMod != '')
		{
			$deviceType = $deviceTokenMod->device_type;
			 /* echo $deviceType;exit;  */
			if($deviceType == 'IOS')
			{
				
				$body = 'You Have Assigned '.$data->allocated_count.' leads. visit Leads Management.';
				$this->send_ISO_notification($deviceTokenMod->device_token,'Leads notification',$body);
			}
			else
			{
				$body = 'You Have Assigned '.$data->allocated_count.' leads. visit Leads Management.';
				$this->sendNotificationA($deviceTokenMod->device_token,'Leads notification',$body);
			}
			/*
			*update Notification Status
			*/
			$updateNotify = CrossSellScenariosAllocation::find($data->id);
			$updateNotify->notify_status = 1;
			$updateNotify->save();
		}
		else
		{
			$updateNotify = CrossSellScenariosAllocation::find($data->id);
			$updateNotify->notify_status = 3;
			$updateNotify->save();
		}
		
	}
	

}





public function ManageNotificationforAttendance()
{

		$deviceTokenMod = DeviceTokenFrontend::get();
		foreach($deviceTokenMod as $dTken)
		{
		
			$deviceType = $dTken->device_type;
			if($dTken->emp_id == '101372')
			{
			 /* echo $deviceType;exit;  */
			if($deviceType == 'IOS')
			{
				
				$body = 'Mark Your Attendance.';
				$this->send_ISO_notification($dTken->device_token,'Attendance Notification',$body,'MarkAttendance');
			}
			else
			{
				$body = 'Mark Your Attendance.';
				$this->sendNotificationA($dTken->device_token,'Attendance Notification',$body,'MarkAttendance');
			}
			/*
			*update Notification Status
			*/
			}
			
		
		}
		
	
}


public static function sendMeNotification($empId,$title,$body,$page)
{
	/* echo $empId;
	echo "<br/>";
	echo $title;
	echo "<br/>";
	echo $body;
	echo "<br/>";
	echo $page;
	exit; */
		$obj = new NotificatonController();
		$deviceTokenMod = DeviceTokenFrontend::where("emp_id",$empId)->first();
		
		if($deviceTokenMod != '')
		{
			$deviceType = $deviceTokenMod->device_type;
			  
			if($deviceType == 'IOS')
			{
				
				$body = $body;
				$obj->send_ISO_notification($deviceTokenMod->device_token,$title,$body,$page);
			}
			else
			{
				
				$body = $body;
				$obj->sendNotificationA($deviceTokenMod->device_token,$title,$body,$page);
			}
			/*
			*update Notification Status
			*/
			
		}
		else
		{
			
		}
		return 0;
}

public function sendNotificationNoSubmissionsMashreq()
{
	$mashreqAgents = Employee_details::where("dept_id",36)->where("job_function",2)->where("offline_status",1)->get();
	/* echo "<pre>";
	print_r($mashreqAgents);
	exit; */
	foreach($mashreqAgents as $agents)
	{
		
		 $threeSetDate = date("Y-m-d",strtotime("-3 days"));
	
		$countSubmissions = DepartmentFormEntry::where("submission_date","<=",$threeSetDate)->where("emp_id",$agents->emp_id)->get()->count();
		if($countSubmissions == 0)
		{
			$empId = $agents->emp_id;
			$deviceTokenMod = DeviceTokenFrontend::where("emp_id",$empId)->first();
		
				if($deviceTokenMod != '')
				{
					
					$deviceType = $deviceTokenMod->device_type;
					
					if($deviceType == 'IOS')
					{
						
						
						//$obj->send_ISO_notification($deviceTokenMod->device_token,"No Submission in more than 3 days","Don’t Fall Behind! It’s been 3 days since your last submission. Let’s change that—submit today! Keep The Sales Coming! We haven’t seen a sale in a while. Submit one now and stay ahead. Let’s Get Moving! It’s been a few days—time to lock in that next sale!","SubmissionList");
					}
					else
					{
						echo "ddd";
						exit;
						
						
						$obj->sendNotificationA($deviceTokenMod->device_token,"No Submission in more than 3 days","Don’t Fall Behind! It’s been 3 days since your last submission. Let’s change that—submit today! Keep The Sales Coming! We haven’t seen a sale in a while. Submit one now and stay ahead. Let’s Get Moving! It’s been a few days—time to lock in that next sale!","SubmissionList");
					}
					/*
					*update Notification Status
					*/
					
				}
		}
	}
	echo "done";
	exit;
}
}
