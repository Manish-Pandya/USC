var monthNames = [ "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December" ];
var monthNames2 = [{val:"01", string:"January"},
                {val:"02", string:"February"},
                {val:"03", string:"March"},
                {val:"04", string:"April"},
                {val:"05", string:"May"},
                {val:"06", string:"June"},
                {val:"07", string:"July"},
                {val:"08", string:"August"},
                {val:"09", string:"September"},
                {val:"10", string:"October"},
                {val:"11", string:"November"},
                {val:"12", string:"December"}]
var getDate = function(time){
            Date.prototype.getMonthFormatted = function() {
                var month = this.getMonth();
                return month < 10 ? '0' + month : month; // ('' + month) for string result
            }

            // Split timestamp into [ Y, M, D, h, m, s ]
            var t = time.split(/[- :]/);

            // Apply each element to the Date function
            // create a new javascript Date object based on the timestamp
            var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

            var hours = date.getHours(); // hours part from the timestamp
            var minutes = date.getMinutes(); // minutes part from the timestamp
            var seconds = date.getSeconds(); // seconds part from the timestamp
            var month = date.getMonth()+1;
            var day = date.getDate();
            var year = date.getFullYear();

            // preserve initial zero
            month = month < 10 ? '0' + month : month;
            day = day < 10 ? '0' + day : day;

            // will display date in mm/dd/yyyy format
            var formattedTime = {};
            formattedTime.formattedString = month + '/' + day + '/' + year;
            formattedTime.year = year;
            formattedTime.monthString = monthNames[date.getMonth()];
            return formattedTime;
        }

var manageInspections = angular.module('manageInspections', ['cgBusy','convenienceMethodWithRoleBasedModule', 'once', 'ui.bootstrap'])
.filter('filterableInspectionStatus', function(){
    return function (statuses) {
        if( !statuses )
            return statuses;

        // RSMS-730: Omit 'Inspected' status from table filter
        return statuses.filter( s => s != 'INSPECTED' );
    }
})
.filter('toArray', function () {
    return function (object) {
        var array = [];
        for (var prop in object) {
            array.push(object[prop]);
        }
        return array;
    }
})
.filter('getDueDate', function () {
    return function (input) {
        var date = new Date(input);
        var duePoint = date.setDate(date.getDate() + 14);
        dueDate = new Date(duePoint).toISOString();
        return dueDate;
    };
})
.filter('getMonthName', function () {
    return function (input) {
        var i = monthNames2.length;
        while (i--) {
            if (input == monthNames2[i].val) return monthNames2[i].string;
        }
    };
})
.filter('onlyUnselected', function () {
    return function (inspectors, selectedInspectors) {
        if (!selectedInspectors) return inspectors;
        var unselectedInspectors = [];
        var selectedInsepctorIds = [];
        var i = selectedInspectors.length;
        while (i--) {
            selectedInsepctorIds.push(selectedInspectors[i].Key_id);
        }

        var j = inspectors.length;
        while (j--) {
            if (selectedInsepctorIds.indexOf(inspectors[j].Key_id) < 0) unselectedInspectors.push(inspectors[j]);
        }
        return unselectedInspectors;
    };
})
.directive('blurIt', function () {
    return {
        template: '<i class="icon-search></i>test"',
        replace:false,
        link: function (scope, element, attrs) {
            element.keyup(function (event) {
                if (event.which === 13 && element.val() != '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }

                //backspace and delete
                if ((event.which === 8 || event.which === 46) && element.val() == '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }
            });

            element.blur(function (event) {
                if (element.val() != '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }
            });
        }
    };
})

.factory('manageInspectionsFactory', function (convenienceMethods, $q, $rootScope) {
    var factory = {};
    factory.InspectionScheduleDtos = [];
    factory.Inspections = [];
    factory.currentYear;
    factory.years = [];
    factory.Inspectors = [];
    factory.minYear = 2015;
    factory.months = [];

    factory.getCurrentYear = function () {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if (this.years.length) {
            deferred.resolve(this.years);
        } else {
            var url = '../../ajaxaction.php?action=getCurrentYear&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function (promise) {
                    deferred.resolve(promise);
                },
                function (promise) {
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function (currentYear) {
                factory.currentYear = { Name: parseInt(currentYear) };
            }
        )

        return deferred.promise;
    }

    factory.getYears = function () {
        var defer = $q.defer();

        this.getCurrentYear()
            .then(
                function (currentYear) {
                    var maxYear = parseInt(currentYear) + 1;
                    var years = [];
                    while (maxYear-- && maxYear >= factory.minYear) {
                        var year = { Name: parseInt(maxYear) }
                        years.push(year);
                    }
                    defer.resolve(years)
                },
                function (error) {

                }

            );

        defer.promise
            .then(
                function (years) {
                    factory.years = years;
                }
            );
        return defer.promise;
    }

    factory.getInspectionScheduleDtos = function (year) {
        factory.year = year;
        //if we don't have the list of pis, get it from the server
        return factory.getDtos(year);
    }

    factory.getDtos = function (year) {
        var deferred = $q.defer();
        var url = '../../ajaxaction.php?action=getInspectionSchedule&year=' + year.Name + '&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
            function (promise) {
                factory.InspectionScheduleDtos = promise;
                deferred.resolve(promise);
            },
            function (promise) {
                deferred.reject();
            }
        );
        return deferred.promise;
    }

    factory.getAllInspectors = function () {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if (this.Inspectors.length) {
            deferred.resolve(this.Inspectors);
        } else {
            var url = '../../ajaxaction.php?action=getAllInspectors&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function (promise) {
                    deferred.resolve(promise);
                },
                function (promise) {
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function (inspectors) {
                factory.Inspectors = inspectors;
            }
        )

        return deferred.promise;
    }

    factory.getMonths = function () {
        this.months = [
            { val: "01", string: "January" },
            { val: "02", string: "February" },
            { val: "03", string: "March" },
            { val: "04", string: "April" },
            { val: "05", string: "May" },
            { val: "06", string: "June" },
            { val: "07", string: "July" },
            { val: "08", string: "August" },
            { val: "09", string: "September" },
            { val: "10", string: "October" },
            { val: "11", string: "November" },
            { val: "12", string: "December" },
        ];

        return this.months;
    }

    factory.scheduleInspection = function (dto, year, inspectorIndex) {
        $rootScope.saving = true;
        $rootScope.error = null;
        if (!dto.Inspectors) dto.Inspectors = [];
        var inspectors = dto.Inspections ? dto.Inspections.Inspectors : [];
        if (inspectorIndex) {
            factory.getAllInspectors()
                .then(
                    function (allInspectors) {
                        inspectors.push(allInspectors[inspectorIndex]);
                    }
                )
        }

        dto.Inspections = {
            Class: "Inspection",
            Key_id: dto.Inspection_id,
            Schedule_month: dto.Schedule_month || dto.Inspections.Schedule_month,
            Schedule_year: year.Name,
            Principal_investigator_id: dto.Pi_key_id,
            Inspectors: inspectors,
            Is_active: true
        }
        console.log(dto.Inspections);
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, dto)
            .then(
                function (inspection) {
                    dto.Inspections = inspection;
                    dto.Inspection_id = inspection.Key_id;
                    $rootScope.saving = false;
                },
                function (error) {
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.replaceInspector = function (dto, year, oldInspector, newInspector, inspector) {
        $rootScope.saving = true;
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //push the replacement inspector into the list
        $rootScope.dtoCopy.Inspections.Inspectors.push(newInspector);
        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    inspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    inspector.edit = false;
                    $rootScope.dtoCopy = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.removeInspector = function (dto, year, inspector) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        $rootScope.saving = true;
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    inspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    inspector.edit = false;
                    $rootScope.dtoCopy = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.addInspector = function (dto, year, newInspector) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        $rootScope.saving = true;
        $rootScope.error = null;
        $rootScope.dtoCopy.Inspections.Inspectors.push(newInspector);

        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    dto.addInspector = false;
                    newInspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    dto.addInspector = false;
                    newInspector.edit = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.editInspector = function (inspector, dto) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        inspector.edit = true;
    }

    factory.cancelEditInspector = function (inspector) {
        inspector.edit = false;
        $rootScope.dtoCopy = false;
    }

    factory.getInspectionsByPi = function (pi, inspections) {
        var l = inspections.length;
        pi.Inspections = [];
        for (var i = 0 ; i < l; i++) {
            var insp = inspections[i];
            if (insp.Principal_investigator_id == pi.Key_id) {
                pi.Inspections.push(insp);
            }
        }
        return pi;
    }

    factory.reduceInspectionScheduleDTOs = function reduceInspectionScheduleDTOs(dtos){
        // Process InspectionScheduleDTO items
        //   1. Apply Campus/Building/Room hierarchy to each
        //   2. Merge items with common Inspection_id to display together
        //   3. 

        // Group existing inspections by their Inspection_id
        let existing_inspections = dtos.filter( dto => dto.Inspection_id != null );
        let inspection_counts = existing_inspections.reduce( (items, dto, idx) => {
                let group = items.find(g => g[0].Inspection_id == dto.Inspection_id);
                if( !group ){
                    group = [];
                    items.push(group);
                }

                // Add this item to the group
                group.push(dto);

                return items;
            }, []);
        let multi_inspection_items = inspection_counts.filter( group => group.length > 1)
        let multi_inspections = multi_inspection_items.map( inspectionGroupItems => {
                // Remove all items in the group and reduce to dtos
                let inspectionGroup = inspectionGroupItems.map( dto => {
                    dtos.splice(dtos.indexOf(dto), 1);
                    return dto;
                });

                // Replace each of these groups with a singular 'master' inspection object
                let master = angular.copy(inspectionGroup[0]);

                console.debug("Reducing inspection group to master DTO", master.Inspection_id);

                // Prepare common fields
                let merged = {
                    Building_rooms: null,
                    Campus_name: null,
                    Building_name: null,
                    Campus_key_id: null,
                    Building_key_id: null,
                    IsMultiple:true,
                    Bio_hazards_present: inspectionGroup.some(function(dto){return dto.Bio_hazards_present }),
                    Chem_hazards_present: inspectionGroup.some(function (dto) { return dto.Chem_hazards_present }),
                    Rad_hazards_present: inspectionGroup.some(function (dto) { return dto.Rad_hazards_present }),
                    Inspection_rooms: inspectionGroup.reduce( (prev, current) => prev.concat(current), []),
                    Deficiency_selection_count: null,
                    Campuses: factory.buildCampusTree(inspectionGroup)
                };

                // Apply common fields to master inspection
                angular.extend(master, merged);

                // Push
                dtos.push(master);
            })
        ;

        // Build campus tree for remaining singular items
        dtos.filter( dto => !dto.IsMultiple )
            .forEach( dto => dto.Campuses = factory.buildCampusTree(dto) );

        return dtos;
    };

    factory.buildCampusTree = function buildCampusTree( itemOrItems ){
        let array = Array.isArray(itemOrItems) ? itemOrItems : [itemOrItems];

        // Build Campus/Building/Room tree for each InspectionScheduleDTO in array
        // Each of these DTOs is scoped to a single Building
        let all_rooms = [];
        let inspected_rooms = [];
        let tree = [];

        array.forEach( dto => {
            // Join all Building_rooms and Inspection_rooms arrays together
            dto.Building_rooms.forEach(room => all_rooms[room.Key_id] = room);

            if( dto.Inspection_rooms ){
                dto.Inspection_rooms.forEach(room => inspected_rooms[room.Key_id] = room);
            }

            // Get or init campus
            let campus = tree.find(c => c.Campus_key_id == dto.Campus_key_id);
            if( !campus ){
                campus = {
                    Campus_key_id: dto.Campus_key_id,
                    Campus_name: dto.Campus_name,
                    Buildings: []
                };

                tree.push(campus);
            }

            // Get or init building
            let building = campus.Buildings.find(b => b.Building_id == dto.Building_key_id);
            if( !building) {
                building = {
                    Building_name: dto.Building_name,
                    Building_id: dto.Building_key_id,
                    Campus_id: dto.Campus_key_id,
                    Campus_name: dto.Campus_name,
                    Rooms: []
                };

                campus.Buildings.push(building);
            }

            dto.Building_rooms.forEach( room => building.Rooms.push(room));
        });

        // Flag any room which is not inspected
        if( array[0].Inspection_id != null ){
            all_rooms.forEach(room => room.notInspected = inspected_rooms[room.Key_id] == undefined);
        }

        // Return the array of Campuses
        return tree;
    }

    return factory;
})

.controller('manageInspectionCtrl', function ($scope, $timeout, manageInspectionsFactory, convenienceMethods, roleBasedFactory, $q) {
    $scope.rbf = roleBasedFactory;
    $scope.mif = manageInspectionsFactory;
    $scope.convenienceMethods = convenienceMethods;
    $scope.constants = Constants;
    $scope.years = [];
    $scope.search = {init:true};
    $scope.run = false;
    $scope.getMargin = function (location) {
        if (!location) return;
        var margin = 0;
        if (location.Buildings) {
            location.Buildings.forEach(function (b) {
                b.Rooms.forEach(function (r) {
                    margin += 10;
                })
            })
        } else if (location.Rooms) {
            margin = location.Rooms.length * 10;
        }
        return "margin-top:"+margin+"px;margin-bottom:"+margin+"px;"
    }
    

    var getDtos = function (year) {
        return manageInspectionsFactory.getInspectionScheduleDtos(year)
            .then(
                function (dtos) {
                    $scope.dtos = manageInspectionsFactory.reduceInspectionScheduleDTOs(dtos);
                    $scope.loading = false;
                    $scope.genericFilter(true);
                }
            )
    },

    getYears = function () {
        return manageInspectionsFactory.getYears()
            .then(
                function (years) {
                    $scope.yearHolder = {};
                    $scope.yearHolder.years = years;
                    $scope.yearHolder.selectedYear = $scope.yearHolder.years[0];
                    return $scope.yearHolder.selectedYear;
                },
                function (error) {
                    $scope.error = 'Uh oh';
                }
            )
    },

    getAllInspectors = function () {
        return manageInspectionsFactory.getAllInspectors()
            .then(
                function (inspectors) {
                    $scope.inspectors = inspectors;
                }
            )
    },

    getMonths = function () {
        $scope.months = manageInspectionsFactory.getMonths();

    };

    var init = function () {
        $scope.loading = true;
        getAllInspectors()
            .then(getYears)
            .then(getDtos)
            .then(getMonths)
    };

    init();

    $scope.selectYear = function () {
        $scope.loading = true;
        $scope.dtos = [];

        return getDtos( $scope.yearHolder.selectedYear);
    }

    $scope.genericFilter = function (init) {
        var filtered = [];
        var defer = $q.defer();
        if (init) {
            filtered = $scope.dtos;
            defer.resolve(filtered);
            $scope.filtered = filtered;
            return;
        }

        var search = $scope.search;
        var items = $scope.dtos;
       
        if (search) {
            $scope.filtering = true;
            window.setTimeout(function () {
                var i = items.length;
                var filtered = [];
                var matched;
                while (i--) {
                    //we filter for every set search filter, looping through the collection only once
                    var item = items[i];
                    matched = true;

                    if (search.room_type) {
                        let typedRooms = item.Campuses
                            .map( c => c.Buildings ).reduce( (all, cur) => all.concat(cur), [])
                            .map( b => b.Rooms ).reduce( (all, cur) => all.concat(cur), [])
                            .filter( r => r.Room_type == search.room_type );

                        matched = typedRooms.length > 0;
                    }

                    if (search.building) {
                        matched = false
                        if (item.Campuses && item.Campuses.length) {
                            item.Campuses.forEach(function (campus) {
                                campus.Buildings.forEach(function (b) {
                                    if (b.Building_name.toLowerCase().indexOf(search.building.toLowerCase()) > -1) {
                                        matched = true;
                                        return;
                                    }
                                })
                            });
                        }
                    }

                    if (search.type) {
                        if (!item.Inspections) {
                            matched = false;
                            continue;
                        }                 
                        
                        if (search.type == Constants.INSPECTION.TYPE.BIO) {
                            //only items with inspections that aren't rad inspection that have bio hazards
                            if (item.Inspections.Is_rad || !item.Bio_hazards_present) {
                                matched = false;
                                continue;
                            }
                        } else if (search.type == Constants.INSPECTION.TYPE.CHEM) {
                            //only items with inspections that aren't rad inspection that have bio hazards
                            if (item.Inspections.Is_rad || !item.Chem_hazards_present) {
                                matched = false;
                                continue;
                            }
                        } else if(!item.Inspections.Is_rad) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.inspector) {
                        if (item.Inspections) {
                            if (item.Inspections.Inspectors && item.Inspections.Inspectors.length) {
                                var z = item.Inspections.Inspectors.length;
                                var longString = "";
                                while (z--) {
                                    longString += item.Inspections.Inspectors[z].Name;
                                }
                                if (longString.toLowerCase().indexOf(search.inspector.toLowerCase()) < 0) matched = false;
                            } else {
                                if (Constants.INSPECTION.SCHEDULE_STATUS.NOT_ASSIGNED.toLowerCase().indexOf(search.inspector.toLowerCase()) < 0) {
                                    matched = false;
                                    continue;
                                }
                            }
                        } else {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.campus) {
                        matched = false
                        if (item.Campuses && item.Campuses.length) {
                            item.Campuses.forEach(function (campus) {
                                if (campus.Campus_name.toLowerCase().indexOf(search.campus.toLowerCase()) > -1) {
                                    matched = true;
                                    return;
                                }
                            });
                        }
                    }

                    if (matched && search.pi && item.Pi_name) {
                        if (item.Pi_name.toLowerCase().indexOf(search.pi.toLowerCase()) < 0) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.status) {
                        /*
                        ,
                        INSPECTED: "INSPECTED",*/

                        if (item.Inspections) var status = item.Inspections.Status;
                        if (!item.Inspections) var status = Constants.INSPECTION.STATUS.NOT_SCHEDULED;
                        if (search.status != Constants.INSPECTION.STATUS.INSPECTED) {
                            if (status.toLowerCase() != search.status.toLowerCase()) {
                                matched = false;
                                continue;
                            }
                        } else {
                            const inspectedStatues = [
                                Constants.INSPECTION.STATUS.INCOMPLETE_INSPECTION,
                                Constants.INSPECTION.STATUS.INCOMPLETE_CAP,
                                Constants.INSPECTION.STATUS.OVERDUE_CAP,
                                Constants.INSPECTION.STATUS.SUBMITTED_CAP,
                                Constants.INSPECTION.STATUS.CLOSED_OUT
                            ];
                            matched = inspectedStatues.indexOf(status.toUpperCase()) != -1;
                            if (!matched) continue;
                        }
                    }

                    if (matched && search.date) {
                        if (!item.Inspections || !item.Inspections.Date_started && !item.Inspections.Schedule_month) {
                            matched = false;
                            continue;
                        } else {
                            var goingToMatch = false;
                            if (item.Inspections && item.Inspections.Date_started) var tempDate = getDate(item.Inspections.Date_started);
                            if (tempDate && tempDate.formattedString.indexOf(search.date) != -1) {
                                goingToMatch = true;
                            }
                            
                            if (item.Inspections && item.Inspections.Schedule_month) {
                                var j = monthNames2.length
                                while (j--) {
                                    if (monthNames2[j].val == item.Inspections.Schedule_month) {
                                        if (monthNames2[j].string.toLowerCase().indexOf(search.date.toLowerCase()) > -1) {
                                            goingToMatch = true;
                                        }
                                    }
                                }
                            }
                            if (!goingToMatch) {
                                matched = false;
                                continue;
                            }
                        }
                    }
                    if (matched && search.hazards) {
                        if (!item[search.hazards]) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched == true) filtered.unshift(item);
                }
            
                $scope.filtered = filtered;
                defer.resolve(filtered);
                
            }, 100);
            defer.promise.then(function () {
                $scope.filtering = false;
            });
            return;
        }
    }

    $scope.getRoomUrlString = function (dto) {
        if (!dto.Inspection_rooms || !dto.Inspection_rooms.length)
            return dto;
        roomIds = [];
        dto.Inspection_rooms.forEach(function (r) {
            roomIds.push(r.Key_id);
        })

        dto.roomUrlParam = $.param({"room":roomIds});
        return dto;
    }



});