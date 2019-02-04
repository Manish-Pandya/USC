var piHub = angular.module('piHub', ['ui.bootstrap', 'convenienceMethodWithRoleBasedModule', 'userList', 'cgBusy'])

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
.filter("noSupervisor", function (userHubFactory) {
    return function (users) {
        if (!users || !users.length) return;
        var l = users.length;
        var matchedUsers = [];
        for (var i = 0; i < l; i++) {
            var u = users[i];
            if (!u.Supervisor_id && (userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_PERSONNEL))) {
                matchedUsers.push(u);
            }
        }
        return matchedUsers;
    }
})
.factory('piHubFactory', function(convenienceMethods,$q, userHubFactory){
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

    factory.getAllUsers = function () {
        var deferred = $q.defer();
        if (!factory.users) {

            var url = '../../ajaxaction.php?action=getUsersForPIHub&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsPromise( url )
            .then(
                function(resp){
                    factory.users = resp.data;
                    deferred.resolve(resp.data);
                },
                function(err){
                    deferred.reject();
                }
            );
        } else {
            deferred.resolve(factory.users);
        }
        return deferred.promise
    }

    //factory

    return factory;
});

piHubMainController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory, userHubFactory){
    $rootScope.webRoot = GLOBAL_WEB_ROOT;
    $scope.doneLoading = false;

    $scope.setRoute = function(route){
        $location.path(route);
    }

    init();

    $scope.order='Last_name';

    $scope.getRoomUrlString = function (room) {
        roomIds = [room.Key_id];        
        room.roomUrlParam = $.param({ "room": roomIds });
        return room;
    }

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
        var url = '../../ajaxaction.php?action=getAllPINames&callback=JSON_CALLBACK';
           convenienceMethods.getData( url, onGetAllPIs, onFailGetAllPIs );

        var url = '../../ajaxaction.php?action=getAllBuildingNames&callback=JSON_CALLBACK';
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
        $scope.noPiSet = false;
        $scope.PI = false;
        var url = '../../ajaxaction.php?action=getPIById&id='+PIKeyID+'&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url)
            .then(getRoomsByPi);
    }

    function getRoomsByPi(pi){
        var url = '../../ajaxaction.php?action=getRoomsByPIId&piId='+pi.Key_id+'&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url)
            .then(
                function( rooms ){
                    pi.Rooms = rooms;
                    $scope.PI = pi;
                    $scope.noPiSet = false;
                },
                function( error ){
                    $scope.error = "The system couldn't retrieve the selected Principal Investigator.  Please check your internet connection and try again."
                }
            );

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
        $location.search("pi", $item.Key_id);
        getPi($item.Key_id);
    }

    $scope.removeRoom = function(room){
        var modalInstance = $modal.open({
          templateUrl: 'roomConfirmationModal.html',
          controller: roomConfirmationController,
          resolve: {
            PI: function () {
              return $scope.PI;
            },
            room: function(){
                return room;
            }
          }
        });

        modalInstance.result.then(function (PI) {
            console.log(PI);
             $scope.PI.Rooms = [];
             $scope.PI.Rooms = PI.Rooms;
        }, function () {

          //$log.info('Modal dismissed at: ' + new Date());
        });
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

    $scope.openModal = function(pi){
        var user = pi.User;
        // pump in PIs Departments
        user.PrincipalInvestigator = {Departments:pi.Departments};
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/piModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
            angular.extend(user, returnedUser)
        });
    }

  };

var ModalInstanceCtrl = function ($scope, $rootScope, $modalInstance, PI, adding, convenienceMethods, piHubFactory, $q) {
    $scope.PI = PI;
    $scope.convenienceMethods = convenienceMethods;
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

    $scope.onSelectBuilding = function (item) {
        $scope.roomsByFloor = {};
        $scope.chosenBuilding = angular.copy(item);
        $scope.loadingBuildingRooms = true;
        var url = '../../ajaxaction.php?action=getAllBuildingRoomNames&buildingId=' + item.Key_id + '&callback=JSON_CALLBACK';

        return convenienceMethods.getDataAsPromise( url, onFailGetBuildings )
        .then( resp => {
            $scope.chosenBuilding.Rooms = resp.data;
            checkRooms($scope.chosenBuilding, $scope.PI);
            $scope.loadingBuildingRooms = false;
        });
    }

    function checkRooms(building, pi) {
        $scope.roomsByFloor = {};
        var lastLabel = '';
        angular.forEach(building.Rooms, function (room, key) {
            if (convenienceMethods.arrayContainsObject(pi.Rooms, room)) room.piHasRel = true;            
            var floorLabel = room.Name.charAt(0);
            if (lastLabel != floorLabel && !$scope.roomsByFloor.hasOwnProperty(floorLabel)) {
                $scope.roomsByFloor[floorLabel] = [];
            }
            $scope.roomsByFloor[floorLabel].push(room);
            lastLabel = floorLabel;
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

        return piHubFactory.addRoom(roomDto).then(
            function(addedRoom){
                // TODO: Reference the incoming data since our 'room' is limited
                console.debug("Added Room:", addedRoom);
                if(room.piHasRel){
                    // Add the room
                    $scope.PI.Rooms.push(addedRoom);
                }else{
                    // Remove the room
                    var idx = convenienceMethods.arrayContainsObject($scope.PI.Rooms, room, null, true);
                    console.log(idx);
                    console.log($scope.PI.Rooms[idx]);
                    $scope.PI.Rooms.splice(idx,1);
                }

                console.debug($scope.PI);

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
          Is_active: true,
          Purpose:newRoom.Purpose || null
        }
        $scope.error = "";

        var len = $scope.chosenBuilding.Rooms.length;
        for (var i = 0; i < len; i++) {
            var room = $scope.chosenBuilding.Rooms[i];
            console.log(roomDto.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase(), room.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase());
            if (roomDto.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase() == room.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase()) {
                $scope.error = "Room " + roomDto.Name + " has already been created";
                newRoom.IsDirty = false;
                return false;
            }
        }

        var createDefer = $q.defer();
        $scope.createRoomStatus = "Creating room...";
        piHubFactory.createRoom(roomDto).then(
            function(room){
                // Room has been created
                room.IsDirty = false;
                $scope.chosenBuilding.Rooms.push(room);
                newRoom.IsDirty = false;
                createDefer.resolve(room);
                $scope.createRoomStatus = "Room created";
                $scope.newRoomName = room.Name;
                return createDefer.promise;
            },
            function(){
                newRoom.IsDirty = false;
                $scope.createRoomStatus = undefined;
                $scope.error="The room could not be created.  Please check your internet connection.";
                createDefer.reject();
                return createDefer.promise;
            }
        )
        .then(
            function(room){
                // Re-select the building
                return $scope.onSelectBuilding($scope.chosenBuilding)
                .then( function(){
                    $scope.createRoomStatus = "Assigning PI to new room...";
                    return room;
                });
            }
        )
        .then(
            function(room){
                console.log(room);
                room.piHasRel = true;

                //add room to pi
                return $scope.handleRoomChecked(room,$scope.chosenBuilding);
            }
        )
        .then( function (){
            $scope.createRoomStatus = "Room assigned.";
        });
    }

    function onSaveRoom(data, room){

    }

    function onFailSaveRoom(){
        alert("Something went wrong when the system tried to create the new room.");
    }

    $scope.close = function(){
        $modalInstance.close($scope.PI);
    }

    $scope.roomUses = [
        { Name: "Chemical Storage" },
        { Name: "Cold Room" },
        { Name: "Dark Room" },
        { Name: "Equipment Room" },
        { Name: "Greenhouse" },
        { Name: "Growth Chamber" },
        { Name: "Rodent Housing" },
        { Name: "Rodent Surgery" },
        { Name: "Tissue Culture" }
    ];

    $scope.getIsCustom = function (purpose) {
        if (!purpose) return false;
        return $scope.roomUses.filter(function (p) {
            return p.Name == purpose;
        }).length == 0;
    }
}

piHubRoomController = function($scope, $location, convenienceMethods){

    init();
    function init(){
        //var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
        //convenienceMethods.getData( url, onGetDepartemnts, onFailGetDepartments );
    }

}

piHubPersonnelController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory, userHubFactory){

    init();
    function init(){
        $rootScope.userPromise = piHubFactory.getAllUsers()
        .then(users => {
            onGetUsers(users);
        });
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

    $scope.openModal = function(user, role){
        if(!user){
          var user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          $rootScope.userPromise = getUsers()
          .then(getRoles)
          .then(fireModal);
        } else {
            $rootScope.userPromise = getUsers()
            .then(fireModal);
        }

        function getUsers(){
            return piHubFactory.getAllUsers()
              .then(
                function(users){
                  return user;
                }
              )
        }

        function fireModal(user) {
            user.piHub = true;
            if (user.Class == "PrincipalInvestigator"){
                // pump in PIs Departments
                var pi = user;
                user = pi.User;
                user.PrincipalInvestigator = {Departments:pi.Departments};
            }
            userHubFactory.setModalData(user);
            //determine which modal we should open based on the user's role(s)
            if(userHubFactory.hasRole(user, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
                templateString = "piModal";
            }else if(userHubFactory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT)){
                templateString = "labContactModal";
            }else{
                templateString = "labPersonnelModal";
            }

            var modalInstance = $modal.open({
              templateUrl: 'userHubPartials/'+templateString+'.html',
              controller: modalCtrl
            });

            modalInstance.result.then(function (returnedUser) {
              if(user.Key_id){
                angular.extend(user, returnedUser)
              }else{
                pi.LabPersonnel.push(returnedUser);
              }
            });
        }

        function getRoles(user) {

            return userHubFactory.getAllRoles()
                .then(
                    function (roles) {
                        var i = userHubFactory.roles.length;
                        while(i--){
                            if(userHubFactory.roles[i].Name.indexOf(role)>-1){
                                user.Roles.push(userHubFactory.roles[i]);
                                return user;
                            }
                        }
                    }
                )
        }
    }

    $scope.openAssignModal = function(type){
            var modalInstance = $modal.open({
              templateUrl: 'piHubPartials/assign-user.html',
              controller: assignUserCtrl,
              resolve: {
                modalData: function () {
                  $scope.PI.type = type;
                  return $scope.PI;
                }
              }
            });

            modalInstance.result.then(function (returnedUser) {
                $scope.PI.LabPersonnel.push(returnedUser);
            });
    }

}
roomConfirmationController = function (PI, room, $scope, $rootScope, piHubFactory, $modalInstance, convenienceMethods, $q) {
    $scope.PI = PI;
    $scope.room = room;
    $scope.checkingPiHazardsInRoom = true;
    $rootScope.loading = convenienceMethods.checkHazards(room, [PI]).then(function (r) {
        // Convert r to boolean
        room.HasHazards = (r && r == 'true');
        $scope.checkingPiHazardsInRoom = false;
        console.log(r, room);
    })


    $scope.confirm = function(){
        $scope.saving = true;

        $scope.error=false;

        roomDto = {
          Class: "RelationshipDto",
          relation_id: room.Key_id,
          master_id: PI.Key_id,
          add: false
        }
        console.log(PI);
        var url = '../../ajaxaction.php?action=savePIRoomRelation';
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(){
                var idx = convenienceMethods.arrayContainsObject(PI.Rooms, room, null, true);
                PI.Rooms.splice(idx,1);
                console.log(PI)
                $scope.saving = false;
                $modalInstance.dismiss();
            },
            function(){
                $scope.saving = false;
                $scope.error = "The room could not be removed.  Please check your internet connection and try again."
            }
        );

    }

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
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

  var hazardDisplayModalInstanceController = function( $scope, $modalInstance, room, convenienceMethods ){

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

  var assignUserCtrl = function($scope, $rootScope,modalData, $modalInstance, userHubFactory, piHubFactory){
      $scope.modalData = modalData;

      $scope.gettingUsers = true;
      piHubFactory.getAllUsers()
        .then(function(users){$scope.users = users;$scope.modalError="";$scope.gettingUsers = false},function(){$scope.modalError="There was an error getting the list of users.  Please check your internet connection and try again.";})

      $scope.save = function (user, confirmed) {
          if (!confirmed) {
              $scope.selectedUser = user;
          } else {
              user = $scope.selectedUser;
          }
          if(!confirmed && !checkUserForSave(user)) return;
          if(user.Supervisor_id){
              if(!$scope.needsConfirmation){
                  $scope.selectedUser = user;
                  $scope.needsConfirmation = true;
                  return;
              }
          }

          user.Supervisor_id = modalData.Key_id
          user.Is_active = true;

          $rootScope.saving = userHubFactory.saveUser(user)
            .then(
              function(user){
                  user.new = true;
                  $modalInstance.close(user);
              }
            )
      }

      function checkUserForSave(user) {
          if (user.Is_active && !user.Supervisor) return true;
          $scope.message = user.Name + " already exists as ";
          if (!user.Is_active) {
              $scope.message = $scope.message + "an innactive ";
          }else{
              $scope.message = $scope.message + "a ";  
          }

          if (userHubFactory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT)) {
              $scope.message = $scope.message + "Lab Contact ";
          }else{
              $scope.message = $scope.message + "Lab Personnel ";
          }

          if (user.Supervisor) {
              $scope.message = $scope.message + "for " + user.Supervisor.Name;
          }

          $scope.message = $scope.message + ".  Would you like to ";

          if (!user.Is_active) {
              $scope.message = $scope.message + "activate and ";
          }

          if (user.Supervisor) {
              $scope.message = $scope.message + "re-";
          }

          $scope.message = $scope.message + "assign them to " + modalData.User.Name + "?" ;

          console.log(modalData, $scope.message);

          return false;
      }


      $scope.cancel = function(){
          $modalInstance.dismiss();
      }
  }
