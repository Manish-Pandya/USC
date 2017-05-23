angular
    .module('EquipmentModule')
    .filter("matchBuilding", function () {
    return function (rooms, string) {
        if (!rooms)
            return;
        var matches = [];
        var i = rooms.length;
        while (i--) {
            if (rooms[i].Building && rooms[i].Building.Name == string)
                matches.unshift(rooms[i]);
        }
        return matches;
    };
})
    .filter("matchEquipmentBuilding", function () {
    return function (equipments, string) {
        if (!equipments) {
            return;
        }
        else if (!string) {
            return equipments;
        }
        var matches = [];
        var i = equipments.length;
        while (i--) {
            if (equipments[i].EquipmentInspections) {
                var matched = false;
                var j = equipments[i].EquipmentInspections.length;
                while (j--) {
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
    };
})
    .filter("matchEquipmentCampus", function () {
    return function (equipments, campus_id) {
        if (!equipments) {
            return;
        }
        else if (!campus_id) {
            return equipments;
        }
        var matches = [];
        var i = equipments.length;
        while (i--) {
            if (equipments[i].EquipmentInspections && equipments[i].EquipmentInspections.length && equipments[i].EquipmentInspections[0].Room) {
                var building = equipments[i].EquipmentInspections[0].Room.Building;
                if (building && building.Campus_id == campus_id)
                    matches.unshift(equipments[i]);
            }
        }
        return matches;
    };
})
    .filter("matchInspectionDate", function () {
    /*
    Match either Certification_date or Due_date to dateString.
    If dateString is currentYear, also accept matches of null Certification_date and Due_date.
    */
    return function (inspections, dateString, currentYear) {
        //console.log(dateString, currentYear, inspections.length);
        if (!inspections) {
            return;
        }
        else if (dateString != '' && (!dateString)) {
            return inspections;
        }
        else if (dateString == "Not Yet Certified") {
            return inspections.filter(function (i) {
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
            }
            else if (dateString.split("-")[0] == currentYear && !insp.Certification_date && !insp.Due_date) {
                matches.push(insp);
            }
        }
        return matches;
    };
})
    .filter("matchEquipmentDate", function () {
    /*
    Passing '' for dateString will strip inspections where the dateProp value is not set.
    For example, stripping equipment inspections where the Certification_date is null.
    Passing '*' for dateString will strip inspections where the dateProp value exists.
    For example, stripping equipment inspections where the Certification_date is is set.

    dateProp can optionally be an array to test dateString against multiple props.
    */
    return function (equipments, dateString, dateProp, currentYear) {
        if (!equipments) {
            return;
        }
        else if (dateString != '' && (!dateString || !dateProp)) {
            return equipments;
        }
        else if (dateString == "Not Yet Certified") {
            return equipments.filter(function (e) {
                return e.EquipmentInspections.every(function (i) {
                    return !i.Certification_date;
                });
            });
        }
        var year = dateString.split('-')[0];
        var matches = [];
        var i = equipments.length;
        while (i--) {
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
                    }
                    else if (equipments[i].EquipmentInspections[j].hasOwnProperty(dateProp)) {
                        var inspectionDate = equipments[i].EquipmentInspections[j][dateProp];
                        if ((!inspectionDate && dateString == '*') || (inspectionDate && inspectionDate.indexOf(year) > -1)) {
                            matched = true;
                            continue;
                        }
                    }
                    if (!equipments[i].EquipmentInspections[j].Certification_date && equipments[i].EquipmentInspections[j].Due_date) {
                        if (parseInt(equipments[i].EquipmentInspections[j].Due_date.split("-")[0]) < parseInt(currentYear)) {
                            matched = true;
                        }
                    }
                    else if (!equipments[i].EquipmentInspections[j].Certification_date && !equipments[i].EquipmentInspections[j].Due_date) {
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
    };
})
    .filter("pi", function () {
    return function (cabs, string) {
        if (!cabs)
            return;
        if (!string)
            return cabs;
        return cabs.filter(function (c) {
            return c.EquipmentInspections && c.EquipmentInspections.length && c.EquipmentInspections.some(function (i) {
                return i.PrincipalInvestigators.some(function (i) { return i.User.Name.toLowerCase().indexOf(string.toLowerCase()) > -1; });
            });
        });
    };
})
    .filter("status", function () {
    return function (cabs, string) {
        if (!cabs)
            return;
        if (!string)
            return cabs;
        return cabs.filter(function (c) {
            return c.EquipmentInspections && c.EquipmentInspections.length && c.EquipmentInspections.some(function (i) {
                return i.Status && i.Status.toLowerCase().indexOf(string.toLowerCase()) > -1;
            });
        });
    };
})
    .filter("cabinetYear", function () {
    return function (cabs, dateString, uncertified) {
        if (!cabs) {
            return;
        }
        else if (!dateString) {
            return cabs;
        }
        else if (uncertified) {
            return cabs.filter(function (e) {
                return e.EquipmentInspections.every(function (i) {
                    return !i.Certification_date;
                });
            });
        }
        return cabs.filter(function (c) {
            return c.EquipmentInspections.some(function (i) {
                return (i.Certification_date && i.Certification_date.indexOf(dateString) > -1) || (i.Due_date && i.Due_date.indexOf(dateString) > -1);
            });
        });
    };
})
    .filter("cabinetInspectionYear", function () {
    return function (inspections, dateString, uncertified) {
        if (!inspections)
            return;
        if (!dateString)
            return inspections;
        return inspections.filter(function (i) {
            return uncertified ? !i.Certification_date : (i.Certification_date && i.Certification_date.indexOf(dateString) > -1) || (i.Due_date && i.Due_date.indexOf(dateString) > -1);
        });
    };
})
    .filter("getSharedRooms", function () {
    return function (pis) {
        if (!pis)
            return;
        var allRooms = [];
        pis.forEach(function (pi) {
            if (!pi.Rooms)
                return;
            pi.Rooms.forEach(function (r) {
                var index = parseInt(r.Key_id);
                if (!allRooms[index]) {
                    allRooms[index] = [];
                }
                r.Bulding_name = r.Building_name || r.Building.Room.Name;
                allRooms[index].push(r);
            });
        });
        if (!allRooms || !allRooms.length)
            return;
        var filtered = allRooms.filter(function (i) {
            return i.length == pis.length;
        });
        filtered.forEach(function (rooms, idx, arr) {
            arr[idx] = rooms[0];
        });
        return filtered;
    };
})
    .filter("hasMoved", function () {
    return function (collection, prop) {
        return collection && collection.length == 1 ? collection :
            collection.every(function (i) {
                return !Array.isArray(i[prop]) ? _.isEqual(i[prop], collection[0][prop]) : i[prop].every(function (j) { return _.isEqual(_.omit(j, "$$hashKey"), _.omit(i[prop][0], "$$hashKey")); });
            }) ? [collection[0]] : collection;
    };
})
    .filter("nameFilter", function () {
    return function (pis, string) {
        if (!string)
            return pis;
        return pis.filter(function (pi) {
            return !string || ((pi.Name && pi.Name.indexOf(string) != -1) || (pi.User && pi.User.Name && pi.User.Name.indexOf(string) != -1));
        });
    };
})
    .filter("piSelected", function () {
    return function (pis, selectedPis) {
        if (!pis || !selectedPis)
            return;
        return pis.filter(function (pi) {
            return _.findIndex(selectedPis, function (p) { return pi.UID == p.UID; }) == -1;
        });
    };
});
