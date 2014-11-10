var piHub = angular.module('piHub', ['ui.bootstrap','convenienceMethodModule'])

.config(function($routeProvider){
	$routeProvider
		.when('/rooms', 
			{
				templateUrl: 'piHubPartials/rooms.html', 
				controller: piHubRoomController
			}
		)
		.when('/personnel', 
			{
				templateUrl: 'piHubPartials/personnel.html', 
				controller: piHubPersonnelController
			}
		)		
		.when('/departments', 
			{
				templateUrl: 'piHubPartials/departments.html', 
				controller: piHubDepartmentsController
			}
		)
		.otherwise(
			{
				redirectTo: '/rooms'
			}
		);
})

.factory('piHubFactory', function(convenienceMethods,$q){
	var factory = {};
	factory.setPI = function(pi){
		this.pi = pi;
	}
	factory.getPI = function(){
		return this.pi;
	}
	factory.setUser = function(user){
		this.user = user;
	}
	factory.getUser = function(){
		return this.user;
	}

	factory.createRoom = function(roomDto){
		var url = "../../ajaxaction.php?action=saveRoom";
		var deferred = $q.defer();
		convenienceMethods.saveDataAndDefer(url, roomDto).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}

	factory.addRoom = function(roomDto){
		var url = "../../ajaxaction.php?action=savePIRoomRelation";
		var deferred = $q.defer();
		convenienceMethods.saveDataAndDefer(url, roomDto).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}

	return factory;
});

piHubMainController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory){
	$scope.doneLoading = false;

	$scope.setRoute = function(route){
    	$location.path(route);
  	}

	init();

	$scope.order='Last_name';

	function init(){
        if($location.search().hasOwnProperty('pi')){
        	 //getPI if there is a "pi" index in the GET
        	 getPi($location.search().pi);
        }else{
        	$scope.noPiSet = true;
        }



        if($location.search().hasOwnProperty('inspection')){
        	$scope.inspectionId = $location.search().inspection;
        }

        console.log($location.search());

        //always get a list of all PIs so that a user can change the PI in scope
        var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
       	convenienceMethods.getData( url, onGetAllPIs, onFailGetAllPIs );

		var url = '../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetBuildings, onFailGetBuildings );	
	}

	function onGetBuildings(data){
		$scope.buildings = data;
		$rootScope.buildings = data;
	}

	function onFailGetBuildings(){
		alert('There was a problem when the system tried to get the list of buildings.')
	}
        

	function getPi(PIKeyID){
		$scope.PI = false;
		var url = '../../ajaxaction.php?action=getPIById&id='+PIKeyID+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetPI, onFailGetPI );
		$scope.noPiSet = false;
	}

	function onGetPI(data){
		console.log(data);
		$scope.PI = data;
		piHubFactory.setPI($scope.PI);
		$scope.doneLoading = data.doneLoading;
		$location.search("pi", $scope.PI.Key_id);
	}

	function onFailGetPI(){
		alert('The system couldn\'t find the Principal Investigator');
	}

	function onGetAllPIs(data){
		$scope.PIs = data;
		$scope.doneLoadingAll = data.doneLoading;
	} 

	function onFailGetAllPIs(){
		alert('Something went wrong getting the list of all Principal Investigators');
	}

	//callback function called when a PI is selected in the typeahead
	$scope.onSelectPi = function($item, $model, $label){
		getPi($item.Key_id);
	}

	$scope.removeRoom = function(room){
		room.IsDirty = true;
		roomDto = {
		  Class: "RelationshipDto",
	      relation_id: room.Key_id,
	      master_id: $scope.PI.Key_id,
	      add: false
	    }

	    convenienceMethods.updateObject( roomDto, room, onRemoveRoom, onFailRemoveRoom, '../../ajaxaction.php?action=savePIRoomRelation' );
	}

	function onRemoveRoom(returned, room){
		room.IsDirty = false;
		var idx = convenienceMethods.arrayContainsObject($scope.PI.Rooms, room, null, true);
		console.log(idx);
		console.log($scope.PI.Rooms[idx]);
		$scope.PI.Rooms.splice(idx,1);
	}

	function onFailRemoveRoom(){
		alert("There was a problem when the system attempted to remove the room.");
	}

	$scope.modalify = function(pi,adding){

	  	var modalInstance = $modal.open({
	      templateUrl: 'roomHandlerModal.html',
	      controller: ModalInstanceCtrl,
	      resolve: {
	        PI: function () {
	          return $scope.PI;
	        },
	        adding: function (){
	        	if(adding)return adding;
	        }
	      }
	    });

	    modalInstance.result.then(function (PI) {
	     	$scope.PI = {};
	     	$scope.PI = PI;
	    }, function () {

	      //$log.info('Modal dismissed at: ' + new Date());
	    });

	}

	$scope.showHazards = function(room){
		console.log(room);
		
	  	var modalInstance = $modal.open({
	      templateUrl: 'roomHazardsModal.html',
	      controller: hazardDisplayModalInstanceController,
	      resolve: {
	        room: function () {
	          return room;
	        },

	      }
	    });

	    modalInstance.result.then(function (hazards) {
	    	console.log(hazards);
	    }, function () {

	      //$log.info('Modal dismissed at: ' + new Date());
	    });
	}

  };

var ModalInstanceCtrl = function ($scope, $rootScope, $modalInstance, PI, adding, convenienceMethods, piHubFactory, $q) {
	$scope.PI = PI;
	console.log(adding);

	if(adding)$scope.addRoom = true;
	if($rootScope.buildings)$scope.buildings = $rootScope.buildings;
	if(!$scope.buildings){
		var url = '../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetBuildings, onFailGetBuildings );
	}


	function onGetBuildings(data){
		$scope.buildings = data;
		//loop through pi rooms, disable rooms pi has already
	}

	function onFailGetBuildings(){
		alert('There was a problem when the system tried to get the list of buildings.')
	}

	$scope.onSelectBuilding = function(item){
		$scope.chosenBuilding = angular.copy(item);
		checkRooms($scope.chosenBuilding, $scope.PI);
	}	

	function checkRooms(building, pi){
		angular.forEach(building.Rooms, function(room, key){
			if(convenienceMethods.arrayContainsObject(pi.Rooms,room))room.piHasRel = true;
		});
	}

	$scope.handleRoomChecked = function(room,building){
		room.IsDirty = true;
		var roomCopy = angular.copy(room);
		var add = false;
		if(room.piHasRel) var add = true;

		roomDto = {
		  Class: "RelationshipDto",
	      relation_id: room.Key_id,
	      master_id: $scope.PI.Key_id,
	      add: add
	    }

	    //room.piHasRel = !room.piHasRel;
	    
	    piHubFactory.addRoom(roomDto).then(
	    	function(promise){
	    		console.log(room);
	    		room.Building = {};
				room.Building.Name = building.Name;
				//room.piHasRel = !room.piHasRel;
				console.log(roomDto);
				if(room.piHasRel){
					$scope.PI.Rooms.push(room);
				}else{

					var idx = convenienceMethods.arrayContainsObject($scope.PI.Rooms, room, null, true);
					console.log(idx);
					console.log($scope.PI.Rooms[idx]);
					$scope.PI.Rooms.splice(idx,1);
				}
				console.log($scope.PI);

				room.IsDirty = false;
	    	},
	    	function(){
	    		$scope.error = "The room could not be added to the PI.  Please check your internet connection and try again."
	    	}
	    )

	    

	}

	function onSaveRoomRelation(data,room,building){
		console.log(data);
		console.log(room);
		

		/*
		angular.forEach(building.Rooms, function(room, key){
			if(convenienceMethods.arrayContainsObject(pi.Rooms,room))room.piHasRel = true;
		});
*/
	}

	function onFailSaveRoomRelation(){

	}

	$scope.addRoomToBuidling = function(newRoom){
		newRoom.IsDirty = true;
		roomDto = {
		  Class: "Room",
	      Building_id: $scope.chosenBuilding.Key_id,
	      Name: newRoom.Name,
	      Is_active:true
	    }

	    console.log(roomDto);
	    var createDefer = $q.defer();
		piHubFactory.createRoom(roomDto).then(
	    	function(room){
    			room.IsDirty = false;
				$scope.chosenBuilding.Rooms.push(room);
				newRoom.IsDirty = false;
				createDefer.resolve(room);
				return createDefer.promise;
	    	},
	    	function(){
	    		newRoom.IsDirty = false;
	    		$scope.error="The room could not be created.  Please check your internet connection.";
	    		createDefer.reject();
	    		return createDefer.promise;
	    	}
	    ).then(
	    	function(room){
	    		console.log(room);
	    		room.piHasRel = true;

	    		//add room to pi
	    		$scope.handleRoomChecked(room,$scope.chosenBuilding);
	    	}
	    )
	}

	function onSaveRoom(data, room){

	}

	function onFailSaveRoom(){
		alert("Something went wrong when the system tried to create the new room.");
	}

	$scope.close = function(){
		$modalInstance.close($scope.PI);
	}

}

piHubRoomController = function($scope, $location, convenienceMethods){
	
	init();
	function init(){
		//var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
		//convenienceMethods.getData( url, onGetDepartemnts, onFailGetDepartments );
	}

}

piHubPersonnelController = function($scope, $location, convenienceMethods, $modal, piHubFactory){
	init();
	function init(){
		var url = '../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetUsers, onFailGetUsers );

	}

	function onGetUsers(data){
		$scope.users = data;

	}

	function onFailGetUsers(){
		alert("Something went wrong when the system tried to get the list of users.");
	}

	$scope.editUser = function(i){
		var modalInstance = $modal.open({
	      templateUrl: 'personnelModal.html',
	      controller: personnelModalController,
	      resolve: {
	        items: function () {
	          return i;
	        }
	      }
	    });

		modalInstance.result.then(function (i) {
		   console.log(piHubFactory.getUser());
	       $scope.PI.LabPersonnel[i] = angular.copy(piHubFactory.getUser());
	       piHubFactory.setPI($scope.PI);
	    });

	}

	$scope.onSelectUser = function(user){
		$scope.selectedUser.IsDirty = true;
		userCopy = angular.copy(user);
		userCopy.Supervisor_id = $scope.PI.Key_id;

		convenienceMethods.updateObject( userCopy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );

	}

	function onSaveUser(data, user){
		$scope.selectedUser.IsDirty = false;
		if(!convenienceMethods.arrayContainsObject($scope.PI.LabPersonnel, data)){
			$scope.PI.LabPersonnel.push(data);
		}
		
	}

	function onFailSaveUser(){
		alert('There was a problem trying to save the user.')
	}

	$scope.deactivateUser = function(user){

		piHubFactory.setUser(user);
		var functionType = 'inactivate';
		var modalInstance = $modal.open({
	      templateUrl: 'confirmationModal.html',
	      controller: confirmationController,
	      resolve: {
	        items: function () {
	          return functionType;
	        }
	      }
	    });

		modalInstance.result.then(function (user) {
	       onRemoveUser(user);
	    });

	}

	$scope.confirmRemoveUser = function(user){

		piHubFactory.setUser(user);
		var functionType = 'remove';
		var modalInstance = $modal.open({
	      templateUrl: 'confirmationModal.html',
	      controller: confirmationController,
	      resolve: {
	        items: function () {
	          return functionType;
	        }
	      }
	    });

		modalInstance.result.then(function (user) {
	       onRemoveUser(user);
	    });

	}

	$scope.removeUser = function(user){
		user.IsDirty = true;
		userCopy = angular.copy(user);
		userCopy.Supervisor_id = null;

		convenienceMethods.updateObject( userCopy, user, onRemoveUser, onFailRemoveUser, '../../ajaxaction.php?action=saveUser' );

	}

	function onRemoveUser(user){
		user.IsDirty = false;
		var idx = convenienceMethods.arrayContainsObject($scope.PI.LabPersonnel, user, null,true);
		console.log(idx);
	    if(idx>-1)$scope.PI.LabPersonnel.splice(idx,1);
	}

	function onFailRemoveUser(){
		alert('There was a problem trying to save the user.');
	}

}

confirmationController = function(items, $scope, piHubFactory, $modalInstance, convenienceMethods){
	$scope.userCopy = piHubFactory.getUser();
	var functionType = items;
	if(functionType.toLowerCase() == 'inactivate'){
		$scope.message =  "Do you want to remove "+$scope.userCopy.Name+" from the PI's lab personnel list?";
	}else{
		$scope.message =  'Do you want to inactivate  '+$scope.userCopy.Name+' everywhere in the Research Safety Management System user list?';
	}

	$scope.confirm = function(){
		$scope.userCopy.IsDirty = true;
		//are we deactivating this user?  Set the user's Is_active property to false, if so.
		if(functionType.toLowerCase() == 'remove')$scope.userCopy.Is_active = false;

		//get rid of the user's PI relationship.
		$scope.userCopy.Supervisor_id = null;

		//save the user
		convenienceMethods.updateObject( $scope.userCopy, null, onConfirmRemoveUser, onFailRemoveUser, '../../ajaxaction.php?action=saveUser' );
	}

	//save call succeeded.  go back to the normal view
	function onConfirmRemoveUser(user){
		$scope.userCopy.IsDirty = false;
		$modalInstance.close(user);
	}

	function onFailRemoveUser(){
		$scope.userCopy.IsDirty = false;
		$scope.error='There was a problem when the system tried to remove the user.  Please check your internet connection and try again.';
	}

	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	}

}

piHubDepartmentsController = function($scope, $location, convenienceMethods,$modal){
	init();
	function init(){
		$scope.doneLoadingDepartments = false;
		var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetDepartemnts, onFailGetDepartments );
	}

	function onGetDepartemnts(data){
		$scope.departments = data;
		$scope.doneLoadingDepartments = true;
	}

	function onFailGetDepartments(){
		alert('There was a problem getting the list of departments');
	}

	$scope.onSelectDepartment = function($item, $model, $label){
		$scope.selectedDepartment.IsDirty = true;

		piDTO = {
		  Class: "RelationshipDto",
	      relation_id: $item.Key_id,
	      master_id: $scope.PI.Key_id,
	      add: true
	    }

		convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation',null, $item );
	}

	
	function onAddDepartment(returned,dept){
		$scope.selectedDepartment.IsDirty = false;
		if(!convenienceMethods.arrayContainsObject($scope.PI.Departments,dept))$scope.PI.Departments.push(dept);
	}

	function onFailAddDepartment(){

	}

	$scope.removeDepartment = function(department){
		department.IsDirty = true;

		piDTO = {
		  Class: "RelationshipDto",
	      relation_id: department.Key_id,
	      master_id: $scope.PI.Key_id,
	      add: false
	    }

		convenienceMethods.updateObject( piDTO, department, onRemoveDepartment, onFailRemoveDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation',null, department );
	}

	function onRemoveDepartment(returned,dept){
		console.log(dept);
		dept.IsDirty = false;
		var idx = convenienceMethods.arrayContainsObject($scope.PI.Departments, dept,true)
		if(idx>-1)$scope.PI.Departments.splice(idx,1);
	}

	function onFailRemoveDepartment(){

	}


  }

  personnelModalController = function($scope, $modalInstance, convenienceMethods, piHubFactory, items){
  	var pi = piHubFactory.getPI();
  	$scope.userCopy = angular.copy(pi.LabPersonnel[items]);
  	piHubFactory.setUser($scope.userCopy);
  	$scope.userCopy.Supervisor = pi;

	$scope.saveUser = function(){
		$scope.userCopy.IsDirty = true;
		//save the user
		convenienceMethods.updateObject( $scope.userCopy, null, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );
	}

	//save call succeeded.  go back to the normal view
	function onSaveUser(user){
		$scope.userCopy.IsDirty = false;
		$scope.userCopy = angular.copy(user);
		console.log($scope.userCopy);
		piHubFactory.setUser($scope.userCopy);
		$modalInstance.close(items);
	}

	function onFailSaveUser(){
		$scope.userCopy.IsDirty = false;
		$scope.error='There was a problem when the system tried to save the user.  Please check your internet connection and try again.';
	}

	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	}

  }
  
  hazardDisplayModalInstanceController = function( $scope, $modalInstance, room, convenienceMethods ){
  	
  	$scope.room = room;
    //the server expects an array of roomIds, but we are only going to send one, so wrap it in an array;
    var rooms = [room.Key_id];
  	var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetHazards, onFailGetHazards );

    function onGetHazards(data){
    	console.log(data);
    	$scope.hazards = data.ActiveSubHazards;
    }

    function onFailGetHazards(){
    	$scope.hazards = false;
    	$scope.noHazards = "No hazards have been selected for this room."
   	}

   	$scope.close = function(){
   		$modalInstance.close($scope.hazards);
   	}
}