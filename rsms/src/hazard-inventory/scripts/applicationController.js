'use strict';

angular
    .module('applicationControllerModule', ['rootApplicationController'])
    .factory('applicationControllerFactory', function applicationControllerFactory(modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods, rootApplicationControllerFactory) {
        var ac = rootApplicationControllerFactory;
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        store.$q = $q;

        ac.getAllPIs= function()
        {
            return this.getAllUsers()
                .then(
                    function(){
                        return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
                    }
                )

        }

        ac.getAllUsers = function()
        {
            return dataSwitchFactory.getAllObjects('User');
        }

        ac.getAllHazardDtos = function(id, roomId){

            var urlSegment = "getHazardRoomDtosByPIId&id="+id;
            if(roomId) urlSegment = urlSegment +"room="+roomId;

            return genericAPIFactory.read( urlSegment )
                    .then(
                        function( returnedPromise ){
                            var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( hazards );
                            return store.get( 'HazardDto' );
                        }
                    );
        }

        ac.handleHazardChecked = function(hazardDto){
            var copy = new window.HazardDto();
            copy.Class                     = hazardDto.Class;
            copy.HasChildren               = hazardDto.HasChildren;
            copy.Hazard_id                 = hazardDto.Hazard_id;
            copy.Hazard_name               = hazardDto.Hazard_name;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.Stored_only               = hazardDto.Stored_only;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.IsPresent                 = hazardDto.IsPresent;

            copy.InspectionRooms = [];
            for(var i =0; i < hazardDto.InspectionRooms.length; i++){
                copy.InspectionRooms[i] = this.copyInpectionRoom( hazardDto.InspectionRooms[i], copy.IsPresent );
            }


            hazardDto.IsPresent = !hazardDto.IsPresent;
            this.clearError();
            console.log(copy);
            this.save(copy)
                .then(
                    function(){
                        hazardDto.IsPresent = copy.IsPresent;
                    },
                    function(){
                        ac.setError("Something went wrong.");
                    }
                )

        }

        ac.saveHazardDto = function(){
        }

        ac.savePIHazardRoom = function(room, hazard, changed){
            var copy = ac.copyInpectionRoom(room);
            this.clearError();
            console.log(copy);

            //the room has been added or removed, as opposed to having its status changed
            if(changed){

            }

            this.save(copy)
                .then(
                    function(){
                        angular.extend()
                        ac.evaluateHazardPresent(hazardDto);
                    },
                    function(){
                        ac.setError("Something went wrong.");
                    }
                )
        }

        ac.copyInpectionRoom = function(room, containsHazard){
            var copy = new window.PIHazardRoomDto();
            copy.Class                     = room.Class;
            copy.Principal_investigator_id = room.Principal_investigator_id;
            copy.ContainsHazard            = room.ContainsHazard;
            copy.Hazard_id                 = room.Hazard_id;
            copy.Room_id                   = room.Room_id;
            copy.Status                    = room.Status;

            if(containsHazard != null){
                copy.ContainsHazard = containsHazard;
                console.log('setting to parent value');
            }else{
                copy.ContainsHazard = room.ContainsHazard;
            }
            return copy;
        }

        ac.evaluateHazardPresent = function(hazardDto){
            var i = hazardDto.InspectionRooms.length;
            hazardDto.IsPresent = false;
            //hazardDto.Status = "In Use";

            while(i--){
                if(hazardDto.InspectionRooms[i].ContainsHazard == true){
                    hazardDto.IsPresent = true;
                }
            }
        }

        return ac;
    });
