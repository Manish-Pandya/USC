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
