<?php 
$res = mysqli_query($db_conx,"SELECT avatar FROM users WHERE id='$log_id' LIMIT 1");
while($row = mysqli_fetch_array($res)){
	$avatar = $row["avatar"];
}
$profile_pic = '<img src="user/'.dir_encrypt($log_username).'/'.$avatar.'" alt="'.$log_username.'">';
if($avatar == NULL){
	//$profile_pic = '<img src="images/avatardefault.jpg">';
	$profile_pic = '<img src="images/avatardefault.jpg" alt="'.$log_username.'">';
}
?>