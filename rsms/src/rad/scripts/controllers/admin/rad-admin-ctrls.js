'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('AdminPickupCtrl', function ($scope, actionFunctionsFactory, $rootScope, $modal, convenienceMethods, radUtilitiesFactory) {
    var af = actionFunctionsFactory;

    $scope.af = af;

    function loadPickupsData(){
        console.debug("Loading data for Pickups...");

        // Load pickups
        $rootScope.pickupsPromise = $rootScope.radModelsPromise
            .then(af.getAllDrums)
            .then(af.getAllPickups)
            .then(function(){
                $scope.pickups = dataStore.Pickup || [];
                console.debug("loaded pickups", $scope.pickups);

                // Collect included containers into a single array for each pickup
                $scope.pickups.forEach(p => {
                    p.includedContainers = radUtilitiesFactory.getAllWasteContainersFromPickup(p);
                });

                // Group by status
                $scope.pickup_groups = radUtilitiesFactory.groupPickupsByStatus($scope.pickups);

                // Default to show all groups
                $scope.show_all_pickup_groups = true;
            }
        );

        // Load ready containers
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

            $scope.pickupReadyContainersByPI = radUtilitiesFactory
                .applyWasteTypeLabels(containers)
                .reduce( reduceContainers, []);
        });
    }

    // Perform data load & prep
    loadPickupsData();

    /////////////////////
    // Scope functions //

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

    $scope.filterPickupGroups = function(group){
        $scope.show_all_pickup_groups = group === undefined;
        if( $scope.show_all_pickup_groups ){
            // show all
            $scope.pickup_groups.forEach(g => {
                g.active = true;
            });
        }
        else{
            // show one
            $scope.pickup_groups.forEach(g => {
                g.active = (g === group);
            });
        }
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
            targetStatusName: targetStatusName || radUtilitiesFactory.getStatusNameByValue(pickup.Status),
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
                        .then(function(data){
                            // Invalidate saved Pickup and containers
                            dataStoreManager.purge('Pickup');
                            dataStoreManager.purge('WasteBag');
                            dataStoreManager.purge('CarboyUseCycle');
                            dataStoreManager.purge('ScintVialCollection');
                            dataStoreManager.purge('OtherWasteContainer');
                            
                            // Reload our data!
                            loadPickupsData();
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
.controller('AdminPickupEditModalCtrl', function($scope, $modalInstance, $q, actionFunctionsFactory, convenienceMethods, radUtilitiesFactory, pickupsValidationFactory){
    
    var modalData = actionFunctionsFactory.getModalData();
    var pickup = modalData.pickup;

    $scope.validationErrors = [];
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
        return radUtilitiesFactory
            .getStatusNameByValue(pickup.Status || Constants.PICKUP.STATUS.REQUESTED)
            .toLowerCase();
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

    $scope.containers.forEach(container => {
        container.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(container.Class);

        if( selectAllContainers ){
            container.isSelectedForPickup = true;
        }
    });

    $scope.isDisposed = function(container){
        var disp = radUtilitiesFactory.isContainerDisposed(container);
        return disp;
    };

    $scope.addOrRemoveContainer = function(container) {
        if( $scope.isDisposed(container) ){
            // Do not allow add/remove of Disposed container
            return;
        }

        container.isSelectedForPickup = !container.isSelectedForPickup;
        $scope.edited_pickup_contents = true;
        console.debug((container.isSelectedForPickup ? 'Add' : 'Remove') + " container from pickup", container);
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
        $scope.edited_pickup_date = true;
        pickup.Pickup_date = convenienceMethods.setMysqlTime(date);
    }

    $scope.editPickupDateCancel = function(pickup){
        $scope.editDate = false;
        $scope.view_Pickup_date = undefined;
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
        c.editing_comment = false;
    };

    $scope.hasChanged = function(){
        return $scope.edited_comment
            || $scope.edited_pickup_date
            || $scope.edited_pickup_contents;
    };

    $scope.validate = function(){
        $scope.validationErrors = pickupsValidationFactory.validatePickup($scope.pickup, $scope.containers);

        $scope.valid = $scope.validationErrors.length == 0;

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

        $modalInstance.close({
            promiseToSave: $q.all(promises)
        });
    }

    $scope.save = function(pickup) {
        if( $scope.validate() ){
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
                    // Not selected for pickup, but we still have ID. This is being removed
                    console.debug('Remove from Pickup:', c);
                    c.Pickup_id = null;
                    modifiedContainers.push(c);
                }
                else if( c.edited_comment ){
                    // No change to selection, but the comment was edited
                    console.debug('Edited container comment:', c);
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
            console.debug("Close/save edit-pickup modal");
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
            .filter(c => c.Current_carboy_use_cycle != null)
            .map(c => c.Current_carboy_use_cycle.Principal_investigator_id)
            .filter(pid => pid);

        console.debug(pi_ids.length + "/" + $scope.carboys.length + " have PI assignments");

        var pis = dataStore.PrincipalInvestigator.filter(pi => pi_ids.includes(pi.Key_id));
        console.debug("Found " + pis.length + "/" + pi_ids + " assigned PIs");

        $scope.carboys.forEach(carboy => {
            if( !carboy.PI && carboy.Current_carboy_use_cycle && carboy.Current_carboy_use_cycle.Principal_investigator_id ){
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
        .then(af.getAllRadPis)
        .then(getCarboysFromDatastore);

    $scope.deactivate = function (carboy) {
        if(window.confirm('Are you sure you want to Retire Carboy ' + carboy.Carboy_number + '?')){
            $scope.saveCarboyPromise = af.retireCarboy( carboy )
            .then(function(updated){
                // TODO: reload data?
            });
        }
    };

    $scope.recirculateCarboy = function(carboy) {
        $scope.saveCarboyPromise = af.recirculateCarboy( carboy )
            .then(function(updated){
                // TODO: reload data?
                updated.PI = undefined;
            });
    };

    $scope.disposedStatuses = [
        Constants.CARBOY_USE_CYCLE.STATUS.POURED,
        Constants.CARBOY_USE_CYCLE.STATUS.DRUMMED
    ];

    $scope.allowRecirculateCarboy = function(carboy){
        if( !carboy.Current_carboy_use_cycle ){
            return false;
        }

        return $scope.disposedStatuses.includes(carboy.Current_carboy_use_cycle.Status);
    };

    $scope.allowRetireCarboy = function(carboy){
        if( !carboy.Current_carboy_use_cycle ){
            return false;
        }

        return $scope.allowRecirculateCarboy(carboy) || carboy.Current_carboy_use_cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.AVAILABLE;
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
    .controller('disposalCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, radUtilitiesFactory, $rootScope, $modal) {
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
            if (!dataStore.OtherWasteContainer)
                dataStore.OtherWasteContainer = [];

            $rootScope.OtherWasteContainers = dataStore.OtherWasteContainer;

            // Apply PI names
            $rootScope.OtherWasteContainers.forEach(c => c.PiName = $scope.getPiName(c.Principal_investigator_id));

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
        $scope.drumableContainers = $rootScope.WasteBags.concat($rootScope.ScintVialCollections).concat($rootScope.OtherWasteContainers)
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
        console.debug("drumableContainers: ", $scope.drumableContainers);
        return $scope.drumableContainers;
    };

    var getOtherWasteTypes = function(){
        return af.getAllOtherWasteTypes()
            .then(function (otherWasteTypes) {
                console.debug("Loaded Other Waste Types", otherWasteTypes);
                $rootScope.otherWasteTypes = dataStore.OtherWasteType;
                return otherWasteTypes;
            }
        );
    };

    $scope.showDrumDetails = function(drum){
        if( !drum ){
            $scope.detailDrum = null;
            return;
        }

        $scope.detailDrum = {
            drum: drum,
            containers: radUtilitiesFactory.getAllWasteContainersFromDrum(drum)
        };
    }

    $scope.removeFromDrum = function(container){
        if( container && $scope.detailDrum ){
            if( window.confirm("Are you sure you want to remove " + (container.Label || container.CarboyNumber) + " from " + $scope.detailDrum.drum.Label + "?") ){
                console.debug("Remove container ", container, " from drum ", $scope.detailDrum.drum);

                $scope.removeFromDrumPromise = actionFunctionsFactory.removeContainerFromDrum(container)
                    .then(function(){
                        var drums = dataStoreManager.get("Drum");

                        return af.replaceDrums(drums)
                            .then(function (returnedDrums) {
                            console.log('Reloaded Drums', returnedDrums);

                            var drum = dataStoreManager.getById("Drum", $scope.detailDrum.drum.Key_id);
                            $scope.showDrumDetails(drum);
                        });
                    })
                    .then(loadDisposalsData);
            }
        }
    };

    $scope.drumDetailsContainersFilter = function(o1){
        return $scope.detailDrum && o1.Drum_id == $scope.detailDrum.drum.Key_id;
    }

    $scope.getCycleRowClass = function(cycle){
        switch(cycle.Status){
            case Constants.CARBOY_USE_CYCLE.STATUS.AT_RSO: return 'at-rso';

            case Constants.CARBOY_USE_CYCLE.STATUS.DECAYING: return 'decaying';

            case Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM:
            case Constants.CARBOY_USE_CYCLE.STATUS.MIXED_WASTE:
                return 'disposable';

            default: return '';
        }

    };

    $scope.hasClosedNotPickedUp = function (containers) {
        return containers.some(function (c) { return c.Pickup_id == null; });
    };

    var loadDisposalsData = function(){
        console.debug("Loading disposals data...");
        $rootScope.radPromise = getAllWasteBags()
        .then(getIsotopes)
        .then(getSVCollections)
        .then(getAllDrums)
        .then(getCycles)
        .then(getMiscWaste)
        .then(getOtherWaste)
        .then(getContainers)
        .then(getOtherWasteTypes)
        .then(function(){console.debug("disposals data loaded.")});
    };

    loadDisposalsData();

    $scope.date = new Date();

    $scope.getOtherWasteOfType = function(type){
        return ($rootScope.OtherWasteContainers || [])
            .filter(c => c.Other_waste_type_id == type.Key_id);
    };

    $scope.getPiName = function (piId){
        var matches = dataStore.PrincipalInvestigator.filter(pi => pi.Key_id == piId);
        if( matches.length )
            return matches[0].Name;
        return null;
    };

    $scope.rsoClearContainer = function(container){
        if( !container.Clearable ){
            console.warn("Container is not clearable", container);
            return;
        }

        // REQUIRE CONFIRMATION

        if (window.confirm("Are you sure you want to clear " + container.Label + "?")) {
            $rootScope.saving = af.closeWasteContainer(container)
                .then(function(r){
                    angular.extend(container, r);
                    return r;
                });
        }
    };

    $scope.manageCarboyDisposal = function (cycle) {
        af.setModalData({
            cycle: af.createCopy(cycle)
        });

        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/carboy-disposal-modal.html',
            controller: 'ManageCarboyDisposalCtrl',
            windowClass: 'modal-dialog-wide'
        });

        modalInstance.result.then(
            function (arg){
                if(arg.promiseToSave){
                    console.debug("Cycle is saving...");
                    $scope.CarboyUseCycleSaving = arg.promiseToSave
                        .then(function(data){
                            console.debug("Saved CarboyUseCycle disposal information.");

                            // Refresh our table
                            getCycles();
                        }
                    );
                }
            }
        );
    };

    $scope.assignDrum = function (object) {
        var modalData = {};

        if(object){
            // Special handling for WasteBag
            if( object.Class == "WasteBag" ){
                if (!object.PickupLots || !object.PickupLots.length) {
                    object.PickupLots = [{
                        Class: "PickupLot",
                        Currie_level: 0,
                        Waste_bag_id: object.Key_id,
                        Waste_type_id: Constants.WASTE_TYPE.SOLID,
                        Isotope_id: null
                    }];
                }
            }

            modalData[object.Class] = object;
        }

        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-assignment.html',
            controller: 'DrumAssignmentCtrl'
        });

        modalInstance.result.then( function(){
            console.debug("Close drum details after drum assignment");
            $scope.showDrumDetails(null);
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
                    // misnomer... 'ParcelUseAmount.Carboy_id' refers to the CYCLE ID
                    modalData.ParcelUseAmount.Carboy_id = container.Key_id;
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
.controller('DisposalHistoryCtrl', function($rootScope, $scope, $q, actionFunctionsFactory, radUtilitiesFactory){
    var af = actionFunctionsFactory;
    $scope.loadData = function loadData(){
        console.debug("Waiting to load waste archive");
        return $scope.loadArchivePromise = $rootScope.radModelsPromise
            .then(function(){
                console.debug("Load all waste containers...");
                return $q.all([
                    af.getAllDrums(),
                    af.getAllWasteBags(),
                    af.getAllCarboys(),
                    af.getAllCarboyUseCycles(),
                    af.getAllScintVialCollections(),
                    af.getAllOtherWasteTypes(),
                    af.getAllOtherWasteContainers(),
                    af.getAllIsotopes(),
                    af.getAllMiscellaneousWaste()
                ]);
            })
            .then(function(){
                // Get list of all containers
                console.debug("Get historical waste containers");
                $scope.containers = radUtilitiesFactory.getAllWasteContainers();

                // Load pickup and drum data for all containters
                $scope.containers.forEach(c => {
                    if( c.loadPickup != undefined ){
                        c.loadPickup();
                    }

                    if( c.loadDrum != undefined ){
                        c.loadDrum();
                    }
                });

                console.debug($scope.containers);

                // Get list of all container types
                $scope.wasteTypes = ['WasteBag', 'ScintVialCollection', 'CarboyUseCycle'].map(stType => {
                    return {
                        Class: stType,
                        ClassLabel: radUtilitiesFactory.getFriendlyWasteLabel(stType),
                        active: true
                    };
                });

                // Add other types
                if( dataStore.OtherWasteType ){
                    dataStore.OtherWasteType.forEach(type => {
                        $scope.wasteTypes.push({
                            Key_id: type.Key_id,
                            Class: type.Class,
                            ClassLabel: type.Name,
                            disabled: !type.Is_active,
                            active: true
                        })
                    });
                }
            }
        );
    };

    $scope.getIconClass = radUtilitiesFactory.getIconClassByContainer;

    $scope.getPiName = function getPiName(piId){
        var pi = dataStoreManager.getById('PrincipalInvestigator', piId);
        return pi ? pi.Name : '';
    }

    $scope.getDrumLabel = function getDrumLabel(container){
        // If container is NOT drummable, return 'N/A'
        var nonDrummableCarboyStatuses = [
            Constants.CARBOY_USE_CYCLE.STATUS.DECAYING,
            Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM,
            Constants.CARBOY_USE_CYCLE.STATUS.POURED
        ];

        var notDrummable = container.Class == 'OtherWasteContainer'
                        || (container.Class == 'CarboyUseCycle' && nonDrummableCarboyStatuses.includes(container.Status));

        if( notDrummable ){
            return "N/A";
        }
        else if( container.Drum_id ){
            var drum = container.Drum;

            if( !drum ){
                drum = dataStoreManager.getById('Drum', container.Drum_id)
            }

            return drum.Label;
        }

        return '';
    };

    $scope.loadData();
})
    .controller('ManageCarboyDisposalCtrl', function($rootScope, $scope, $modalInstance, actionFunctionsFactory, convenienceMethods){
        console.debug("Open carboy disposal management modal");

        $scope.statuses = Constants.CARBOY_USE_CYCLE.STATUS;
        $scope.cycle = actionFunctionsFactory.getModalData().cycle;
        $scope.changes = {
            comments: $scope.cycle.Comments,
            volume: $scope.cycle.Volume,
            readings: {
                add: [],
                edit: []
            }
        };

        // Determine applicable statuses
        function getAvailableTransitions(status){
            switch(status){

                case $scope.statuses.AT_RSO:
                    return [
                        $scope.statuses.DECAYING, $scope.statuses.HOT_ROOM, $scope.statuses.MIXED_WASTE
                    ];

                case $scope.statuses.DECAYING: return [$scope.statuses.HOT_ROOM]

                default:
                    return [];
            }
        }

        function getAvailableDisposals(status){
            switch(status){
                // Actual disposal
                case $scope.statuses.HOT_ROOM:    return [$scope.statuses.POURED];
                case $scope.statuses.MIXED_WASTE: return [$scope.statuses.DRUMMED];

                default:
                    return [];
            }
        }

        $scope.transitions = getAvailableTransitions($scope.cycle.Status);
        $scope.disposals = getAvailableDisposals($scope.cycle.Status);

        $scope.tabs = [
            {name:'Transition', active:true},
            {name:'Readings'}
        ];

        $scope.isTabActive = function(tabName){
            for(var index in $scope.tabs){
                var tab = $scope.tabs[index];
                if(tab.active && tab.name === tabName){
                    return true;
                }
            }

            return false;
        };

        $scope.changeTab = function(tab){
            $scope.tabs.forEach(t => t.active = t == tab);
        };

        $scope.changeStatus = function(newStatus){
            if( !newStatus ){
                console.debug("Reset status");
                $scope.changes.status = undefined;
                return;
            }

            $scope.changes.changed = true;
            $scope.changes.status = newStatus;

            console.debug("Change status to ", newStatus);

            if( newStatus == $scope.statuses.POURED ){
                $scope.changes.pourDate = $scope.getDefaultDate();
            }
            else if( newStatus == $scope.statuses.HOT_ROOM ){
                $scope.changes.hotDate = $scope.getDefaultDate();
            }
        };

        $scope.getDefaultDate = function(){
            return $scope.formatDate(new Date());
        };

        $scope.formatDate = function(date){
            return convenienceMethods.setMysqlTime(date);
        }

        $scope.getIsotopeName = function(isotope_id){
            return $rootScope.isotopes.filter(i => i.Key_id == isotope_id)[0].Name;
        };

        $scope.editReading = function(reading){
            $scope.editing = true;

            if( !reading ){
                // Add empty reading (enable edit) to cycle
                $scope.changes.readings.add.push({
                    EditCopy: {}
                });
            }
            else{
                reading.EditCopy = actionFunctionsFactory.createCopy(reading);
            }
        };

        $scope.editReadingCancel = function(reading){
            $scope.editing = false;
            reading.EditCopy = undefined;

            if( !reading.Key_id ){
                // This was a new one; remove it
                var idx = $scope.changes.readings.add.indexOf(reading);
                if( idx > -1 ){
                    $scope.changes.readings.add.splice(idx, 1);
                }
            }
        };

        $scope.validateReading = function validateReading( reading ){
            if( !reading.Isotope_id || !reading.Curie_level || !reading.Date_read ){
                // Missing required fields
                return false;
            }

            return true;
        };

        $scope.editReadingSave = function(reading){
            // validate changes
            if( !$scope.validateReading(reading.EditCopy) ){
                // TODO: display message?
                return;
            }

            // copy changes
            console.debug("Save reading:", reading);

            // Queue changes to be saved
            if( reading.Key_id ){
                $scope.changes.readings.edit.push(reading.EditCopy);
            }
            // else it already exists in Add array

            // Replace contents
            reading.Isotope = reading.EditCopy.Isotope;
            reading.Isotope_id = reading.EditCopy.Isotope_id;
            reading.Curie_level = reading.EditCopy.Curie_level;

            if( typeof reading.EditCopy.Date_read == 'string'){
                reading.Date_read = reading.EditCopy.Date_read;
            }
            else{
                reading.Date_read = $scope.formatDate(reading.EditCopy.Date_read);
            }

            reading.EditCopy = undefined;

            reading.edited = true;
            $scope.editing = false;
            $scope.changes.changed = true;
            console.debug("Reading queued for save:", reading);
        };

        $scope.validate = function(cycle, changes){
            if( !changes.changed ){
                return true;
            }

            $scope.validationErrors = [];
            if( changes.status ){
                // Validate Status change

                // Require Pour Date when Pouring
                if( changes.status == $scope.statuses.POURED && !changes.pourDate ){
                    $scope.validationErrors.push("Pour Date is required");
                }

                if( changes.status == $scope.statuses.DRUMMED && !changes.drumId){
                    $scope.validationErrors.push("Drum is required");
                }

                // TODO: If status is now a disposal, ensure we have a 'final reading'
            }

            return $scope.validationErrors.length == 0;
        };

        $scope.save = function(cycle, changes){
            console.debug("Save changes to ", cycle, $scope.changes);

            // validate changes
            if( $scope.validate(cycle, changes) ){
                $modalInstance.close({
                    promiseToSave: actionFunctionsFactory.saveCarboyUseCycleDisposalDetails(cycle, changes)
                });
            }

        };

        $scope.cancel = function(){
            actionFunctionsFactory.deleteModalData();
            $modalInstance.dismiss();
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
                .then(reloadDrums);
        };
        $scope.saveSVCollection = function (collection, copy) {
            $scope.close();
            $rootScope.saving = af.saveSVCollection(collection, copy)
                .then(reloadDrums);
        };
        var reloadDrums = function () {
            var drums = dataStoreManager.get("Drum");
            af.replaceDrums(drums)
                .then(function (returnedDrums) {
                console.log('Reloaded Drums', returnedDrums);
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
            $modalInstance.close();
        };
        $scope.cancel = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }
    }])
    .controller('DrumShipCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.validate = function(copy) {
            $scope.validationErrors = [];

            // Shipment date is required
            if( copy.Pickup_date == null ){
                $scope.validationErrors.push("Shipment date is required.");
            }

            return $scope.validationErrors.length == 0;
        };

        $scope.shipDrum = function (drum, copy) {
            if( !$scope.validate(copy) ){
                return;
            }

            // format dates, if required
            if( typeof copy.Pickup_date != 'string' ){
                copy.Pickup_date = convenienceMethods.setMysqlTime(copy.Pickup_date);
            }

            if( typeof copy.Date_destroyed != 'string' ){
                copy.Date_destroyed = convenienceMethods.setMysqlTime(copy.Date_destroyed);
            }

            $rootScope.saving = af.saveDrum(drum, copy);
            $scope.close();
        };
        $scope.saveDrum = function (drum, copy) {
            $rootScope.saving = af.saveDrum(drum, copy);
            $scope.close();
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.close();
        };
        $scope.cancel = function () {
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
        if( !$state.params.pi_inventory ){
            // No PI selected
            return;
        }

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

    $scope.selectQuarterOption = function selectQuarterOption( index ){
        $scope.selectedQuarter = $scope.quarterOptions[index];
    }

    /** Create (or update) a quarterly inventory */
    $scope.createInventory = function ( quarter ) {
        if( !quarter ){
            return;
        }

        let startDate = quarter.startDate;
        let endDate = quarter.endDate;
        $scope.QuarterlyInventorySaving = af.createQuarterlyInventory(startDate, endDate)
            .then(function (inventory) {
            $scope.inventory = inventory;
            console.log(inventory);
        }, function () { });
    };

    // Populate the form to be able to create:
    //   CURRENT quarter
    //   PREVIOUS quarter
    //   NEXT quarter
    function q_opt( name, moment ){
        let start = convenienceMethods.setMysqlTime(moment.startOf('quarter'));
        let end = convenienceMethods.setMysqlTime(moment.endOf('quarter'));

        return {
            name: name,
            displayStart: convenienceMethods.dateToIso(start),
            displayEnd: convenienceMethods.dateToIso(end),
            startDate: start,
            endDate: end,
        };
    }

    let quarter_current = moment().quarter( moment().quarter() );
    let quarter_previous = moment().quarter( moment().quarter() ).subtract(1, 'quarter');
    let quarter_next = moment().quarter( moment().quarter() ).add(1, 'quarter');

    $scope.quarterOptions = [
        q_opt( 'Next Quarter', quarter_next ),
        q_opt( 'This Quarter', quarter_current ),
        q_opt( 'Last Quarter', quarter_previous )
    ];
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
            $scope.isotopes = dataStore.Isotope;
        }, function () { });
    };
    $scope.af = af;
    $rootScope.isotopesPromise = getAllIsotopes();
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.Isotope();
            object.Is_active = true;
            object.Class = "Isotope";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/isotope-modal.html',
            controller: 'IsotopeModalCtrl'
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
    $scope.cancel = function(){
        $modalInstance.dismiss();
        af.deleteModalData();
    }
    $scope.close = function () {
        $modalInstance.close();
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

    $rootScope.getAuthorizedRoomsForPi = function (pi){
        if( pi.CurrentPi_authorization )
            return pi.CurrentPi_authorization.Rooms || [];

        return [];
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

        // Flag all Users, Rooms, and Departments as 'authorized' to get around UI/Controller flow bug
        copy.Users.forEach(u => u.isAuthorized = true);
        copy.Rooms.forEach(r => r.isAuthorized = true);
        copy.Departments.forEach(d => d.isAuthorized = true);

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
        $rootScope.saving = actionFunctionsFactory.save(piAuth).then(function (returnedAuth) { $modalInstance.close(returnedAuth.Conditions); });
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
    .controller('PiDetailModalCtrl', ['$scope', 'dataSwitchFactory', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'parcelValidationFactory', '$timeout',
    function ($scope, dataSwitchFactory, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, parcelValidationFactory, $timeout) {
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
        $scope.getRoomsInBuilding = function(building){
            if( !building.Rooms ){
                $rootScope.loading = af.getAllRooms().then(function(rooms){
                    building.Rooms = rooms.filter(r => r.Building_id == building.Key_id);
                });
            }
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
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                ParcelAuthorizations: []
            };

            // Add a single parcelauth after load
            $timeout( () => $scope.addParcelAuth() );
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

        $scope.addParcelAuth = function addParcelAuth(){
            if( $scope.modalData.ParcelCopy ){
                let current_percent = $scope.getParcelAuthPercentage();

                $scope.modalData.ParcelCopy.ParcelAuthorizations.push({
                    Class: 'ParcelAuthorization',
                    Parcel_id: $scope.modalData.ParcelCopy.Key_id,
                    Authorization_id: null,
                    Percentage: 100.0 - current_percent
                });

                console.debug("Add parcel authorization", $scope.modalData.ParcelCopy.ParcelAuthorizations);
            }
        };

        $scope.removeParcelAuth = function removeParcelAuth(parcelAuth){
            if( $scope.modalData.ParcelCopy ){
                let idx = $scope.modalData.ParcelCopy.ParcelAuthorizations.indexOf(parcelAuth);
                if( idx > -1 ){
                    console.debug("Remove ", parcelAuth);
                    $scope.modalData.ParcelCopy.ParcelAuthorizations.splice(idx, 1);
                }
                else {
                    console.error("No parcel auth found");
                }
            }
        };

        $scope.selectAuth = function (parcel_auth) {
            if ($scope.modalData.ParcelCopy){
                parcel_auth.Authorization = dataStoreManager.getById("Authorization", parcel_auth.Authorization_id);
            }
        };

        $scope.getParcelAuthPercentage = function getParcelAuthPercentage(){
            return parcelValidationFactory.getParcelAuthorizationPercentage($scope.modalData.ParcelCopy);
        };

        $scope.getParcelAuthQuantity = function getParcelAuthQuantity(parcel_auth){
            return parcelValidationFactory.getParcelAuthorizationQuantity($scope.modalData.ParcelCopy, parcel_auth);
        };

        $scope.addIsotope = function (id) {
            var newAuth = new Authorization();
            newAuth.Class = "Authorization";
            newAuth.Pi_authorization_id = id;
            newAuth.Is_active = true;
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

            if ( terminated ) {
                copy.Is_active = false;
                console.log("getting termination date");
                copy.Termination_date = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.Form_Termination_date));
                for (var n = 0; n < copy.Authorizations; n++) {
                    copy.Authorizations[n].Is_active = false;
                }

                // Flag all Users, Rooms, and Departments as 'authorized' to get around UI/Controller flow bug
                copy.Users.forEach(u => u.isAuthorized = true);
                copy.Rooms.forEach(r => r.isAuthorized = true);
                copy.Departments.forEach(d => d.isAuthorized = true);
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

        $scope.getParcelValidationErrors = function getParcelValidationErrors(){
            return parcelValidationFactory.validateParcel(
                $scope.modalData.pi,
                $scope.modalData.ParcelCopy
            );
        };

        $scope.saveParcel = function (copy, parcel, pi) {
            af.deleteModalData();
            af.saveParcel(copy, parcel, pi).then(function (r) {
                if (parcel) {
                    console.log(r);
                    _.assign(parcel, r);
                }
                $modalInstance.close(r);
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
    .controller('RadminMainCtrl', function ($scope, $rootScope, actionFunctionsFactory, $state, $modal, $timeout) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.$state = $state;

    // Load RAD Models; retain promise for chaining
    $rootScope.radModelsPromise = af.getRadModels()
        .then(function (models) {
        // RSMS-957 extract PI Names from store
        var pis = dataStoreManager.get('PrincipalInvestigatorNameDto');
        console.debug('DataStore', dataStore);
        $scope.typeAheadPis = pis;
        return;
    });
    $scope.onSelectPi = function (pi) {
        $state.go('radmin.pi-detail', {
            pi: pi.Key_id
        });

        // Deselect entry after navigation has started
        $timeout(() => {
            if( $scope.pi ){
                $scope.pi.selected = null;
            }
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

    var getParcels = function () {
        return af.getAllParcels()
            .then(function (parcels) {
                if( dataStore.Parcel ){
                    dataStore.Parcel.forEach(function (p) {
                        p.loadAuthorization();
                    });
                }
        });
    };
    var getAllPis = function () {
        return af.getAllPIs();
    };
    var getUses = function () {
        return af.getAllParcelUses();
    };
    var getAuths = function () {
        return af.getAllPIAuthorizations();
    };
    $scope.loading = af.getRadModels()
        .then(getAllPis)
        .then(getAuths)
        .then(getUses)
        .then(getParcels)
        .then( () => {
            $scope.parcels = dataStore.Parcel;
            $scope.pis = dataStore.PrincipalInvestigator;
            $scope.uses = dataStore.ParcelUse;
            $scope.auths = dataStore.PIAuthorization;

            console.log("Done loading transfers data");
        });
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
        modalInstance.result.then(getParcels);
    };
    $scope.openTransferOutModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object) {
            if (object.Parcel_id) {
                var parcel = dataStoreManager.getById("Parcel", object.Parcel_id);
                if (parcel)
                    var auth = dataStoreManager.getById("Authorization", parcel.ParcelAuthorizations[0].Authorization_id);
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
                    modalInstance.result.then(getParcels);
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
            modalInstance.result.then(getParcels);
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
                    var auth = dataStoreManager.getById("Authorization", parcel.ParcelAuthorizations[0].Authorization_id);
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
                    modalInstance.result.then(getParcels);
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
            modalInstance.result.then(getParcels);
        }
    };
})
.directive("transferParcelIsotopeNames", function () {
    return {
        restrict: 'E',
        scope: {
            parcel: "="
        },
        controller: function($scope){
            $scope.isSingle = function(){ return $scope.parcel.ParcelAuthorizations.length == 1; };
            $scope.shortName = function(){
                if( $scope.isSingle() ){
                    return $scope.parcel.ParcelAuthorizations[0].Isotope.Name;
                }
                else {
                    return 'Multiple';
                }
            };
            $scope.isoNameCSV = function(){
                return $scope.parcel.ParcelAuthorizations.map(pa => pa.Isotope.Name).join(', ');
            }
        },
        template: `<i class="icon-help" ng-if="!isSingle()" popover="{{isoNameCSV()}}"></i><span>{{shortName()}}</span>`
     }
})
    .controller('TransferModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {
        var af = actionFunctionsFactory;
        af.clearError();
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

        $scope.allowQuantityEdit = function(){
            // Only allow editing of the quantity if:
            // there is only a single use amount listed
            // OR this is NOT a conversion of an existing use
            return $scope.modalData.ParcelUseCopy.ParcelUseAmounts.length == 1
                && $scope.modalData.ParcelUseCopy.Key_id == null;
        }

        $scope.saveTransferIn = function (copy, parcel) {
            console.log(parcel);
            copy.Transfer_in_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_Transfer_in_date));
            copy.Is_active = true;
            af.saveParcel(copy, parcel, $scope.modalData.PI)
                .then($scope.close);
        };
        $scope.saveTransferOut = function (parcel, copy, use, convertUse) {
            $scope.modalData.tooMuch = false;
            if( convertUse ){
                // Validate conversion of Use; do not compare to quantity
                console.debug("Converting use to Transfer:", convertUse);
            }
            else if (copy.Quantity > parcel.Remainder) {
                $scope.modalData.tooMuch = "You can't transfer that much.";
                return;
            }
            parcel.loadUses().then(function () {
                // View will always provide at least one ParcelUseAmount

                // Ensure all Amounts are Transfers and apply Comments
                if(copy.ParcelUseAmounts && copy.ParcelUseAmounts.length ){
                    copy.ParcelUseAmounts.forEach(amt => {
                        amt.Class = amt.Class || "ParcelUseAmount";
                        amt.Parcel_use_id = copy.Key_id || null;

                        amt.Curie_level = amt.Curie_level || copy.Quantity;
                        amt.Waste_type_id = Constants.WASTE_TYPE.TRANSFER;
                        amt.Comments = copy.ParcelUseAmounts[0].Comments;
                    });
                }

                copy.Date_transferred = convenienceMethods.setMysqlTime(copy.view_Date_transferred);

                console.debug("Saving Transfer-Out", copy);
                //if it walks like a duck
                if (!use.Key_id)
                    use = false;
                $scope.saving = af.saveParcelUse(parcel, copy, use)
                    .then($scope.close);
            });
        };

        $scope.selectParcelForTransfer = function (parcel, use){
            if( use ){
                // We are converting an actual use
                $scope.modalData.selectedParcelUse = dataStoreManager.getById('ParcelUse', use.Key_id);
                $scope.modalData.ParcelUseCopy = dataStoreManager.createCopy( $scope.modalData.selectedParcelUse );
                console.debug("Select ParcelUse to transform to a Transfer:", $scope.modalData.selectedParcelUse);
            }

            $scope.modalData.forceSelectParcel = false;
            $scope.modalData.ParcelUseCopy.Parcel_id = parcel.Key_id;
            $scope.modalData.selectedParcel = dataStoreManager.getById('Parcel', $scope.modalData.ParcelUseCopy.Parcel_id);
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
            if( pi ) {
                $scope.selectReceivingPi(pi);
            }
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
            var parcels = dataStoreManager.get("Parcel") || [];
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

            if( parcels ){
                var i = parcels.length;
                while (i--) {
                    parcels[i].loadPrincipalInvestigator();
                }
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
        $scope.otherWasteTypes = dataStore.OtherWasteType || [];
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
            $scope.otherWasteTypes = dataStore.OtherWasteType || [];
            if (object && object.Key_id) {
                angular.extend(object, returned);
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
            .then(function(saved){
                dataStoreManager.store(saved);
                return saved;
            })
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
        if( $scope.reports && $scope.reports.length ){
            $scope.date_loaded = new Date($scope.reports[0].ReportDate);
        }
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
        modalInstance.result.then(loadConditions);
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
        $rootScope.saving = actionFunctionsFactory.save(condition)
            .then(function (c) {
                dataStoreManager.store(c);
                return c;
            })
            .then(function (c) { return $modalInstance.close(c); });
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
});
