var userList = angular.module('userList', ['ui.bootstrap']);

userList.factory('testFactory', function($http){
	
	//initialize a factory object
	var tempFactory = {};
	
	//simple 'getter' to grab data from service layer
	tempFactory.getUsers = function(onSuccess, url){
		console.log(onSuccess);
	
	//user jsonp method of the angularjs $http object to request data from service layer
	$http.jsonp(url)
		.success( function(data) {	
		   console.log(url);
		   console.log('success');
		   console.log(data);
		   //onSuccess is the method we have passed from the controller.  Binds data from service layer to angularjs $scope object
	       onSuccess(data);
	    })
	    .error(function(data, status, headers, config){
            alert('error');
            console.log(headers());
            console.log(status);
            console.log(config);
        });
        
	};
	tempFactory.saveUser = function(onSuccess, url){
		alert('saving user');
	}
	
	return tempFactory;
});

//called on page load, gets initial user data to list users
function UserListController($scope, testFactory) {
  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
	  testFactory.getUsers(onGetUsers,'http://hazmars.graysail.com/api.php?callback=JSON_CALLBACK');
	  console.log('init called');
  };
  //grab set user list data into the $scrope object
  function onGetUsers(data) {
	  $scope.users = data;
	  console.log($scope.users);
  }


  $scope.editUser = function(user){

  	angular.forEach($scope.users, function(thisUser, key){
  		console.log(user);
	  	thisUser.edit = false;
	  	thisUser.notEdit = true;
	});
	
	user.notEdit = false;
  	user.edit = true;
  	var editedUser = angular.copy(user);

  	console.log(editedUser);

  	$scope.userCopy = editedUser;

  }
  
  /*
   * USER SAVE METHODS
   * used for creating and updating users
   * 
   */
  $scope.saveUser = function(user){

  	$scope.userCopy.edit = false;

  	for (var property in scope.userCopy) {
	    if (scope.userCopy.hasOwnProperty(property)) {
	        // do stuff
	    }
	}



  	user.name = $scope.userCopy.name;
  	user.name = $scope.userCopy.name;
  	user.name = $scope.userCopy.name;
  	user.name = $scope.userCopy.name;
  	user.name = $scope.userCopy.name;
  	user.edit = $scope.userCopy.edit;
  	console.log(user);

	//testFactory.saveUser(onGetUsers,'http://hazmars.graysail.com/api.php?callback=JSON_CALLBACK');
  }
  
  function onSaveUser(){
	  
  }
};

//set controller
userList.controller( 'UserListController', UserListController);