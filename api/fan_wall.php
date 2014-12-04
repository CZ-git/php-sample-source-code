<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$image = "";
$button = "";

$image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

if ($data["tab_id"])
  $and .= "and tab_id = '$data[tab_id]' ";
if ($data["yt_id"])
  $and .= "and youtube_id = '$data[yt_id]' ";
if ($data["parent_id"]) 
  $and .= "and parent_id = '$data[parent_id]' ";
else
  $and .= "and parent_id = 0 ";

if (!$data["n"])
  $data["n"] = 20;

$limit = "";
if ($data["n"] > 0) {
  $limit = "limit $data[n]";
  if ($data["s"] > 0)
    $limit .= " offset $data[s]";
}

if ($data["show_all"])
  $limit = "";

$sql = "select *
        from fan_wall_comments
        where app_id = '$app_id'
        $and
        order by created desc
        $limit";
if($data["debug"] == "1") {
    echo $sql;
    exit;
}
$res = mysql_query($sql, $conn);

$feed = array();

$num=0;

$load_google_api = false;
$twitter_access_token = '';

while ($l = mysql_fetch_array($res)) {

    $num++;
    $image_url = '';
    
    if ( $l["avatar"] ) {
        $image_url = $l["avatar"];
    } else {
        if ($l["facebook_id"]) {
            $image_url = "http://graph.facebook.com/$l[facebook_id]/picture";  
        } else if ($l["twitter_id"]) {
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
                if ( is_numeric( $l['twitter_id'] ) )
                    curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/1.1/users/show.json?user_id=' . $l['twitter_id']);
                else
                    curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/1.1/users/show.json?screen_name=' . $l['twitter_id']);
                    
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
        } else if ($l["googleplus_id"]) {

            if ( !$load_google_api ) {
                define('REDIRECT_URL', 'http://www.appsomen.com/iphone/1.1/fan_wall.php');
                define('CLIENT_ID', '778648179325-kppdsdn74m37ubrd8nm6ajhlumj6ln7m.apps.googleusercontent.com');
                define('CLIENT_SECRET', '0pST3ktWh-Ep_9fje-u39Ydk');
                define('THIS_PAGE', 'fan_wall.php');
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
                if ( is_numeric($l["googleplus_id"]) ) {
                    try {                    
                        $ginfo = $gplus->people->get($l["googleplus_id"]);
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

    if (!$qry["parent_id"]) {
        $sql = "select count(*) from fan_wall_comments
              where parent_id = '$l[id]'";
        $res2 = mysql_query($sql, $conn);
        $replies = mysql_result($res2, 0, 0);
    } else
        $replies = 0;    
	
	$comments[] = array(
		"id" => $l["id"],
		"parent_id" => $l["parent_id"],
		"time" => strtotime( $l["created"] ),
		"comment" => $l["comment"],
		"name" => $l["name"],
		"replies" => $replies,
		"image" => $image_url
	);
	
}

if (!count($comments)) {
    $comments = array();
}

// Now...
// If yt_id is specified, I should check fan wall tab and get the background image from it.
if($data["yt_id"] != "") {
    $image = get_fanwall_background($conn, $app_id, $data);
}

$feed[] = array(
    "comments" => $comments,
    "image" => $image
);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>