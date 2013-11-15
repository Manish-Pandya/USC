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
	tempFactory.saveUser = function(url, user, onSuccess){
		$http.post(url, user)
		.success(function(data, status, headers, config) {
		    onSuccess(data);
		})
		.error(function(data, status, headers, config) {
			console.log(headers());
            console.log(status);
            console.log(config);
		});

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
	  testFactory.getUsers(onGetUsers,'/Erasmus/src/ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK');
	  console.log('init called');
  };
  //grab set user list data into the $scrope object
  function onGetUsers(data) {
	  $scope.users = data;
	  console.log($scope.users);
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

  	//set the IsActive state for the user in the view model
	if(user.IsActive == false || !user.IsActive){
  		user.IsActive = true;
  	}else{
  		user.IsActive = false;
  	}


  	//callback function to make sure that the user state matches the the state of the corresponding object on the server
  	var switchActiveState = function(data,user){
	 	user = data;
  	}

  	//send the edit to the server, pass it to the callback
  	testFactory.saveUser('/Erasmus/src/ajaxaction.php?action=saveUser', user, switchActiveState);

  }
  
  /*
   * USER SAVE METHODS
   * used for creating and updating users
   * 
   */
  $scope.saveUser = function(copy, user){

	//callback function.  recieves new user data and updates the edited user view model object with it
	var updateUserView = function(data){
		console.log(user);
	  	console.log($scope.userCopy);
	    $scope.userCopy = data;
  
	  	angular.forEach($scope.users, function(thisUser, key){
		  	thisUser.notEdit = false;
		});


	  	//set each property of the edited user to the corresponding property of the user object on the server
  	   for (var property in $scope.userCopy) {
			if (data.hasOwnProperty(property)) {
			    user[property] = $scope.userCopy[property];
			}
	   }

	   	user.edit = false;
		user.updated = true;
	}

	testFactory.saveUser('/Erasmus/src/ajaxaction.php?action=saveUser', $scope.userCopy, updateUserView);

  }

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
	}
  }
  
};

//set controller
userList.controller( 'UserListController', UserListController);