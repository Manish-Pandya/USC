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
  factory.roles = [];
  factory.departments = [];
  factory.pis = [];
  factory.users = [];
  factory.labContacts = [];
  factory.personnel = [];
  factory.modalData = {};

  factory.setPIs = function(pis)
  {
    this.pis = pis;
  }

  factory.getPIs = function(){
    return this.pis;
  }

  factory.getAllPis = function()
  {
    
    //if we don't have a the list of pis, get it from the server
    var deferred = $q.defer();

    //lazy load
    if(factory.pis.length){
      alert('lazy pis');
      deferred.resolve(factory.pis);
      return deferred.promise;
    }

    var url = '../../ajaxaction.php?action=getPisForUserHub&callback=JSON_CALLBACK';
      convenienceMethods.getDataAsDeferredPromise(url).then(
      function(pis){
        factory.pis = pis;
        deferred.resolve(pis);
      },
      function(promise){
        deferred.reject();
      }
    );
    return deferred.promise;
  }

  factory.getAllUsers = function()
  {
    var deferred = $q.defer();

      //lazy load
      if(factory.users.length){
        deferred.resolve(factory.users);
        return deferred.promise;
      }

      var url = '../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK';
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


  factory.getLabContacts = function()
  {
    this.labContacts = [];
    var i = this.users.length;
    while(i--){
      var user = factory.users[i];
      if(factory.hasRole(user, 'Lab Contact')){
        console.log(factory.pis);
        factory.getRelation(user, 'Supervisor', 'Supervisor_id', factory.pis );
        factory.labContacts.push(user);
      }
    }

    return this.labContacts;
  }

  factory.getPersonnel = function()
  {
    this.personnel = [];
    var i = this.users.length;
    while(i--){
      var user = factory.users[i];
      if(factory.hasRole(user, 'admin') || factory.hasRole(user, 'inspector') || factory.hasRole(user, 'radiation'))factory.personnel.push(user);
    }

    return this.personnel;
  }

  factory.hasRole = function(user, role)
  {
    var j = user.Roles.length;
    while(j--){
      var userRole = user.Roles[j];
      console.log(role);
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

  factory.getBuildingsByPi = function(pi)
  {
      pi.Buildings = [];
      var i = pi.Rooms.length;
      var buildingIds = [];

      while(i--){
          var room = pi.Rooms[i];
          console.log(buildingIds);
          if( room && buildingIds.indexOf( room.Building.Key_id ) < 0 ){
            buildingIds.push(room.Building.Key_id);
            pi.Buildings.push(room.Building);
          }
      }
  }

  factory.saveUser = function(userDto)
  {
    var url = "../../ajaxaction.php?action=saveUser";
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
    var url = "../../ajaxaction.php?action=savePI";
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
        alert('adf')
        deferred.resolve(factory.roles);
        return deferred.promise;
      }

      var url = '../../ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK';
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

      var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
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

  return factory

});

var MainUserListController = function(userHubFactory, $scope, $rootScope, $location, convenienceMethods, $route) {


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


}

var piController = function($scope, $modal, userHubFactory, $rootScope, convenienceMethods) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    var getAllPis = function(users){
      userHubFactory.getAllPis()
        .then(
          function(pis){
            return pis
          },
          function(){
            $rootScope.error="There was a problem getting the list of Principal Investigators.  Please check your internet connection and try again."
          }
        )
        .then(
          function(pis){
            var i = pis.length;
            while(i--){
              var pi = pis[i];
              userHubFactory.getRelation(pi, 'User', 'User_id', userHubFactory.users);
              userHubFactory.getBuildingsByPi(pi);
            }
            $scope.pis = pis;
            $rootScope.neededUsers = true;
          }
        )
    }

    userHubFactory.getAllUsers()
      .then(getAllPis);

    $scope.openModal = function(pi){
        if(!pi)pi = {Is_active: true, Is_new:true, Class:'PrincipalInvestigator', User:{}, Departments:[]};
        userHubFactory.setModalData(pi);

        var modalInstance = $modal.open({
          templateUrl: 'piModal.html',
          controller: modalCtrl
        });


        modalInstance.result.then(function (returnedPi) {
          if(returnedPi.Is_new){
            userHubFactory.users.push(returnedPi.User);
            userHubFactory.pis.push(returnedPi);
          }else{  
            console.log( returnedPi.User.First_name );
            angular.extend(pi, returnedPi);
                     
          }
        });

    }

}

var labContactController = function($scope, $modal, $rootScope, userHubFactory) {
    $rootScope.neededUsers = false;
    $rootScope.error="";

    var getAllPis = function(users){
      return userHubFactory.getAllPis()
        .then(
          function(pis){
            var i = pis.length;
            while(i--){
              var pi = pis[i];
              userHubFactory.getRelation(pi, 'User', 'User_id', userHubFactory.users);
            }
          },
          function(){
            $rootScope.error="There was a problem getting the list of Principal Investigators.  Please check your internet connection and try again."
          }
        )
    }

    var getLabContacts = function()
    {
      if(userHubFactory.users.length){
        $scope.LabContacts = userHubFactory.getLabContacts();
        $rootScope.neededUsers = true;
      }else{
        $rootScope.error="There was problem getting the lab contacts.  Please check your internet connection and try again.";
      }
    }

    userHubFactory.getAllUsers()
      .then(getAllPis)
      .then(getLabContacts)
}

var personnelController = function($scope, $modal, $rootScope, userHubFactory, convenienceMethods) {
  $rootScope.neededUsers = false;
    $rootScope.error="";
    var getPersonnel = function(users)
    { 
      if(userHubFactory.users.length){
        $scope.Admins = userHubFactory.getPersonnel();
        $rootScope.neededUsers = true;
      }else{
        $rootScope.error="There was problem getting the lab contacts.  Please check your internet connection and try again.";
      }
    }

    userHubFactory.getAllUsers()
      .then(getPersonnel);
}

modalCtrl = function($scope, userHubFactory, $modalInstance, convenienceMethods, $q){

    //make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
    $scope.modalData = convenienceMethods.copyObject( userHubFactory.getModalData() );
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
        }
      )

    $scope.cancel = function () {
        $modalInstance.dismiss();
    };


    $scope.onSelectBuilding = function(building){
        $scope.modalData.Building_id = building.Key_id;
    }

    $scope.savePi = function(){

      var pi = userHubFactory.getModalData();
      console.log($scope.modalData)
      var userDto = $scope.modalData.User;

      saveUser( userDto )
        .then(saveNewPi)
        .then(closeModal)
    }

    $scope.saveUser = function(user){

    }

    $scope.saveInspector = function(){

    }

    function saveUser( userDto )
    {
        return userHubFactory.saveUser( userDto )
          .then(
            function( returnedUser ){
              return returnedUser;
            },
            function(){
              $rootScope.error="The user could not be saved.  Please check your internet connection and try again."
            }
          )
    }

    function saveNewPi( user ){
        if($scope.modalData.Class=="PrincipalInvestigator" && $scope.modalData.Is_new){
          return userHubFactory.savePI($scope.modalData)
            .then(
              function(returnedPi){
                $scope.modalData.User = user;
                return $scope.modalData;
              },
              function(){
                $rootScope.error = "The new Principal Investigator record for this user could not be saved.  Please check your internet connection and try again."
              }
            )
        }else{
          var defer = $q.defer();
          $scope.modalData.User = user;
          defer.resolve( $scope.modalData );
          return defer.promise
        }
    }

    function closeModal( dataToReturn ){
        $modalInstance.close(dataToReturn);
    }

}