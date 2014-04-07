<?php
require_once '../top_view.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/piHub.js"></script>
<span ng-app="piHub" ng-controller="piHubMainController">
<div class="navbar">
<ul class="nav pageMenu bg-color-blue" style="min-height: 50px; background: #86b32d; color:white !important; padding: 4px 0 0 0; width:100%">
	<li class="span3">
		<img src="../../../img/pi-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">PI Hub</h2>	
	</li>
	<div style="clear:both; height:0; font-size:0; ">&nbsp;</div>
</ul>
<div class="whitebg" style="padding:70px 70px;">
	<div id="editPiForm" class="">
		<form class="form">
		     <div class="control-group">
		       <label class="control-label" for="name"><h3 style="font-weight:bold">Select A Principal Investigator</h3></label>
		       <div class="controls">
		       <span ng-show="!doneLoadingAll">
		         <input class="span4" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
		       	<img class="" style="height:23px; margin:-9px 0 0 -35px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		       </span>
		       <span ng-hide="!doneLoadingAll">
		       	<input style="" class="span4"  typeahead-on-select='onSelectPi($item, $model, $label)' type="text" ng-model="customSelected" placeholder="Add a PI" typeahead="pi as (pi.User.Name+' '+pi.User.Username) for pi in PIs | filter:$viewValue">
		       </span>
		      </div>
		     </div>
		</form>
	</div>
	<span ng-hide="!PI">
		<div class="btn-group" id="piButtons">
			<a href="UserHub.php#3" id="editPI" class="btn btn-large btn-primary" style="margin-left: 0;
"><i class="icon-pencil"></i>Edit PI</a>
			<a ng-click="setRoute('rooms')" id="editPI" class="btn btn-large btn-info"><i class="icon-enter"></i>Manage Rooms</a>
			<a ng-click="setRoute('personnel')" class="btn btn-large btn-success"><i class="icon-user-2"></i>Manage Lab Users</a>
			<a ng-click="setRoute('departments')" class="btn btn-large btn-primary"><i class="icon-tree-view"></i>Manage Deparments</a>
		<!--	<a ng-click="setRoute('safetyContacts')" class="btn btn-large btn-success"><i class="icon-phone"></i>Manage Safety Contacts</a><!--<a href="#specialHazards" id="editPI" class="btn btn-large btn-warning">Manage Special Haz-->
		</div>
	</span>
	<h1 ng-hide="!PI">Principle Investigator:  {{PI.User.Name}}</h1>
	<div class="loading" ng-show='!PI' >
		<span ng-hide="noPiSet">
		  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Selected Principal Investigator...
		</span>
	</div>
	<span ng-hide="!PI">
		<ng-view></ng-view>
	</span>




<!--
<table class="userList table table-striped table-hover list" id="safetyContacts">
<thead>
	<tr><th colspan="5"><h2 class="alert alert-success">Davit Mrelashvili's Safety Contacts</h2></th></tr>
	<tr><th colspan="5"><a class="btn-success btn-large btn" style="" data-toggle="modal"  href="#addUser" ><img src='../../img/add-user-icon.png'>Add Safety Contact</a></th></tr>
	<tr>
		<th width="20%">Edit Safety Contact</th><th width="20%">Remove Room</th><th width="20%">Name</th><th width="20%">LDAP ID</th><th width="20%">Email</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Edit</a></td>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td>Bob Userington</td>
		<td>bUserington</td>
		<td>bob@bob.bob</td>
	</tr>
	<tr>
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Edit</a></td>
		<td><a class="btn btn-large btn-danger removeRow" href="#">Remove</a></td>
		<td>Beth Userington</td>
		<td>bethUserington</td>
		<td>bob@bob.bob</td>

	</tr>
	<tr>
		<td width="20%"><a class="edit btn btn-large btn-primary"  href="UserHub.php">Edit</a></td>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td>Jane Doe</td>
		<td>jDoe</td>
		<td>bob@bob.bob</td>

	</tr>
</tbody>
</table>
<div id="departments" class="table">
	<h2 class="alert" style="background: rgba(182, 182, 182, 0.7); border-color:rgba(182, 182, 182, 0.7);">Davit Mrelashvili's Departments</h2>
	<input class="typeahead" name="buildings" id="departmentsField" autocomplete="off" type="text" style="margin: 0 auto;" data-provide="typeahead" data-items="5000" data-source='["Animal Resource Facilities","Biological Sciences","Cell Biology and Anatomy","Chemical Engineering","Chemistry and Biochemistry","Civil and Environmental Engineering","Earth and Ocean Sciences","Electrical Engineering","Environmental Health and Safety","Environmental Health Sciences","Electron Microscopy Center","Epidemiology and Biostatistics","Exercise Science","Mechanical Engineering","Pathology, Microbiology and Immunology","Pharmaceutical and Biomedical Sciences","Pharmacology, Physiology and Neuroscience","Physics and Astronomy","Psychology","Surgery","Thompson Student Health Lab"]'>
	<a class="btn" id="addDepartment">Add Department</a>
	<ul id="departmentList">
		<li>Biological Sciences</li>
		<li>Cell Biology and Anatomy</li>
		<li>Pathology, Microbiology and Immunology</li>
		<li>Surgery</li>
</ul>
</div>

<div id="specialHazards" class="table">
	<h2 class="alert" style="background: #faa732; border-color: #faa732;">Davit Mrelashvili's Special Hazards</h2>
	<input class="typeahead" name="buildings" id="specialHazardsField" autocomplete="off" type="text" style="margin: 0 auto;" data-provide="typeahead" data-items="5000" data-source='["Animal Resource Facilities","Biological Sciences","Cell Biology and Anatomy","Chemical Engineering","Chemistry and Biochemistry","Civil and Environmental Engineering","Earth and Ocean Sciences","Electrical Engineering","Environmental Health and Safety","Environmental Health Sciences","Electron Microscopy Center","Epidemiology and Biostatistics","Exercise Science","Mechanical Engineering","Pathology, Microbiology and Immunology","Pharmaceutical and Biomedical Sciences","Pharmacology, Physiology and Neuroscience","Physics and Astronomy","Psychology","Surgery","Thompson Student Health Lab"]'>
	<a class="btn btn-warning" id="addSpecialHazard">Add Special Hazard</a>
	<ul id="specialHazardList">
		<li>Reserpine</li>
		<li>Dinitrophenol</li>
		<li>Ethyl carbamate (Urethane)</li>
		<li>Hexachlorocyclohexanes</li>
</ul>
</div>

</div>
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

  </div>
  <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large">Save changes</a>
  </div>
  </form>
</div>
<!-- end edit user modal dialogue -->


<!-- begin add new user modal dialogue 
<div class="modal hide fade" id="addUser">
	<div class="modal-header">
		<h3>Add a New Safety Contact</h3>
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
    
		
	</div>
	 <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large">Create</a>
  </div>
</div>
-->
<!-- SPECIAL HAZARDS 
<table class="roomList table table-striped table-hover list" id="rooms">
<thead>
	<tr><td colspan="5"><h2 class="alert" style="background: #49afcd; border-color:#49afcd;">Davit Mrelashvili's Rooms</h2></td></tr>
	<tr><td colspan="5"><a href="#roomModal" data-toggle="modal" class="btn btn-info btn-large">Add Room</a><a href="#roomModal" data-toggle="modal" class="btn btn-primary btn-large">Create Room</a></tr>
	<tr>
		<th>Remove Room</th><th>Campus</th><th>Building</th><th>Room Number</th><th>Show Hazards</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td width="25%">School of Medicine, VA Campus</td>
		<td width="25%">Building 4</td>
		<td width="25%">207</td>
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Show Hazards</a></td>
	</tr>
	<tr>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td>School of Medicine, VA Campus</td>
		<td>Building 4</td>
		<td>212</td>
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Show Hazards</a></td>
	</tr>
	<tr>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td>School of Medicine, VA Campus</td>
		<td>Building 4</td>
		<td>200</td>
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Show Hazards</a></td>
	</tr>
	<tr>
		<td><a class="btn btn-danger btn-large removeRow" href="#">Remove</a></td>
		<td>School of Medicine, VA Campus</td>
		<td>Building 4</td>
		<td>217</td>	
		<td><a class="edit btn btn-large btn-primary"  href="UserHub.php">Show Hazards</a></td>
	</tr>
</tbody>
</table>-->
<!-- end add new user modal dialogue 
-->
<!-- begin add room modal dialogue 
<div class="modal hide fade" id="roomModal">
	<div class="modal-header">
		<h3>Add a Room</h3>
	</div>
	<form style="padding:0; margin:0;" class="form-horizontal">
	<div class="modal-body">

	<div class="control-group">
	    <label class="control-label" for="fName">Building</label>
	    <div class="controls">
	      <input class="typeahead" name="buildings" id="buildings" autocomplete="off" type="text" style="margin: 0 auto;" data-provide="typeahead" data-items="5000" data-source="">
	    </div>
    </div>
    
	<div class="control-group">
	    <label class="control-label" for="fName">Room #</label>
	    <div class="controls">
	      <input type="text" name="fName" id="fNameNew"  >
	    </div>
    </div>
    
		
	</div>
	 <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large">Create</a>
  </div>
  </form>
</div>
</span>

</td>-->
</span>
<script>
/*
$(document).ready(function(){
	$('#specialHazards').hide();
	$('#departments').hide();
	$('table, form, #safetyContacts').hide();
	<?php 
		if (isset($_GET)){
			foreach ($_GET as $key=>$val){?>
					$("#<?php echo $key?>").show();
		<?php }
		}
	?>
});
$(document.body).on("click", "#piButtons a", function(e){
	
	if($(this).attr('id') != 'editPI'){
		e.preventDefault();
	}
	id = $(this).attr("href");
	console.log("adsfa");
	$('.table').hide();
	$(id).toggle();
});

$(document.body).on("click", "#addDepartment", function(e){
	$('#departmentList').append('<li>'+$('#departmentsField').val()+'</li>');
});

$(document.body).on("click", "#addSpecialHazard", function(e){
	$('#specialHazardList').append('<li>'+$('#specialHazardsField').val()+'</li>');
});

$('.removeRow').click(function(){
	$(this).parents('tr').hide();
});

var buildingsArray = [


                  	{
                  		"name": "(Aiken) ALAN B. MILLER NURSING BUILDING",
                  		"key_id": "407",
                  		"bldg_id": "926"
                  	},



                  	{
                  		"name": "(Aiken) ANCILL BUILDING CONCESSIONS",
                  		"key_id": "393",
                  		"bldg_id": "907"
                  	},



                  	{
                  		"name": "(Aiken) BUSINESS & EDUCATION BLDG",
                  		"key_id": "403",
                  		"bldg_id": "916"
                  	},



                  	{
                  		"name": "(Aiken) CHILD DEVELOPMENT CENTER",
                  		"key_id": "398",
                  		"bldg_id": "911"
                  	},



                  	{
                  		"name": "(Aiken) CONVOCATION CENTER",
                  		"key_id": "406",
                  		"bldg_id": "921"
                  	},



                  	{
                  		"name": "(Aiken) ETHERREDGE CENTER",
                  		"key_id": "395",
                  		"bldg_id": "909"
                  	},



                  	{
                  		"name": "(Aiken) GREGG-GRANITEVILLE LIBRARY",
                  		"key_id": "389",
                  		"bldg_id": "904"
                  	},



                  	{
                  		"name": "(Aiken) HUMANITIES & SOCIAL SCIENCES",
                  		"key_id": "392",
                  		"bldg_id": "906"
                  	},



                  	{
                  		"name": "(Aiken) NATATORIUM",
                  		"key_id": "391",
                  		"bldg_id": "905A"
                  	},



                  	{
                  		"name": "(Aiken) PACER COMMONS",
                  		"key_id": "410",
                  		"bldg_id": "928"
                  	},



                  	{
                  		"name": "(Aiken) PACER CROSSINGS",
                  		"key_id": "411",
                  		"bldg_id": "929"
                  	},



                  	{
                  		"name": "(Aiken) PACER DOWNS APARTMENTS",
                  		"key_id": "408",
                  		"bldg_id": "927"
                  	},



                  	{
                  		"name": "(Aiken) PACER DOWNS OFFICE MARKET",
                  		"key_id": "409",
                  		"bldg_id": "927A"
                  	},



                  	{
                  		"name": "(Aiken) PICKENS SALLEY HOUSE",
                  		"key_id": "399",
                  		"bldg_id": "912"
                  	},



                  	{
                  		"name": "(Aiken) PRESSBOX OFFICE",
                  		"key_id": "400",
                  		"bldg_id": "913"
                  	},



                  	{
                  		"name": "(Aiken) ROBERT E PENLAND ADM & CLS",
                  		"key_id": "388",
                  		"bldg_id": "903"
                  	},



                  	{
                  		"name": "(Aiken) ROBERTO HERNANDEZ STADIUM",
                  		"key_id": "405",
                  		"bldg_id": "920"
                  	},



                  	{
                  		"name": "(Aiken) RUTH PATRICK SCIENCE",
                  		"key_id": "402",
                  		"bldg_id": "915"
                  	},



                  	{
                  		"name": "(Aiken) SCIENCE BUILDING",
                  		"key_id": "397",
                  		"bldg_id": "910"
                  	},



                  	{
                  		"name": "(Aiken) SOCCER BUILDING",
                  		"key_id": "404",
                  		"bldg_id": "918"
                  	},



                  	{
                  		"name": "(Aiken) SOFTBALL TEAM ROOM",
                  		"key_id": "401",
                  		"bldg_id": "914"
                  	},



                  	{
                  		"name": "(Aiken) STORAGE BUILDING",
                  		"key_id": "396",
                  		"bldg_id": "909A"
                  	},



                  	{
                  		"name": "(Aiken) STUDENT CENTER",
                  		"key_id": "390",
                  		"bldg_id": "905"
                  	},



                  	{
                  		"name": "(Aiken) SUPPLY MAINTENANCE",
                  		"key_id": "394",
                  		"bldg_id": "908"
                  	},



                  	{
                  		"name": "(Aiken) TENNIS OFFICE",
                  		"key_id": "387",
                  		"bldg_id": "902"
                  	},



                  	{
                  		"name": "(Aiken) TRAILER 1",
                  		"key_id": "386",
                  		"bldg_id": "901"
                  	},



                  	{
                  		"name": "(Beaufort) ART STUDIO",
                  		"key_id": "368",
                  		"bldg_id": "804"
                  	},



                  	{
                  		"name": "(Beaufort) BARNWELL HOUSE",
                  		"key_id": "382",
                  		"bldg_id": "811"
                  	},



                  	{
                  		"name": "(Beaufort) BEAUFORT COLLEGE BUILDING",
                  		"key_id": "364",
                  		"bldg_id": "801"
                  	},



                  	{
                  		"name": "(Beaufort) CAMPUS CENTER",
                  		"key_id": "383",
                  		"bldg_id": "812"
                  	},



                  	{
                  		"name": "(Beaufort) CENTER FOR THE ARTS",
                  		"key_id": "371",
                  		"bldg_id": "807"
                  	},



                  	{
                  		"name": "(Beaufort) COASTAL ZONE ED CENTER",
                  		"key_id": "373",
                  		"bldg_id": "809"
                  	},



                  	{
                  		"name": "(Beaufort) ELLIOTT HOUSE",
                  		"key_id": "365",
                  		"bldg_id": "801A"
                  	},



                  	{
                  		"name": "(Beaufort) GRAYSON FACULTY HOUSE",
                  		"key_id": "369",
                  		"bldg_id": "805"
                  	},



                  	{
                  		"name": "(Beaufort) GREENHOUSE",
                  		"key_id": "379",
                  		"bldg_id": "810E"
                  	},



                  	{
                  		"name": "(Beaufort) HAMILTON HOUSE",
                  		"key_id": "372",
                  		"bldg_id": "808"
                  	},



                  	{
                  		"name": "(Beaufort) HARGRAY BUILDING",
                  		"key_id": "374",
                  		"bldg_id": "810"
                  	},



                  	{
                  		"name": "(Beaufort) HILTON HEAD ANNEX",
                  		"key_id": "385",
                  		"bldg_id": "818A"
                  	},



                  	{
                  		"name": "(Beaufort) HILTON HEAD TP",
                  		"key_id": "384",
                  		"bldg_id": "818"
                  	},



                  	{
                  		"name": "(Beaufort) LIBRARY SOUTH",
                  		"key_id": "376",
                  		"bldg_id": "810B"
                  	},



                  	{
                  		"name": "(Beaufort) MAINTENANCE BUILDING",
                  		"key_id": "377",
                  		"bldg_id": "810C"
                  	},



                  	{
                  		"name": "(Beaufort) MARINE SCIENCE BUILDING",
                  		"key_id": "370",
                  		"bldg_id": "806"
                  	},



                  	{
                  		"name": "(Beaufort) PRITCHARDS ISLAND",
                  		"key_id": "363",
                  		"bldg_id": "223"
                  	},



                  	{
                  		"name": "(Beaufort) PUMP HOUSE",
                  		"key_id": "378",
                  		"bldg_id": "810D"
                  	},



                  	{
                  		"name": "(Beaufort) RECTORY",
                  		"key_id": "367",
                  		"bldg_id": "802A"
                  	},



                  	{
                  		"name": "(Beaufort) SANDSTONE BUILDING",
                  		"key_id": "366",
                  		"bldg_id": "802"
                  	},



                  	{
                  		"name": "(Beaufort) SCIENCE & TECHNOLOGY BUILDING",
                  		"key_id": "375",
                  		"bldg_id": "810A"
                  	},



                  	{
                  		"name": "(Beaufort) STORAGE BUILDING",
                  		"key_id": "380",
                  		"bldg_id": "810F"
                  	},



                  	{
                  		"name": "(Beaufort) VEHICLE STORAGE BUILDING",
                  		"key_id": "381",
                  		"bldg_id": "810G"
                  	},



                  	{
                  		"name": "1000 Catawba Street",
                  		"key_id": "224",
                  		"bldg_id": "630"
                  	},



                  	{
                  		"name": "101 S. Bull Street",
                  		"key_id": "111",
                  		"bldg_id": "202"
                  	},



                  	{
                  		"name": "1034 Key Road",
                  		"key_id": "211",
                  		"bldg_id": "209A"
                  	},



                  	{
                  		"name": "105 S. Bull Street",
                  		"key_id": "112",
                  		"bldg_id": "202a"
                  	},



                  	{
                  		"name": "109 S. Bull Street",
                  		"key_id": "113",
                  		"bldg_id": "202b"
                  	},



                  	{
                  		"name": "1101 George Rogers Blvd.",
                  		"key_id": "210",
                  		"bldg_id": "209"
                  	},



                  	{
                  		"name": "1200 Catawba Street",
                  		"key_id": "180",
                  		"bldg_id": "171"
                  	},



                  	{
                  		"name": "1223 Catawba Street",
                  		"key_id": "187",
                  		"bldg_id": "163A"
                  	},



                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"key_id": "173",
                  		"bldg_id": "139"
                  	},



                  	{
                  		"name": "1301 Gervais Street",
                  		"key_id": "230",
                  		"bldg_id": "636"
                  	},



                  	{
                  		"name": "1320 Main Street",
                  		"key_id": "228",
                  		"bldg_id": "634"
                  	},



                  	{
                  		"name": "1338 Pickens Street",
                  		"key_id": "226",
                  		"bldg_id": "632"
                  	},



                  	{
                  		"name": "1420 Henderson Street",
                  		"key_id": "222",
                  		"bldg_id": "628"
                  	},



                  	{
                  		"name": "1430 Senate Street",
                  		"key_id": "229",
                  		"bldg_id": "635"
                  	},



                  	{
                  		"name": "1501 Senate Street",
                  		"key_id": "96",
                  		"bldg_id": "28"
                  	},



                  	{
                  		"name": "1527 Senate Street",
                  		"key_id": "158",
                  		"bldg_id": "28a"
                  	},



                  	{
                  		"name": "1600 Gervais Street",
                  		"key_id": "227",
                  		"bldg_id": "633"
                  	},



                  	{
                  		"name": "1600 Hampton Street",
                  		"key_id": "92",
                  		"bldg_id": "29"
                  	},



                  	{
                  		"name": "1600 Hampton Street Annex",
                  		"key_id": "159",
                  		"bldg_id": "29a"
                  	},



                  	{
                  		"name": "1600 Hampton Street Garage",
                  		"key_id": "114",
                  		"bldg_id": "29b"
                  	},



                  	{
                  		"name": "1620 Gervais Street",
                  		"key_id": "223",
                  		"bldg_id": "629"
                  	},



                  	{
                  		"name": "1629 Pendleton Street",
                  		"key_id": "1",
                  		"bldg_id": "35"
                  	},



                  	{
                  		"name": "1710 College Street",
                  		"key_id": "78",
                  		"bldg_id": "47"
                  	},



                  	{
                  		"name": "1714 College Street",
                  		"key_id": "75",
                  		"bldg_id": "57"
                  	},



                  	{
                  		"name": "1719 Green Street",
                  		"key_id": "95",
                  		"bldg_id": "53"
                  	},



                  	{
                  		"name": "1723-25 Green Street",
                  		"key_id": "94",
                  		"bldg_id": "44"
                  	},



                  	{
                  		"name": "1728 College Street",
                  		"key_id": "80",
                  		"bldg_id": "58"
                  	},



                  	{
                  		"name": "1731 College Street",
                  		"key_id": "121",
                  		"bldg_id": "38"
                  	},



                  	{
                  		"name": "1800 Gervais Street",
                  		"key_id": "225",
                  		"bldg_id": "631"
                  	},



                  	{
                  		"name": "1819 Pendleton Street",
                  		"key_id": "73",
                  		"bldg_id": "41"
                  	},



                  	{
                  		"name": "201 S. Marion Street",
                  		"key_id": "110",
                  		"bldg_id": "203"
                  	},



                  	{
                  		"name": "300 Main Street",
                  		"key_id": "157",
                  		"bldg_id": "170"
                  	},



                  	{
                  		"name": "503 Main Street",
                  		"key_id": "155",
                  		"bldg_id": "150"
                  	},



                  	{
                  		"name": "508 Assembly Street",
                  		"key_id": "156",
                  		"bldg_id": "153"
                  	},



                  	{
                  		"name": "513 Pickens Street",
                  		"key_id": "181",
                  		"bldg_id": "129"
                  	},



                  	{
                  		"name": "514 Main Street (UTS Annex)",
                  		"key_id": "175",
                  		"bldg_id": "145"
                  	},



                  	{
                  		"name": "516 Main Street",
                  		"key_id": "197",
                  		"bldg_id": "144"
                  	},



                  	{
                  		"name": "518 Main Street",
                  		"key_id": "196",
                  		"bldg_id": "143"
                  	},



                  	{
                  		"name": "700 College Street",
                  		"key_id": "102",
                  		"bldg_id": "83a"
                  	},



                  	{
                  		"name": "707 Catawba Street - Film Library",
                  		"key_id": "189",
                  		"bldg_id": "619"
                  	},



                  	{
                  		"name": "718 Devine",
                  		"key_id": "103",
                  		"bldg_id": "226"
                  	},



                  	{
                  		"name": "720 College Street/Pearle Labs",
                  		"key_id": "100",
                  		"bldg_id": "83b"
                  	},



                  	{
                  		"name": "Anderson Academic Enrichment Center",
                  		"key_id": "200",
                  		"bldg_id": "189"
                  	},



                  	{
                  		"name": "Archaeology Annex",
                  		"key_id": "53",
                  		"bldg_id": "155"
                  	},



                  	{
                  		"name": "Athletic Practice Facility",
                  		"key_id": "90",
                  		"bldg_id": "84a"
                  	},



                  	{
                  		"name": "Athletics Office Annex",
                  		"key_id": "165",
                  		"bldg_id": "206"
                  	},



                  	{
                  		"name": "Athletics Village Parking Garage",
                  		"key_id": "202",
                  		"bldg_id": "191"
                  	},



                  	{
                  		"name": "Athletics Village Softball Stadium",
                  		"key_id": "203",
                  		"bldg_id": "192"
                  	},



                  	{
                  		"name": "Athletics Village Tennis Facility",
                  		"key_id": "204",
                  		"bldg_id": "192A"
                  	},



                  	{
                  		"name": "Baptist Student Union",
                  		"key_id": "69",
                  		"bldg_id": "610"
                  	},



                  	{
                  		"name": "Barnwell College",
                  		"key_id": "28",
                  		"bldg_id": "18"
                  	},



                  	{
                  		"name": "Bates House",
                  		"key_id": "10",
                  		"bldg_id": "160"
                  	},



                  	{
                  		"name": "Bates House Cafeteria",
                  		"key_id": "13",
                  		"bldg_id": "161"
                  	},



                  	{
                  		"name": "Bates House West",
                  		"key_id": "14",
                  		"bldg_id": "162"
                  	},



                  	{
                  		"name": "Belser Arboretum",
                  		"key_id": "212",
                  		"bldg_id": "212"
                  	},



                  	{
                  		"name": "Benson",
                  		"key_id": "55",
                  		"bldg_id": "159"
                  	},



                  	{
                  		"name": "Blatt Physical Education Center",
                  		"key_id": "172",
                  		"bldg_id": "138"
                  	},



                  	{
                  		"name": "Blossom Street Garage",
                  		"key_id": "47",
                  		"bldg_id": "136"
                  	},



                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"key_id": "45",
                  		"bldg_id": "134"
                  	},



                  	{
                  		"name": "Bull Street Garage",
                  		"key_id": "37",
                  		"bldg_id": "117"
                  	},



                  	{
                  		"name": "Callcott House",
                  		"key_id": "79",
                  		"bldg_id": "45"
                  	},



                  	{
                  		"name": "Callcott Social Science Center",
                  		"key_id": "154",
                  		"bldg_id": "115"
                  	},



                  	{
                  		"name": "Capstone House",
                  		"key_id": "170",
                  		"bldg_id": "39"
                  	},



                  	{
                  		"name": "Carolina  Coliseum",
                  		"key_id": "67",
                  		"bldg_id": "84"
                  	},



                  	{
                  		"name": "Carolina Baseball Stadium",
                  		"key_id": "193",
                  		"bldg_id": "235"
                  	},



                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"key_id": "2",
                  		"bldg_id": "175"
                  	},



                  	{
                  		"name": "Cliff Apartments",
                  		"key_id": "15",
                  		"bldg_id": "165"
                  	},



                  	{
                  		"name": "Close/Hipp Building",
                  		"key_id": "169",
                  		"bldg_id": "36"
                  	},



                  	{
                  		"name": "Coker Life Sciences Building",
                  		"key_id": "9",
                  		"bldg_id": "100"
                  	},



                  	{
                  		"name": "Colonial Center",
                  		"key_id": "124",
                  		"bldg_id": "158"
                  	},



                  	{
                  		"name": "Columbia Hall",
                  		"key_id": "71",
                  		"bldg_id": "40"
                  	},



                  	{
                  		"name": "Currell Annex",
                  		"key_id": "31",
                  		"bldg_id": "68"
                  	},



                  	{
                  		"name": "Currell College",
                  		"key_id": "29",
                  		"bldg_id": "66"
                  	},



                  	{
                  		"name": "Davis College",
                  		"key_id": "22",
                  		"bldg_id": "62"
                  	},



                  	{
                  		"name": "DeSaussure College",
                  		"key_id": "16",
                  		"bldg_id": "11"
                  	},



                  	{
                  		"name": "Devine Street Research Center",
                  		"key_id": "116",
                  		"bldg_id": "228"
                  	},



                  	{
                  		"name": "Discovery Building",
                  		"key_id": "191",
                  		"bldg_id": "230"
                  	},



                  	{
                  		"name": "Discovery Parking Garage",
                  		"key_id": "219",
                  		"bldg_id": "231"
                  	},



                  	{
                  		"name": "Drayton Hall",
                  		"key_id": "64",
                  		"bldg_id": "78"
                  	},



                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"key_id": "118",
                  		"bldg_id": "89"
                  	},



                  	{
                  		"name": "East Quadrangle",
                  		"key_id": "153",
                  		"bldg_id": "135a"
                  	},



                  	{
                  		"name": "Energy Facility East",
                  		"key_id": "84",
                  		"bldg_id": "52"
                  	},



                  	{
                  		"name": "Energy Facility North",
                  		"key_id": "218",
                  		"bldg_id": "229"
                  	},



                  	{
                  		"name": "Energy Facility South",
                  		"key_id": "32",
                  		"bldg_id": "185"
                  	},



                  	{
                  		"name": "Energy Facility West",
                  		"key_id": "49",
                  		"bldg_id": "140"
                  	},



                  	{
                  		"name": "Engineering Technical Fabrication Facility",
                  		"key_id": "104",
                  		"bldg_id": "163"
                  	},



                  	{
                  		"name": "Eugene Stone III Stadium",
                  		"key_id": "39",
                  		"bldg_id": "187"
                  	},



                  	{
                  		"name": "Facilities Management Center",
                  		"key_id": "99",
                  		"bldg_id": "83"
                  	},



                  	{
                  		"name": "Farmers Market Green Shed",
                  		"key_id": "186",
                  		"bldg_id": "240"
                  	},



                  	{
                  		"name": "Field House",
                  		"key_id": "34",
                  		"bldg_id": "186"
                  	},



                  	{
                  		"name": "Film Library",
                  		"key_id": "51",
                  		"bldg_id": "151"
                  	},



                  	{
                  		"name": "Flinn Hall",
                  		"key_id": "81",
                  		"bldg_id": "6"
                  	},



                  	{
                  		"name": "Fort Jackson Storage Facility",
                  		"key_id": "188",
                  		"bldg_id": "705A"
                  	},



                  	{
                  		"name": "Gambrell Hall",
                  		"key_id": "83",
                  		"bldg_id": "51"
                  	},



                  	{
                  		"name": "Gibbes Court",
                  		"key_id": "93",
                  		"bldg_id": "43"
                  	},



                  	{
                  		"name": "Golf Training Center",
                  		"key_id": "199",
                  		"bldg_id": "188A"
                  	},



                  	{
                  		"name": "Graduate Science Research Center",
                  		"key_id": "162",
                  		"bldg_id": "114"
                  	},



                  	{
                  		"name": "Greek Housing",
                  		"key_id": "144",
                  		"bldg_id": "148"
                  	},



                  	{
                  		"name": "Greek Housing - A",
                  		"key_id": "139",
                  		"bldg_id": "148a"
                  	},



                  	{
                  		"name": "Greek Housing - B",
                  		"key_id": "134",
                  		"bldg_id": "148b"
                  	},



                  	{
                  		"name": "Greek Housing - C",
                  		"key_id": "128",
                  		"bldg_id": "148c"
                  	},



                  	{
                  		"name": "Greek Housing - D",
                  		"key_id": "129",
                  		"bldg_id": "148d"
                  	},



                  	{
                  		"name": "Greek Housing - E",
                  		"key_id": "141",
                  		"bldg_id": "148e"
                  	},



                  	{
                  		"name": "Greek Housing - F",
                  		"key_id": "140",
                  		"bldg_id": "148f"
                  	},



                  	{
                  		"name": "Greek Housing - G",
                  		"key_id": "132",
                  		"bldg_id": "148g"
                  	},



                  	{
                  		"name": "Greek Housing - H",
                  		"key_id": "131",
                  		"bldg_id": "148h"
                  	},



                  	{
                  		"name": "Greek Housing - I",
                  		"key_id": "133",
                  		"bldg_id": "148i"
                  	},



                  	{
                  		"name": "Greek Housing - J",
                  		"key_id": "130",
                  		"bldg_id": "148j"
                  	},



                  	{
                  		"name": "Greek Housing - K",
                  		"key_id": "138",
                  		"bldg_id": "148k"
                  	},



                  	{
                  		"name": "Greek Housing - L",
                  		"key_id": "137",
                  		"bldg_id": "148l"
                  	},



                  	{
                  		"name": "Greek Housing - M",
                  		"key_id": "136",
                  		"bldg_id": "148m"
                  	},



                  	{
                  		"name": "Greek Housing - N",
                  		"key_id": "135",
                  		"bldg_id": "148n"
                  	},



                  	{
                  		"name": "Greek Housing - O",
                  		"key_id": "142",
                  		"bldg_id": "148o"
                  	},



                  	{
                  		"name": "Greek Housing - P",
                  		"key_id": "143",
                  		"bldg_id": "148p"
                  	},



                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"key_id": "182",
                  		"bldg_id": "146a"
                  	},



                  	{
                  		"name": "Green (West) Quadrangle B",
                  		"key_id": "183",
                  		"bldg_id": "146b"
                  	},



                  	{
                  		"name": "Green (West) Quadrangle C",
                  		"key_id": "184",
                  		"bldg_id": "146c"
                  	},



                  	{
                  		"name": "Green (West) Quadrangle D",
                  		"key_id": "185",
                  		"bldg_id": "146d"
                  	},



                  	{
                  		"name": "Greenhouse",
                  		"key_id": "63",
                  		"bldg_id": "75"
                  	},



                  	{
                  		"name": "Greenhouse #2",
                  		"key_id": "25",
                  		"bldg_id": "177"
                  	},



                  	{
                  		"name": "Grounds Maintenance Shop",
                  		"key_id": "126",
                  		"bldg_id": "82b"
                  	},



                  	{
                  		"name": "Hamilton College",
                  		"key_id": "56",
                  		"bldg_id": "16"
                  	},



                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"key_id": "66",
                  		"bldg_id": "8"
                  	},



                  	{
                  		"name": "Health Sciences Building",
                  		"key_id": "164",
                  		"bldg_id": "76"
                  	},



                  	{
                  		"name": "Horizon Building",
                  		"key_id": "190",
                  		"bldg_id": "236"
                  	},



                  	{
                  		"name": "Horizon Parking Garage",
                  		"key_id": "220",
                  		"bldg_id": "237"
                  	},



                  	{
                  		"name": "Inn at USC",
                  		"key_id": "168",
                  		"bldg_id": "32"
                  	},



                  	{
                  		"name": "Institute of Archaeology and Anthropology",
                  		"key_id": "33",
                  		"bldg_id": "7"
                  	},



                  	{
                  		"name": "Inventory Central Supply",
                  		"key_id": "98",
                  		"bldg_id": "81"
                  	},



                  	{
                  		"name": "James F. Byrnes Building",
                  		"key_id": "8",
                  		"bldg_id": "1"
                  	},



                  	{
                  		"name": "John Welsh Humanities Center",
                  		"key_id": "57",
                  		"bldg_id": "54"
                  	},



                  	{
                  		"name": "Jones Physical Science Center",
                  		"key_id": "86",
                  		"bldg_id": "90"
                  	},



                  	{
                  		"name": "Kirkland Apartments",
                  		"key_id": "4",
                  		"bldg_id": "30"
                  	},



                  	{
                  		"name": "Koger Center for the Arts",
                  		"key_id": "163",
                  		"bldg_id": "86"
                  	},



                  	{
                  		"name": "Latter-Day Saints Student Union",
                  		"key_id": "91",
                  		"bldg_id": "615"
                  	},



                  	{
                  		"name": "Law Center",
                  		"key_id": "68",
                  		"bldg_id": "85"
                  	},



                  	{
                  		"name": "LeConte College",
                  		"key_id": "161",
                  		"bldg_id": "60"
                  	},



                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"key_id": "61",
                  		"bldg_id": "72"
                  	},



                  	{
                  		"name": "Library Annex & Conservation Facility",
                  		"key_id": "217",
                  		"bldg_id": "227"
                  	},



                  	{
                  		"name": "Lieber College",
                  		"key_id": "62",
                  		"bldg_id": "74"
                  	},



                  	{
                  		"name": "Longstreet Annex",
                  		"key_id": "11",
                  		"bldg_id": "101"
                  	},



                  	{
                  		"name": "Longstreet Theatre",
                  		"key_id": "12",
                  		"bldg_id": "102"
                  	},



                  	{
                  		"name": "Maxcy College",
                  		"key_id": "88",
                  		"bldg_id": "9"
                  	},



                  	{
                  		"name": "Maxcy Gregg Tennis Building",
                  		"key_id": "101",
                  		"bldg_id": "137"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"key_id": "149",
                  		"bldg_id": "106"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - B",
                  		"key_id": "146",
                  		"bldg_id": "107"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - C",
                  		"key_id": "147",
                  		"bldg_id": "108"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - D",
                  		"key_id": "148",
                  		"bldg_id": "105"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - E",
                  		"key_id": "145",
                  		"bldg_id": "104"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - F",
                  		"key_id": "150",
                  		"bldg_id": "109"
                  	},



                  	{
                  		"name": "McBryde Quadrangle - G",
                  		"key_id": "151",
                  		"bldg_id": "110"
                  	},



                  	{
                  		"name": "McClintock",
                  		"key_id": "38",
                  		"bldg_id": "118"
                  	},



                  	{
                  		"name": "McCutchen House",
                  		"key_id": "166",
                  		"bldg_id": "10"
                  	},



                  	{
                  		"name": "McKissick",
                  		"key_id": "167",
                  		"bldg_id": "15"
                  	},



                  	{
                  		"name": "McMaster College",
                  		"key_id": "6",
                  		"bldg_id": "33"
                  	},



                  	{
                  		"name": "Melton Observatory",
                  		"key_id": "24",
                  		"bldg_id": "63"
                  	},



                  	{
                  		"name": "Middleburg Plaza CD",
                  		"key_id": "213",
                  		"bldg_id": "217"
                  	},



                  	{
                  		"name": "Middleburg Plaza Speech & Hearing",
                  		"key_id": "214",
                  		"bldg_id": "217A"
                  	},



                  	{
                  		"name": "Motor Pool",
                  		"key_id": "97",
                  		"bldg_id": "82a"
                  	},



                  	{
                  		"name": "Nada Apartments",
                  		"key_id": "46",
                  		"bldg_id": "49"
                  	},



                  	{
                  		"name": "Nada Apartments",
                  		"key_id": "54",
                  		"bldg_id": "50"
                  	},



                  	{
                  		"name": "Nada Apartments",
                  		"key_id": "77",
                  		"bldg_id": "48"
                  	},



                  	{
                  		"name": "National Advocacy Center",
                  		"key_id": "70",
                  		"bldg_id": "27"
                  	},



                  	{
                  		"name": "Neutron  Generator",
                  		"key_id": "87",
                  		"bldg_id": "91"
                  	},



                  	{
                  		"name": "New Band Hall",
                  		"key_id": "192",
                  		"bldg_id": "164"
                  	},



                  	{
                  		"name": "Old Observatory",
                  		"key_id": "44",
                  		"bldg_id": "13"
                  	},



                  	{
                  		"name": "Onewood Farm - Building A",
                  		"key_id": "206",
                  		"bldg_id": "204A"
                  	},



                  	{
                  		"name": "Onewood Farm - Building B",
                  		"key_id": "207",
                  		"bldg_id": "204B"
                  	},



                  	{
                  		"name": "Onewood Farm - Gamecock Barn",
                  		"key_id": "208",
                  		"bldg_id": "204C"
                  	},



                  	{
                  		"name": "Onewood Farm - Garnet & Black Barns",
                  		"key_id": "209",
                  		"bldg_id": "204D"
                  	},



                  	{
                  		"name": "Onewood Farm - Main Building",
                  		"key_id": "205",
                  		"bldg_id": "204"
                  	},



                  	{
                  		"name": "Osborne Administration Building",
                  		"key_id": "48",
                  		"bldg_id": "14"
                  	},



                  	{
                  		"name": "PALM Center",
                  		"key_id": "20",
                  		"bldg_id": "612"
                  	},



                  	{
                  		"name": "Patterson Hall",
                  		"key_id": "60",
                  		"bldg_id": "121"
                  	},



                  	{
                  		"name": "Pendleton Street Garage",
                  		"key_id": "3",
                  		"bldg_id": "19"
                  	},



                  	{
                  		"name": "Petigru College",
                  		"key_id": "18",
                  		"bldg_id": "61"
                  	},



                  	{
                  		"name": "Presbyterian Student Center",
                  		"key_id": "21",
                  		"bldg_id": "614"
                  	},



                  	{
                  		"name": "President's House",
                  		"key_id": "117",
                  		"bldg_id": "69"
                  	},



                  	{
                  		"name": "Preston Residential College",
                  		"key_id": "35",
                  		"bldg_id": "70"
                  	},



                  	{
                  		"name": "Psychology Annex (819 Barnwell Street)",
                  		"key_id": "115",
                  		"bldg_id": "34"
                  	},



                  	{
                  		"name": "Public Health Research Center",
                  		"key_id": "179",
                  		"bldg_id": "156a"
                  	},



                  	{
                  		"name": "Rex Enright Athletic Center",
                  		"key_id": "120",
                  		"bldg_id": "205"
                  	},



                  	{
                  		"name": "Rice Athletics Center",
                  		"key_id": "201",
                  		"bldg_id": "190"
                  	},



                  	{
                  		"name": "Roost - A",
                  		"key_id": "105",
                  		"bldg_id": "195"
                  	},



                  	{
                  		"name": "Roost - B",
                  		"key_id": "106",
                  		"bldg_id": "196"
                  	},



                  	{
                  		"name": "Roost - D",
                  		"key_id": "107",
                  		"bldg_id": "198"
                  	},



                  	{
                  		"name": "Roost - E",
                  		"key_id": "108",
                  		"bldg_id": "199"
                  	},



                  	{
                  		"name": "Roost Residence Hall",
                  		"key_id": "109",
                  		"bldg_id": "207"
                  	},



                  	{
                  		"name": "Russell House",
                  		"key_id": "171",
                  		"bldg_id": "112"
                  	},



                  	{
                  		"name": "Rutledge College",
                  		"key_id": "30",
                  		"bldg_id": "67"
                  	},



                  	{
                  		"name": "Saint Thomas More Center",
                  		"key_id": "19",
                  		"bldg_id": "611"
                  	},



                  	{
                  		"name": "School of Music",
                  		"key_id": "89",
                  		"bldg_id": "86a"
                  	},



                  	{
                  		"name": "Science Annex 1",
                  		"key_id": "7",
                  		"bldg_id": "73"
                  	},



                  	{
                  		"name": "Science Annex 2",
                  		"key_id": "36",
                  		"bldg_id": "71"
                  	},



                  	{
                  		"name": "Senate Street Parking Garage",
                  		"key_id": "5",
                  		"bldg_id": "31"
                  	},



                  	{
                  		"name": "Sims",
                  		"key_id": "42",
                  		"bldg_id": "120"
                  	},



                  	{
                  		"name": "Sloan College",
                  		"key_id": "17",
                  		"bldg_id": "17"
                  	},



                  	{
                  		"name": "South Caroliniana Library",
                  		"key_id": "72",
                  		"bldg_id": "4"
                  	},



                  	{
                  		"name": "South Quadrangle",
                  		"key_id": "152",
                  		"bldg_id": "135"
                  	},



                  	{
                  		"name": "South Tower",
                  		"key_id": "43",
                  		"bldg_id": "122"
                  	},



                  	{
                  		"name": "Special Collections - Fritz Hollings Library Annex",
                  		"key_id": "194",
                  		"bldg_id": "103A"
                  	},



                  	{
                  		"name": "Spigner House",
                  		"key_id": "74",
                  		"bldg_id": "42"
                  	},



                  	{
                  		"name": "Spring Sports Center",
                  		"key_id": "176",
                  		"bldg_id": "200"
                  	},



                  	{
                  		"name": "Storage Shed",
                  		"key_id": "127",
                  		"bldg_id": "81a"
                  	},



                  	{
                  		"name": "Strom Thurmond Wellness and Fitness Center",
                  		"key_id": "122",
                  		"bldg_id": "157"
                  	},



                  	{
                  		"name": "Student Financial Aid",
                  		"key_id": "76",
                  		"bldg_id": "46"
                  	},



                  	{
                  		"name": "Sumter Street Parking Garage",
                  		"key_id": "174",
                  		"bldg_id": "141"
                  	},



                  	{
                  		"name": "Sumwalt",
                  		"key_id": "85",
                  		"bldg_id": "88"
                  	},



                  	{
                  		"name": "Swearingen Engineering Center",
                  		"key_id": "23",
                  		"bldg_id": "173"
                  	},



                  	{
                  		"name": "The Colloquium",
                  		"key_id": "195",
                  		"bldg_id": "59"
                  	},



                  	{
                  		"name": "The Horseshoe",
                  		"key_id": "178",
                  		"bldg_id": "a"
                  	},



                  	{
                  		"name": "Thomas Cooper Library",
                  		"key_id": "125",
                  		"bldg_id": "103"
                  	},



                  	{
                  		"name": "Thomson Student Health Center",
                  		"key_id": "27",
                  		"bldg_id": "111"
                  	},



                  	{
                  		"name": "Thornwell College",
                  		"key_id": "41",
                  		"bldg_id": "12"
                  	},



                  	{
                  		"name": "University Band Hall",
                  		"key_id": "50",
                  		"bldg_id": "149"
                  	},



                  	{
                  		"name": "University Printing",
                  		"key_id": "52",
                  		"bldg_id": "154"
                  	},



                  	{
                  		"name": "USC Child Development and Research Center",
                  		"key_id": "160",
                  		"bldg_id": "133"
                  	},



                  	{
                  		"name": "USC Credit Union (710 Pulaski Street)",
                  		"key_id": "119",
                  		"bldg_id": "616"
                  	},



                  	{
                  		"name": "Wade Hampton",
                  		"key_id": "40",
                  		"bldg_id": "119"
                  	},



                  	{
                  		"name": "Wardlaw College",
                  		"key_id": "65",
                  		"bldg_id": "80"
                  	},



                  	{
                  		"name": "Wardle Golf House",
                  		"key_id": "198",
                  		"bldg_id": "188"
                  	},



                  	{
                  		"name": "Wedge Administration",
                  		"key_id": "216",
                  		"bldg_id": "221"
                  	},



                  	{
                  		"name": "Wedge Plantation",
                  		"key_id": "215",
                  		"bldg_id": "220"
                  	},



                  	{
                  		"name": "Welsh Humanities Classroom Building",
                  		"key_id": "58",
                  		"bldg_id": "55"
                  	},



                  	{
                  		"name": "Whaley House",
                  		"key_id": "221",
                  		"bldg_id": "627"
                  	},



                  	{
                  		"name": "Williams-Brice Building",
                  		"key_id": "59",
                  		"bldg_id": "56"
                  	},



                  	{
                  		"name": "Williams-Brice Building",
                  		"key_id": "123",
                  		"bldg_id": "56a"
                  	},



                  	{
                  		"name": "Williams-Brice Stadium",
                  		"key_id": "177",
                  		"bldg_id": "210"
                  	},



                  	{
                  		"name": "Woodrow College",
                  		"key_id": "26",
                  		"bldg_id": "65"
                  	},



                  	{
                  		"name": "World War Memorial",
                  		"key_id": "82",
                  		"bldg_id": "5"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW",
                  		"key_id": "351",
                  		"bldg_id": "215"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW COTTAGE #1",
                  		"key_id": "355",
                  		"bldg_id": "218A"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW COTTAGE #2",
                  		"key_id": "356",
                  		"bldg_id": "218B"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW COTTAGE #3",
                  		"key_id": "357",
                  		"bldg_id": "218C"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW DISCOVERY CENTER",
                  		"key_id": "358",
                  		"bldg_id": "219"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW DORMS",
                  		"key_id": "352",
                  		"bldg_id": "215A"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW KIMBEL LODGE",
                  		"key_id": "354",
                  		"bldg_id": "218"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW MAINTENANCE SHOP",
                  		"key_id": "353",
                  		"bldg_id": "215B"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW MARINE LABORATORY",
                  		"key_id": "360",
                  		"bldg_id": "222"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW POND SHELTER",
                  		"key_id": "359",
                  		"bldg_id": "219A"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW SCREENED LAB",
                  		"key_id": "362",
                  		"bldg_id": "222B"
                  	},



                  	{
                  		"name": "(Georgetown) HOBCAW WET LABORATORY",
                  		"key_id": "361",
                  		"bldg_id": "222A"
                  	},



                  	{
                  		"name": "(Lancaster) CAROL RAY DOWLING BUILDING",
                  		"key_id": "349",
                  		"bldg_id": "826"
                  	},



                  	{
                  		"name": "(Lancaster) GREGORY HEALTH & WELLNESS",
                  		"key_id": "346",
                  		"bldg_id": "823"
                  	},



                  	{
                  		"name": "(Lancaster) HUBBARD",
                  		"key_id": "343",
                  		"bldg_id": "820"
                  	},



                  	{
                  		"name": "(Lancaster) JAMES BRADLEY ARTS & SCIENCE CTR",
                  		"key_id": "348",
                  		"bldg_id": "825"
                  	},



                  	{
                  		"name": "(Lancaster) MAINTENANCE BUILDING",
                  		"key_id": "347",
                  		"bldg_id": "824"
                  	},



                  	{
                  		"name": "(Lancaster) MEDFORD",
                  		"key_id": "345",
                  		"bldg_id": "822"
                  	},



                  	{
                  		"name": "(Lancaster) NATIVE AMERICAN STUDIES CENTER",
                  		"key_id": "350",
                  		"bldg_id": "828"
                  	},



                  	{
                  		"name": "(Lancaster) STARR",
                  		"key_id": "344",
                  		"bldg_id": "821"
                  	},



                  	{
                  		"name": "(Salkehatchie) 278 ANNEX",
                  		"key_id": "338",
                  		"bldg_id": "857"
                  	},



                  	{
                  		"name": "(Salkehatchie) ADMINISTRATION ANNEX",
                  		"key_id": "324",
                  		"bldg_id": "847"
                  	},



                  	{
                  		"name": "(Salkehatchie) ADMISSIONS",
                  		"key_id": "325",
                  		"bldg_id": "848"
                  	},



                  	{
                  		"name": "(Salkehatchie) ART ANNEX",
                  		"key_id": "329",
                  		"bldg_id": "850"
                  	},



                  	{
                  		"name": "(Salkehatchie) ATHLETIC CONCESSIONS",
                  		"key_id": "331",
                  		"bldg_id": "852"
                  	},



                  	{
                  		"name": "(Salkehatchie) BASEBALL PRESS BOX",
                  		"key_id": "333",
                  		"bldg_id": "852B"
                  	},



                  	{
                  		"name": "(Salkehatchie) CAROLINA THEATRE",
                  		"key_id": "330",
                  		"bldg_id": "851"
                  	},



                  	{
                  		"name": "(Salkehatchie) CENTRAL CLASSROOM BUILDING",
                  		"key_id": "317",
                  		"bldg_id": "841"
                  	},



                  	{
                  		"name": "(Salkehatchie) CONFERENCE CENTER BUILDING",
                  		"key_id": "335",
                  		"bldg_id": "854"
                  	},



                  	{
                  		"name": "(Salkehatchie) EDUCATION BUILDING",
                  		"key_id": "327",
                  		"bldg_id": "849"
                  	},



                  	{
                  		"name": "(Salkehatchie) EDUCATION BUILDING ANNEX",
                  		"key_id": "328",
                  		"bldg_id": "849A"
                  	},



                  	{
                  		"name": "(Salkehatchie) FACULTY HOUSE",
                  		"key_id": "318",
                  		"bldg_id": "842"
                  	},



                  	{
                  		"name": "(Salkehatchie) GAZEBO",
                  		"key_id": "320",
                  		"bldg_id": "843A"
                  	},



                  	{
                  		"name": "(Salkehatchie) HUT COMPLEX",
                  		"key_id": "323",
                  		"bldg_id": "845"
                  	},



                  	{
                  		"name": "(Salkehatchie) LEADERSHIP CENTER",
                  		"key_id": "337",
                  		"bldg_id": "856"
                  	},



                  	{
                  		"name": "(Salkehatchie) LIBRARY COMPUTER SCIENCE",
                  		"key_id": "334",
                  		"bldg_id": "853"
                  	},



                  	{
                  		"name": "(Salkehatchie) MAINTENANCE CENTER",
                  		"key_id": "321",
                  		"bldg_id": "844"
                  	},



                  	{
                  		"name": "(Salkehatchie) MAINTENANCE SHED",
                  		"key_id": "322",
                  		"bldg_id": "844A"
                  	},



                  	{
                  		"name": "(Salkehatchie) SCIENCE ADMINISTRATION",
                  		"key_id": "316",
                  		"bldg_id": "840"
                  	},



                  	{
                  		"name": "(Salkehatchie) SPORTS INFORMATION CENTER",
                  		"key_id": "326",
                  		"bldg_id": "848A"
                  	},



                  	{
                  		"name": "(Salkehatchie) STUDENT GAME",
                  		"key_id": "319",
                  		"bldg_id": "843"
                  	},



                  	{
                  		"name": "(Salkehatchie) STUDENT SERVICES BUILDING",
                  		"key_id": "336",
                  		"bldg_id": "855"
                  	},



                  	{
                  		"name": "(Salkehatchie) WALTERBORO LIBRARY",
                  		"key_id": "342",
                  		"bldg_id": "859"
                  	},



                  	{
                  		"name": "(Salkehatchie) WALTERBORO MAIN BUILDING",
                  		"key_id": "339",
                  		"bldg_id": "858"
                  	},



                  	{
                  		"name": "(Salkehatchie) WALTERBORO RESEARCH CENTER",
                  		"key_id": "341",
                  		"bldg_id": "858B"
                  	},



                  	{
                  		"name": "(Salkehatchie) WALTERBORO SCIENCE BUILDING",
                  		"key_id": "340",
                  		"bldg_id": "858A"
                  	},



                  	{
                  		"name": "(Salkehatchie) WELLNESS CENTER",
                  		"key_id": "332",
                  		"bldg_id": "852A"
                  	},



                  	{
                  		"name": "(Sumter) ADMINISTRATION",
                  		"key_id": "298",
                  		"bldg_id": "880"
                  	},



                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"key_id": "301",
                  		"bldg_id": "883"
                  	},



                  	{
                  		"name": "(Sumter) ARTS AND LETTERS BUILDING",
                  		"key_id": "305",
                  		"bldg_id": "886A"
                  	},



                  	{
                  		"name": "(Sumter) BUSINESS ADMINISTRATION",
                  		"key_id": "299",
                  		"bldg_id": "881"
                  	},



                  	{
                  		"name": "(Sumter) NETTLES",
                  		"key_id": "304",
                  		"bldg_id": "885A"
                  	},



                  	{
                  		"name": "(Sumter) SCHWARTZ BUILDING",
                  		"key_id": "303",
                  		"bldg_id": "885"
                  	},



                  	{
                  		"name": "(Sumter) SCIENCE BUILDING",
                  		"key_id": "300",
                  		"bldg_id": "882"
                  	},



                  	{
                  		"name": "(Sumter) STUDENT UNION",
                  		"key_id": "302",
                  		"bldg_id": "884"
                  	},



                  	{
                  		"name": "(Sumter) SUMTER PORTABLE A",
                  		"key_id": "306",
                  		"bldg_id": "891"
                  	},



                  	{
                  		"name": "(Sumter) SUMTER PORTABLE E",
                  		"key_id": "307",
                  		"bldg_id": "895"
                  	},



                  	{
                  		"name": "(Union) BOOKSTORE",
                  		"key_id": "313",
                  		"bldg_id": "866"
                  	},



                  	{
                  		"name": "(Union) CENTRAL BUILDING",
                  		"key_id": "312",
                  		"bldg_id": "864"
                  	},



                  	{
                  		"name": "(Union) FOUNDERS HOUSE",
                  		"key_id": "311",
                  		"bldg_id": "863"
                  	},



                  	{
                  		"name": "(Union) LAURENS",
                  		"key_id": "315",
                  		"bldg_id": "870"
                  	},



                  	{
                  		"name": "(Union) MAIN BUILDING",
                  		"key_id": "308",
                  		"bldg_id": "860"
                  	},



                  	{
                  		"name": "(Union) MAINTENANCE SHOP",
                  		"key_id": "314",
                  		"bldg_id": "867"
                  	},



                  	{
                  		"name": "(Union) TRULUCK ACTIVITIES",
                  		"key_id": "309",
                  		"bldg_id": "861"
                  	},



                  	{
                  		"name": "(Union) UNION STORAGE BUILDING",
                  		"key_id": "310",
                  		"bldg_id": "861A"
                  	},



                  	{
                  		"name": "(Upstate) ACADEMIC ANNEX BUILDING",
                  		"key_id": "289",
                  		"bldg_id": "994"
                  	},



                  	{
                  		"name": "(Upstate) ACADEMIC ANNEX BUILDING THREE",
                  		"key_id": "291",
                  		"bldg_id": "994B"
                  	},



                  	{
                  		"name": "(Upstate) ACADEMIC ANNEX BUILDING TWO",
                  		"key_id": "290",
                  		"bldg_id": "994A"
                  	},



                  	{
                  		"name": "(Upstate) ACTIVITIES BUILDING",
                  		"key_id": "274",
                  		"bldg_id": "977"
                  	},



                  	{
                  		"name": "(Upstate) ADMINISTRATION",
                  		"key_id": "264",
                  		"bldg_id": "970"
                  	},



                  	{
                  		"name": "(Upstate) CAMPUS LANDSCAPE BUILDING",
                  		"key_id": "285",
                  		"bldg_id": "990B"
                  	},



                  	{
                  		"name": "(Upstate) CAMPUS LIFE CENTER",
                  		"key_id": "279",
                  		"bldg_id": "986"
                  	},



                  	{
                  		"name": "(Upstate) CHILD CARE CENTER",
                  		"key_id": "267",
                  		"bldg_id": "973"
                  	},



                  	{
                  		"name": "(Upstate) COLLEGE OF ARTS AND SCIENCES BLDG.",
                  		"key_id": "275",
                  		"bldg_id": "978"
                  	},



                  	{
                  		"name": "(Upstate) COMMUNITY ED & OUTREACH CTR ANNEX",
                  		"key_id": "294",
                  		"bldg_id": "996A"
                  	},



                  	{
                  		"name": "(Upstate) COMMUNITY ED & RESEARCH CENTER",
                  		"key_id": "293",
                  		"bldg_id": "996"
                  	},



                  	{
                  		"name": "(Upstate) FACILITIES MANAGEMENT CENTER",
                  		"key_id": "283",
                  		"bldg_id": "990"
                  	},



                  	{
                  		"name": "(Upstate) HEALTH EDUCATION COMPLEX",
                  		"key_id": "295",
                  		"bldg_id": "997"
                  	},



                  	{
                  		"name": "(Upstate) HEALTH SERVICES BUILDING",
                  		"key_id": "292",
                  		"bldg_id": "995"
                  	},



                  	{
                  		"name": "(Upstate) HODGE CENTER",
                  		"key_id": "265",
                  		"bldg_id": "971"
                  	},



                  	{
                  		"name": "(Upstate) HORACE C. SMITH BUILDING",
                  		"key_id": "266",
                  		"bldg_id": "972"
                  	},



                  	{
                  		"name": "(Upstate) HUMANITIES & PERFORMING ARTS",
                  		"key_id": "277",
                  		"bldg_id": "983"
                  	},



                  	{
                  		"name": "(Upstate) JOHN M RAMPEY JR CENTER",
                  		"key_id": "281",
                  		"bldg_id": "988"
                  	},



                  	{
                  		"name": "(Upstate) JOHNSON COLLEGE OF BUSINESS",
                  		"key_id": "297",
                  		"bldg_id": "999"
                  	},



                  	{
                  		"name": "(Upstate) L E ROEL GARDEN PAVILION",
                  		"key_id": "263",
                  		"bldg_id": "781"
                  	},



                  	{
                  		"name": "(Upstate) LIBRARY CLASS",
                  		"key_id": "268",
                  		"bldg_id": "974"
                  	},



                  	{
                  		"name": "(Upstate) MAGNOLIA HOUSE",
                  		"key_id": "296",
                  		"bldg_id": "998"
                  	},



                  	{
                  		"name": "(Upstate) MAINTENANCE WAREHOUSE",
                  		"key_id": "271",
                  		"bldg_id": "976A"
                  	},



                  	{
                  		"name": "(Upstate) MAINTENANCE WAREHOUSE",
                  		"key_id": "272",
                  		"bldg_id": "976B"
                  	},



                  	{
                  		"name": "(Upstate) MAINTENANCE WAREHOUSE",
                  		"key_id": "273",
                  		"bldg_id": "976C"
                  	},



                  	{
                  		"name": "(Upstate) MEDIA BUILDING",
                  		"key_id": "269",
                  		"bldg_id": "975"
                  	},



                  	{
                  		"name": "(Upstate) P K H VISUAL ARTS CENTER",
                  		"key_id": "270",
                  		"bldg_id": "976"
                  	},



                  	{
                  		"name": "(Upstate) PALMETTO HOUSE",
                  		"key_id": "288",
                  		"bldg_id": "993"
                  	},



                  	{
                  		"name": "(Upstate) PALMETTO VILLAS",
                  		"key_id": "282",
                  		"bldg_id": "989"
                  	},



                  	{
                  		"name": "(Upstate) PINEGATE HOUSE",
                  		"key_id": "286",
                  		"bldg_id": "991"
                  	},



                  	{
                  		"name": "(Upstate) PORTABLE UNITS",
                  		"key_id": "276",
                  		"bldg_id": "980"
                  	},



                  	{
                  		"name": "(Upstate) PUBLIC SAFETY BUILDING",
                  		"key_id": "280",
                  		"bldg_id": "987"
                  	},



                  	{
                  		"name": "(Upstate) SMITH FARMHOUSE",
                  		"key_id": "278",
                  		"bldg_id": "985"
                  	},



                  	{
                  		"name": "(Upstate) SPORTS TURF BUILDING",
                  		"key_id": "284",
                  		"bldg_id": "990A"
                  	},



                  	{
                  		"name": "(Upstate) UNIVERSITY READINESS CTR",
                  		"key_id": "287",
                  		"bldg_id": "992"
                  	},



                  	{
                  		"name": "(Upstate) UNIVERSITY SERVICES BUILDING",
                  		"key_id": "261",
                  		"bldg_id": "780"
                  	},



                  	{
                  		"name": "(Upstate) UNIVERSITY SERVICES BUILDING ANNEX",
                  		"key_id": "262",
                  		"bldg_id": "780A"
                  	},



                  	{
                  		"name": "(USC SOM) 1 MEDICAL PARK",
                  		"key_id": "237",
                  		"bldg_id": "601E"
                  	},



                  	{
                  		"name": "(USC SOM) 1325 LAUREL STREET",
                  		"key_id": "231",
                  		"bldg_id": "600"
                  	},



                  	{
                  		"name": "(USC SOM) 14 MEDICAL PARK",
                  		"key_id": "245",
                  		"bldg_id": "606"
                  	},



                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"key_id": "255",
                  		"bldg_id": "667"
                  	},



                  	{
                  		"name": "(USC SOM) 2 MEDICAL PARK",
                  		"key_id": "234",
                  		"bldg_id": "601B"
                  	},



                  	{
                  		"name": "(USC SOM) 3 MEDICAL PARK",
                  		"key_id": "236",
                  		"bldg_id": "601D"
                  	},



                  	{
                  		"name": "(USC SOM) 3209 COLONIAL DRIVE",
                  		"key_id": "240",
                  		"bldg_id": "601H"
                  	},



                  	{
                  		"name": "(USC SOM) 4 MEDICAL PARK",
                  		"key_id": "233",
                  		"bldg_id": "601A"
                  	},



                  	{
                  		"name": "(USC SOM) 5 MEDICAL PARK",
                  		"key_id": "232",
                  		"bldg_id": "601"
                  	},



                  	{
                  		"name": "(USC SOM) 6 MEDICAL PARK",
                  		"key_id": "235",
                  		"bldg_id": "601C"
                  	},



                  	{
                  		"name": "(USC SOM) 7 MEDICAL PARK",
                  		"key_id": "238",
                  		"bldg_id": "601F"
                  	},



                  	{
                  		"name": "(USC SOM) 8 MEDICAL PARK",
                  		"key_id": "248",
                  		"bldg_id": "609"
                  	},



                  	{
                  		"name": "(USC SOM) 9 MEDICAL PARK",
                  		"key_id": "239",
                  		"bldg_id": "601G"
                  	},



                  	{
                  		"name": "(USC SOM) BAPTIST HOSPITAL",
                  		"key_id": "241",
                  		"bldg_id": "602"
                  	},



                  	{
                  		"name": "(USC SOM) CENTER FOR DISABILITIES RESOURCES",
                  		"key_id": "246",
                  		"bldg_id": "607"
                  	},



                  	{
                  		"name": "(USC SOM) DORN VETERANS HOSPITAL",
                  		"key_id": "244",
                  		"bldg_id": "605"
                  	},



                  	{
                  		"name": "(USC SOM) GENERATOR BUILDING",
                  		"key_id": "257",
                  		"bldg_id": "668A"
                  	},



                  	{
                  		"name": "(USC SOM) GENERATOR BUILDINGCORRIDOR",
                  		"key_id": "259",
                  		"bldg_id": "669"
                  	},



                  	{
                  		"name": "(USC SOM) HALL PSYCHIATRIC INSTITUTE",
                  		"key_id": "243",
                  		"bldg_id": "604"
                  	},



                  	{
                  		"name": "(USC SOM) HEALTH SCIENCES EDUCATION BUILDING",
                  		"key_id": "260",
                  		"bldg_id": "670"
                  	},



                  	{
                  		"name": "(USC SOM) LEXINGTON HOSPITAL",
                  		"key_id": "247",
                  		"bldg_id": "608"
                  	},



                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"key_id": "249",
                  		"bldg_id": "661"
                  	},



                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #2",
                  		"key_id": "250",
                  		"bldg_id": "662"
                  	},



                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"key_id": "252",
                  		"bldg_id": "664"
                  	},



                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #3",
                  		"key_id": "256",
                  		"bldg_id": "668"
                  	},



                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"key_id": "251",
                  		"bldg_id": "663"
                  	},



                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #101",
                  		"key_id": "253",
                  		"bldg_id": "665"
                  	},



                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #104",
                  		"key_id": "254",
                  		"bldg_id": "666"
                  	},



                  	{
                  		"name": "(USC SOM) PROVIDENCE HOSPITAL",
                  		"key_id": "242",
                  		"bldg_id": "603"
                  	},



                  	{
                  		"name": "(USC SOM) STORAGE BUILDING",
                  		"key_id": "258",
                  		"bldg_id": "668B"
                  	},

                     ];



                  var inspectionsArray = [
                  	{
                  		"name": "Sims",
                  		"date": "06/07/13",
                  		"building_id": "42",
                  		"key_id": "736",
                  		"bldg_id": "120",
                  		"cp": "",
                  		"wo": "S13-CSH014",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "06/06/13",
                  		"building_id": "14",
                  		"key_id": "731",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/06/13",
                  		"building_id": "177",
                  		"key_id": "737",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - G",
                  		"date": "06/06/13",
                  		"building_id": "151",
                  		"key_id": "738",
                  		"bldg_id": "110",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "06/05/13",
                  		"building_id": "172",
                  		"key_id": "732",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00420261",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "06/04/13",
                  		"building_id": "109",
                  		"key_id": "730",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "06/04/13",
                  		"building_id": "170",
                  		"key_id": "734",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "S13-CSH014",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "06/04/13",
                  		"building_id": "38",
                  		"key_id": "735",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "S13-CSH014",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "05/31/13",
                  		"building_id": "170",
                  		"key_id": "733",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "00426610",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "716",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "717",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Gary Bennett"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "715",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "719",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Gary Bennett"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "714",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"building_id": "55",
                  		"key_id": "718",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/25/13",
                  		"building_id": "59",
                  		"key_id": "632",
                  		"bldg_id": "56",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/25/13",
                  		"building_id": "59",
                  		"key_id": "631",
                  		"bldg_id": "56",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/25/13",
                  		"building_id": "59",
                  		"key_id": "633",
                  		"bldg_id": "56",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/23/13",
                  		"building_id": "55",
                  		"key_id": "630",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/10/13",
                  		"building_id": "55",
                  		"key_id": "629",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/08/13",
                  		"building_id": "55",
                  		"key_id": "628",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "test",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/05/13",
                  		"building_id": "55",
                  		"key_id": "626",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/05/13",
                  		"building_id": "55",
                  		"key_id": "627",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Drayton Hall",
                  		"date": "03/10/13",
                  		"building_id": "64",
                  		"key_id": "223",
                  		"bldg_id": "78",
                  		"cp": "",
                  		"wo": "00416095",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "02/22/13",
                  		"building_id": "2",
                  		"key_id": "447",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "Fm00419856",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1723-25 Green Street",
                  		"date": "02/22/13",
                  		"building_id": "94",
                  		"key_id": "441",
                  		"bldg_id": "44",
                  		"cp": "",
                  		"wo": "fm00419855",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/21/13",
                  		"building_id": "55",
                  		"key_id": "433",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/20/13",
                  		"building_id": "55",
                  		"key_id": "407",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/19/13",
                  		"building_id": "55",
                  		"key_id": "402",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/19/13",
                  		"building_id": "55",
                  		"key_id": "399",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/19/13",
                  		"building_id": "55",
                  		"key_id": "396",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/19/13",
                  		"building_id": "55",
                  		"key_id": "400",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/19/13",
                  		"building_id": "55",
                  		"key_id": "401",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "The Horseshoe",
                  		"date": "02/14/13",
                  		"building_id": "178",
                  		"key_id": "369",
                  		"bldg_id": "a",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"date": "02/14/13",
                  		"building_id": "173",
                  		"key_id": "372",
                  		"bldg_id": "139",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "361",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "360",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "359",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "358",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "356",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"building_id": "125",
                  		"key_id": "357",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/04/13",
                  		"building_id": "55",
                  		"key_id": "287",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/04/13",
                  		"building_id": "55",
                  		"key_id": "286",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"date": "01/18/13",
                  		"building_id": "301",
                  		"key_id": "634",
                  		"bldg_id": "883",
                  		"cp": "",
                  		"wo": "00416247",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"building_id": "55",
                  		"key_id": "209",
                  		"bldg_id": "159",
                  		"cp": "sadfad",
                  		"wo": "asdfasdf",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"building_id": "55",
                  		"key_id": "210",
                  		"bldg_id": "159",
                  		"cp": "sadfad",
                  		"wo": "asdfasdf",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"building_id": "55",
                  		"key_id": "211",
                  		"bldg_id": "159",
                  		"cp": "sadfad",
                  		"wo": "asdfasdf",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "01/17/13",
                  		"building_id": "83",
                  		"key_id": "213",
                  		"bldg_id": "51",
                  		"cp": "",
                  		"wo": "021300391",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McCutchen House",
                  		"date": "01/17/13",
                  		"building_id": "166",
                  		"key_id": "216",
                  		"bldg_id": "10",
                  		"cp": "",
                  		"wo": "021300390",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "Callcott House",
                  		"date": "01/17/13",
                  		"building_id": "79",
                  		"key_id": "217",
                  		"bldg_id": "45",
                  		"cp": "",
                  		"wo": "021300389",
                  		"inspector": "Gary Bennett"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "01/17/13",
                  		"building_id": "59",
                  		"key_id": "218",
                  		"bldg_id": "56",
                  		"cp": "",
                  		"wo": "021300389",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"date": "01/11/13",
                  		"building_id": "301",
                  		"key_id": "635",
                  		"bldg_id": "883",
                  		"cp": "",
                  		"wo": "00416247",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "01/10/13",
                  		"building_id": "2",
                  		"key_id": "259",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "FM00416088",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "01/10/13",
                  		"building_id": "172",
                  		"key_id": "221",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "021300268",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Discovery Building",
                  		"date": "01/09/13",
                  		"building_id": "191",
                  		"key_id": "642",
                  		"bldg_id": "230",
                  		"cp": "00291151",
                  		"wo": "00415702",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wardlaw College",
                  		"date": "01/09/13",
                  		"building_id": "65",
                  		"key_id": "220",
                  		"bldg_id": "80",
                  		"cp": "",
                  		"wo": "00416011",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McCutchen House",
                  		"date": "01/09/13",
                  		"building_id": "166",
                  		"key_id": "222",
                  		"bldg_id": "10",
                  		"cp": "",
                  		"wo": "00416012",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Currell College",
                  		"date": "01/04/13",
                  		"building_id": "29",
                  		"key_id": "279",
                  		"bldg_id": "66",
                  		"cp": "",
                  		"wo": "FM00412921",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "12/14/12",
                  		"building_id": "125",
                  		"key_id": "262",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "FM00414247",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "12/13/12",
                  		"building_id": "66",
                  		"key_id": "275",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "FM00414709",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "12/11/12",
                  		"building_id": "56",
                  		"key_id": "440",
                  		"bldg_id": "16",
                  		"cp": "",
                  		"wo": "FM00413318",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "12/10/12",
                  		"building_id": "38",
                  		"key_id": "536",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "FM00414325",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "National Advocacy Center",
                  		"date": "12/07/12",
                  		"building_id": "70",
                  		"key_id": "448",
                  		"bldg_id": "27",
                  		"cp": "",
                  		"wo": "SR00342065",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "12/06/12",
                  		"building_id": "92",
                  		"key_id": "284",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "FM00413649",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "12/06/12",
                  		"building_id": "92",
                  		"key_id": "463",
                  		"bldg_id": "29",
                  		"cp": "00364767",
                  		"wo": "0041439",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "12/04/12",
                  		"building_id": "67",
                  		"key_id": "556",
                  		"bldg_id": "84",
                  		"cp": "00320407",
                  		"wo": "00413872",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "12/03/12",
                  		"building_id": "171",
                  		"key_id": "614",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00413584",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "11/27/12",
                  		"building_id": "172",
                  		"key_id": "462",
                  		"bldg_id": "138",
                  		"cp": "00365952",
                  		"wo": "00412827",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "11/20/12",
                  		"building_id": "61",
                  		"key_id": "488",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "SR00365834",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Salkehatchie) LIBRARY COMPUTER SCIENCE",
                  		"date": "11/19/12",
                  		"building_id": "334",
                  		"key_id": "645",
                  		"bldg_id": "853",
                  		"cp": "00332669",
                  		"wo": "00414975",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1723-25 Green Street",
                  		"date": "11/08/12",
                  		"building_id": "94",
                  		"key_id": "468",
                  		"bldg_id": "44",
                  		"cp": "",
                  		"wo": "SR00364914",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "11/08/12",
                  		"building_id": "71",
                  		"key_id": "496",
                  		"bldg_id": "40",
                  		"cp": "00343190",
                  		"wo": "00411963",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "11/05/12",
                  		"building_id": "41",
                  		"key_id": "474",
                  		"bldg_id": "12",
                  		"cp": "00356776",
                  		"wo": "00411638",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "11/01/12",
                  		"building_id": "167",
                  		"key_id": "519",
                  		"bldg_id": "15",
                  		"cp": "00336856",
                  		"wo": "00411342",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "10/31/12",
                  		"building_id": "2",
                  		"key_id": "580",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "FM00385012",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/30/12",
                  		"building_id": "55",
                  		"key_id": "4",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/30/12",
                  		"building_id": "55",
                  		"key_id": "3",
                  		"bldg_id": "159",
                  		"cp": "test",
                  		"wo": "test",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "10/30/12",
                  		"building_id": "172",
                  		"key_id": "550",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "FM00410720",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "201 S. Marion Street",
                  		"date": "10/25/12",
                  		"building_id": "110",
                  		"key_id": "604",
                  		"bldg_id": "203",
                  		"cp": "",
                  		"wo": "00410723",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "10/24/12",
                  		"building_id": "249",
                  		"key_id": "649",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00410621",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "10/24/12",
                  		"building_id": "251",
                  		"key_id": "658",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00407776",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "President's House",
                  		"date": "10/19/12",
                  		"building_id": "117",
                  		"key_id": "473",
                  		"bldg_id": "69",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "109 S. Bull Street",
                  		"date": "10/18/12",
                  		"building_id": "113",
                  		"key_id": "606",
                  		"bldg_id": "202b",
                  		"cp": "",
                  		"wo": "00410237",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "10/17/12",
                  		"building_id": "72",
                  		"key_id": "412",
                  		"bldg_id": "4",
                  		"cp": "",
                  		"wo": "FM00409956",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "President's House",
                  		"date": "10/15/12",
                  		"building_id": "117",
                  		"key_id": "254",
                  		"bldg_id": "69",
                  		"cp": "",
                  		"wo": "FM00409810",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "10/12/12",
                  		"building_id": "88",
                  		"key_id": "231",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "00362461",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "10/08/12",
                  		"building_id": "49",
                  		"key_id": "256",
                  		"bldg_id": "140",
                  		"cp": "",
                  		"wo": "FM00409997",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #2",
                  		"date": "10/03/12",
                  		"building_id": "250",
                  		"key_id": "639",
                  		"bldg_id": "662",
                  		"cp": "",
                  		"wo": "00406629",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #2",
                  		"date": "10/03/12",
                  		"building_id": "250",
                  		"key_id": "640",
                  		"bldg_id": "662",
                  		"cp": "",
                  		"wo": "00406629",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) 5 MEDICAL PARK",
                  		"date": "10/01/12",
                  		"building_id": "232",
                  		"key_id": "636",
                  		"bldg_id": "601",
                  		"cp": "",
                  		"wo": "00408632",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "10/01/12",
                  		"building_id": "249",
                  		"key_id": "638",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00408168",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"date": "10/01/12",
                  		"building_id": "252",
                  		"key_id": "641",
                  		"bldg_id": "664",
                  		"cp": "",
                  		"wo": "00408169",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "10/01/12",
                  		"building_id": "61",
                  		"key_id": "255",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "FM00408630",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "09/27/12",
                  		"building_id": "15",
                  		"key_id": "260",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "FM00408360",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "09/26/12",
                  		"building_id": "8",
                  		"key_id": "261",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "FM00408298",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "09/26/12",
                  		"building_id": "10",
                  		"key_id": "266",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "FM00387133",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "09/26/12",
                  		"building_id": "8",
                  		"key_id": "623",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "00408166",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Salkehatchie) WALTERBORO MAIN BUILDING",
                  		"date": "09/24/12",
                  		"building_id": "339",
                  		"key_id": "644",
                  		"bldg_id": "858",
                  		"cp": "00332669",
                  		"wo": "00408034",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "09/21/12",
                  		"building_id": "177",
                  		"key_id": "264",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "FM00407571",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "09/21/12",
                  		"building_id": "56",
                  		"key_id": "234",
                  		"bldg_id": "16",
                  		"cp": "",
                  		"wo": "FM00402659",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "09/20/12",
                  		"building_id": "48",
                  		"key_id": "272",
                  		"bldg_id": "14",
                  		"cp": "",
                  		"wo": "FM00402686",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "514 Main Street (UTS Annex)",
                  		"date": "09/19/12",
                  		"building_id": "175",
                  		"key_id": "273",
                  		"bldg_id": "145",
                  		"cp": "",
                  		"wo": "FM00392537",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "09/19/12",
                  		"building_id": "23",
                  		"key_id": "475",
                  		"bldg_id": "173",
                  		"cp": "00354580",
                  		"wo": "00407634",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Georgetown) HOBCAW MARINE LABORATORY",
                  		"date": "09/18/12",
                  		"building_id": "360",
                  		"key_id": "643",
                  		"bldg_id": "222",
                  		"cp": "00320788",
                  		"wo": "00407491",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Discovery Building",
                  		"date": "09/12/12",
                  		"building_id": "191",
                  		"key_id": "673",
                  		"bldg_id": "230",
                  		"cp": "00291151",
                  		"wo": "00407008",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "09/11/12",
                  		"building_id": "86",
                  		"key_id": "487",
                  		"bldg_id": "90",
                  		"cp": "00347722",
                  		"wo": "00392274",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) ADMINISTRATION",
                  		"date": "09/10/12",
                  		"building_id": "298",
                  		"key_id": "672",
                  		"bldg_id": "880",
                  		"cp": "00250841",
                  		"wo": "00405117",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "09/07/12",
                  		"building_id": "9",
                  		"key_id": "548",
                  		"bldg_id": "100",
                  		"cp": "00320407",
                  		"wo": "00361888",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "08/29/12",
                  		"building_id": "14",
                  		"key_id": "610",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "00405519",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"date": "08/27/12",
                  		"building_id": "255",
                  		"key_id": "665",
                  		"bldg_id": "667",
                  		"cp": "",
                  		"wo": "00405456",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "08/24/12",
                  		"building_id": "125",
                  		"key_id": "525",
                  		"bldg_id": "103",
                  		"cp": "00336849",
                  		"wo": "00380601",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "08/21/12",
                  		"building_id": "8",
                  		"key_id": "521",
                  		"bldg_id": "1",
                  		"cp": "00336855",
                  		"wo": "00404714",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "08/20/12",
                  		"building_id": "177",
                  		"key_id": "477",
                  		"bldg_id": "210",
                  		"cp": "00353757",
                  		"wo": "00399280",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "08/18/12",
                  		"building_id": "41",
                  		"key_id": "27",
                  		"bldg_id": "12",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "08/10/12",
                  		"building_id": "249",
                  		"key_id": "637",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00403506",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "109 S. Bull Street",
                  		"date": "08/09/12",
                  		"building_id": "113",
                  		"key_id": "605",
                  		"bldg_id": "202b",
                  		"cp": "",
                  		"wo": "00403705",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "08/09/12",
                  		"building_id": "249",
                  		"key_id": "654",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00403510",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "08/08/12",
                  		"building_id": "249",
                  		"key_id": "652",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00403507",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"date": "08/08/12",
                  		"building_id": "252",
                  		"key_id": "677",
                  		"bldg_id": "664",
                  		"cp": "00358551",
                  		"wo": "00403515",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "08/07/12",
                  		"building_id": "43",
                  		"key_id": "42",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Stephen Wagstaff"
                  	},

                  	{
                  		"name": "Psychology Annex (819 Barnwell Street)",
                  		"date": "08/07/12",
                  		"building_id": "115",
                  		"key_id": "619",
                  		"bldg_id": "34",
                  		"cp": "",
                  		"wo": "00403252",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1501 Senate Street",
                  		"date": "08/07/12",
                  		"building_id": "96",
                  		"key_id": "620",
                  		"bldg_id": "28",
                  		"cp": "",
                  		"wo": "00403250",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Senate Street Parking Garage",
                  		"date": "08/07/12",
                  		"building_id": "5",
                  		"key_id": "465",
                  		"bldg_id": "31",
                  		"cp": "",
                  		"wo": "SR00356983",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "08/07/12",
                  		"building_id": "171",
                  		"key_id": "503",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "FM00402290",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "08/06/12",
                  		"building_id": "171",
                  		"key_id": "530",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "FM00403205",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "08/06/12",
                  		"building_id": "171",
                  		"key_id": "613",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00402708",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "08/03/12",
                  		"building_id": "43",
                  		"key_id": "41",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Stephen Wagstaff"
                  	},

                  	{
                  		"name": "1723-25 Green Street",
                  		"date": "08/03/12",
                  		"building_id": "94",
                  		"key_id": "53",
                  		"bldg_id": "44",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "07/31/12",
                  		"building_id": "23",
                  		"key_id": "478",
                  		"bldg_id": "173",
                  		"cp": "00351249",
                  		"wo": "00396236",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Strom Thurmond Wellness and Fitness Center",
                  		"date": "07/25/12",
                  		"building_id": "122",
                  		"key_id": "554",
                  		"bldg_id": "157",
                  		"cp": "",
                  		"wo": "FM00402144",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "07/24/12",
                  		"building_id": "41",
                  		"key_id": "26",
                  		"bldg_id": "12",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "07/24/12",
                  		"building_id": "43",
                  		"key_id": "40",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "07/24/12",
                  		"building_id": "15",
                  		"key_id": "94",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00315256",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1420 Henderson Street",
                  		"date": "07/23/12",
                  		"building_id": "222",
                  		"key_id": "648",
                  		"bldg_id": "628",
                  		"cp": "",
                  		"wo": "00400308",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "07/19/12",
                  		"building_id": "93",
                  		"key_id": "466",
                  		"bldg_id": "43",
                  		"cp": "",
                  		"wo": "SR00355287",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "07/18/12",
                  		"building_id": "172",
                  		"key_id": "551",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "FM00401393",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "07/18/12",
                  		"building_id": "66",
                  		"key_id": "413",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "FM00401371",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"date": "07/18/12",
                  		"building_id": "149",
                  		"key_id": "471",
                  		"bldg_id": "106",
                  		"cp": "00358545",
                  		"wo": "00401403",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "07/16/12",
                  		"building_id": "125",
                  		"key_id": "493",
                  		"bldg_id": "103",
                  		"cp": "00345236",
                  		"wo": "00389581",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "07/12/12",
                  		"building_id": "177",
                  		"key_id": "514",
                  		"bldg_id": "210",
                  		"cp": "00340524",
                  		"wo": "00397262",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "07/11/12",
                  		"building_id": "15",
                  		"key_id": "579",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "FM00400867",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "John Welsh Humanities Center",
                  		"date": "07/11/12",
                  		"building_id": "57",
                  		"key_id": "334",
                  		"bldg_id": "54",
                  		"cp": "",
                  		"wo": "FM00398233",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "07/11/12",
                  		"building_id": "15",
                  		"key_id": "609",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "00400867",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "07/11/12",
                  		"building_id": "92",
                  		"key_id": "490",
                  		"bldg_id": "29",
                  		"cp": "00345328",
                  		"wo": "00394368",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "07/10/12",
                  		"building_id": "23",
                  		"key_id": "596",
                  		"bldg_id": "173",
                  		"cp": "00395615",
                  		"wo": "00399653",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Colonial Center",
                  		"date": "07/10/12",
                  		"building_id": "124",
                  		"key_id": "479",
                  		"bldg_id": "158",
                  		"cp": "00351079",
                  		"wo": "00396236",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - G",
                  		"date": "07/09/12",
                  		"building_id": "151",
                  		"key_id": "500",
                  		"bldg_id": "110",
                  		"cp": "",
                  		"wo": "FM00400598",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomson Student Health Center",
                  		"date": "07/09/12",
                  		"building_id": "27",
                  		"key_id": "501",
                  		"bldg_id": "111",
                  		"cp": "",
                  		"wo": "FM00400306",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "07/02/12",
                  		"building_id": "2",
                  		"key_id": "7",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Institute of Archaeology and Anthropology",
                  		"date": "07/02/12",
                  		"building_id": "33",
                  		"key_id": "476",
                  		"bldg_id": "7",
                  		"cp": "00354561",
                  		"wo": "00400107",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "06/29/12",
                  		"building_id": "15",
                  		"key_id": "578",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "FM00400323",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "06/29/12",
                  		"building_id": "15",
                  		"key_id": "93",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00315256",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/29/12",
                  		"building_id": "171",
                  		"key_id": "483",
                  		"bldg_id": "112",
                  		"cp": "00348458",
                  		"wo": "00393105",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Currell Annex",
                  		"date": "06/25/12",
                  		"building_id": "31",
                  		"key_id": "310",
                  		"bldg_id": "68",
                  		"cp": "",
                  		"wo": "FM00414623",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "06/23/12",
                  		"building_id": "169",
                  		"key_id": "326",
                  		"bldg_id": "36",
                  		"cp": "CP00351005",
                  		"wo": "FM00399310",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Institute of Archaeology and Anthropology",
                  		"date": "06/23/12",
                  		"building_id": "33",
                  		"key_id": "625",
                  		"bldg_id": "7",
                  		"cp": "00351005",
                  		"wo": "00399313",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"date": "06/22/12",
                  		"building_id": "149",
                  		"key_id": "102",
                  		"bldg_id": "106",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "World War Memorial",
                  		"date": "06/22/12",
                  		"building_id": "82",
                  		"key_id": "408",
                  		"bldg_id": "5",
                  		"cp": "",
                  		"wo": "FM00398450",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"date": "06/22/12",
                  		"building_id": "149",
                  		"key_id": "499",
                  		"bldg_id": "106",
                  		"cp": "",
                  		"wo": "FM00400601",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1629 Pendleton Street",
                  		"date": "06/20/12",
                  		"building_id": "1",
                  		"key_id": "1",
                  		"bldg_id": "35",
                  		"cp": "",
                  		"wo": "FM00399121",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "06/20/12",
                  		"building_id": "35",
                  		"key_id": "82",
                  		"bldg_id": "70",
                  		"cp": "",
                  		"wo": "00399121",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/20/12",
                  		"building_id": "125",
                  		"key_id": "615",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00396821",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Inventory Central Supply",
                  		"date": "06/20/12",
                  		"building_id": "98",
                  		"key_id": "202",
                  		"bldg_id": "81",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Inventory Central Supply",
                  		"date": "06/20/12",
                  		"building_id": "98",
                  		"key_id": "492",
                  		"bldg_id": "81",
                  		"cp": "",
                  		"wo": "SR00354325",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/20/12",
                  		"building_id": "125",
                  		"key_id": "497",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "FM00396820",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/20/12",
                  		"building_id": "125",
                  		"key_id": "498",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "FM00400003",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/19/12",
                  		"building_id": "171",
                  		"key_id": "467",
                  		"bldg_id": "112",
                  		"cp": "00358545",
                  		"wo": "00396472",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/19/12",
                  		"building_id": "171",
                  		"key_id": "469",
                  		"bldg_id": "112",
                  		"cp": "00358545",
                  		"wo": "00398264",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "06/18/12",
                  		"building_id": "2",
                  		"key_id": "2",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "tbd",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/18/12",
                  		"building_id": "171",
                  		"key_id": "612",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00398857",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "06/18/12",
                  		"building_id": "85",
                  		"key_id": "386",
                  		"bldg_id": "88",
                  		"cp": "",
                  		"wo": "Fm00398856",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Georgetown) HOBCAW MARINE LABORATORY",
                  		"date": "06/18/12",
                  		"building_id": "360",
                  		"key_id": "646",
                  		"bldg_id": "222",
                  		"cp": "",
                  		"wo": "00401243",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/14/12",
                  		"building_id": "177",
                  		"key_id": "589",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "FM00397902",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Sloan College",
                  		"date": "06/13/12",
                  		"building_id": "17",
                  		"key_id": "280",
                  		"bldg_id": "17",
                  		"cp": "",
                  		"wo": "FM00411897",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/13/12",
                  		"building_id": "177",
                  		"key_id": "537",
                  		"bldg_id": "210",
                  		"cp": "00326762",
                  		"wo": "00368982",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/13/12",
                  		"building_id": "177",
                  		"key_id": "603",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00396289",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "School of Music",
                  		"date": "06/13/12",
                  		"building_id": "89",
                  		"key_id": "383",
                  		"bldg_id": "86a",
                  		"cp": "",
                  		"wo": "Fm00398457",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "513 Pickens Street",
                  		"date": "06/08/12",
                  		"building_id": "181",
                  		"key_id": "290",
                  		"bldg_id": "129",
                  		"cp": "",
                  		"wo": "FM00414623",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/08/12",
                  		"building_id": "171",
                  		"key_id": "212",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00398045",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/08/12",
                  		"building_id": "177",
                  		"key_id": "480",
                  		"bldg_id": "210",
                  		"cp": "00349445",
                  		"wo": "00394141",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/08/12",
                  		"building_id": "177",
                  		"key_id": "481",
                  		"bldg_id": "210",
                  		"cp": "00349445",
                  		"wo": "00394141",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Public Health Research Center",
                  		"date": "06/08/12",
                  		"building_id": "179",
                  		"key_id": "510",
                  		"bldg_id": "156a",
                  		"cp": "00341959",
                  		"wo": "00398178",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "300 Main Street",
                  		"date": "06/07/12",
                  		"building_id": "157",
                  		"key_id": "257",
                  		"bldg_id": "170",
                  		"cp": "",
                  		"wo": "FM00416498",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "300 Main Street",
                  		"date": "06/07/12",
                  		"building_id": "157",
                  		"key_id": "288",
                  		"bldg_id": "170",
                  		"cp": "",
                  		"wo": "FM00397408",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "06/07/12",
                  		"building_id": "28",
                  		"key_id": "308",
                  		"bldg_id": "18",
                  		"cp": "",
                  		"wo": "FM00414623",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Flinn Hall",
                  		"date": "06/07/12",
                  		"building_id": "81",
                  		"key_id": "328",
                  		"bldg_id": "6",
                  		"cp": "CP00351005",
                  		"wo": "FM00397974",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Strom Thurmond Wellness and Fitness Center",
                  		"date": "06/07/12",
                  		"building_id": "122",
                  		"key_id": "611",
                  		"bldg_id": "157",
                  		"cp": "",
                  		"wo": "00397893",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "06/07/12",
                  		"building_id": "61",
                  		"key_id": "617",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "00397892",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "06/06/12",
                  		"building_id": "16",
                  		"key_id": "327",
                  		"bldg_id": "11",
                  		"cp": "CP00351005",
                  		"wo": "FM00397889",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "06/06/12",
                  		"building_id": "83",
                  		"key_id": "331",
                  		"bldg_id": "51",
                  		"cp": "CP00351005",
                  		"wo": "FM00397890",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"date": "06/05/12",
                  		"building_id": "182",
                  		"key_id": "16",
                  		"bldg_id": "146a",
                  		"cp": "",
                  		"wo": "00397891",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"date": "06/05/12",
                  		"building_id": "182",
                  		"key_id": "553",
                  		"bldg_id": "146a",
                  		"cp": "",
                  		"wo": "FM00397184",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "06/05/12",
                  		"building_id": "171",
                  		"key_id": "624",
                  		"bldg_id": "112",
                  		"cp": "00351005",
                  		"wo": "00397257",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/01/12",
                  		"building_id": "177",
                  		"key_id": "513",
                  		"bldg_id": "210",
                  		"cp": "00340524",
                  		"wo": "00385048",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "06/01/12",
                  		"building_id": "48",
                  		"key_id": "539",
                  		"bldg_id": "14",
                  		"cp": "00326285",
                  		"wo": "00397409",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "06/01/12",
                  		"building_id": "45",
                  		"key_id": "565",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00385724",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Graduate Science Research Center",
                  		"date": "06/01/12",
                  		"building_id": "162",
                  		"key_id": "214",
                  		"bldg_id": "114",
                  		"cp": "",
                  		"wo": "00397184",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "06/01/12",
                  		"building_id": "86",
                  		"key_id": "494",
                  		"bldg_id": "90",
                  		"cp": "",
                  		"wo": "SR00351999",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "05/31/12",
                  		"building_id": "68",
                  		"key_id": "363",
                  		"bldg_id": "85",
                  		"cp": "",
                  		"wo": "FM00397406",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Sumter) NETTLES",
                  		"date": "05/31/12",
                  		"building_id": "304",
                  		"key_id": "674",
                  		"bldg_id": "885A",
                  		"cp": "00314724",
                  		"wo": "00395270",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "05/30/12",
                  		"building_id": "9",
                  		"key_id": "309",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "FM00414623",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "05/30/12",
                  		"building_id": "8",
                  		"key_id": "233",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "1212125",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "05/29/12",
                  		"building_id": "2",
                  		"key_id": "63",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00351165",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "05/29/12",
                  		"building_id": "10",
                  		"key_id": "575",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "FM00396811",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "05/29/12",
                  		"building_id": "71",
                  		"key_id": "101",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "SR00348050",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Longstreet Annex",
                  		"date": "05/29/12",
                  		"building_id": "11",
                  		"key_id": "381",
                  		"bldg_id": "101",
                  		"cp": "",
                  		"wo": "FM00397261",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomson Student Health Center",
                  		"date": "05/29/12",
                  		"building_id": "27",
                  		"key_id": "384",
                  		"bldg_id": "111",
                  		"cp": "",
                  		"wo": "Fm00397259",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Welsh Humanities Classroom Building",
                  		"date": "05/28/12",
                  		"building_id": "58",
                  		"key_id": "543",
                  		"bldg_id": "55",
                  		"cp": "00324714",
                  		"wo": "00392264",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "05/25/12",
                  		"building_id": "125",
                  		"key_id": "598",
                  		"bldg_id": "103",
                  		"cp": "00347465",
                  		"wo": "00394619",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "05/25/12",
                  		"building_id": "125",
                  		"key_id": "608",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "FM00392736",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "The Colloquium",
                  		"date": "05/25/12",
                  		"building_id": "195",
                  		"key_id": "670",
                  		"bldg_id": "59",
                  		"cp": "00247679",
                  		"wo": "00396582",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "05/24/12",
                  		"building_id": "45",
                  		"key_id": "564",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00385724",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "05/24/12",
                  		"building_id": "109",
                  		"key_id": "78",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "05/23/12",
                  		"building_id": "88",
                  		"key_id": "108",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "00383289",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #104",
                  		"date": "05/23/12",
                  		"building_id": "254",
                  		"key_id": "676",
                  		"bldg_id": "666",
                  		"cp": "00336869",
                  		"wo": "00396806",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "05/23/12",
                  		"building_id": "9",
                  		"key_id": "495",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "FM00399169",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "05/22/12",
                  		"building_id": "67",
                  		"key_id": "586",
                  		"bldg_id": "84",
                  		"cp": "00247679",
                  		"wo": "00396809",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "05/21/12",
                  		"building_id": "92",
                  		"key_id": "583",
                  		"bldg_id": "29",
                  		"cp": "00247682",
                  		"wo": "00397020",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "The Colloquium",
                  		"date": "05/21/12",
                  		"building_id": "195",
                  		"key_id": "671",
                  		"bldg_id": "59",
                  		"cp": "00247679",
                  		"wo": "00396582",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "05/21/12",
                  		"building_id": "68",
                  		"key_id": "489",
                  		"bldg_id": "85",
                  		"cp": "00346700",
                  		"wo": "00396571",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "05/18/12",
                  		"building_id": "125",
                  		"key_id": "597",
                  		"bldg_id": "103",
                  		"cp": "00347465",
                  		"wo": "00394619",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "05/18/12",
                  		"building_id": "92",
                  		"key_id": "509",
                  		"bldg_id": "29",
                  		"cp": "00343092",
                  		"wo": "00395765",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/17/12",
                  		"building_id": "171",
                  		"key_id": "587",
                  		"bldg_id": "112",
                  		"cp": "00247678",
                  		"wo": "003961318",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/17/12",
                  		"building_id": "171",
                  		"key_id": "484",
                  		"bldg_id": "112",
                  		"cp": "00348456",
                  		"wo": "00394370",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Strom Thurmond Wellness and Fitness Center",
                  		"date": "05/16/12",
                  		"building_id": "122",
                  		"key_id": "516",
                  		"bldg_id": "157",
                  		"cp": "00337037",
                  		"wo": "00394612",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "05/16/12",
                  		"building_id": "251",
                  		"key_id": "657",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00395651",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "05/15/12",
                  		"building_id": "88",
                  		"key_id": "570",
                  		"bldg_id": "9",
                  		"cp": "00285789",
                  		"wo": "00383289",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"date": "05/14/12",
                  		"building_id": "182",
                  		"key_id": "464",
                  		"bldg_id": "146a",
                  		"cp": "00358545",
                  		"wo": "00411131",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Quadrangle",
                  		"date": "05/11/12",
                  		"building_id": "152",
                  		"key_id": "44",
                  		"bldg_id": "135",
                  		"cp": "",
                  		"wo": "00395763",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "05/11/12",
                  		"building_id": "177",
                  		"key_id": "601",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00395816",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "05/09/12",
                  		"building_id": "86",
                  		"key_id": "283",
                  		"bldg_id": "90",
                  		"cp": "",
                  		"wo": "FM00383323",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/09/12",
                  		"building_id": "171",
                  		"key_id": "584",
                  		"bldg_id": "112",
                  		"cp": "00247679",
                  		"wo": "00395611",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "05/07/12",
                  		"building_id": "10",
                  		"key_id": "37",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "SR00350494",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Colonial Center",
                  		"date": "05/07/12",
                  		"building_id": "124",
                  		"key_id": "555",
                  		"bldg_id": "158",
                  		"cp": "",
                  		"wo": "FM00394466",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "05/07/12",
                  		"building_id": "10",
                  		"key_id": "577",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "FM00395373",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/07/12",
                  		"building_id": "171",
                  		"key_id": "585",
                  		"bldg_id": "112",
                  		"cp": "00247679",
                  		"wo": "00395611",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "514 Main Street (UTS Annex)",
                  		"date": "05/03/12",
                  		"building_id": "175",
                  		"key_id": "552",
                  		"bldg_id": "145",
                  		"cp": "",
                  		"wo": "FM00395199",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "105 S. Bull Street",
                  		"date": "05/03/12",
                  		"building_id": "112",
                  		"key_id": "83",
                  		"bldg_id": "202a",
                  		"cp": "",
                  		"wo": "SR00349871",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "05/01/12",
                  		"building_id": "67",
                  		"key_id": "540",
                  		"bldg_id": "84",
                  		"cp": "00324723",
                  		"wo": "00366662",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "05/01/12",
                  		"building_id": "67",
                  		"key_id": "557",
                  		"bldg_id": "84",
                  		"cp": "00311862",
                  		"wo": "00352594",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "05/01/12",
                  		"building_id": "170",
                  		"key_id": "59",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Davis College",
                  		"date": "05/01/12",
                  		"building_id": "22",
                  		"key_id": "574",
                  		"bldg_id": "62",
                  		"cp": "00254385",
                  		"wo": "00382929",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "05/01/12",
                  		"building_id": "2",
                  		"key_id": "67",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00349697",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "05/01/12",
                  		"building_id": "71",
                  		"key_id": "116",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "SR00348050",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "05/01/12",
                  		"building_id": "71",
                  		"key_id": "118",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "SR00349698",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "04/27/12",
                  		"building_id": "177",
                  		"key_id": "562",
                  		"bldg_id": "210",
                  		"cp": "00305528",
                  		"wo": "00346159",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Pendleton Street Garage",
                  		"date": "04/25/12",
                  		"building_id": "3",
                  		"key_id": "526",
                  		"bldg_id": "19",
                  		"cp": "00332680",
                  		"wo": "00388283",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "04/25/12",
                  		"building_id": "23",
                  		"key_id": "541",
                  		"bldg_id": "173",
                  		"cp": "00324719",
                  		"wo": "00388282",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "04/24/12",
                  		"building_id": "249",
                  		"key_id": "653",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00394369",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "04/24/12",
                  		"building_id": "251",
                  		"key_id": "655",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00394367",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "04/20/12",
                  		"building_id": "16",
                  		"key_id": "122",
                  		"bldg_id": "11",
                  		"cp": "",
                  		"wo": "FM00381618",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "04/19/12",
                  		"building_id": "66",
                  		"key_id": "132",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00381877",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Longstreet Theatre",
                  		"date": "04/18/12",
                  		"building_id": "12",
                  		"key_id": "482",
                  		"bldg_id": "102",
                  		"cp": "00348804",
                  		"wo": "00393597",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "04/14/12",
                  		"building_id": "92",
                  		"key_id": "491",
                  		"bldg_id": "29",
                  		"cp": "00345328",
                  		"wo": "00394368",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Welsh Humanities Classroom Building",
                  		"date": "04/12/12",
                  		"building_id": "58",
                  		"key_id": "544",
                  		"bldg_id": "55",
                  		"cp": "00324714",
                  		"wo": "00366649",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "04/11/12",
                  		"building_id": "177",
                  		"key_id": "504",
                  		"bldg_id": "210",
                  		"cp": "00343189",
                  		"wo": "00387461",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "04/06/12",
                  		"building_id": "66",
                  		"key_id": "131",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00393841",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Longstreet Theatre",
                  		"date": "04/03/12",
                  		"building_id": "12",
                  		"key_id": "486",
                  		"bldg_id": "102",
                  		"cp": "00347966",
                  		"wo": "0392545",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/02/12",
                  		"building_id": "59",
                  		"key_id": "567",
                  		"bldg_id": "56",
                  		"cp": "00285941",
                  		"wo": "00324359",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "03/29/12",
                  		"building_id": "249",
                  		"key_id": "650",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00392332",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "03/23/12",
                  		"building_id": "177",
                  		"key_id": "561",
                  		"bldg_id": "210",
                  		"cp": "00305528",
                  		"wo": "00391844",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "03/23/12",
                  		"building_id": "92",
                  		"key_id": "582",
                  		"bldg_id": "29",
                  		"cp": "00247682",
                  		"wo": "00391693",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "03/22/12",
                  		"building_id": "85",
                  		"key_id": "532",
                  		"bldg_id": "88",
                  		"cp": "00330687",
                  		"wo": "00380122",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/21/12",
                  		"building_id": "8",
                  		"key_id": "409",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "FM00388036",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "03/20/12",
                  		"building_id": "125",
                  		"key_id": "219",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00380601",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "03/19/12",
                  		"building_id": "9",
                  		"key_id": "546",
                  		"bldg_id": "100",
                  		"cp": "00321821",
                  		"wo": "00391311",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "03/16/12",
                  		"building_id": "88",
                  		"key_id": "107",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "03/15/12",
                  		"building_id": "90",
                  		"key_id": "616",
                  		"bldg_id": "84a",
                  		"cp": "",
                  		"wo": "000391097",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "03/14/12",
                  		"building_id": "177",
                  		"key_id": "591",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "FM00397902",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "03/13/12",
                  		"building_id": "170",
                  		"key_id": "593",
                  		"bldg_id": "39",
                  		"cp": "00246261",
                  		"wo": "00390670",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #101",
                  		"date": "03/13/12",
                  		"building_id": "253",
                  		"key_id": "660",
                  		"bldg_id": "665",
                  		"cp": "",
                  		"wo": "00390820",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wardlaw College",
                  		"date": "03/12/12",
                  		"building_id": "65",
                  		"key_id": "594",
                  		"bldg_id": "80",
                  		"cp": "00237866",
                  		"wo": "00270196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "03/08/12",
                  		"building_id": "14",
                  		"key_id": "573",
                  		"bldg_id": "162",
                  		"cp": "00254387",
                  		"wo": "00390270",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "03/08/12",
                  		"building_id": "249",
                  		"key_id": "651",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00390495",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "03/07/12",
                  		"building_id": "172",
                  		"key_id": "595",
                  		"bldg_id": "138",
                  		"cp": "00237866",
                  		"wo": "00270196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "03/06/12",
                  		"building_id": "6",
                  		"key_id": "515",
                  		"bldg_id": "33",
                  		"cp": "00338095",
                  		"wo": "00381546",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "03/02/12",
                  		"building_id": "177",
                  		"key_id": "506",
                  		"bldg_id": "210",
                  		"cp": "00343189",
                  		"wo": "00389740",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "02/29/12",
                  		"building_id": "170",
                  		"key_id": "52",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "SR00345591",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "02/29/12",
                  		"building_id": "14",
                  		"key_id": "72",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "SR00345480",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "02/29/12",
                  		"building_id": "171",
                  		"key_id": "588",
                  		"bldg_id": "112",
                  		"cp": "00247678",
                  		"wo": "00389741",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "02/29/12",
                  		"building_id": "109",
                  		"key_id": "79",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "00389799",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "02/29/12",
                  		"building_id": "71",
                  		"key_id": "117",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "SR00348050",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "02/29/12",
                  		"building_id": "251",
                  		"key_id": "656",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00389855",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "513 Pickens Street",
                  		"date": "02/28/12",
                  		"building_id": "181",
                  		"key_id": "581",
                  		"bldg_id": "129",
                  		"cp": "00247683",
                  		"wo": "00389785",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "02/27/12",
                  		"building_id": "83",
                  		"key_id": "545",
                  		"bldg_id": "51",
                  		"cp": "00324712",
                  		"wo": "00388280",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "02/23/12",
                  		"building_id": "167",
                  		"key_id": "547",
                  		"bldg_id": "15",
                  		"cp": "00320790",
                  		"wo": "00389086",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "02/23/12",
                  		"building_id": "68",
                  		"key_id": "508",
                  		"bldg_id": "85",
                  		"cp": "00343183",
                  		"wo": "00387473",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Middleburg Plaza Speech & Hearing",
                  		"date": "02/22/12",
                  		"building_id": "214",
                  		"key_id": "647",
                  		"bldg_id": "217A",
                  		"cp": "",
                  		"wo": "00389297",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "John Welsh Humanities Center",
                  		"date": "02/21/12",
                  		"building_id": "57",
                  		"key_id": "517",
                  		"bldg_id": "54",
                  		"cp": "00336859",
                  		"wo": "00389294",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "02/21/12",
                  		"building_id": "68",
                  		"key_id": "524",
                  		"bldg_id": "85",
                  		"cp": "00336850",
                  		"wo": "00385358",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Welsh Humanities Classroom Building",
                  		"date": "02/20/12",
                  		"building_id": "58",
                  		"key_id": "518",
                  		"bldg_id": "55",
                  		"cp": "00336858",
                  		"wo": "00389293",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street Annex",
                  		"date": "02/20/12",
                  		"building_id": "159",
                  		"key_id": "523",
                  		"bldg_id": "29a",
                  		"cp": "00336853",
                  		"wo": "00389189",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "02/20/12",
                  		"building_id": "43",
                  		"key_id": "30",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/20/12",
                  		"building_id": "177",
                  		"key_id": "560",
                  		"bldg_id": "210",
                  		"cp": "00306823",
                  		"wo": "00388279",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/17/12",
                  		"building_id": "177",
                  		"key_id": "505",
                  		"bldg_id": "210",
                  		"cp": "00343189",
                  		"wo": "00388284",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "02/16/12",
                  		"building_id": "28",
                  		"key_id": "442",
                  		"bldg_id": "18",
                  		"cp": "",
                  		"wo": "SR00344664",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/15/12",
                  		"building_id": "177",
                  		"key_id": "602",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00388287",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Pendleton Street Garage",
                  		"date": "02/14/12",
                  		"building_id": "3",
                  		"key_id": "528",
                  		"bldg_id": "19",
                  		"cp": "00332680",
                  		"wo": "00388283",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "02/13/12",
                  		"building_id": "85",
                  		"key_id": "542",
                  		"bldg_id": "88",
                  		"cp": "00324716",
                  		"wo": "00366703",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/12",
                  		"building_id": "125",
                  		"key_id": "599",
                  		"bldg_id": "103",
                  		"cp": "00342465",
                  		"wo": "00388285",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Middleburg Plaza CD",
                  		"date": "02/13/12",
                  		"building_id": "213",
                  		"key_id": "669",
                  		"bldg_id": "217",
                  		"cp": " BC00342303",
                  		"wo": "00388461",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Pendleton Street Garage",
                  		"date": "02/10/12",
                  		"building_id": "3",
                  		"key_id": "527",
                  		"bldg_id": "19",
                  		"cp": "00332680",
                  		"wo": "00388283",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"date": "02/10/12",
                  		"building_id": "255",
                  		"key_id": "666",
                  		"bldg_id": "667",
                  		"cp": "",
                  		"wo": "00388288",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "02/09/12",
                  		"building_id": "171",
                  		"key_id": "502",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "FM00388134",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/09/12",
                  		"building_id": "177",
                  		"key_id": "507",
                  		"bldg_id": "210",
                  		"cp": "00343189",
                  		"wo": "00388284",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "02/08/12",
                  		"building_id": "67",
                  		"key_id": "571",
                  		"bldg_id": "84",
                  		"cp": "00285786",
                  		"wo": "00388528",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "02/08/12",
                  		"building_id": "2",
                  		"key_id": "65",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00343790",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "02/08/12",
                  		"building_id": "2",
                  		"key_id": "69",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00343792",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "02/08/12",
                  		"building_id": "2",
                  		"key_id": "70",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00343676",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/07/12",
                  		"building_id": "177",
                  		"key_id": "559",
                  		"bldg_id": "210",
                  		"cp": "00306823",
                  		"wo": "00388279",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "02/07/12",
                  		"building_id": "177",
                  		"key_id": "600",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00387925",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "02/03/12",
                  		"building_id": "8",
                  		"key_id": "520",
                  		"bldg_id": "1",
                  		"cp": "00336855",
                  		"wo": "00387504",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"date": "02/03/12",
                  		"building_id": "255",
                  		"key_id": "668",
                  		"bldg_id": "667",
                  		"cp": "",
                  		"wo": "00387933",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "01/31/12",
                  		"building_id": "14",
                  		"key_id": "6",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "01/31/12",
                  		"building_id": "59",
                  		"key_id": "529",
                  		"bldg_id": "56",
                  		"cp": "00331985",
                  		"wo": "00375244",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "01/30/12",
                  		"building_id": "171",
                  		"key_id": "558",
                  		"bldg_id": "112",
                  		"cp": "00310496",
                  		"wo": "00386879",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "National Advocacy Center",
                  		"date": "01/27/12",
                  		"building_id": "70",
                  		"key_id": "444",
                  		"bldg_id": "27",
                  		"cp": "",
                  		"wo": "SR00342065",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Salkehatchie) WALTERBORO SCIENCE BUILDING",
                  		"date": "01/26/12",
                  		"building_id": "340",
                  		"key_id": "675",
                  		"bldg_id": "858A",
                  		"cp": "00329825",
                  		"wo": "00381878",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/24/12",
                  		"building_id": "55",
                  		"key_id": "522",
                  		"bldg_id": "159",
                  		"cp": "00336854",
                  		"wo": "00386524",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "01/24/12",
                  		"building_id": "88",
                  		"key_id": "414",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "SR00342684",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "109 S. Bull Street",
                  		"date": "01/23/12",
                  		"building_id": "113",
                  		"key_id": "114",
                  		"bldg_id": "202b",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "01/18/12",
                  		"building_id": "85",
                  		"key_id": "533",
                  		"bldg_id": "88",
                  		"cp": "00330687",
                  		"wo": "00373813",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "01/18/12",
                  		"building_id": "88",
                  		"key_id": "569",
                  		"bldg_id": "9",
                  		"cp": "00285789",
                  		"wo": "00383405",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "01/17/12",
                  		"building_id": "59",
                  		"key_id": "563",
                  		"bldg_id": "56",
                  		"cp": "00303131",
                  		"wo": "00385687",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "01/13/12",
                  		"building_id": "92",
                  		"key_id": "622",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "00385677",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"date": "01/13/12",
                  		"building_id": "255",
                  		"key_id": "667",
                  		"bldg_id": "667",
                  		"cp": "",
                  		"wo": "00385739",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "01/12/12",
                  		"building_id": "8",
                  		"key_id": "410",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "SR00340424",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "01/11/12",
                  		"building_id": "169",
                  		"key_id": "512",
                  		"bldg_id": "36",
                  		"cp": "00340541",
                  		"wo": "00385360",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle B",
                  		"date": "01/11/12",
                  		"building_id": "183",
                  		"key_id": "17",
                  		"bldg_id": "146b",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "01/11/12",
                  		"building_id": "171",
                  		"key_id": "531",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "FM00385430",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #101",
                  		"date": "01/11/12",
                  		"building_id": "253",
                  		"key_id": "659",
                  		"bldg_id": "665",
                  		"cp": "",
                  		"wo": "00385356",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "01/06/12",
                  		"building_id": "167",
                  		"key_id": "572",
                  		"bldg_id": "15",
                  		"cp": "00264430",
                  		"wo": "00384993",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Davis College",
                  		"date": "01/06/12",
                  		"building_id": "22",
                  		"key_id": "511",
                  		"bldg_id": "62",
                  		"cp": "00340541",
                  		"wo": "00382929",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "01/05/12",
                  		"building_id": "43",
                  		"key_id": "549",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "FM00385014",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "01/05/12",
                  		"building_id": "43",
                  		"key_id": "39",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "01/05/12",
                  		"building_id": "10",
                  		"key_id": "576",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "FM00385013",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "01/04/12",
                  		"building_id": "10",
                  		"key_id": "35",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "SR00340943",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "01/04/12",
                  		"building_id": "45",
                  		"key_id": "566",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00385724",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "12/14/11",
                  		"building_id": "18",
                  		"key_id": "330",
                  		"bldg_id": "61",
                  		"cp": "",
                  		"wo": "00374091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "12/12/11",
                  		"building_id": "68",
                  		"key_id": "450",
                  		"bldg_id": "85",
                  		"cp": "00323103",
                  		"wo": "00382475",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "12/09/11",
                  		"building_id": "9",
                  		"key_id": "432",
                  		"bldg_id": "100",
                  		"cp": "00320407",
                  		"wo": "00383406",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Greek Housing",
                  		"date": "12/08/11",
                  		"building_id": "144",
                  		"key_id": "142",
                  		"bldg_id": "148",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Sumter) NETTLES",
                  		"date": "12/06/11",
                  		"building_id": "304",
                  		"key_id": "683",
                  		"bldg_id": "885A",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) ADMINISTRATION",
                  		"date": "12/06/11",
                  		"building_id": "298",
                  		"key_id": "684",
                  		"bldg_id": "880",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) SCIENCE BUILDING",
                  		"date": "12/06/11",
                  		"building_id": "300",
                  		"key_id": "685",
                  		"bldg_id": "882",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) BUSINESS ADMINISTRATION",
                  		"date": "12/06/11",
                  		"building_id": "299",
                  		"key_id": "686",
                  		"bldg_id": "881",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) STUDENT UNION",
                  		"date": "12/06/11",
                  		"building_id": "302",
                  		"key_id": "687",
                  		"bldg_id": "884",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) SCHWARTZ BUILDING",
                  		"date": "12/06/11",
                  		"building_id": "303",
                  		"key_id": "688",
                  		"bldg_id": "885",
                  		"cp": "00314724",
                  		"wo": "00383196",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "12/05/11",
                  		"building_id": "85",
                  		"key_id": "292",
                  		"bldg_id": "88",
                  		"cp": "",
                  		"wo": "00383194",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "11/29/11",
                  		"building_id": "172",
                  		"key_id": "455",
                  		"bldg_id": "138",
                  		"cp": "00326285",
                  		"wo": "00382815",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "11/22/11",
                  		"building_id": "86",
                  		"key_id": "406",
                  		"bldg_id": "90",
                  		"cp": "00314593",
                  		"wo": "00382475",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) 15 MEDICAL PARK/CLINICAL EDUCATION",
                  		"date": "11/21/11",
                  		"building_id": "255",
                  		"key_id": "698",
                  		"bldg_id": "667",
                  		"cp": "",
                  		"wo": "00382363",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "11/21/11",
                  		"building_id": "169",
                  		"key_id": "461",
                  		"bldg_id": "36",
                  		"cp": "00335962",
                  		"wo": "00382356",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "11/18/11",
                  		"building_id": "109",
                  		"key_id": "77",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "11/17/11",
                  		"building_id": "2",
                  		"key_id": "68",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00338380",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "11/16/11",
                  		"building_id": "9",
                  		"key_id": "421",
                  		"bldg_id": "100",
                  		"cp": "00317259",
                  		"wo": "00380994",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "11/14/11",
                  		"building_id": "2",
                  		"key_id": "299",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "00381357",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "11/14/11",
                  		"building_id": "88",
                  		"key_id": "300",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "00381357",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "11/14/11",
                  		"building_id": "15",
                  		"key_id": "92",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00315256",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - F",
                  		"date": "11/14/11",
                  		"building_id": "150",
                  		"key_id": "103",
                  		"bldg_id": "109",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Aiken) SCIENCE BUILDING",
                  		"date": "11/08/11",
                  		"building_id": "397",
                  		"key_id": "695",
                  		"bldg_id": "910",
                  		"cp": "00274456",
                  		"wo": "00380123",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "11/03/11",
                  		"building_id": "18",
                  		"key_id": "337",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "FM00374046",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Aiken) ETHERREDGE CENTER",
                  		"date": "11/02/11",
                  		"building_id": "395",
                  		"key_id": "680",
                  		"bldg_id": "909",
                  		"cp": "00324330",
                  		"wo": "00380125",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Aiken) BUSINESS & EDUCATION BLDG",
                  		"date": "11/02/11",
                  		"building_id": "403",
                  		"key_id": "681",
                  		"bldg_id": "916",
                  		"cp": "00323933",
                  		"wo": "00380124",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - G",
                  		"date": "11/01/11",
                  		"building_id": "151",
                  		"key_id": "104",
                  		"bldg_id": "110",
                  		"cp": "",
                  		"wo": "00380993",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "10/31/11",
                  		"building_id": "2",
                  		"key_id": "62",
                  		"bldg_id": "175",
                  		"cp": "",
                  		"wo": "SR00337275",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "10/31/11",
                  		"building_id": "169",
                  		"key_id": "323",
                  		"bldg_id": "36",
                  		"cp": "",
                  		"wo": "00374086",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Davis College",
                  		"date": "10/31/11",
                  		"building_id": "22",
                  		"key_id": "325",
                  		"bldg_id": "62",
                  		"cp": "",
                  		"wo": "00379959",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "10/31/11",
                  		"building_id": "9",
                  		"key_id": "329",
                  		"bldg_id": "100",
                  		"cp": "BC00335314",
                  		"wo": "00378548",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "10/31/11",
                  		"building_id": "92",
                  		"key_id": "342",
                  		"bldg_id": "29",
                  		"cp": "00247682",
                  		"wo": "00380491",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "10/31/11",
                  		"building_id": "167",
                  		"key_id": "438",
                  		"bldg_id": "15",
                  		"cp": "00320790",
                  		"wo": "00378248",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "10/27/11",
                  		"building_id": "177",
                  		"key_id": "317",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00379339",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "10/20/11",
                  		"building_id": "18",
                  		"key_id": "339",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "FM00374046",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "10/19/11",
                  		"building_id": "40",
                  		"key_id": "23",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "379807",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "10/18/11",
                  		"building_id": "18",
                  		"key_id": "338",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "FM00374046",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "10/17/11",
                  		"building_id": "249",
                  		"key_id": "703",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00379332",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "10/13/11",
                  		"building_id": "171",
                  		"key_id": "460",
                  		"bldg_id": "112",
                  		"cp": "00335651",
                  		"wo": "00378760",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "10/11/11",
                  		"building_id": "170",
                  		"key_id": "362",
                  		"bldg_id": "39",
                  		"cp": "00281290",
                  		"wo": "00378781",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "10/06/11",
                  		"building_id": "9",
                  		"key_id": "427",
                  		"bldg_id": "100",
                  		"cp": "00317845",
                  		"wo": "00378247",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Health Sciences Building",
                  		"date": "10/04/11",
                  		"building_id": "164",
                  		"key_id": "458",
                  		"bldg_id": "76",
                  		"cp": "00332833",
                  		"wo": "00378244",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "09/30/11",
                  		"building_id": "167",
                  		"key_id": "439",
                  		"bldg_id": "15",
                  		"cp": "00320790",
                  		"wo": "00362372",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "09/29/11",
                  		"building_id": "92",
                  		"key_id": "341",
                  		"bldg_id": "29",
                  		"cp": "00247682",
                  		"wo": "00377641",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"date": "09/29/11",
                  		"building_id": "301",
                  		"key_id": "679",
                  		"bldg_id": "883",
                  		"cp": "00326568",
                  		"wo": "00368823",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"date": "09/28/11",
                  		"building_id": "252",
                  		"key_id": "682",
                  		"bldg_id": "664",
                  		"cp": "00323933",
                  		"wo": "00377442",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #101",
                  		"date": "09/27/11",
                  		"building_id": "253",
                  		"key_id": "699",
                  		"bldg_id": "665",
                  		"cp": "",
                  		"wo": "00377442",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "09/22/11",
                  		"building_id": "49",
                  		"key_id": "307",
                  		"bldg_id": "140",
                  		"cp": "",
                  		"wo": "00376756",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "09/22/11",
                  		"building_id": "23",
                  		"key_id": "324",
                  		"bldg_id": "173",
                  		"cp": "",
                  		"wo": "00376612",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "09/16/11",
                  		"building_id": "18",
                  		"key_id": "333",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "00374091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "09/14/11",
                  		"building_id": "18",
                  		"key_id": "332",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "00374091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "09/14/11",
                  		"building_id": "67",
                  		"key_id": "347",
                  		"bldg_id": "84",
                  		"cp": "00264213",
                  		"wo": "00373643",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "09/12/11",
                  		"building_id": "249",
                  		"key_id": "694",
                  		"bldg_id": "661",
                  		"cp": "00291154",
                  		"wo": "00375578",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "09/08/11",
                  		"building_id": "67",
                  		"key_id": "346",
                  		"bldg_id": "84",
                  		"cp": "00264213",
                  		"wo": "00373643",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "09/07/11",
                  		"building_id": "6",
                  		"key_id": "454",
                  		"bldg_id": "33",
                  		"cp": "00326285",
                  		"wo": "00375250",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "09/06/11",
                  		"building_id": "90",
                  		"key_id": "457",
                  		"bldg_id": "84a",
                  		"cp": "00328097",
                  		"wo": "00375071",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "09/02/11",
                  		"building_id": "41",
                  		"key_id": "424",
                  		"bldg_id": "12",
                  		"cp": "00317525",
                  		"wo": "00358774",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "08/30/11",
                  		"building_id": "41",
                  		"key_id": "423",
                  		"bldg_id": "12",
                  		"cp": "00317525",
                  		"wo": "00373279",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "08/30/11",
                  		"building_id": "68",
                  		"key_id": "446",
                  		"bldg_id": "85",
                  		"cp": "00322882",
                  		"wo": "00374095",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomson Student Health Center",
                  		"date": "08/30/11",
                  		"building_id": "27",
                  		"key_id": "449",
                  		"bldg_id": "111",
                  		"cp": "00322882",
                  		"wo": "00374095",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "08/26/11",
                  		"building_id": "18",
                  		"key_id": "336",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "00374091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "08/25/11",
                  		"building_id": "18",
                  		"key_id": "335",
                  		"bldg_id": "61",
                  		"cp": "CP00003388",
                  		"wo": "00374091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "08/24/11",
                  		"building_id": "86",
                  		"key_id": "429",
                  		"bldg_id": "90",
                  		"cp": "00318959",
                  		"wo": "00360322",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "08/23/11",
                  		"building_id": "251",
                  		"key_id": "701",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00373645",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #1",
                  		"date": "08/23/11",
                  		"building_id": "249",
                  		"key_id": "702",
                  		"bldg_id": "661",
                  		"cp": "",
                  		"wo": "00373647",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "08/18/11",
                  		"building_id": "177",
                  		"key_id": "249",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00373297",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "08/11/11",
                  		"building_id": "177",
                  		"key_id": "453",
                  		"bldg_id": "210",
                  		"cp": "00325031",
                  		"wo": "00372167",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "08/08/11",
                  		"building_id": "38",
                  		"key_id": "99",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "08/01/11",
                  		"building_id": "8",
                  		"key_id": "397",
                  		"bldg_id": "1",
                  		"cp": "00306099",
                  		"wo": "00368419",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "07/29/11",
                  		"building_id": "177",
                  		"key_id": "315",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00371021",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Longstreet Theatre",
                  		"date": "07/28/11",
                  		"building_id": "12",
                  		"key_id": "377",
                  		"bldg_id": "102",
                  		"cp": "00285943",
                  		"wo": "00369439",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Longstreet Annex",
                  		"date": "07/28/11",
                  		"building_id": "11",
                  		"key_id": "378",
                  		"bldg_id": "101",
                  		"cp": "00285943",
                  		"wo": "00369439",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "07/25/11",
                  		"building_id": "68",
                  		"key_id": "445",
                  		"bldg_id": "85",
                  		"cp": "00321821",
                  		"wo": "00370662",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "07/22/11",
                  		"building_id": "86",
                  		"key_id": "393",
                  		"bldg_id": "90",
                  		"cp": "00299097",
                  		"wo": "00370249",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "07/19/11",
                  		"building_id": "170",
                  		"key_id": "48",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "SR00327670",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "07/15/11",
                  		"building_id": "86",
                  		"key_id": "394",
                  		"bldg_id": "90",
                  		"cp": "00299097",
                  		"wo": "00338722",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "07/14/11",
                  		"building_id": "28",
                  		"key_id": "322",
                  		"bldg_id": "18",
                  		"cp": "",
                  		"wo": "00369695",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "07/13/11",
                  		"building_id": "9",
                  		"key_id": "293",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "00369529",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "07/12/11",
                  		"building_id": "172",
                  		"key_id": "303",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00369224",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "07/12/11",
                  		"building_id": "8",
                  		"key_id": "430",
                  		"bldg_id": "1",
                  		"cp": "00318959",
                  		"wo": "00360322",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "07/11/11",
                  		"building_id": "43",
                  		"key_id": "437",
                  		"bldg_id": "122",
                  		"cp": "00320776",
                  		"wo": "00364706",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1501 Senate Street",
                  		"date": "07/11/11",
                  		"building_id": "96",
                  		"key_id": "248",
                  		"bldg_id": "28",
                  		"cp": "",
                  		"wo": "00369664",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "07/08/11",
                  		"building_id": "66",
                  		"key_id": "395",
                  		"bldg_id": "8",
                  		"cp": "00300708",
                  		"wo": "00368990",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "07/08/11",
                  		"building_id": "90",
                  		"key_id": "431",
                  		"bldg_id": "84a",
                  		"cp": "00320316",
                  		"wo": "00361781",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Melton Observatory",
                  		"date": "07/07/11",
                  		"building_id": "24",
                  		"key_id": "291",
                  		"bldg_id": "63",
                  		"cp": "",
                  		"wo": "00368628",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "07/07/11",
                  		"building_id": "177",
                  		"key_id": "316",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00368629",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "06/30/11",
                  		"building_id": "9",
                  		"key_id": "376",
                  		"bldg_id": "100",
                  		"cp": "00285932",
                  		"wo": "00324381",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "06/29/11",
                  		"building_id": "67",
                  		"key_id": "373",
                  		"bldg_id": "84",
                  		"cp": "00285786",
                  		"wo": "00366658",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "06/28/11",
                  		"building_id": "93",
                  		"key_id": "128",
                  		"bldg_id": "43",
                  		"cp": "",
                  		"wo": "SR00326025",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Sims",
                  		"date": "06/24/11",
                  		"building_id": "42",
                  		"key_id": "50",
                  		"bldg_id": "120",
                  		"cp": "",
                  		"wo": "00367974",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/16/11",
                  		"building_id": "125",
                  		"key_id": "415",
                  		"bldg_id": "103",
                  		"cp": "00316164",
                  		"wo": "00367350",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/16/11",
                  		"building_id": "125",
                  		"key_id": "416",
                  		"bldg_id": "103",
                  		"cp": "00316166",
                  		"wo": "00357458",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "06/16/11",
                  		"building_id": "125",
                  		"key_id": "451",
                  		"bldg_id": "103",
                  		"cp": "00323205",
                  		"wo": "00366979",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Graduate Science Research Center",
                  		"date": "06/10/11",
                  		"building_id": "162",
                  		"key_id": "452",
                  		"bldg_id": "114",
                  		"cp": "00323898",
                  		"wo": "00366980",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "06/08/11",
                  		"building_id": "59",
                  		"key_id": "422",
                  		"bldg_id": "56",
                  		"cp": "00317311",
                  		"wo": "00365066",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"date": "06/07/11",
                  		"building_id": "149",
                  		"key_id": "296",
                  		"bldg_id": "106",
                  		"cp": "",
                  		"wo": "00366820",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "06/07/11",
                  		"building_id": "177",
                  		"key_id": "354",
                  		"bldg_id": "210",
                  		"cp": "00272087",
                  		"wo": "0366659",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Institute of Archaeology and Anthropology",
                  		"date": "06/06/11",
                  		"building_id": "33",
                  		"key_id": "263",
                  		"bldg_id": "7",
                  		"cp": "",
                  		"wo": "00367473",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "06/06/11",
                  		"building_id": "10",
                  		"key_id": "47",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "SR00324625",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "06/06/11",
                  		"building_id": "92",
                  		"key_id": "456",
                  		"bldg_id": "29",
                  		"cp": "00316981",
                  		"wo": "00362612",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "06/03/11",
                  		"building_id": "92",
                  		"key_id": "268",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "00366200",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "06/03/11",
                  		"building_id": "38",
                  		"key_id": "98",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "00366251",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "06/03/11",
                  		"building_id": "88",
                  		"key_id": "106",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "00366251",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "06/02/11",
                  		"building_id": "10",
                  		"key_id": "36",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "SR00324351",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "06/02/11",
                  		"building_id": "92",
                  		"key_id": "420",
                  		"bldg_id": "29",
                  		"cp": "00316981",
                  		"wo": "00358779",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "06/01/11",
                  		"building_id": "15",
                  		"key_id": "91",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00315256",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "05/31/11",
                  		"building_id": "177",
                  		"key_id": "318",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00364707",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Currell College",
                  		"date": "05/26/11",
                  		"building_id": "29",
                  		"key_id": "435",
                  		"bldg_id": "66",
                  		"cp": "00320727",
                  		"wo": "00364709",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "John Welsh Humanities Center",
                  		"date": "05/23/11",
                  		"building_id": "57",
                  		"key_id": "434",
                  		"bldg_id": "54",
                  		"cp": "00320727",
                  		"wo": "00364709",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "05/23/11",
                  		"building_id": "83",
                  		"key_id": "436",
                  		"bldg_id": "51",
                  		"cp": "00320727",
                  		"wo": "00364709",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "05/19/11",
                  		"building_id": "169",
                  		"key_id": "269",
                  		"bldg_id": "36",
                  		"cp": "",
                  		"wo": "00364952",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "John Welsh Humanities Center",
                  		"date": "05/19/11",
                  		"building_id": "57",
                  		"key_id": "428",
                  		"bldg_id": "54",
                  		"cp": "00318959",
                  		"wo": "00360322",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wardlaw College",
                  		"date": "05/17/11",
                  		"building_id": "65",
                  		"key_id": "289",
                  		"bldg_id": "80",
                  		"cp": "",
                  		"wo": "00364705",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "05/17/11",
                  		"building_id": "16",
                  		"key_id": "119",
                  		"bldg_id": "11",
                  		"cp": "CP00254384",
                  		"wo": "FM00360982",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "05/17/11",
                  		"building_id": "86",
                  		"key_id": "385",
                  		"bldg_id": "90",
                  		"cp": "00292304",
                  		"wo": "00331576",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "05/09/11",
                  		"building_id": "66",
                  		"key_id": "123",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00364122",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "05/06/11",
                  		"building_id": "169",
                  		"key_id": "270",
                  		"bldg_id": "36",
                  		"cp": "",
                  		"wo": "00364215",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/05/11",
                  		"building_id": "171",
                  		"key_id": "405",
                  		"bldg_id": "112",
                  		"cp": "00310496",
                  		"wo": "00350948",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "05/02/11",
                  		"building_id": "171",
                  		"key_id": "297",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00363606",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "04/28/11",
                  		"building_id": "92",
                  		"key_id": "419",
                  		"bldg_id": "29",
                  		"cp": "00316981",
                  		"wo": "00362612",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) NETTLES",
                  		"date": "04/28/11",
                  		"building_id": "304",
                  		"key_id": "689",
                  		"bldg_id": "885A",
                  		"cp": "00310294",
                  		"wo": "00363413",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Nada Apartments",
                  		"date": "04/26/11",
                  		"building_id": "77",
                  		"key_id": "95",
                  		"bldg_id": "48",
                  		"cp": "",
                  		"wo": "00363090",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "04/26/11",
                  		"building_id": "180",
                  		"key_id": "389",
                  		"bldg_id": "171",
                  		"cp": "00293637",
                  		"wo": "00332502",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "04/26/11",
                  		"building_id": "180",
                  		"key_id": "693",
                  		"bldg_id": "171",
                  		"cp": "00293637",
                  		"wo": "00332502",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Onewood Farm - Garnet & Black Barns",
                  		"date": "04/26/11",
                  		"building_id": "209",
                  		"key_id": "705",
                  		"bldg_id": "204D",
                  		"cp": "",
                  		"wo": "00363089",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #4",
                  		"date": "04/25/11",
                  		"building_id": "251",
                  		"key_id": "700",
                  		"bldg_id": "663",
                  		"cp": "",
                  		"wo": "00363091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "04/20/11",
                  		"building_id": "48",
                  		"key_id": "425",
                  		"bldg_id": "14",
                  		"cp": "00317526",
                  		"wo": "00361952",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "04/19/11",
                  		"building_id": "170",
                  		"key_id": "274",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "00361955",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "04/19/11",
                  		"building_id": "170",
                  		"key_id": "61",
                  		"bldg_id": "39",
                  		"cp": "CP00321821",
                  		"wo": "FM00395151",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "04/19/11",
                  		"building_id": "9",
                  		"key_id": "252",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "00331576",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "04/19/11",
                  		"building_id": "170",
                  		"key_id": "253",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "00361955",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "04/13/11",
                  		"building_id": "66",
                  		"key_id": "267",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00361951",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "04/13/11",
                  		"building_id": "35",
                  		"key_id": "352",
                  		"bldg_id": "70",
                  		"cp": "00272087",
                  		"wo": "00361944",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "04/08/11",
                  		"building_id": "9",
                  		"key_id": "353",
                  		"bldg_id": "100",
                  		"cp": "00272087",
                  		"wo": "00360912",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "04/04/11",
                  		"building_id": "16",
                  		"key_id": "120",
                  		"bldg_id": "11",
                  		"cp": "CP00254384",
                  		"wo": "FM00360982",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "03/30/11",
                  		"building_id": "40",
                  		"key_id": "22",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "00355253",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "03/29/11",
                  		"building_id": "167",
                  		"key_id": "348",
                  		"bldg_id": "15",
                  		"cp": "00264430",
                  		"wo": "00300072",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "03/28/11",
                  		"building_id": "172",
                  		"key_id": "304",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00360661",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "03/25/11",
                  		"building_id": "167",
                  		"key_id": "349",
                  		"bldg_id": "15",
                  		"cp": "00264430",
                  		"wo": "00300072",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Beaufort) HARGRAY BUILDING",
                  		"date": "03/24/11",
                  		"building_id": "374",
                  		"key_id": "696",
                  		"bldg_id": "810",
                  		"cp": "00264437",
                  		"wo": "00352857",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Beaufort) HARGRAY BUILDING",
                  		"date": "03/24/11",
                  		"building_id": "374",
                  		"key_id": "697",
                  		"bldg_id": "810",
                  		"cp": "00248714",
                  		"wo": "00352856",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "03/23/11",
                  		"building_id": "26",
                  		"key_id": "278",
                  		"bldg_id": "65",
                  		"cp": "",
                  		"wo": "00355754",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Colonial Center",
                  		"date": "03/23/11",
                  		"building_id": "124",
                  		"key_id": "355",
                  		"bldg_id": "158",
                  		"cp": "00272704",
                  		"wo": "00358443",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "03/22/11",
                  		"building_id": "35",
                  		"key_id": "285",
                  		"bldg_id": "70",
                  		"cp": "",
                  		"wo": "00360166",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"date": "03/22/11",
                  		"building_id": "252",
                  		"key_id": "691",
                  		"bldg_id": "664",
                  		"cp": "00309607",
                  		"wo": "00360169",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "03/18/11",
                  		"building_id": "55",
                  		"key_id": "5",
                  		"bldg_id": "159",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "03/18/11",
                  		"building_id": "14",
                  		"key_id": "71",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "SR00318539",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "03/18/11",
                  		"building_id": "45",
                  		"key_id": "379",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00329833",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "03/11/11",
                  		"building_id": "6",
                  		"key_id": "418",
                  		"bldg_id": "33",
                  		"cp": "00316868",
                  		"wo": "00359070",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "03/09/11",
                  		"building_id": "38",
                  		"key_id": "298",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "00357970",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "03/09/11",
                  		"building_id": "23",
                  		"key_id": "314",
                  		"bldg_id": "173",
                  		"cp": "",
                  		"wo": "00367686",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "03/09/11",
                  		"building_id": "56",
                  		"key_id": "345",
                  		"bldg_id": "16",
                  		"cp": "00255488",
                  		"wo": "00358607",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "03/09/11",
                  		"building_id": "125",
                  		"key_id": "417",
                  		"bldg_id": "103",
                  		"cp": "00316168",
                  		"wo": "00358943",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1501 Senate Street",
                  		"date": "03/08/11",
                  		"building_id": "96",
                  		"key_id": "321",
                  		"bldg_id": "28",
                  		"cp": "",
                  		"wo": "00357582",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "02/28/11",
                  		"building_id": "8",
                  		"key_id": "258",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "00357927",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "02/28/11",
                  		"building_id": "86",
                  		"key_id": "391",
                  		"bldg_id": "90",
                  		"cp": "00294480",
                  		"wo": "00352864",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "02/24/11",
                  		"building_id": "49",
                  		"key_id": "398",
                  		"bldg_id": "140",
                  		"cp": "00306387",
                  		"wo": "00354321",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "02/22/11",
                  		"building_id": "35",
                  		"key_id": "80",
                  		"bldg_id": "70",
                  		"cp": "",
                  		"wo": "00345390",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "02/22/11",
                  		"building_id": "93",
                  		"key_id": "127",
                  		"bldg_id": "43",
                  		"cp": "CP00311053",
                  		"wo": "FM00351656",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "02/16/11",
                  		"building_id": "49",
                  		"key_id": "306",
                  		"bldg_id": "140",
                  		"cp": "",
                  		"wo": "00354054",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "02/15/11",
                  		"building_id": "26",
                  		"key_id": "426",
                  		"bldg_id": "65",
                  		"cp": "00317771",
                  		"wo": "0035574",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "02/14/11",
                  		"building_id": "43",
                  		"key_id": "29",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "00356991",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "02/14/11",
                  		"building_id": "14",
                  		"key_id": "311",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "00356989",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "02/10/11",
                  		"building_id": "15",
                  		"key_id": "90",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00315256",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/09/11",
                  		"building_id": "125",
                  		"key_id": "294",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00356393",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/09/11",
                  		"building_id": "125",
                  		"key_id": "295",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00356392",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"date": "02/08/11",
                  		"building_id": "118",
                  		"key_id": "375",
                  		"bldg_id": "89",
                  		"cp": "00285931",
                  		"wo": "00346815",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "02/07/11",
                  		"building_id": "170",
                  		"key_id": "271",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "00356992",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "02/07/11",
                  		"building_id": "40",
                  		"key_id": "538",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "02/07/11",
                  		"building_id": "40",
                  		"key_id": "301",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "02/07/11",
                  		"building_id": "14",
                  		"key_id": "312",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "00356297",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "East Quadrangle",
                  		"date": "02/07/11",
                  		"building_id": "153",
                  		"key_id": "124",
                  		"bldg_id": "135a",
                  		"cp": "",
                  		"wo": "SR00315210",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "02/04/11",
                  		"building_id": "30",
                  		"key_id": "281",
                  		"bldg_id": "67",
                  		"cp": "",
                  		"wo": "00355756",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "02/04/11",
                  		"building_id": "9",
                  		"key_id": "343",
                  		"bldg_id": "100",
                  		"cp": "00253116",
                  		"wo": "000352563",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Davis College",
                  		"date": "02/02/11",
                  		"building_id": "22",
                  		"key_id": "344",
                  		"bldg_id": "62",
                  		"cp": "00254385",
                  		"wo": "00355252",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "02/02/11",
                  		"building_id": "61",
                  		"key_id": "112",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "356294",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "01/28/11",
                  		"building_id": "15",
                  		"key_id": "276",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "00355561",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "01/28/11",
                  		"building_id": "109",
                  		"key_id": "75",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "00355556",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "01/28/11",
                  		"building_id": "92",
                  		"key_id": "340",
                  		"bldg_id": "29",
                  		"cp": "CP00247676",
                  		"wo": "00355089",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "01/27/11",
                  		"building_id": "180",
                  		"key_id": "388",
                  		"bldg_id": "171",
                  		"cp": "00293637",
                  		"wo": "00354575",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "01/26/11",
                  		"building_id": "66",
                  		"key_id": "374",
                  		"bldg_id": "8",
                  		"cp": "00285788",
                  		"wo": "00354620",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Georgetown) HOBCAW MARINE LABORATORY",
                  		"date": "01/25/11",
                  		"building_id": "360",
                  		"key_id": "690",
                  		"bldg_id": "222",
                  		"cp": "00350654",
                  		"wo": "00363413",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "01/19/11",
                  		"building_id": "177",
                  		"key_id": "351",
                  		"bldg_id": "210",
                  		"cp": "00266985",
                  		"wo": "00349440",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "01/19/11",
                  		"building_id": "61",
                  		"key_id": "111",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "00354475",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina Baseball Stadium",
                  		"date": "01/18/11",
                  		"building_id": "193",
                  		"key_id": "692",
                  		"bldg_id": "235",
                  		"cp": "00301154",
                  		"wo": "00354471",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "01/07/11",
                  		"building_id": "66",
                  		"key_id": "265",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00367473",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "01/04/11",
                  		"building_id": "9",
                  		"key_id": "251",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "00352859",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "12/16/10",
                  		"building_id": "9",
                  		"key_id": "250",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "00352563",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"date": "12/15/10",
                  		"building_id": "118",
                  		"key_id": "404",
                  		"bldg_id": "89",
                  		"cp": "00308102",
                  		"wo": "00352562",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "12/14/10",
                  		"building_id": "66",
                  		"key_id": "121",
                  		"bldg_id": "8",
                  		"cp": "",
                  		"wo": "00352698",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #2",
                  		"date": "12/13/10",
                  		"building_id": "250",
                  		"key_id": "661",
                  		"bldg_id": "662",
                  		"cp": "",
                  		"wo": "FM00352050",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #2",
                  		"date": "12/13/10",
                  		"building_id": "250",
                  		"key_id": "662",
                  		"bldg_id": "662",
                  		"cp": "",
                  		"wo": "FM00352051",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #3",
                  		"date": "12/13/10",
                  		"building_id": "256",
                  		"key_id": "663",
                  		"bldg_id": "668",
                  		"cp": "",
                  		"wo": "FM00352052",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(USC SOM) MED SCHOOL V.A. #28",
                  		"date": "12/13/10",
                  		"building_id": "252",
                  		"key_id": "711",
                  		"bldg_id": "664",
                  		"cp": "",
                  		"wo": "00352265",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "12/10/10",
                  		"building_id": "125",
                  		"key_id": "228",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00352364",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "12/10/10",
                  		"building_id": "125",
                  		"key_id": "236",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00352269",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Salkehatchie) WALTERBORO MAIN BUILDING",
                  		"date": "12/09/10",
                  		"building_id": "339",
                  		"key_id": "709",
                  		"bldg_id": "858",
                  		"cp": "00298810",
                  		"wo": "00346595",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "12/08/10",
                  		"building_id": "68",
                  		"key_id": "240",
                  		"bldg_id": "85",
                  		"cp": "",
                  		"wo": "00335938",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Salkehatchie) CENTRAL CLASSROOM BUILDING",
                  		"date": "12/06/10",
                  		"building_id": "317",
                  		"key_id": "708",
                  		"bldg_id": "841",
                  		"cp": "00298810",
                  		"wo": "00346595",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "12/01/10",
                  		"building_id": "35",
                  		"key_id": "227",
                  		"bldg_id": "70",
                  		"cp": "",
                  		"wo": "00345390",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "11/30/10",
                  		"building_id": "14",
                  		"key_id": "230",
                  		"bldg_id": "162",
                  		"cp": "",
                  		"wo": "00351018",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Field House",
                  		"date": "11/30/10",
                  		"building_id": "34",
                  		"key_id": "239",
                  		"bldg_id": "186",
                  		"cp": "",
                  		"wo": "00350672",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle C",
                  		"date": "11/23/10",
                  		"building_id": "184",
                  		"key_id": "229",
                  		"bldg_id": "146c",
                  		"cp": "",
                  		"wo": "00351012",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"date": "11/22/10",
                  		"building_id": "118",
                  		"key_id": "237",
                  		"bldg_id": "89",
                  		"cp": "",
                  		"wo": "00350361",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "11/22/10",
                  		"building_id": "18",
                  		"key_id": "238",
                  		"bldg_id": "61",
                  		"cp": "",
                  		"wo": "00350230",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "11/19/10",
                  		"building_id": "28",
                  		"key_id": "225",
                  		"bldg_id": "18",
                  		"cp": "",
                  		"wo": "00350629",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "11/11/10",
                  		"building_id": "8",
                  		"key_id": "319",
                  		"bldg_id": "1",
                  		"cp": "",
                  		"wo": "00354796",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "11/10/10",
                  		"building_id": "92",
                  		"key_id": "226",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "00349875",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "11/05/10",
                  		"building_id": "40",
                  		"key_id": "19",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "00348469",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "11/03/10",
                  		"building_id": "177",
                  		"key_id": "247",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00349097",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "11/02/10",
                  		"building_id": "15",
                  		"key_id": "88",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "SR00308400",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "11/02/10",
                  		"building_id": "172",
                  		"key_id": "224",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00348839",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "201 S. Marion Street",
                  		"date": "11/02/10",
                  		"building_id": "110",
                  		"key_id": "232",
                  		"bldg_id": "203",
                  		"cp": "",
                  		"wo": "00349442",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle D",
                  		"date": "11/01/10",
                  		"building_id": "185",
                  		"key_id": "15",
                  		"bldg_id": "146d",
                  		"cp": "",
                  		"wo": "00348846",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "10/28/10",
                  		"building_id": "10",
                  		"key_id": "38",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "10/28/10",
                  		"building_id": "170",
                  		"key_id": "55",
                  		"bldg_id": "39",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Chris Geary"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "10/28/10",
                  		"building_id": "71",
                  		"key_id": "97",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "10/26/10",
                  		"building_id": "45",
                  		"key_id": "380",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00346813",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "10/26/10",
                  		"building_id": "45",
                  		"key_id": "382",
                  		"bldg_id": "134",
                  		"cp": "00291153",
                  		"wo": "00346813",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) GENERATOR BUILDINGCORRIDOR",
                  		"date": "10/26/10",
                  		"building_id": "259",
                  		"key_id": "707",
                  		"bldg_id": "669",
                  		"cp": "00301973",
                  		"wo": "00341748",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(USC SOM) MEDICAL V.A. #101",
                  		"date": "10/26/10",
                  		"building_id": "253",
                  		"key_id": "713",
                  		"bldg_id": "665",
                  		"cp": "00291154",
                  		"wo": "00329836",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "10/26/10",
                  		"building_id": "18",
                  		"key_id": "241",
                  		"bldg_id": "61",
                  		"cp": "",
                  		"wo": "00348466",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "10/22/10",
                  		"building_id": "109",
                  		"key_id": "235",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "00348472",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "10/20/10",
                  		"building_id": "86",
                  		"key_id": "392",
                  		"bldg_id": "90",
                  		"cp": "00294480",
                  		"wo": "0328666",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "10/20/10",
                  		"building_id": "172",
                  		"key_id": "246",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00346872",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "10/19/10",
                  		"building_id": "68",
                  		"key_id": "411",
                  		"bldg_id": "85",
                  		"cp": "00315973",
                  		"wo": "00347663",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"date": "10/19/10",
                  		"building_id": "301",
                  		"key_id": "710",
                  		"bldg_id": "883",
                  		"cp": "00297512",
                  		"wo": "00347830",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "10/18/10",
                  		"building_id": "83",
                  		"key_id": "403",
                  		"bldg_id": "51",
                  		"cp": "00307225",
                  		"wo": "00347663",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Fort Jackson Storage Facility",
                  		"date": "10/18/10",
                  		"building_id": "188",
                  		"key_id": "664",
                  		"bldg_id": "705A",
                  		"cp": "",
                  		"wo": "FM00311696",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Fort Jackson Storage Facility",
                  		"date": "10/18/10",
                  		"building_id": "188",
                  		"key_id": "712",
                  		"bldg_id": "705A",
                  		"cp": "00274400",
                  		"wo": "00311696",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumter Street Parking Garage",
                  		"date": "10/14/10",
                  		"building_id": "174",
                  		"key_id": "242",
                  		"bldg_id": "141",
                  		"cp": "",
                  		"wo": "00346873",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Energy Facility East",
                  		"date": "10/12/10",
                  		"building_id": "84",
                  		"key_id": "244",
                  		"bldg_id": "52",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "10/12/10",
                  		"building_id": "49",
                  		"key_id": "245",
                  		"bldg_id": "140",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "10/08/10",
                  		"building_id": "86",
                  		"key_id": "387",
                  		"bldg_id": "90",
                  		"cp": "00292304",
                  		"wo": "00346871",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "10/07/10",
                  		"building_id": "83",
                  		"key_id": "320",
                  		"bldg_id": "51",
                  		"cp": "",
                  		"wo": "00347174",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Beaufort) SANDSTONE BUILDING",
                  		"date": "09/27/10",
                  		"building_id": "366",
                  		"key_id": "706",
                  		"bldg_id": "802",
                  		"cp": "00307903",
                  		"wo": "00348033",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "09/22/10",
                  		"building_id": "171",
                  		"key_id": "459",
                  		"bldg_id": "112",
                  		"cp": "00335651",
                  		"wo": "00345862",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House Cafeteria",
                  		"date": "09/21/10",
                  		"building_id": "13",
                  		"key_id": "31",
                  		"bldg_id": "161",
                  		"cp": "CP00247670",
                  		"wo": "SR00305703",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "09/08/10",
                  		"building_id": "92",
                  		"key_id": "243",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "00343969",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "08/03/10",
                  		"building_id": "30",
                  		"key_id": "282",
                  		"bldg_id": "67",
                  		"cp": "",
                  		"wo": "00341051",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "07/15/10",
                  		"building_id": "83",
                  		"key_id": "592",
                  		"bldg_id": "51",
                  		"cp": "",
                  		"wo": "FM00389639",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "07/06/10",
                  		"building_id": "167",
                  		"key_id": "350",
                  		"bldg_id": "15",
                  		"cp": "00264430",
                  		"wo": "00300072",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "06/22/10",
                  		"building_id": "88",
                  		"key_id": "568",
                  		"bldg_id": "9",
                  		"cp": "00285789",
                  		"wo": "00383289",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "06/17/10",
                  		"building_id": "71",
                  		"key_id": "618",
                  		"bldg_id": "40",
                  		"cp": "",
                  		"wo": "00336817",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "06/16/10",
                  		"building_id": "170",
                  		"key_id": "590",
                  		"bldg_id": "39",
                  		"cp": "00246261",
                  		"wo": "00336581",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "President's House",
                  		"date": "06/03/10",
                  		"building_id": "117",
                  		"key_id": "84",
                  		"bldg_id": "69",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "04/06/10",
                  		"building_id": "180",
                  		"key_id": "206",
                  		"bldg_id": "171",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Lancaster) MAINTENANCE BUILDING",
                  		"date": "03/19/10",
                  		"building_id": "347",
                  		"key_id": "726",
                  		"bldg_id": "824",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "03/11/10",
                  		"building_id": "180",
                  		"key_id": "390",
                  		"bldg_id": "171",
                  		"cp": "00293637",
                  		"wo": "00328907",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Lancaster) GREGORY HEALTH & WELLNESS",
                  		"date": "03/10/10",
                  		"building_id": "346",
                  		"key_id": "724",
                  		"bldg_id": "823",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "(Lancaster) CAROL RAY DOWLING BUILDING",
                  		"date": "03/10/10",
                  		"building_id": "349",
                  		"key_id": "725",
                  		"bldg_id": "826",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "(Lancaster) MEDFORD",
                  		"date": "03/09/10",
                  		"building_id": "345",
                  		"key_id": "722",
                  		"bldg_id": "822",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Lancaster) HUBBARD",
                  		"date": "03/09/10",
                  		"building_id": "343",
                  		"key_id": "723",
                  		"bldg_id": "820",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Union) TRULUCK ACTIVITIES",
                  		"date": "03/09/10",
                  		"building_id": "309",
                  		"key_id": "727",
                  		"bldg_id": "861",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Psychology Annex (819 Barnwell Street)",
                  		"date": "03/08/10",
                  		"building_id": "115",
                  		"key_id": "621",
                  		"bldg_id": "34",
                  		"cp": "",
                  		"wo": "00329217",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "03/08/10",
                  		"building_id": "28",
                  		"key_id": "147",
                  		"bldg_id": "18",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "03/08/10",
                  		"building_id": "177",
                  		"key_id": "191",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00329220",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Athletics Office Annex",
                  		"date": "03/08/10",
                  		"building_id": "165",
                  		"key_id": "193",
                  		"bldg_id": "206",
                  		"cp": "",
                  		"wo": "00312195",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Lancaster) STARR",
                  		"date": "03/08/10",
                  		"building_id": "344",
                  		"key_id": "720",
                  		"bldg_id": "821",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Lancaster) JAMES BRADLEY ARTS & SCIENCE CTR",
                  		"date": "03/08/10",
                  		"building_id": "348",
                  		"key_id": "721",
                  		"bldg_id": "825",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Dexter Murphy"
                  	},

                  	{
                  		"name": "Neutron  Generator",
                  		"date": "03/05/10",
                  		"building_id": "87",
                  		"key_id": "171",
                  		"bldg_id": "91",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "03/05/10",
                  		"building_id": "90",
                  		"key_id": "192",
                  		"bldg_id": "84a",
                  		"cp": "",
                  		"wo": "00312026",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "(Union) MAINTENANCE SHOP",
                  		"date": "03/05/10",
                  		"building_id": "314",
                  		"key_id": "728",
                  		"bldg_id": "867",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Union) CENTRAL BUILDING",
                  		"date": "03/05/10",
                  		"building_id": "312",
                  		"key_id": "729",
                  		"bldg_id": "864",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "(Union) FOUNDERS HOUSE",
                  		"date": "03/05/10",
                  		"building_id": "311",
                  		"key_id": "739",
                  		"bldg_id": "863",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "USC Child Development and Research Center",
                  		"date": "03/03/10",
                  		"building_id": "160",
                  		"key_id": "155",
                  		"bldg_id": "133",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Lieber College",
                  		"date": "02/19/10",
                  		"building_id": "62",
                  		"key_id": "174",
                  		"bldg_id": "74",
                  		"cp": "",
                  		"wo": "00327788",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1527 Senate Street",
                  		"date": "02/19/10",
                  		"building_id": "158",
                  		"key_id": "183",
                  		"bldg_id": "28a",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1527 Senate Street",
                  		"date": "02/18/10",
                  		"building_id": "158",
                  		"key_id": "182",
                  		"bldg_id": "28a",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Graduate Science Research Center",
                  		"date": "02/17/10",
                  		"building_id": "162",
                  		"key_id": "534",
                  		"bldg_id": "114",
                  		"cp": "",
                  		"wo": "FM00390497",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Graduate Science Research Center",
                  		"date": "02/17/10",
                  		"building_id": "162",
                  		"key_id": "166",
                  		"bldg_id": "114",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Public Health Research Center",
                  		"date": "02/17/10",
                  		"building_id": "179",
                  		"key_id": "170",
                  		"bldg_id": "156a",
                  		"cp": "",
                  		"wo": "00312174",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Horizon Building",
                  		"date": "02/11/10",
                  		"building_id": "190",
                  		"key_id": "678",
                  		"bldg_id": "236",
                  		"cp": "00332657",
                  		"wo": "00376013",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Nada Apartments",
                  		"date": "02/08/10",
                  		"building_id": "77",
                  		"key_id": "89",
                  		"bldg_id": "48",
                  		"cp": "",
                  		"wo": "00325172",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "01/21/10",
                  		"building_id": "177",
                  		"key_id": "194",
                  		"bldg_id": "210",
                  		"cp": "",
                  		"wo": "00321770",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Bates House Cafeteria",
                  		"date": "01/13/10",
                  		"building_id": "13",
                  		"key_id": "21",
                  		"bldg_id": "161",
                  		"cp": "CP00274761",
                  		"wo": "FM00312123",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "12/14/09",
                  		"building_id": "40",
                  		"key_id": "18",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "12/14/09",
                  		"building_id": "40",
                  		"key_id": "24",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "384119",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "12/14/09",
                  		"building_id": "40",
                  		"key_id": "302",
                  		"bldg_id": "119",
                  		"cp": "",
                  		"wo": "00372103",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Nada Apartments",
                  		"date": "12/14/09",
                  		"building_id": "77",
                  		"key_id": "87",
                  		"bldg_id": "48",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "12/14/09",
                  		"building_id": "38",
                  		"key_id": "96",
                  		"bldg_id": "118",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Greek Housing - F",
                  		"date": "12/11/09",
                  		"building_id": "140",
                  		"key_id": "130",
                  		"bldg_id": "148f",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing",
                  		"date": "12/11/09",
                  		"building_id": "144",
                  		"key_id": "137",
                  		"bldg_id": "148",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - N",
                  		"date": "12/10/09",
                  		"building_id": "135",
                  		"key_id": "133",
                  		"bldg_id": "148n",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing",
                  		"date": "12/10/09",
                  		"building_id": "144",
                  		"key_id": "134",
                  		"bldg_id": "148",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - O",
                  		"date": "12/10/09",
                  		"building_id": "142",
                  		"key_id": "136",
                  		"bldg_id": "148o",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing",
                  		"date": "12/10/09",
                  		"building_id": "144",
                  		"key_id": "139",
                  		"bldg_id": "148",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - B",
                  		"date": "12/09/09",
                  		"building_id": "134",
                  		"key_id": "129",
                  		"bldg_id": "148b",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - I",
                  		"date": "12/09/09",
                  		"building_id": "133",
                  		"key_id": "138",
                  		"bldg_id": "148i",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - G",
                  		"date": "12/09/09",
                  		"building_id": "132",
                  		"key_id": "140",
                  		"bldg_id": "148g",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing",
                  		"date": "12/09/09",
                  		"building_id": "144",
                  		"key_id": "143",
                  		"bldg_id": "148",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - K",
                  		"date": "12/07/09",
                  		"building_id": "138",
                  		"key_id": "135",
                  		"bldg_id": "148k",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Greek Housing - A",
                  		"date": "12/07/09",
                  		"building_id": "139",
                  		"key_id": "141",
                  		"bldg_id": "148a",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "11/02/09",
                  		"building_id": "172",
                  		"key_id": "148",
                  		"bldg_id": "138",
                  		"cp": "",
                  		"wo": "00319713",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "School of Music",
                  		"date": "11/02/09",
                  		"building_id": "89",
                  		"key_id": "169",
                  		"bldg_id": "86a",
                  		"cp": "",
                  		"wo": "00319723",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Facilities Management Center",
                  		"date": "11/02/09",
                  		"building_id": "99",
                  		"key_id": "207",
                  		"bldg_id": "83",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Energy Facility East",
                  		"date": "10/29/09",
                  		"building_id": "84",
                  		"key_id": "205",
                  		"bldg_id": "52",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Wardlaw College",
                  		"date": "10/21/09",
                  		"building_id": "65",
                  		"key_id": "157",
                  		"bldg_id": "80",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "10/21/09",
                  		"building_id": "9",
                  		"key_id": "159",
                  		"bldg_id": "100",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Drayton Hall",
                  		"date": "10/21/09",
                  		"building_id": "64",
                  		"key_id": "162",
                  		"bldg_id": "78",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"date": "10/21/09",
                  		"building_id": "118",
                  		"key_id": "164",
                  		"bldg_id": "89",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Koger Center for the Arts",
                  		"date": "10/21/09",
                  		"building_id": "163",
                  		"key_id": "197",
                  		"bldg_id": "86",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Inventory Central Supply",
                  		"date": "10/21/09",
                  		"building_id": "98",
                  		"key_id": "201",
                  		"bldg_id": "81",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "10/20/09",
                  		"building_id": "85",
                  		"key_id": "168",
                  		"bldg_id": "88",
                  		"cp": "",
                  		"wo": "00318858",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "10/19/09",
                  		"building_id": "23",
                  		"key_id": "167",
                  		"bldg_id": "173",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "10/14/09",
                  		"building_id": "67",
                  		"key_id": "160",
                  		"bldg_id": "84",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Currell Annex",
                  		"date": "10/13/09",
                  		"building_id": "31",
                  		"key_id": "161",
                  		"bldg_id": "68",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "10/12/09",
                  		"building_id": "15",
                  		"key_id": "86",
                  		"bldg_id": "165",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "10/09/09",
                  		"building_id": "59",
                  		"key_id": "156",
                  		"bldg_id": "56",
                  		"cp": "",
                  		"wo": "00317808",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1728 College Street",
                  		"date": "10/01/09",
                  		"building_id": "80",
                  		"key_id": "150",
                  		"bldg_id": "58",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "10/01/09",
                  		"building_id": "18",
                  		"key_id": "186",
                  		"bldg_id": "61",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "09/30/09",
                  		"building_id": "26",
                  		"key_id": "10",
                  		"bldg_id": "65",
                  		"cp": "",
                  		"wo": "00316409",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Callcott Social Science Center",
                  		"date": "09/30/09",
                  		"building_id": "154",
                  		"key_id": "152",
                  		"bldg_id": "115",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street Annex",
                  		"date": "09/29/09",
                  		"building_id": "159",
                  		"key_id": "180",
                  		"bldg_id": "29a",
                  		"cp": "",
                  		"wo": "00316808",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle D",
                  		"date": "09/21/09",
                  		"building_id": "185",
                  		"key_id": "14",
                  		"bldg_id": "146d",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1714 College Street",
                  		"date": "09/21/09",
                  		"building_id": "75",
                  		"key_id": "151",
                  		"bldg_id": "57",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1710 College Street",
                  		"date": "09/21/09",
                  		"building_id": "78",
                  		"key_id": "181",
                  		"bldg_id": "47",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Student Financial Aid",
                  		"date": "09/19/09",
                  		"building_id": "76",
                  		"key_id": "196",
                  		"bldg_id": "46",
                  		"cp": "",
                  		"wo": "00315844",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle C",
                  		"date": "09/18/09",
                  		"building_id": "184",
                  		"key_id": "13",
                  		"bldg_id": "146c",
                  		"cp": "",
                  		"wo": "00315887",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "09/18/09",
                  		"building_id": "93",
                  		"key_id": "126",
                  		"bldg_id": "43",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "09/16/09",
                  		"building_id": "48",
                  		"key_id": "185",
                  		"bldg_id": "14",
                  		"cp": "",
                  		"wo": "00315535",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1731 College Street",
                  		"date": "09/15/09",
                  		"building_id": "121",
                  		"key_id": "149",
                  		"bldg_id": "38",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "09/15/09",
                  		"building_id": "169",
                  		"key_id": "158",
                  		"bldg_id": "36",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "09/14/09",
                  		"building_id": "92",
                  		"key_id": "179",
                  		"bldg_id": "29",
                  		"cp": "",
                  		"wo": "00314358",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "109 S. Bull Street",
                  		"date": "09/11/09",
                  		"building_id": "113",
                  		"key_id": "113",
                  		"bldg_id": "202b",
                  		"cp": "",
                  		"wo": "00314366",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "09/09/09",
                  		"building_id": "167",
                  		"key_id": "172",
                  		"bldg_id": "15",
                  		"cp": "",
                  		"wo": "00314350",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "McCutchen House",
                  		"date": "09/09/09",
                  		"building_id": "166",
                  		"key_id": "195",
                  		"bldg_id": "10",
                  		"cp": "",
                  		"wo": "00314350",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "World War Memorial",
                  		"date": "09/02/09",
                  		"building_id": "82",
                  		"key_id": "190",
                  		"bldg_id": "5",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Spigner House",
                  		"date": "08/31/09",
                  		"building_id": "74",
                  		"key_id": "187",
                  		"bldg_id": "42",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Grounds Maintenance Shop",
                  		"date": "08/31/09",
                  		"building_id": "126",
                  		"key_id": "203",
                  		"bldg_id": "82b",
                  		"cp": "",
                  		"wo": "00312028",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Longstreet Theatre",
                  		"date": "08/28/09",
                  		"building_id": "12",
                  		"key_id": "173",
                  		"bldg_id": "102",
                  		"cp": "",
                  		"wo": "00313827",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1501 Senate Street",
                  		"date": "08/27/09",
                  		"building_id": "96",
                  		"key_id": "198",
                  		"bldg_id": "28",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "720 College Street/Pearle Labs",
                  		"date": "08/25/09",
                  		"building_id": "100",
                  		"key_id": "199",
                  		"bldg_id": "83b",
                  		"cp": "",
                  		"wo": "00312027",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Motor Pool",
                  		"date": "08/25/09",
                  		"building_id": "97",
                  		"key_id": "200",
                  		"bldg_id": "82a",
                  		"cp": "",
                  		"wo": "00312029",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "513 Pickens Street",
                  		"date": "08/24/09",
                  		"building_id": "181",
                  		"key_id": "176",
                  		"bldg_id": "129",
                  		"cp": "",
                  		"wo": "00312091",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"date": "08/21/09",
                  		"building_id": "173",
                  		"key_id": "189",
                  		"bldg_id": "139",
                  		"cp": "",
                  		"wo": "00312192",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost - B",
                  		"date": "08/20/09",
                  		"building_id": "106",
                  		"key_id": "64",
                  		"bldg_id": "196",
                  		"cp": "",
                  		"wo": "00312153",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "718 Devine",
                  		"date": "08/20/09",
                  		"building_id": "103",
                  		"key_id": "178",
                  		"bldg_id": "226",
                  		"cp": "",
                  		"wo": "00312193",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Devine Street Research Center",
                  		"date": "08/20/09",
                  		"building_id": "116",
                  		"key_id": "208",
                  		"bldg_id": "228",
                  		"cp": "",
                  		"wo": "00312192",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Roost - E",
                  		"date": "08/19/09",
                  		"building_id": "108",
                  		"key_id": "73",
                  		"bldg_id": "199",
                  		"cp": "",
                  		"wo": "00312152",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Roost - A",
                  		"date": "08/18/09",
                  		"building_id": "105",
                  		"key_id": "60",
                  		"bldg_id": "195",
                  		"cp": "",
                  		"wo": "00305129",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "LeConte College",
                  		"date": "08/14/09",
                  		"building_id": "161",
                  		"key_id": "470",
                  		"bldg_id": "60",
                  		"cp": "",
                  		"wo": "FM00397292",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"date": "08/13/09",
                  		"building_id": "182",
                  		"key_id": "11",
                  		"bldg_id": "146a",
                  		"cp": "",
                  		"wo": "00312080",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle B",
                  		"date": "08/13/09",
                  		"building_id": "183",
                  		"key_id": "12",
                  		"bldg_id": "146b",
                  		"cp": "",
                  		"wo": "00312079",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "08/12/09",
                  		"building_id": "10",
                  		"key_id": "34",
                  		"bldg_id": "160",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Quadrangle",
                  		"date": "08/12/09",
                  		"building_id": "152",
                  		"key_id": "43",
                  		"bldg_id": "135",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Flinn Hall",
                  		"date": "08/07/09",
                  		"building_id": "81",
                  		"key_id": "125",
                  		"bldg_id": "6",
                  		"cp": "",
                  		"wo": "",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "07/31/09",
                  		"building_id": "43",
                  		"key_id": "28",
                  		"bldg_id": "122",
                  		"cp": "",
                  		"wo": "00311313",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "07/31/09",
                  		"building_id": "61",
                  		"key_id": "110",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "TBD",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sims",
                  		"date": "07/30/09",
                  		"building_id": "42",
                  		"key_id": "46",
                  		"bldg_id": "120",
                  		"cp": "",
                  		"wo": "00311314",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "07/29/09",
                  		"building_id": "88",
                  		"key_id": "105",
                  		"bldg_id": "9",
                  		"cp": "",
                  		"wo": "002311315",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "07/13/09",
                  		"building_id": "48",
                  		"key_id": "184",
                  		"bldg_id": "14",
                  		"cp": "",
                  		"wo": "00309911",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Facilities Management Center",
                  		"date": "07/01/09",
                  		"building_id": "99",
                  		"key_id": "204",
                  		"bldg_id": "83",
                  		"cp": "",
                  		"wo": "00309180",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "06/26/09",
                  		"building_id": "30",
                  		"key_id": "57",
                  		"bldg_id": "67",
                  		"cp": "",
                  		"wo": "00300637",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "06/04/09",
                  		"building_id": "109",
                  		"key_id": "58",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "00307211",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Health Sciences Building",
                  		"date": "05/21/09",
                  		"building_id": "164",
                  		"key_id": "177",
                  		"bldg_id": "76",
                  		"cp": "",
                  		"wo": "00300975",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Sims",
                  		"date": "04/28/09",
                  		"building_id": "42",
                  		"key_id": "45",
                  		"bldg_id": "120",
                  		"cp": "",
                  		"wo": "00304315",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "04/15/09",
                  		"building_id": "125",
                  		"key_id": "165",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00264630",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "03/10/09",
                  		"building_id": "30",
                  		"key_id": "54",
                  		"bldg_id": "67",
                  		"cp": "",
                  		"wo": "00288548",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"date": "02/25/09",
                  		"building_id": "173",
                  		"key_id": "305",
                  		"bldg_id": "139",
                  		"cp": "",
                  		"wo": "00300161",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/23/09",
                  		"building_id": "125",
                  		"key_id": "163",
                  		"bldg_id": "103",
                  		"cp": "",
                  		"wo": "00299298",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "707 Catawba Street - Film Library",
                  		"date": "02/22/09",
                  		"building_id": "189",
                  		"key_id": "704",
                  		"bldg_id": "619",
                  		"cp": "",
                  		"wo": "00272368",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "513 Pickens Street",
                  		"date": "02/17/09",
                  		"building_id": "181",
                  		"key_id": "175",
                  		"bldg_id": "129",
                  		"cp": "",
                  		"wo": "00293119",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "12/02/08",
                  		"building_id": "59",
                  		"key_id": "153",
                  		"bldg_id": "56",
                  		"cp": "",
                  		"wo": "00283462",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "11/12/08",
                  		"building_id": "35",
                  		"key_id": "81",
                  		"bldg_id": "70",
                  		"cp": "",
                  		"wo": "00289035",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "11/10/08",
                  		"building_id": "109",
                  		"key_id": "76",
                  		"bldg_id": "207",
                  		"cp": "",
                  		"wo": "00293074",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "07/07/08",
                  		"building_id": "61",
                  		"key_id": "109",
                  		"bldg_id": "72",
                  		"cp": "",
                  		"wo": "00276412",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "05/16/08",
                  		"building_id": "26",
                  		"key_id": "9",
                  		"bldg_id": "65",
                  		"cp": "",
                  		"wo": "00278767",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "South Quadrangle",
                  		"date": "08/28/07",
                  		"building_id": "152",
                  		"key_id": "56",
                  		"bldg_id": "135",
                  		"cp": "",
                  		"wo": "0025842",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "04/03/07",
                  		"building_id": "30",
                  		"key_id": "51",
                  		"bldg_id": "67",
                  		"cp": "",
                  		"wo": "00245937",
                  		"inspector": "Ed Pitts"
                  	},

                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"date": "10/17/06",
                  		"building_id": "173",
                  		"key_id": "188",
                  		"bldg_id": "139",
                  		"cp": "",
                  		"wo": "00233991",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "10/02/06",
                  		"building_id": "68",
                  		"key_id": "146",
                  		"bldg_id": "85",
                  		"cp": "",
                  		"wo": "00232441",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "09/15/06",
                  		"building_id": "171",
                  		"key_id": "144",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00231591",
                  		"inspector": "Darryl Washington"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "09/15/06",
                  		"building_id": "171",
                  		"key_id": "145",
                  		"bldg_id": "112",
                  		"cp": "",
                  		"wo": "00231596",
                  		"inspector": "Darryl Washington"
                  	},

                     ];

                  var reportsArray = [
                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "10/30/12",
                  		"key_id": "1"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "04/30/13",
                  		"key_id": "2"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/30/12",
                  		"key_id": "3"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/30/12",
                  		"key_id": "4"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/30/12",
                  		"key_id": "5"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "10/30/12",
                  		"key_id": "6"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "10/30/12",
                  		"key_id": "7"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/31/12",
                  		"key_id": "8"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/31/12",
                  		"key_id": "9"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "10/31/12",
                  		"key_id": "10"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "11/12/12",
                  		"key_id": "11"
                  	},

                  	{
                  		"name": "Wade Hampton",
                  		"date": "04/30/13",
                  		"key_id": "12"
                  	},

                  	{
                  		"name": "Bates House Cafeteria",
                  		"date": "11/12/12",
                  		"key_id": "13"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "11/12/12",
                  		"key_id": "14"
                  	},

                  	{
                  		"name": "Woodrow College",
                  		"date": "04/26/13",
                  		"key_id": "15"
                  	},

                  	{
                  		"name": "Bates House Cafeteria",
                  		"date": "04/30/13",
                  		"key_id": "16"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "11/13/12",
                  		"key_id": "17"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "11/13/12",
                  		"key_id": "18"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "11/27/12",
                  		"key_id": "19"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "04/26/13",
                  		"key_id": "20"
                  	},

                  	{
                  		"name": "Callcott Social Science Center",
                  		"date": "12/03/12",
                  		"key_id": "21"
                  	},

                  	{
                  		"name": "Callcott Social Science Center",
                  		"date": "04/30/13",
                  		"key_id": "22"
                  	},

                  	{
                  		"name": "105 S. Bull Street",
                  		"date": "12/11/12",
                  		"key_id": "23"
                  	},

                  	{
                  		"name": "105 S. Bull Street",
                  		"date": "12/11/12",
                  		"key_id": "24"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "12/12/12",
                  		"key_id": "25"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "12/17/12",
                  		"key_id": "26"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "12/17/12",
                  		"key_id": "27"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/17/12",
                  		"key_id": "28"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "12/17/12",
                  		"key_id": "29"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/17/12",
                  		"key_id": "30"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/17/12",
                  		"key_id": "31"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/17/12",
                  		"key_id": "32"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/18/12",
                  		"key_id": "33"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "12/18/12",
                  		"key_id": "34"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "12/18/12",
                  		"key_id": "35"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "12/18/12",
                  		"key_id": "36"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "04/25/13",
                  		"key_id": "37"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"key_id": "38"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"key_id": "39"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/18/13",
                  		"key_id": "40"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "01/18/13",
                  		"key_id": "41"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "01/18/13",
                  		"key_id": "42"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "01/18/13",
                  		"key_id": "43"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "01/23/13",
                  		"key_id": "44"
                  	},

                  	{
                  		"name": "Callcott House",
                  		"date": "01/25/13",
                  		"key_id": "45"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "01/25/13",
                  		"key_id": "46"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "01/25/13",
                  		"key_id": "47"
                  	},

                  	{
                  		"name": "Russell House",
                  		"date": "04/30/13",
                  		"key_id": "48"
                  	},

                  	{
                  		"name": "Legare/Pinckney Colleges",
                  		"date": "04/30/13",
                  		"key_id": "49"
                  	},

                  	{
                  		"name": "Coker Life Sciences Building",
                  		"date": "04/30/13",
                  		"key_id": "50"
                  	},

                  	{
                  		"name": "LeConte College",
                  		"date": "04/26/13",
                  		"key_id": "51"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "01/25/13",
                  		"key_id": "52"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "01/25/13",
                  		"key_id": "53"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "01/25/13",
                  		"key_id": "54"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "04/26/13",
                  		"key_id": "55"
                  	},

                  	{
                  		"name": "513 Pickens Street",
                  		"date": "04/30/13",
                  		"key_id": "56"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/13/13",
                  		"key_id": "57"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"key_id": "58"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "02/13/13",
                  		"key_id": "59"
                  	},

                  	{
                  		"name": "Thomas Cooper Library",
                  		"date": "04/30/13",
                  		"key_id": "60"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/21/13",
                  		"key_id": "61"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/21/13",
                  		"key_id": "62"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "02/21/13",
                  		"key_id": "63"
                  	},

                  	{
                  		"name": "1723-25 Green Street",
                  		"date": "04/26/13",
                  		"key_id": "64"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/22/13",
                  		"key_id": "65"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/22/13",
                  		"key_id": "66"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/22/13",
                  		"key_id": "67"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/22/13",
                  		"key_id": "68"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "03/27/13",
                  		"key_id": "69"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "03/27/13",
                  		"key_id": "70"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/08/13",
                  		"key_id": "71"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "04/08/13",
                  		"key_id": "72"
                  	},

                  	{
                  		"name": "Bates House",
                  		"date": "04/30/13",
                  		"key_id": "73"
                  	},

                  	{
                  		"name": "Currell College",
                  		"date": "04/30/13",
                  		"key_id": "74"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/10/13",
                  		"key_id": "75"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/10/13",
                  		"key_id": "76"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/11/13",
                  		"key_id": "77"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "04/12/13",
                  		"key_id": "78"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "04/15/13",
                  		"key_id": "79"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/23/13",
                  		"key_id": "80"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/23/13",
                  		"key_id": "81"
                  	},

                  	{
                  		"name": "The Horseshoe",
                  		"date": "04/23/13",
                  		"key_id": "82"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/23/13",
                  		"key_id": "83"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/23/13",
                  		"key_id": "84"
                  	},

                  	{
                  		"name": "Bates House West",
                  		"date": "04/30/13",
                  		"key_id": "85"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/24/13",
                  		"key_id": "86"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "04/30/13",
                  		"key_id": "87"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/25/13",
                  		"key_id": "88"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/25/13",
                  		"key_id": "89"
                  	},

                  	{
                  		"name": "Williams-Brice Building",
                  		"date": "04/26/13",
                  		"key_id": "90"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "04/26/13",
                  		"key_id": "91"
                  	},

                  	{
                  		"name": "Flinn Hall",
                  		"date": "04/26/13",
                  		"key_id": "92"
                  	},

                  	{
                  		"name": "Institute of Archaeology and Anthropology",
                  		"date": "04/25/13",
                  		"key_id": "93"
                  	},

                  	{
                  		"name": "World War Memorial",
                  		"date": "04/25/13",
                  		"key_id": "94"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "04/26/13",
                  		"key_id": "95"
                  	},

                  	{
                  		"name": "Maxcy College",
                  		"date": "04/26/13",
                  		"key_id": "96"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "04/26/13",
                  		"key_id": "97"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "04/26/13",
                  		"key_id": "98"
                  	},

                  	{
                  		"name": "Old Observatory",
                  		"date": "04/26/13",
                  		"key_id": "99"
                  	},

                  	{
                  		"name": "Osborne Administration Building",
                  		"date": "04/26/13",
                  		"key_id": "100"
                  	},

                  	{
                  		"name": "McKissick",
                  		"date": "04/26/13",
                  		"key_id": "101"
                  	},

                  	{
                  		"name": "Hamilton College",
                  		"date": "04/26/13",
                  		"key_id": "102"
                  	},

                  	{
                  		"name": "Sloan College",
                  		"date": "04/26/13",
                  		"key_id": "103"
                  	},

                  	{
                  		"name": "Barnwell College",
                  		"date": "04/26/13",
                  		"key_id": "104"
                  	},

                  	{
                  		"name": "Pendleton Street Garage",
                  		"date": "04/26/13",
                  		"key_id": "105"
                  	},

                  	{
                  		"name": "National Advocacy Center",
                  		"date": "04/26/13",
                  		"key_id": "106"
                  	},

                  	{
                  		"name": "1501 Senate Street",
                  		"date": "04/26/13",
                  		"key_id": "107"
                  	},

                  	{
                  		"name": "1527 Senate Street",
                  		"date": "04/26/13",
                  		"key_id": "108"
                  	},

                  	{
                  		"name": "1600 Hampton Street",
                  		"date": "04/26/13",
                  		"key_id": "109"
                  	},

                  	{
                  		"name": "1600 Hampton Street Annex",
                  		"date": "04/26/13",
                  		"key_id": "110"
                  	},

                  	{
                  		"name": "1600 Hampton Street Garage",
                  		"date": "04/26/13",
                  		"key_id": "111"
                  	},

                  	{
                  		"name": "Kirkland Apartments",
                  		"date": "04/25/13",
                  		"key_id": "112"
                  	},

                  	{
                  		"name": "Senate Street Parking Garage",
                  		"date": "04/26/13",
                  		"key_id": "113"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "04/26/13",
                  		"key_id": "114"
                  	},

                  	{
                  		"name": "Psychology Annex (819 Barnwell Street)",
                  		"date": "04/25/13",
                  		"key_id": "115"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "04/25/13",
                  		"key_id": "116"
                  	},

                  	{
                  		"name": "Close/Hipp Building",
                  		"date": "04/26/13",
                  		"key_id": "117"
                  	},

                  	{
                  		"name": "1731 College Street",
                  		"date": "04/26/13",
                  		"key_id": "118"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "04/25/13",
                  		"key_id": "119"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "04/26/13",
                  		"key_id": "120"
                  	},

                  	{
                  		"name": "Spigner House",
                  		"date": "04/25/13",
                  		"key_id": "121"
                  	},

                  	{
                  		"name": "Spigner House",
                  		"date": "04/26/13",
                  		"key_id": "122"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "04/25/13",
                  		"key_id": "123"
                  	},

                  	{
                  		"name": "Gibbes Court",
                  		"date": "04/26/13",
                  		"key_id": "124"
                  	},

                  	{
                  		"name": "Capstone House",
                  		"date": "04/26/13",
                  		"key_id": "125"
                  	},

                  	{
                  		"name": "Flinn Hall",
                  		"date": "04/26/13",
                  		"key_id": "126"
                  	},

                  	{
                  		"name": "(Sumter) ANDERSON LIBRARY",
                  		"date": "04/25/13",
                  		"key_id": "127"
                  	},

                  	{
                  		"name": "James F. Byrnes Building",
                  		"date": "04/26/13",
                  		"key_id": "128"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "04/26/13",
                  		"key_id": "129"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "04/26/13",
                  		"key_id": "130"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "04/26/13",
                  		"key_id": "131"
                  	},

                  	{
                  		"name": "South Caroliniana Library",
                  		"date": "04/26/13",
                  		"key_id": "132"
                  	},

                  	{
                  		"name": "McCutchen House",
                  		"date": "04/26/13",
                  		"key_id": "133"
                  	},

                  	{
                  		"name": "Kirkland Apartments",
                  		"date": "04/26/13",
                  		"key_id": "134"
                  	},

                  	{
                  		"name": "1710 College Street",
                  		"date": "04/26/13",
                  		"key_id": "135"
                  	},

                  	{
                  		"name": "1710 College Street",
                  		"date": "04/26/13",
                  		"key_id": "136"
                  	},

                  	{
                  		"name": "Gambrell Hall",
                  		"date": "04/26/13",
                  		"key_id": "137"
                  	},

                  	{
                  		"name": "Energy Facility East",
                  		"date": "04/26/13",
                  		"key_id": "138"
                  	},

                  	{
                  		"name": "1719 Green Street",
                  		"date": "04/26/13",
                  		"key_id": "139"
                  	},

                  	{
                  		"name": "John Welsh Humanities Center",
                  		"date": "04/26/13",
                  		"key_id": "140"
                  	},

                  	{
                  		"name": "1714 College Street",
                  		"date": "04/26/13",
                  		"key_id": "141"
                  	},

                  	{
                  		"name": "1728 College Street",
                  		"date": "04/26/13",
                  		"key_id": "142"
                  	},

                  	{
                  		"name": "Petigru College",
                  		"date": "04/26/13",
                  		"key_id": "143"
                  	},

                  	{
                  		"name": "Davis College",
                  		"date": "04/26/13",
                  		"key_id": "144"
                  	},

                  	{
                  		"name": "Melton Observatory",
                  		"date": "04/26/13",
                  		"key_id": "145"
                  	},

                  	{
                  		"name": "Rutledge College",
                  		"date": "04/30/13",
                  		"key_id": "146"
                  	},

                  	{
                  		"name": "Currell Annex",
                  		"date": "04/30/13",
                  		"key_id": "147"
                  	},

                  	{
                  		"name": "President's House",
                  		"date": "04/30/13",
                  		"key_id": "148"
                  	},

                  	{
                  		"name": "Preston Residential College",
                  		"date": "04/30/13",
                  		"key_id": "149"
                  	},

                  	{
                  		"name": "Lieber College",
                  		"date": "04/30/13",
                  		"key_id": "150"
                  	},

                  	{
                  		"name": "Health Sciences Building",
                  		"date": "04/30/13",
                  		"key_id": "151"
                  	},

                  	{
                  		"name": "Drayton Hall",
                  		"date": "04/30/13",
                  		"key_id": "152"
                  	},

                  	{
                  		"name": "Wardlaw College",
                  		"date": "04/30/13",
                  		"key_id": "153"
                  	},

                  	{
                  		"name": "Inventory Central Supply",
                  		"date": "04/30/13",
                  		"key_id": "154"
                  	},

                  	{
                  		"name": "Facilities Management Center",
                  		"date": "04/30/13",
                  		"key_id": "155"
                  	},

                  	{
                  		"name": "720 College Street/Pearle Labs",
                  		"date": "04/30/13",
                  		"key_id": "156"
                  	},

                  	{
                  		"name": "Carolina  Coliseum",
                  		"date": "04/30/13",
                  		"key_id": "157"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "04/30/13",
                  		"key_id": "158"
                  	},

                  	{
                  		"name": "Law Center",
                  		"date": "04/30/13",
                  		"key_id": "159"
                  	},

                  	{
                  		"name": "Koger Center for the Arts",
                  		"date": "04/30/13",
                  		"key_id": "160"
                  	},

                  	{
                  		"name": "School of Music",
                  		"date": "04/30/13",
                  		"key_id": "161"
                  	},

                  	{
                  		"name": "Sumwalt",
                  		"date": "04/30/13",
                  		"key_id": "162"
                  	},

                  	{
                  		"name": "Earth and Water Sciences Center",
                  		"date": "04/30/13",
                  		"key_id": "163"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "04/30/13",
                  		"key_id": "164"
                  	},

                  	{
                  		"name": "Longstreet Annex",
                  		"date": "04/30/13",
                  		"key_id": "165"
                  	},

                  	{
                  		"name": "Longstreet Theatre",
                  		"date": "04/30/13",
                  		"key_id": "166"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - A",
                  		"date": "04/30/13",
                  		"key_id": "167"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - F",
                  		"date": "04/30/13",
                  		"key_id": "168"
                  	},

                  	{
                  		"name": "McBryde Quadrangle - G",
                  		"date": "04/30/13",
                  		"key_id": "169"
                  	},

                  	{
                  		"name": "Thomson Student Health Center",
                  		"date": "04/30/13",
                  		"key_id": "170"
                  	},

                  	{
                  		"name": "109 S. Bull Street",
                  		"date": "04/30/13",
                  		"key_id": "171"
                  	},

                  	{
                  		"name": "Graduate Science Research Center",
                  		"date": "04/30/13",
                  		"key_id": "172"
                  	},

                  	{
                  		"name": "McClintock",
                  		"date": "04/30/13",
                  		"key_id": "173"
                  	},

                  	{
                  		"name": "Sims",
                  		"date": "04/30/13",
                  		"key_id": "174"
                  	},

                  	{
                  		"name": "South Tower",
                  		"date": "04/30/13",
                  		"key_id": "175"
                  	},

                  	{
                  		"name": "USC Child Development and Research Center",
                  		"date": "04/30/13",
                  		"key_id": "176"
                  	},

                  	{
                  		"name": "Booker T. Washington Auditorium",
                  		"date": "04/30/13",
                  		"key_id": "177"
                  	},

                  	{
                  		"name": "South Quadrangle",
                  		"date": "04/30/13",
                  		"key_id": "178"
                  	},

                  	{
                  		"name": "East Quadrangle",
                  		"date": "04/30/13",
                  		"key_id": "179"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "04/30/13",
                  		"key_id": "180"
                  	},

                  	{
                  		"name": "1244 Blossom Street (University Technology Services)",
                  		"date": "04/30/13",
                  		"key_id": "181"
                  	},

                  	{
                  		"name": "Energy Facility West",
                  		"date": "04/30/13",
                  		"key_id": "182"
                  	},

                  	{
                  		"name": "Sumter Street Parking Garage",
                  		"date": "04/30/13",
                  		"key_id": "183"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle A",
                  		"date": "04/30/13",
                  		"key_id": "184"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle B",
                  		"date": "04/30/13",
                  		"key_id": "185"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle C",
                  		"date": "04/30/13",
                  		"key_id": "186"
                  	},

                  	{
                  		"name": "Green (West) Quadrangle D",
                  		"date": "04/30/13",
                  		"key_id": "187"
                  	},

                  	{
                  		"name": "Public Health Research Center",
                  		"date": "04/30/13",
                  		"key_id": "188"
                  	},

                  	{
                  		"name": "Strom Thurmond Wellness and Fitness Center",
                  		"date": "04/30/13",
                  		"key_id": "189"
                  	},

                  	{
                  		"name": "Colonial Center",
                  		"date": "04/30/13",
                  		"key_id": "190"
                  	},

                  	{
                  		"name": "Cliff Apartments",
                  		"date": "04/30/13",
                  		"key_id": "191"
                  	},

                  	{
                  		"name": "300 Main Street",
                  		"date": "04/30/13",
                  		"key_id": "192"
                  	},

                  	{
                  		"name": "1200 Catawba Street",
                  		"date": "04/30/13",
                  		"key_id": "193"
                  	},

                  	{
                  		"name": "Swearingen Engineering Center",
                  		"date": "04/30/13",
                  		"key_id": "194"
                  	},

                  	{
                  		"name": "Field House",
                  		"date": "04/30/13",
                  		"key_id": "195"
                  	},

                  	{
                  		"name": "Roost - E",
                  		"date": "04/30/13",
                  		"key_id": "196"
                  	},

                  	{
                  		"name": "Roost Residence Hall",
                  		"date": "04/30/13",
                  		"key_id": "197"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "04/30/13",
                  		"key_id": "198"
                  	},

                  	{
                  		"name": "Discovery Building",
                  		"date": "04/30/13",
                  		"key_id": "199"
                  	},

                  	{
                  		"name": "Greenhouse #2",
                  		"date": "04/30/13",
                  		"key_id": "200"
                  	},

                  	{
                  		"name": "Thornwell College",
                  		"date": "05/14/13",
                  		"key_id": "201"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "05/14/13",
                  		"key_id": "202"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "05/14/13",
                  		"key_id": "203"
                  	},

                  	{
                  		"name": "McMaster College",
                  		"date": "05/14/13",
                  		"key_id": "204"
                  	},

                  	{
                  		"name": "Blatt Physical Education Center",
                  		"date": "05/15/13",
                  		"key_id": "205"
                  	},

                  	{
                  		"name": "Jones Physical Science Center",
                  		"date": "05/20/13",
                  		"key_id": "206"
                  	},

                  	{
                  		"name": "Williams-Brice Stadium",
                  		"date": "05/20/13",
                  		"key_id": "207"
                  	},

                  	{
                  		"name": "Athletic Practice Facility",
                  		"date": "05/20/13",
                  		"key_id": "208"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"key_id": "209"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "05/21/13",
                  		"key_id": "210"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "05/21/13",
                  		"key_id": "211"
                  	},

                  	{
                  		"name": "Carolina Gardens Apartments",
                  		"date": "05/21/13",
                  		"key_id": "212"
                  	},

                  	{
                  		"name": "Harper/Elliott Colleges",
                  		"date": "05/22/13",
                  		"key_id": "213"
                  	},

                  	{
                  		"name": "DeSaussure College",
                  		"date": "05/22/13",
                  		"key_id": "214"
                  	},

                  	{
                  		"name": "(Lancaster) GREGORY HEALTH & WELLNESS",
                  		"date": "05/23/13",
                  		"key_id": "215"
                  	},

                  	{
                  		"name": "(Lancaster) GREGORY HEALTH & WELLNESS",
                  		"date": "05/23/13",
                  		"key_id": "216"
                  	},

                  	{
                  		"name": "Columbia Hall",
                  		"date": "05/29/13",
                  		"key_id": "217"
                  	},

                  	{
                  		"name": "Benson",
                  		"date": "06/07/13",
                  		"key_id": "218"
                  	},

                     ];

                  $(document).ready(function(){
                	
						var specialHazardsLen = specialHazards.length;
	                  	console.log("length: " + specialHazardsLen);
	                  	var html="[";
	                  	$.each(specialHazards, function(index, element){
	                  	   
	                  	   var name = this['label'];

	                  	   if (index != specialHazardsLen - 1) {
	                             console.log('not last');
	                             html += '"' + name + '",';
	                         }else{
	                  	   html += '"' + name + '"';
	                  	   console.log('last');
	                         }
	                  	});
	                  	html += "]";
	                  	$('#specialHazardsInput').attr("data-source", html);
	          
                  	
                  	var len = buildingsArray.length;
                  	//console.log("length: " + len);
                  	var html="[";
                  	$.each(buildingsArray, function(index, element){
                  	   
                  	   var name = this['name'];

                  	   if (index != len - 1) {
                             console.log('not last');
                             html += '"' + name + '",';
                         }else{
                  	   html += '"' + name + '"';
                  	   console.log('last');
                         }
                  	});
                  	html += "]";
                  	$('#buildings').attr("data-source", html);
                  	});


                  var specialHazards = [						{label: 'Flexal',children: []}			,
                                								{label: 'Guanarito',children: []}			,
                                								{label: 'Junin',children: []}			,
                                								{label: 'Machupo',children: []}			,
                                								{label: 'Sabia',children: []},								
                                                    		{label: 'Recombinant DNA', id:'1',serialRequired:'1',serialNumber:"1k2h493233", rooms: [{room:'101'},{room:'103'}, {rooms:'106'}], children: [									
                                                    				{label: 'Viral Vectors', id:'2',serialRequired:'1',serialNumber:"3fk2h493233", rooms: [{room:'103'}], children: [							
                                                    						{label: 'Adeno-associated Virus (AAV)', hasChecklist: '1',  id:'2',children: []}					,
                                                    						{label: 'Adenovirus', id:'3', hasChecklist: '1', children: []}					,
                                                    						{label: 'Baculovirus',children: []}					,
                                                    						{label: 'Epstein-Barr Virus (EBV)',children: []}					,
                                                    						{label: 'Herpes Simplex Virus (HSV)',children: []}					,
                                                    						{label: 'Poxvirus / Vaccinia',children: []}					,
                                                    						{label: 'Retrovirus / Lentivirus (EIAV)',children: []}					,
                                                    						{label: 'Retrovirus / Lentivirus (FIV)',children: []}					,
                                                    						{label: 'Retrovirus / Lentivirus (HIV)',children: []}					,
                                                    						{label: 'Retrovirus / Lentivirus (SIV)',children: []}					,
                                                    						{label: 'Retrovirus / MMLV (Amphotropic or Pseudotyped)',children: []}					,
                                                    						{label: 'Retrovirus / MMLV (Ecotropic)', id:'4',rooms: [{room:'103'}],children: []}					
                                                    				]}		
                                                    			]					
                                                    		},									
                                                    		{label: 'Select Agents and Toxins',children: [									
                                                    				{label: 'HHS Select Agents and Toxins',children: [							
                                                    						{label: 'Abrin',children: []}					,
                                         						{label: 'Shiga-like ribosome inactivating proteins',children: []}					,
                                                    						{label: 'Shigatoxin',children: []}					,
                                                    						{label: 'South American Haemorrhagic Fever viruses',children: [					
                                                    								
                                                    						]},					
                                                    						{label: 'Staphylococcal enterotoxins',children: []}					,
                                                    						{label: 'T-2 toxin',children: []}					,
                                                    						{label: 'Tetrodotoxin',children: []}					,
                                                    						{label: 'Tick-borne encephalitis complex (flavi) viruses',children: [					
                                                    								{label: 'Central European Tick-borne encephalitis',children: []}			,
                                                    								{label: 'Far Eastern Tick-borne encephalitis',children: []}			,
                                                    								{label: 'Kyasanur Forest disease',children: []}			,
                                                    								{label: 'Omsk Hemorrhagic Fever',children: []}			,
                                                    								{label: 'Russian Spring and Summer encephalitis',children: []}			
                                                    						]},					
                                                    						{label: 'Variola major virus (Smallpox virus)',children: []}					,
                                                    						{label: 'Variola minor virus (Alastrim)',children: []}					,
                                                    						{label: 'Yersinia pestis',children: []}					
                                                    				]},												
                                                    				{label: 'USDA VETERINARY SERVICES (VS) SELECT AGENTS',children: [							
                                                    						{label: 'African horse sickness virus',children: []}					,
                                                    						{label: 'African swine fever virus',children: []}					,
                                                    						{label: 'Akabane virus',children: []}					,
                                                    						{label: 'Avian influenza virus (highly pathogenic)',children: []}					,                                                    				
                                                    						{label: 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3',children: []}					,
                                                    						{label: 'Virulent Newcastle disease virus 1',children: []}					
                                                    				]},							
                                                    				{label: 'USDA PPQ SELECT AGENTS AND TOXINS',children: [							
                                                    						{label: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)', id:'5', rooms: [{room:'103'}],children: []}					,
                                                    						{label: 'Phoma glycinicola (formerly Pyrenochaeta glycines)',children: []}					,
                                                    						{label: 'Ralstonia solanacearum race 3, biovar 2',children: []}					,
                                                    						{label: 'Rathayibacter toxicus',children: []}					,
                                                    						{label: 'Sclerophthora rayssiae var zeae',children: []}					,
                                                    						{label: 'Synchytrium endobioticum',children: []}					,
                                                    						{label: 'Xanthomonas oryzae',children: []}					,
                                                    						{label: 'Xylella fastidiosa (citrus variegated chlorosis strain)',children: []}					
                                                    				]}							
                                                    		]},									
                                                    		{label: 'Human-derived Materials',children: [									
                                                    				{label: 'Blood',children: []}					,		
                                                    				{label: 'Fluids',children: []}					,		
                                                    				{label: 'Cells',children: []}					,		
                                                    				{label: 'Cell line',children: []}					,		
                                                    				{label: 'Other tissue',children: []}							
                                                    		]},									
                                                    		{label: 'Biosafety Level 1 (BSL-1)',children: []}					,                                                					
                                                    									
                                                    ];
                                                    */
</script>
<?php
require_once '../bottom_view.php';
?>