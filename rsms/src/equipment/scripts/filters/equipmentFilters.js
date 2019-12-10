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
    return function (equips, string) {
        if (!equips)
            return;
        if (!string)
            return equips;
        return equips.filter(function (c) {
            return c.EquipmentInspections && c.EquipmentInspections.length && c.EquipmentInspections.some(function (i) {
                return i.PrincipalInvestigators.some(function (i) { return i.User.Name.toLowerCase().indexOf(string.toLowerCase()) > -1; });
            });
        });
    };
})
    .filter("status", function () {
    return function (equips, filterStatus) {
        if (!equips)
            return;
        if (!filterStatus)
            return equips;
        return equips.filter(function (c) {
            return c.EquipmentInspections && c.EquipmentInspections.length && c.EquipmentInspections.some(function (i) {
                return i.Status && i.Status.toLowerCase().indexOf(filterStatus.Data.toLowerCase()) > -1;
            });
        });
    };
})
    .filter("statusFilterArray", function () {
    return function (objs, uncert, isCurrentYear, isPreviousYear) {
        if (!objs)
            return;
        return objs.filter(function (obj) {
            if (uncert) {
                return obj.uncertified == uncert;
            }
            else if (isCurrentYear) {
                return obj.currentYear == isCurrentYear;
            }
            else if (isPreviousYear) {
                return obj.previousYear == isPreviousYear;
            }
        });
    };
})
    .filter("equipmentYear", function () {
    return function (equips, dateString, uncertified, showInactive) {
        if (!equips) {
            return;
        }
        else if (!dateString) {
            return equips;
        }
        else if (uncertified) {
            var year = new Date().getFullYear().toString();
            return equips.filter(function (e) {
                return e.EquipmentInspections.every(function (i) {
                    // true if not certified in current year
                    return i.Is_uncertified || !i.Certification_date || i.Certification_date.indexOf(dateString) == -1;
                });
            });
        }
        else if (showInactive) {
            return equips.filter(function (e) {
                return !e.Is_active;
            });
        }
        return equips.filter(function (c) {
            return c.EquipmentInspections.some(function (i) {
                return !i.Is_uncertified && ((i.Certification_date && i.Certification_date.indexOf(dateString) > -1) || (i.Fail_date && i.Fail_date.indexOf(dateString) > -1)
                    || parseInt(dateString) > new Date().getFullYear() && (i.Due_date.indexOf(dateString) != -1));
            });
        });
    };
})
    .filter("equipmentInspectionYear", function () {
    return function (inspections, dateString, uncertified, showInactive) {
        if (!inspections)
            return;
        if (!dateString)
            return inspections;

        let insps = inspections.filter(function (i) {
            return uncertified
                // Is uncertified
                ? i.Is_uncertified
                //  OR has NO Certification date
                //      AND (has NO fail date OR was failed this year)
                    || (!i.Certification_date && (!i.Fail_date || i.Fail_date.indexOf(dateString) != -1))
                // Is NOT uncertified
                : !i.Is_uncertified
                    // AND
                    && (
                        // Is certified this year
                        (i.Certification_date && i.Certification_date.indexOf(dateString) > -1)
                        // OR is due this year
                        || (i.Due_date && i.Due_date.indexOf(dateString) > -1)
                        // Or is failed this year
                        || (i.Fail_date && i.Fail_date.indexOf(dateString) > -1)
                );
            });

        // Inactive inspections may not match date filtering
        if( showInactive && !insps.length) {
            // If none match while showing Inactive inspections,
            //    just sort by Date_created and match the first item
            insps = [inspections.sort(function (a, b) {
                    return a.Date_created > b.Date_created;
                })[0]];
        }

        return insps;
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
        if (collection && !Array.isArray(collection))
            return [collection];
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
})
    .filter("activeWhenInspected", function () {
    return function (pis, insp) {
        if (!pis)
            return;
        return pis.filter(function (pi) {
            if (!pi.Is_active) {
                console.log(pi.Date_last_modified, insp.Date_created);
            }
            return pi.Is_active || pi.Date_last_modified >= insp.Date_created;
        });
    };
})

.factory('roomSortService', function(){
    let room_service = {
        // Tokenize parts, normalize, sort
        tokenizer_regexp: /\d+|\D+/g,

        get_room: function get_room(equipment){
            return equipment.EquipmentInspections[equipment.EquipmentInspections.length - 1].Room;
        },

        tokenize_room: function tokenize_room( room ){
            let tokens = [];
            if( room.Building ){
                tokens.push( room.Building.Name );
            }

            return tokens.concat( room_service.tokenize_string(room.Name) )
        },

        tokenize_string: function tokenize_string( str ){
            if( !str ) return [];
            return str.match(room_service.tokenizer_regexp);
        },

        compareToken: function compareToken( a, b ){
            if( a == b ) return 0;
            if( !a ) return -1;
            if( !b ) return  1;

            let a_nan = isNaN(a);
            let b_nan = isNaN(b);

            let a_val = a;
            let b_val = b;

            if( a_nan == b_nan ){
                // same class

                if( a_nan ){
                    // Strings, so compare naturally
                    return a_val.localeCompare(b_val);
                }
                else {
                    // Numeric; Compare as numbers rather than strings
                    return parseInt(a) > parseInt(b) ? 1 : -1;
                }
            }
            else {
                // different classes; convert to string and hope for best
                a_val = '' + a;
                b_val = '' + b;
                return a_val.localeCompare(b_val);
            }

        },

        token_sort: function token_sort( a_tokens, b_tokens ){
            if( a_tokens == b_tokens ) return 0;
            if( !a_tokens ) return -1;
            if( !b_tokens ) return  1;

            // Compare each paired token
            let product = [];

            let max = Math.max( a_tokens.length, b_tokens.length );
            for( let i = 0; i < max; i++){
                product[i] = room_service.compareToken( a_tokens[i], b_tokens[i] );
            }

            // Compute final equality from parts
            for( let i = 0; i < product.length; i++ ){
                if( product[i] !== 0 ){
                    return product[i];
                }
            }

            return 0;
        },

        sort_equipment: function sort( a, b ){
            if( a == b ) return 0;
            if( !a ) return -1;
            if( !b ) return  1;

            // Map equipments to their most-recent inspection's Room
            // And use that room as their sort value
            let a_room = room_service.get_room(a);
            let b_room = room_service.get_room(b);

            // Tokenize each room
            let a_tokens = room_service.tokenize_room(a_room);
            let b_tokens = room_service.tokenize_room(b_room);

            return room_service.token_sort(a_tokens, b_tokens);
        }
    };

    return room_service;
})
.filter('orderEquipmentByRoom', function(roomSortService){
    return function( equips ){
        if( !equips ) return equips;
        return equips.sort( roomSortService.sort_equipment );
    };
})
;
