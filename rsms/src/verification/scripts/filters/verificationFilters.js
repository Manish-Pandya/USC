angular.module('filtersApp',[])
    .filter('contactOrPersonnel', function() {
      return function(users, bool) {
            if(!users)return;
            var matches = [];
            var i = users.length;
            while(i--){
               var isContact = false;
               var j = users[i].Roles.length;
               while(j--){
                   if(users[i].Roles[j].Name == Constants.ROLE.NAME.LAB_CONTACT){
                     isContact = true;
                   }
               }
               if(bool === isContact)matches.push(users[i]);
            }
            return matches;
      };
    })
    .filter('pendingContactOrPersonnel',function(){
        return function(changes, role){
            if(!changes)return;
            var changedThings = [];
            var i = changes.length;
            while(i--){
                if(changes[i].Role && (!role || changes[i].Role == role)) {
                    changedThings.push(changes[i]);
                }
            }
            return changedThings;
        }
    })
    .filter('tel', function () {
        return function (phoneNumber) {
            if (!phoneNumber)
                return phoneNumber;

            return formatLocal('US', phoneNumber);
        }
    })
    .filter('hasNewStatus',function(){
        return function(changes, status){
            if(!changes)return;
            var changedThings = [];
            var i = changes.length;
            while(i--){
                if(changes[i].New_status && (!status || changes[i].New_status == status))changedThings.push(changes[i])
            }
            return changedThings;
        }
    })
    .filter('phoneChanges',function(){
        return function(changes){
            if(!changes)return;
            var phoneChanges = [];
            var i = changes.length;
            while(i--){
                if(changes[i].Emergency_phone)phoneChanges.push(changes[i])
            }
            return phoneChanges;
        }
    })
    .filter('activePendingRoomChangeOnly', function() {
        return function(array) {
                if(!array)return;
                var activeObjects = [];

                var i = array.length;
                while(i--){
                    if(array[i].PendingRoomChange.Is_active)activeObjects.unshift(array[i]);
            }
            return activeObjects;
        };
    })
    .filter('activePendingUserChangeOnly', function() {
        return function(array) {
                if(!array)return;
                var activeObjects = [];
                var i = array.length;
                while(i--){
                    if(array[i].PendingUserChange.Is_active)activeObjects.unshift(array[i]);
            }
            return activeObjects;
        };
    })
    .filter('hazardRoomFilter', function () {
        return function (hazards, roomId, flip) {
            if (!hazards) return;
            if (!roomId) return hazards;
            var matchedHazards = [];
            var len = hazards.length;
            var parent = dataStoreManager.getById("HazardDto", hazards[0].Parent_hazard_id);
            parent.show = false;
            for (var i = 0; i < len; i++){
                var hazard = hazards[i];
                hazard.matchedForOtherPi = false;
                var roomLen = hazard.InspectionRooms.length;
                for (var x = 0; x < roomLen; x++) {
                    var room = hazard.InspectionRooms[x];
                    if (room.Room_id == roomId) {
                        var push;
                        if (!flip) {
                            push = false;
                            if (room.ContainsHazard || room.HasMultiplePis) {
                                parent.show = true;
                                push = true;
                            }
                            if (room.PendingHazardDtoChange && room.PendingHazardDtoChange.Key_id) {
                                parent.show = true;
                                push = true;
                            }
                        } else {
                            push = true;
                            if ((room.ContainsHazard || room.HasMultiplePis) || (room.PendingHazardDtoChange && room.PendingHazardDtoChange.Key_id)) {
                                push = false
                            }
                            
                        }
                        if (push) matchedHazards.push(hazard);

                        /*

                        if (!flip && (((room.ContainsHazard || room.HasMultiplePis)) && !hazard.ActiveSubHazards.length || (room.PendingHazardDtoChange && room.PendingHazardDtoChange.Key_id))){
                            if (room.HasMultiplePis) hazard.matchedForOtherPi = true;
                            //based on model, should only ever push each matched hazard once
                            matchedHazards.push(hazard);
                            parent.show = true;
                        }

                        if (flip && ( (!room.ContainsHazard && !room.HasMultiplePis) || (!room.ContainsHazard && room.PendingHazardDtoChange && !room.PendingHazardDtoChange.Key_id) ) ) {
                            if (room.HasMultiplePis) hazard.matchedForOtherPi = true;
                            

                            //based on model, should only ever push each matched hazard once
                            matchedHazards.push(hazard);
                        }
                        */
                    }
                }
            }
            return matchedHazards;
        }
    })
    .filter('roomIdMatches', function () { return function (things) { return things; }})
