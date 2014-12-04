<?php
$optin = false; //yes, send optin emails
$up_exist = true; // yes, update currently subscribed users
$replace_int = false; // no, add interest, don't replace


error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

/*
if(strtolower($data["app_code"]) == "excelcredit") {
	$nv["type"] = "os";
}
*/


if (is_array($nv)) {
	
		$type = $nv["type"];
		if($type == "mc") {	// If it is for MailChimp?
			require_once("MCAPI/MCAPI.class.php");
			$api = new MCAPI($nv["mc"]["apikey"]);
					
			$batch = array();
			foreach($emails AS $qry ) {
				if ( !empty($qry["name"]) ) {
					$name = explode(" ", $qry["name"], 2);
					$batch[] = array('EMAIL'=>$qry["email"], 'FNAME'=>$name[0], 'LNAME'=>$name[1]);
				}
				else {
					$batch[] = array('EMAIL'=>$qry["email"]);
				}
			}
				
			$vals = $api->listBatchSubscribe($nv['mc']['listid'], $batch, $optin, $up_exist, $replace_int);
			
			if ($api->errorCode){
			  $mc_results = "MailChimp Subscribe Failed!\n\n";
				$mc_results .= "Code: ".$api->errorCode."\n";
				$mc_results .= "Message: ".$api->errorMessage."\n";
			} 
			else {
				$mc_results = "MailChimp Subscribe Success!\n\n";
				$mc_results .= "Emails Added:   ".$vals['add_count']."\n";
				$mc_results .= "Emails Updated: ".$vals['update_count']."\n";
				$mc_results .= "Emails with Errors:  ".$vals['error_count']."\n";
				foreach($vals['errors'] as $val){
					$mc_results .= $val['email_address']." failed\n";
					$mc_results .= "Code: ".$val['code']."\n";
					$mc_results .= "Message: ".$val['message']."\n";
				}
			}
		} else if ($type == "ic") {	// If it is for iContact?
			require_once("iCAPI/icontact_api.php");
			
			$batch = array();
			foreach($emails AS $qry ) {
				if ( !empty($qry["name"]) ) {
					$name = explode(" ", $qry["name"], 2);
					$batch[] = array('email'=>$qry["email"], 'firstName'=>$name[0], 'lastName'=>$name[1]);
				}
				else {
					$batch[] = array('email'=>$qry["email"]);
				}
			}
			
			$icontact = new iContact($nv['ic']['apiid'], $nv['ic']['apiuser'], $nv['ic']['apipassword']);
			$result = $icontact->request('/a/'.$nv['ic']['accountid'].'/c/'.$nv['ic']['folderid'].'/contacts/', 'POST', json_encode($batch));

			if($result->code == '200') {
				$suc = json_decode($result->data);
				$suc = $suc->contacts;
				if(is_array($suc)) {
					$subs = array();
					foreach($suc AS $value) {
						$subs[] = array(
							'contactId' => $value->contactId,
							'listId' => $nv['ic']['listid'],
							'status' => 'normal'
						);
					} 
					$countv = count($subs);
					$result = $icontact->request('/a/'.$nv['ic']['accountid'].'/c/'.$nv['ic']['folderid'].'/subscriptions/', 'POST', json_encode($subs));

					if($result->code == '200') {
						$mc_results = 'iContact - Uploading and subscribing succeded!'."\n";
						$mc_results .= $countv.' contacts have been added(or updated) and subscribed to the list.';
					} else if ($result->data != ''){
						$mc_results = 'iContact - Uploading succeded but subscribing failed!';
						$mc_results .= "\n";
						$errorobj = json_decode($result->data);
						$mc_results .= implode("\n", $errorobj->errors);
					} else {
						$mc_results = 'iContact - Uploading succeded but subscribing failed!';
					} 							
				} 
				
			} else if ($result->data != ''){
				$mc_results = 'iContact - Uploading contacts failed!';
				$mc_results .= "\n";
				$errorobj = json_decode($result->data);
				$mc_results .= implode("\n", $errorobj->errors);
			} else {
				$mc_results = 'iContact - Uploading contacts failed!';
			}
		} else if ($type == "cc") {	// If it is for ConstantContact?
			require_once("CtCt/ctctWrapper.php");
			
			$batchstr = 'activityType=SV_ADD&data=';
			$vstr = 'Email Address,First Name,Last Name';
			foreach($emails AS $qry ) {
				$fname = '';
				$lname = '';
				$email = $qry["email"];
				if ( !empty($qry["name"]) ) {
					$name = explode(" ", $qry["name"], 2);
					$fname = $name[0];
					$lname = $name[1];
				}
				$vstr .= "\n".$email.', '.$fname.', '.$lname;
			}
			$batchstr .= urlencode($vstr);
			$batchstr .='&lists='.urlencode($nv['cc']['listid']);
			
			$cutil = new Utility($nv['cc']['apikey'], $nv['cc']['apiuser'], $nv['cc']['apipassword']);
			$act = new ActivitiesCollection($cutil);
			$result = $act->bulkUrlEncoded($batchstr);
			
			if($result['info']['http_code'] == '201') {
				$mc_results = 'ConstantContact - Uploading and subscribing succeded!'."\n";
				$mc_results .= "\n";
				$mc_results .= 'Please check the [Activity] information from your ConstantContacts account to check more details.';
			} else {
				$mc_results = 'ConstantContact - Uploading contacts failed!';
				$mc_results .= "\n";
				$mc_results .= $result['xml'];
			}
		} else if ($type == "cm") {	// If it is for CampaignMonitor?
			require_once 'CmpMntr/csrest_subscribers.php';
			$batch = array();
			foreach($emails AS $qry ) {
				$batch[] = array(
				    'EmailAddress' => $qry["email"],
				    'Name' => $qry["name"],
				    'CustomFields' => array()
				);
			}

			$wrap = new CS_REST_Subscribers($nv['cm']['listid'], $nv['cc']['apikey']);
			$result = $wrap->import($batch, true);
			if($result->was_successful()) {
			    $mc_results = 'CampaginMonitor - Uploading and subscribing succeded!'."\n";
				$mc_results .= "\n";
				$mc_results .= "The results are following:"."\n";
				$mc_results .= "<ul><li>Total unique emails submitted: ".$result->response->TotalUniqueEmailsSubmitted;
				$mc_results .= "</li><li>Total existing subscribers: ".$result->response->TotalExistingSubscribers;
				$mc_results .= "</li><li>Total new subscribers: ".$result->response->TotalNewSubscribers;
				$mc_results .= "</li></ul>";  
			} else {
				$mc_results = '';
				//$mc_results = 'CampaginMonitor - Uploading contacts failed!';
				//$mc_results .= "\n";
				
				$mc_results .= "<ul><li>Total unique emails submitted: ".$result->response->ResultData->TotalUniqueEmailsSubmitted;
				$mc_results .= "</li><li>Total existing subscribers: ".$result->response->ResultData->TotalExistingSubscribers;
				$mc_results .= "</li><li>Total new subscribers: ".$result->response->ResultData->TotalNewSubscribers;
				$mc_results .= "</li></ul>";
			    
			    $mc_results .= $result->response->Message;
				$mc_results .= "\n";
				
				$fd = $result->response->ResultData->FailureDetails;
				if(is_array($fd) && isset($fd)) {
					$mc_results .= "<ul>";
					foreach($fd AS $v) {
						$mc_results .= "<li>Email address: ".$v->EmailAddress."   {".$v->Message."}";
					}
					$mc_results .= "</ul>";	
				}
			}
		} else if ($type == "gr") {	// If it is for GetResponse?
			require_once 'GR/jsonRPCClient.php';
			
			$api_url = 'http://api2.getresponse.com';
			$client = new jsonRPCClient($api_url);
			$client->setDebug(true);
			$failed = array();
			
			foreach($emails AS $qry ) {
				try {
					if(isset($qry["email"]) && !empty($qry["email"])) {
					    $result = $client->add_contact(
					        $nv['gr']['apikey'],
					        array (
					            'campaign'  => $nv['gr']['listid'],
					            'name'      => $qry["name"],
					            'email'     => $qry["email"]
					        )
					    );
					}
				}
				catch (Exception $e) {
				    # check for communication and response errors
				    # implement handling if needed
				    $failed[] = array('email'=>$qry["email"], 'error'=>$e->getMessage());
				}
				
			}
			
			if(count($failed) == 0) {
				$mc_results = 'GetResponse - Uploading and subscribing succeded!'."\n";
			} else {
				$mc_results = 'GetResponse - Some of Uploading and subscribing failed!'."\n";
				$mc_results .= "\n";
				$mc_results .= '<table border="0"><tr><th>Failed Email</th><th>Reason</th></tr>';
				foreach($failed AS $value) {
					$mc_results .= '<tr>';
					$mc_results .= '<td>'.$value['email'].'</td>';
					$mc_results .= '<td>'.$value['error'].'</td>';
					$mc_results .= '</tr>';	
				} 
				$mc_results .= '</table>';  
			}
		} else if ($type == "em") {	// If it is for Emma?
			require_once "MyEmma/myEmma.php";
			$client = new MyEmma($nv["em"]["accountid"], $nv["em"]["apiuser"], $nv["em"]["apipassword"]);
			
			foreach($emails AS $qry ) {
				if ( !empty($qry["name"]) ) {
					$name = explode(" ", $qry["name"], 2);
					$batch[] = array('email'=>$qry["email"], 'first_name'=>$name[0], 'last_name'=>$name[1]);
				}
				else {
					$batch[] = array('email'=>$qry["email"]);
				}
			}
			$result = $client->subscribeEmails($batch, intval($nv["em"]["listid"]));
			if(isset($result["data"]->import_id)) {
				$mc_results = 'MyEmma - Uploading and subscribing succeded!'."\n";
			} else {
				$mc_results = 'We are sorry! '."\n".'MyEmma - Uploading and subscribing failed!'."\n";
			}
			
		} else if ($type == "os") {	// If it is for 1-Shopping?
			require_once "1shopping/1shopping.php";
			$client = new OneShopping($nv["os"]["merchantid"], $nv["os"]["responderid"]);
			
			$failedEmails = array();
			foreach($emails AS $qry ) {
				if(empty($qry["email"])) continue;
				$batch = array(
					"Name" => $qry["name"], 
					"Email1" => $qry["email"],
					"cmdSubmit" => "Submit",
					"requiredfields" => "Email1",
					"visiblefields" => "Name,Email1",
					"allowmulti" => "0",
					"defaultar" => "677190",
					"copyarresponse" => "1",
					"ARThankyouURL" => "www.1automationwiz.com/app/thankyou.asp?ID=55994",
					"merchantid" => "55994"
				);
				if(!$client->httpRequest($batch)) {
					$failedEmails[] = $qry["email"];
				};
				
			}
			if(count($failedEmails) > 0) {
				$mc_results = '1-ShoppingCart - Uploading and subscribing failed for following emails:'."\n";
				$mc_results .='<ul>';
				foreach($failedEmails AS $k => $emv) {
					$mc_results .='<li>'.$emv.'</li>';
				}
				$mc_results .='</ul>';
			} else {
				$mc_results = '1-ShoppingCart - Uploading and subscribing succeded!'."\n";
			}
		} else if ($type == "sp") {	// If it is for 1-Shopping?
			include_once "Mail.php";
			include_once "Mail/mime.php";
			
			$failedEmails = array();
			foreach($emails AS $qry ) {
				$crlf = "\n";
				
				$hdrs = array(
					'From'    => $qry["email"],
					//'CC' => "support@appsomen.com",
					'Subject' => 'Subscribe email',
					'Content-Type' => 'text/html; charset=UTF-8',
					'Content-Transfer-Encoding' => '8bit',
					'Message-ID' => '<' . uniqid() . 'support@appsomen.com>',
					'MIME-Version' => '1.0',
				);
				
				$mime = new Mail_mime($crlf);
				
				$mime->setTXTBody("Subscription request. Thanks.");
				$mime->setHTMLBody("Subscription request. Thanks.");
				$mimeparams = array(
					'text_encoding' => "8bit",
					'text_charset' => "UTF-8",
					'html_charset' => "UTF-8",
					'head_charset' => "UTF-8",
				);

				$body = $mime->get($mimeparams);
				$hdrs = $mime->headers($hdrs);

				$body = $mime->get();
				$hdrs = $mime->headers($hdrs);
				
				$mail =& Mail::factory('mail');
				if(!$mail->send($nv["sp"]["email"], $hdrs, $body)) {
					$failedEmails[] = $qry["email"];
				};
				
			}
			if(count($failedEmails) > 0) {
				$mc_results = '1-ShoppingCart - Uploading and subscribing failed for following emails:'."\n";
				$mc_results .='<ul>';
				foreach($failedEmails AS $k => $emv) {
					$mc_results .='<li>'.$emv.'</li>';
				}
				$mc_results .='</ul>';
			} else {
				$mc_results = '1-ShoppingCart - Uploading and subscribing succeded!'."\n";
			}
			
		}  
} else {
	$mc_results = "Error: Integration information must be specified first.";
}
?>