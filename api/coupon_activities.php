<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$and = '';
if ( $data["id"] ) {
	$and .= " and a.detail_id = '$data[id]'";
}

$and .= " and a.detail_type = '10'";

if ( $data['app_code'] ) {
	$appsql = "SELECT id FROM apps WHERE code='" . $data['app_code'] . "'";
	$appres = mysql_query($appsql, $conn);
	if ( mysql_num_rows( $appres ) ) {
		$app_id = mysql_result( $appres, 0, 0 );
		$and .= " and a.app_id = '$app_id'";
	}
}

$sql = "SELECT * FROM app_user_comments a WHERE 1=1 $and ORDER BY a.id DESC";

if (!$data["count"])
    $data["count"] = 20;

include_once "fetch_limit.php";
$limit = $SQL_LIMIT;

if ($data["show_all"])
    $limit = "";

$sql .= " $limit ";

$res = mysql_query($sql, $conn);

if ( mysql_num_rows( $res ) ) {

	$load_google_api = false;
	$twitter_access_token = '';

	while ( $act = mysql_fetch_array($res) ) {

		$image_url = '';

		if ( $act["avatar"] ) {
			$image_url = $act["avatar"];
		} else {
		
			if ( $act["user_type"] == "1" ) {
				$image_url = "http://graph.facebook.com/$act[user_id]/picture";
			} else if ($act["user_type"] == "2") {
				if ( !$twitter_access_token ) {
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/oauth2/token');
					curl_setopt($ch,CURLOPT_POST, true);
					$postdata = array();
					$postdata['grant_type'] = "client_credentials";
					curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);
					$consumerKey = "kafFaEXqtvogJgJj9VFzA";
					$consumerSecret = "sA8oXmcit8Kg9HRB3Act6B0rZ8VKurDVnf6X8g2Vk";
					curl_setopt($ch,CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
					curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
					$result = curl_exec($ch);
					curl_close($ch);
					$result = json_decode($result);
					
					if ( $result->access_token ) {
						$twitter_access_token = $result->access_token;
					}
				}
				
				if ( $twitter_access_token ) {
					$ch = curl_init();
					if ( is_numeric( $act['user_id'] ) )
						curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/1.1/users/show.json?user_id=' . $act['user_id']);
					else
						curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/1.1/users/show.json?screen_name=' . $act['user_id']);
						
					curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: Bearer ' . $twitter_access_token));
					curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
					$result_pro = curl_exec($ch);
					curl_close($ch);
					$result_pro = json_decode($result_pro);
					
					if ( $result_pro->profile_image_url )
						$image_url = $result_pro->profile_image_url;
				}
			} else if ($act["user_type"] == "3") {

				if ( !$load_google_api ) {
					define('REDIRECT_URL', 'http://sandbox.appsomen.com/iphone/1.1/coupon_activities.php');
					define('CLIENT_ID', '778648179325-kppdsdn74m37ubrd8nm6ajhlumj6ln7m.apps.googleusercontent.com');
					define('CLIENT_SECRET', '0pST3ktWh-Ep_9fje-u39Ydk');
					define('THIS_PAGE', 'coupon_activities.php');
					define('APP_NAME', 'AppsOmen Mobile App');

					require_once 'google2/Google_Client.php';
					require_once 'google2/storage.php';
					require_once 'google2/authHelper.php';
					require_once 'google2/contrib/Google_PlusService.php';

					// Build a new client object to work with authorization.
					$client = new Google_Client();
					$client->setClientId(CLIENT_ID);
					$client->setClientSecret(CLIENT_SECRET);
					$client->setRedirectUri(REDIRECT_URL);
					$client->setApplicationName(APP_NAME);
					$client->setScopes( array('https://www.googleapis.com/auth/plus.login') );
					$client->setAccessType("offline");
					$client->setUseObjects(true);
					$storage = new apiSessionStorage();

					$authHelper = new AuthHelper($client, $storage, THIS_PAGE);

					$accessToken = '{"access_token":"ya29.1.AADtN_WQiPFZ7_rHc2NQ_k5aNcCqYVD2w0G8GUsCpDvjEV9bEJq4Y5bLfKA3IapaHw","token_type":"Bearer","expires_in":3600,"id_token":"eyJhbGciOiJSUzI1NiIsImtpZCI6IjdmYzA4Mjc0YTdiYTA3Y2ZkOWMyZTUxNzA2YTRjYmJmZmU0MGUxYjkifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwiZW1haWwiOiJibXc3NDhAZ21haWwuY29tIiwidG9rZW5faGFzaCI6InIzQVVfVzhwVHVRc0FSdks4N1I5dlEiLCJhdF9oYXNoIjoicjNBVV9XOHBUdVFzQVJ2Szg3Ujl2USIsInZlcmlmaWVkX2VtYWlsIjoidHJ1ZSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImF1ZCI6Ijc3ODY0ODE3OTMyNS1rcHBkc2RuNzRtMzd1YnJkOG5tNmFqaGx1bWo2bG43bS5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImNpZCI6Ijc3ODY0ODE3OTMyNS1rcHBkc2RuNzRtMzd1YnJkOG5tNmFqaGx1bWo2bG43bS5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImF6cCI6Ijc3ODY0ODE3OTMyNS1rcHBkc2RuNzRtMzd1YnJkOG5tNmFqaGx1bWo2bG43bS5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImlkIjoiMTA0MDM0ODU0MTgxMjc5NTg2MzI2Iiwic3ViIjoiMTA0MDM0ODU0MTgxMjc5NTg2MzI2IiwiaWF0IjoxMzk0ODA0Njc2LCJleHAiOjEzOTQ4MDg1NzZ9.oV7BOtzTi7AQRiyvERX43nulpp_njBfOT4vjDs9vPySpGARbaSjK44-Xyocrf169ooHJCWdKmzdZ8De4DEE8FVtyCnb2ZSYe35rL6I7rgq1JRbC4IR9WJZDom0uAELQqgWKym6CeUuUHK-cLR7QsbvsHad2Wpb80aSiGTPKFDpI","refresh_token":"1\/aaslTqFME8ARZjXHUS9uqmfKjrehHu5C62-BdpaXnQ8","created":1394804976}';
					$client->setAccessToken( $accessToken );
					
					$gplus = new Google_PlusService($client); 

					$load_google_api = true;
				}
				
				if ( $gplus && $client && $client->getAccessToken() ) {
					if ( is_numeric($act['user_id']) ) {
                        try {
						    $ginfo = $gplus->people->get($act['user_id']);
						    if ( $ginfo ) {						
							    $image_url = $ginfo->image->url;
						    }   
                        } catch (Google_ServiceException $e) {                        
                        } catch (Google_IOException $e) {                        
                        } catch (Google_Exception $e) {                        
                        }                        
					}
				}

			}
		}

		$feed[] = array(
			'action' => intval($act['comment']),
			'name' => $act['name'],
			'avatar_url' => $image_url,
			'time' => strtotime( $act['created'] ),
			'sequence' => intval($act['sequence'])
		);
	}

} else {
	$feed = array(
	);
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

die();

?>