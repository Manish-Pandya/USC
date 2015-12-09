var locationHub = angular.module('locationHub', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once'])

.config(function($routeProvider){
    $routeProvider
        .when('/rooms',
            {
                templateUrl: 'locationHubPartials/rooms.html',
                controller: roomsCtrl
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
.filter('genericFilter', function () {
    return function (items,search) {
        if(search){
            var i = 0;
            if(items)i = items.length;
            var filtered = [];

            var isMatched = function(input, item){
                if(item.Name == input)return true;
                return false;
            }

            while(i--){

                //we filter for every set search filter, looping through the collection only once

                var item=items[i];
                item.matched = true;

                if(search.building){
                    if( item.Building && item.Building.Name && item.Building.Name.toLowerCase().indexOf(search.building.toLowerCase() ) < 0 ){
                        item.matched = false;
                    }

                    if(item.Class == "Building" && item.Name.toLowerCase().indexOf(search.building.toLowerCase()) < 0 )  item.matched = false;

                }

                if(search.room){
                    if( item.Class == 'Room' && item.Name && item.Name.toLowerCase().indexOf(search.room.toLowerCase()) < 0 )  item.matched = false;
                }

                if(search.purpose){
                    if( item.Class == 'Room' && !item.Purpose || item.Purpose.toLowerCase().indexOf(search.purpose.toLowerCase()) < 0 )  item.matched = false;
                }

                if( search.campus ) {
                    if( !item.Building || !item.Building.Campus ){
                        item.matched = false;
                        console.log('set false because no building or campus')
                    }
                    if( item.Building.Campus && item.Building.Campus.Name.toLowerCase().indexOf( search.campus.toLowerCase() ) < 0 ){
                        item.matched = false;
                        console.log('set false because of lack of match');
                    }
                }

                if(search.pi || search.department && item.PrincipalInvestigators){
                    if(!item.PrincipalInvestigators.length){
                        console.log('no pis in room '+item.Name);
                        item.PrincipalInvestigators = [{Class:"PrincipalInvestigator",User:{Name: 'Unassigned', Class:"User"}, Departments:[{Name: 'Unassigned'}] }];
                    }

                    var j = item.PrincipalInvestigators.length
                    item.matched = false
                    while(j--){

                        var pi = item.PrincipalInvestigators[j];
                        if( search.pi && pi.User.Name && pi.User.Name.toLowerCase().indexOf(search.pi.toLowerCase()) > -1 ) item.matched = true;

                        if(search.department){
                            if(!pi.Departments || !pi.Departments.length){
                                pi.Departments = [{Name: 'Unassigned'}];
                            }else{
                                item.matched = false;
                                var k = pi.Departments.length;
                                while(k--){
                                    if( pi.Departments && pi.Departments[k].Name && pi.Departments[k].Name.toLowerCase().indexOf(search.department.toLowerCase()) > -1 ) item.matched = true;
                                }
                            }

                        }
                    }

                }

                if(item.matched == true)filtered.push(item);

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
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllRooms&callback=JSON_CALLBACK';
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
        if(room.Building)return room.Building;
        if(!room.trusted){
            var i = this.buildings.length
            while(i--){
                room.trusted = true;
                if(this.buildings[i].Key_id == room.Building_id){
                    room.Building = this.buildings[i];
                }
            }
        }

        return room.Building;
    }

    factory.getAllPis = function(){
        //lazy load

        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        if(factory.pis){
            deferred.resolve(factory.pis);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
                  convenienceMethods.getDataAsDeferredPromise(url).then(
                  function(promise){
                    deferred.resolve(promise);
                    factory.pis = promise;
                  },
                  function(promise){
                    deferred.reject();
                  }
            );
        }
        return deferred.promise;
    }

    factory.saveRoom = function(roomDto){
        $rootScope.validationError='';
        if(!roomDto.Key_id){
            var defer = $q.defer();
            if(this.roomAlreadyExists(roomDto)){
                $rootScope.validationError="Room "+roomDto.Name+" already exists in "+ this.getBuildingByRoom(roomDto).Name+'.';
                roomDto.IsDirty=false;
                return
            }
        }
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveRoom";
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

    factory.roomAlreadyExists = function(room)
    {
        var i=this.rooms.length;
        while(i--){
            if(this.rooms[i].Name.toLowerCase()==room.Name.toLowerCase() && this.rooms[i].Building_id == room.Building_id)return true;
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

            $rootScope.copy = convenienceMethods.copyObject(obj);
    }

    factory.cancelEdit = function(obj, scope)
    {
            $rootScope.copy = null;
            obj.edit = false;

            //if this is a new object, we should pull it out of the collection
            if(obj.newObj && scope){

                var i = scope.length
                while(i--){
                    if(scope[i].newObj)scope.splice(i,1);
                }

            }
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
    $scope.location = $location.path();
    $scope.setRoute = function(route){
        $location.path(route);
        $scope.location = route;
    }
    $rootScope.iterator=0;
}

roomsCtrl = function($scope, $rootScope, $location, convenienceMethods, $modal, locationHubFactory, roleBasedFactory){
    $rootScope.modal = false;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;
    $rootScope.rbf = roleBasedFactory;

    locationHubFactory.getBuildings()
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


}

var buildingsCtrl = function ($scope, $rootScope, $modal, locationHubFactory, roleBasedFactory) {
    $rootScope.rbf = roleBasedFactory;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;

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
            if(building.Campus)$rootScope.copy.Campus_id = building.Campus.Key_id;
            locationHubFactory.saveBuilding($rootScope.copy)
                .then(
                    function( returned ){
                        building.IsDirty = false;
                        building.edit = false;
                        building.isNew = true;
                        angular.extend(building, returned)
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
                        angular.extend(campus, returned)
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
            function(){

                var rooms = locationHubFactory.rooms;
                var i = rooms.length;
                while(i--){
                    if(room.Key_id === rooms[i].Key_id){
                        var originalRoom = rooms[i];
                        break;
                    }
                }

                if(!adding){
                    var idx = convenienceMethods.arrayContainsObject(room.PrincipalInvestigators, pi, null, true);
                    room.PrincipalInvestigators.splice(idx,1);
                    //find the room in the factory collection of rooms, remove the pi from it as well
                    originalRoom.PrincipalInvestigators.splice(idx,1);
                }else{
                    room.PrincipalInvestigators.push(pi);
                    originalRoom.PrincipalInvestigators.push(pi);
                }
                pi.saving = false;
            },
            function(){
                pi.saving = false;
                var added = adding ? "added" : "removed";
                $scope.error = "The PI could not be " + added + ".  Please check your internet connection and try again.";
            }
        );

    }

}
