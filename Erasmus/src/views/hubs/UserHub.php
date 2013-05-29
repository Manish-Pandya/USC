<?php
require_once '../top_view.php';
?>
<div class="pageMenu" style="background: #86b32d; color:white; padding: 4px 0 0 0;">
<ul class="nav"  style="height:50px">
	<li class="span2">
		<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
		<h2 style="padding: 11px 0 5px 85px;">User Hub</h2>
	</li>
	<li class="divider-vertical"></li>
	<li>
		<a class="addUser"><img href="#addUser" data-toggle="modal" src='../../img/add-user-icon.png'>Add User</a>
	</li>
	
</ul>
</div>


<table class="userList table table-striped table-hover">
<thead>
	<tr>
		<th>Edit User</th><th>Activate/Deactivate User</th><th>Name</th><th>LDAP ID</th><th>Email</th><th>Role</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><a class="edit btn btn-large btn-primary" data-toggle="modal" href="#editUser1">Edit</a></td>
		<td><a class="btn btn-danger btn-large deactivateRow" href="#">Deactivate</a></td>
		<td>Bob Userington</td>
		<td>bUserington</td>
		<td>bob@bob.bob</td>
		<td>Administrator</td>
	</tr>
	<tr>
		<td><a class="edit btn btn-large btn-primary" data-toggle="modal" href="#editUser1">Edit</a></td>
		<td><a class="btn btn-large btn-danger deactivateRow" href="#">Deactivate</a></td>
		<td>Beth Userington</td>
		<td>bethUserington</td>
		<td>bob@bob.bob</td>
		<td>Principal Investigator</td>
	</tr>
	<tr>
		<td><a class="edit btn btn-large btn-primary" data-toggle="modal" href="#editUser1">Edit</a></td>
		<td><a class="btn btn-danger btn-large deactivateRow" href="#">Deactivate</a></td>
		<td>Jane Doe</td>
		<td>jDoe</td>
		<td>bob@bob.bob</td>
		<td>Lab User</td>
	</tr>
</tbody>
</table>

<!-- begin edit user modal dialogue -->
<div class="modal hide fade" id="editUser1">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Editing Bob Userington</h3>
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
    <a href="#" class="btn" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary">Save changes</a>
  </div>
  </form>
</div>
<!-- end edit user modal dialogue -->

<!-- begin add new user modal dialogue -->
<div class="modal hide fade" id="addUser">

</div>
<!-- end add new user modal dialogue -->
<?php
require_once '../bottom_view.php';
?>