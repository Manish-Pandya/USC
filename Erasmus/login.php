<?php
include('src/views/top_view.php');
?>
<h2 style="padding:5px 0;">Welcome to the University of South Carolina Research Safety Management System</h2>
<form class="form form-horizontal" method="post" action="src/action.php" style="padding:20px; background:white;">
	<?php if ($_SESSION['errors'] != null){ ?>
		<div class="alert"><?php echo $_SESSION['errors']; ?></div>
	<? }
	?>
	<input type="hidden" name="action" value="loginAction">
	<div class="form-group">
	    <label for="LDAPID">Username</label>
	    <input type="text" class="form-control" name="ldapID" id="exampleInputEmail1" placeholder="Enter Username">
  	</div>
 	<div class="form-group">
	    <label for="exampleInputPassword1">Password</label>
	    <input type="password" name="password" class="form-control" id="password" placeholder="Password">
 	</div>
 	<div class="form-group" style="margin-top:20px;">
	    <button type="submit" name="submit" class="btn btn-large btn-success" id="password" style="padding:0 20px;">Login</button>
 	</div>
</form>