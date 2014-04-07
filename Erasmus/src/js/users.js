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

    console.log($scope.labContacts);
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

  //click handler checks the user's IsActive property, udpates accordingly
  $scope.handleUserActive = function(user){

    userDTO = angular.copy(user);

  	//set the IsActive state for the user in the view model
	  if(userDTO.IsActive == false || !userDTO.IsActive){
  		userDTO.IsActive = true;
  	}else{
  		userDTO.IsActive = false;
  	}


  	//send the edit to the server, pass it to the callback
    convenienceMethods.updateObject( userDTO, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser'  );

  	//testFactory.saveUser('../../ajaxaction.php?action=saveUser', user, switchActiveState);

  }
  onFailSaveUser = function(user){
    alert('There was an error saving the user ' + user.Name);
  }
  onSaveUser = function( userDTO, user ){
     userDTO.edit = false;
     convenienceMethods.setPropertiesFromDTO( userDTO, user );
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
    newUser.IsActive = true;
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

      convenienceMethods.setPropertiesFromDTO( $scope.returnedByModal[0], $scope.returnedByModal[1] );

      if($scope.returnedByModal[0].Roles.length > 1){
        //TODO
        //update other copies of user that in scope for other roles tables
      }

    }, function () {

      //$log.info('Modal dismissed at: ' + new Date());
    });
  };

};

// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.

var ModalInstanceCtrl = function ($scope, $modalInstance, items, convenienceMethods) {

  $scope.items = items;
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  //set the type of user
  if(items[0].Class == "PrincipleInvestigator"){
    $scope.userType = items[2][3];
    $scope.userCopy = angular.copy(items[0].user);
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
    userCopy.IsDirty = true;
    //$modalInstance.close($scope.items);

    //save user
    var url=""
    convenienceMethods.updateObject( userCopy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' )
  };

  function onSaveUser(returnedData, oldData){
    //if user is a PI, save that record
    if(convenienceMethods.arrayContainsObject(returnedData.Roles, $scope.roles[3])){
      //save pi
    }else{
      data = [];
      data[0] = returnedData;
      data[1] = oldData;
    }

    oldData.IsDirty = false;
    //else cclose and send back user object and copy as param of close funciton
    $modalInstance.close(data);

  }

  function onFailSaveUser(){

  }

  function onSavePI(returnedData, oldData){
    //call set properties, 
  }

  function onFailSavePi(){

  }

  function setProperties(data){

    //unset dirty flag

    //close modal, passing back updated user/pi objects
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
};