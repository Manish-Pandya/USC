angular
    .module('HazardInventory')
    .filter("Sub_rooms", function(){
        return function(rooms, bool){
            if(!rooms)return;
            var matches = [];
            var i = rooms.length;
            var atLeastOneFalse = false;
            while(i--){
                if(!matches.hasOwnProperty(room.Building_name)){
                    matches[room.Building_name]=[];
                }
                if(room.ContainsHazard){
                    matches[room.Building_name].unshift(room);
                }else{
                    atLeastOneFalse = true;
                }

            }
            if(matches.length == rooms.legth)return null;
            return matches;
        }
    })
