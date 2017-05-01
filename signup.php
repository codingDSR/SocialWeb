<?php
session_start();
// If user is logged in, header them away
if(isset($_SESSION["username"])){
	header("location: message.php?msg=NO to that weenis");
    exit();
}
?><?php
// Ajax calls this NAME CHECK code to execute
if(isset($_POST["usernamecheck"])){
	include_once("php_includes/db_conx.php");
	$username = preg_replace('#[^a-z0-9]#i', '', $_POST['usernamecheck']);
	$sql = "SELECT id FROM users WHERE username='$username' LIMIT 1";
    $query = mysqli_query($db_conx, $sql);
    $uname_check = mysqli_num_rows($query);
    if (strlen($username) < 3 || strlen($username) > 16) {
	    echo '<strong style="color:#F00;">3 - 16 characters please</strong>';
	    exit();
    }
	if (is_numeric($username[0])) {
	    echo '<strong style="color:#F00;">Usernames must begin with a letter</strong>';
	    exit();
    }
    if ($uname_check < 1) {
	    echo '<strong style="color:#009900;">' . $username . ' is OK</strong>';
	    exit();
    } else {
	    echo '<strong style="color:#F00;">' . $username . ' is taken</strong>';
	    exit();
    }
}
?><?php
// Ajax calls this REGISTRATION code to execute
if(isset($_POST["u"])){
	// CONNECT TO THE DATABASE
	include_once("php_includes/db_conx.php");
	// GATHER THE POSTED DATA INTO LOCAL VARIABLES
	$u = preg_replace('#[^a-z0-9]#i', '', $_POST['u']);
	$e = mysqli_real_escape_string($db_conx, $_POST['e']);
	$p = mysqli_real_escape_string($db_conx, $_POST['p']);
	// GET USER IP ADDRESS
  $ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));
	// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
	$sql = "SELECT id FROM users WHERE username='$u' LIMIT 1";
  $query = mysqli_query($db_conx, $sql);
	$u_check = mysqli_num_rows($query);
	// -------------------------------------------
	$sql = "SELECT id FROM users WHERE email='$e' LIMIT 1";
  $query = mysqli_query($db_conx, $sql);
	$e_check = mysqli_num_rows($query);
	// FORM DATA ERROR HANDLING
	if($u == "" || $e == "" || $p == ""){
		echo "The form submission is missing values.";
        exit();
	} else if ($u_check > 0){
        echo "The username you entered is alreay taken";
        exit();
	} else if ($e_check > 0){
        echo "That email address is already in use in the system";
        exit();
	} else if (strlen($u) < 3 || strlen($u) > 16) {
        echo "Username must be between 3 and 16 characters";
        exit();
    } else if (is_numeric($u[0])) {
        echo 'Username cannot begin with a number';
        exit();
    } else {
	// END FORM DATA ERROR HANDLING
	    // Begin Insertion of data into the database
		// Hash the password and apply your own mysterious unique salt
		//$cryptpass = crypt($p);
		include_once ("php_includes/randStrGen.php");
		//$p_hash = randStrGen(20)."$cryptpass".randStrGen(20);
    $p_hash = sha1($p);
		// Add user info into the database table for the main site table
		$sql = "INSERT INTO users (username, email, password, ip, signup, lastlogin, notescheck)
		        VALUES('$u','$e','$p_hash','$ip',now(),now(),now())";
		$query = mysqli_query($db_conx, $sql);
		$uid = mysqli_insert_id($db_conx);
		// Establish their row in the useroptions table
		$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";
		$query = mysqli_query($db_conx, $sql);
		// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
    require_once('php_includes/dir_hash.php');
		if (!file_exists('user/'.dir_encrypt($u))) {
			mkdir('user/'.dir_encrypt($u), 0755);
		}
		// Email the user their activation link
		// $to = "$e";
		// $from = "auto_responder@yoursitename.com";
		// $subject = 'yoursitename Account Activation';
		// $message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>yoursitename Message</title></head><body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px; background:#333; font-size:24px; color:#CCC;"><a href="http://www.yoursitename.com"><img src="http://www.yoursitename.com/images/logo.png" width="36" height="30" alt="yoursitename" style="border:none; float:left;"></a>yoursitename Account Activation</div><div style="padding:24px; font-size:17px;">Hello '.$u.',<br /><br />Click the link below to activate your account when ready:<br /><br /><a href="http://www.yoursitename.com/activation.php?id='.$uid.'&u='.$u.'&e='.$e.'&p='.$p_hash.'">Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>'.$e.'</b></div></body></html>';
		// $headers = "From: $from\n";
    //     $headers .= "MIME-Version: 1.0\n";
    //     $headers .= "Content-type: text/html; charset=iso-8859-1\n";
		// mail($to, $subject, $message, $headers);
		echo "signup_success";
		exit();
	}
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="style/style.css">
<style type="text/css">
#signupform{
	margin-top:24px;
}
#signupform > div {
	margin-top: 12px;
}
#signupform > input,select {
	width: 200px;
	padding: 3px;
	background: #F3F9DD;
}
#signupbtn {
	font-size:18px;
	padding: 12px;
}
#terms {
	border:#CCC 1px solid;
	background: #F5F5F5;
	padding: 12px;
}
</style>
<script src="js/main.js"></script>
<script src="js/ajax.js"></script>
<script>
window.onload = function(){
  const usernameRef = _('username');
  if (usernameRef.addEventListener) {
      usernameRef.addEventListener("blur", checkusername);
      usernameRef.addEventListener("keyup", function(){
        restrict('username');
      });
  } else if (x.attachEvent) {
      usernameRef.attachEvent("blur", checkusername);
      usernameRef.attachEvent("keyup", function(){
        restrict('username');
      });
  }
};
const ValidateEmail = function (mail) {
  return !(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail));
}
const trim = function(txt) {
	return txt.replace(/^\s+|\s+$/g,"");
}

const restrict =  function (elem){
	var tf = _(elem);
	var rx = new RegExp;
	if(elem == "email"){
		rx = /[' "]/gi;
	} else if(elem == "username"){
		rx = /[^a-z0-9]/gi;
	}
	tf.value = tf.value.replace(rx, "");
}

function checkusername(){
	var u = trim(_("username").value);
	if(u != ""){
		_("unamestatus").innerHTML = 'checking ...';
		var ajax = ajaxObj("POST", "signup.php");
        ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
	            _("unamestatus").innerHTML = ajax.responseText;
	        }
        }
        ajax.send("usernamecheck="+u);
	}
}
const signup = function (){
	var u = trim(_("username").value);
	var e = trim(_("email").value);
	var p1 = _("pass1").value;
  var status = _("status");
	if(u == "" || e == "" || p1 == ""){
		status.innerHTML = "Fill out all of the form data";
    return ;
	} else if(ValidateEmail(e)){
    status.innerHTML = "Enter Valid Email Address";
    return ;
  } else if(p1.length > 16 || p1.length < 6){
    status.innerHTML = "Password length > 6 & < 16";
    return ;
  } else {
		_("signupbtn").style.display = "none";
		status.innerHTML = 'please wait ...';
		var ajax = ajaxObj("POST", "signup.php");
        ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
            //alert(ajax.responseText);
	            if(ajax.responseText != "signup_success"){
					status.innerHTML = ajax.responseText;
					_("signupbtn").style.display = "block";
				} else {
					window.scrollTo(0,0);
					_("signupform").innerHTML = "OK "+u+", check your email inbox and junk mail box at <u>"+e+"</u> in a moment to complete the sign up process by activating your account. You will not be able to do anything on the site until you successfully activate your account.";
				}
	        }
        }
        ajax.send("u="+u+"&e="+e+"&p="+p1);
	}
}

</script>
</head>
<body>
<?php include_once("view_components/template_pageTop.php"); ?>
<div id="pageMiddle">
  <h3>Sign Up Here</h3>
  <form name="signupform" id="signupform" onsubmit="return false;">
    <div>Username: </div>
    <input id="username" type="text" maxlength="16">
    <span id="unamestatus"></span>
    <div>Email Address:</div>
    <input id="email" type="text" maxlength="88">
    <div>Password:</div>
    <input id="pass1" type="password" maxlength="16">
    <br /><br />
    <button id="signupbtn" onclick="signup()">Create Account</button>
    <span id="status"></span>
  </form>
</div>
<?php include_once("view_components/template_pageBottom.php"); ?>
</body>
</html>
