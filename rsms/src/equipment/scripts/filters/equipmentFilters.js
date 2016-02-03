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
        /*
        Passing '' for dateString will strip inspections where the dateProp value is not set.
        For example, stripping equipment inspections where the Certification_date is null.
        Passing '*' for dateString will strip inspections where the dateProp value exists.
        For example, stripping equipment inspections where the Certification_date is is set.
        */
        return function(equipments, dateString, dateProp){
            if(!equipments) {
                return;
            } else if (dateString != '' && (!dateString || !dateProp)) {
                return equipments;
            }
            var year = dateString.split('-')[0];
            var matches = [];
            var i = equipments.length;
            while(i--){
                if (equipments[i].EquipmentInspections) {
                    var j = equipments[i].EquipmentInspections.length;
                    while(j--){
                        if (equipments[i].EquipmentInspections[j][dateProp]){
                            console.log(dateProp, dateString, dateString == "*", equipments[i].EquipmentInspections[j][dateProp]);
                            if (dateString != "*" && equipments[i].EquipmentInspections[j][dateProp].indexOf(year) > -1) {
                                matches.unshift(equipments[i]);
                            }
                        }
                    }
                }
            }
            return matches;
        }
    })
