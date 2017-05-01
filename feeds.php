<?php
include_once("php_includes/check_login_status.php");
// Make sure the user is logged in and sanitize the session
if(isset($_SESSION['username'])){
	$u = $log_id;
} else {
    echo "You need to be logged in.";
    exit();	
}
// get array of friends
$sql = "SELECT COUNT(id) FROM friends WHERE user1_id='$u' AND accepted='1' OR user2_id='$u' AND accepted='1'";
$query = mysqli_query($db_conx, $sql);
$query_count = mysqli_fetch_row($query);
$friend_count = $query_count[0];
if($friend_count < 1){
	echo "no feed available";
	exit;
} else {
	$all_friends = array();
	$add_self = array_push($all_friends, $u);
	$sql = "SELECT user1_id, user2_id FROM friends WHERE (user2_id='$u' OR user1_id='$u') AND accepted='1'";
	$query = mysqli_query($db_conx, $sql);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		if ($row["user1_id"] != $u){array_push($all_friends, $row["user1_id"]);}
		if ($row["user2_id"] != $u){array_push($all_friends, $row["user2_id"]);}
	}
}
// get feed
// based loosely on code in template_status.php
// my method to get images is based on my other video tutorial
// that always has a value in the database
// http://www.youtube.com/watch?v=U79z3ZJSBSc
// if you do not edit yours and have default images, this will not work properly
// broken image links and maybe errors
$statuslist = "";
$friendsCSV = join("','", $all_friends);
// all 1 line
$sql = "SELECT s.*, u.avatar
		FROM status AS s
		LEFT JOIN users AS u ON u.id = s.author_id
		WHERE s.author_id IN ('$friendsCSV') AND (s.type='a' OR s.type='c')
		ORDER BY s.postdate DESC LIMIT 20";
		
$query = mysqli_query($db_conx, $sql);
$statusnumrows = mysqli_num_rows($query);
if($statusnumrows > 0){
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$statusid = $row["id"];
		$account_name = $row["account_id"];
		$author = $row["author_id"];
		$postdate = $row["postdate"];
		$data = $row["data"];
		$avatar = $row["avatar"];
		$data = nl2br($data);
		$data = str_replace("&amp;","&",$data);
		$data = stripslashes($data);
	
		// GATHER UP ANY STATUS REPLIES
		$status_replies = "";
		// all 1 line
		$sql2 = "SELECT s.*, u.avatar
			 	FROM status AS s
			 	LEFT JOIN users AS u ON u.id = s.author_id
			 	WHERE s.osid = '$statusid'
			 	AND s.type='b'
			 	ORDER BY postdate ASC";
		$query_replies = mysqli_query($db_conx, $sql2);
		$replynumrows = mysqli_num_rows($query_replies);
    	if($replynumrows > 0){
        	while ($row2 = mysqli_fetch_array($query_replies, MYSQLI_ASSOC)) {
				$statusreplyid = $row2["id"];
				$replyauthor = $row2["author_id"];
				$replydata = $row2["data"];
				$replydata = nl2br($replydata);
				$replypostdate = $row2["postdate"];
				$avatar2 = $row2["avatar"];
				$replydata = str_replace("&amp;","&",$replydata);
				$replydata = stripslashes($replydata);
				// all 1 line
				$status_replies .= '
				<div id="reply_'.$statusreplyid.'" class="reply_boxes"><div>
				<img src="user/'.$replyauthor.'/'.$avatar2.'" width="20" height="20" />
				<b>Reply by <a href="user.php?u='.$replyauthor.'">'.$replyauthor.'</a> '.$replypostdate.':</b>
				<br />'.$replydata.'</div></div>';
        	}
    	}
		// all 1 line
		$statuslist .= '
		<div id="status_'.$statusid.'" class="status_boxes"><div>
		<img src="user/'.$author.'/'.$avatar.'" width="20" height="20" />
		<b>Posted by <a href="user.php?u='.$author.'">'.$author.'</a> '.$postdate.':</b>
		<br />'.$data.'</div>'.$status_replies.'</div>';
	
		// all 1 line
		$statuslist .= '
		<textarea id="replytext_'.$statusid.'" class="replytext" 
		onkeyup="statusMax(this,250)" placeholder="write a comment here"></textarea>
		<button id="replyBtn_'.$statusid.'" onclick="replyToStatus('.$statusid.',\''.$log_id.'\',\'replytext_'.$statusid.'\',this)">Reply</button>';	
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style/style.css">
<style type="text/css">
<!-- style copied from user.php -->
textarea#statustext{width:982px; height:80px; padding:8px; border:#999 1px solid; font-size:16px;}
div.status_boxes{padding:12px; line-height:1.5em;}
div.status_boxes > div{padding:8px; border:#99C20C 1px solid; background: #F4FDDF;}
div.status_boxes > div > b{font-size:12px;}
div.status_boxes > button{padding:5px; font-size:12px;}
textarea.replytext{width:98%; height:40px; padding:1%; border:#999 1px solid;}
div.reply_boxes{padding:12px; border:#999 1px solid; background:#F5F5F5;}
div.reply_boxes > div > b{font-size:12px;}
</style>
<script src="js/main.js"></script>
<script src="js/ajax.js"></script>
<script type="text/javascript">
<!-- functions copied from template_status.php -->
function replyToStatus(sid,user,ta,btn){
	var data = _(ta).value;
	if(data == ""){
		alert("Type something first weenis");
		return false;
	}
	_("replyBtn_"+sid).disabled = true;
	var ajax = ajaxObj("POST", "php_parsers/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			var datArray = ajax.responseText.split("|");
			if(datArray[0] == "reply_ok"){
				var rid = datArray[1];
				data = data.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/n/g,"<br />").replace(/r/g,"<br />");
				_("status_"+sid).innerHTML += '<div id="reply_'+rid+'" class="reply_boxes"><div><b>Reply by you just now:</b><span id="srdb_'+rid+'"><a href="#" onclick="return false;" onmousedown="deleteReply(''+rid+'','reply_'+rid+'');" title="DELETE THIS COMMENT">remove</a></span><br />'+data+'</div></div>';
				_("replyBtn_"+sid).disabled = false;
				_(ta).value = "";
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=status_reply&sid="+sid+"&user="+user+"&data="+data);
}
function deleteReply(replyid,replybox){
	var conf = confirm("Press OK to confirm deletion of this reply");
	if(conf != true){
		return false;
	}
	var ajax = ajaxObj("POST", "php_parsers/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "delete_ok"){
				_(replybox).style.display = 'none';
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=delete_reply&replyid="+replyid);
}
function statusMax(field, maxlimit) {
	if (field.value.length > maxlimit){
		alert(maxlimit+" maximum character limit reached");
		field.value = field.value.substring(0, maxlimit);
	}
}
</script>
</head>
<body>
<?php //include_once("view_components/template_pageTop.php"); ?>
<div id="pageMiddle">
	<?php echo $statuslist; ?>
</div>
<?php //include_once("template_pageBottom.php"); ?>
</body>
</html>