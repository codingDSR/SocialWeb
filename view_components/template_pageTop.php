<?php
// It is important for any file that includes this file, to have
// check_login_status.php included at its very top.
$envelope = '<img src="images/note_dead.jpg" width="22" height="12" alt="Notes" title="This envelope is for logged in members">';
$loginLink = '<a href="login.php">Log In</a> &nbsp; | &nbsp; <a href="signup.php">Sign Up</a>';
if($user_ok == true) {
	$sql = "SELECT notescheck FROM users WHERE username='$log_username' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_row($query);
	$notescheck = $row[0];
	$sql = "SELECT id FROM notifications WHERE user_id='$log_id' AND date_time > '$notescheck' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$numrows = mysqli_num_rows($query);
    if ($numrows == 0) {
		$envelope = '<a href="notifications.php" title="Your notifications and friend requests"><img src="images/note_still.jpg" width="22" height="12" alt="Notes"></a>';
    } else {
		$envelope = '<a href="notifications.php" title="You have new notifications"><img src="images/note_flash.gif" width="22" height="12" alt="Notes"></a>';
	}
    $loginLink = '<a href="user.php?u='.$log_username.'">'.$log_username.'</a> &nbsp; | &nbsp; <a href="logout.php">Log Out</a>';
}
?>
<link rel="stylesheet" href="style/style.css">  
    <nav class="navbar navbar-default no-margin navbar-fixed-top">
    <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header fixed-brand">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"  id="menu-toggle">
                      <span class="glyphicon glyphicon-th-large" aria-hidden="true"></span>
                    </button>
                    <a class="navbar-brand" href="#"><i class="fa fa-rocket fa-4"></i> SEEGATESITE</a>        
                </div><!-- navbar-header-->

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <ul class="nav navbar-nav">
                                <li class="active" ><button class="navbar-toggle collapse in" data-toggle="collapse" id="menu-toggle-2"> <span class="glyphicon glyphicon-th-large" aria-hidden="true"></span></button></li>
                            </ul>
                </div><!-- bs-example-navbar-collapse-1 -->
    </nav>
    <div id="wrapper" class="toggled">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav nav-pills nav-stacked" id="menu">

<!--                 <li class="active">
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-pencil-square-o fa-stack-1x"></i></span> Post Status</a>
                       <ul class="nav-pills nav-stacked" style="list-style-type:none;">
                        <li><a href="#">link1</a></li>
                        <li><a href="#">link2</a></li>
                    </ul>
                </li> -->
 <!--                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-flag fa-stack-1x "></i></span> Shortcut</a>
                    <ul class="nav-pills nav-stacked" style="list-style-type:none;">
                        <li><a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-flag fa-stack-1x "></i></span>link1</a></li>
                        <li><a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-flag fa-stack-1x "></i></span>link2</a></li>

                    </ul>

                </li> -->
                <li class="row">
                    <div style="float:left;padding-left:15px;">
                        <?php 
                            $res = mysqli_query($db_conx,"SELECT avatar FROM users WHERE id='$log_id' LIMIT 1");
                            while($row=mysqli_fetch_array($res)){
                                $log_user_avatar = $row["avatar"];
                            }
                            $log_profile_pic = '<img src="user/'.dir_encrypt($log_username).'/'.$log_user_avatar.'" alt="'.$u.'">';
                            if($avatar == NULL){
                                //$profile_pic = '<img src="images/avatardefault.jpg">';
                                $log_profile_pic = '<img src="images/avatardefault.jpg" alt="'.$user1.'">';
                            }   
                            echo $log_profile_pic;                         
                        ?>
                    </div>
                    <div style="display:inline;">
                      <h3><?php echo $log_username; ?></h3>
                    </div>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-user fa-stack-1x "></i></span>My Profile</a>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-pencil-square-o fa-stack-1x "></i></span>Post Status</a>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-globe fa-stack-1x "></i></span>Notifications</a>
                </li>                
                <li>
                    <a href="#"> <span class="fa-stack fa-lg pull-left"><i class="fa fa-user-plus fa-stack-1x "></i></span>Friend Requests</a>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-search fa-stack-1x "></i></span>Search</a>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-wrench fa-stack-1x "></i></span>Settings</a>
                </li>
                <li>
                    <a href="#"><span class="fa-stack fa-lg pull-left"><i class="fa fa-power-off fa-stack-1x "></i></span>Logout</a>
                </li>                
            </ul>
        </div><!-- /#sidebar-wrapper -->
        <!-- Page Content -->
<!--         <div id="page-content-wrapper">
            <div class="container-fluid xyz">
                <div class="row">
                    <div class="col-lg-12">
                        <h1>Simple Sidebar With Bootstrap 3 by <a href="http://seegatesite.com/create-simple-cool-sidebar-menu-with-bootstrap-3/" >Seegatesite.com</a></h1>
                        <p>This sidebar is adopted from start bootstrap simple sidebar startboostrap.com, which I modified slightly to be more cool. For tutorials and how to create it , you can read from my site here <a href="http://seegatesite.com/create-simple-cool-sidebar-menu-with-bootstrap-3/">create cool simple sidebar menu with boostrap 3</a></p>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- /#page-content-wrapper -->






