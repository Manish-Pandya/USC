var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodModule'])


.config(function($routeProvider){
  $routeProvider
    .when('/pis', 
      {
        templateUrl: 'userHubPartials/pis.html', 
        controller: piController
      }
    )
    .when('/contacts', 
      {
        templateUrl: 'userHubPartials/contacts.html', 
        controller: labContactController
      }
    )   
    .when('/EHSPersonnel', 
      {
        templateUrl: 'userHubPartials/EHSPersonnel.html', 
        controller: MainUserListController
      }
    )
    //.when('', {template: '', controller: })
    .otherwise(
      {
        redirectTo: '/pis'
      }
    );
});

//called on page load, gets initial user data to list users
var MainUserListController = function($scope, $modal, $routeParams, $browser, $sniffer, $rootElement, $location, convenienceMethods, $filter, $route,$window) {
 //console.log($modal);

  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    
    if($window.isProductionServer)$scope.isProductionServer = true;

    console.log('init');

    convenienceMethods.getData('../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK',onGetPis,onFailGetPis);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK',onGetRoles,onFailGetRoles);
	  convenienceMethods.getData('../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK',onGetUsers,onFailGet);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK',onGetDepartments,onFailGet);

    if(!$scope.selectedRoute)$scope.selectedRoute = $location.path();
    console.log($scope.selectedRoute);

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
    console.log(data);
    $scope.users = data;
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

    //do this only if we have not yet looped through our users, otherwise we will append the list of users to itself when we switch routes
    if(!$scope.run){
      //push users into correct arrays based on role
      angular.forEach($scope.users, function(user, key){
        angular.forEach(user.Roles, function(role, key){
          //skip PIs, they are gotten through a different api call
          var trimmedName = role.Name.replace(/\s/g, '');
          if(!$scope[trimmedName+'s'])$scope[trimmedName+'s'] = [];
          if(role.Key_id != 4 && !convenienceMethods.arrayContainsObject($scope[trimmedName+'s'],user))$scope[trimmedName+'s'].push(user);
          //for lab contacts, set the appropriate PI as their supervisor
          if(role.Key_id == 5){
            console.log('here');
            angular.forEach($scope.pis, function(pi, key){
              if(user.Supervisor_id == pi.Key_id){
                user.Supervisor = pi;
              }
            });
          }
        });
      });
    }
    $scope.run = true;
    console.log($scope);
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


  //----------------------------------------------------------------------
  //
  // ROUTING
  //
  //----------------------------------------------------------------------
  $scope.setRoute = function(){
    $location.path($scope.selectedRoute);
  }

  //----------------------------------------------------------------------
  //
  // USER SAVE METHODS
  //
  //----------------------------------------------------------------------
  //click handler checks the user's Is_active property, udpates accordingly
  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    userDTO = angular.copy(user);

  	//set the Is_active state for the user in the view model
	  userDTO.Is_active = !userDTO.Is_active;
  
  	//send the edit to the server, pass it to the callback
    if(!userDTO.Class == "PrincipalInvestigator"){
      convenienceMethods.updateObject( userDTO, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser'  );
    }else{
      convenienceMethods.updateObject( userDTO, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=savePI'  );
    }

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
  	if($scope.userCopy.Key_id)$scope.userCopy.Roles.splice(index,1);
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

  $scope.filterizer = function(selectedPi,contact) {
    console.log(contact);
    return function(selectedPi,contact) {
        return contact.Supervisor.User.Name != selectedPi;
    }
  }

  $scope.departmentFilter = function() {
    var show = false;
    console.log($scope.selectedDepartment);
    return function(pi) {
        angular.forEach(pi.Departments, function(department, key){
          if(!$scope.selectedDepartment || department.Name.toLowerCase().indexOf($scope.selectedDepartment.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.buildingFilter = function() {
    var show = false;
    return function(pi) {
        angular.forEach(pi.Buildings, function(building, key){
          if(!$scope.selectedBuilding || building.Name.toLowerCase().indexOf($scope.selectedBuilding.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }


};

var labContactController = function($scope, $modal, $routeParams, $browser, $sniffer, $rootElement, $location, convenienceMethods, $filter, $route) {
  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/contacts';

  $scope.addUser = function (user) {
    $scope.items = [];
    if(user){
      //we are editing a user that already exists
      var userCopy = angular.copy(user);
    }else{
      //we are creating a new user
      var userCopy = {}
      userCopy.Class = "User";
      userCopy.Roles = [];
      userCopy.Roles.push($scope.roles[4]);
    }

    $scope.items.push(userCopy);
    $scope.items.push(user);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'labContactModal.html',
      controller: labContactModalInstanceController,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
      selectedItem.IsDirty = false;
      console.log(selectedItem);
      if(!convenienceMethods.arrayContainsObject($scope.LabContacts,selectedItem)){
        //new user, push the object into the scope
        $scope.LabContacts.push(selectedItem);
      }else{
        //edited user.  find the object in scope and update its properties
        var idx = convenienceMethods.arrayContainsObject($scope.LabContacts,selectedItem,null,true);
        user = $scope.LabContacts[idx];
        convenienceMethods.setPropertiesFromDTO(selectedItem,user);
      }
    });
  };
}


//controller for modal instance for lab contacts
var labContactModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  $scope.failFindUser = false;
  console.log($window.isProductionServer);
  if($window.isProductionServer)$scope.isProductionServer = true;

  
  $scope.getAuthUser = function(){
    console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.userCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    console.log(data);
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/contacts');

  $scope.userCopy = items[0];
  if(items[1])$scope.user = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.saveUser = function (userCopy, user) {
    var roles;
    userCopy.Is_active = true;
    userCopy.IsDirty = true;    
    userCopy.Primary_department_id = userCopy.Primary_department.Key_id;
    userCopy.Supervisor_id = userCopy.Supervisor.Key_id;

    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    console.log(userCopy);
    if(!userCopy.Key_id)userCopy.Is_active = true;
    convenienceMethods.updateObject( userCopy, user, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    userCopy.Key_id = data.Key_id;
    var rolesToAdd = $scope.userCopy.Roles;
    //see if we have new roles, but only if the user is not new, in which case all roles are new
    if($scope.user){
      var rolesToAdd = [];
      angular.forEach($scope.userCopy.Roles, function(role, key){
        if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
      });
    }
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });

    if(userCopy.isPI){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }
   
    $modalInstance.close($scope.userCopy);
  }

  $scope.onSelectPI = function($item, $model, $label){
    console.log($item);
    console.log($model);
  }

  $scope.onSelectRole = function($item, $model, $label,id){
      console.log('we are in the role branch');
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
    alert("There was a problem when trying to add a role to the user.");
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

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
    console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert("There was a problem when trying to remove a role from the user.");
  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    console.log(userCopy);
    userCopy.IsDirty = true;
    userCopy.Is_active = true;
    userCopy.Supervisor_id = userCopy.Supervisor.Key_id;
    userCopy.Primary_department_id = userCopy.Primary_department.Key_id;
    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
  
};

var piController = function($scope, $modal, $routeParams, $browser, $sniffer, $rootElement, $location, convenienceMethods, $filter, $route) {
  console.log('pi contoller');
  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/pis';

  $scope.addPi = function (pi) {
    $scope.items = [];
    if(pi){
      //we are editing a user that already exists
      var piCopy = angular.copy(pi);
    }else{
      //we are creating a new user
      var piCopy = {}
      piCopy.Class = "PrincipalInvestigator";
      piCopy.User = {};
      piCopy.User.Class = "User";
      piCopy.User.Roles = [];
      piCopy.User.Roles.push($scope.roles[3]);
    }

    $scope.items.push(piCopy);
    $scope.items.push(pi);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'piModal.html',
      controller: piModalInstanceController,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
      console.log(selectedItem);
      selectedItem.IsDirty = false;
      console.log(selectedItem);
      if(!convenienceMethods.arrayContainsObject($scope.pis,selectedItem)){
        //new user, push the object into the scope
        $scope.pis.push(selectedItem);
      }else{
        //edited user.  find the object in scope and update its properties
        console.log('index: '+convenienceMethods.arrayContainsObject($scope.pis,selectedItem,null,true));
        var idx = convenienceMethods.arrayContainsObject($scope.pis,selectedItem,null,true);
        var pi = $scope.pis[idx];
        console.log( pi );
        convenienceMethods.setPropertiesFromDTO(selectedItem,pi);
      }
    });
  };
}


//controller for modal instance for lab contacts
var piModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  $scope.failFindUser = false;
  console.log(items);
  if($window.isProductionServer)$scope.isProductionServer = true;

  
  $scope.getAuthUser = function(){
    console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.userCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    console.log(data);
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/pis');

  $scope.piCopy = items[0];
  if(items[1])$scope.pi = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.saveUser = function (piCopy, user) {
    var roles;
    piCopy.Is_active = true;
    piCopy.IsDirty = true;    
   
    if(!piCopy.Key_id)roles = piCopy.User.Roles;
    //save user
    console.log(piCopy);
    if(!piCopy.Key_id)piCopy.Is_active = true;
    convenienceMethods.updateObject( piCopy.User, user, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    userCopy.Key_id = data.Key_id;
    var rolesToAdd = $scope.userCopy.Roles;
    //see if we have new roles, but only if the user is not new, in which case all roles are new
    if($scope.user){
      var rolesToAdd = [];
      angular.forEach($scope.userCopy.Roles, function(role, key){
        if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
      });
    }
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });

    if(userCopy.isPI){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }
   
    $modalInstance.close($scope.userCopy);
  }

  $scope.onSelectPI = function($item, $model, $label){
    console.log($item);
    console.log($model);
  }

  $scope.onSelectRole = function($item, $model, $label,id){
      console.log('we are in the role branch');
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
    alert("There was a problem when trying to add a role to the user.");
  }

  //new user save methods
  $scope.saveNewUser = function(piCopy, pi){
    console.log('here')
    console.log(piCopy);
    piCopy.IsDirty = true;
    piCopy.User.Is_active = true;
    convenienceMethods.updateObject( piCopy.User, piCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );

   // if(pi)

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
        if(!$scope.piCopy.Departments)$scope.piCopy.Departments = [];
        $scope.piCopy.Departments.push($item);
        console.log($scope.piCopy);
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

      if($scope.piCopy.User.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.piCopy.User.Key_id,
          add: true
      }

      console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
     }
  }

  function onAddRole(returned,role,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.piCopy.User.Roles,role))$scope.piCopy.User.Roles.push(role);
  }

  function onFailAddRole(){
    alert('An error occurred when trying to add the role.');
  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.piCopy.User.Key_id,
        add: false
      }

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.piCopy.User.Roles, dept, null, true);
    console.log(idx);
    if(idx>-1)$scope.piCopy.User.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert('There was a problem when attempting to remove the role.');
  }

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    console.log(data);
    $scope.piCopy.IsDirty = false;
    $scope.piCopy.User.Key_id = data.Key_id;
    angular.forEach($scope.piCopy.User.Roles, function(role, key){
      $scope.onSelectRole(role);
    });

   if(!$scope.piCopy.Key_id){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, $scope.piCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }else{
      $modalInstance.close($scope.piCopy);
    }

  }

  function onSaveNewPI(piDTO, depts){
    console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    console.log($scope.piCopy);
    angular.forEach($scope.piCopy.Departments, function(department, key){
      console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });

    $modalInstance.close($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }
  
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
    if(!items[0].User)items[0].User = {Class: 'User'}
    if(!items[0].User.Roles){
       items[0].User.Roles = [];
      items[0].User.Roles.push($scope.roles[3])
    }
    $scope.userCopy = angular.copy(items[0].User);
    $scope.piCopy   = angular.copy(items[0]);

  }else{
    $scope.userCopy = items[0]
    if(items[0].Supervisor){
      console.log('here');
      $scope.userType = items[2][4];
    }
  }
  console.log( $scope.userCopy );

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
    if(!userCopy.Key_id)userCopy.Is_active = true;
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
    if(returnedData.Roles && convenienceMethods.arrayContainsObject($scope.userCopy.Roles, $scope.roles[3])){
      //save pi

      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: returnedData.Key_id,
      }
      console.log(piDTO);
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
    console.log('pi');
    $scope.piCopy = angular.copy( returnedData );
    angular.forEach(oldData.Departments, function(department, key){
      console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });
    $modalInstance.close($scope.items);
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

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
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
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }

    $scope.items[0] = $scope.userCopy;
    $modalInstance.close($scope.items);

  }

  function onSaveNewPI(piDTO, depts){
    console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    console.log($scope.piCopy);
    angular.forEach(depts, function(department, key){
      console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });
    $modalInstance.close($scope.items);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  $scope.saveNewPi = function(){
     console.log(userCopy);
    userCopy.IsDirty = true;
    
    console.log(userCopy);

    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }


};