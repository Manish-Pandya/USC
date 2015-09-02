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
    .filter('tel', function () {
        return function (phoneNumber) {
            if (!phoneNumber)
                return phoneNumber;

            return formatLocal('US', phoneNumber);
        }
    })
    .filter('userChanges',function(){
        return function(changes){
            if(!changes)return;
            var userChanges = [];
            var i = changes.length;
            while(i--){
                if(changes[i].New_status)userChanges.push(changes[i])
            }
            return userChanges;
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
