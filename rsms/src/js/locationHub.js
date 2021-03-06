var locationHub = angular.module('locationHub', ['ui.bootstrap',
    'convenienceMethodWithRoleBasedModule', 'once', 'cgBusy'])

.config(function($routeProvider){
    $routeProvider
        .when('/rooms',
            {
                templateUrl: 'locationHubPartials/all-rooms.html',
                controller: roomsCtrl,
                resolve: {
                    roomType: () => null
                }
            }
        )
        .when('/rooms/research-labs',
            {
                templateUrl: 'locationHubPartials/pi_labs.html',
                controller: roomsCtrl,
                resolve: {
                    roomType: () => Constants.ROOM_TYPE.RESEARCH_LAB
                }
            }
        )
        .when('/rooms/animal-facilities',
            {
                templateUrl: 'locationHubPartials/pi_labs.html',
                controller: roomsCtrl,
                resolve: {
                    roomType: () => Constants.ROOM_TYPE.ANIMAL_FACILITY
                }
            }
        )
        .when('/rooms/teaching-labs',
            {
                templateUrl: 'locationHubPartials/non_pi_labs.html',
                controller: roomsCtrl,
                resolve: {
                    roomType: () => Constants.ROOM_TYPE.TEACHING_LAB
                }
            }
        )
        .when('/rooms/training-rooms',
            {
                templateUrl: 'locationHubPartials/training_rooms.html',
                controller: roomsCtrl,
                resolve: {
                    roomType: () => Constants.ROOM_TYPE.TRAINING_ROOM
                }
            }
        )
        .when('/buildings',
            {
                templateUrl: 'locationHubPartials/buildings.html',
                controller: buildingsCtrl
            }
        )
        .when('/campuses',
            {
                templateUrl: 'locationHubPartials/campuses.html',
                controller: campusesCtrl
            }
        )
        .otherwise(
            {
                redirectTo: '/rooms'
            }
        );
})
.filter('roomTypeFilter', function(){
    return function(rooms, roomTypeName){
        if( !rooms || !roomTypeName ) return rooms;

        // Include rooms which match the given room type
        // Also include rooms which have not yet been saved
        return rooms.filter(r => r.Room_type == roomTypeName || !r.Key_id);
    }
})
.filter('toArray', function () {
    return function (object) {
        var array = [];
        for (var prop in object) {
            array.push(object[prop]);
        }
        return array;
    }
})
.filter('roomUnassignedFilter', function(){
    return function(room){
        if( !room ){
            return true;
        }

        // 'Unassigned' here means:
        //   1. Assigned to Zero PIs
        //   2. Assigned to Only Inactive PIs
        let empty = (!room.PrincipalInvestigators || room.PrincipalInvestigators.length == 0);
        let onlyInactive = room.PrincipalInvestigators.filter( pi => !pi.Is_active ).length == room.PrincipalInvestigators.length;

        return empty || onlyInactive;
    }
})
.filter('piActiveFilter', function(){
    return function(pis, search){
        if ( !search || !pis ){
            return pis;
        }

        var matched = [];

        if( search.activePis || search.inactivePis ){
            pis.forEach( pi => {
                // Match the PI if its active status matches either filter
                if( (search.activePis && pi.Is_active == search.activePis) || (search.inactivePis && !pi.Is_active == search.inactivePis) ){
                    matched.push(pi);
                }
            });
        }

        return matched;
    };
})
.filter('genericFilter', function ( $filter ) {
    return function (items,search) {
        if (search) {
            var i = 0;
            if(items)i = items.length;
            var filtered = [];

            var isMatched = function(input, item){
                if(item.Name == input)return true;
                return false;
            }

            while(i--){

                //we filter for every set search filter, looping through the collection only once

                var item = items[i];
                var item_matched = true;

                // Only apply filters if the item isn't new
                if( !item.isNew ){
                    if(search.room_type){
                        if( item.Room_type != search.room_type ){
                            item_matched = false;
                        }
                    }

                    if(search.building){
                        if( item.Building && item.Building.Name && item.Building.Name.toLowerCase().indexOf(search.building.toLowerCase() ) < 0 ){
                            item_matched = false;
                        }

                        if(item.Class == "Building" && item.Name.toLowerCase().indexOf(search.building.toLowerCase()) < 0 )  item_matched = false;

                    }

                    if (search.hazards) {
                        console.log(item.Name + ' | ' + item[search.hazards] + ' | ' + search.hazards)
                        if ( item.Class == "Room" && !item[search.hazards] || item[search.hazards] == false || item[search.hazards] == "0" ) item_matched = false;
                    }

                    if(search.room){
                        if( item.Class == 'Room' && item.Name && item.Name.toLowerCase().indexOf(search.room.toLowerCase()) < 0 )  item_matched = false;
                    }

                    if(search.purpose){
                        if( item.Class == 'Room' && !item.Purpose || item.Purpose.toLowerCase().indexOf(search.purpose.toLowerCase()) < 0 )  item_matched = false;
                    }

                    if (search.alias) {
                        if (item.Class == 'Building' && !item.Alias || item.Alias.toLowerCase().indexOf(search.alias.toLowerCase()) < 0) item_matched = false;
                    }

                    if( search.campus ) {
                        if( item.Class != "Building" && (!item.Building || !item.Building.Campus) ){
                            item_matched = false;
                            console.log('set false because no building or campus')
                        }
                        if (item.Building && item.Building.Campus && item.Building.Campus.Name && item.Building.Campus.Name.toLowerCase().indexOf(search.campus.toLowerCase()) < 0) {
                            item_matched = false;
                            console.log('set false because of lack of match');
                        }
                        if(item.Class == "Building" && item.Campus && item.Campus.Name && item.Campus.Name.toLowerCase().indexOf( search.campus.toLowerCase() ) < 0 ){
                            item_matched = false;
                            console.log('set false because of lack of match');
                        }
                    }

                    // Filter based on mix of PI assignment and Is_active status
                    //   (arbitrary block intentional)
                    {
                        let assignedToActive = false;
                        let assignedToInactive = false;
                        item.PrincipalInvestigators.forEach( pi => {
                            if ( pi.Is_active ){
                                assignedToActive = true;
                            }
                            else {
                                assignedToInactive = true;
                            }
                        });

                        let unassigned = !assignedToActive && !assignedToInactive;

                        if( !search.activePis && assignedToActive ){
                            // Exclude rooms assigned to Active PIs
                            item_matched = false;
                            console.debug("Exclude room assigned to Active PIs", item);
                        }

                        else if( !search.unassignedPis && unassigned ){
                            // Exclude Unassigned rooms (assigned to no one OR only to Inactive)
                            item_matched = false;
                            console.debug("Exclude Unassigned room", item);
                        }

                        // Note that search.unassignedPis takes precedence over search.inactivePis
                        //   due to an overlap in conditions
                        // Therefore, they are mutually-exclusive for purposes of filtering Rooms.
                        // While search.unassignedPis is active, search.inactivePis may only be used
                        //   for display purposes (to show/hide Inactive PI names)
                        else if( !search.inactivePis && assignedToInactive && !search.unassignedPis ){
                            // Exclude rooms assigned to Inactive PIs
                            item_matched = false;
                            console.debug("Exclude room assigned to Inactive PIs", item);
                        }
                    }

                    // Search througn User assignments
                    if( item.UserAssignments && item.UserAssignments.length > 0 ){
                        if( search.assignedUser || search.department ){
                            let matches = item.UserAssignments
                                .map(assignment => assignment.User)
                                .filter(user => {
                                    // User name includes user search value
                                    let user_matches = false;
                                    if( search.assignedUser ){
                                        user_matches = (search.assignedUser && user.Name.toLowerCase().indexOf(search.assignedUser.toLowerCase()) > -1);
                                    }

                                    // Any of User's department names include dept search value
                                    let dept_matches = false;
                                    if( search.department) {
                                        dept_matches = user.Departments
                                            .filter( d => d.Name.toLowerCase().indexOf(search.department.toLowerCase()) > -1)
                                            .length > 0;
                                    }

                                    return user_matches || dept_matches;
                                });

                            item_matched = matches.length > 0;
                        }
                    }

                    // Search through PrincipalInvestigator assignments
                    if( item.PrincipalInvestigators && item.PrincipalInvestigators.length > 0 ){

                        if( search.pi || search.department ){
                            if(!item.PrincipalInvestigators.length){
                                console.log('no pis in room '+item.Name);
                            }

                            var j = item.PrincipalInvestigators.length
                            item_matched = false;
                            var deptMatch = false;
                            while(j--){

                                var pi = item.PrincipalInvestigators[j];
                                if( search.pi && pi.User.Name && pi.User.Name.toLowerCase().indexOf(search.pi.toLowerCase()) > -1 ){
                                    item_matched = true;
                                    var piMatch = true;
                                }

                                if(search.department){
                                    deptMatch = false;
                                    if(!pi.Departments || !pi.Departments.length){

                                    }
                                    else{
                                        var k = pi.Departments.length;
                                        while(k--){
                                            if( pi.Departments && pi.Departments[k].Name && pi.Departments[k].Name.toLowerCase().indexOf(search.department.toLowerCase()) > -1 ) deptMatch = true;
                                        }
                                    }
                                    if( ( !search.pi && deptMatch ) || ( piMatch && deptMatch ) )item_matched = true;
                                }
                            }
                        }
                    }
                }

                if(item_matched == true)filtered.push(item);

            }
            filtered.reverse();
            return filtered;
        }else{
            return items;
        }
    };
})
.factory('locationHubFactory', function(convenienceMethods,$q,$rootScope,$http){
    var factory = {};
    factory.rooms = [];
    factory.buildings = [];
    factory.campuss = [];
    factory.modalData;
    factory.isEditing = false;

    factory.editing = function(bool) {
        factory.isEditing = bool;
        console.log("dig", factory.isEditing);
    }

    factory.getRooms = function(){
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if(this.rooms.length){
            deferred.resolve(this.rooms);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllRoomDetails&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function(rooms){
                factory.rooms = rooms;
            }
        )

        return deferred.promise;

    }


    factory.getBuildings = function()
    {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
                //lazy load
        if(this.buildings.length){
            deferred.resolve(this.buildings);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllBuildings&skipRooms=true&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }
        deferred.promise.then(
            function(buildings){
                factory.buildings = buildings;
            }
        )
        return deferred.promise;
    }

    factory.setBuildings = function(buildings)
    {
        this.buildings = buildings;
    }
    factory.getCampuses = function()
    {
        console.log(this);
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if(this.campuss.length){
            deferred.resolve(this.campuss);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllCampuses&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }
        deferred.promise.then(
            function(campuses){
                factory.campuss = campuses;
            }
        )
        return deferred.promise;
    }
    factory.setCampuses = function( campuses )
    {
        this.campuss = campuses
    }

    factory.getBuildingByRoom = function( room )
    {
        if(!room.Building){
            room.Building = this.buildings.find( b => b.Key_id == room.Building_id);
        }

        return room.Building;
    }

    factory.getAssignableUsers = function(roomType){
        // Get or init promise to load this type of user
        if( !factory.AssignableUsers ){
            factory.AssignableUsers = {};
        }

        // IF NOT INIT'D
        if( !factory.AssignableUsers[roomType] ){
            // Init data/promise container
            factory.AssignableUsers[roomType] = {
                loading: false,
                data: undefined,
                dataWillLoad: $q.defer()
            };
        }

        let dataContainer = factory.AssignableUsers[roomType];

        // IF NOT LOADING
        if( !dataContainer.loading ){
            dataContainer.loading = true;

            // Load the data from endpoint
            let endpoint = GLOBAL_WEB_ROOT + 'ajaxaction.php?callback=JSON_CALLBACK&action=getAllAssignableUserDetails&roomTypeName=' + roomType;
            convenienceMethods.getDataAsDeferredPromise(endpoint).then(
                function(promise){
                    // Set container data
                    dataContainer.data = promise;

                    // Resolve container promise
                    dataContainer.dataWillLoad.resolve(dataContainer.data);
                },
                function(promise){
                    dataContainer.loading = false;
                    dataContainer.dataWillLoad.reject();
                    ToastApi.toast("Failed to load user list", ToastApi.ToastType.ERROR);
                }
            );
        }

        return dataContainer.dataWillLoad.promise;
    };

    factory.getAllPis = function(){
        //lazy load

        //if we don't have a the list of pis, get it from the server
        if( !factory.AllPisWillLoad ){
            factory.AllPisWillLoad = $q.defer();

            if(factory.pis){
                factory.AllPisWillLoad.resolve(factory.pis);
            }
            else{
                var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllPIDetails&callback=JSON_CALLBACK';
                    convenienceMethods.getDataAsDeferredPromise(url).then(
                    function(promise){
                        factory.AllPisWillLoad.resolve(promise);
                        factory.pis = promise;
                    },
                    function(promise){
                        factory.AllPisWillLoad.reject();
                    }
                );
            }
        }

        return factory.AllPisWillLoad.promise;
    }

    /**
     * Retrieve all assignments (both UserAssignment and PrincipalInvestigator) for the given Room
     */
    factory.getRoomAssignments = function(room) {
        let assignments = (room.UserAssignments || []);
        // Special-case: PIs are modeled differently, but we can mock them up as Assignments for validation
        if( room.PrincipalInvestigators ){
            assignments = assignments.concat( room.PrincipalInvestigators.map(pi => {
                return {
                    User_id: pi.User_id,
                    User: pi.User,
                    Role_name: Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR
                };
            }));
        }

        return assignments;
    }

    factory.validateRoom = function (room) {
        /* Room must have:
             - Building
             - Name
             - Type
        */

        let errors = [];

        // Room must have: Type
        if( !room.Room_type ){
            errors.push("Room Type is required.");
        }
        else {
            // Validate assignments
            let assignments = this.getRoomAssignments(room);
            // Selected type's assignable_to must be compatible with
            //   any existing assignments
            let type = Constants.ROOM_TYPE[room.Room_type];

            if( assignments.length ){
                let incompatible = assignments
                    .filter( a => a.Role_name != type.assignable_to )   // Filter to assignments which are different from RoomType
                    .map( a => a.Role_name );                           // Map to the assignable role name
                let incompatible_types = incompatible
                    .filter( (v, i, self) => self.indexOf(v) === i);    // Filter to unique values

                if( incompatible.length ){
                    // Incompatible assignments exist, so we cannot change to this room type
                    errors.push("Cannot change Room Type to '" + type.label + "' - it is assigned to " + incompatible.length + ' '
                        + incompatible_types.join(', ') + " user" + (incompatible.length != 1 ? 's' : ''));
                }
            }
        }

        // Room must have: Name
        if( !room.Name ){
            // Name is required
            errors.push("Name is required.");
        }

        // Room must have: Building
        // Room Name must be unique in its building
        if( !room.Building_id ){
            errors.push("Building is required.");
        }
        else {
            // Find rooms in the same building with the same name
            let name_lc = room.Name.toLowerCase();
            let name_collissions = this.rooms.filter( r => {
                // Ignore the same room
                if( r.Key_id == room.Key_id ){
                    return false;
                }

                if( r.Building_id == room.Building_id ){
                    return r.Name.toLowerCase() == name_lc;
                }

                return false;
            });

            if( name_collissions.length > 0 ){
                errors.push("Room " + room.Name + " already exists in " + this.getBuildingByRoom(room).Name + '.');
            }
        }

        return errors;
    }

    factory.saveRoom = function (roomDto) {
        this.clearErrors();
        var deferred = $q.defer();

        console.log('Validating Room before save', roomDto);
        let validationErrors = this.validateRoom(roomDto);

        if( validationErrors.length > 0 ){
            // Validation failed
            $rootScope.validationErrors = validationErrors;

            roomDto.IsDirty=false;
            deferred.reject({ validation_failed: true, messages: validationErrors });
        }
        else {
            // Validation passed

            console.log('Attempt to save Room', roomDto);
            var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveRoom";
            convenienceMethods.saveDataAndDefer(url, roomDto).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(error){
                    deferred.reject(error);
                }
            );
        }
        return deferred.promise
    }

    factory.roomAlreadyExists = function(room)
    {
        var i=this.rooms.length;
        while(i--){
            if (this.rooms[i].Key_id && this.rooms[i].Name.toLowerCase()==room.Name.toLowerCase() && this.rooms[i].Building_id == room.Building_id)return true;
        }
        return false;
    }

    factory.saveBuilding = function(buildingDto){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveBuilding";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, buildingDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }


    factory.saveCampus = function(campusDto){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveCampus";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, campusDto).then(
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

    factory.handleObjectActive = function(object)
    {
        $rootScope.error = null;
        object.IsDirty = true;
        var copy = convenienceMethods.copyObject( object );
        copy.Is_active = !copy.Is_active;

        this['save'+object.Class](copy)
            .then(
                function(returned){
                    //TODO:  change factory's properties to uppercase, remove stupid toLowercase() calls
                    var i = factory[object.Class.toLowerCase()+'s'].length

                    while(i--){
                        copy.IsDirty = false;
                        if( factory[object.Class.toLowerCase()+'s'][i].Key_id ==  copy.Key_id) factory[object.Class.toLowerCase()+'s'][i] = copy;
                    }

                },
                function(){
                    $rootScope.error = 'The ' + object.Class.toLowerCase() + ' could not be saved.  Please check your internet connection and try again.';
                    object.IsDirty = false;
                }
            )

    }

    factory.setEditState = function(obj, scope)
    {
            var i = scope.length
            while(i--){
                scope[i].edit = false;
            }

            if(!obj.edit)obj.edit = false;
            obj.edit = !obj.edit;
            if(obj.Class == 'Building'  && obj.Campus == false)obj.Campus = '';

            this.editing(obj.edit);

            $rootScope.copy = convenienceMethods.copyObject(obj);
    }

    factory.clearErrors = function(){
        $rootScope.validationErrors = null;
        if( $rootScope.errorToast ){
            ToastApi.dismissToast($rootScope.errorToast.id);
        }
    };

    factory.cancelEdit = function(obj, scope)
    {
            this.clearErrors();
            $rootScope.copy = null;
            obj.edit = false;

            //if this is a new object, we should pull it out of the collection
            if(obj.newObj && scope){

                var i = scope.length
                while(i--){
                    if(scope[i].newObj)scope.splice(i,1);
                }

            }

            this.editing(false);
    }

    factory.getCSV = function(){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=getLocationCSV";
        $http.get(url, function(status, response){
            // success
        }, function(status, response){
            $rootScope.error = 'The list of locations could not be retrieved.  Please check your internet connection and try again.';
        });
    }


    return factory;
});


routeCtrl = function($scope, $location,$rootScope){
    $scope.locationHubViews = [
        { route: '/rooms', name: 'All Rooms' },
        {},
        { route: '/rooms/research-labs', name: 'Research Labs' },
        { route: '/rooms/teaching-labs', name: 'Teaching Labs' },
        { route: '/rooms/animal-facilities', name: 'Animal Facilities' },
        {},
        { route: '/rooms/training-rooms', name: 'Training Rooms' },
        {},
        { route: '/buildings', name: 'Buildings' },
        { route: '/campuses', name: 'Campuses' }
    ];

    $scope.location = $location.path();
    $scope.setRoute = function(route){
        $location.path(route);
        $scope.location = route;
    }
    $rootScope.iterator=0;
}

roomsCtrl = function($scope, $rootScope, $location, $routeParams, convenienceMethods, $q, $modal, locationHubFactory, roleBasedFactory, roomType){
    $rootScope.modal = false;
    $scope.loading = true;
    var lhf = $scope.lhf = locationHubFactory;
    $rootScope.rbf = roleBasedFactory;
    $scope.constants = Constants;
    $scope.convenienceMethods = convenienceMethods;
    $scope.roomType = roomType;
    console.debug("Rooms controller | type=" + roomType);

    // Default search parameters
    $scope.search = {
        activePis: true,
        unassignedPis: true
    };

    function _strToBoolean( value ){
        if( (typeof value) == 'string' ){
            return value.toLowerCase() == 'true';
        }
        else return value == true;
    }

    if( $routeParams ){
        console.log("Params", $routeParams);
        angular.extend($scope.search, $routeParams);
        console.log("Extended search opts", $scope.search);

        // string-to-boolean conversion
        $scope.search.activePis = _strToBoolean($scope.search.activePis);
        $scope.search.inactivePis = _strToBoolean($scope.search.inactivePis);
        $scope.search.unassignedPis = _strToBoolean($scope.search.unassignedPis);

        console.log("Boolean-converted search opts", $scope.search);
    }

    $scope.userCanEditRoom = roleBasedFactory.getHasPermission([
        $rootScope.R[Constants.ROLE.NAME.ADMIN],
        $rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN]
    ]);

    $scope.editRoom = function (room) {
        locationHubFactory.clearErrors();

        if (!room) {
            // Match room type from Scope or Search

            var room = {
                Class: "Room",
                Room_type: ($scope.roomType ? $scope.roomType.name : null) || $scope.search.room_type,
                PrincipalInvestigators: [],
                Name: "",
                isNew: true,
                Is_active: true,
                edit: true
            }
        }

        $scope.roomCopy = angular.copy(room);
        room.edit = true;
        $scope.roomCopy.edit = true;
        $scope.editingRoom = true;

        if (!room || !room.Key_id) $scope.rooms.unshift($scope.roomCopy)

        // Load all users who can be assigned to this Room
        if( $scope.roomType ){
            locationHubFactory.getAssignableUsers($scope.roomType.name)
                .then(users => {
                    $scope.assignableUsers = users;
                    $scope.assignableUsers.selected = false;

                    // Special-case match for PIs: Assign PI-users to 'pis' field
                    if( $scope.roomType.assignable_to == Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR ){
                        $scope.pis = $scope.assignableUsers;
                    }
                    else if($scope.roomCopy.UserAssignments.length){
                        $scope.assignableUsers.selected = $scope.assignableUsers.find(
                            u => u.Key_id == $scope.roomCopy.UserAssignments[0].User_id);
                    }
                });
        }
    }

    $scope.cancelEdit = function (room) {
        locationHubFactory.cancelEdit(room);
        delete $scope.roomCopy;
        $scope.editingRoom = false;
        if (!room.Key_id) {
            $scope.rooms.splice($scope.rooms.indexOf(room), 1);
        }
        room.edit = false;
    }

    $scope.loading = locationHubFactory.getBuildings()
        .then(
            function(buildings){
                locationHubFactory.getRooms()
                .then(
                    function(rooms){
                        $scope.rooms = rooms;
                        $scope.loading = false;
                    }
                )
            }
        )
    
    $scope.openRoomModal = function(room){

        if(!room)room = {Is_active: true, Class:'Room', Name:'', Building:{Name:''}, PrincipalInvestigators:[]};
        locationHubFactory.setModalData(null);
        locationHubFactory.setModalData(room);

        var modalInstance = $modal.open({
          templateUrl: 'locationHubPartials/roomsModal.html',
          controller: modalCtrl
        });


        modalInstance.result.then(function () {
           locationHubFactory.getRooms()
                .then(
                    function(rooms){
                        $scope.rooms = rooms;
                        $scope.loading = false;
                    }
                )
        });

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

    $scope.saveRoom = function (room, originalRoom) {
        //unset global error, if it exists.
        $scope.error = null;

        // Verify save if PI was removed
        var verifyIfRequired = $q.defer();

        // Find if PIs were removed
        var removedPIs = [];
        for(var i = 0; i < originalRoom.PrincipalInvestigators.length; i++){
            var origPI = originalRoom.PrincipalInvestigators[i];

            var found = false;
            for(var j = 0; j < room.PrincipalInvestigators.length; j++){
                var toSavePI = room.PrincipalInvestigators[j];

                if( origPI.Key_id == toSavePI.Key_id ){
                    found = true;
                    break;
                }
            }

            if( !found ){
                removedPIs.push(origPI);
            }
        }

        if( removedPIs.length ){
            console.debug("PIs were removed from room: ", removedPIs);

            var modalInstance = $modal.open({
                templateUrl: 'roomConfirmationModal.html',
                controller: roomConfirmationController,
                resolve: {
                    PI: function() {
                        return removedPIs;
                    },
                    room: function () {
                        room.deactivating = false;
                        return room;
                    }
                }
            });

            // Instruct the modal to simply confirm, rather than persisting a change
            modalInstance.simpleConfirm = true;
            modalInstance.result.then(
                () => verifyIfRequired.resolve(),
                () => {
                    console.debug("User cancelled confirmation dialog");

                    // Reject verification
                    verifyIfRequired.reject();

                    // Cancel the edit
                    $scope.cancelEdit(originalRoom);
                }
            );
        }
        else {
            verifyIfRequired.resolve();
        }

        verifyIfRequired.promise.then(
            () => locationHubFactory['getRooms']().then(
                    function (stuff) {
                        var collection = stuff;
                        console.log(room, originalRoom);
                        $rootScope.saving = $q.all([locationHubFactory.saveRoom(room)]).then(
                            function (r) {
                                var returned = r[0];
                                if (room.Key_id) {
                                    //we are editing an old object
                                    var i = collection.length;
                                    while (i--) {
                                        //var objectInCollection = collection[i];
                                        if (collection[i].Key_id == returned.Key_id) {
                                            collection[i] = returned;
                                            break;
                                        }
                                    }
                                    room.IsDirty = false;
                                } else {
                                    //we are creating an new object
                                    collection.push(returned);
                                    $scope.rooms = collection;
                                }
                                room.edit = false;

                                let name = (room.Building_name ? room.Building_name : 'Room') + ' ' + room.Name;
                                ToastApi.toast( 'Saved room: ' + name);

                                // Close the editor
                                $scope.cancelEdit(room);
                        },
                        function (err) {
                            console.error(err);
                            if( err.validation_failed ){
                                let msg = 'The ' + room.Class + ' could not be saved.';
                                msg += "<ul class='red'>"
                                    + err.messages.map(m => "<li>" + m + "</li>").join('')
                                    + "</ul>";

                                $rootScope.errorToast = ToastApi.toast( msg, ToastApi.ToastType.ERROR, -1 );
                            }
                            else if(err.Class == 'ActionError'){
                                ToastApi.toast( err.Message, ToastApi.ToastType.ERROR );
                            }

                            room.IsDirty = false;
                        }
                    )
                    }
            )

        );

    }

    /**
     * Change the UserAssignment(s) for the edited Room.
     *
     * Note that while the data model supports multiple assignments,
     * only one is displayed/modified due to lack of use-cases.
     */
    $scope.assignUser = function (user, add){
        if( add ){
            // Assign to user, overwriting any existing
            let new_assignment = {
                'Class': 'UserRoomAssignment',
                'Role_name': $scope.roomType.assignable_to,
                'User_id': user.Key_id,
                'User': user
            };
            $scope.roomCopy.UserAssignments = [new_assignment];
        }
        else {
            // Unassign all
            $scope.roomCopy.UserAssignments = [];

            // Clear out selected placeholder
            $scope.assignableUsers.selected = null;
        }
    };

    $scope.handlePI = function (pi, add) {
        // Handle PI selection

        if ( add ) {
            // Add PI to list
            $scope.roomCopy.PrincipalInvestigators.push(pi);
        } else {
            // Remove PI from list
            // Find PI in room's list
            let idx = $scope.roomCopy.PrincipalInvestigators.indexOf(pi);

            console.debug("Remove index " + idx + " from room list");
            $scope.roomCopy.PrincipalInvestigators.splice(idx, 1);
        }

        // Clear selection from dropdown (if it's been initialized)
        if( $scope.pis ){
            $scope.pis.selected = null;
        }
    }

    $scope.removeRoom = function (room, pi) {
        var modalInstance = $modal.open({
            templateUrl: 'roomConfirmationModal.html',
            controller: roomConfirmationController,
            resolve: {
                PI: function () {
                    return pi;
                },
                room: function () {
                    room.deactivating = false;
                    return room;
                }
            }
        });

        modalInstance.result.then(function (room) {
            var idx = convenienceMethods.arrayContainsObject(room.PrincipalInvestigators, pi, null, true);
            room.PrincipalInvestigators.splice(idx, 1);
        }, function () {

            //$log.info('Modal dismissed at: ' + new Date());
        });
    }

    $scope.getIsCustom = function (purpose) {
        if (!purpose) return false;
        return $scope.roomUses.filter(function (p) {
            return p.Name == purpose;
        }).length == 0;
    }

    //Math class is not exposed in angular lexer, so:
    $scope.roundDown = function (num, digits) {
        return Math.floor(num / digits) * digits;
    }

    $scope.confirmDeactivate = function (room) {
        if (room.PrincipalInvestigators && room.PrincipalInvestigators.length) {
            $rootScope.loadingHasHazards = $q.all([convenienceMethods.checkHazards(room, room.PrincipalInvestigators)]).then(function (r) {
                let resp = r[0];
                if ( resp.HasHazards ) {
                    var modalInstance = $modal.open({
                        templateUrl: 'roomConfirmationModal.html',
                        controller: roomConfirmationController,
                        resolve: {
                            PI: function () {
                                return room.PrincipalInvestigators[0];
                            },
                            room: function () {
                                room.deactivating = true;
                                return room;
                            }
                        }
                    });
                } else {
                    return lhf.handleObjectActive(room);
                }
            })
        } else {
            return lhf.handleObjectActive(room);
        }

    }

}

var buildingsCtrl = function ($scope, $rootScope, $modal, locationHubFactory, roleBasedFactory) {
    $rootScope.rbf = roleBasedFactory;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;

    $scope.userCanEditBuilding = roleBasedFactory.getHasPermission([
        $rootScope.R[Constants.ROLE.NAME.ADMIN],
        $rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN]
    ]);

    locationHubFactory.getBuildings()
        .then(
            function(buildings){
                console.log(buildings);
                $scope.buildings = buildings;
                $scope.loading = false;
                locationHubFactory.getCampuses().then(
                    function(campuses){
                        $scope.campuses = campuses;
                    }
                );
            }
        )

    $scope.saveBuilding = function(building){
            building.index = false;
            console.log(building);
            building.IsDirty = true;
            if(!$rootScope.copy.Is_active)$rootScope.copy.Is_active = true;
            locationHubFactory.saveBuilding($rootScope.copy)
                .then(
                    function (returned) {
                        console.log(returned);
                        building.IsDirty = false;
                        building.edit = false;
                        building.isNew = true;
                        angular.extend(building, returned);
                        building.Campus = returned.Campus;

                        ToastApi.toast("Saved " + building.Name);
                        locationHubFactory.cancelEdit(building, $scope.buildings);
                    },
                    function(error){
                        building.IsDirty = false;
                        building.edit = false;
                        $scope.error = 'The building could not be saved.  Please check your internet connection and try again.';
                    }
                )
    }

    $scope.onSelectCampus = function(campus,building){
        building.Campus = campus;
        building.Campus_id = campus.Key_id;
        console.log(building);
    }

    $scope.addBuilding = function(){
        $rootScope.copy = {Class:'Building', Is_active:true, edit:true, index:1, newObj:true}
        $scope.buildings.push($rootScope.copy);
    }

}


campusesCtrl = function($scope, $rootScope, locationHubFactory, roleBasedFactory){
    $rootScope.rbf = roleBasedFactory;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;

    $scope.userCanEditCampus = roleBasedFactory.getHasPermission([
        $rootScope.R[Constants.ROLE.NAME.ADMIN],
        $rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN]
    ]);

    locationHubFactory.getCampuses()
        .then(
            function(campuses){
                console.log(campuses);
                $scope.campuses = campuses;
                $scope.loading = false;
            }
        )

    $scope.saveCampus = function(campus){
            campus.IsDirty = true;
            if(!$rootScope.copy.Is_active)$rootScope.copy.Is_active = true;
            locationHubFactory.saveCampus($rootScope.copy)
                .then(
                    function( returned ){
                        console.log(returned);
                        campus.IsDirty = false;
                        campus.edit = false;
                        campus.isNew = true;
                        campus.index = false;
                        angular.extend(campus, returned);

                        ToastApi.toast("Saved " + campus.Name);
                        locationHubFactory.cancelEdit(campus, $scope.campuses);
                    },
                    function(error){
                        campus.IsDirty = false;
                        campus.edit = false;
                        $scope.error = 'The building could not be saved.  Please check your internet connection and try again.';
                    }
                )
    }


    $scope.addCampus = function(){
        $rootScope.copy = {Class:'Campus', Is_active:true, edit:true, index:1, newObj:true}
        $scope.campuses.push($rootScope.copy);
    }

}

modalCtrl = function($scope, $rootScope, locationHubFactory, $modalInstance, convenienceMethods){
    $rootScope.validationError='';
    $rootScope.modal = true;

    $scope.roomUses = [
        {Name:"Chemical Storage"},
        {Name:"Cold Room"},
        {Name:"Dark Room"},
        {Name:"Equipment Room"},
        {Name:"Greenhouse"},
        {Name:"Growth Chamber"},
        {Name:"Rodent Housing"},
        {Name:"Rodent Surgery"},
        {Name:"Tissue Culture"}
    ];

    //make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
    $scope.modalData = convenienceMethods.copyObject( locationHubFactory.getModalData() );
    $scope.selectedUse = {Name:$scope.modalData.Purpose};

    locationHubFactory.getBuildings().then(
        function(buildings){
            $scope.buildings = buildings;
        }
    );

    if($scope.modalData.Class == "Room"){
        locationHubFactory.getAllPis()
            .then(
                function(pis){
                    $scope.pis = pis;
                    $scope.pis.selected = false;
                }
            ).then(
                function(){
                    $scope.departmentsHaveSpecialtyLab = false;
                    var i = $scope.modalData.PrincipalInvestigators.length;
                    while(i--){
                        var n = $scope.modalData.PrincipalInvestigators[i].Departments.length;
                        while(n--) {
                            var dept = $scope.modalData.PrincipalInvestigators[i].Departments[n];
                            if (dept.Specialty_lab != null && dept.Specialty_lab) {
                                console.log(dept.Name);
                                $scope.departmentsHaveSpecialtyLab = true;
                            }
                        }
                    }
                }
            )
    }

    $scope.cancel = function () {
       $rootScope.modal = false;

      $rootScope.validationError='';
      $modalInstance.dismiss();
        console.log($scope.use);
    };


    $scope.onSelectBuilding = function(building){
        $scope.modalData.Building_id = building.Key_id;
    }

    $scope.save = function(obj){
        $rootScope.modal = false;

        obj.IsDirty=true;
        //unset global error, if it exists.
        $scope.error = null;

        locationHubFactory['get'+obj.Class+'s']().then(
                function(stuff){
                    var collection = stuff;
                    locationHubFactory['save'+obj.Class]( obj ).then(
                        function(returned){
                        if( obj.Key_id ){
                            //we are editing an old object
                            var i = collection.length;
                            while(i--){
                                //var objectInCollection = collection[i];
                                if(collection[i].Key_id == returned.Key_id){
                                    collection[i] = returned;
                                    break;
                                }
                            }
                            obj.IsDirty=false;
                        }else{
                            //we are creating an new object
                            collection.push(returned);
                            obj.IsDirty=false;
                        }
                        $modalInstance.close();
                    },
                    function(){
                        $scope.error = 'The' + obj.Class + ' could not be saved.  Please check your internet connection and try again.';
                        obj.IsDirty=false;
                        $modalInstance.dismiss();
                    }
                )
            }

        );

    }

    $scope.handlePI = function(pi, adding){
        pi.saving = true;
        $scope.modalError="";
        var room = $scope.modalData;
        if(!room.Key_id){
            room.PrincipalInvestigators.push(pi);
            return;
        }
        var roomDto = {
          Class: "RelationshipDto",
          relation_id: room.Key_id,
          master_id: pi.Key_id,
          add: adding
        }
        var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=savePIRoomRelation';
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(room){
                var rooms = locationHubFactory.rooms;
                var i = rooms.length;
                while(i--){
                    if (room.Key_id === rooms[i].Key_id) {
                        console.log(room);
                        var originalRoom = $scope.modalData = room;
                        break;
                    }
                }
                pi.saving = false;
                $scope.pis.selected = null;
            },
            function(){
                pi.saving = false;
                var added = adding ? "added" : "removed";
                $scope.error = "The PI could not be " + added + ".  Please check your internet connection and try again.";
            }
        );

    }

}
roomConfirmationController = function (PI, room, $scope, $rootScope, $modalInstance, convenienceMethods, $q) {
    var checkPIs;
    if( Array.isArray(PI) ){
        $scope.PIs = PI;
        checkPIs = PI;
    }
    else{
        $scope.PI = PI;
        checkPIs = [PI];
    }

    $scope.room = room;

    $scope.checkData = function checkData(){
        if( checkPIs.length ){
            $scope.checkingPiHazardsInRoom = true;
            $rootScope.loadingHasHazards = $q.all([convenienceMethods.checkHazards(room, checkPIs)]).then(function (r) {
                let resp = r[0];
                $scope.checkingPiHazardsInRoom = false;
                room.HasHazards = resp.HasHazards;
                console.log(resp, room);

                $scope.PIsWithHazards = checkPIs.filter( pi =>
                    resp.PI_ids.some(entry => entry.Key_id == pi.Key_id)
                );

                $scope.Pis_with_hazards = resp.PI_ids;
            })
        }
        else{
            // No one to check
            $scope.checkingPiHazardsInRoom = false;
            room.HasHazards = false;
        }
    };

    // Initially check the data
    $scope.checkData();

    var hazardInventory = null;
    $scope.openHazardInventory = function openHazardInventory(pi){
        let params = '?' + $.param({"pi": pi.Key_id}) +
                     '&' + $.param({"room": [room.Key_id]});

        // Open hazard inventory for PI & Room in a new window
        hazardInventory = window.open(window.GLOBAL_WEB_ROOT + 'hazard-inventory/#' + params);

        // Ensure a reminder toast is displayed both here and there
        let loc_toast = undefined;
        let inv_toast = undefined;

        let reminder = function reminder(existingReminder, api, message){
            if( !api ) return null;

            // Create toast if we don't have a link to one or our linked one was dismissed (i.e. doesn't exist)
            if( !existingReminder || !api.getToast(existingReminder.id) ){
                return api.toast(message, api.ToastType.ERROR, -1);
            }

            return existingReminder;
        };

        // Re-remind periodically
        let reminderMessage = "Close the Hazard Inventory to refresh Hazards for Room " + room.Name + " in the Location Hub";
        let reminderInterval = setInterval(function(){
            inv_toast = reminder(inv_toast, hazardInventory.ToastApi, reminderMessage);
            loc_toast = reminder(loc_toast, window.ToastApi, reminderMessage);
        }, 5000);

        // When the hazardInventory window is closed...
        hazardInventory.onbeforeunload = function(){
            // Cancel our reminder interval
            clearInterval( reminderInterval );

            // Dismiss our local toast
            //   (we can ignore the remote toast, as it's unloaded)
            if( loc_toast ){
                ToastApi.dismissToast( loc_toast.id );
            }

            // Refresh the confirmation details
            console.info("Linked Hazard-inventory window is unloading; Refresh confirmation dialog data");
            $scope.checkData();

            $scope._closedHazardInventory = true;
        };
    }

    $scope.confirm = function () {
        // Check if we're supposed to simply confirm...
        if( $modalInstance.simpleConfirm ){
            $modalInstance.close();
            return;
        }

        $scope.saving = true;

        $scope.error = false;

        roomDto = {
            Class: "RelationshipDto",
            relation_id: room.Key_id,
            master_id: PI.Key_id,
            add: false
        }
        console.log(PI);
        var url = '../../ajaxaction.php?action=savePIRoomRelation';
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function () {
                $scope.saving = false;
                $modalInstance.dismiss();
                ToastApi.toast('Room ' + room.Name + ' has been saved');
            },
            function () {
                $scope.saving = false;
                $scope.error = "The room could not be removed.  Please check your internet connection and try again."
            }
        );

    }

    $scope.cancel = function () {
        // If we have a hazard-inventory window linked, also close it
        if( hazardInventory ){
            hazardInventory.close();
        }

        if( $scope._closedHazardInventory ){
            // Hazard Inventory window was closed. Notify the user
            // that cancelling this action will not refresh hazards
            let name = $scope.room.Building_name + ' ' + $scope.room.Name;
            ToastApi.toast(
                "If you made changes to " + name + " in the Hazard Inventory, " +
                "the hazard icons displayed here may not update until this page is refreshed.",
                ToastApi.ToastType.WARNING,
                -1
            );
        }

        $modalInstance.dismiss('cancel');
    }

}
