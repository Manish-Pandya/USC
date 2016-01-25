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
            dataStore.HazardDto = null;
            var urlSegment = "getHazardRoomDtosByPIId&id="+id;
            if(roomId) urlSegment = urlSegment +"&roomId="+roomId;

            return genericAPIFactory.read( urlSegment )
                    .then(
                        function( returnedPromise ){
                            var hazards = modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                            store.store( hazards );
                            return store.get( 'HazardDto' );
                        }
                    );
        }

        ac.handleHazardChecked = function(hazardDto, hazard){
            var copy = new window.HazardDto();
            copy.Class                     = hazardDto.Class;
            copy.MasterHazardId            = hazard.Key_id;
            copy.HasChildren               = hazardDto.HasChildren;
            copy.Hazard_id                 = hazardDto.Hazard_id;
            copy.Hazard_name               = hazardDto.Hazard_name;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.Stored_only               = hazardDto.Stored_only;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.Principal_investigator_id = hazardDto.Principal_investigator_id;
            copy.IsPresent                 = hazardDto.IsPresent;

            copy.InspectionRooms = [];

            //the key ids of all the hazards that are direct children of the root hazard, our "branch" level hazards.
            var branchLevelIDs = Constants.BRANCH_HAZARD_IDS;

            //see if our hazard has a parent that isn't a branch level hazard
            if(branchLevelIDs.indexOf(hazardDto.Parent_hazard_id)<0){
                var parentHazard = dataStoreManager.getById("HazardDto", hazardDto.Parent_hazard_id);
            }

            for(var i =0; i < hazardDto.InspectionRooms.length; i++){
                hazardDto.InspectionRooms[i].MasterHazardId = hazard.Key_id;
                if(!parentHazard){
                    copy.InspectionRooms[i] = this.copyInpectionRoom( hazardDto.InspectionRooms[i], copy.IsPresent );
                }
                //make sure we don't put this hazard in a room that its parent isn't in
                else{
                    var isPresent = false;
                    //hazard can only be put in room if it has been checked AND the parent is in the same room
                    if(copy.IsPresent && parentHazard.InspectionRooms[i].ContainsHazard){
                        isPresent = true;
                    }
                    copy.InspectionRooms[i] = this.copyInpectionRoom( hazardDto.InspectionRooms[i], isPresent );
                }
            }
            console.log(copy);

            hazardDto.IsPresent = !hazardDto.IsPresent;
            this.clearError();
            this.save(copy)
                .then(
                    function(){
                        hazardDto.IsPresent = copy.IsPresent;
                        for(var i =0; i < hazardDto.InspectionRooms.length; i++){
                            hazardDto.InspectionRooms[i].ContainsHazard = copy.InspectionRooms[i].ContainsHazard;
                            hazardDto.InspectionRooms[i].Status = copy.InspectionRooms[i].Status;
                            hazardDto.InspectionRooms[i].storeOnly = copy.InspectionRooms[i].storeOnly;
                            ac.evaluateHazardPresent(hazardDto);
                        }

                    },
                    function(){
                        ac.setError("Something went wrong.");
                    }
                )

        }

        ac.saveHazardDto = function(){
        }

        ac.savePIHazardRoom = function(room, hazard, changed, parent){
            var copy = ac.copyInpectionRoom(room);
            copy.MasterHazardId = parent.Key_id;
            this.clearError();

            //the room has been added or removed, as opposed to having its status changed
            if(changed){
                room.ContainsHazard = !room.ContainsHazard;
            }

            //make sure that we don't need the user to confirm the save
            if(ac.needsConfirmation(room, hazard)){
               var confirm = window.confirm(hazard.Hazard_name + " has SubHazards hazards in " + room.Building_name + ", room" +room.Room_name + ". Are you sure you want to apply your change to all its SubHazards?");
               if(confirm == false){
                if(hazard.needsStoredOnlyConfimration){
                    room.Status = "In Use";
                    room.storedOnly = false;
                }
                return;
               }
            }

            this.save(copy)
                .then(
                    function(){
                        angular.extend(room, copy)
                        ac.evaluateHazardPresent(hazard);
                    },
                    function(){
                        ac.setError("Something went wrong.");
                    }
                )
        }

        ac.needsConfirmation = function(room, hazard){
            //get the index of the room in the hazards collection
            var i = hazard.InspectionRooms.length;
            while(i--){
                if(hazard.InspectionRooms[i].Room_id == room.Room_id){
                    var idx = i;
                    //break;
                }
            }

            var i = hazard.ActiveSubHazards.length;
            while(i--){
                //if we are removing the hazard from the room, warn the user that its children will be removed
                if(!hazard.InspectionRooms[idx].ContainsHazard){
                    if(hazard.ActiveSubHazards[i].InspectionRooms[idx].ContainsHazard){
                        hazard.needs = true;
                        return true;
                    }
                }
                //if we are setting the hazard to "Stored Only" in the room, warn the user that we will be setting its children to same
                else if(hazard.InspectionRooms[idx].Status == Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY){
                    if(hazard.ActiveSubHazards[i].InspectionRooms[idx].ContainsHazard && hazard.ActiveSubHazards[i].InspectionRooms[idx].Status != Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY){
                        hazard.needsStoredOnlyConfimration = true;
                        return true;
                    }
                }
            }
        }

        ac.copyInpectionRoom = function(room, containsHazard){
            var copy = new window.PIHazardRoomDto();
            copy.Class                     = room.Class;
            copy.Principal_investigator_id = room.Principal_investigator_id;
            copy.ContainsHazard            = room.ContainsHazard;
            copy.Hazard_id                 = room.Hazard_id;
            copy.Room_id                   = room.Room_id;
            copy.Status                    = room.Status;
            copy.MasterHazardId            = room.MasterHazardId;

            if(containsHazard != null){
                copy.ContainsHazard = containsHazard;
            }else{
                copy.ContainsHazard = room.ContainsHazard;
            }
            return copy;
        }

        ac.evaluateHazardPresent = function(hazardDto, notParent){
            var i = hazardDto.InspectionRooms.length;
            hazardDto.IsPresent = false;
            hazardDto.Status = "In Use";

            var storedOnly = true;

            while(i--){
                if(!notParent){
                    if(hazardDto.InspectionRooms[i].ContainsHazard == true){
                        hazardDto.IsPresent = true;
                        if(hazardDto.InspectionRooms[i].Status != Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY){
                            storedOnly = false;
                        }
                    }
                }else{
                    var parent = dataStoreManager.getById("HazardDto", hazardDto.Parent_hazard_id);
                    if(parent.InspectionRooms[i].ContainsHazard == false){
                        hazardDto.InspectionRooms[i].ContainsHazard = false;
                    }else{
                        if(parent.InspectionRooms[i].Status == Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY){
                            hazardDto.InspectionRooms[i].Status = Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY;
                            if(hazardDto.InspectionRooms[i].ContainsHazard){
                                hazardDto.IsPresent = true;
                            }
                        }else{
                            if(hazardDto.InspectionRooms[i].ContainsHazard){
                                hazardDto.IsPresent = true;
                                if(!hazardDto.InspectionRooms[i].Status != Constants.HAZARD_PI_ROOM.STATUS.STORED_ONLY){
                                    storedOnly = false;
                                }
                            }
                        }
                    }
                }
            }
            if(hazardDto.IsPresent){
                hazardDto.Stored_only = storedOnly;
            }else{
                hazardDto.Stored_only = false;
            }

            if(hazardDto.ActiveSubHazards){
                var i = hazardDto.ActiveSubHazards.length;
                while(i--){
                     ac.evaluateHazardPresent(hazardDto.ActiveSubHazards[i], true);
                }
            }
        }

        ac.getBuildings = function(id, roomId){
            var urlSegment = "getBuildingsByPIID&id="+id;
            if(roomId) urlSegment = urlSegment +"&roomId="+roomId;

            return genericAPIFactory.read( urlSegment )
                    .then(
                        function( returnedPromise ){
                            //buildings will only be displayed and will be reloaded whenever a new parent is selected.  there is no need to isntantiate or cache them
                            var buildings = returnedPromise.data;
                            return buildings;
                        }
                    );
        }

        ac.getPIs = function(hazardDto, room){

            var urlSegment = "getPisByHazardAndRoomIDs";
            var ids = [];

            //specify a single room
            if(room){
                //we've passed a room object from the top of the view, where we display all of the pis rooms and buildings
                if(room.Class == "Room"){
                    var id = room.Key_id
                }
                //we've passed a PIHazardRoomDto object from a HazardDtos collection of inspection rooms
                else{
                    var id = room.Room_id
                }
                urlSegment += "&"+$.param({roomIds:[id]});
            }

            if(hazardDto){
                //we didn't specify a single room, so get the ids for each room in the hazards inspection rooms
                if(!room){
                    var i = hazardDto.InspectionRooms.length;
                    while(i--){
                        ids.push(hazardDto.InspectionRooms[i].Room_id);
                    }
                    urlSegment += "&"+$.param({roomIds:ids});
                }
                urlSegment += "&hazardId="+hazardDto.Hazard_id;
            }

            return genericAPIFactory.read( urlSegment )
                    .then(
                        function( returnedPromise ){
                           return  modelInflatorFactory.instateAllObjectsFromJson( returnedPromise.data );
                        }
                    );
        }

        ac.savePI = function(pi, copy){
            this.save(copy)
                .then(
                    function(returned){
                        pi.Inspection_notes = returned.Inspection_notes;
                        pi.editNote = false;
                    },
                    function(){
                        ac.setError("Something went wrong.");
                    }
                )
        }

        ac.saveInspectionRoomRelationship = function(inspection, room){
            if(typeof room.checked == 'undefined')room.checked = false;
                room.userChecked = room.checked;
                var deferred = $q.defer();
                var url = "saveInspectionRoomRelation&roomId="+room.Key_id+"&inspectionId="+inspection.Key_id+"&add="+room.checked;

                $rootScope.RoomSaving = genericAPIFactory.read(url).then(
                                        function(promise){
                                            room.IsDirty = false;
                                            return room;
                                          },
                                          function(promise){
                                            room.IsDirty = false;
                                            room.checked = !room.checked;
                                            deferred.reject();
                                          }
                                        );
                return $rootScope.RoomSaving;
        }

        ac.initialiseInspection = function(PI, inspectorIds, inspectionId, rad){
            //if we don't have a pi, get one from the server
            if(!inspectorIds)inspectorIds=[10];
            var url = 'initiateInspection&piId='+PI.Key_id+'&'+$.param({inspectorIds:inspectorIds});
            if(rad)url = url+"&rad=true";

            if(inspectionId) url+='&inspectionId='+inspectionId;
            var temp = this;
             $rootScope.InspectionSaving = genericAPIFactory.read(url).then(
                                              function( returned ){
                                                  var inspection = returned.data;
                                                  if(rad){
                                                    //navigate to checklist for rad inspection.
                                                    window.location = "../views/inspection/InspectionChecklist.php#?inspection="+inspection.Key_id;
                                                  }else{
                                                    inspection.Is_new = true;
                                                    PI.Inspections.push(inspection);
                                                  }
                                              }
                                        );
        }

        return ac;
    });
