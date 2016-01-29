angular
    .module('EquipmentModule')
    .filter("matchBuilding", function(){
        return function(rooms, string){
            if(!rooms)return;
            var matches = [];
            var i = rooms.length;
            while(i--){
                if(rooms[i].Building && rooms[i].Building.Name == string)matches.unshift(rooms[i]);
            }
            return matches;
        }
    })
    .filter("matchCabinetBuilding", function(){
        return function(cabinets, string){
            if(!cabinets) {
                return;
            } else if (!string) {
                return cabinets;
            }
            var matches = [];
            var i = cabinets.length;
            while(i--){
                if(cabinets[i].Room.Building && cabinets[i].Room.Building.Name.toLowerCase().indexOf(string) > -1) matches.unshift(cabinets[i]);
            }
            return matches;
        }
    })
    .filter("matchCabinetCampus", function(){
        return function(cabinets, string){
            if(!cabinets) {
                return;
            } else if (!string) {
                return cabinets;
            }
            var matches = [];
            var i = cabinets.length;
            while(i--){
                if(cabinets[i].Room && cabinets[i].Room.Building.Campus.Name && cabinets[i].Room.Building.Campus.Name.toLowerCase().indexOf(string) > -1) matches.unshift(cabinets[i]);
            }
            return matches;
        }
    })
    .filter("matchDate", function(){
        return function(equipments, dateString, dateProp){
            if(!equipments) {
                return;
            } else if (!dateString || !dateProp) {
                return equipments;
            }
            var year = dateString.split('-')[0];
            console.log(year, dateProp);
            var matches = [];
            var i = equipments.length;
            while(i--){
                var j = equipments[i].EquipmentInspections.length;
                while(j--){
                    if(equipments[i].EquipmentInspections[j][dateProp]){
                        if(equipments[i].EquipmentInspections[j][dateProp].indexOf(year) > -1) matches.unshift(equipments[i]);
                    }
                }
            }
            return matches;
        }
    })
