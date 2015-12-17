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
    .filter("matchCertDate", function(){
        return function(equipments, string){
            if(!equipments) {
                return;
            } else if (!string) {
                return equipments;
            }
            var matches = [];
            var i = equipments.length;
            while(i--){
                console.log(string);
                if(equipments[i].Certification_date.indexOf(string) > -1) matches.unshift(equipments[i]);
            }
            return matches;
        }
    })
