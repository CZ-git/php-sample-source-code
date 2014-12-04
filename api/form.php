<html>
<head>
<title>Form for Ady</title>
<style>
	.ady_form{margin:0 auto; width:960px;}
	.form_item{clear:both;width:100%;}
	.form_item label{width:30%;float:left;}
	.form_item input{width:70%;float:left;}
	.div_submit{clear:both;text-align:center;}
</style>
</head>
<body>
	<div class="ady_form">
		<form id="ady_form" method="post" action="form_process.php" enctype="multipart/form-data">
			<div class="form_item">
				<label for="app_name">Application Name</label>
				<input type="text" id="app_name" name="app_name" value="" >
			</div>
			<div class="form_item">
				<label for="identifier">Identifier</label>
				<input type="text" id="identifier" name="identifier" value="" >
			</div>
			<div class="form_item">
				<label for="version">Version</label>
				<input type="text" id="version" name="version" value="" >
			</div>
			<div class="form_item">
				<label for="provisioning_profile">Provisioning Profile</label>
				<input type="file" id="provisioning_profile" name="provisioning_profile" value="" >
			</div>
			<div class="form_item">
				<label for="image_files">Image files</label>
				<input type="file" id="image_files" name="image_files" value="" >
			</div>
			<div class="form_item">
				<label for="developer_name">Developer Name</label>
				<input type="text" id="developer_name" name="developer_name" value="" >
			</div>
			<div class="div_submit">
				<input type="submit" id="ady_btn" name="ady_btn" value="Submit" />
			</div>
		</form>
	</div>
</body>
</html>
