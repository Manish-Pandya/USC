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
                    var matched = false;
                    var j = equipments[i].EquipmentInspections.length;
                    while(j--){
                        if (equipments[i].EquipmentInspections[j].Room.Building && equipments[i].EquipmentInspections[j].Room.Building.Name.toLowerCase().indexOf(string.toLowerCase()) > -1) {
                            matched = true;
                        }
                    }
                    if (matched) {
                        matches.unshift(equipments[i]);
                    }
                }
            }
            return matches;
        }
    })
    .filter("matchEquipmentCampus", function(){
        return function (equipments, campus_id) {
            if(!equipments) {
                return;
            } else if (!campus_id) {
                return equipments;
            }
            var matches = [];
            var i = equipments.length;
            while (i--) {
                if (equipments[i].EquipmentInspections && equipments[i].EquipmentInspections.length && equipments[i].EquipmentInspections[0].Room) {
                    var building = equipments[i].EquipmentInspections[0].Room.Building;
                    if (building && building.Campus_id == campus_id) matches.unshift(equipments[i]);
                }
            }
            return matches;
        }
    })
    .filter("matchInspectionDate", function () {
        /*
        Match either Certification_date or Due_date to dateString.
        If dateString is currentYear, also accept matches of null Certification_date and Due_date.
        */
        return function (inspections, dateString, currentYear) {
            console.log(dateString, currentYear, inspections.length);
            if (!inspections) {
                return;
            } else if (dateString != '' && (!dateString)) {
                return inspections;
            } else if (dateString == "Not Yet Certified") {
                return inspections.filter(function (i) {
                    console.log(i);
                    return !i.Certification_date;
                });
            }
            var year = dateString.split('-')[0];
            var matches = [];
            var i = inspections.length;
            while (i--) {
                var insp = inspections[i];
                if ((insp.Certification_date && insp.Certification_date.indexOf(dateString) > -1) || (insp.Due_date && insp.Due_date.indexOf(dateString) > -1)) {
                    matches.push(insp);
                } else if (dateString.split("-")[0] == currentYear && !insp.Certification_date && !insp.Due_date) {
                    matches.push(insp);
                }
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

        dateProp can optionally be an array to test dateString against multiple props.
        */
        return function (equipments, dateString, dateProp, currentYear) {
            if(!equipments) {
                return;
            } else if (dateString != '' && (!dateString || !dateProp)) {
                return equipments;
            } else if (dateString == "Not Yet Certified") {
                
                return equipments.filter(function (e) {
                    return e.EquipmentInspections.every(function (i) {
                        return !i.Certification_date;
                    })
                });
            }
            
            var year = dateString.split('-')[0];
            var matches = [];
            var i = equipments.length;
            while(i--){
                if (equipments[i].EquipmentInspections) {
                    var j = equipments[i].EquipmentInspections.length;
                    var matched = false;
                    while (j--) {
                        if (Array.isArray(dateProp)) {
                            for (var n = 0; n < dateProp.length; n++) {
                                if (equipments[i].EquipmentInspections[j].hasOwnProperty(dateProp[n])) {
                                    var inspectionDate = equipments[i].EquipmentInspections[j][dateProp[n]];
                                    if ((!inspectionDate && dateString == '*') || (inspectionDate && inspectionDate.indexOf(year) > -1)) {
                                        matched = true;
                                        continue;
                                    }
                                }
                            }
                        } else if ( equipments[i].EquipmentInspections[j].hasOwnProperty(dateProp) ) {
                            var inspectionDate = equipments[i].EquipmentInspections[j][dateProp];
                            if ( (!inspectionDate && dateString == '*') || (inspectionDate && inspectionDate.indexOf(year) > -1) ) {
                                matched = true;
                                continue;
                            }
                        }
                        if (!equipments[i].EquipmentInspections[j].Certification_date && equipments[i].EquipmentInspections[j].Due_date) {
                            if (parseInt(equipments[i].EquipmentInspections[j].Due_date.split("-")[0]) < parseInt(currentYear)) {
                                matched = true;
                            }
                        } else if (!equipments[i].EquipmentInspections[j].Certification_date && !equipments[i].EquipmentInspections[j].Due_date) {
                            if (dateString == currentYear) {
                                matched = true;
                            }
                        }
                    }
                }
                //console.log(dateString, currentYear, equipments[i].Key_id, equipments[i].EquipmentInspections.length, matched);
                if (matched) {
                    matches.unshift(equipments[i]);
                }
            }
            return matches;
        }
    })
