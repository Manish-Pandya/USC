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


            // hours part from the timestamp
            var hours = date.getHours();
            // minutes part from the timestamp
            var minutes = date.getMinutes();
            // seconds part from the timestamp
            var seconds = date.getSeconds();

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
            //console.log(formattedTime);
            return formattedTime;
        }


var manageInspections = angular.module('manageInspections', ['convenienceMethodWithRoleBasedModule', 'once', 'ui.bootstrap'])
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
                    console.log('uh ih')
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
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if (this.InspectionScheduleDtos.length) {
            deferred.resolve(this.InspectionScheduleDtos);
        } else {
            var url = '../../ajaxaction.php?action=getInspectionSchedule&year=' + year.Name + '&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function (promise) {
                    deferred.resolve(promise);
                },
                function (promise) {
                    console.log('usho')
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function (InspectionScheduleDtos) {
                factory.InspectionScheduleDtos = { Name: parseInt(InspectionScheduleDtos) };
            },
            function () {
                alert('error getting schedule')
            }
        )

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
                    console.log('usho')
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

        console.log(dto);

        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, dto)
            .then(
                function (inspection) {
                    console.log(inspection);
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
        console.log($rootScope.dtoCopy);
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                console.log('removing ' + $rootScope.dtoCopy.Inspections.Inspectors[i].Name);
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //push the replacement inspector into the list
        $rootScope.dtoCopy.Inspections.Inspectors.push(newInspector);
        console.log($rootScope.dtoCopy);
        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    console.log(inspection);
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
        console.log($rootScope.dtoCopy);
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                console.log('removing ' + $rootScope.dtoCopy.Inspections.Inspectors[i].Name);
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    console.log(inspection);
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
        console.log(dto);
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        inspector.edit = true;
    }

    factory.cancelEditInspector = function (inspector) {
        inspector.edit = false;
        $rootScope.dtoCopy = false;
    }

    factory.parseDtos = function (dto) {
        console.log(dto);
        var dtos = [];
        var l = dto.Pis.length;
        for (var i = 0; i < l; i++) {
            var pi = dto.Pis[i];
            pi = factory.getInspectionsByPi(pi, dto.Inpsections);       
            
            
            //create a dto obj for each inspection that the pi has
            //cache an obj of uninspected rooms, grouped by building
            var n = pi.Inspections.length;
            for(var j = 0; j < n; j++){
                var dtoTemplate = {
                    Pi_name: pi.User.Name,
                    pi_key_id: pi.User.Key_id,

                }

            }


            //create a dto obj for each inspection the pi still needs
        }
        dtos = dto.Pis;
        console.log(dtos);
        return dtos;
    }
  /*
    private $pi_name;


    private $pi_key_id;
	
    private $building_name;
	
    private $building_key_id;
	
    private $campus_key_id;
	
    private $campus_name;

    private $building_rooms;


    private $inspection_rooms;


    private $inspections;
	
    private $inspection_id;
    private $bio_hazards_present;
    private $chem_hazards_present;
    private $rad_hazards_present;
    private $deficiency_selection_count;	

    
    */

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

    factory.collapseDtos = function (dtos) {
        var l = dtos.length;
        var ids = [];
        var duplicateIds = [];
        for (var i = 0; i < l; i++) {
            var d = dtos[i];
            if (!d.Inspections) continue;
            if (ids.indexOf(d.Inspections.Key_id) < 0) {
                ids.push(d.Inspections.Key_id)
            } else if (duplicateIds.indexOf(d.Inspections.Key_id) < 0) {
                duplicateIds.push(d.Inspections.Key_id);
            }
        }
        
        var masterIndex;
        var l = duplicateIds.length;
        for (var i = 0; i < l; i++) {
            var id = duplicateIds[i];
            var relevantDtos = dtos.reduce(function (relevantDtos, dto, index) {
                if (dto.Inspections && dto.Inspections.Key_id == id) {
                    relevantDtos.push(dto);
                    if (!masterIndex) {
                        var masterIndex = index;
                    }
                    dtos.splice(index, 1);
                }
                return relevantDtos;
            }, []);
            var masterDto = JSON.parse(JSON.stringify(relevantDtos[0]));
            map = {
                Building_rooms: null,
                Campus_name: null,
                Building_name: null,
                Campus_key_id: null,
                Building_key_id: null,
                Campuses: invertRooms(relevantDtos),
                IsMultiple:true,
                Bio_hazards_present: relevantDtos.some(function(dto){return dto.Bio_hazards_present }),
                Chem_hazards_present: relevantDtos.some(function (dto) { return dto.Chem_hazards_present }),
                Rad_hazards_present: relevantDtos.some(function (dto) { return dto.Rad_hazards_present }),
                Deficiency_selection_count: null
            }
            angular.extend(masterDto, map);
            console.log(masterDto);

            dtos.splice(masterIndex, 0, masterDto);
        }

        function invertRooms(dtos) {
            campuses = dtos.map(function (dto, idx) {
                var campus = {
                    Campus_id: dto.Campus_key_id,
                    Campus_name:dto.Campus_name,
                    Buildings:[]
                }
                campus.Buildings.push({ Building_id: dto.Building_key_id, Buidling_name: dto.Building_name });
                
                campus.Buildings = campus.Buildings.map(function (building, idxInner) {
                    var rooms = dto.Inspection_rooms;
                    building.Rooms = rooms.map(function (room) {
                        var innerRoom = room;
                        return room;
                    })
                    return building;
                })
                
                return campus;
            })

            return campuses;

        }

        function invertRoomForNonMultiples(dto) {
            var rooms = dto.Inspection_rooms || dto.Building_rooms;
            campuses = rooms.map(function (dto, idx) {
                var campus = {
                    Campus_id: dto.Campus_key_id,
                    Campus_name: dto.Campus_name,
                    Buildings: []
                }
                campus.Buildings.push({ Building_id: dto.Building_key_id, Buidling_name: dto.Building_name });

                campus.Buildings = campus.Buildings.map(function (building, idxInner) {
                    var rooms = dto.Inspection_rooms;
                    building.Rooms = rooms.map(function (room) {
                        var innerRoom = room;
                        return room;
                    })
                    return building;
                })

                return campus;
            })

            return campuses;
        }

        return dtos;
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

    var getDtos = function (year) {
        return manageInspectionsFactory.getInspectionScheduleDtos(year)
            .then(
                function (dtos) {
                    //$scope.dtos = manageInspectionsFactory.parseDtos(dto);
                    $scope.dtos = manageInspectionsFactory.collapseDtos(dtos);
                    //$scope.dtos = dtos;
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

    }

    var init = function () {
        $scope.loading = true;
        getAllInspectors()
            .then(getYears)
            .then(getDtos)
            .then(getMonths)
    }

    init();


    $scope.selectYear = function () {
        $scope.loading = true;
        $scope.dtos = [];

        manageInspectionsFactory.getInspectionScheduleDtos($scope.yearHolder.selectedYear)
            .then(
                function (dtos) {
                    console.log(dtos);
                    $scope.dtos = dtos;
                    $scope.loading = false;
                },
                function (error) {
                    $scope.error = "The system could not retrieve the list of inspections for the selected year.  Please check your internet connection and try again."
                }
            )
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
                console.log(i);
                var filtered = [];
                var matched;
                while (i--) {
                    //we filter for every set search filter, looping through the collection only once
                    var item = items[i];
                    matched = true;

                    if (search.building) {
                        if (item.Building_name && item.Building_name.toLowerCase().indexOf(search.building.toLowerCase()) < 0) {
                            matched = false;
                            continue;
                        }

                    }

                    if (search.type) {
                        if (!item.Inspections) {
                            matched = false;
                            continue;
                        }                 
                        
                        console.log(search.type + ' | ' + Constants.INSPECTION.TYPE.BIO)
                        if (search.type == Constants.INSPECTION.TYPE.BIO) {
                            console.log('bio');
                            //only items with inspections that aren't rad inspection that have bio hazards
                            if (item.Inspections.Is_rad || !item.Bio_hazards_present) {
                                matched = false;
                                continue;
                            }
                            console.log(item);
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
                        if (item.Campus_name.toLowerCase().indexOf(search.campus.toLowerCase()) < 0) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.pi && item.Pi_name) {
                        if (item.Pi_name.toLowerCase().indexOf(search.pi.toLowerCase()) < 0) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.status) {
                        if (item.Inspections) var status = item.Inspections.Status;
                        if (!item.Inspections) var status = Constants.INSPECTION.STATUS.NOT_SCHEDULED;
                        if (status.toLowerCase() != search.status.toLowerCase()) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.date) {
                        if (!item.Inspections || !item.Inspections.Date_started && !item.Inspections.Schedule_month) {
                            matched = false;
                            continue;
                        } else {
                            if (item.Inspections && item.Inspections.Date_started) var tempDate = getDate(item.Inspections.Date_started);
                            if (tempDate && tempDate.formattedString.indexOf(search.date) < 0) {
                                var goingToMatch = false;
                            } else {
                                var goingToMatch = true;
                            }
                            if (item.Inspections && item.Inspections.Schedule_month) {
                                //console.log(item.Inspections.Schedule_month);
                                var j = monthNames2.length
                                while (j--) {
                                    if (monthNames2[j].val == item.Inspections.Schedule_month) {
                                        if (monthNames2[j].string.toLowerCase().indexOf(search.date.toLowerCase()) > -1) var goingToMatch = true;
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


});