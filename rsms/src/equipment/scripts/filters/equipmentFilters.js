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
    .filter("matchEquipmentBuilding", function(){
        return function(equipments, string){
            if(!equipments) {
                return;
            } else if (!string) {
                return equipments;
            }
            var matches = [];
            var i = equipments.length;
            while(i--){
                if (equipments[i].EquipmentInspections) {
                    var j = equipments[i].EquipmentInspections.length;
                    while(j--){
                        if(equipments[i].EquipmentInspections[j].Room.Building && equipments[i].EquipmentInspections[j].Room.Building.Name.toLowerCase().indexOf(string) > -1) matches.unshift(equipments[i]);
                    }
                }
            }
            return matches;
        }
    })
    .filter("matchEquipmentCampus", function(){
        return function(equipments, string){
            if(!equipments) {
                return;
            } else if (!string) {
                return equipments;
            }
            var matches = [];
            var i = equipments.length;
            while(i--){
                if(equipments[i].Room && equipments[i].Room.Building.Campus.Name && equipments[i].Room.Building.Campus.Name.toLowerCase().indexOf(string) > -1) matches.unshift(equipments[i]);
            }
            return matches;
        }
    })
    .filter("matchEquipmentDate", function(){
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
                        if ( equipments[i].EquipmentInspections[j].hasOwnProperty(dateProp) ) {
                            var inspectionDate = equipments[i].EquipmentInspections[j][dateProp];
                            if ( (!inspectionDate && dateString == '*') || (inspectionDate && inspectionDate.indexOf(year) > -1) ) {
                                matches.unshift(equipments[i]);
                                break;
                            }
                        }
                    }
                }
            }
            return matches;
        }
    })
