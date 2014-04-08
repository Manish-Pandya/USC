var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodModule']);

//called on page load, gets initial user data to list users
var UserListController = function($scope, $modal, $routeParams, $browser, $sniffer, $rootElement, $location, convenienceMethods) {
 //console.log($modal);
  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    convenienceMethods.getData('../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK',onGetPis,onFailGetPis);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK',onGetRoles,onFailGetRoles);
	  convenienceMethods.getData('../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK',onGetUsers,onFailGet);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK',onGetDepartments,onFailGet);
  }

  function onGetDepartments(data){
    $scope.departments = data;
  }

  function onGetRoles(data){
    $scope.roles = data;
  }

  function onFailGetRoles(){

  }

  //grab set user list data into the $scrope object
  function onGetUsers(data) {
    $scope.users = data;
    console.log(data);
	  //$scope.users = data;

   // 

  }
  function onFailGet(){
    alert('Something went wrong when we tried to build the list of users.');
  }

  function onGetPis(data){
     angular.forEach(data, function(pi, key){
      pi.Buildings = [];
      angular.forEach(pi.Rooms, function(room, key){
        if(!convenienceMethods.arrayContainsObject(pi.Buildings, room.Building))pi.Buildings.push(room.Building);
      });
    });

    $scope.pis = data;

    $scope.Admins = [];
    $scope.SafetyInspectors = [];
    $scope.RadiationInspectors = [];
    $scope.LabContacts = [];

    //push users into correct arrays based on role
    angular.forEach($scope.users, function(user, key){
      console.log(user);
      angular.forEach(user.Roles, function(role, key){
        //skip PIs, they are gotten through a different api call
        var trimmedName = role.Name.replace(/\s/g, '');

        if(role.Key_id != 4)$scope[trimmedName+'s'].push(user);
        //for lab contacts, set the appropriate PI as their supervisor
        if(role.Key_id == 5){
          console.log('here');
          angular.forEach($scope.pis, function(pi, key){
            console.log(user);
            console.log(pi);
            if(user.Supervisor_id == pi.Key_id){
              user.Supervisor = pi;
            }
          });
        }
      });
    });

    console.log($scope.LabContacts);
  }
  function onFailGetPis(){

  }


  $scope.editUser = function(user){

  	//set some display properties for the user objects, so that we can hilight the user currently being edited
  	angular.forEach($scope.users, function(thisUser, key){
	  	thisUser.edit = false;
	  	thisUser.notEdit = true;
	  	thisUser.updated = false;
	});
	
	   //set display properties to hilight user currently being edited.
	  user.notEdit = false;
  	user.edit = true;

  	//make a copy of the edited user, save it in the scope.  this will allow us to cancel any edits 
  	$scope.userCopy = angular.copy(user);


  }

  //click handler checks the user's Is_active property, udpates accordingly
  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    userDTO = angular.copy(user);

  	//set the Is_active state for the user in the view model
	  userDTO.Is_active = !userDTO.Is_active;
  
  	//send the edit to the server, pass it to the callback
    convenienceMethods.updateObject( userDTO, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser'  );

  	//testFactory.saveUser('../../ajaxaction.php?action=saveUser', user, switchActiveState);

  }
  onFailSaveUser = function(user){
    alert('There was an error saving the user ' + user.Name);
  }
  onSaveUser = function( userDTO, user ){


     user.IsDirty = false;
     userDTO.edit = false;
     user.Is_active = userDTO.Is_active;
     user.edit = false;
     console.log(user);
  }
  /*
   * USER SAVE METHODS
   * used for creating and updating users
   * 
   */
  $scope.saveUser = function(copy, user){

  	//send the edit to the server, pass it to the callback
    convenienceMethods.updateObject( copy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser'  );

  }
/*
  $scope.addUser = function(){

  	//new empty user object
  	newUser = {};

  	//grab a list of the User objects properties, set them to empty strings.
  	for (var property in $scope.users[0]) {
		if ($scope.users[0].hasOwnProperty(property)) {
			  console.log(property);
		    newUser[property] = '';
		  }
    }

    //make sure the new user is active and not in an edited state.
    newUser.Is_active = true;
    newUser.edit = false;

    //add the new user to the beginning of the $scope.users array of user objects
  	$scope.users.unshift(newUser);

  	//send the user to the edit function, so that its properties can be edited in the UI
  	$scope.editUser(newUser);

  }
*/
  //undo edits to a user
  $scope.cancelEdits = function(user){

  	//set the user back to a state that indicates it's not currently being edited.
  	//since we haven't saved the user, we still have the original user preserved in $scope.users, and can access it
  	 user.notEdit = false;
  	 user.edit = false;

  	//reset the display properties for all the users
  	for(i=0;i<$scope.users.length;i++){

  		thisUser = $scope.users[i];
  		thisUser.notEdit = false;
  		thisUser.edit = false;
  		thisUser.updated = false;
  		$scope.userCopy.Roles.adding = false;
  	}
  }

  //remove a role from a user
  $scope.removeRole = function(index){
    //find the role in the user's role by its index, remove it
  	$scope.userCopy.Roles.splice(index,1);
  }

  $scope.addRole = function(){
    //set the user's role state to adding, so that we display the html select of user roles
    $scope.userCopy.Roles.adding = true;
    $scope.filterRoles();
  }

  $scope.confirmAdd = function(role){
    $scope.userCopy.Roles.adding = false;
    //add the role to the user's roles
    $scope.userCopy.Roles.push(role);
  }

  //filter the roles so that we can only add roles that user doesn't already have
  $scope.filterRoles = function() {
    console.log($scope.userCopy.Roles);
     if($scope.userCopy){
      for(i=$scope.roles.length;i>-1;i--){
         item=$scope.roles[i];
         console.log(item);
         if( $scope.userCopy.Roles.indexOf(item) != -1){
            console.log(i);
            console.log( 'yes');
            $scope.roles.splice(i,1);
         }
      }
    }
  };


  $scope.addUser = function (user) {
    console.log('here');
    $scope.items = [];
    if(user){
      var userCopy = angular.copy(user);
    }else{
      var userCopy = {
          Class: "User"
       }
    }

    $scope.items.push(userCopy);
    $scope.items.push(user);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'myModalContent.html',
      controller: ModalInstanceCtrl,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
      $scope.returnedByModal = selectedItem;
      console.log($scope.returnedByModal);

      if($scope.returnedByModal[1]){
        convenienceMethods.setPropertiesFromDTO( $scope.returnedByModal[0], $scope.returnedByModal[1] );
        if($scope.returnedByModal[1].Supervisor_id){
          angular.forEach($scope.pis, function(pi, key){
            if(pi.Key_id == $scope.returnedByModal[1].Supervisor_id)$scope.returnedByModal[1].Supervisor = angular.copy(pi);
          });
        }
      }else{
        angular.forEach($scope.returnedByModal[0].Roles, function(role, key){
          var trimmedName = role.Name.replace(/\s/g, '');
          scopeRole = [trimmedName+'s'];
          if(!arrayContainsObject(scopeRole, role)){scopeRole.push(role)}
        });
      }

      

      if($scope.returnedByModal[1].Class = 'PrincipalInvestigator'){

      }else{
        angular.forEach($scope.returnedByModal[0].Roles, function(role, key){
          var trimmedName = role.Name.replace(/\s/g, '');
          scopeRole = [trimmedName+'s'];
          if(!arrayContainsObject(scopeRole, role)){scopeRole.push(role)}
        });
      }
/*
      if($scope.returnedByModal[0].Roles.length > 1){
        //TODO
        //update other copies of user that in scope for other roles tables
      }*/

    }, function () {

      //$log.info('Modal dismissed at: ' + new Date());
    });
  };

};

// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.

var ModalInstanceCtrl = function ($scope, $modalInstance, items, convenienceMethods) {
  console.log($modalInstance);
  console.log(items);

  $scope.items = items;
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  //set the type of user
  if(items[0].Class == "PrincipalInvestigator"){
    console.log('pi');
    $scope.userCopy = angular.copy(items[0].User);
    $scope.piCopy   = angular.copy(items[0]);
  }else{
    $scope.userCopy = items[0]
    if(items[0].Supervisor){
      console.log('here');
      $scope.userType = items[2][4];
    }
  }
  console.log(  $scope.userCopy  );

  $scope.selected = {
    item: $scope.items[0]
  };


  $scope.saveUser = function (userCopy, user) {
    var roles;
    userCopy.IsDirty = true;
    //$modalInstance.close($scope.items);
    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    console.log(userCopy);
    convenienceMethods.updateObject( userCopy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onSaveUser(returnedData, oldData, roles){
    console.log(oldData);
    if(!oldData){
       returnedData.Roles = $scope.userCopy.Roles;
       oldData = angular.copy(returnedData);
    }

     console.log(oldData);


    if(!returnedData.Roles)returnedData.Roles = $scope.userCopy.Roles;
    //if user is a PI, save that record
    if(returnedData.Roles && convenienceMethods.arrayContainsObject($scope.userCopy.Roles, $scope.roles[4])){
      //save pi

      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: returnedData.Key_id
      }

      convenienceMethods.updateObject( piDTO, piDTO, onSavePI,onFailSavePi, '../../ajaxaction.php?action=savePI',roles );
  

    }else{
      data = [];
      data[0] = returnedData;
      data[1] = oldData;
      $modalInstance.close(data);
    }

    oldData.IsDirty = false;


    if(oldData.Class == "PrincipalInvestigator"){
      data = [];
      data[0] = oldData;
      data[0].User = returnedData;
      data[0].Departments = $scope.piCopy.Departments;
    }

    //else cclose and send back user object and copy as param of close funciton
    

  }

  function onFailSaveUser(){

  }

  function onSavePI(returnedData, oldData){
    $scope.piCopy = angular.copy( returnedData );
  }

  function onFailSavePi(){
    alert("There was a problem saving the Principal Investigator.")
  }

  function setProperties(data){

    //unset dirty flag

    //close modal, passing back updated user/pi objects
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
    
  $scope.onSelectDepartment = function($item, $model, $label){

    if($scope.piCopy && $scope.piCopy.Key_id){
      if($model)$model.IsDirty = true;

      piDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.piCopy.Key_id,
          add: true
      }
      
      convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, $model  );
 
    }else{
        if(!$scope.userCopy.isPI){
        $scope.userCopy.Supervisor = {}
        if(!$scope.userCopy.Supervisor.Departments)$scope.userCopy.Supervisor.Departments = [];
        $scope.userCopy.Supervisor.Departments.push($item);
        console.log($scope.userCopy);
      }else{
        
      }
    }
  }

  function onAddDepartment(returned,dept,model){
    model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.piCopy.Departments,dept))$scope.piCopy.Departments.push(dept);
  }

  function onFailAddDepartment(){

  }

  $scope.removeDepartment = function(department, item, model){
    department.IsDirty = true;
    console.log(department);

    piDTO = {
      Class: "RelationshipDto",
        relation_id: department.Key_id,
        master_id: $scope.piCopy.Key_id,
        add: false
      }

    convenienceMethods.updateObject( piDTO, department, onRemoveDepartment, onFailRemoveDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, department );
  }

  function onRemoveDepartment(returned,dept){
    console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.piCopy.Departments, dept, null, true);
    console.log(idx);
    if(idx>-1)$scope.piCopy.Departments.splice(idx,1);
  }

  function onFailRemoveDepartment(){

  }

  $scope.onSelectRole = function($item, $model, $label,id){
      if($model)$model.IsDirty = true;

      if($scope.userCopy.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.userCopy.Key_id,
          add: true
      }

      console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
        if(convenienceMethods.arrayContainsObject($scope.userCopy.Roles,$scope.roles[3]))$scope.userCopy.isPI = true;
     }
  }

  function onAddRole(returned,dept,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.userCopy.Roles,dept))$scope.userCopy.Roles.push(dept);
  }

  function onFailAddRole(){

  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.userCopy.Key_id,
        add: false
      }

    convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
  }

  function onRemoveRole(returned,dept){
    console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
    console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){

  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    console.log(userCopy);
    userCopy.IsDirty = true;
    
    console.log(userCopy);

    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    userCopy.Key_id = data.Key_id;
    angular.forEach(userCopy.Roles, function(role, key){
      $scope.onSelectRole(role, $scope.selectedRole);
    });

    if(userCopy.isPI){

      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }

      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI,onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }

    $scope.items[0] = $scope.userCopy;
    $modalInstance.close($scope.items);

  }

  function onSaveNewPI(piDTO, depts){
    $scope.userCopy.Supervisor_id = piDTO.Key_id;
    $scope.userCopy.Supervisor = angular.copy(piDTO);
    $scope.piCopy = angular.copy(piDTO);
    console.log($scope.piCopy);
    angular.forEach(depts, function(department, key){
      console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

};