angular
    .module('HazardInventory')
    .filter("Is_equipment", function(){
        return function(hazards, bool){
            if(!hazards)return;
            var matches = [];
            var i = hazards.length;
            while(i--){
                if(hazards[i].Is_equipment == bool)matches.unshift(hazards[i]);
            }
            return matches;
        }
    })
