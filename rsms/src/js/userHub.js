var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once'])
.directive('tableRow', ['$window', function($window) {
    return {
      restrict : 'A',
      link : function(scope, element, attributes) {
      }
    }
 }])
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
    .when('/labPersonnel',
      {
        templateUrl: 'userHubPartials/labPersonnel.html',
        controller: labPersonnelController
      }
    )
    .when('/uncategorized',
      {
        templateUrl: 'userHubPartials/uncategorized.html',
        controller: uncatController
      }
    )
    .otherwise(
      {
        redirectTo: '/pis'
      }
    );
})
.filter('isPI',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var pis = [];             /* more code, to make code better  */
    var i = users.length
    while(i--){
      if(userHubFactory.hasRole(users[i], 'principal investigator')){
        if(users[i].PrincipalInvestigator){
          userHubFactory.getBuildingsByPi(users[i].PrincipalInvestigator);
          pis.unshift(users[i]);
        }else{
          users[i].isUncat = true;
        }
      }
    }
    return pis;
  }
}])
.filter('isEHSPersonnel',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      var shouldPush = false;
      if(userHubFactory.hasRole(users[i], 'admin') || userHubFactory.hasRole(users[i], 'radiation') || userHubFactory.hasRole(users[i], 'read only')){
        shouldPush = true;
      }

      if(userHubFactory.hasRole(users[i], 'inspector')){
        if(users[i].Inspector){
          shouldPush = true;
        }else{
          users[i].isUncat = true;
        }
      }
      if(shouldPush)personnel.unshift(users[i]);
    }
    return personnel;
  }
}])
.filter('isNotContact',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( !userHubFactory.hasRole(users[i], 'contact') && userHubFactory.hasRole(users[i], 'personnel') ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
      }
    }
    return personnel;
  }
}])
.filter('isLabContact',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( userHubFactory.hasRole(users[i], 'contact') || userHubFactory.hasRole(users[i], 'contact') ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
      }
    }
    return personnel;
  }
}])
.filter('isLabPersonnel',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( userHubFactory.hasRole(users[i], 'personnel') || userHubFactory.hasRole(users[i], 'contact') ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
        if( userHubFactory.hasRole(users[i], 'contact') )users[i].isContact = true;
      }
    }
    return personnel;
  }
}])
.filter('isUncat',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var uncat = [];
    var i = users.length
    while(i--){
      if(!users[i].Roles || !users[i].Roles.length){
        uncat.unshift(users[i]);
      }

      if(userHubFactory.hasRole(users[i], 'principal investigator')){
        if(!users[i].PrincipalInvestigator){
          uncat.unshift(users[i]);
        }
      }

      if(userHubFactory.hasRole(users[i], 'inspector')){
         if(!users[i].Inspector){
          uncat.unshift(users[i]);
        }
      }
    }
    return uncat;
  }
}])
.filter('tel', function () {
    return function (phoneNumber) {
        if (!phoneNumber)
            return phoneNumber;

        return formatLocal('US', phoneNumber);
    }
})
.factory('userHubFactory', function(convenienceMethods,$q, $rootScope, roleBasedFactory){

  var factory = {};
  factory.roles = [];
  factory.departments = [];
  factory.pis = [];
  factory.users = [];
  factory.labContacts = [];
  factory.personnel = [];
  factory.modalData = {};
  factory.uncategorizedUsers = [];
  factory.openedModal = false;

  factory.getSupervisor = function(user){
    var i = factory.users.length;
    while(i--){
      if(factory.users[i].PrincipalInvestigator){
          if(user.Supervisor_id == factory.users[i].PrincipalInvestigator.Key_id)user.Supervisor = factory.users[i];
      }
    }
  }

  factory.getPIs = function(){
    var pis = [];
    var i = factory.users.length;
    while(i--){
      if(factory.users[i].PrincipalInvestigator)pis.unshift(factory.users[i]);
    }
    return pis;
  }


  factory.getAllUsers = function(){
    var deferred = $q.defer();

      //lazy load
      if(factory.users.length){
        deferred.resolve(factory.users);
        return deferred.promise;
      }

      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getUsersForUserHub&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(users){
          factory.users = users;
          deferred.resolve(users);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.hasRole = function(user, role)
  {
    var j = user.Roles.length;
    while(j--){
      var userRole = user.Roles[j];
      if(userRole.Name.toLowerCase().indexOf(role.toLowerCase())>-1) return true
    }
    return false;
  }

  factory.getRelation = function(object, objIndex, foreignKey, collectionToSearch )
  {
      var i = collectionToSearch.length;
      while(i--){
        if(object[foreignKey] == collectionToSearch[i].Key_id)object[objIndex]=collectionToSearch[i];
      }
  }

  factory.getUserByPiUser_id = function(id)
  {
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].Key_id == id)return factory.users[i];
      }
  }

  factory.getUserByPIId = function(id){
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].PrincipalInvestigator && factory.users[i].PrincipalInvestigator.Key_id == id)return factory.users[i];
      }
  }

  factory.getUserId = function(id){
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].Key_id == id)return factory.users[i];
      }
  }

  factory.getBuildingsByPi = function(pi)
  {
      pi.Buildings = [];
      if(!pi.Rooms || !pi.Rooms.length)return;
      var i = pi.Rooms.length;
      var buildingIds = [];

      while(i--){
          var room = pi.Rooms[i];
          if( room && buildingIds.indexOf( room.Building.Key_id ) < 0 ){
            buildingIds.push(room.Building.Key_id);
            pi.Buildings.push(room.Building);
          }
      }
  }

  factory.saveUser = function(userDto)
  {
    console.log(userDto);
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUser";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, userDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePi = function(pi)
  {
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=savePI";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, pi)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }


  factory.setModalData = function( data )
  {
    this.modalData = data;
  }

  factory.getModalData = function()
  {
    return this.modalData;
  }

  factory.getAllRoles = function()
  {
      var deferred = $q.defer();

      //lazy load
      if(factory.roles.length){
        deferred.resolve(factory.roles);
        return deferred.promise;
      }
      
      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(roles){
          factory.roles = roles;
          deferred.resolve(roles);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.getAllDepartments = function()
  {
      var deferred = $q.defer();

      //lazy load
      if(factory.departments.length){
        deferred.resolve(factory.departments);
        return deferred.promise;
      }

      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(departments){
          factory.departments = departments;
          deferred.resolve(departments);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.saveUserRoleRelations = function(userId, rolesToAdd){
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUserRoleRelations&callback=JSON_CALLBACK&userId="+userId+'&'+$.param({roleIds:rolesToAdd});
    var deferred = $q.defer();
      convenienceMethods.getDataAsDeferredPromise(url)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePIDepartmentRelations = function(piId, departmentIds){
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?callback=JSON_CALLBACK&action=savePIDepartmentRelations&piId="+piId+'&'+$.param({departmentIds:departmentIds});
    var deferred = $q.defer();
      convenienceMethods.getDataAsDeferredPromise(url)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.saveUserRoleRelation = function(user, role, add)
  {
    relDto = {
        Class: "RelationshipDto",
        relation_id: role.Key_id,
        master_id: user.Key_id,
        add: add
    }

    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUserRoleRelation";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, relDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePIDepartmentRelation = function(pi, dept, add)
  {
    relDto = {
        Class: "RelationshipDto",
        relation_id: dept.Key_id,
        master_id: pi.Key_id,
        add: add
    }

    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=savePIDepartmentRelation";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, relDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.getPIByUserId = function(user_id)
  {
    var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getPIByUserId&id='+user_id+'&callback=JSON_CALLBACK'
    return convenienceMethods.getDataAsDeferredPromise(url)
      .then(
        function(pi){
          return pi;
        },
        function(promise){
          return 'error';
        }
      )
  }

  factory.lookUpUser = function(string)
  {
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=lookupUser&username="+string+"&callback=JSON_CALLBACK";
        var deferred = $q.defer();
          convenienceMethods.getDataAsDeferredPromise(url)
            .then(
              function(promise){
                deferred.resolve(promise);
              },
              function(promise){
                deferred.reject();
              }
            );
        return deferred.promise
  }

  factory.placeUser = function(user, previousFlag)
  {
      var defer = $q.defer();
      var i = user.Roles.length;
      if(i==0 && factory.notInCollection(user, factory.uncategorizedUsers)){
        factory.uncategorizedUsers.push(user);
      }
      while(i--){
        if(factory.hasRole(user, 'Principal Investigator')){
          factory.getPIByUserId(user.Key_id)
            .then(
              function(pi){
                if(factory.notInCollection(pi, factory.pis)){
                  pi.User = user;
                  factory.pis.push(pi);
                }
              }
            )
        }
        if(factory.hasRole(user, 'admin') || factory.hasRole(user, 'inspector') || factory.hasRole(user, 'radiation') && factory.notInCollection(user, factory.personnel))factory.personnel.push(user);
        if(factory.hasRole(user, 'Lab Contact') && factory.notInCollection(user, factory.labContacts))factory.labContacts.push(user);
      }
  }

  factory.removeUserFromCollections = function(user)
  {
      var defer = $q.defer();
      var i = user.Roles.length;

      if(i!=0 && factory.notInCollection(user, factory.uncategorizedUsers)){
        var j = factory.uncategorizedUsers.length;
        while(j--){
          if(factory.uncategorizedUsers[j].Key_id == user.Key_id)factory.uncategorizedUsers.splice(j,1);
        }
      }

      while(i--){
        if(!factory.hasRole(user, 'Principal Investigator')){
          factory.getPIByUserId(user.Key_id)
            .then(
              function(pi){
                //find pi in pis collection and remove
                var j = factory.pis.length;
                while(j--){
                  if(factory.pis[j].Key_id == pi.Key_id)factory.pis.splice(j,1);
                }
              }
            )
        }
        if(!factory.hasRole(user, 'admin') && !factory.hasRole(user, 'inspector') && !factory.hasRole(user, 'radiation') && !factory.notInCollection(user, factory.personnel)){
            //find user in admin and remove
            var j = factory.personnel.length;
            while(j--){
              if (factory.personnel[j].Key_id == user.Key_id)factory.personnel.splice(j,1);
            }
        }
        if(!factory.hasRole(user, 'Lab Contact') && !factory.notInCollection(user, factory.labContacts)){
            //find user in contacts and remove
            //find user in admin and remove
            var j = factory.labContacts.length;
            while(j--){
              if (factory.labContacts[j].Key_id == user.Key_id)factory.labContacts.splice(j,1);
            }
        }
      }
  }

  factory.notInCollection = function(object, collection)
  {
      var i = collection.length;
      while(i--){
        if(collection[i].Key_id == object.Key_id)return false;
      }
      return true;
  }

  factory.iterate = function(num){
    return num+1;
  }

  return factory

});

var MainUserListController = function(userHubFactory, $scope, $rootScope, $location, convenienceMethods, $route) {
    $rootScope.uhf=userHubFactory;
    $rootScope.order = 'Last_name';

    //----------------------------------------------------------------------
    //
    // ROUTING
    //
    //----------------------------------------------------------------------
    $scope.setRoute = function(){
      $location.path($scope.selectedRoute);
    }

    if(!$location.path()) {
      // by default pis are loaded, so set path to this, and update selectedRoute accordingly
      $location.path("/pis");
    }
    if(!$scope.selectedRoute)$scope.selectedRoute = $location.path();

    $rootScope.showInactive = false;

    $rootScope.handleUserActive = function(user){
      $rootScope.error = '';
      user.IsDirty = true;
      var userCopy = convenienceMethods.copyObject(user);
      userCopy.Is_active = !userCopy.Is_active;
      userHubFactory.saveUser(userCopy)
        .then(
          function(returnedUser){
            user.Is_active = !user.Is_active;
            user.IsDirty = false;
          },
          function(){
            user.IsDirty = false;
            $rootScope.error = 'The user could not be saved.  Please check your internet connection and try again.'
          }
        )
    }

    userHubFactory.getAllRoles()
      .then(
        function(roles){
          return roles;
        },
        function(){
          $rootScope.error = 'The system could not retrieve the list of roles.  Please check your internet connection and try again.'
        }
      )
    userHubFactory.getAllDepartments()
      .then(
        function(departments){
          return departments;
        },
        function(){
          $rootScope.error = 'The system could not retrieve the list of roles.  Please check your internet connection and try again.'
        }
      )

    $scope.activeFilter = function(showInactive, pis){
      return function(obj) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!pis && obj.Is_active != showInactive)show = true;
        if(pis && obj.PrincipalInvestigator && obj.PrincipalInvestigator.Is_active != showInactive){
          show = true;
        }
        return show;
    }
  }


}

var piController = function($scope, $modal, userHubFactory, $rootScope, convenienceMethods, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $rootScope.renderDone = false;
    userHubFactory.getAllUsers()
      .then(
          function(users){
            $scope.pis = userHubFactory.users;
            $rootScope.neededUsers = true;
            if($location.search().pi && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserByPIId($location.search().pi));
            }
            $rootScope.renderDone = true;
            return users;
          },
          function(){
            $rootScope.error="There was a problem getting the list of Principal Investigators.  Please check your internet connection and try again."
          }
        )

    $scope.openModal = function(pi){
        if(!pi){
          pi = {Is_active: true, Is_new:true, Class:'User', Roles:[], PrincipalInvestigator:{Is_active:true, Departments:[], Class:'PrincipalInvestigator'}};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf('Investigator')>-1)pi.Roles.push(userHubFactory.roles[i]);
          }
        }
        userHubFactory.setModalData(pi);

        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/piModal.html',
          controller: modalCtrl
        });


        modalInstance.result.then(function (returnedPi) {
          if(pi.Key_id){
            console.log(returnedPi)
            angular.extend(pi, returnedPi);
          }else{
            userHubFactory.users.push(returnedPi);
          }
        });

    }

  $scope.departmentFilter = function() {
    if(!$scope.search)$scope.search = {};
    return function(user) {
        var show = false;
        //for pis that don't have departments, don't filter them unless the filter has some text
        if(!user.PrincipalInvestigator.Departments)user.PrincipalInvestigator.Departments = [];
        if(!user.PrincipalInvestigator.Departments.length){
          if(typeof $scope.search.selectedDepartment == 'undefined' || $scope.search.selectedDepartment.length == 0){
            show = true;
          }
        }

        angular.forEach(user.PrincipalInvestigator.Departments, function(department, key){
          if(typeof $scope.search.selectedDepartment == 'undefined'|| department.Name.toLowerCase().indexOf($scope.search.selectedDepartment.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.buildingFilter = function() {
    if(!$scope.search)$scope.search = {};

    return function(user) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!user.PrincipalInvestigator.Buildings)pi.Buildings = [];
        if(!user.PrincipalInvestigator.Buildings.length){
          if(typeof $scope.search.selectedBuilding == 'undefined' || $scope.search.selectedBuilding.length == 0){
            show = true;
          }
        }
        angular.forEach(user.PrincipalInvestigator.Buildings, function(building, key){
          if(typeof $scope.search.selectedBuilding == 'undefined' || building.Name.toLowerCase().indexOf($scope.search.selectedBuilding.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.handlePiActive = function(pi){
      $rootScope.error = '';
      pi.IsDirty = true;
      var piCopy = convenienceMethods.copyObject(pi);
      piCopy.Is_active = !pi.Is_active;
      userHubFactory.savePi(piCopy)
        .then(
          function(returnedPi){
            pi.Is_active = !pi.Is_active;
            pi.IsDirty = false;
          },
          function(){
            pi.IsDirty = false;
            $rootScope.error = 'The Principal Investigator could not be saved.  Please check your internet connection and try again.'
          }
        )
  }

  $scope.order = 'Last_name';

}

var labContactController = function($scope, $modal, $rootScope, userHubFactory, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $scope.order = 'Last_name';
    $rootScope.renderDone = false;

    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.LabContacts = userHubFactory.users;
          $rootScope.neededUsers = true;
            if($location.search().contactId && $location.search().piId && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserId($location.search().contactId), $location.search().piId);
            }
          $rootScope.renderDone = true;
        }
      )

    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf('Lab Contact')>-1)user.Roles.push(userHubFactory.roles[i]);
          }
        }
        if(!user.Supervisor_id){
          user.Supervisor_id = piId;
          user.Supervisor = userHubFactory.getUserByPIId($location.search().piId);
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/labContactModal.html',
          controller: modalCtrl
        });
        modalInstance.result.then(function (returnedUser) {
          if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var personnelController = function($scope, $modal, $rootScope, userHubFactory, convenienceMethods, $timeout, $location) {
    $rootScope.neededUsers = false;
    $rootScope.order="Last_name";
    $rootScope.error="";
    $rootScope.renderDone = false;

    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.Admins = userHubFactory.users;
          $rootScope.neededUsers = true;
          $timeout(function() {
                $rootScope.renderDone = true;
            }, 300);
        },
        function(){
          $rootScope.error="There was problem getting the lab contacts.  Please check your internet connection and try again.";
        }
      )


    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/personnelModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
         if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var labPersonnelController = function($scope, $modal, $rootScope, userHubFactory, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $scope.order = 'Last_name';
    $rootScope.renderDone = false;


    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.LabPersonnel = userHubFactory.users;
          $rootScope.neededUsers = true;
            if($location.search().personnelId && $location.search().piId && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserId($location.search().personnelId), $location.search().piId);
            }
          $rootScope.renderDone = true;
        }
      )

    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf('Lab Personnel')>-1)user.Roles.push(userHubFactory.roles[i]);
          }
        }
        if(!user.Supervisor_id){
          user.Supervisor_id = piId;
          user.Supervisor = userHubFactory.getUserByPIId($location.search().piId);
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/labPersonnelModal.html',
          controller: modalCtrl
        });
        modalInstance.result.then(function (returnedUser) {
          if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var uncatController = function($scope, $modal, $rootScope, userHubFactory, convenienceMethods) {
    $rootScope.order="Last_name";
    $rootScope.neededUsers = false;
    $rootScope.error="";

    var getUncategorizedUsers = function(){
      return userHubFactory.getUncategorizedUsers()
        .then(
          function(users){
            console.log(users);
            $scope.user = userHubFactory.users;
            $rootScope.neededUsers = true;
          }
        )
    }

    userHubFactory.getAllUsers()
      .then(function(users){
        $rootScope.neededUsers = true;
        $scope.users = users;
      });


    $scope.openModal = function(user,$index){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
        }
        userHubFactory.setModalData(user);
        user.Is_incategorized = true;
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/personnelModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
          console.log(returnedUser);
          angular.extend(user, returnedUser);
        });
    }
}
modalCtrl = function($scope, userHubFactory, $modalInstance, convenienceMethods, $q, $location){
    if($location.$$host.indexOf('graysail')<0){
      $scope.isProductionServer = true;
    }else{
      $scope.isProductionServer = false;
    }

    $scope.modalError="";
    //make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
    $scope.modalData = convenienceMethods.copyObject( userHubFactory.getModalData() );
    $scope.order="Last_name";
    $scope.phoneNumberPattern = /^\(?\d{3}\)?[- ]?\d{3}[- ]?\d{4}$/;
    $scope.phoneNumberErrorMsg = "E.G. 123-555-5555 or (123) 555-5555";
    $scope.emailPattern = /^[_a-z0-9]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i;
    $scope.emailErrorMsg = "Invalid email address";
    $scope.pis = userHubFactory.getPIs();

    userHubFactory.getAllRoles()
      .then(
        function(roles){
          $scope.roles = roles;
        }
      )
    userHubFactory.getAllDepartments()
      .then(
        function(departments){
          $scope.departments = departments;
            //if the user has a department, set the selected Department for ui-select elements to the matching index of $scope.departments
            if($scope.modalData.Primary_department){
                var i = $scope.departments.length;
                while(i--){
                    if($scope.departments[i].Key_id === $scope.modalData.Primary_department.Key_id){
                        $scope.departmentIdx = i;
                        break;
                    }
                }
            }
        }
      )

    //if the user has a supervisor, set the selected PI for ui-select elements to the matching index of $scope.pis
    if($scope.modalData.Supervisor_id){
        var i = $scope.pis.length;
        while(i--){
            if($scope.pis[i].PrincipalInvestigator.Key_id === $scope.modalData.Supervisor_id){
                $scope.piIndex = i;
                break;
            }
        }
    }





    $scope.cancel = function () {
        $modalInstance.dismiss();
    };

    $scope.savePi = function(){
      $scope.modalData.IsDirty=true;
      $scope.modalError=""
      console.log($scope.modalData)
      var userDto = $scope.modalData;

      saveUser( userDto )
        .then(saveRoles)
        .then(savePiDepartmentRelations)
        .then(closeModal)
    }

    $scope.onSelectRole = function(role, $model, $label, id){
      $scope.modalError=""
      //console.log('we are in the role branch');
      if(userHubFactory.getModalData().Class=="PrincipalInvestigator"){
          var user = $scope.modalData.User;
      }else{
          var user = $scope.modalData;
      }
      if(userHubFactory.getModalData().Key_id){
          userHubFactory.saveUserRoleRelation(user, role, true)
          .then(
              function(){
                user.Roles.push(role);
                //all lab contacts are also lab personnel.  Server side application logic automatically adds the role, but saveUserRoleRelation on the server only returns a boolean, so we add here as well
                if(role.Name == "Lab Contact"){
                    var i = userHubFactory.roles.length;
                    while(i--){
                        if(userHubFactory.roles[i].Name.indexOf('Lab Personnel')>-1)user.Roles.push(userHubFactory.roles[i]);
                    }
                }
                if(user.Is_incategorized){
                  userHubFactory.placeUser(user);
                }
                $model.IsDirty=false;
              },
              function(){
                $scope.modalError = 'The role could not be added.  Please check your internet connection and try again.'
              }
            )
       }

       //we don't have a user, because we are creating a new one.  cache the roles for save on callback when the user is saved.
       else{
          if($model)$model.IsDirty = false;
          if(!user.Roles)user.Roles = [];
          user.Roles.push(role);
       }
    }

    $scope.saveUser = function(){
      $scope.modalData.IsDirty = true;
      var user = $scope.modalData;
      $scope.modalError="";
      saveUser( user )
        .then(saveRoles)
        .then(closeModal)
    }

    $scope.onAddDepartmentToPi = function(department){
        console.log(department);
        $scope.modalError=""
        var deptToAdd = convenienceMethods.copyObject(department);
        $scope.modalData.PrincipalInvestigator.Departments.push(deptToAdd);
        if($scope.modalData.Key_id){
          deptToAdd.IsDirty=true;
          userHubFactory.savePIDepartmentRelation($scope.modalData.PrincipalInvestigator, deptToAdd, true)
            .then(
              function(){
                deptToAdd.IsDirty=false;
                userHubFactory.setModalData($scope.modalData.PrincipalInvestigator);
              },
              function(){
                deptToAdd.IsDirty=false;
                var i = $scope.modalData.PrincipalInvestigator.Departments;
                while(i--){
                  if($scope.modalData.PrincipalInvestigator.Departments[i].Key_id == deptToAdd.Key_id)$scope.modalData.PrincipalInvestigator.Departments(i,1);
                }
                $scope.modalError="The department could not be added to the Principal Investigator.  Please check your internet connection and try again."
              }
            )
        }
    }

    $scope.removeDepartment = function(department){
        $scope.modalError="";
        var pi = $scope.modalData;
        var i = pi.PrincipalInvestigator.Departments.length;
        console.log(pi);
        if(!pi.PrincipalInvestigator.Key_id){
          while(i--){
            if(pi.PrincipalInvestigator.Departments[i].Key_id == department.Key_id)pi.PrincipalInvestigator.Departments.splice(i,1);
          }
        }else{
          department.IsDirty = true;
          userHubFactory.savePIDepartmentRelation(pi.PrincipalInvestigator, department, false)
            .then(
              function(){
                  department.IsDirty = false;
                  while(i--){
                    if(pi.PrincipalInvestigator.Departments[i].Key_id == department.Key_id)pi.PrincipalInvestigator.Departments.splice(i,1);
                  }
              },
              function(){
                department.IsDirty = false;
                $scope.modalError = "The department could not be removed.  Please check your internet connection and try again.";
              }
            )
        }
    }

    $scope.removeRole = function(user, role){
        $scope.modalError="";
        var i = user.Roles.length;
        if(!user.Key_id){
          while(i--){
            if(user.Roles[i].Key_id == role.Key_id)user.Roles.splice(i,1);
          }
        }else{
          role.IsDirty = true;
          userHubFactory.saveUserRoleRelation(user, role, false)
            .then(
              function(){
                  role.IsDirty = false;
                  while(i--){
                    if(user.Roles[i].Key_id == role.Key_id)user.Roles.splice(i,1);
                  }
              },
              function(){
                role.IsDirty = false;
                $scope.modalError = "The role could not be removed.  Please check your internet connection and try again.";
              }
            )
        }
    }

    $scope.isPIRequired = function(user) {
        for (var i = 0; i < user.Roles.length; i++) {
            if (user.Roles[i].Name == "Lab Contact" || user.Roles[i].Name == "Lab Personnel") {
                return true;
            }
        }
        return false;
    }

    $scope.onSelectPI = function(pi,user){
      $scope.modalData.Supervisor = pi;
      $scope.modalData.Supervisor_id = pi.PrincipalInvestigator.Key_id;
    }

    $scope.onSelectDepartment = function(dept,user){
      $scope.modalData.Primary_department_id = dept.Key_id;
      $scope.modalData.Primary_department = dept;
    }
    $scope.getAuthUser = function(user){
     $scope.lookingForUser = true;
     $scope.modalError = false;
     var i = userHubFactory.users.length;
      while(i--){
        if( userHubFactory.users[i].Username && $scope.modalData.userNameForQuery.toLowerCase() == userHubFactory.users[i].Username.toLowerCase()){
          $scope.modalError='The username '+$scope.modalData.userNameForQuery+' is already taken by another user in the system.';
          return;
        }
      }
      userHubFactory.lookUpUser($scope.modalData.userNameForQuery)
        .then(
          function(returnedUser){
            if(returnedUser==null){
               $scope.modalError='No user with that username was found.';
               $scope.lookingForUser = false;
               return;
            }
            $scope.lookingForUser = false;
            if($scope.modalData.Class=="PrincipalInvestigator"){
              $scope.modalData.User = returnedUser;
              $scope.modalData.User.Roles = user.Roles;
              console.log(returnedUser);
            }else{
              $scope.modalData=returnedUser;
              $scope.modalData.Roles = user.Roles;
              if(user.PrincipalInvestigator)$scope.modalData.PrincipalInvestigator = user.PrincipalInvestigator;
              if(user.Inspector)$scope.modalData.Inspector = user.Inspector;
            }
          },
          function(){
            $scope.lookingForUser = false;
            $scope.modalError='There was a problem querying for the user.  Please check your internet connection and try again.';
          }
        )

    }

    function saveUser( userDto )
    {
        return userHubFactory.saveUser( userDto )
          .then(
            function( returnedUser ){
              console.log(returnedUser);
              returnedUser.Roles = userDto.Roles;
              if(userDto.PrincipalInvestigator && returnedUser.PrincipalInvestigator){
                returnedUser.PrincipalInvestigator.Departments = userDto.PrincipalInvestigator.Departments;
                returnedUser.PrincipalInvestigator.Rooms = userDto.PrincipalInvestigator.Rooms;
              }
              return returnedUser;
            },
            function(){
              $scope.modalError="The user could not be saved.  Please check your internet connection and try again."
            }
          )
    }

    function getPiByUser( user ){
        console.log('getting pi')
        var defer = $q.defer();
        if($scope.modalData.Class=="PrincipalInvestigator"){
          if($scope.modalData.Is_new){
            return userHubFactory.getPIByUserId(user.Key_id)
              .then(
                function(returnedPi){
                 return returnedPi;
                },
                function(){
                  $scope.modalError = "The system couldn't get the Principal Investigator record.  Please check your internet connection and try again.";
                }
              )
          }else{
            $scope.modalData.User = user;
            defer.resolve( $scope.modalData );
            return defer.promise
          }
        }
        else{
          $defer.resolve(user);
          return defer.promise;
        }
    }

    function saveRoles( user ){
      console.log(user);
      var userCopy, oldRoles
      var oldRoleIds = [];
      var idsToAdd = [];

      userCopy = userHubFactory.getModalData();
      oldRoles = $scope.modalData.Roles;


      if(!userHubFactory.getModalData().Is_new){
        //get the ids of the roles the user already had, if the user is not new
        var i = oldRoles.length;
        while(i--){
          oldRoleIds.push(oldRoles[i].Key_id);
        }

      }
      //get the ids of the roles to add
      var j = user.Roles.length;
      while(j--){
        if(oldRoleIds.indexOf(user.Roles[j].Key_id)<0)idsToAdd.push(user.Roles[j].Key_id);
      }
      console.log(idsToAdd);

      if(!idsToAdd.length){
        var defer = $q.defer();
        defer.resolve(user);
        return defer.promise;
      }

      return userHubFactory.saveUserRoleRelations(user.Key_id, idsToAdd)
        .then(
          function(){
            console.log(user);
            return user;
          },
          function(){
            $scope.modalError = 'The user was saved, but there was a problem adding one or more of the roles.  Please check your internet connection and try again.'
          }
        )


    }

    function savePiDepartmentRelations(pi){
      console.log('saving dept relations')
      //save deparments added to pi
      var oldDepartments = [];
      var newDepartmentIds = [];
      var piCopy = convenienceMethods.copyObject(pi);
      console.log(pi)
      var i = piCopy.PrincipalInvestigator.Departments.length;
      while(i--){
        oldDepartments.push(piCopy.PrincipalInvestigator.Departments[i].Key_id);
      }

      var j = pi.PrincipalInvestigator.Departments.length;
      while(j--){
        if(oldDepartments.indexOf(pi.PrincipalInvestigator.Departments[j].Key_id)<0)newDepartmentIds.push(pi.PrincipalInvestigator.Departments[j].Key_id)
      }
      console.log(newDepartmentIds);
      if(!newDepartmentIds.length){
        var defer = $q.defer();
        defer.resolve(pi);
        return defer.promise;
      }else{
        return userHubFactory.savePIDepartmentRelations(pi.PrincipalInvestigator.Key_id, newDepartmentIds)
          .then(
            function(){
              return pi;
            },
            function(){
              $scope.modalError = 'The PI was saved, but there was a problem adding one or more of the departments.  Please check your internet connection and try again.'
            }
          )
      }
    }

    function closeModal( dataToReturn ){
        console.log(dataToReturn);
        $scope.modalData.IsDirty = false;
        $modalInstance.close(dataToReturn);
    }

}
