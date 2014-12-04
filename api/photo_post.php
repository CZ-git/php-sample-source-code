<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_REQUEST);

$isFileUploaded = true;

$feed = array(
	array(
		"error" => "0",
	)
);

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
 		
 		$fs_limit = 5 * 1024 * 1024;	// 5MB of Maximum
		if($fs_limit < $_FILES["image"]["size"]) {
		
			$feed[0]["error"] = "7";
			
		}  else {
			
			$data["type"] = get_app_tab_type($conn, $data["tab_id"]);
			
			$sql = "INSERT INTO app_user_photos (app_id, tab_id, detail_type, detail_id, caption, created)
			        values ('$app_id', '$data[tab_id]', '$data[type]', '$data[id]', '$data[caption]', '".date("Y-m-d H:i:s")."')";
			$res = mysql_query($sql, $conn);
			$idv = mysql_insert_id($conn);

			$dir = findUploadDirectory($app_id) . "/user_photos/" . $data["type"] . "/" . $data["id"];
			createDirectory($dir);	
			move_uploaded_file($_FILES["image"]["tmp_name"], "$dir/photo_$idv.$real_ext");

			if(!file_exists("$dir/photo_$idv.$real_ext")) {
				$feed[0]["etc"] = array(
					"sql" => $sql, 
					"file" => $_FILES["image"],
					"last_error" => error_get_last (),
				);
			}

			chmod("$dir/photo_$idv.$real_ext", 0664);

			
			
			// Lets update extension field.
			$sql = "UPDATE app_user_photos SET ext = '$real_ext' WHERE id= '$idv'";
			mysql_query($sql, $conn);
			
			
		}
 	} else {
 		$feed[0]["error"] = "9"; // Unknown type
 	}
  
} else {
	$feed[0]["error"] = "4"; // No data uploaded
}

echo json_encode($feed);