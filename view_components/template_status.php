<?php
//require('./php_includes/db_conx.php');
$status_ui = "";
$statuslist = "";
// if($isOwner == "yes"){
// 	$status_ui = '<textarea class="form-control" id="statustext" onkeyup="statusMax(this,250)" placeholder="What&#39;s new with you '.$u.'?"></textarea>';
// 	$status_ui .= '<button id="statusBtn" onclick="postToStatus(\'status_post\',\'a\',\''.$u.'\',\'statustext\')">Post</button>';
// } else if($isFriend == true && $log_username != $u){
// 	//$status_ui = '<textarea class="form-control" id="statustext" onkeyup="statusMax(this,250)" placeholder="Hi '.$log_username.', say something to '.$u.'"></textarea>';
// 	//$status_ui .= '<button id="statusBtn" onclick="postToStatus(\'status_post\',\'c\',\''.$u.'\',\'statustext\')">Post</button>';
// }

if($isOwner == "yes"){
	$status_ui = '<textarea class="form-control" id="statustext" onkeyup="statusMax(this,250)" placeholder="What&#39;s new with you '.$u.'?"></textarea>';
	$status_ui .= '<div id="uploadDisplay_SP"></div>';
	$status_ui .= '<div id="btns_SP" class="">';
		$status_ui .= '<button class="btn button-lg button-primary" id="statusBtn" onclick="postToStatus(\'status_post\',\'a\',\''.$u.'\',\'statustext\')">Post</button>';
		$status_ui .= '<button id="triggerBtn_SP" class="btn button-lg button-primary triggerBtn" onclick="triggerUpload(event, \'fu_SP\')" >Upload A Photo</button>';
		$status_ui .= '<input id="public_private_post" data-toggle="toggle" data-on="Private" data-off="Public" type="checkbox" data-toggle="toggle" data-width="100">';
	$status_ui .= '</div>';
	$status_ui .= '<div id="standardUpload" class="hiddenStuff">';
		$status_ui .= '<form id="image_SP" enctype="multipart/form-data" method="post">';
		$status_ui .= '<input type="file" name="FileUpload" id="fu_SP" onchange="doUpload(\'fu_SP\')"/>';
		$status_ui .= '</form>';
	$status_ui .= '</div>';
} else if($isFriend == true && $log_username != $u){
	// $status_ui = '<textarea id="statustext" onkeyup="statusMax(this,250)" onfocus="showBtnDiv()" placeholder="Hi '.$log_username.', say something to '.$u.'"></textarea>';
	// $status_ui .= '<div id="uploadDisplay_SP"></div>';
	// $status_ui .= '<div id="btns_SP" class="hiddenStuff">';
	// 	$status_ui .= '<button id="statusBtn" onclick="postToStatus('status_post','c',''.$u.'','statustext')">Post</button>';
	// 	$status_ui .= '<img src="images/camera.JPG" id="triggerBtn_SP" class="triggerBtn" onclick="triggerUpload(event, 'fu_SP')" width="137" height="22" title="Upload A Photo" />';
	// $status_ui .= '</div>';
	// $status_ui .= '<div id="standardUpload" class="hiddenStuff">';
	// 	$status_ui .= '<form id="image_SP" enctype="multipart/form-data" method="post">';
	// 	$status_ui .= '<input type="file" name="FileUpload" id="fu_SP" onchange="doUpload('fu_SP')"/>';
	// 	$status_ui .= '</form>';
	// $status_ui .= '</div>';
}

?><?php

$res = mysqli_query($db_conx,"SELECT id FROM users WHERE username='$u' LIMIT 1");
if(mysqli_num_rows($res)>0){
	$row = mysqli_fetch_array($res);
	$user_id = $row["id"];
} else {
	echo "No Such User";
	die();
}


$oneStatusBlock = ''; 
if($log_username == $u) {
	$sql = "SELECT s.*,u.username,u.avatar 
			FROM status s,users u 
			WHERE 
				account_id='$user_id' 
				AND 
				type NOT IN ('b')
				AND
				s.author_id=u.id 
			ORDER BY postdate DESC 
			LIMIT 20";
} else {
	$sql = "SELECT s.*,u.username,u.avatar 
			FROM status s,users u 
			WHERE 
				account_id='$user_id' 
				AND 
				type NOT IN ('b','d')
				AND
				s.author_id=u.id 
			ORDER BY postdate DESC 
			LIMIT 20";	
}


$query = mysqli_query($db_conx, $sql);
$statusnumrows = mysqli_num_rows($query);
while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
	$statusid = $row["id"];
	$account_name = $row["account_id"];
	$author = $row["author_id"];
	$author_name = $row["username"];
	$author_avatar = $row["avatar"];
	//$postdate = date_format($row["postdate"],"Y/m/d");
	$postdate = date('M d, Y', strtotime($row["postdate"]));
	$data = $row["data"];
	$data = nl2br($data);
	$data = str_replace("&amp;","&",$data);
	$data = stripslashes($data);
	


	$statusDeleteButton = '';
	if($author == $log_id || $account_name == $log_id ){
		//$statusDeleteButton = '<span id="sdb_'.$statusid.'"><a href="#" onclick="return false;" onmousedown="deleteStatus(\''.$statusid.'\',\'status_'.$statusid.'\');" title="DELETE THIS STATUS AND ITS REPLIES"><i class="fa fa-trash-o" aria-hidden="true"> &nbsp;Delete</a></span> &nbsp; &nbsp;';
		$statusDeleteButton = '<a href="#" onclick="return false;" onmousedown="deleteStatus(\''.$statusid.'\',\'status_'.$statusid.'\');" title="DELETE THIS STATUS AND ITS REPLIES"><i class="fa fa-trash-o" aria-hidden="true"></i> &nbsp;Delete</a>';
	}
	$moreOptionBtn = '';
	$moreOptionBtn .= '<div class="dropdown pull-right moreOptionBtn">';
	$moreOptionBtn .= '<button class="btn btn-default dropdown-toggle left_side_panel" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fa fa-ellipsis-v"></i></button>';
	$moreOptionBtn .= '<ul class="dropdown-menu ">';
	$moreOptionBtn .= '<li>'.$statusDeleteButton.'</li>';
	//$moreOptionBtn .= '<a href="#" onclick="return false;" onmousedown="deleteStatus('70','status_70');" title="DELETE THIS STATUS AND ITS REPLIES"><i class="fa fa-trash-o" aria-hidden="true"></i>&nbsp; Delete</a>';
	$moreOptionBtn .= '</ul>';
	$moreOptionBtn .= '</div>';
	// GATHER UP ANY STATUS REPLIES
	$status_replies = "";
	$query_replies = mysqli_query($db_conx, 
	   "SELECT s.*,u.username,u.avatar  
		FROM status s,users u 
		WHERE 
			osid='$statusid' 
			AND 
			type='b'
			AND
			s.author_id=u.id 
		ORDER BY postdate ASC
		LIMIT 10
	");
	$replynumrows = mysqli_num_rows($query_replies);
    if($replynumrows > 0){
        while ($row2 = mysqli_fetch_array($query_replies, MYSQLI_ASSOC)) {
			$statusreplyid = $row2["id"];
			$replyauthor = $row2["author_id"];
			$replay_author_name = $row["username"];
			$replay_author_avatar = $row["avatar"];
			$replydata = $row2["data"];
			$replydata = nl2br($replydata);
			//$replypostdate = $row2["postdate"];
			$replypostdate = date('M d, Y', strtotime($row2["postdate"]));
			$replydata = str_replace("&amp;","&",$replydata);
			$replydata = stripslashes($replydata);
			$replyDeleteButton = '';
			if($replyauthor == $log_id || $account_name == $log_id ){
				$replyDeleteButton = 
					'<span id="srdb_'.$statusreplyid.'">
						<a href="#" onclick="return false;" onmousedown="deleteReply(\''.$statusreplyid.'\',\'reply_'.$statusreplyid.'\');" title="DELETE THIS COMMENT">Delete</a>
					</span>';
			}
		    $author_profile_pic = '<img src="user/'.dir_encrypt($replay_author_name).'/'.$replay_author_avatar.'" alt="'.$author_name.'">';
		    if($avatar == NULL){
		        $author_avatar = '<img src="images/avatardefault.jpg" alt="'.$replay_author_name.'">';
		    }   
			$status_replies .= 
				'<div id="reply_'.$statusreplyid.'" class="reply_boxes row">
					<div class="col-xs-1 col-sm-1 col-md-1">'.
						$author_profile_pic.'
					</div>
					<div class="col-xs-11 col-sm-11 col-md-11">	
						<h5><a href="user.php?u='.$replay_author_name.'">'.$replay_author_name.'</a></h5>'.
						'<p class="replaydata">'.$replydata.'<p>'.
						'<span>'.$replypostdate.' &nbsp;'.
						$replyDeleteButton.
						'</span>'.						
					'</div>
				</div>';
        }
    }
    $author_profile_pic = '<img src="user/'.dir_encrypt($author_name).'/'.$author_avatar.'" alt="'.$author_name.'">';
    if($avatar == NULL){
        $author_avatar = '<img src="images/avatardefault.jpg" alt="'.$author_name.'">';
    }   
	$oneStatusBlock .= 
		'<div class="status_boxes">
			<div>'.
				'<span>'.
				$author_profile_pic.
				'</span>
				<p>
				<a class="authorname" href="user.php?u='.$author_name.'">'.$author_name.'</a> <br><span class="date">'.
				$postdate.'</span></p>'.$moreOptionBtn.'<div class="clear"></div><div class="data">'.
				$data.'</div>
			</div>'.
		'</div>';
	if($isFriend == true || $log_id == $user_id){
		$oneStatusBlock .= '<div class="commentBox">';
		$oneStatusBlock .= '<div class="likesANDcomments">';		
		$oneStatusBlock .= '<p><a>6 likes</a> . <a>5 comments</a></p>';
		$oneStatusBlock .= '</div>';		
		$oneStatusBlock .= '<div class="logUserCommentBox">';
	    $oneStatusBlock .= '<div class="col-xs-1 col-sm-1 col-md-1 text-center">';
	    $oneStatusBlock .= '<span>'.$profile_pic.'</span>';
	    $oneStatusBlock .= '</div>';
	    $oneStatusBlock .= '<div class="input-group col-xs-11 col-sm-11 col-md-11">';
	    $oneStatusBlock .= '<input type="text" id="replytext_'.$statusid.'" class="form-control replytext" onkeyup="statusMax(this,250)" placeholder="write a comment here"/>';
	    $oneStatusBlock .= '<span class="input-group-btn"><button class="btn btn-secondary" id="replyBtn_'.$statusid.'" onclick="replyToStatus('.$statusid.',\''.$user_id.'\',\'replytext_'.$statusid.'\',this)"><i class="fa fa-paper-plane" aria-hidden="true"></i></button></span>';
	    $oneStatusBlock .= '</div>';
	    $oneStatusBlock .= '</div>';
		$oneStatusBlock .= '<div class="clear"></div>';
	    $oneStatusBlock .= $status_replies;
		$oneStatusBlock .= '</div>';
	}
	$statuslist .= '<div id="status_'.$statusid.'">'.$oneStatusBlock.'</div>';
	$oneStatusBlock = ''; 
}
?>
<script>

// function postToStatus(action,type,user,ta){
// 	var data = _(ta).value;
// 	if(data == ""){
// 		alert("Type something first weenis");
// 		return false;
// 	}
// 	_("statusBtn").disabled = true;
// 	var ajax = ajaxObj("POST", "php_parsers/status_system.php");
// 	ajax.onreadystatechange = function() {
// 		if(ajaxReturn(ajax) == true) {
// 			var datArray = ajax.responseText.split("|");
// 			if(datArray[0] == "post_ok"){
// 				var sid = datArray[1];
// 				console.log(sid);
// 				data = data.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\n/g,"<br />").replace(/\r/g,"<br />");
// 				var currentHTML = _("statusarea").innerHTML;
// 				_("statusarea").innerHTML = '<div id="status_'+sid+'" class="status_boxes"><div><b>Posted by you just now:</b> <span id="sdb_'+sid+'"><a href="#" onclick="return false;" onmousedown="deleteStatus(\''+sid+'\',\'status_'+sid+'\');" title="DELETE THIS STATUS AND ITS REPLIES">delete status</a></span><br />'+data+'</div></div><textarea id="replytext_'+sid+'" class="replytext" onkeyup="statusMax(this,250)" placeholder="write a comment here"></textarea><button id="replyBtn_'+sid+'" onclick="replyToStatus('+sid+',\'<?php echo $u; ?>\',\'replytext_'+sid+'\',this)">Reply</button>'+currentHTML;
// 				_("statusBtn").disabled = false;
// 				_(ta).value = "";
// 			} else {
// 				alert(ajax.responseText);
// 			}
// 		}
// 	}
// 	ajax.send("action="+action+"&type="+type+"&user="+<?php echo $loguserID;?>+"&data="+data);
// }
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
				data = data.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\n/g,"<br />").replace(/\r/g,"<br />");
				_("status_"+sid).innerHTML += '<div id="reply_'+rid+'" class="reply_boxes"><div><b>Reply by you just now:</b><span id="srdb_'+rid+'"><a href="#" onclick="return false;" onmousedown="deleteReply(\''+rid+'\',\'reply_'+rid+'\');" title="DELETE THIS COMMENT">remove</a></span><br />'+data+'</div></div>';
				_("replyBtn_"+sid).disabled = false;
				_(ta).value = "";
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=status_reply&sid="+sid+"&user="+user+"&data="+data);
}
function deleteStatus(statusid,statusbox){
	var conf = confirm("Press OK to confirm deletion of this status and its replies");
	if(conf != true){
		return false;
	}
	var ajax = ajaxObj("POST", "php_parsers/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "delete_ok"){
				_(statusbox).style.display = 'none';
				_("replytext_"+statusid).style.display = 'none';
				_("replyBtn_"+statusid).style.display = 'none';
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=delete_status&statusid="+statusid);
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
