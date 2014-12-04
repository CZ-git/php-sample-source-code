<?php

include_once "dbconnect.inc";
include_once "app.inc";

$feed = array(
	array(
		"error" => "0",
		//"image_error" => "0",
	)
);

$data = make_data_safe($_REQUEST);

$data["type"] = get_app_tab_type($conn, $data["tab_id"]);

// If it is tab itself, then detail id is really useless
if($data["type"] == "0") $data["id"] = "0"; // type = "0" means the tab itself... maybe comment tab 

$stuff = "comment986" . $data["user_id"] . "bizapps" . $data["user_type"];
$hash = md5($stuff);

if (strtoupper($hash) != strtoupper($data["hash"])) {

	$feed[0]["error"] = "9";
	echo json_encode($feed);

	exit;
}

$required_fields = array("tab_id", "name", "comment");
foreach($required_fields as $field) {
  if (!$data[$field]) {
	
	$feed[0]["error"] = "7";
	$feed[0]["data"] = $_REQUEST;
	echo json_encode($feed);

	exit;

  }
}

if ( !$data['parent_id'] )
	$data['parent_id'] = 0;

$sql = "INSERT INTO app_user_comments (app_id, tab_id, parent_id, detail_type, detail_id, user_id, user_type, name, comment, created, latitude, longitude, ext, avatar, timezone)
		values ('".$app_id."', '".$data[tab_id]."', '".$data[parent_id]."', '".$data[type]."', '".$data[id]."', '".$data[user_id]."', '".$data[user_type]."', '".$data[name]."', '".$data[comment]."', 
				'".gmdate("Y-m-d H:i:s")."', '" . doubleval($data["latitude"]) . "', '" . doubleval($data["longitude"]) . "', '', '".$data[avatar]."', '".$data[timezone]."')";
$res = mysql_query($sql, $conn);
if (!$res) {
	
	$feed[0]["error"] = "9";
	echo json_encode($feed);

	exit;

} else {
	
	$idv = mysql_insert_id($conn); 
	// Now lets upload a file
	$isFileUploaded = false;
	if (is_array($_FILES["image"]) && $_FILES["image"]["size"] != "0") {
		
		$isFileUploaded = true;
		
		ob_start();

		$arg = escapeshellarg( $_FILES["image"]["tmp_name"] );
		 if (stristr(system( "file $arg",$type),'PNG')) {
			$real_ext='png';
		} else if(stristr(system( "file $arg",$type),'JPEG')) {
			 $real_ext='jpg';
		 } else if(stristr(system( "file $arg",$type),'GIF')) {
			 $real_ext='gif';
		 }
		 else if(stristr(system( "file $arg",$type),'bitmap')) {
			 $real_ext='bmp';
		 } else {
			 $real_ext='jpg';
			 
			 $isFileUploaded = false;
			 
		 }

		ob_end_clean();
		 
		 
		 if($isFileUploaded) {
				 
			 $fs_limit = 5 * 1024 * 1024;    // 5MB of Maximum
			if($fs_limit < $_FILES["image"]["size"]) {
				// Too Large photo, so stop uploading....

				$feed[0]["image_error"] = "7";

			}  else {
				$dir = findUploadDirectory($app_id) . "/user_photos/0/" . $data["tab_id"];
				createDirectory($dir);    
				move_uploaded_file($_FILES["image"]["tmp_name"], "$dir/photo_$idv.$real_ext");
				chmod("$dir/photo_$idv.$real_ext", 0664);

				// Lets update extension field.
				$sql = "UPDATE app_user_comments SET ext = '$real_ext' WHERE id= '$idv'";
				mysql_query($sql, $conn);
				
				// Done!!!
			}
		 } else {
			 // Unknown image type
			$feed[0]["image_error"] = "9";
		 }
	  
	} else {
		//echo '[{"error":"4"}]'; // No data uploaded
		$feed[0]["image_error"] = "4";
	}
	
	echo json_encode($feed);

}

?>