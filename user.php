<?php
include_once("php_includes/check_login_status.php");
// Initialize any variables that the page might echo

$u = "";
$sex = "Male";
$userlevel = "";
$profile_pic = "";
$profile_pic_btn = "";
$avatar_form = "";
$country = "";
$joindate = "";
$lastsession = "";
// Make sure the _GET username is set, and sanitize it
if(isset($_GET["u"])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
	require_once('php_includes/dir_hash.php');
	require_once('php_includes/libs_link.php');
	require_once('view_components/template_logged_user_avatar.php');
} else {
    header("location: http://www.webintersect.com");
    exit();
}

$loguserID = $_SESSION['userid'];

// Select the member from the users table
$sql = "SELECT * FROM users WHERE username='$u' AND activated='1' LIMIT 1";
$user_query = mysqli_query($db_conx, $sql);
// Now make sure that user exists in the table
$numrows = mysqli_num_rows($user_query);
if($numrows < 1){
	echo "That user does not exist or is not yet activated, press back";
    exit();
}
// Check to see if the viewer is the account owner
$isOwner = "no";
if($u == $log_username && $user_ok == true){
	$isOwner = "yes";
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Toggle Avatar Form</a>';
	$avatar_form  = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="php_parsers/photo_system.php">';
	$avatar_form .=   '<h4>Change your avatar</h4>';
	$avatar_form .=   '<input type="file" name="avatar" required>';
	$avatar_form .=   '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}
// Fetch the user row from the query above
while ($row = mysqli_fetch_array($user_query, MYSQLI_ASSOC)) {
	$profile_id = $row["id"];
	$gender = $row["gender"];
	$country = $row["country"];
	$userlevel = $row["userlevel"];
	$avatar = $row["avatar"];
	$signup = $row["signup"];
	$lastlogin = $row["lastlogin"];
	$joindate = strftime("%b %d, %Y", strtotime($signup));
	$lastsession = strftime("%b %d, %Y", strtotime($lastlogin));
}
if($gender == "f"){
	$sex = "Female";
}
	$get_profile_pic = '<img src="user/'.dir_encrypt($u).'/'.$avatar.'" alt="'.$u.'">';
	if($avatar == NULL){
		//$profile_pic = '<img src="images/avatardefault.jpg">';
		$get_profile_pic = '<img src="images/avatardefault.jpg" alt="'.$u.'">';
	}
?>

<?php
$isFriend = false;
$ownerBlockViewer = false;
$viewerBlockOwner = false;
if($u != $log_username && $user_ok == true){
	$friend_check = "SELECT id FROM friends WHERE user1_id='$log_id' AND user2_id='$profile_id' AND accepted='1' OR user1_id='$profile_id' AND user2_id='$log_id' AND accepted='1' LIMIT 1";
	if(mysqli_num_rows(mysqli_query($db_conx, $friend_check)) > 0){
        $isFriend = true;
    }
	// $block_check1 = "SELECT id FROM blockedusers WHERE blocker='$u' AND blockee='$log_username' LIMIT 1";
	// if(mysqli_num_rows(mysqli_query($db_conx, $block_check1)) > 0){
 //        $ownerBlockViewer = true;
 //    }
	// $block_check2 = "SELECT id FROM blockedusers WHERE blocker='$log_username' AND blockee='$u' LIMIT 1";
	// if(mysqli_num_rows(mysqli_query($db_conx, $block_check2)) > 0){
 //        $viewerBlockOwner = true;
 //    }
}
?><?php
$friend_button = '';
//$friend_button = '<button disabled>Request As Friend</button>';
//$block_button = '<button disabled>Block User</button>';
// LOGIC FOR FRIEND BUTTON
if($isFriend == true){
	$friend_button = '<button onclick="friendToggle(\'unfriend\',\''.$profile_id.'\',\'friendBtn\')">Unfriend</button>';
} else if($user_ok == true && $u != $log_username && $ownerBlockViewer == false){
	$friend_button = '<button onclick="friendToggle(\'friend\',\''.$profile_id.'\',\'friendBtn\')">Request As Friend</button>';
}
// LOGIC FOR BLOCK BUTTON
// if($viewerBlockOwner == true){
// 	$block_button = '<button onclick="blockToggle(\'unblock\',\''.$u.'\',\'blockBtn\')">Unblock User</button>';
// } else if($user_ok == true && $u != $log_username){
// 	$block_button = '<button onclick="blockToggle(\'block\',\''.$u.'\',\'blockBtn\')">Block User</button>';
// }
?><?php
// $friendsHTML = '';
// $friends_view_all_link = '';
// $sql = "SELECT COUNT(id) FROM friends WHERE user1='$u' AND accepted='1' OR user2='$u' AND accepted='1'";
// $query = mysqli_query($db_conx, $sql);
// $query_count = mysqli_fetch_row($query);
// $friend_count = $query_count[0];
// if($friend_count < 1){
// 	$friendsHTML = $u." has no friends yet";
// } else {
// 	$max = 18;
// 	$all_friends = array();
// 	$sql = "SELECT user1 FROM friends WHERE user2='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
// 	$query = mysqli_query($db_conx, $sql);
// 	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
// 		array_push($all_friends, $row["user1"]);
// 	}
// 	$sql = "SELECT user2 FROM friends WHERE user1='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
// 	$query = mysqli_query($db_conx, $sql);
// 	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
// 		array_push($all_friends, $row["user2"]);
// 	}
// 	$friendArrayCount = count($all_friends);
// 	if($friendArrayCount > $max){
// 		array_splice($all_friends, $max);
// 	}
// 	if($friend_count > $max){
// 		$friends_view_all_link = '<a href="view_friends.php?u='.$u.'">view all</a>';
// 	}
// 	$orLogic = '';
// 	foreach($all_friends as $key => $user){
// 			$orLogic .= "username='$user' OR ";
// 	}
// 	$orLogic = chop($orLogic, "OR ");
// 	$sql = "SELECT username, avatar FROM users WHERE $orLogic";
// 	$query = mysqli_query($db_conx, $sql);
// 	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
// 		$friend_username = $row["username"];
// 		$friend_avatar = $row["avatar"];
// 		if($friend_avatar != ""){
// 			$friend_pic = 'user/'.$friend_username.'/'.$friend_avatar.'';
// 		} else {
// 			$friend_pic = 'images/avatardefault.jpg';
// 		}
// 		$friendsHTML .= '<a href="user.php?u='.$friend_username.'"><img class="friendpics" src="'.$friend_pic.'" alt="'.$friend_username.'" title="'.$friend_username.'"></a>';
// 	}
// }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
<title><?php echo $u; ?></title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<?php //printCSSLibs(); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style type="text/css">
/*div#photo_showcase{float:right; background:url(style/photo_showcase_bg.jpg) no-repeat; width:136px; height:127px; margin:20px 30px 0px 0px; cursor:pointer;}
div#photo_showcase > img{width:74px; height:74px; margin:37px 0px 0px 9px;}
img.friendpics{border:#000 1px solid; width:40px; height:40px; margin:2px;}*/
</style>
<style type="text/css">
/*textarea#statustext{width:982px; height:80px; padding:8px; border:#999 1px solid; font-size:16px;}
textarea.replytext{width:98%; height:40px; padding:1%; border:#999 1px solid;}*/
/*div.status_boxes{padding:12px; line-height:1.5em;}*/
div.status_boxes > div{padding:8px;border-bottom: none; background: #fff;}
div.status_boxes > div > b{font-size:12px;}
div.status_boxes > button{padding:5px; font-size:12px;}
/*div.reply_boxes{padding:12px; border:#999 1px solid; background:#F5F5F5;}
div.reply_boxes > div > b{font-size:12px;}*/
</style>
<script src="js/main.js"></script>
<script src="js/ajax.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
function toggleElement(x){
	var x = _(x);
	if(x.style.display == 'block'){
		x.style.display = 'none';
	}else{
		x.style.display = 'block';
	}
}

function friendToggle(type,user,elem){
	var conf = confirm("Press OK to confirm the '"+type+"' action for user <?php echo $u; ?>.");
	if(conf != true){
		return false;
	}
	_(elem).innerHTML = 'please wait ...';
	var ajax = ajaxObj("POST", "php_parsers/friend_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "friend_request_sent"){
				_(elem).innerHTML = 'OK Friend Request Sent';
			} else if(ajax.responseText == "unfriend_ok"){
				_(elem).innerHTML = '<button onclick="friendToggle(\'friend\',\'<?php echo $u; ?>\',\'friendBtn\')">Request As Friend</button>';
			} else {
				alert(ajax.responseText);
				_(elem).innerHTML = 'Try again later';
			}
		}
	}
	ajax.send("type="+type+"&user="+<?php echo $profile_id;?>);
}
function blockToggle(type,blockee,elem){
	var conf = confirm("Press OK to confirm the '"+type+"' action on user <?php echo $u; ?>.");
	if(conf != true){
		return false;
	}
	var elem = document.getElementById(elem);
	elem.innerHTML = 'please wait ...';
	var ajax = ajaxObj("POST", "php_parsers/block_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "blocked_ok"){
				elem.innerHTML = '<button onclick="blockToggle(\'unblock\',\'<?php echo $u; ?>\',\'blockBtn\')">Unblock User</button>';
			} else if(ajax.responseText == "unblocked_ok"){
				elem.innerHTML = '<button onclick="blockToggle(\'block\',\'<?php echo $u; ?>\',\'blockBtn\')">Block User</button>';
			} else {
				alert(ajax.responseText);
				elem.innerHTML = 'Try again later';
			}
		}
	}
	ajax.send("type="+type+"&blockee="+blockee);
}
function triggerUpload(e,elem){
	e.preventDefault();
	_(elem).click();	
}
var hasImage = "";
window.onbeforeunload = function(){
	if(hasImage != ""){
	    return "You have not posted your image";
	}
}

function doUpload(id){
	// www.developphp.com/video/JavaScript/File-Upload-Progress-Bar-Meter-Tutorial-Ajax-PHP
	var file = _(id).files[0];
	if(file.name == ""){
		return false;		
	}
	if(file.type != "image/jpeg" && file.type != "image/gif"){
		alert("That file type is not supported.");
		return false;
	}
	_("triggerBtn_SP").style.display = "none";
	_("uploadDisplay_SP").innerHTML = "Image uploading......";
	var formdata = new FormData();
	formdata.append("stPic", file);
	var ajax = new XMLHttpRequest();
	ajax.addEventListener("load", completeHandler, false);
	ajax.addEventListener("error", errorHandler, false);
	ajax.addEventListener("abort", abortHandler, false);
	ajax.open("POST", "php_parsers/photo_system.php");
	ajax.send(formdata);	
}
function completeHandler(event){
	var data = event.target.responseText;
	var datArray = data.split("|");
	console.log(datArray);
	if(datArray[0].trim() == "upload_complete"){
		hasImage = datArray[1];
		_("uploadDisplay_SP").innerHTML = '<img src="tempUploads/'+datArray[1]+'" class="statusImage" />';
		_("triggerBtn_SP").style.display = "inline";
	} else {
		_("uploadDisplay_SP").innerHTML = datArray[0];
		_("triggerBtn_SP").style.display = "inline";
	}
}
function errorHandler(event){
	_("uploadDisplay_SP").innerHTML = "Upload Failed";
	_("triggerBtn_SP").style.display = "inline";
}
function abortHandler(event){
	_("uploadDisplay_SP").innerHTML = "Upload Aborted";
	_("triggerBtn_SP").style.display = "inline";
}
function postToStatus(action,type,user,ta){
	var data = _(ta).value;
	if(data == "" && hasImage == ""){
		alert("Type something first weenis");
		return false;
	}
	var data2 = "";
	if(data != ""){
		data2 = data.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/n/g,"<br />").replace(/r/g,"<br />");
	}
	if (data2 == "" && hasImage != ""){
		data = "||na||";
		data2 = '<img src="permUploads/'+hasImage+'" />';		
	} else if (data2 != "" && hasImage != ""){
		data2 += '<br /><img src="permUploads/'+hasImage+'" />';
	} else {
		hasImage = "na";
	}
	if(_('public_private_post').checked === true){
		type = 'd';
	} else {
		type = 'a';
	}
	
	_("statusBtn").disabled = true;
	var ajax = ajaxObj("POST", "php_parsers/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			var datArray = ajax.responseText.split("|");
			if(datArray[0] == "post_ok"){
				var sid = datArray[1];
				var currentHTML = _("statusarea").innerHTML;
//				_("statusarea").innerHTML = '<div id="status_'+sid+'" class="status_boxes"><div><b>Posted by you just now:</b> <span id="sdb_'+sid+'"><a href="#" onclick="return false;" onmousedown="deleteStatus(\''+sid+'\',\'status_'+sid+'\');" title="DELETE THIS STATUS AND ITS REPLIES">delete status</a></span><br />'+data+'</div></div><textarea id="replytext_'+sid+'" class="replytext" onkeyup="statusMax(this,250)" placeholder="write a comment here"></textarea><button id="replyBtn_'+sid+'" onclick="replyToStatus('+sid+',\'<?php echo $u; ?>\',\'replytext_'+sid+'\',this)">Reply</button>'+currentHTML;
				_("statusarea").innerHTML = '<div id="status_'+sid+'" class="status_boxes"><div><b>Posted by you just now:</b> <span id="sdb_'+sid+'"><a href="#" onclick="return false;" onmousedown="deleteStatus(\''+sid+'\',\'status_'+sid+'\');" title="DELETE THIS STATUS AND ITS REPLIES">delete status</a></span><br />'+data2+'</div></div><textarea id="replytext_'+sid+'" class="replytext" onkeyup="statusMax(this,250)" placeholder="write a comment here"></textarea><button id="replyBtn_'+sid+'" onclick="replyToStatus('+sid+',\'<?php echo $u; ?>\',\'replytext_'+sid+'\',this)">Reply</button>'+currentHTML;
				_("statusBtn").disabled = false;
				_(ta).value = "";
				_("triggerBtn_SP").style.display = "inline";
				_("btns_SP").style.display = "none";
				_("uploadDisplay_SP").innerHTML = "";
				_("fu_SP").value = "";
				hasImage = "";
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action="+action+"&type="+type+"&user="+<?php echo $loguserID;?>+"&data="+data+"&image="+hasImage);
}
</script>
</head>
<body>
<?php include_once("view_components/template_pageTop.php"); ?>
        <div id="page-content-wrapper">
        	<div style="margin-top:80px;"></div>	
            <div class="container-fluid xyz row">
				<h2 class="display-for-mob"><?php echo $u; ?></h2>
				<div class="col-xs-12 col-sm-12 col-md-3 profile-left">	
				  <div id="profile_pic_box" ><?php echo $profile_pic_btn; ?><?php echo $avatar_form; ?><?php echo $get_profile_pic; ?></div>
				  <div><span id="friendBtn"><?php echo $friend_button; ?></span></div>	
				  <!-- <p>Is the viewer the page owner, logged in and verified? <b><?php //echo $isOwner; ?></b></p> -->
				  <div class="about">
					  <p>About 
						<?php 
							if($u == $log_username && $user_ok == true) {
								echo " &nbsp;<a><i class='fa fa-pencil'></i></a>";
							}
						?>
					  </p>
					  <p><b>Join Date:</b> <?php echo $joindate; ?></p>
					  <p><b>Last Session:</b> <?php echo $lastsession; ?></p>
				  </div>
				  <p><span id="friendBtn"><?php //echo $friend_button; ?></span> <?php //echo $u." has ".$friend_count." friends"; ?> <?php //echo $friends_view_all_link; ?></p>
				  <p><?php //echo $friendsHTML; ?></p>
				  <?php include_once("view_components/template_status.php"); ?>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-8">
					<div><h2 class="display-for-non-mob"><?php echo $u.'\'s profile'; ?></h2></div>
					<div id="statusui">
					  <?php echo $status_ui; ?>
					</div>
					<div id="statusarea">
					  <?php echo $statuslist; ?>
					</div>					
				</div>
            </div>
        </div>
    </div>
<?php include_once("view_components/template_pageBottom.php"); ?>
<?php //printJSLibs(); ?>


<script src="js/sidebar_menu.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.sidebar-nav li').eq(1).addClass('active');
});
</script>
</body>
</html>
