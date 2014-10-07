///////////to do: develop a local factory to share data between views

var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodModule','once'])


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
        controller: personnelController
      }
    )
    .otherwise(
      {
        redirectTo: '/pis'
      }
    );
})
.factory('userHubFactory', function(convenienceMethods,$q){

  var factory = {};
  var allPis = [];
  var pis = [];

  factory.setPIs = function(pis){
    this.pis = pis;
  }

  factory.getPIs = function(){
    return this.pis;
  }

  factory.getAllPis = function(){
    
    //if we don't have a the list of pis, get it from the server
    var deferred = $q.defer();

    //lazy load
    if(this.allPis){
      deferred.resolve(this.allPis);
      return deferred.promise;
    }

    var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
      convenienceMethods.getDataAsDeferredPromise(url).then(
      function(promise){
        deferred.resolve(promise);
      },
      function(promise){
        deferred.reject();
      }
    );
    return deferred.promise;
  }

  return factory

});
//called on page load, gets initial user data to list users
var MainUserListController = function(userHubFactory,$scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route,$window,userHubFactory) {
 //console.log($modal);
  $scope.showInactive = false;
  $scope.users = [];
  $scope.order='Last_name';
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  function init(){

    convenienceMethods.getData('../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK',onGetPis,onFailGetPis);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK',onGetRoles,onFailGetRoles);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK',onGetUsers,onFailGetUsers);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK',onGetDepartments,onFailGetDepartments);

    // sometimes $location.path() isn't set yet, so check for this
    if(!$location.path()) {
      // by default pis are loaded, so set path to this, and update selectedRoute accordingly
      $location.path("/pis");
    }
    if(!$scope.selectedRoute)$scope.selectedRoute = $location.path();
  }

  function onGetDepartments(data){
    $scope.departments = data;
  }

  function onFailGetDepartments(data){
    alert("Something went wrong when the system tried to get the list of departments");
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

  function onFailGetUsers(){
    alert('Something went wrong when we tried to build the list of users.');
  }

  function onFailGetPIs(){
    alert('Something went wrong when we tried to build the list of Principal Investigators.');
  }

  function onGetPis(data){
     userHubFactory.setPIs(data);
     angular.forEach(data, function(pi, key){
      pi.Buildings = [];
      angular.forEach(pi.Rooms, function(room, key){
       if(room&&!convenienceMethods.arrayContainsObject(pi.Buildings, room.Building))pi.Buildings.push(room.Building);
      });
    });

    $scope.pis = data;
    //do this only if we have not yet looped through our users, otherwise we will append the list of users to itself when we switch routes
    if(!$scope.run){
      $scope.setUsers();
    }else{
      alert('already run');
    }
    $scope.run = true;
  }

  function onFailGetPis(){
    alert('Something went wrong when the system tried to get the list of all Principal Investigators.')
  }

  //fix up scope user collections
  $scope.setUsers = function(){
    //push users into correct arrays based on role
    angular.forEach($scope.users, function(user, key){
      user.userTypes = convenienceMethods.getUserTypes(user);
     // console.log(user);
      $scope.putUserInRightPlace(user);
    });
    //console.log($scope.LabContacts);
  }

  $scope.putUserInRightPlace = function(user){

     if(user.Class == 'PrincipalInvestigator'){
        if(!convenienceMethods.arrayContainsObject($scope.PIs, user)){
          $scope.PIs.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.PIs, user, null, true);
          $scope.PIs[idx] = angular.copy(user);
        }

        $scope.putUserInRightPlace(user.User);
     }

     if(!user.userTypes)user.userTypes = convenienceMethods.getUserTypes(user);
     if(!$scope.LabContacts)$scope.LabContacts = [];
      if(user.userTypes.indexOf('Lab Contact') > -1){

        if(!convenienceMethods.arrayContainsObject($scope.LabContacts, user)){
          $scope.LabContacts.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.LabContacts, user, null, true);
          $scope.LabContacts[idx] = angular.copy(user);
        }

        
        //lab contacts have supervising pi's, but the user object only comes with a key_id for the supervising pi, so we find the right pi
        angular.forEach($scope.pis, function(pi, key){
           pi.User.userTypes = convenienceMethods.getUserTypes( pi.User);
           if(user.Supervisor_id == pi.Key_id){
             user.Supervisor = {}; 
             user.Supervisor.User = {};
             user.Supervisor.User.Name = pi.User.Name;
              user.Supervisor.User.Lab_phone = pi.User.Lab_phone;
             user.Supervisor.Key_id = pi.Key_id;
           }
        });
      }

      if(!$scope.Admins)$scope.Admins = [];
      if(user.userTypes.indexOf('Admin') > -1 || user.userTypes.indexOf('Radiation Inspector') > -1 || user.userTypes.indexOf('Safety Inspector') > -1){
        if(!convenienceMethods.arrayContainsObject($scope.Admins, user)){
          $scope.Admins.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.Admins, user, null, true);
          $scope.Admins[idx] = angular.copy(user);
        }
      }
  }

  //----------------------------------------------------------------------
  //
  // ROUTING
  //
  //----------------------------------------------------------------------
  $scope.setRoute = function(){
    $location.path($scope.selectedRoute);
  }

};

var labContactController = function(userHubFactory, $scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route) {

  //look at GET parameters to determine if we should alter the view accordingly
  //if we have linked to this view from the PI hub to manage a PI's lab personnel, filter the view to only those PI's associated with th
  
  if($location.search().piId){
    $scope.piId = $location.search().piId;
  }

  if($location.$$host.indexOf('graysail'<0))$scope.isProductionServer = true;


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
       convenienceMethods.getUserTypes(selectedItem);
       $scope.putUserInRightPlace(selectedItem);
    });
  };

  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    console.log(user);
    var userCopy = angular.copy(user);
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(userCopy.Is_active == false){
      userCopy.Is_active = true;
    }else{
      userCopy.Is_active = false;
    }
    convenienceMethods.updateObject( userCopy, user, onSetUserActive, onFailSetUserActive, '../../ajaxaction.php?action=saveUser' );
  }

  function onSetUserActive(returned, old){
    console.log(returned);
    old.IsDirty = false;
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(returned.Is_active == 0){
      returned.Is_active = false;
    }else{
      returned.Is_active = true;
    }
    old.Is_active = returned.Is_active;
    console.log(old);
  }

  function onFailSetUserActive(){
    alert("The user could not be saved");
  }

  $scope.deactiveUser = function(user){
    $scope.handleUserActive(user);
    var userCopy = angular.user(user);
    userCopy.Is_active = false;
    convenienceMethods.updateObject (userCopy, user, onDeactivateUser, onFailDeactivateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onDeactivateUser(userDTO,user){
    idx = convenienceMethods.arrayContainsObject(user, $scope.PI.LabPersonnel, null, true);
    $scope.PI.LabPersonnel.splice(idx, 1);
  }

  function onFailDeactivateUser(){
    $scope.error = 'There was a problem when the system tried to deactivate the user.  Check your internet connection.'
  }
}


//controller for modal instance for lab contacts
var labContactModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window, userHubFactory) {
  if($location.$$host.indexOf('graysail'<0))$scope.isProductionServer = true;



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
    if(!data.Roles)data.Roles = [];
    angular.forEach($scope.userCopy.Roles, function(role, key){
      data.Roles.push(role);
    });
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  //$location.path('/contacts');

  $scope.userCopy = items[0];
  if(items[1])$scope.user = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  if($location.search().piId){
    $scope.piId = $location.search().piId;
    $scope.pis = userHubFactory.getPIs();

    if(!$scope.userCopy.Supervisor){
      var piLen = $scope.pis.length;
      for(i=0;i<piLen;i++){
        if($location.search().piId === $scope.pis[i].Key_id){
          $scope.userCopy.Supervisor = $scope.pis[i];
        }
      }
    }
  }

  $scope.saveUser = function (userCopy, user) {
    console.log(userCopy);
    var roles;
    userCopy.Is_active = true;
    userCopy.IsDirty = true;    
    if(userCopy.Primary_department)userDTO.Primary_department_id = userCopy.Primary_department.Key_id;
    if(userCopy.Supervisor)userDTO.Supervisor_id = userCopy.Supervisor.Key_id;

    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    console.log(userCopy);
    if(!userCopy.Key_id)userCopy.Is_active = true;
    userCopy.Supervisor = {};
    convenienceMethods.updateObject( userCopy, user, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
   
    $scope.userCopy.Key_id = data.Key_id;
    console.log( data );
    console.log( $scope.userCopy);
    var rolesToAdd = $scope.userCopy.Roles;
    //see if we have new roles, but only if the user is not new, in which case all roles are new
    if($scope.user){
      console.log('right here');
      var rolesToAdd = [];
      angular.forEach($scope.userCopy.Roles, function(role, key){
        if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
      });
    }
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });

    if(userCopy.userTypes){
      if(userCopy.userTypes.indexOf('Principal Investigator')>-1 && !convenienceMethods.arrayContainsObject($scope.pis,userCopy)){
        var piDTO = {
          Class: "PrincipalInvestigator",
          User_id: data.Key_id,
          Is_active: true
        }
        convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
      }

      if(userCopy.userTypes.indexOf("Safety Inspector" > -1)){
        var inspectorDTO = {
          Class: "Inspector",
          User_id: data.Key_id,
          Is_active: true
        }
        convenienceMethods.updateObject( inspectorDTO, userCopy.Departments, onSaveNewInspector, onFailSaveNewInspector, '../../ajaxaction.php?action=saveInspector');
      }
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
        console.log('here in the no key branch');
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
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
   // console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert("There was a problem when trying to remove a role from the user.");
  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    userCopy.IsDirty = true;
    userCopy.Is_active = true;
    if(userCopy.Supervisor)userCopy.Supervisor_id = userCopy.Supervisor.Key_id;
    if(userCopy.Primary_department)userCopy.Primary_department_id = userCopy.Primary_department.Key_id;
    //console.log(userCopy);

    var userDTO = {
      Class: "User",
      Is_active: true,
      Key_id: userCopy.Key_id,
      First_name: userCopy.First_name,
      Last_name: userCopy.Last_name,
      Email: userCopy.Email,
      Emergency_phone: userCopy.Emergency_phone,
      Lab_phone: userCopy.Lab_phone,
      Office_phone: userCopy.Office_phone,
      Username: userCopy.Username
    }

    //we separate properties that belong to sub-objects so that we don't throw js errors if they are not set
    if(userCopy.Primary_department)userDTO.Primary_department_id = userCopy.Primary_department.Key_id;
    if(userCopy.Supervisor)userDTO.Supervisor_id = userCopy.Supervisor.Key_id;

    convenienceMethods.updateObject( userDTO, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onSaveNewPI(piDTO, depts){
    //console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    console.log($scope.piCopy);
    angular.forEach($scope.departmentToAdd, function(department, key){
      console.log(department);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });

    $modalInstance.close($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    console.log('inspector');
  }

  function onFailSaveNewInspector(){
    alert('There was a problem creating the new Inspector.');
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
  
};
var personnelController = function($scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route) {
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
      userCopy.Roles.push($scope.roles[0]);
    }

    $scope.items.push(userCopy);
    $scope.items.push(user);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'personnelModal.html',
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
       convenienceMethods.getUserTypes(selectedItem);
       $scope.putUserInRightPlace(selectedItem);
    });
  };

  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    console.log(user);
    var userCopy = angular.copy(user);
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(userCopy.Is_active == false){
      userCopy.Is_active = true;
    }else{
      userCopy.Is_active = false;
    }
    console.log(userCopy);
    convenienceMethods.updateObject( userCopy, user, onSetUserActive, onFailSetUserActive, '../../ajaxaction.php?action=saveUser' );
  }

  function onSetUserActive(returned, old){
    console.log(returned);
    old.IsDirty = false;
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(returned.Is_active == 0){
      returned.Is_active = false;
    }else{
      returned.Is_active = true;
    }
    old.Is_active = returned.Is_active;
    console.log(old);
  }

  function onFailSetUserActive(){
    alert("The user could not be saved");
  }
}


//controller for modal instance for lab contacts
var personnelModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  if($location.$$host.indexOf('graysail'<0))$scope.isProductionServer = true;

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
    if(!data.Roles)data.Roles = [];
    data.Roles.push($scope.roles[1]);
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/EHSPersonnel');

  $scope.userCopy = items[0];
  if(items[1])$scope.user = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.saveUser = function (userCopy, user) {
    var roles;
    userCopy.Is_active = true;
    userCopy.IsDirty = true;
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

    if($scope.userCopy.userTypes.indexOf('PrincipalInvestigator')>-1 && !convenienceMethods.arrayContainsObject($scope.pis,$scope.userCopy)){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }
   
    $modalInstance.close($scope.userCopy);
  }

  function onSaveNewPI(piDTO, depts){
    console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    console.log($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    console.log('pi');
    $scope.inspectorCopy = angular.copy(inspectorDTO);
    console.log($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }


  $scope.onSelectPI = function($item, $model, $label){
    console.log($item);
    console.log($model);
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

var piController = function($scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route, $timeout) {


  //have we come here from piHub, by clicking the edit PI button?
  //if so, we should have a pi's last name in our $location.search()

  if($location.search().pi)$scope.searchText = $location.search().pi;

  $scope.wcount = function() {
    $timeout(function() {
      $scope.watchers = convenienceMethods.watchersContainedIn($scope);
    });
  };

  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/pis';
  $scope.order = 'User.Last_name';
  $scope.addPi = function (pi) {
    $scope.items = [];
    if(pi){
      //we are editing a PI that already exists
      console.log(pi);
      var piCopy = angular.copy(pi);
    }else{
      //we are creating a PI user
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
       convenienceMethods.getUserTypes(selectedItem.User);
       $scope.putUserInRightPlace(selectedItem.User);

      console.log(selectedItem);
      //a new pi, push into the pis array
      if(!convenienceMethods.arrayContainsObject($scope.pis,selectedItem)){
        console.log('new pi');
        $scope.pis.push(selectedItem);
      }else{
        //an edited pi, find in scope and update accordingly
        var idx = convenienceMethods.arrayContainsObject($scope.pis,selectedItem, null, true);
        console.log(idx);
        $scope.pis[idx] = angular.copy(selectedItem);
      }

      console.log($scope.pis);
    });
  }

  $scope.departmentFilter = function() {
   
    return function(pi) {
         var show = false;
        //for pis that don't have departments, don't filter them unless the filter has some text
        if(!pi.Departments)pi.Departments = [];
        if(!pi.Departments.length){
          if(typeof $scope.selectedDepartment == 'undefined' || $scope.selectedDepartment.length == 0){
            show = true;
          }
        }

        angular.forEach(pi.Departments, function(department, key){
          if(typeof $scope.selectedDepartment == 'undefined'|| department.Name.toLowerCase().indexOf($scope.selectedDepartment.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.buildingFilter = function() {
    return function(pi) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!pi.Buildings)pi.Buildings = [];
        if(!pi.Buildings.length){
          if(typeof $scope.selectedBuilding == 'undefined' || $scope.selectedBuilding.length == 0){
            show = true;
          }
        }
        angular.forEach(pi.Buildings, function(building, key){
          if(typeof $scope.selectedBuilding == 'undefined' || building.Name.toLowerCase().indexOf($scope.selectedBuilding.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.handlePiActive = function(pi){
    pi.testFlag = 'test';
    pi.IsDirty = true;
    console.log(pi);
    var pi = angular.copy(pi); 
    var piDTO = {
          Class: "PrincipalInvestigator",
          User_id: pi.User_id,
          Is_active: !pi.Is_active,
          Key_id: pi.Key_id
    }
    convenienceMethods.updateObject( piDTO, pi, onSetPiActive, onFailSetPiActive, '../../ajaxaction.php?action=savePI', pi );
  }


  function onSetPiActive(returned, old){
    console.log(old);
    console.log(returned);
    old.IsDirty = false;
    old.Is_active = !old.Is_active;

    var idx = convenienceMethods.arrayContainsObject($scope.pis, old, null, true);
    $scope.pis[idx] = angular.copy(old);
  }

  function onFailSetPiActive(){
    $scope.piCopy.IsDirty = false;
    alert("The PI could not be saved.");
  }
  $scope.wcount();
}


//controller for modal instance for lab contacts
var piModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  if($location.$$host.indexOf('graysail'<0))$scope.isProductionServer = true;


  $scope.failFindUser = false;
  console.log(items[0]);
  if($window.isProductionServer)$scope.isProductionServer = true;

  $scope.getAuthUser = function(){
    console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.piCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    if(!data.Roles)data.Roles = [];
    data.Roles.push($scope.roles[3]);
    console.log(data);
    $scope.piCopy.User = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/pis');

  $scope.piCopy = items[0];
  if(items[1]){
    $scope.pi = items[1];
    $scope.userCopy = $scope.pi.User;
  }
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.savePi = function(){
    $scope.piCopy.IsDirty = true;
    //save the user record
    var userDTO = {
      Class: "User",
      Is_active: true,
      Key_id: $scope.piCopy.User.Key_id,
      First_name: $scope.piCopy.User.First_name,
      Last_name: $scope.piCopy.User.Last_name,
      Email: $scope.piCopy.User.Email,
      Emergency_phone: $scope.piCopy.User.Emergency_phone,
      Lab_phone: $scope.piCopy.User.Lab_phone,
      Office_phone: $scope.piCopy.User.Office_phone,
      Username: $scope.piCopy.User.Username
    }
    console.log(userDTO);

    convenienceMethods.updateObject( userDTO, $scope.piCopy.User, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );

  }

  function onSaveUser(returned, old){

    $scope.userCopy = angular.copy(returned);
    if(returned.Key_id && returned.Key_id > 0){
      //if the pi exists already, we don't need to save it
      if(!$scope.piCopy.Key_id){
         var piDTO = {
            Class: "PrincipalInvestigator",
            User_id: returned.Key_id,
            Is_active: true
         }
        convenienceMethods.updateObject( piDTO, returned, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
      }else{
        onSaveNewPI($scope.piCopy);
      }
    }else{
      onFailSaveUser()
    }
  }

  function onFailSaveUser(){
    alert('There was a problem saving the PI');
  }

  function onSaveNewPI(returned, old){
    console.log(returned);
   // convenienceMethods.setPropertiesFromDTO($scope.piCopy, returned);
    $scope.piCopy.Key_id = returned.Key_id;
    $scope.piCopy.Is_active = returned.Is_active;
    $scope.piCopy.User = angular.copy($scope.userCopy);

    var rolesToAdd = [];
    angular.forEach($scope.piCopy.User.Roles, function(role, key){
      if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
    });
    
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });

    var deptsToAdd = [];
    angular.forEach($scope.piCopy.Departments, function(dept, key){
      if(!convenienceMethods.arrayContainsObject(deptsToAdd,dept))deptsToAdd.push(dept);
    });
    angular.forEach(deptsToAdd, function(dept, key){
      console.log(dept);
      $scope.onSelectDepartment(dept);
    });

    $scope.piCopy.IsDirty = false;
    //if we have a new inspector to save, save it
    //convenienceMethods.getUserTypes
    console.log($scope.piCopy);
    $scope.piCopy.User = angular.copy($scope.userCopy);
    $modalInstance.close($scope.piCopy);

  }

  function onFailSaveNewPi(){
    alert('The PI could not be saved.');
  }


  $scope.onSelectDepartment = function($item, $model, $label){
      //console.log($scope.piCopy);

    if($scope.piCopy && $scope.piCopy.Key_id){
      if($model)$model.IsDirty = true;

      piDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.piCopy.Key_id,
          add: true
      }
     // console.log(piDTO);
      convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, $model  );
 
    }else{
        if(!$scope.piCopy.Departments)$scope.piCopy.Departments = [];
        if(!convenienceMethods.arrayContainsObject($scope.piCopy.Departments,$item))$scope.piCopy.Departments.push($item);
        //console.log($scope.piCopy);
      }
  }

  function onAddDepartment(returned,dept,model){
    console.log('asdf');
    if(model)model.IsDirty = false;
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


  $scope.cancel = function () {
    console.log('closing');
    $modalInstance.dismiss('cancel');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    console.log('inspector');
  }

  function onFailSaveNewInspector(){
    alert('There was a problem creating the new Inspector.');
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
    if($model)model.IsDirty = false;
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