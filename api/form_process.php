<?php
	$app_name = $_POST['app_name'];
	$identifier = $_POST['identifier'];
	$version = $_POST['version'];
	$uploaddir = "../../uploads/form/";
	
	$upload_provisioning_profile = $uploaddir . basename($_FILES['provisioning_profile']['name']);
	$upload_pp_info = pathinfo($_FILES['provisioning_profile']['name']);
	$i = 0;
	while (file_exists($upload_provisioning_profile)){
		$i++;
		$upload_provisioning_profile = $uploaddir . $upload_pp_info['filename'] . $i . $upload_pp_info['extension'];
	}
	if (move_uploaded_file($_FILES['provisioning_profile']['tmp_name'], $upload_provisioning_profile)) {
		$provisioning_profile = $upload_provisioning_profile;
	} else {
		$provisioning_profile =  "Provisioning Profile upload fail!";
	}
	
	$upload_image_files = $uploaddir . basename($_FILES['image_files']['name']);
	$upload_if_info = pathinfo($_FILES['image_files']['name']);
	$i = 0;
	while (file_exists($upload_image_files)){
		$i++;
		$upload_image_files = $uploaddir . $upload_if_info['filename'] . $i . $upload_if_info['extension'];
	}
	if (move_uploaded_file($_FILES['image_files']['tmp_name'], $upload_image_files)) {
		$image_files = $upload_image_files;
	} else {
		$image_files =  "Image file upload fail!";
	}
	$developer_name = $_POST['developer_name'];
	
?>
<html>
<head>
<title>Form for Ady</title>
<style>
	.ady_item{margin:0 auto; width:960px;}
	.item{clear:both;width:100%;}
	.item label{width:30%;float:left;}
	.item div{width:70%;float:left;}
	.back_item{clear:both;width:100%;text-align:center;}
</style>
</head>
<body>
	<div class="ady_item">
		<div class="item">
			<label for="app_name">Application Name</label>
			<div><?php echo $app_name;?></div>
		</div>
		<div class="item">
			<label for="identifier">Identifier</label>
			<div><?php echo $identifier;?></div>
		</div>
		<div class="item">
			<label for="version">Version</label>
			<div><?php echo $version;?></div>
		</div>
		<div class="item">
			<label for="provisioning_profile">Provisioning Profile</label>
			<div><?php echo $provisioning_profile;?></div>
		</div>
		<div class="item">
			<label for="image_files">Image files</label>
			<div><?php echo $image_files;?></div>
		</div>
		<div class="item">
			<label for="developer_name">Developer Name</label>
			<div><?php echo $developer_name;?></div>
		</div>
		<div class="back_item">
			<a href="form.php">Back</a>
		</div>
	</div>
</body>
</html>
