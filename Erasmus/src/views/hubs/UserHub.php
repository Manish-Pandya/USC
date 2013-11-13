<?php
require_once '../top_view.php';
?>
<script src="../../js/users.js"></script>

<div class="navbar">
<ul class="nav pageMenu" style="min-height: 50px; background: #86b32d; color:white !important; padding: 4px 0 0 0; width:100%">
	<li class="span3">
		<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">User Hub</h2>	
	</li>
	<li>
		<a class=" btn" style="" data-toggle="modal"  href="#addUser" ><img src='../../img/add-user-icon.png'>Add User</a>
	</li>
</ul>

</div>
<span ng-app="userList" ng-controller="UserListController">
<table class="userList table table-striped table-hover">
<thead>
	<tr>
		<th>Edit User</th><th>Activate/Deactivate User</th><th>Name</th><th>LDAP ID</th><th>Email</th><th>Role</th>
	</tr>
</thead>
<tbody >
	<tr id="{{user.id}}" ng-repeat="user in users" ng-class="{edit: user.edit, notedit: user.notEdit}">
		<td ng-hide="user.edit"><a class="edit btn btn-large btn-primary" ng-click="editUser(user)">Edit</a></td><td ng-show="user.edit"><a class="edit btn btn-large btn-info" ng-click="saveUser(user)">Save</a></td>
		<td ng-hide="user.edit"><a class="btn btn-danger btn-large DeactivateeRow" href="#">Deactivate</a></td><td ng-show="user.edit"><a class="edit btn btn-large btn-danger" ng-click="cancelEdits(user)">Cancel</a></td>
		<td ng-hide="user.edit">{{user.name}}</td><td ng-show="user.edit"><input ng-model="userCopy.name"/></td>
		<td ng-hide="user.edit">{{user.ldap}}</td><td ng-show="user.edit"><input ng-model="userCopy.ldap"/></td>
		<td ng-hide="user.edit">{{user.email}}</td><td ng-show="user.edit"><input ng-model="userCopy.email"/></td>
		<td ng-hide="user.edit">{{user.role}}Administrator</td><td ng-show="user.edit"><input ng-init="Administrator" ng-model="userCopy.role"/></td>
	</tr>
</tbody>
</table>
<pre>{{users | json}}</pre>
</span>
<!--
<div ng-app="userList" ng-controller="UserListController">
	<ul >
		<li ng-repeat="user in users">
			<ul class="span12">
				<li class="span1"><a class="edit btn btn-large btn-primary" ng-click="editUser(user)">Edit</a></li>
				<li class="span2"><a class="btn btn-danger btn-large DeactivateeRow" href="#">Deactivate</a></li>
				<li class="span2" ng-hide="user.edit">{{user.name}}</li><li ng-show="user.edit"><input ng-model="userCopy.name"/></li>
				<li class="span2">bUserington</li>
				<li class="span2">{{user.email}}</li>
				<li class="span2">Administrator</li>
			</ul>
		</li>
	</ul>
</div>
-->
<!-- begin edit user modal dialogue
<div class="modal hide fade" id="editUser1">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Editing <span id="nameOfEditedUser">Bob Userington</span></h3>
  </div>
  <form style="padding:0; margin:0;" class="form-horizontal">
  <div class="modal-body">
 
  	<div class="control-group">
	    <label class="control-label" for="fName">First Name</label>
	    <div class="controls">
	      <input type="text" name="fName" id="fName" placeholder="Password" value="Bob">
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="lName">Last Name</label>
	    <div class="controls">
	      <input type="text" name="lName" id="lName" placeholder="Password" value="Userington">
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="email">Email</label>
	    <div class="controls">
	      <input type="text" name="email" id="email" placeholder="Password" value="bob@bob.bob">
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="role">Role</label>
	    <div class="controls">
	      <select name="role" id="role">
	      	<option>Administrator</option>
	      	<option>Inpsector</option>
	      	<option>Lab User</option>
	      	<option>Principle Investigator</option>
	      </select>
	    </div>
    </div>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large" id="saveChanges">Save changes</a>
  </div>
  </form>
</div> -->
<!-- end edit user modal dialogue -->


<!-- begin add new user modal dialogue 
<div class="modal hide fade" id="addUser">
	<div class="modal-header">
		<h3>Add a New User</h3>
	</div>
	<form style="padding:0; margin:0;" class="form-horizontal">
	<div class="modal-body">

	<div class="control-group">
	    <label class="control-label" for="fName">LDAP ID</label>
	    <div class="controls">
	      <input type="text" name="fName" id="ldapID" placeholder="" value="">
	    </div>
    </div>
    
	<div class="control-group">
	    <label class="control-label" for="fName">First Name</label>
	    <div class="controls">
	      <input type="text" name="fName" id="fNameNew"  >
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="lName">Last Name</label>
	    <div class="controls">
	      <input type="text" name="lNameNew" id="lNameNew" >
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="email">Email</label>
	    <div class="controls">
	      <input type="text" name="email" id="emailNew">
	    </div>
    </div>
    
    <div class="control-group">
	    <label class="control-label" for="role">Role</label>
	    <div class="controls">
	      <select name="role" id="role">
	      	<option>Administrator</option>
	      	<option>Inpsector</option>
	      	<option>Lab User</option>
	      	<option>Principle Investigator</option>
	      </select>
	    </div>
    </div>
		
	</div>
	 <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large">Create</a>
  </div>
</div>-->
<!-- end add new user modal dialogue -->
<script>
/*
$(document).ready(function(){
	var query = location.href.split('#');
	var anchorPart = query[1];
	if 	(anchorPart != null){
		$('#'+anchorPart).addClass('blue-border');
		$('#'+anchorPart+' td').addClass('shadow');
		$('#editUser1').modal('show');
		$('#fName').val('Davit');
		$('#lName').val('Mrelashvili');
		$('#email').val('dmrelashvili@sc.edu');
		$("#role").val("Principle Investigator");
		$('#nameOfEditedUser').text('Davit Mrelashvili');
		$("#saveChanges").attr('href', "PIHub.php")
	}
})

$('#ldapID').change(function(){
	$('#fNameNew').val('Robert');
	$('#lNameNew').val('Userington');
	$('#emailNew').val('ruserington@sc.edu');
})*/
</script>
<?php
require_once '../bottom_view.php';
?>