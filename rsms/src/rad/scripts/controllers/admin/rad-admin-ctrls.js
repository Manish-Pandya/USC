'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('AdminPickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods, radUtilitiesFactory) {
    var af = actionFunctionsFactory;

    $scope.af = af;

    $rootScope.pickupsPromise = af.getRadModels()
        .then(af.getAllPickups)
        .then(function(){
            $scope.pickups = dataStore.Pickup || [];
            console.log("pickups", $scope.pickups);

            function groupPickups(label, statusName){
                var status = Constants.PICKUP.STATUS[statusName];
                return {
                    label: label,
                    status: status,
                    statusName: statusName,

                    allowStart: status == Constants.PICKUP.STATUS.REQUESTED,
                    allowEdit: status == Constants.PICKUP.STATUS.PICKED_UP,
                    allowComplete: status == Constants.PICKUP.STATUS.PICKED_UP,

                    listAvailableContainers: [Constants.PICKUP.STATUS.REQUESTED, Constants.PICKUP.STATUS.PICKED_UP].includes(status),
                    listIncludedContainers: [Constants.PICKUP.STATUS.PICKED_UP, Constants.PICKUP.STATUS.AT_RSO].includes(status),

                    pickups: $scope.pickups.filter(p => p.Status == status)
                };
            }

            // Collect included containers into a single array for each pickup
            $scope.pickups.forEach(p => {
                p.includedContainers = radUtilitiesFactory.getAllWasteContainersFromPickup(p);
            });

            // Group by status
            $scope.pickup_groups = [
                groupPickups('Requested', 'REQUESTED'),
                groupPickups('In-Progress', 'PICKED_UP'),
                groupPickups('Completed', 'AT_RSO')
            ];
        });

    $rootScope.pickupReadyContainersPromise = af.getWasteContainersReadyForPickup()
    .then(function(containers){
        console.debug("Loaded all pickup-ready containers ", containers)

        // Map containers to their PI
        function reduceContainers(map, container){
            // Add PI key if we don't have it yet
            if( !map[container.Principal_investigator_id] ){
                map[container.Principal_investigator_id] = [];
            }

            // Push the container to that PI's entry
            map[container.Principal_investigator_id].push(container);

            return map;
        }

        $scope.pickupReadyContainersByPI = containers.reduce( reduceContainers, []);
    });

    function getAllPickups() {
        af.getAllPickups().then(function () {
            var pickups = af.get("Pickup");
            for (var i = 0; i < pickups.length; i++) {
                if (pickups[i].Status == Constants.PICKUP.STATUS.PICKED_UP
                    || (pickups[i].Status == Constants.PICKUP.STATUS.REQUESTED && pickups[i].Waste_bags.length)
                    || pickups[i].Scint_vial_collections.length
                    || pickups[i].Carboy_use_cycles.length) {
                    pickups[i].loadCarboyUseCycles();
                    pickups[i].loadWasteBags();
                    pickups[i].loadCurrentScintVialCollections();
                }
            }
            $scope.pickups = dataStoreManager.get("Pickup");
        });
    };

    $scope.editPickup = function(sourcePickup, targetStatusName){
        // Clone the pickup to ease cancelling
        var pickup = angular.extend({}, sourcePickup);

        // Assign pickup date
        if( !pickup.Pickup_date ){
            pickup.Pickup_date = convenienceMethods.setMysqlTime(new Date());
            console.debug("Assign pickup date to ", pickup.Pickup_date);
        }

        var modalData = {
            pickup: pickup,
            targetStatusName: targetStatusName,
            availableContainers: $scope.pickupReadyContainersByPI[pickup.Principal_investigator_id]
        };

        af.setModalData(modalData);

        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/edit-pickup-modal.html',
            controller: 'AdminPickupEditModalCtrl',
            windowClass: 'modal-dialog-wide'
        });

        modalInstance.result.then(
            function (arg){
                if(arg.promiseToSave){
                    console.debug("Pickup is saving...");
                    $scope.PickupSaving = arg.promiseToSave
                        .then(function(){
                            console.debug("TODO: INVALIDATE");
                        });
                    
                }
            }
        );
    };

    $scope.setStatusAndSave = function (pickup) {
        var pickupCopy = angular.extend({}, pickup);
        if (pickupCopy.Status == Constants.PICKUP.STATUS.REQUESTED)
            pickup.Pickup_date = null;
        //Prevent recursive structure by leaving out Carboy client-side convenience property
        pickupCopy.Carboy_use_cycles = pickupCopy.Carboy_use_cycles.map(function (c) {
            return {
                Key_id: c.Key_id,
                Class: "CarboyUseCycle",
                Close_date: c.Close_date,
                Pickup_id: c.Pickup_id,
                Principal_investigator_id: c.Principal_investigator_id
            };
        });
        console.log("PRE_SAVE PICKUP", pickupCopy);
        af.savePickup(pickup, pickupCopy, true);
    };

    $scope.selectWaste = function (waste, pickup, pi) {
        pi = dataStoreManager.getById("PrincipalInvestigator", pi.Key_id);
        pi.loadActiveParcels().then(function () {
            var modalData = { pi: pi, waste: {}, amts: [] };
            if (!Array.isArray(waste))
                waste = [waste];
            var containerIds = [];
            waste = waste.forEach(function (w) {
                containerIds.push(w.Key_id);
            });
            modalData.pi.ActiveParcels.forEach(function (p) {
                if (p.ParcelUses) {
                    p.ParcelUses.forEach(function (pu) {
                        pu.ParcelUseAmounts.forEach(function (amt) {
                            console.log(amt.IsPickedUp, pickup.Key_id, amt.Waste_type_id, Constants.WASTE_TYPE.SOLID);
                            if (amt.IsPickedUp == pickup.Key_id && amt.Waste_type_id == Constants.WASTE_TYPE.SOLID) {
                                amt.Date_used = pu.Date_used;
                                amt.Isotope_name = p.Authorization.IsotopeName;
                                modalData.amts.push(amt);
                            }
                        });
                    });
                }
            });
            modalData.waste = waste;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/pi/pi-modals/select-waste-modal.html',
                controller: 'SelectWasteCtrl'
            });
            modalInstance.result.then(function (arr) {
                console.log(arr);
                if (arr[1])
                    pi.CurrentWasteBag = arr[1];
                pickup.Waste_bags = [];
                pickup.Waste_bags = arr[0].Waste_bags;
            });
        });
    };

    $scope.setPickupDate = function (pickup) {
        var pickupCopy = dataStoreManager.createCopy(pickup);
        pickupCopy.Pickup_date = convenienceMethods.setMysqlTime(pickup.view_Pickup_date);
        af.savePickup(pickup, pickupCopy, true).then(function (r) {
            pickup.Pickup_date = r.Pickup_date;
            pickup.editDate = false;
        });
    };

    $scope.adminRemoveFromPickup = function (container) {
        return $scope.af.adminRemoveFromPickup(container).then(function () { container.Pickup_id = null; });
    };

    $scope.adminAddToPickup = function (container, pickup) {
        return $scope.af.adminAddToPickup(container, pickup).then(function () {
            container.Pickup_id = pickup.Key_id;
            if (!$scope.hasClosedNotPickedUp($scope.availableContainers))
                $scope.filterObj.reverse = false;
        });
    };

    $scope.filterObj = { reverse: false };

    $scope.hasPickupId = function (item) {
        var reverse = $scope.filterObj.reverse;
        return (item.Pickup_id == null) == reverse;
    };

    $scope.pickupsFilter = function (pickup, reverse) {
        console.log(pickup.PiName, Pickup);
        if (!$scope.availableContainers)
            $scope.availableContainers = [];
        return $scope.availableContainers[pickup.Key_id] =
            (pickup.Carboy_use_cycles || [])
                .concat(pickup.Waste_bags || [])
                .concat(pickup.Scint_vial_collections || [])
                .concat(pickup.Other_waste_containers || [])
                .map(function (c, idx) {
                var container = angular.extend({}, c);
                container.ViewLabel = c.Label || c.CarboyNumber;
                //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
                container.idx = idx + 1;
                switch (c.Class) {
                    case ("WasteBag"):
                        container.ClassLabel = "Waste Bags";
                        break;
                    case ("CarboyUseCycle"):
                        container.ClassLabel = "Carboys";
                        break;
                    case ("ScintVialCollection"):
                        container.ClassLabel = "Scint Vial Containers";
                        break;
                    case ("OtherWasteContainer"):
                        container.ClassLabel = "Other Waste";
                        break;
                    default:
                        container.ClassLabel = "";
                }
                return container;
            });
    };

    $scope.hasClosedNotPickedUp = function (containers) {
        return containers.some(function (c) { return c.Pickup_id == null; });
    };
})
.controller('AdminPickupEditModalCtrl', function($scope, $modalInstance, $q, actionFunctionsFactory, convenienceMethods, radUtilitiesFactory){
    
    var modalData = actionFunctionsFactory.getModalData();
    var pickup = modalData.pickup;

    $scope.pickup = pickup;
    console.debug("Open edit-pickup modal", $scope, $modalInstance, $scope.modalData);

    $scope.targetStatus = Constants.PICKUP.STATUS[modalData.targetStatusName];

    // Change from REQUESTED to PICKED_UP
    $scope.editActionLabel = pickup.Status == Constants.PICKUP.STATUS.REQUESTED ? 'Begin' : 'Edit';

    if( $scope.targetStatus == Constants.PICKUP.STATUS.AT_RSO ){
        $scope.editActionLabel = 'Finalize';
    }

    // Determine class-name for modal header
    $scope.getStatusClassName = function(pickup){
        switch(pickup.Status){
            default:
            case Constants.PICKUP.STATUS.REQUESTED: return 'requested';
            case Constants.PICKUP.STATUS.PICKED_UP: return 'picked_up';
            case Constants.PICKUP.STATUS.AT_RSO:    return 'at_rso';
        }
    }

    $scope.statusClass = $scope.getStatusClassName(pickup);

    // Collect all included containers from pickup

    // Flag all included containers as selected
    var preselectedContainers = radUtilitiesFactory.getAllWasteContainersFromPickup(pickup)
        .map(c => {
            c.isSelectedForPickup = true;
            return c;
        });

    // For new Pickups, we want to select by-default all available containers
    var selectAllContainers = pickup.Status === Constants.PICKUP.STATUS.REQUESTED;

    // Merge included with available (if any) and apply labels
    $scope.containers = 
        (modalData.availableContainers || [])
        .concat(preselectedContainers || []);

    if( selectAllContainers ){
        $scope.containers.forEach(container => {
            container.isSelectedForPickup = true;
        });
    };

    $scope.countSelected = function(){
        return $scope.containers.filter(c => c.isSelectedForPickup).length;
    };

    $scope.addOrRemoveContainer = function(container) {
        container.isSelectedForPickup = !container.isSelectedForPickup;

        console.debug("TODO: " + (container.isSelectedForPickup ? 'Add' : 'Remove') + " container from pickup", container);
    };

    $scope.getClassByContainerType = function(container) {
        switch(container.Class){
            case "WasteBag":            return "icon-remove-2 solids-containers";
            case "ScintVialCollection": return "icon-sv scint-vials";
            case "CarboyUseCycle":      return "icon-carboy carboys";
            case "OtherWasteContainer": return "other icon-beaker-alt red";
            default: return "";
        }
    };

    $scope.editPickupDate = function(pickup){
        $scope.editDate = true;
        $scope.view_Pickup_date = convenienceMethods.dateToIso(pickup.Pickup_date);
    };

    $scope.editPickupDateAccept = function(pickup, date){
        $scope.editDate = false;
        pickup.Pickup_date = convenienceMethods.setMysqlTime(date);
    }

    $scope.editPickupDateCancel = function(pickup){
        $scope.editDate = false;
        $scope.view_Pickup_date = new Date();
    }

    $scope.editComment = function(c){
        $scope.comment_copy = c.Comments;
        c.editing_comment = true;
    };

    $scope.editCommentAccept = function(c){
        console.debug("Accept new comments for ", c);
        console.debug("\\______New:", c.Comments);
        console.debug("\\_Previous:", $scope.comment_copy);

        // Forget our old value
        $scope.comment_copy = undefined;
        c.editing_comment = false;

        // Flag that a comment was edited
        c.edited_comment = true;
        $scope.edited_comment = true;
    };

    $scope.editCommentCancel = function(c){
        c.Comments = $scope.comment_copy;
        c.edit = false;
    };

    $scope.validate = function(){
        // Require at least one selected container
        var selectedContainers = $scope.countSelected() > 0;

        // TODO Validate Pickup Date
        var validPickupDate = true;

        $scope.valid = (selectedContainers && validPickupDate);

        return $scope.valid;
    }

    $scope.saveOnlyComments = function(pickup){
        console.debug("Saving Containers with edited comments...");
        var promises = [];
        $scope.containers.forEach(c => {
            if( c.edited_comment ){
                console.debug('Saving Comments:', c);
                promises.push(actionFunctionsFactory.save(c));
            }
        });

        $scope.pickupsCommentsSavingPromise = $q.all(promises)
        .then(
            function(){
                console.debug("Containers saved");
                $modalInstance.close();
            },
            function(err){
                console.error("Error saving Container:", err);
            }
        );
    }

    $scope.save = function(pickup) {
        if( $scope.validate() ){
            console.debug("Close/save edit-pickup modal");
            console.debug("Prepare to save ", pickup);

            // Assign/Unassign containers
            var modifiedContainers = [];

            $scope.containers.forEach(c => {
                if( c.isSelectedForPickup ){
                    console.debug('     Add to Pickup:', c);
                    c.Pickup_id = pickup.Key_id;
                    modifiedContainers.push(c);
                }
                else if( c.Pickup_id ) {
                    console.debug('Remove from Pickup:', c);
                    c.Pickup_id = null;
                    modifiedContainers.push(c);
                }
                else{
                    // nothing to do
                    console.debug('No change to Container: ', c);
                }
            });

            // Update Status?
            if( $scope.targetStatus != pickup.Status ){
                console.debug("Change pickup status from " + pickup.Status + " to " + $scope.targetStatus);
                pickup.Status = $scope.targetStatus;
            }

            // Save Pickup (& containers?)
            $modalInstance.close({
                promiseToSave: actionFunctionsFactory.savePickupDetails(pickup, modifiedContainers)
            });
        }
        else{
            console.warn("Cannot save invalid pickup");
        }
    };

    $scope.dismiss = function(){
        console.debug("Dismiss edit-pickup modal");
        $modalInstance.dismiss();
    };
})
    .controller('AdminPickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    if (!$scope.modalData.SolidsContainerCopy) {
        $scope.modalData.SolidsContainerCopy = {
            Class: 'SolidsContainer',
            Room_id: null,
            Is_active: true
        };
    }
    $scope.close = function () {
        $modalInstance.dismiss();
        af.deleteModalData();
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .filter('authsFilter', function () {
    return function (auths, filterObj) {
        var filtered = auths.filter(function (a) {
            if (a.Termination_date && !filterObj.showTerminated)
                return false;
            if (!a.Termination_date && filterObj.showTerminated)
                return false;
            if (!filterObj)
                return true;
            if (filterObj.piName && a.PiName.toLowerCase().indexOf(filterObj.piName.toLowerCase()) == -1) {
                return false;
            }
            if (filterObj.department) {
                if (!a.Departments.some(function (d) {
                    return d.Name.toLowerCase().indexOf(filterObj.department.toLowerCase()) != -1;
                }))
                    return false;
            }
            if (filterObj.room) {
                if (!a.Rooms.some(function (r) {
                    return r.Name.toLowerCase().indexOf(filterObj.room.toLowerCase()) != -1;
                }))
                    return false;
            }
            if (filterObj.building) {
                if (!a.Rooms.some(function (r) {
                    return r.Building.Name.toLowerCase().indexOf(filterObj.building.toLowerCase()) != -1;
                }))
                    return false;
            }
            return true;
        });
        return filtered;
    };
})
    .controller('AuthReportCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    var af = $scope.af = actionFunctionsFactory;
    if (!$rootScope.filterObj)
        $rootScope.filterObj = { showNew: false, piName: '' };

    $rootScope.configureAuthColumns = function(){
        $rootScope.columnConfig = {
            investigator:     true,

            approvalDate:     $rootScope.filterObj.showNew,
            newOrUpdateNotes: $rootScope.filterObj.showNew,

            terminatedDate:   !$rootScope.filterObj.showNew && $rootScope.filterObj.showTerminated,
            terminatedNotes:  !$rootScope.filterObj.showNew && $rootScope.filterObj.showTerminated,

            department:       !$rootScope.filterObj.showNew && !$rootScope.filterObj.showTerminated,
            buildingAndRoom:  !$rootScope.filterObj.showNew && !$rootScope.filterObj.showTerminated,
            amendments:       $rootScope.filterObj.showNew && !$rootScope.filterObj.showTerminated,
            licenseAuth:      $rootScope.filterObj.showNew && !$rootScope.filterObj.showTerminated,
            authNumber:       !$rootScope.filterObj.showNew,
            lastAmended:      !$rootScope.filterObj.showNew,
            isotopes:         true
        };

        if(!$rootScope.filterObj.showNew){
            $scope.orderProps = ["PiName"];
        }
        else {
            $scope.orderProps = ["Approval_date", "PiName"];
        }

        console.debug("Columns:", $rootScope.columnConfig, "Sort:", $scope.orderProps);
    }

    $rootScope.configureAuthColumns();

    $rootScope.search = function (filterObj) {
        console.debug("Filtering authorizations with ", filterObj);

        if (!filterObj.fromDate)
            return $scope.piAuths;
        $scope.filtered = $rootScope.allAuths.filter(function (a) {
            var d = a.Approval_date;
            if (d < convenienceMethods.setMysqlTime(filterObj.fromDate))
                return false;
            if (filterObj.toDate && d > convenienceMethods.setMysqlTime(filterObj.toDate))
                return false;
            return true;
        });
        console.log($scope.filtered);
        return $scope.filtered;
    };
    $scope.print = function () {
        window.print();
    };

    $rootScope.radPromise = af.getRadModels()
        .then(af.getAllPIAuthorizations)
        .then(function (piAuths) {
            $rootScope.piAuths = [];
            $rootScope.allAuths = dataStore.PIAuthorization;
            console.log($rootScope.allAuths);
            var piAuths = _.groupBy(dataStore.PIAuthorization, 'Principal_investigator_id');
            for (var pi_id in piAuths) {
                var newest_pi_auth = piAuths[pi_id].sort(function (a, b) {
                    var sortVector = b.Amendment_number - a.Amendment_number || b.Key_id - a.Key_id || b.Approval_date - a.Approval_date;
                    return sortVector;
                })[0];
                $rootScope.piAuths.push(newest_pi_auth);
                $rootScope.filtered = $rootScope.piAuths;
            }
            console.log($scope.piAuths);
        }, function () {
            console.warn("Error retrieving all PI Authorizations...");
        })
        .then(function(){
            // ensure that any pre-existing filters are applied after data is ready
            $scope.filtered = $rootScope.search($rootScope.filterObj);
        });

    console.log("AuthReportCtrl running");
    $rootScope.radPromise.then(function(){
        console.log("AuthReportCtrl complete");
    });
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('CarboysCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    var af = actionFunctionsFactory;

    var getCarboysFromDatastore = function(){
        // Get them
        $scope.carboys = dataStore.Carboy;

        // Make sure their PI is set, if available
        var pi_ids = $scope.carboys
            .map(c => c.Current_carboy_use_cycle.Principal_investigator_id)
            .filter(pid => pid);

        console.debug(pi_ids.length + "/" + $scope.carboys.length + " have PI assignments");

        var pis = dataStore.PrincipalInvestigator.filter(pi => pi_ids.includes(pi.Key_id));
        console.debug("Found " + pis.length + "/" + pi_ids + " assigned PIs");

        $scope.carboys.forEach(carboy => {
            if( !carboy.PI && carboy.Current_carboy_use_cycle.Principal_investigator_id ){
                console.debug("Find assignment for carboy ", carboy,);
                var match = pis.filter(pi => pi.Key_id == carboy.Current_carboy_use_cycle.Principal_investigator_id)[0];
                if(match){
                    carboy.PI = match;
                    console.debug("...", carboy.PI);
                }
                else{
                    console.warn("Could not find PI assignment", carboy);
                }
            }
        });
    }

    $scope.af = af;
    $scope.carboysPromise = $rootScope.radModelsPromise
        .then(af.getAllCarboys)
        .then(getCarboysFromDatastore);

    $scope.deactivate = function (carboy) {
        var copy = dataStoreManager.createCopy(carboy);
        copy.Retirement_date = new Date();
        af.saveCarboy(carboy.PrincipalInvestigator, copy, carboy);
    };
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.Carboy();
            object.Class = "Carboy";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/carboy-modal.html',
            controller: 'CarboysModalCtrl'
        });

        modalInstance.result.then(getCarboysFromDatastore);
    };
})
    .controller('CarboysModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    console.log($scope.modalData);
    $scope.save = function (carboy) {
        af.saveCarboy(carboy.PrincipalInvestigator, carboy, $scope.modalData.Carboy)
        .then(function () {
            $modalInstance.close(); //success
            af.deleteModalData();
        });
    };

    $scope.close = function () {
        $modalInstance.dismiss(); //cancel
        af.deleteModalData();
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('disposalCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.cv = convenienceMethods;
    var getAllDrums = function () {
        return af.getAllDrums()
            .then(function (drums) {
            if (!dataStore.Drum)
                dataStore.Drum = [];
            $rootScope.drums = dataStore.Drum;
            return drums;
        });
    };
    var getAllWasteBags = function () {
        return af.getAllWasteBags()
            .then(function (bags) {
            if (!dataStore.WasteBag)
                dataStore.WasteBag = [];
            var i = dataStore.WasteBag.length;
            while (i--) {
                dataStore.WasteBag[i].loadPickup();
            }
            $rootScope.WasteBags = dataStore.WasteBag;
            return bags;
        });
    };
    var getCycles = function () {
        return af.getAllCarboyUseCycles()
            .then(function (cycles) {
            if (!dataStore.CarboyUseCycle)
                dataStore.CarboyUseCycle = [];
            $scope.cycles = dataStoreManager.get("CarboyUseCycle");
            return cycles;
        });
    };
    var getSVCollections = function () {
        return af.getAllScintVialCollections()
            .then(function (svCollections) {
            if (!dataStore.ScintVialCollection)
                dataStore.ScintVialCollection = [];
            var i = dataStore.ScintVialCollection.length;
            while (i--) {
                dataStore.ScintVialCollection[i].loadPickup();
            }
            $rootScope.ScintVialCollections = dataStore.ScintVialCollection;
            return svCollections;
        });
    };
    var getOtherWaste = function () {
        return af.getAllOtherWasteContainers()
            .then(function (containers) {
            if (!dataStore.OtherWasteContainers)
                dataStore.OtherWasteContainer = [];
            dataStore.OtherWasteContainer.forEach(function (c) { if (c.Key_id == 4)
                console.log("CONTENTS", c.Contents); });
            console.log("CONTAINERS", dataStore.OtherWasteContainer);
            $rootScope.OtherWasteContainers = dataStore.OtherWasteContainer;
            return $rootScope.OtherWasteContainers;
        });
    };
    var getIsotopes = function () {
        return af.getAllIsotopes()
            .then(function (isotopes) {
            $rootScope.isotopes = dataStore.Isotope;
            return isotopes;
        });
    };
    var getMiscWaste = function () {
        return af.getAllMiscellaneousWaste()
            .then(function (mics) {
            $rootScope.miscWastes = dataStore.MiscellaneousWaste;
            return mics;
        });
    };
    var getContainers = function () {
        console.log($rootScope.OtherWasteContainers);
        return $scope.drumableContainers = $rootScope.WasteBags.concat($rootScope.ScintVialCollections).concat($rootScope.OtherWasteContainers)
            .filter(function (c) {
            //console.log(c.Class, c)
            return c.Pickup_date && !c.Drum_id;
        })
            .map(function (c, idx) {
            var container = angular.extend({}, c);
            container.ViewLabel = c.Label || c.CarboyNumber;
            //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
            container.idx = idx + 1;
            switch (c.Class) {
                case ("WasteBag"):
                    container.ClassLabel = "Waste Bags";
                    break;
                case ("CarboyUseCycle"):
                    container.ClassLabel = "Carboys";
                    break;
                case ("ScintVialCollection"):
                    container.ClassLabel = "Scint Vial Containers";
                    break;
                case ("OtherWasteContainer"):
                    container.ClassLabel = "Other Waste";
                    break;
                default:
                    container.ClassLabel = "";
            }
            return container;
        });
    };
    $scope.hasClosedNotPickedUp = function (containers) {
        return containers.some(function (c) { return c.Pickup_id == null; });
    };
    $rootScope.radPromise = getAllWasteBags()
        .then(getIsotopes)
        .then(getSVCollections)
        .then(getAllDrums)
        .then(getCycles)
        .then(getMiscWaste)
        .then(getOtherWaste)
        .then(getContainers);
    $scope.date = new Date();
    $scope.assignDrum = function (object) {
        var modalData = {};
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-assignment.html',
            controller: 'DrumAssignmentCtrl'
        });
    };
    $scope.assignWasteBagToDrum = function (wasteBag) {
        if (!wasteBag.PickupLots || !wasteBag.PickupLots.length) {
            wasteBag.PickupLots = [{
                    Class: "PickupLot",
                    Currie_level: 0,
                    Waste_bag_id: wasteBag.Key_id,
                    Waste_type_id: Constants.WASTE_TYPE.SOLID,
                    Isotope_id: null
                }];
        }
        af.setModalData({ "WasteBag": wasteBag });
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-assignment.html',
            controller: 'DrumAssignmentCtrl'
        });
    };
    $scope.drumModal = function (object) {
        var modalData = {};
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-shipment.html',
            controller: 'DrumShipCtrl'
        });
    };
    $scope.editDrum = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.Drum();
            object.Class = "Drum";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-modal.html',
            controller: 'DrumShipCtrl'
        });
    };
    $scope.editCycle = function (cycle) {
        cycle.edit = true;
        af.createCopy(cycle);
    };
    $scope.cancelEditCycle = function (cycle) {
        cycle.edit = false;
        $rootScope.CarboyUseCycleCopy = {};
    };
    $scope.pour = function (cycle) {
        if (!cycle.pourable) {
            if (window.confirm("This carboy will not decay until " + convenienceMethods.dateToIso(cycle.Pour_allowed_date) + ". Are you sure you want to pour it now?")) {
                pour(cycle);
            }
        }
        else {
            pour(cycle);
        }
        function pour(cycle) {
            af.createCopy(cycle);
            af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle, true);
        }
    };
    $scope.editReading = function (reading) {
        reading.edit = true;
        af.createCopy(reading);
    };
    $scope.addReading = function (cycle) {
        cycle.readingEdit = true;
        $rootScope.CarboyReadingAmountCopy = new window.CarboyReadingAmount();
        $rootScope.CarboyReadingAmountCopy.Carboy_use_cycle_id = cycle.Key_id;
        $rootScope.CarboyReadingAmountCopy.edit = true;
        $rootScope.CarboyReadingAmountCopy.Class = "CarboyReadingAmount";
        if (!cycle.Carboy_reading_amounts)
            cycle.Carboy_reading_amounts = [];
        cycle.Carboy_reading_amounts.push($rootScope.CarboyReadingAmountCopy);
    };
    $scope.removeReading = function (cycle, reading) {
        reading.edit = true;
        af.createCopy(reading);
        for (var n = 0; n < cycle.Carboy_reading_amounts.length; n++) {
            if (cycle.Carboy_reading_amounts[n] == reading) {
                // TODO, make sure this is actually being saved. Don't think it is currently.
                af.createCopy(cycle);
                cycle.Carboy_reading_amounts.splice(n, 1);
                af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle);
            }
        }
    };
    $scope.getIsPastHotRoomDate = function (cycle) {
        var todayAtMidnight = new Date();
        todayAtMidnight.setHours(0, 0, 0, 0);
        var date = cycle.Hot_check_date;
        var hotCheckSeconds = convenienceMethods.getDate(date).getTime();
        return hotCheckSeconds < todayAtMidnight.getTime();
    };
    $scope.resetHotRoomDate = function (cycle) {
        af.createCopy(cycle);
        $rootScope.CarboyUseCycleCopy.Hot_check_date = convenienceMethods.setMysqlTime(new Date());
        af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle);
    };
    $scope.getDateRead = function (reading) {
        if (reading.Date_read)
            return convenienceMethods.dateToIso(reading.Date_read);
        return convenienceMethods.dateToIso(convenienceMethods.setMysqlTime(new Date()));
    };
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.MiscellaneousWaste();
            object.Class = "MiscellaneousWaste";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/misc-waste-modal.html',
            controller: 'MiscWasteModalCtrl'
        });
        modalInstance.result.then(function () {
            getMiscWaste();
        });
    };
    $scope.addWaste = function (container, amount) {
        var modalData = { Container: container, ParcelUseAmount: {} };
        if (amount) {
            modalData.ParcelUseAmount = amount;
        }
        else {
            modalData.ParcelUseAmount = new window.ParcelUseAmount();
            modalData.ParcelUseAmount.Class = "ParcelUseAmount";
            modalData.ParcelUseAmount.Is_active = true;
            switch (container.Class) {
                case ("WasteBag"):
                    modalData.ParcelUseAmount.Waste_bag_id = container.Key_id;
                    break;
                case ("CarboyUseCycle"):
                    modalData.ParcelUseAmount.Carboy_id = container.Carboy_id;
                    break;
                case ("ScintVialCollection"):
                    modalData.ParcelUseAmount.Scint_vial_collection_id = container.Key_id;
                    break;
                case ("OtherWasteContainer"):
                    modalData.ParcelUseAmount.Other_waste_container_id = container.Key_id;
                    modalData.ParcelUseAmount.Other_waste_type_id = container.Other_waste_type_id;
                    break;
                default:
                    break;
            }
        }
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/add-waste-modal.html',
            controller: 'AddWasteCtrl'
        });
        modalInstance.result.then(function () {
            //getMiscWaste();
        });
    };
    $scope.nonDrummed = function (container) {
        return !container.Drum_id;
    };
})
    .controller('DrumAssignmentCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.saveWasteBag = function (bag, copy) {
            $scope.close();
            $rootScope.saving = af.saveWasteBag(bag, copy)
                .then(function (r) {
                bag.Contents = r.Contents;
                console.log(r);
            })
                .then(reloadDrums);
        };
        $scope.saveCarboyUseCycle = function (cycle, copy) {
            $scope.close();
            $rootScope.saving = af.saveCarboyUseCycle(copy, cycle)
                .then(reloadDrum);
        };
        $scope.saveSVCollection = function (collection, copy) {
            $scope.close();
            $rootScope.saving = af.saveSVCollection(collection, copy)
                .then(reloadDrum);
        };
        var reloadDrum = function (obj) {
            var drum = dataStoreManager.getById("Drum", obj.Drum_id);
            af.replaceDrum(drum)
                .then(function (returnedDrum) {
                console.log(returnedDrum);
                return drum.Contents = returnedDrum.Contents;
            });
        };
        var reloadDrums = function () {
            var drums = dataStoreManager.get("Drum");
            af.replaceDrums(drums)
                .then(function (returnedDrums) {
                console.log(returnedDrums);
            });
        };
        $scope.addPickupLot = function (wasteBag) {
            wasteBag.PickupLots.push({
                Class: "PickupLot",
                Currie_level: 0,
                Waste_bag_id: wasteBag.Key_id,
                Isotope_id: null
            });
        };
        $scope.removePickupLot = function (wasteBag, index) {
            wasteBag.PickupLots.splice(index, 1);
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }])
    .controller('DrumShipCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.shipDrum = function (drum, copy) {
            copy.Date_destroyed = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.view_Date_destroyed));
            $rootScope.saving = af.saveDrum(drum, copy);
            $scope.close();
        };
        $scope.saveDrum = function (drum, copy) {
            $rootScope.saving = af.saveDrum(drum, copy);
            $scope.close();
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }])
    .controller('drumDetailCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    var af = $scope.af = actionFunctionsFactory;
    var getDrum = function (id) {
        return af.getAllDrums()
            .then(function (drums) {
            if (!dataStore.Drum)
                dataStore.Drum = [];
            $scope.drum = dataStoreManager.getById("Drum", id);
            $scope.drum.loadDrumWipeTest();
            return $scope.drum;
        });
    };
    $rootScope.loading = getDrum($stateParams.drumId);
    $scope.editDrumWipeTest = function (drum, test) {
        $rootScope.DrumWipeTestCopy = {};
        if (!test) {
            $rootScope.DrumWipeTestCopy = new window.DrumWipeTest();
            $rootScope.DrumWipeTestCopy.Drum_id = drum.Key_id;
            $rootScope.DrumWipeTestCopy.Class = "DrumWipeTest";
            $rootScope.DrumWipeTestCopy.Is_active = true;
        }
        else {
            af.createCopy(test);
        }
        drum.Creating_wipe = true;
    };
    $scope.cancelDrumWipeTestEdit = function (drum) {
        drum.Creating_wipe = false;
        $rootScope.DrumWipeTestCopy = {};
    };
    $scope.cancelDrumWipeEdit = function (test, smear) {
        smear.edit = false;
        $rootScope.DrumWipeTestCopy = {};
    };
    $scope.editDrumWipe = function (wipeTest, wipe) {
        if (!wipeTest.Drum_wipes)
            wipeTest.Drum_wipes = [];
        $rootScope.DrumWipeCopy = {};
        var i = wipeTest.Drum_wipes.length;
        while (i--) {
            wipeTest.Drum_wipes[i].edit = false;
        }
        if (!wipe) {
            $rootScope.DrumWipeCopy = new window.DrumWipe();
            $rootScope.DrumWipeCopy.Drum_wipe_test_id = wipeTest.Key_id;
            $rootScope.DrumWipeCopy.Class = "DrumWipe";
            $rootScope.DrumWipeCopy.edit = true;
            $rootScope.DrumWipeCopy.Is_active = true;
            wipeTest.Drum_wipes.unshift($rootScope.DrumWipeCopy);
        }
        else {
            wipe.edit = true;
            af.createCopy(wipe);
        }
    };
})
    .controller('MiscWasteModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        var md = $scope.modalData = af.getModalData();
        console.log(md);
        var amount = new ParcelUseAmount();
        if (md.MiscellaneousWasteCopy &&
            (!md.MiscellaneousWasteCopy.Parcel_use_amountss
                || !md.MiscellaneousWasteCopy.Parcel_use_amounts.length > 0)) {
            amount.Miscellaneous_waste_id == md.MiscellaneousWasteCopy || null;
        }
        else {
            angular.extend(amount, md.MiscellaneousWaste.Parcel_use_amounts[0]);
        }
        md.MiscellaneousWasteCopy.Parcel_use_amounts = [amount];
        $scope.save = function (copy, mw) {
            console.log(copy, mw);
            af.saveMiscellaneousWaste(copy, mw).then(function () {
                $modalInstance.close(mw);
            });
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }])
    .controller('AddWasteCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        var md = $scope.modalData = af.getModalData();
        console.log(md);
        $scope.save = function (copy, pua, container) {
            console.log(copy, pua, container);
            af.saveParcelUseAmount(copy, pua, container).then(function () {
                $modalInstance.close(container);
            });
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }]);
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('InventoriesCtrl', function ($scope, actionFunctionsFactory, $rootScope, $state, convenienceMethods) {
    console.log("Inventories Controller", $scope);
    var af = actionFunctionsFactory;
    var getInventory = function () {
        console.log("Get inventory with ID " + $state.params.pi_inventory);
        return af.getPIInventoryById($state.params.pi_inventory)
            .then(function(inventory){
                console.log("PI " + $state.params.pi_inventory + " | Inventory: ", inventory);
                $scope.pi_inventory = inventory;
            });
    };

    var getMostRecentInventory = function(){
        console.log("Get most recent inventory");
        return af.getMostRecentInventory()
            .then(function(quarterlyInventory){
                $scope.inventory = quarterlyInventory;
                console.log("Most recent quarterly inventory:", $scope.inventory);
            });
    }

    $scope.getAllPIs = af.getAllPIs()
        .then(function (pis) {
        $scope.PIs = pis;
        return;
    }, function () {
        $scope.error = "Couldn't get the PIs";
        return false;
    });

    $scope.af = af;

    if ($state.current.name == 'radmin-quarterly-inventory') {
        $scope.inventoryPromise = getInventory();
    }
    else{
        $scope.inventoryPromise = getMostRecentInventory().then(getInventory());
    }

    $scope.getInventoriesByPiId = function (id) {
        $scope.piInventoriesPromise = af.getInventoriesByPiId(id)
            .then(function (piInventories) {
            console.log(piInventories);
            $scope.piInventories = piInventories;
        });
    };
    $scope.createInventory = function () {
        var startDate = convenienceMethods.setMysqlTime(moment().startOf('quarter'));
        var endDate = convenienceMethods.setMysqlTime(moment().endOf('quarter'));
        $scope.QuarterlyInventorySaving = af.createQuarterlyInventory(startDate, endDate)
            .then(function (inventory) {
            $scope.inventory = inventory;
            console.log(inventory);
        }, function () { });
    };
    $scope.startDate = convenienceMethods.dateToIso(convenienceMethods.setMysqlTime(moment().startOf('quarter')));
    $scope.endDate = convenienceMethods.dateToIso(convenienceMethods.setMysqlTime(moment().endOf('quarter')));
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('IsotopeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    var af = actionFunctionsFactory;
    var sortIsotopes = function (isotopes) {
        return isotopes.sort(function (a, b) {
            return a.License_line_item.length - b.License_line_item.length || a.License_line_item.localeCompare(b.License_line_item) || a.Name - b.Name;
        });
    };
    var getAllIsotopes = function () {
        af.getAllIsotopes()
            .then(function (isotopes) {
            $scope.isotopes = sortIsotopes(dataStore.Isotope);
        }, function () { });
    };
    $scope.af = af;
    $rootScope.isotopesPromise = getAllIsotopes();
    $scope.deactivate = function (isotope) {
        var copy = dataStoreManager.createCopy(isotope);
        copy.Is_active = !copy.Is_active;
        af.saveCarboy(copy, isotope);
    };
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.Carboy();
            object.Class = "Carboy";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/isotope-modal.html',
            controller: 'IsotopeModalCtrl'
        });
        modalInstance.result.then(function () {
            $scope.isotopes = sortIsotopes(dataStore.Isotope);
        });
    };
})
    .controller('IsotopeModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    if (!af.getModalData().Isotope) {
        $scope.modalData.IsotopeCopy = new window.Isotope();
        $scope.modalData.IsotopeCopy.Class = "Isotope";
    }
    console.log($scope.modalData);
    $scope.save = function (copy, isotope) {
        af.saveIsotope(copy, isotope)
            .then($scope.close);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        af.deleteModalData();
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('AllOrdersCtrl', function ($scope, $q, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    var af = actionFunctionsFactory;
    $scope.af = af;

    // Wait for RadModels to load
    $rootScope.parcelPromise = $rootScope.radModelsPromise
        .then(function(){
            $scope.pis = dataStore.PrincipalInvestigator;
        });

    $scope.deactivate = function (carboy) {
        var copy = dataStoreManager.createCopy(carboy);
        copy.Retirement_date = new Date();
        af.saveCarboy(carboy.PrincipalInvestigator, copy, carboy);
    };
    $scope.openModal = function (object, pi) {
        var modalData = {};
        if (!object) {
            object = new window.Parcel();
            object.Class = "Parcel";
        }
        modalData.pi = pi;
        modalData[object.Class] = object;
        console.log(modalData);
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/parcel-modal.html',
            controller: 'PiDetailModalCtrl'
        });
    };
    $scope.openWipeTestModal = function (parcel, pi) {
        var modalData = {};
        modalData.pi = pi;
        modalData.Parcel = parcel;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/package-wipe-test.html',
            controller: 'WipeTestModalCtrl'
        });
    };
    $scope.updateParcelStatus = function (pi, parcel, status) {
        var copy = new window.Parcel;
        angular.extend(copy, parcel);
        copy.Status = status;
        copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
        af.saveParcel(copy, parcel, pi);
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PiDetailCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.activesShown = {
        showActivePos: false,
        showActiveParcles: false
    };
    $scope.switchShowActive = function (show) {
        $scope.activesShown[show] = !$scope.activesShown[show];
    };
    var getRadPi = function () {
        return actionFunctionsFactory.getRadPIById($stateParams.pi)
            .then(function (pi) {
            console.log("PI:", pi);
            // pi = new window.PrincipalInvestigator();
            pi.loadUser();
            pi.loadRooms();
            pi.loadActiveParcels();
            pi.loadPurchaseOrders();
            pi.loadPIAuthorizations();
            pi.loadCarboyUseCycles();
            pi.loadWasteBags();
            $rootScope.pi = pi;
            //$scope.getHighestAmendmentNumber($scope.mappedAmendments);
            $scope.cycles = $scope.getCycles(pi.CarboyUseCycles);
            return pi;
        }, function () {
        });
    };
    $rootScope.$watch("pi", function (oldPi, newPi) {
        console.log("WACHTED", oldPi, newPi, $rootScope.pi);
        if ($rootScope.pi)
            $scope.getHighestAmendmentNumber($rootScope.pi.Pi_authorization);
    });
    $rootScope.radPromise = af.getRadModels()
        .then(getRadPi);
    $scope.onSelectPi = function (pi) {
        $state.go('.pi-detail', { pi: pi.Key_id });
    };
    $scope.selectAmendement = function (num) {
        console.log(num);
        $scope.mappedAmendments.forEach(function (a) {
            if (a.weight == num) {
                $scope.selectedPiAuth = a;
                return;
            }
        });
    };
    $scope.getAuthRooms = function (piRooms, auth) {
        $scope.modalData.resultRooms = piRooms;
        if (auth.Rooms) {
            $scope.modalData.resultRooms =
                $scope.modalData.resultRooms.concat(auth.Rooms.filter(function (r) {
                    return !convenienceMethods.arrayContainsObject(piRooms, r);
                }));
        }
    };
    $scope.openModal = function (templateName, object, isAmendment) {
        console.log("yo", object);
        var modalData = {};
        modalData.pi = $scope.pi;
        modalData.isAmendment = isAmendment || false;
        if (object)
            modalData[object.Class] = object;
        console.log(modalData);
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: templateName + '.html',
            controller: 'PiDetailModalCtrl'
        });
        modalInstance.result.then(function (thing) {
            console.log("thing returned", thing);
            if (object && object.Class == "Parcel") {
                console.log(object, thing);
                $scope.selectedView = false;
                $scope.pi.ActiveParcels.forEach(function (p) {
                    if (p.Key_id == thing.Key_id)
                        p = thing;
                });
                //$scope.loading = $scope.pi.loadActiveParcels();
                $scope.reloadParcels = true;
                setTimeout(function () {
                    $scope.selectedView = 'parcels';
                    $scope.reloadParcels = true;
                    $scope.$apply();
                }, 10);
            }
            else if (thing.Class == "PIAuthorization") {
                $rootScope.pi.Pi_authorization = $rootScope.pi.Pi_authorization.map(function (piAuth) {
                    return piAuth.Key_id == thing.Key_id ? angular.extend(piAuth, thing) : piAuth;
                });
                $scope.getHighestAmendmentNumber($rootScope.pi.Pi_authorization, thing);
            }
            if (thing && thing.Class == "CarboyUseCycle") {
                $scope.cycles.push(thing);
            }
        });
    };
    $scope.getCycles = function (cycles) {
        if(!cycles){
            return [];
        }

        return cycles.filter(function (c) { return c.Status == Constants.CARBOY_USE_CYCLE.STATUS.IN_USE; });
    };
    $scope.getHighestAmendmentNumber = function (amendments, selected) {
        if (!amendments)
            return;
        console.log(amendments);
        var highestAuthNumber = 0;
        amendments.sort(function (a, b) {
            var sortVector = a.Amendment_number - b.Amendment_number || a.Key_id - b.Key_id || a.Approval_date - b.Approval_date;
            return sortVector;
        });
        console.log(amendments);
        for (var i = 0; i < amendments.length; i++) {
            var amendment = amendments[i];
            convenienceMethods.dateToIso(amendment.Approval_date, amendment, "Approval_date", true);
            convenienceMethods.dateToIso(amendment.Termination_date, amendment, "Termination_date", true);
            amendment.Amendment_label = amendment.Amendment_number ? "Amendment " + amendment.Amendment_number : "Original Authorization";
            amendment.Amendment_label = amendment.Termination_date ? amendment.Amendment_label + " (Terminated " + amendment.view_Termination_date + ")" : amendment.Amendment_label + " (" + amendment.view_Approval_date + ")";
            amendment.weight = i;
            if (selected && selected.Key_id && selected.Key_id == amendment.Key_id) {
                //amendment = selected;
                console.log(selected, amendment);
                $scope.selectedPiAuth = amendment;
            }
            console.log(i);
        }
        $scope.mappedAmendments = amendments;
        if (!selected)
            $scope.selectedPiAuth = $scope.mappedAmendments[amendments.length - 1];
        $scope.selectedAmendment = amendments.length - 1;
        return $scope.selectedAmendment;
    };
    $scope.openAuthModal = function (templateName, piAuth, auth) {
        var modalData = {};
        modalData.pi = $scope.pi;
        if (piAuth)
            modalData[piAuth.Class] = piAuth;
        if (auth)
            modalData[auth.Class] = auth;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: templateName + '.html',
            controller: 'PiDetailModalCtrl'
        });
        modalInstance.result.then(function (modifiedAuth) {
            console.log(auth, modifiedAuth);
            //angular.extend(auth, modifiedAuth)
        });
    };
    $scope.openWipeTestModal = function (parcel) {
        var modalData = { pi: null, Parcel: null };
        modalData.pi = $scope.pi;
        modalData.Parcel = parcel;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/package-wipe-test.html',
            controller: 'WipeTestModalCtrl'
        });
    };
    $scope.markAsArrived = function (pi, parcel) {
        var copy = new window.Parcel();
        angular.extend(copy, parcel);
        copy.Status = Constants.PARCEL.STATUS.DELIVERED;
        copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
        $scope.saving = af.saveParcel(copy, parcel, pi);
    };
    $scope.reopenAuth = function (piAuth) {
        var copy = new window.PIAuthorization();
        angular.extend(copy, piAuth);
        copy.Termination_date = null;
        copy.view_Termination_date = null;
        for (var n = 0; n < copy.Authorizations; n++) {
            copy.Authorizations[n].Is_active = true;
        }
        af.savePIAuthorization(copy, piAuth, $scope.pi, piAuth.Rooms || [], piAuth.Users || []);
    };
    $scope.removeOtherWasteType = function (type, pi) {
        var id = type.Key_id;
        var url = "../ajaxaction.php?action=assignOtherWasteType&remove=true&callback=JSON_CALLBACK&piId=" + pi.Key_id + "&owtId=" + id;
        $scope.saving = convenienceMethods.getDataAsDeferredPromise(url).then(function (type) {
            pi.OtherWasteTypes.forEach(function (owt, i) {
                console.log(owt, i, owt.Key_id, id);
                if (owt.Key_id == id) {
                    console.log(owt);
                    pi.OtherWasteTypes.splice(i, 1);
                }
            });
        });
    };
    $scope.openConditionsModal = function (piAuth) {
        console.log(piAuth);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/pi-auth-conditions-modal.html',
            controller: 'PiAuthConditionsModalCtrl',
            resolve: {
                Auth: function () { return angular.extend({}, piAuth, true); }
            }
        });
        modalInstance.result.then(function (c) {
            piAuth.Conditions = [].concat(c);
            console.log("post save", c, piAuth.Conditions);
        });
    };
})
    .controller('PiAuthConditionsModalCtrl', function ($scope, $rootScope, $modalInstance, Auth, actionFunctionsFactory, convenienceMethods, dataSwitchFactory) {
    var mapConditions = function (conditions) {
        return conditions.sort(function (a, b) { return a.Order_index > b.Order_index; }).map(function (c, i) {
            return angular.extend(c, { Order_index: i + 1 });
        });
    };
    Auth.Conditions = mapConditions(Auth.Conditions);
    $scope.auth = Auth;
    var filterConditions = function (conditions, auth) {
        if( !conditions ){
            return [];
        }

        return conditions.filter(function (c) {
            console.log(auth.Conditions, !convenienceMethods.arrayContainsObject(auth.Conditions, c));
            return !convenienceMethods.arrayContainsObject(auth.Conditions, c);
        });
    };
    var loadConditions = function () {
        return dataSwitchFactory.getAllObjects("RadCondition")
            .then(function () {
            $scope.conditions = filterConditions(dataStore.RadCondition, Auth);
        });
    };
    $scope.addCondtion = function (condition, auth) {
        auth.Conditions.push(condition);
        $scope.conditions = filterConditions(dataStore.RadCondition, auth);
        auth.Conditions = mapConditions(Auth.Conditions);
        console.log(auth.Conditions);
    };
    $scope.removeCondition = function (idx, auth) {
        auth.Conditions.splice(idx, 1);
        $scope.conditions = filterConditions(dataStore.RadCondition, auth);
        auth.Conditions = mapConditions(Auth.Conditions);
        console.log(auth.Conditions);
    };
    $scope.loading = loadConditions();
    $scope.save = function (piAuth) {
        console.log(piAuth);
        $rootScope.saving = actionFunctionsFactory.save(piAuth).then(function (returnedAuth) { $modalInstance.close(returnedAuth.C, onditions); });
    };
    $scope.move = function (auth, direction, idx) {
        var conditionToMoveIdx = auth.Conditions[idx].Order_index;
        console.log(idx, idx - direction, conditionToMoveIdx, auth.Conditions);
        auth.Conditions[idx].Order_index = auth.Conditions[idx - direction].Order_index;
        auth.Conditions[idx - direction].Order_index = conditionToMoveIdx;
        auth.Conditions = mapConditions(auth.Conditions);
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
})
    .controller('PiDetailModalCtrl', ['$scope', 'dataSwitchFactory', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, dataSwitchFactory, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, $http) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.cm = convenienceMethods;
        console.log("HEY", dataSwitchFactory, actionFunctionsFactory);
        //$scope.carboys = af.getCachedCollection('CarboyUseCycle');
        $scope.loadCarboys = function () {
            $scope.loadingCarboys = true;
            $scope.loading = dataSwitchFactory.getAllObjects("CarboyUseCycle", false, true).then(function () {
                $scope.carboys = dataStore.CarboyUseCycle;
                $scope.loadingCarboys = false;
            });
        };
        $scope.otherWasteTypes = dataStore.OtherWasteType;
        $scope.getType = function (typeId) {
            return $scope.otherWasteTypes.filter(function (t) { return t.Key_id == typeId; })[0];
        };
        $scope.addWasteType = function (pi, type) {
            console.log(pi);
            var url = "../ajaxaction.php?action=assignOtherWasteType&callback=JSON_CALLBACK&piId=" + pi.Key_id + "&owtId=" + type.Key_id;
            $scope.saving = convenienceMethods.getDataAsDeferredPromise(url).then(function (type) {
                console.log(pi);
                pi.OtherWasteTypes.push(type[0]);
                $modalInstance.close(type[0]);
            });
        };
        $scope.getBuildings = function () {
            $rootScope.loading = af.getAllBuildings().then(function (b) {
                console.log(dataStore);
                $scope.modalData.addRoom = true;
                console.log(dataStore.Building);
                $scope.modalData.Buildings = dataStore.Building;
                $scope.modalData.building = $scope.modalData.building ? $scope.modalData.building : {};
            });
        };
        $scope.getRooms = function () {
            $rootScope.loading = af.getAllRooms().then(function (b) {
                console.log(dataStore);
                $scope.modalData.addUser = true;
                console.log(dataStore.Building);
                $scope.modalData.Users = dataStore.User;
                $scope.modalData.building = $scope.modalData.building ? $scope.modalData.building : {};
            });
        };
        $scope.getUsers = function () {
            $rootScope.loading = af.getAllUsers(true).then(function (b) {
                $scope.modalData.addUser = true;
                $scope.modalData.Users = dataStore.User;
            });
        };
        if (!$scope.modalData.PurchaseOrderCopy) {
            $scope.modalData.PurchaseOrderCopy = {
                Class: 'PurchaseOrder',
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Purchase_order_number: null,
                Is_active: true
            };
        }
        if (!$scope.modalData.ParcelCopy) {
            $scope.modalData.ParcelCopy = {
                Class: 'Parcel',
                Purchase_order: null,
                Purchase_order_id: null,
                Status: 'Ordered',
                Isotope: null,
                Isotope_id: null,
                Arrival_date: null,
                Is_active: true,
                Principal_investigator_id: $scope.modalData.pi.Key_id
            };
        }
        if (!$scope.modalData.PIAuthorizationCopy) {
            $scope.modalData.PIAuthorizationCopy = {
                Class: 'PIAuthorization',
                Rooms: [],
                Authorization_number: null,
                Is_active: true,
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Authorizations: []
            };
        }
        $scope.getApprovalDate = function (a, isAmendment) {
            if (isAmendment) {
                return "";
            }
            return a.view_Approval_date;
        };
        if (!$scope.modalData.AuthorizationCopy) {
            $scope.modalData.AuthorizationCopy = {
                Class: 'Authorization',
                Principal_investigator_id: $scope.modalData.PIAuthorizationCopy && $scope.modalData.PIAuthorizationCopy.Principal_investigator_id ? $scope.modalData.PIAuthorizationCopy.Principal_investigator_id : null,
                Isotope: {},
                Isotope_id: null,
                Is_active: true,
                Pi_authorization_id: $scope.modalData.PIAuthorizationCopy ? $scope.modalData.PIAuthorizationCopy.Key_id : null
            };
        }
        if (!$scope.modalData.SolidsContainerCopy) {
            $scope.modalData.SolidsContainerCopy = {
                Class: 'SolidsContainer',
                Room_id: null,
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Is_active: true
            };
        }
        var isotopePromise = af.getAllIsotopes()
            .then(function () {
            $scope.isotopes = af.getCachedCollection('Isotope');
        }, function () {
            $rootScope.error = "There was a problem retrieving the list of all isotopes.  Please check your internet connection and try again.";
        });
        $scope.getTerminationDate = function (piAuth) {
            if (piAuth.Termination_date)
                piAuth.Form_Termination_date = convenienceMethods.dateToIso(piAuth.Termination_date);
        };
        $scope.selectIsotope = function (auth) {
            auth.Isotope = dataStoreManager.getById("Isotope", auth.Isotope_id);
            if ($scope.modalData.AuthorizationCopy && $scope.modalData.AuthorizationCopy.Isotope) {
                $scope.modalData.AuthorizationCopy.Isotope_id = $scope.modalData.AuthorizationCopy.Isotope.Key_id;
                if ($scope.modalData.ParcelCopy && $scope.modalData.ParcelCopy.Isotope)
                    $scope.modalData.ParcelCopy.Isotope_id = $scope.modalData.ParcelCopy.Isotope.Key_id;
            }
        };
        $scope.selectPO = function (po) {
            if ($scope.modalData.ParcelCopy)
                $scope.modalData.ParcelCopy.PurchaseOrderrder = dataStoreManager.getById("PurchaseOrder", $scope.modalData.ParcelCopy.Purchase_order_id);
        };
        $scope.selectAuth = function (po) {
            if ($scope.modalData.ParcelCopy)
                $scope.modalData.ParcelCopy.Authorization = dataStoreManager.getById("Authorization", $scope.modalData.ParcelCopy.Authorization_id);
        };
        $scope.addIsotope = function (id) {
            var newAuth = new Authorization();
            newAuth.Class = "Authorization";
            newAuth.Pi_authorization_id = id;
            newAuth.Is_active = newAuth.isIncluded = true;
            newAuth.Isotope = new Isotope();
            newAuth.Isotope.Class = "Isotope";
            $scope.modalData.PIAuthorizationCopy.Authorizations.push(newAuth);
        };
        $scope.close = function (auth) {
            af.deleteModalData();
            if (auth) {
                var i = auth.Authorizations.length;
                while (i--) {
                    var is = auth.Authorizations[i];
                    if (!is.Key_id)
                        auth.Authorizations.splice(i, 1);
                }
            }
            $modalInstance.dismiss();
        };
        $scope.getHasOriginal = function (auth) {
            return $scope.modalData.pi.Pi_authorization.some(function (a) {
                return !a.Amendment_number || a.Amendment_number == "0";
            });
        };
        $scope.evaluateOrignal = function (auth) {
            if (auth.isOriginal)
                auth.Amendment_number = null;
        };
        $scope.savePIAuthorization = function (copy, auth, terminated, rooms, users) {
            console.log("calling this", terminated);
            var pi = $scope.modalData.pi;
            if ($scope.modalData.isAmendment)
                copy.Key_id = null;
            copy.Approval_date = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.view_Approval_date));
            if (!terminated) {
                for (var n = 0; n < copy.Authorizations; n++) {
                    if (!terminated && !copy.Authorizations[n].isIncluded) {
                        copy.Authorizations.splice(n, 1);
                    }
                }
            }
            else {
                copy.Is_active = false;
                console.log("getting termination date");
                copy.Termination_date = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.Form_Termination_date));
                for (var n = 0; n < copy.Authorizations; n++) {
                    copy.Authorizations[n].Is_active = false;
                }
            }
            console.log(copy); //return;
            af.savePIAuthorization(copy, auth, pi, rooms, users)
                .then(function (returnedAuth) {
                $modalInstance.close(returnedAuth);
                af.deleteModalData();
            });
        };
        $scope.saveAuthorization = function (piAuth, copy, auth) {
            //return;
            copy.Pi_authorization_id = copy.Pi_authorization_id || pi.Pi_authorization.Key_id;
            af.deleteModalData();
            af.saveAuthorization(piAuth, copy, auth).then(function (returnedAuth) { return $modalInstance.close(returnedAuth); });
        };
        $scope.saveParcel = function (pi, copy, parcel) {
            af.deleteModalData();
            af.saveParcel(pi, copy, parcel).then(function (r) {
                if (parcel) {
                    console.log(r);
                    _.assign(parcel, r);
                    $modalInstance.close(r);
                }
            });
        };
        $scope.savePO = function (pi, copy, po) {
            $modalInstance.dismiss();
            af.deleteModalData();
            af.savePurchaseOrder(pi, copy, po);
        };
        $scope.saveContainer = function (pi, copy, container) {
            $modalInstance.dismiss();
            af.deleteModalData();
            af.saveSolidsContainer(pi, copy, container);
        };
        $scope.saveCarboy = function (pi, copy, carboy) {
            $modalInstance.dismiss();
            af.deleteModalData();
            af.saveCarboy(pi, copy, carboy);
        };
        $scope.markAsArrived = function (pi, copy, parcel) {
            copy.Status = Constants.PARCEL.STATUS.ARRIVED;
            copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
            $scope.saveParcel(pi, copy, parcel);
        };
        $scope.addCarboyToLab = function (cycle, pi) {
            console.log(cycle);
            //cycle.loadCarboy();
            cycle.Is_active = false;
            var cycleCopy = {
                Class: "CarboyUseCycle",
                Room_id: cycle.Room ? cycle.Room.Key_id : null,
                Principal_investigator_id: pi.Key_id,
                Key_id: cycle.Key_id || null,
                Carboy_id: cycle.Carboy_id
            };
            console.log(cycleCopy);
            af.deleteModalData();
            af.addCarboyToLab(cycleCopy, pi).then(function (c) {
                console.log(c);
                $modalInstance.close(c);
            });
        };
        $scope.roomIsAuthorized = function (room, authorization) {
            room.isAuthorized = false;
            if (!authorization.Rooms && authorization.Key_id)
                return;
            if (authorization.Rooms) {
                var i = authorization.Rooms.length;
                while (i--) {
                    if (authorization.Rooms[i].Key_id == room.Key_id) {
                        return true;
                    }
                }
                return false;
            }
            else {
                return true;
            }
        };
        $scope.userIsAuthorized = function (user, authorization) {
            user.isAuthorized = false;
            if (!authorization.Users && authorization.Key_id)
                return;
            if (authorization.Users) {
                var i = authorization.Users.length;
                while (i--) {
                    if (authorization.Users[i].Key_id == user.Key_id) {
                        return true;
                    }
                }
                return false;
            }
            else {
                return true;
            }
        };
        $scope.getAuthRooms = function (piRooms, auth) {
            $scope.modalData.resultRooms = piRooms;
            if (auth.Rooms) {
                $scope.modalData.resultRooms =
                    $scope.modalData.resultRooms.concat(auth.Rooms.filter(function (r) {
                        return !convenienceMethods.arrayContainsObject(piRooms, r);
                    }));
            }
        };
        $scope.selectRoom = function (room) {
            console.log(room);
            $scope.modalData.PIAuthorizationCopy.Rooms.push(room);
            $scope.getAuthRooms($scope.modalData.pi.Rooms, $scope.modalData.PIAuthorizationCopy);
            $scope.modalData.addRoom = false;
        };
        $scope.getAuthUsers = function (users, auth) {
            if (!auth.Users)
                auth.Users = [];
            $scope.modalData.resultUsers = users || [];
            if (auth.Users) {
                $scope.modalData.resultUsers =
                    $scope.modalData.resultUsers.concat(auth.Users.filter(function (u) {
                        return !convenienceMethods.arrayContainsObject(users, u);
                    }));
            }
        };
        $scope.selectUser = function (user) {
            if (!$scope.modalData.PIAuthorizationCopy.Users)
                $scope.modalData.PIAuthorizationCopy.Users = [];
            $scope.modalData.PIAuthorizationCopy.Users.push(user);
            user.isAuthorized = true;
            $scope.getAuthUsers($scope.modalData.pi.Users, $scope.modalData.PIAuthorizationCopy);
            $scope.modalData.addUser = false;
        };
        $scope.departmentIsAuthorized = function (department, authorization) {
            department.isAuthorized = false;
            if (!authorization.Departments && authorization.Key_id)
                return;
            if (authorization.Departments) {
                var i = authorization.Departments.length;
                while (i--) {
                    if (authorization.Departments[i].Key_id == department.Key_id) {
                        return true;
                    }
                }
                return false;
            }
            else {
                return true;
            }
            return false;
        };
        $scope.getSuggestedAmendmentNumber = function (pi) {
            //get a suggetion for amendment number
            $scope.suggestedAmendmentNumber;
            var i = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
            var gapFound = false;
            if (i > 1) {
                while (i--) {
                    if (!$scope.modalData.PIAuthorizationCopy.Authorizations[i]) {
                        gapFound = true;
                        break;
                    }
                }
                if (gapFound) {
                    $scope.suggestedAmendmentNumber = i;
                }
                else {
                    $scope.suggestedAmendmentNumber = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
                }
            }
            else {
                $scope.suggestedAmendmentNumber = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
            }
            return $scope.suggestedAmendmentNumber;
        };
    }]);
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .controller('RadminMainCtrl', function ($scope, $rootScope, actionFunctionsFactory, $state, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.$state = $state;

    // Load RAD Models; retain promise for chaining
    $rootScope.radModelsPromise = af.getRadModels()
        .then(function (models) {
        var pis = dataStoreManager.get('PrincipalInvestigator');
        console.log(dataStore);
        $scope.typeAheadPis = pis;
        return;
    });
    $scope.onSelectPi = function (pi) {
        $state.go('radmin.pi-detail', {
            pi: pi.Key_id
        });
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .controller('RadminParentCtrl', function ($scope, $q, $http, actionFunctionsFactory, $state, pis) {
    alert('in contorller');
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('TransferCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.dsm = dataStoreManager;
    $scope.modalData = af.getRadModels();
    var getParcels = function () {
        return af.getAllParcels()
            .then(function (parcels) {
            dataStore.Parcel.forEach(function (p) {
                p.loadAuthorization();
            });
            return $scope.parcels = dataStore.Parcel;
        });
    };
    var getAllPis = function () {
        return af.getAllPIs().then(function (pis) {
            return $scope.pis = dataStore.PrincipalInvestigator;
        });
    };
    var getUses = function () {
        return af.getAllParcelUses().then(function (pis) {
            return $scope.uses = dataStore.ParcelUse;
        });
    };
    var getAuths = function () {
        return af.getAllPIAuthorizations().then(function (pis) {
            return $scope.auths = dataStore.PIAuthorization;
        });
    };
    $scope.loading = getAllPis()
        .then(getUses)
        .then(getAuths)
        .then(getParcels);
    $scope.openTransferInModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object) {
            modalData.Parcel = object;
            modalData.pi = dataStoreManager.getById("PrincipalInvestigator", object.Principal_investigator_id);
        }
        else {
            modalData.Parcel = { Class: "Parcel" };
        }
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/transfer-in-modal.html',
            controller: 'TransferModalCtrl'
        });
    };
    $scope.openTransferInventoryModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object) {
            modalData.Parcel = object;
            modalData.pi = dataStoreManager.getById("PrincipalInvestigator", object.Principal_investigator_id);
        }
        else {
            modalData.Parcel = { Class: "Parcel" };
        }
        modalData.Parcel.Is_active = true;
        modalData.Parcel.Status = Constants.PARCEL.STATUS.DELIVERED;
        //all inventory transfers get a date of the end of the year before the system's o
        console.log(modalData);
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/transfer-inventory-modal.html',
            controller: 'TransferModalCtrl'
        });
    };
    $scope.openTransferOutModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object) {
            if (object.Parcel_id) {
                var parcel = dataStoreManager.getById("Parcel", object.Parcel_id);
                if (parcel)
                    var auth = dataStoreManager.getById("Authorization", parcel.Authorization_id);
                if (auth)
                    var piAuth = dataStoreManager.getById("PIAuthorization", auth.Pi_authorization_id);
                if (piAuth)
                    modalData.pi = dataStoreManager.getById("PrincipalInvestigator", piAuth.Principal_investigator_id);
                modalData.pi.loadActiveParcels().then(function () {
                    modalData.ParcelUse = object;
                    af.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'views/admin/admin-modals/transfer-out-modal.html',
                        controller: 'TransferModalCtrl'
                    });
                });
            }
        }
        else {
            modalData.ParcelUse = { Class: "ParcelUse" };
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-out-modal.html',
                controller: 'TransferModalCtrl'
            });
        }
    };
    $scope.openTransferBetweenModal = function (object) {
        console.log(object);
        var modalData = {};
        modalData.transferBetween = true;
        if (object) {
            if (!object.Destination_parcel_id) {
                object.DestinationParcel = new Parcel();
                object.DestinationParcel.Class = "Parcel";
            }
            if (object.Parcel_id) {
                var parcel = dataStoreManager.getById("Parcel", object.Parcel_id);
                if (parcel)
                    var auth = dataStoreManager.getById("Authorization", parcel.Authorization_id);
                if (auth)
                    var piAuth = dataStoreManager.getById("PIAuthorization", auth.Pi_authorization_id);
                if (piAuth)
                    modalData.pi = dataStoreManager.getById("PrincipalInvestigator", piAuth.Principal_investigator_id);
                modalData.pi.loadActiveParcels().then(function () {
                    modalData.ParcelUse = object;
                    af.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'views/admin/admin-modals/transfer-between-modal.html',
                        controller: 'TransferModalCtrl'
                    });
                });
            }
        }
        else {
            modalData.ParcelUse = { Class: "ParcelUse" };
            var object = modalData.ParcelUse;
            object.DestinationParcel = new Parcel();
            object.DestinationParcel.Class = "Parcel";
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-between-modal.html',
                controller: 'TransferModalCtrl'
            });
        }
    };
})
    .controller('TransferModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.dataStore = dataStore;
        $scope.dsm = dataStoreManager;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.cv = convenienceMethods;
        //set up local model for transfer between
        $scope.onSelectPi = function (pi, parcel) {
            pi.loadPIAuthorizations();
            pi.loadActiveParcels();
            $scope.modalData.PI = pi;
        };
        $scope.getHighestAuth = function (pi) {
            if (pi && pi.Pi_authorization && pi.Pi_authorization.length) {
                var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                        return moment(amendment.Approval_date).valueOf();
                    }]);
                return auths[auths.length - 1];
            }
        };
        $scope.saveTransferIn = function (copy, parcel) {
            console.log(parcel);
            copy.Transfer_in_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_Transfer_in_date));
            af.saveParcel(copy, parcel, $scope.modalData.PI)
                .then($scope.close);
        };
        $scope.saveTransferOut = function (parcel, copy, use) {
            $scope.modalData.tooMuch = false;
            if (copy.Quantity > parcel.Remainder) {
                $scope.modalData.tooMuch = "You can't transfer that much.";
                return;
            }
            parcel.loadUses().then(function () {
                var amt = new ParcelUseAmount();
                amt.Parcel_use_id = copy.Key_id || null;
                if (copy.ParcelUseAmounts && copy.ParcelUseAmounts.length) {
                    amt.Key_id = copy.ParcelUseAmounts[0].Key_id || null;
                    amt.Comments = copy.ParcelUseAmounts[0].Comments;
                }
                amt.Class = "ParcelUseAmount";
                amt.Curie_level = copy.Quantity;
                amt.Waste_type_id = Constants.WASTE_TYPE.TRANSFER;
                copy.ParcelUseAmounts = [amt];
                copy.Date_transferred = convenienceMethods.setMysqlTime(copy.view_Date_transferred);
                console.log(copy);
                //if it walks like a duck
                if (!use.Key_id)
                    use = false;
                $scope.saving = af.saveParcelUse(parcel, copy, use)
                    .then($scope.close);
            });
        };
        $scope.selectReceivingPi = function (pi) {
            $scope.loading = pi.loadPIAuthorizations().then(function () {
                console.log(pi);
                $scope.auths = $scope.getHighestAuth(pi);
                console.log($scope.auths);
                return $scope.auths;
            });
        };
        $scope.getReceivingPi = function (use) {
            var pi = dataStoreManager.getById("PrincipalInvestigator", use.DestinationParcel.Principal_investigator_id);
            $scope.selectReceivingPi(pi);
            return pi;
        };
        $scope.saveTransferBetween = function (parcel, copy, use) {
            $scope.modalData.tooMuch = false;
            if (copy.Quantity > parcel.Remainder) {
                $scope.modalData.tooMuch = "You can't transfer that much.";
                return;
            }
            var parcels = dataStoreManager.get("Parcel");
            $scope.rsError = false;
            parcels.forEach(function (p) {
                if (p.Rs_number == copy.DestinationParcel.Rs_number)
                    $scope.rsError = true;
            });
            if ($scope.rsError)
                return;
            parcel.loadUses().then(function () {
                var amt = new ParcelUseAmount();
                amt.Parcel_use_id = copy.Key_id || null;
                if (copy.ParcelUseAmounts && copy.ParcelUseAmounts.length) {
                    amt.Key_id = copy.ParcelUseAmounts[0].Key_id || null;
                    amt.Comments = copy.ParcelUseAmounts[0].Comments;
                }
                amt.Class = "ParcelUseAmount";
                amt.Curie_level = copy.Quantity;
                amt.Waste_type_id = Constants.WASTE_TYPE.TRANSFER;
                copy.ParcelUseAmounts = [amt];
                copy.Date_transferred = convenienceMethods.setMysqlTime(copy.view_Date_transferred);
                copy.DestinationParcel.Transfer_in_date = convenienceMethods.setMysqlTime(copy.view_Date_transferred);
                console.log(copy);
                //if it walks like a duck
                if (!use.Key_id)
                    use = false;
                $scope.saving = af.saveParcelUse(parcel, copy, use)
                    .then($scope.close);
            });
        };
        $scope.getTransferNumberSuggestion = function (str) {
            console.log(str);
            var parcels = dataStoreManager.get("Parcel");
            var num = 0;
            var finalNum = 1;
            parcels.forEach(function (p) {
                if (p.Rs_number.indexOf(str) != -1) {
                    console.log(p.Rs_number.substring(2));
                    var pNum = parseInt(p.Rs_number.substring(2));
                    if (pNum > num)
                        num = pNum;
                }
            });
            return num + 1;
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }]);
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('WipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    var getParcels = function () {
        return af.getAllParcels()
            .then(function (parcels) {
                if( !parcels ){
                    parcels = dataStoreManager.get('Parcel');
                }

            var i = parcels.length;
            while (i--) {
                parcels[i].loadPrincipalInvestigator();
            }
            $rootScope.parcels = dataStore.Parcel;
            return parcels;
        });
    };
    var getMiscTests = function () {
        return af.getAllMiscellaneousWipeTests()
            .then(function (tests) {
            if (!dataStore.MiscellaneousWipeTest)
                dataStore.MiscellaneousWipeTest = [];
            $rootScope.miscellaneousWipeTests = dataStore.MiscellaneousWipeTest;
        });
    };
    getParcels()
        .then(getMiscTests);
    $scope.editParcelWipeTest = function (parcel, test) {
        $rootScope.ParcelWipeTestCopy = {};
        if (!test) {
            $rootScope.ParcelWipeTestCopy = new window.ParcelWipeTest();
            $rootScope.ParcelWipeTestCopy.Parcel_id = parcel.Key_id;
            $rootScope.ParcelWipeTestCopy.Class = "ParcelWipeTest";
            $rootScope.ParcelWipeTestCopy.Is_active = true;
        }
        else {
            af.createCopy(test);
        }
        parcel.Creating_wipe = true;
    };
    $scope.cancelParcelWipeTestEdit = function (parcel) {
        parcel.Creating_wipe = false;
        $rootScope.ParcelWipeTestCopy = {};
    };
    $scope.editWipeParcelWipe = function (wipeTest, wipe) {
        $rootScope.ParcelWipeCopy = {};
        if (!wipeTest.Parcel_wipes)
            wipeTest.Parcel_wipes = [];
        var i = wipeTest.Parcel_wipes.length;
        while (i--) {
            wipeTest.Parcel_wipes[i].edit = false;
        }
        if (!wipe) {
            $rootScope.ParcelWipeCopy = new window.ParcelWipe();
            $rootScope.ParcelWipeCopy.Parcel_wipe_test_id = wipeTest.Key_id;
            $rootScope.ParcelWipeCopy.Class = "ParcelWipe";
            $rootScope.ParcelWipeCopy.edit = true;
            $rootScope.ParcelWipeCopy.Is_active = true;
            wipeTest.Parcel_wipes.unshift($rootScope.ParcelWipeCopy);
        }
        else {
            wipe.edit = true;
            af.createCopy(wipe);
        }
    };
    $scope.addMiscWipes = function (test) {
        //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
        if (!test.Miscellaneous_wipes)
            test.Miscellaneous_wipes = [];
        var i = 10;
        while (i--) {
            var miscellaneousWipe = new window.MiscellaneousWipe();
            miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
            miscellaneousWipe.Class = "MiscellaneousWipe";
            miscellaneousWipe.edit = true;
            test.Miscellaneous_wipes.push(miscellaneousWipe);
        }
        test.adding = true;
    };
    $scope.cancelParcelWipeEdit = function (wipe, test) {
        wipe.edit = false;
        $rootScope.ParcelWipeCopy = {};
        var i = test.Parcel_wipes.length;
        while (i--) {
            if (!test.Parcel_wipes[i].Key_id) {
                test.Parcel_wipes.splice(i, 1);
            }
        }
    };
    $scope.clouseOutMWT = function (test) {
        af.createCopy(test);
        $rootScope.MiscellaneousWipeTestCopy.Closeout_date = convenienceMethods.setMysqlTime(new Date());
        af.saveMiscellaneousWipeTest($rootScope.MiscellaneousWipeTestCopy);
    };
    $scope.cancelMiscWipeTestEdit = function (test) {
        $scope.Creating_wipe = false;
        $rootScope.ParcelWipeTestCopy = {};
    };
    $scope.editMiscWipe = function (test, wipe) {
        $rootScope.MiscellaneousWipeCopy = {};
        if (!test.Miscellaneous_wipes)
            test.Miscellaneous_wipes = [];
        var i = test.Miscellaneous_wipes.length;
        while (i--) {
            test.Miscellaneous_wipes[i].edit = false;
        }
        if (!wipe) {
            $rootScope.MiscellaneousWipeCopy = new window.MiscellaneousWipe();
            $rootScope.MiscellaneousWipeCopy.Class = "MiscellaneousWipe";
            $rootScope.MiscellaneousWipeCopy.Is_active = true;
            $rootScope.MiscellaneousWipeCopy.miscellaneous_wipe_test_id = test.Key_id;
            $rootScope.MiscellaneousWipeCopy.edit = true;
            test.Miscellaneous_wipes.unshift($rootScope.MiscellaneousWipeCopy);
        }
        else {
            wipe.edit = true;
            af.createCopy(wipe);
        }
    };
    $scope.cancelMiscWipeEdit = function (test, wipe) {
        wipe.edit = false;
        $rootScope.MiscellaneousWipeCopy = {};
        var i = test.Miscellaneous_wipes.length;
        while (i--) {
            if (!test.Miscellaneous_wipes[i].Key_id) {
                console.log();
                test.Miscellaneous_wipes.splice(i, 1);
            }
        }
    };
    //Suggested/common locations for performing parcel wipes
    $scope.parcelWipeLocations = ['Background', 'Outside', 'Inside', 'Bag', 'Styrofoam', 'Cylinder', 'Vial', 'Lead Pig'];
    $scope.openModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/misc-wipe-modal.html',
            controller: 'MiscellaneousWipeTestCtrl'
        });
    };
})
    .controller('MiscellaneousWipeTestCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        if (!$scope.modalData.MiscellaneousWipeTest) {
            $scope.modalData.MiscellaneousWipeTest = new window.MiscellaneousWipeTest();
            $scope.modalData.MiscellaneousWipeTest.Class = "MiscellaneousWipeTest";
            $scope.modalData.MiscellaneousWipeTest.Is_active = true;
        }
        $scope.save = function (test) {
            af.saveMiscellaneousWipeTest(test)
                .then($scope.close);
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }])
    .controller('WipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {
        //TODO:  if af.getModalData() doesn't have wipeTest, create and save one for it
        //       creating wipe test message while loading
        //
        //
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.editParcelWipeTest = function (parcel, originalParcel, force) {
            if (!parcel.Wipe_test || !parcel.Wipe_test.length) {
                parcel.Wipe_test = [modelInflatorFactory.instantiateObjectFromJson(new window.ParcelWipeTest())];
                parcel.Wipe_test[0].parcel_id = parcel.Key_id;
                parcel.Wipe_test[0].Class = "ParcelWipeTest";
                parcel.Wipe_test[0].edit = true;
                parcel.Wipe_test[0].Parcel_wipes = [];
                for (var i = 0; i < 7; i++) {
                    var wipe = new window.ParcelWipe();
                    wipe.Parcel_wipe_test_id = parcel.Key_id ? parcel.Key : null;
                    wipe.Rading_type = "LSC";
                    wipe.edit = true;
                    wipe.Class = 'ParcelWipe';
                    if (i == 0)
                        wipe.Location = "Background";
                    parcel.Wipe_test[0].Parcel_wipes.push(wipe);
                }
                if (!force)
                    var force = true;
            }
            else {
                console.log(parcel);
                af.createCopy(parcel.Wipe_test[0]);
            }
            if (force)
                originalParcel.Creating_wipe = true;
        };
        $scope.editParcelWipeTest($scope.modalData.ParcelCopy, $scope.modalData.Parcel);
        $scope.cancelParcelWipeTestEdit = function (parcel) {
            parcel.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {};
        };
        $scope.editWipeParcelWipe = function (wipeTest, wipe, force) {
            $rootScope.ParcelWipeCopy = {};
            if (!wipeTest.Parcel_wipes)
                wipeTest.Parcel_wipes = [];
            var i = wipeTest.Parcel_wipes.length;
            while (i--) {
                wipeTest.Parcel_wipes[i].edit = false;
            }
            if (!wipe) {
                af.getModalData().Wipe_test = new window.ParcelWipe();
                af.getModalData().Wipe_test.Parcel_wipe_test_id = wipeTest.Key_id;
                af.getModalData().Wipe_test.Class = "ParcelWipe";
                af.getModalData().Wipe_test.edit = true;
                af.getModalData().Wipe_test.Is_active = true;
            }
            else {
                wipe.edit = true;
                af.createCopy(wipe);
            }
        };
        $scope.addMiscWipes = function (test) {
            //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
            if (!test.Miscellaneous_wipes)
                test.Miscellaneous_wipes = [];
            var i = 10;
            while (i--) {
                var miscellaneousWipe = new window.MiscellaneousWipe();
                miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
                miscellaneousWipe.Class = "MiscellaneousWipe";
                miscellaneousWipe.edit = true;
                test.Miscellaneous_wipes.push(miscellaneousWipe);
            }
            test.adding = true;
        };
        $scope.cancelParcelWipeEdit = function (wipe, test) {
            wipe.edit = false;
            $rootScope.ParcelWipeCopy = {};
            var i = test.Parcel_wipes.length;
            while (i--) {
                if (!test.Parcel_wipes[i].Key_id) {
                    test.Parcel_wipes.splice(i, 1);
                }
            }
        };
        $scope.onClick = function () {
            alert('wrong ctrl');
        };
        //Suggested/common locations for performing parcel wipes
        $scope.parcelWipeLocations = [
            {
                Name: "Background"
            },
            {
                Name: "Outside"
            },
            {
                Name: "Inside"
            },
            {
                Name: "Bag"
            },
            {
                Name: "Styrofoam"
            },
            {
                Name: "Cylinder"
            },
            {
                Name: "Vial"
            },
            {
                Name: "Lead Pig"
            }
        ];
        $scope.setLocation = function (wipe) {
            if (wipe.Location) {
                var i = $scope.parcelWipeLocations.length;
                while (i--) {
                    if (wipe.Location == $scope.parcelWipeLocations.Name) {
                        wipe.DropLocation = $scope.parcelWipeLocations[i];
                        $scope.$apply();
                        break;
                    }
                }
            }
            console.log(wipe);
        };
        $scope.save = function (test) {
            af.saveParcelWipeTest(test)
                .then($scope.close);
        };
        $scope.close = function () {
            $scope.modalData.Parcel.Creating_wipe = false;
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }])
    .controller('OtherWasteCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.loading = af.getAllOtherWasteTypes().then(function () {
        console.log(dataStore);
        $scope.otherWasteTypes = dataStore.OtherWasteType;
    });
    $rootScope.switchActive = function (owt) {
        console.log(owt);
        owt.Is_active = !owt.Is_active;
        $scope.saving = af.save(owt)
            .then(function () { });
    };
    $scope.openModal = function (object) {
        console.log(object);
        var modalData = {};
        if (!object) {
            var object = new OtherWasteType();
            object.Is_active = true;
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/other-waste-modal.html',
            controller: 'OtherWasteModalCtrl'
        });
        modalInstance.result.then(function (returned) {
            console.log(returned);
            if (object && object.Key_id) {
                angular.extend(object, returned);
            }
            else {
                $scope.otherWasteTypes.push(returned);
            }
        });
    };
})
    .controller('OtherWasteModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    console.log($scope.modalData);
    $scope.save = function (owt) {
        console.log(owt);
        af.save(owt)
            .then($scope.close);
    };
    $scope.close = function (owt) {
        $modalInstance.close(owt);
        console.log(owt);
        af.deleteModalData();
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
})
    .controller('IsotopeReportCtrl', function ($scope, actionFunctionsFactory, $rootScope) {
    $rootScope.loading = actionFunctionsFactory.getInventoryReport().then(function () {
        $scope.reports = dataStore.RadReportDTO;
    });
})
    .controller('ZapCtrl', function ($scope, $modal) {
    $scope.openModal = function (object) {
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/zap-modal.html',
            controller: 'ZapModalCtrl'
        });
    };
})
    .controller('ZapModalCtrl', function ($scope, $http, $modalInstance, actionFunctionsFactory) {
    $scope.save = function (owt) {
        console.log(owt);
        $scope.resettingRadData = actionFunctionsFactory.resetRadData()
        .then(
            function( returned ) {
                if( !returned || returned.status !== 200){
                    alert("Unable to reset RAD Data. Contact your system administrator.");
                }
                else{
                    if(window.confirm('RAD Data has been reset, and the page needs to be reloaded. Reload now?')){
                        window.location.reload(true);
                    }
                    else{
                        console.warn("Rad data has been reset and needs to be reloaded");
                    }
                }
            },
            function(err){
                console.error("Can't do it!", err);
            });
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
})
    .controller('ConditionsCtrl', function ($scope, $modal, actionFunctionsFactory, dataSwitchFactory) {
    var af = $scope.af = actionFunctionsFactory;
    var loadConditions = function () {
        return dataSwitchFactory.getAllObjects("RadCondition")
            .then(function () {
            console.log("STORE", dataStore);
            $scope.conditions = dataStore.RadCondition;
        });
    };
    $scope.openModal = function (condition) {
        var condition = !condition ? { Class: "RadCondition" } : condition;
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/condition-modal.html',
            controller: 'ConditionsModalCtrl',
            resolve: {
                Condition: function () { return angular.extend({}, condition); }
            }
        });
        modalInstance.result.then(function (c) {
            console.log("returned", c);
            if (!condition.Key_id)
                $scope.conditions.splice(0, 0, condition);
            angular.extend(condition, c);
        });
    };
    $scope.loading = loadConditions();
})
    .controller('ConditionsModalCtrl', function ($scope, $rootScope, $modalInstance, Condition, actionFunctionsFactory) {
    $scope.condition = Condition;
    $scope.tinymceOptions = {
        plugins: 'link lists',
        toolbar: 'bold | italic | underline | lists | bullist | numlist',
        menubar: false,
        elementpath: false,
        content_style: "p,ul li, ol li {font-size:14px}"
    };
    $scope.save = function (condition) {
        console.log(condition);
        $rootScope.saving = actionFunctionsFactory.save(condition).then(function (c) { return $modalInstance.close(c); });
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
});
