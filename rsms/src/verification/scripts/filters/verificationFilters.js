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
                   if(users[i].Roles[j].Name == "Lab Contact"){
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
