<?php
include_once("../php_includes/check_login_status.php");
include_once("../php_includes/dir_hash.php");
if($user_ok != true || $log_username == "") {
	exit();
}
?><?php
if (isset($_FILES["avatar"]["name"]) && $_FILES["avatar"]["tmp_name"] != ""){
	$fileName = $_FILES["avatar"]["name"];
    $fileTmpLoc = $_FILES["avatar"]["tmp_name"];
	$fileType = $_FILES["avatar"]["type"];
	$fileSize = $_FILES["avatar"]["size"];
	$fileErrorMsg = $_FILES["avatar"]["error"];
	$kaboom = explode(".", $fileName);
	$fileExt = end($kaboom);
	list($width, $height) = getimagesize($fileTmpLoc);
	if($width < 10 || $height < 10){
		header("location: ../message.php?msg=ERROR: That image has no dimensions");
        exit();
	}
	$db_file_name = rand(100000000000,999999999999).".".$fileExt;
	if($fileSize > 5048576) {
		header("location: ../message.php?msg=ERROR: Your image file was larger than 5mb");
		exit();
	} else if (!preg_match("/\.(gif|jpg|png)$/i", $fileName) ) {
		header("location: ../message.php?msg=ERROR: Your image file was not jpg, gif or png type");
		exit();
	} else if ($fileErrorMsg == 1) {
		header("location: ../message.php?msg=ERROR: An unknown error occurred");
		exit();
	}
	$sql = "SELECT avatar FROM users WHERE username='$log_username' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_row($query);
	$avatar = $row[0];
	if($avatar != ""){
		$picurl = "../user/$log_username/$avatar";
	    if (file_exists($picurl)) { unlink($picurl); }
	}
	$dir_hash = dir_encrypt($log_username);
	$moveResult = move_uploaded_file($fileTmpLoc, "../user/$dir_hash/$db_file_name");
	if ($moveResult != true) {
		header("location: ../message.php?msg=ERROR: File upload failed");
		exit();
	}
	include_once("../php_includes/image_resize.php");
	$target_file = "../user/$dir_hash/$db_file_name";
	$resized_file = "../user/$dir_hash/$db_file_name";
	$wmax = 200;
	$hmax = 300;
	img_resize($target_file, $resized_file, $wmax, $hmax, $fileExt);
	$sql = "UPDATE users SET avatar='$db_file_name' WHERE username='$log_username' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	mysqli_close($db_conx);
	header("location: ../user.php?u=$log_username");
	exit();
}
?>

<?php
if (isset($_FILES["stPic"]["name"]) && $_FILES["stPic"]["tmp_name"] != ""){
	$fileName = $_FILES["stPic"]["name"];
	$fileTmpLoc = $_FILES["stPic"]["tmp_name"];
	$fileType = $_FILES["stPic"]["type"];
	$fileSize = $_FILES["stPic"]["size"];
	$fileErrorMsg = $_FILES["stPic"]["error"];	
	$kaboom = explode(".", $fileName);
	$fileExt = end($kaboom);
	list($width, $height) = getimagesize($fileTmpLoc);
	if($width < 10 || $height < 10){
		echo"Image is too small|fail";
        exit();	
	}
	$time = time();
	$db_file_name = $log_username.time().".".$fileExt;
	if($fileSize > 1048576) {
		echo "Your image file was larger than 1mb|fail";
		exit();	
	} else if (!preg_match("/.(gif|jpg|png)$/i", $fileName) ) {
		echo "Your image file was not jpg, gif or png type|fail";
		exit();
	} else if ($fileErrorMsg == 1) {
		echo "An unknown error occurred|fail";
		exit();
	}	
	if(move_uploaded_file($fileTmpLoc, "../tempUploads/$db_file_name")){
    	echo "upload_complete|$db_file_name";
	} else {
    	echo "move_uploaded_file function failed";
	}
}
?>