<?php
include_once("../php_includes/check_login_status.php");
if($user_ok != true || $log_username == "") {
	exit();
}
?><?php
if (isset($_POST['action']) && $_POST['action'] == "status_post"){
	// Make sure post data is not empty
	if(strlen($_POST['data']) < 1 && $_POST['image'] == "na"){
		mysqli_close($db_conx);
	    echo "data_empty";
	    exit();
	}

	$image = preg_replace('#[^a-z0-9.]#i', '', $_POST['image']);
	if($image != "na"){
		$kb = explode(".", $image);
		$fileExt = end($kb);
		rename("../tempUploads/$image","../permUploads/$image");
		include_once("../php_includes/image_resize.php");
		$target_file = "../permUploads/$image";
		$resized_file = "../permUploads/$image";
		$wmax = 600;
		$hmax = 700;
		list($width,$height) = getimagesize($target_file);
		if($width > $wmax || $height > $hmax){
			img_resize($target_file,$resized_file,$wmax,$hmax,$fileExt);
		}
	}


	// Make sure type is either a or c
	if($_POST['type'] != "a" && $_POST['type'] != "c" && $_POST['type'] != "d"){
		mysqli_close($db_conx);
	    echo "type_unknown";
	    exit();
	}
	// Clean all of the $_POST vars that will interact with the database
	$type = preg_replace('#[^a-z]#', '', $_POST['type']);
	$account_id = preg_replace('#[^0-9]#i', '', $_POST['user']);
	// echo $account_id;
	// exit();
	$data = htmlentities($_POST['data']);
	$data = mysqli_real_escape_string($db_conx, $data);
	// Make sure account name exists (the profile being posted on)

	if($data == "||na||" && $image != "na"){
		$data = '<img class="img-responsive" src="permUploads/'.$image.'" />';
		//$data = '<img src="permUploads/'.$image.'" class="biggerStatusImage" />';
	} else if ($data != "||na||" && $image != "na"){
		$data = $data.'<br /><img class="img-responsive" src="permUploads/'.$image.'" />';
		//$data = $data.'<br /><img src="permUploads/'.$image.'" class="biggerStatusImage" />';
	}

	$sql = "SELECT COUNT(id),username FROM users WHERE id='$account_id' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_row($query);
	if($row[0] < 1){
		mysqli_close($db_conx);
		echo "account_no_exist";
		exit();
	} else {
		$account_name = $row[1];
	}
	// Insert the status post into the database now
	$author_id = (int)$log_id;
	$sql = "INSERT INTO status(account_id, author_id, type, data, postdate)
			VALUES($account_id,$author_id,'$type','$data',now())";
	$query = mysqli_query($db_conx, $sql);
	$id = mysqli_insert_id($db_conx);
	mysqli_query($db_conx, "UPDATE status SET osid='$id' WHERE id='$id' LIMIT 1");
	// Count posts of type "a" for the person posting and evaluate the count
	// $sql = "SELECT COUNT(id) FROM status WHERE author_id='$log_id' AND type='a'";
  //   $query = mysqli_query($db_conx, $sql);
	// $row = mysqli_fetch_row($query);
	// if ($row[0] > 99) { // If they have 10 or more posts of type a
	// 	// Delete their oldest post if you want a system that auto flushes the oldest
	// 	// (you can auto flush for post types c and b if you wish to also)
	// 	$sql = "SELECT id FROM status WHERE author_id='$log_id' AND type='a' ORDER BY id ASC LIMIT 1";
  //   	$query = mysqli_query($db_conx, $sql);
	// 	$row = mysqli_fetch_row($query);
	// 	$oldest = $row[0];
	// 	mysqli_query($db_conx, "DELETE FROM status WHERE osid='$oldest'");
	// }
	// Insert notifications to all friends of the post author
	$friends = array();
	$query = mysqli_query($db_conx, "SELECT user1_id FROM friends WHERE user2_id='$log_id' AND accepted='1'");
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) { array_push($friends, $row["user1_id"]); }
	$query = mysqli_query($db_conx, "SELECT user2_id FROM friends WHERE user1_id='$log_id' AND accepted='1'");
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) { array_push($friends, $row["user2_id"]); }
	for($i = 0; $i < count($friends); $i++){
		$friend = $friends[$i];
		$app = "Status Post";
		//$note = $log_username.' posted on: <br /><a href="user.php?u='.$account_name.'#status_'.$id.'">'.$account_name.'&#39;s Profile</a>';
		$note = 'posted here <br /><a href="user.php?u='.$account_name.'#status_'.$id.'">&#39;s Profile</a>';
		mysqli_query($db_conx, "INSERT INTO notifications(user_id, initiator_id, app, note, date_time) VALUES('$friend','$log_id','$app','$note',now())");
	}
	mysqli_close($db_conx);
	echo "post_ok|$id";
	exit();
}
?><?php
//action=status_reply&osid="+osid+"&user="+user+"&data="+data
if (isset($_POST['action']) && $_POST['action'] == "status_reply"){
	// Make sure data is not empty
	if(strlen($_POST['data']) < 1){
		mysqli_close($db_conx);
	    echo "data_empty";
	    exit();
	}
	// Clean the posted variables
	$osid = preg_replace('#[^0-9]#', '', $_POST['sid']);
	$account_id = preg_replace('#[^a-z0-9]#i', '', $_POST['user']);
	$data = htmlentities($_POST['data']);
	$data = mysqli_real_escape_string($db_conx, $data);
	// Make sure account name exists (the profile being posted on)
	$sql = "SELECT COUNT(id) FROM users WHERE id='$account_id' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_row($query);
	if($row[0] < 1){
		mysqli_close($db_conx);
		echo "$account_no_exist";
		exit();
	}
	// Insert the status reply post into the database now
	$sql = "INSERT INTO status(osid, account_id, author_id, type, data, postdate)
	        VALUES('$osid','$account_id','$log_id','b','$data',now())";
	$query = mysqli_query($db_conx, $sql);
	$id = mysqli_insert_id($db_conx);
	// Insert notifications for everybody in the conversation except this author
	// $sql = "SELECT author FROM status WHERE osid='$osid' AND author!='$log_username' GROUP BY author";
	// $query = mysqli_query($db_conx, $sql);
	// while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
	// 	$participant = $row["author"];
	// 	$app = "Status Reply";
	// 	$note = $log_username.' commented here:<br /><a href="user.php?u='.$account_name.'#status_'.$osid.'">Click here to view the conversation</a>';
	// 	mysqli_query($db_conx, "INSERT INTO notifications(username, initiator, app, note, date_time)
	// 	             VALUES('$participant','$log_username','$app','$note',now())");
	// }
	mysqli_close($db_conx);
	echo "reply_ok|$id";
	exit();
}
?><?php
if (isset($_POST['action']) && $_POST['action'] == "delete_status"){
	if(!isset($_POST['statusid']) || $_POST['statusid'] == ""){
		mysqli_close($db_conx);
		echo "status id is missing";
		exit();
	}
	$statusid = preg_replace('#[^0-9]#', '', $_POST['statusid']);
	// Check to make sure this logged in user actually owns that comment
	$query = mysqli_query($db_conx, "SELECT account_id, author_id FROM status WHERE id='$statusid' LIMIT 1");
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$account_id = $row["account_id"];
		$author_id = $row["author_id"];
	}
    if ($author_id == $log_id || $account_id == $log_id) {
		mysqli_query($db_conx, "DELETE FROM status WHERE osid='$statusid'");
		mysqli_close($db_conx);
	    echo "delete_ok";
		exit();
	}
}
?><?php
if (isset($_POST['action']) && $_POST['action'] == "delete_reply"){
	if(!isset($_POST['replyid']) || $_POST['replyid'] == ""){
		mysqli_close($db_conx);
		exit();
	}
	$replyid = preg_replace('#[^0-9]#', '', $_POST['replyid']);
	// Check to make sure the person deleting this reply is either the account owner or the person who wrote it
	$query = mysqli_query($db_conx, "SELECT osid, account_id, author_id FROM status WHERE id='$replyid' LIMIT 1");
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$osid = $row["osid"];
		$account_name = $row["account_id"];
		$author = $row["author_id"];
	}
    if ($author == $log_id || $account_name == $log_id) {
		mysqli_query($db_conx, "DELETE FROM status WHERE id='$replyid' LIMIT 1");
		mysqli_close($db_conx);
	    echo "delete_ok";
		exit();
	}
}
?>
