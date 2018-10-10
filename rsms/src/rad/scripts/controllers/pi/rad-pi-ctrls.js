'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PiRadHomeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods, radUtilitiesFactory) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
            console.log(pi);
            $scope.pi = pi;
        }, function () { })
        .then(function(){
            $scope.piAuth = radUtilitiesFactory.getPIAuthorization($scope.pi);
        });
    $scope.getNeedsLabWipes = function (pi) {
        var d = new Date();
        var oneWeekAgo = convenienceMethods.setMysqlTime(new Date(d.setDate((d.getDate() - 7))).toLocaleString());
        var hasWipes = pi.ActiveParcels.filter(function (p) {
            return p.ParcelUses.some(function (pu) { return pu.Date_used < oneWeekAgo; });
        }).length;
        var recentWipePerformed = pi.WipeTests.filter(function (wt) { return wt.Date_created > oneWeekAgo; }).length;
        return hasWipes && !recentWipePerformed;
    };
});
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('AuthCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    var getRadPi = function () {
        return actionFunctionsFactory.getRadPIById($stateParams.pi)
            .then(function (pi) {
            $rootScope.pi = pi;
            return pi;
        }, function () {
        })
            .then(function (pi) {
            return pi.loadPIAuthorizations().then(function () {
                var auth = $rootScope.getHighestAuth(pi);
                auth.Amendment_label = auth.Amendment_number ? "Amendment " + auth.Amendment_number : "Original Authorization";
                auth.weight = parseInt(auth.Amendment_number || "0");
                return auth;
            });
        })
            .then(function (auth) {
            $scope.roomsLoading = auth.loadRooms().then(function () { auth.loadDepartments(); });
            return $scope.selectedPiAuth = auth;
        });
    };
    $rootScope.parcelPromise = getRadPi();
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # InventoryViewCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('InventoryViewCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.constants = Constants;
    $rootScope.loading = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        console.log(pi);
        $scope.pi = pi;
    }, function () { });
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('OrdersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, radUtilitiesFactory) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.constants = Constants;
    $rootScope.parcelPromise = af.getAllIsotopes()
        .then(getPI);
    var getPI = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        console.log(pi);
        $scope.pi = pi;
    }, function () { })
    .then(function(){
        $scope.piAuthorization = radUtilitiesFactory.getPIAuthorization($scope.pi);
    });

    $scope.openModal = function (object) {
        if( !$scope.piAuthorization || $scope.piAuthorization.Termination_date ){
            window.alert("Your current authorization is terminated. No new orders can be placed.");
            return;
        }

        var modalData = {};
        modalData.pi = $scope.pi;
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/orders-modal.html',
            controller: 'OrderModalCtrl'
        });
    };
})
    .controller('OrderModalCtrl', function ($scope, actionFunctionsFactory, $modalInstance, radUtilitiesFactory) {
    var af = actionFunctionsFactory;
    $scope.constants = Constants;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    $scope.errors = {};

    $scope.piAuthorization = radUtilitiesFactory.getPIAuthorization($scope.modalData.pi);

    if (!$scope.modalData.ParcelCopy) {
        $scope.modalData.ParcelCopy = {
            Class: 'Parcel',
            Is_active: true,
            Status: Constants.PARCEL.STATUS.REQUESTED,
            Principal_investigator_id: $scope.modalData.pi.Key_id
        };
    }

    $scope.onSelectAuthorization = function(){
        $scope.findRelevantInventory($scope.modalData.ParcelCopy, $scope.modalData.pi);
        $scope.checkMaxOrder($scope.modalData.ParcelCopy);
    };

    /**
     * Finds the most recent isotope inventory from the modaldata's PI
     * @param {Parcel} parcel
     */
    $scope.findRelevantInventory = function(parcel, pi){
        if( !parcel ){
            // No parcel to check, so no inventory to find
            $scope.relevantInventory = null;
            return;
        }

        console.debug("Find relevant inventory for parcel " + parcel.Authorization_id);

        // Find the relevant inventory
        var i = pi.CurrentIsotopeInventories.length;
        while (i--) {
            if (pi.CurrentIsotopeInventories[i].Authorization_id == parcel.Authorization_id) {
                $scope.relevantInventory = pi.CurrentIsotopeInventories[i];
                break;
            }
        }

        console.debug("Relevant Inventory for " + parcel.Authorization_id + ": ", $scope.relevantInventory);
        return $scope.relevantInventory;
    };

    $scope.selectRoom = function () {
        $scope.modalData.ParcelCopy.Room_id = $scope.modalData.ParcelCopy.Room.Key_id;
    };
    $scope.checkMaxOrder = function (parcel) {
        $scope.errors.quantityExceeded = true;

        // Ensure relevant inventory has been set
        if( !$scope.relevantInventory ){
            $scope.findRelevantInventory(parcel, $scope.modalData.pi);
        }

        // Aggressively ensure that this is validated against the correct authorization/relevantInventory
        if( $scope.relevantInventory && $scope.relevantInventory.Authorization_id == parcel.Authorization_id){
            // Validate request against relevant inventory
            var max = parseFloat($scope.relevantInventory.Max_order);
            var req = parseFloat(parcel.Quantity);

            console.debug("Validate requested order of " + req + " against max of " + max);

            if (isNaN(req) ){
                // must be a number...
                $scope.errors.quantityExceeded = true;
            }
            else if (max < req) {
                // Cannot request more than maximum
                $scope.errors.quantityExceeded = true;
            }
            else if (req <= 0){
                // Cannot request less than or equal to zero
                $scope.errors.quantityExceeded = true;
            }
            else {
                $scope.errors.quantityExceeded = false;
            }
        }
        else if(parcel.Authorization_id !== undefined){
            // No relevant inventory was matched to the auth ID!
            var invs = "";
            $scope.modalData.pi.CurrentIsotopeInventories.forEach(inv=>invs += inv.Authorization_id + ',');
            console.error("No current inventories (" + invs + ") match Authorization ID " + parcel.Authorization_id);

            // TODO: Provide a better error message
            $scope.errors.quantityExceeded = true;
        }
        // else no auth has been selected
        return $scope.errors.quantityExceeded;

    };

    $scope.validateOrder = function(parcel){
        var valid = parcel
            && !$scope.errors.quantityExceeded
            && parcel.Purchase_order_id != null
            && parcel.Authorization_id != null
            && !_.isEmpty(parcel.Catalog_number)
            && !_.isEmpty(parcel.Chemical_compound);
        return valid;
    };

    $scope.saveParcel = function (pi, copy, parcel) {
        af.deleteModalData();
        af.saveParcel(pi, copy, parcel).
            then(function () {
            $scope.close();
        });
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        af.deleteModalData();
    };
});
'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
    .controller('ParcelUseLogCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, roleBasedFactory, parcelUseValidationFactory, radUtilitiesFactory) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.roleBasedFactory = roleBasedFactory;
    $scope.constants = Constants;
    console.log($scope.constants);
    af.clearError();
    var getPi = function () {
        return af.getRadPIById($stateParams.pi)
            .then(function () {
            $rootScope.pi = dataStoreManager.getById('PrincipalInvestigator', $stateParams.pi);
            var i = $rootScope.pi.ActiveParcels.length;
            while (i--) {
                var parcel = dataStoreManager.getById("Parcel", $rootScope.pi.ActiveParcels.Key_id);
                if (parcel)
                    parcel.Authorization = $rootScope.pi.ActiveParcels.Authorization;
            }
            console.log($rootScope.pi);
            $rootScope.pickups = $rootScope.pi.Pickups;
            return $rootScope.pi;
        }, function () { });
    };
    var getParcel = function () {
        return af.getParcelById($stateParams.parcel)
            .then(function () {
            $rootScope.parcel = dataStoreManager.getById("Parcel", $stateParams.parcel);
            $rootScope.parcel.loadUses();
            $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
            return $rootScope.parcel;
        });
    };
    $rootScope.mapUses = function (pus) {
        pus.forEach(function (pu) {
            pu.hasPickups = [];
            pu.ParcelUseAmounts.forEach(function (amt) {
                if (amt.IsPickedUp && pu.hasPickups.indexOf(amt.IsPickedUp) == -1)
                    pu.hasPickups.push(amt.IsPickedUp);
            });
            if (pu.hasPickups.indexOf("0") == -1 && pu.ParcelUseAmounts.some(function (amt) { return !amt.IsPickedUp; }))
                pu.hasPickups.push("0");
        });
        var mappedUses = $rootScope.parcel.ParcelUses.reduce(function (obj, item) {
            item.hasPickups.forEach(function (i) {
                if (!obj[i])
                    obj[i] = { pUses: [], pickupId: i };
                if (obj[i].pUses.indexOf(item) == -1) {
                    obj[i].pUses.push(item);
                }
            });
            return obj;
        }, { pickupId: "0", pUses: [] });
        return mappedUses;
    };
    $scope.parcelPromise = getParcel()
        .then(getPi);
    $scope.getPickup = function (id) {
        //console.log(id);
        if (!$rootScope.pi || !$rootScope.pi.Pickups)
            return false;
        return $rootScope.pi.Pickups.filter(function (p) { return p.Key_id == id; })[0];
    };
    $scope.getContainer = function (type, id) {
        return "<small><br>" + $scope.pi[type + "s"].filter(function (c) { return c.Key_id == id; })[0].Label + "</small>" || null;
    };
    $scope.getSampleAmount = function (use) {
        var total = use.Quantity;
        use.ParcelUseAmounts
            .filter(pu => pu.Is_active)
            .forEach(function (pu) {
                total -= parseFloat(pu.Curie_level);
            }
        );
        total = Math.round(total * 100000) / 100000;
        if (total >= 0)
            return total + "mCi";
        return "N/A";
    };
    $scope.addUsage = function (parcel) {
        if (!$scope.parcel.ParcelUses)
            $scope.parcel.ParcelUses = [];
        var i = $scope.parcel.ParcelUses.length;
        while (i--) {
            $scope.parcel.ParcelUses[i].edit = false;
        }
        $rootScope.ParcelUseCopy = {};
        $rootScope.ParcelUseCopy = new window.ParcelUse();
        $rootScope.ParcelUseCopy.Parcel_id = $scope.parcel.Key_id;
        $rootScope.ParcelUseCopy.ParcelUseAmounts = [];
        $rootScope.ParcelUseCopy.Class = "ParcelUse";
        $rootScope.ParcelUseCopy.Is_active = true;
        var amt = new window.ParcelUseAmount();
        amt.Waste_type_id = -1;
        amt.Curie_level = 0;
        amt.Is_active = true;
        console.log($rootScope.ParcelUseCopy.ParcelUseAmounts);
        $rootScope.ParcelUseCopy.ParcelUseAmounts = $rootScope.ParcelUseCopy.ParcelUseAmounts.concat([amt]);
        $rootScope.ParcelUseCopy.ParcelUseAmounts.forEach(function (pua) { return pua.Is_active = true; });
        $scope.editUse($rootScope.ParcelUseCopy);
    };
    $scope.editUse = function (use) {
        var i = $scope.parcel.ParcelUses.length;
        while (i--) {
            $scope.parcel.ParcelUses[i].edit = false;
        }

        // clone the use
        $rootScope.ParcelUseCopy = {};
        $rootScope.use = use;
        af.createCopy(use);

        // Calculate the available Remainder, including this useage if it's already active
        $rootScope.parcelUsageTotalUsableActivity = parcelUseValidationFactory.getAvailableQuantityForUseValidation($scope.parcel, $rootScope.ParcelUseCopy);

        // Ensure there's a 'still in use' amount
        if ($rootScope.ParcelUseCopy.ParcelUseAmounts.filter(function (pu) {
            return pu.Waste_type_id == Constants.WASTE_TYPE.SAMPLE;
        }).length == 0) {
            var sampleUsageAmount = new window.ParcelUseAmount();
            sampleUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SAMPLE;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(sampleUsageAmount);
        }

        // Open the modal
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/parcel-use-log-modal.html',
            controller: 'ModalParcelUseLogCtrl'
        });
    };
    $scope.tabulateWaste = function (uses, type, pickupId) {
        var sum = 0;
        uses.forEach(function (u) {
            u.ParcelUseAmounts.forEach(function (amt) {
                if ((!pickupId || pickupId == amt.IsPickedUp) && amt.Waste_type_id == type)
                    sum += parseFloat(amt.Curie_level);
            });
        });

        if( sum > 0 ) {
            // Only display non-zero values to avoid misleading zeroes
            return sum.toString() + "mCi";
        }
        else {
            // n/a
            return "";
        }
    };

    /**
     * Deactive (or reactivate!) parcel usage
     *
     * @param {*} pu 
     */
    $scope.deactivate = function (pu) {
        var allowAction = false;

        // Are we activating or deactivating?
        if( !pu.Is_active ){
            // First validate that the usage can be activated
            allowAction = parcelUseValidationFactory.validateUseLogEntry($rootScope.parcel, pu);
        }
        else {
            // Always allow deactivation
            allowAction = true;
        }

        if( allowAction ){
            // de/activate the usage
            var activationStatus = !pu.Is_active;
            var verb = activationStatus ? 'Activating' : 'Deactivating';

            if( !window.confirm("Are you sure you want to " + (activationStatus ? 'Reactivate' : 'Deactivate') + " this usage?") ){
                console.debug("User canceled parcel-use de/activate action");
                return;
            }

            console.log(verb + " parcel use:", pu);
            pu.Is_active = activationStatus;

            // de/activate the usage amounts
            pu.ParcelUseAmounts.forEach(function (pua) {
                // RSMS-683: When deactivating usages, we need to always ensure that non-disposed amount IS ALWAYS INACTIVE
                if( radUtilitiesFactory.isParcelUseAmountUsed(pua) ){
                    console.log(verb + " parcel use amount", pua);
                    pua.Is_active = activationStatus;
                }
                else{
                    console.debug("Parcel use amount is unused and must remain inactive", pua);
                    pua.Is_active = false;
                }
            });

            // Save
            $scope.saving = af.saveParcelUse($rootScope.parcel, pu, pu).then(function (returned) {
                if (!$rootScope.parcel.ParcelUses || !$rootScope.parcel.ParcelUses.length) {
                    console.log(returned);
                    $rootScope.parcel.ParcelUses = returned.ParcelUses;
                }
                $rootScope.parcelUses = {};
                $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
            });
        }
        else{
            // This usage is invalid
            alert("This use is invalid and cannot be activated. Edit the entry and try again.");
        }
    };
});
angular.module('00RsmsAngularOrmApp')
    .controller('ModalParcelUseLogCtrl', function ($scope, $rootScope, $modalInstance, $modal, actionFunctionsFactory, convenienceMethods, roleBasedFactory, parcelUseValidationFactory) {
        console.debug("Start ModalParcelUseLogCtrl");
        console.debug("$rootScope:", $rootScope);
        console.debug("$scope:", $scope);
    $scope.roleBasedFactory = roleBasedFactory;
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.getContainers = function (pi) {
        console.log("WASTEBAGS", pi.WasteBags);
        var containers = pi.CarboyUseCycles.concat(pi.WasteBags).concat(pi.ScintVialCollections).concat(pi.OtherWasteContainers)
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
        console.dir("CONTAINERS");
        console.dir(containers);
        return containers;
    };
    $scope.getContainer = function (amt, pi) {
        var num = pi.Containers.filter(function (container) {
            if (container.Class == "ScintVialCollection" && amt.Scint_vial_collection_id && amt.Scint_vial_collection_id == container.Key_id) {
                return true;
            }
            else if (container.Class == "CarboyUseCycle" && amt.Carboy_id && amt.Carboy_id == container.Key_id) {
                return true;
            }
            else if (container.Class == "WasteBag" && amt.Waste_bag_id && amt.Waste_bag_id == container.Key_id) {
                return true;
            }
            else if (container.Class == "OtherWasteContainer" && amt.Other_waste_container_id && amt.Other_waste_container_id == container.Key_id) {
                return true;
            }
            return false;
        }).map(function (c) {
            if (c.Close_date)
                amt.PickedUp = true;
            return c.idx;
        })[0] || null;
        return num;
    };
    $scope.addAmount = function () {
        var amt = new window.ParcelUseAmount();
        amt.Is_active = true;
        amt.Curie_level = 0;
        amt.Waste_type_id = -1;
        $rootScope.ParcelUseCopy.ParcelUseAmounts.splice($rootScope.ParcelUseCopy.ParcelUseAmounts.length - 1, 0, amt);
    };
    $scope.removeAmount = function (idx) {
        var amt = new window.ParcelUseAmount();
        $rootScope.ParcelUseCopy.ParcelUseAmounts.splice(idx, 1);
    };
    $scope.close = function (use, parcel) {
        use.edit = false;
        use.error = false;
        $rootScope.ParcelUseCopy = {};
        var i = use.ParcelUseAmounts.length;
        while (i--) {
            use.ParcelUseAmounts[i].Curie_level = use.ParcelUseAmounts[i].OldQuantity;
        }
        parcel.edit = false;
        $modalInstance.dismiss();
    };
    $scope.validateRemainder = function (parcel, copy, use) {
        use.error = null;
        var uses = parcel.ParcelUses;
        var valid = true;
        var total = 0;
        var i = uses.length;
        while (i--) {
            total += parseFloat(uses[i].Quantity);
        }
        total += parseFloat(copy.Quantity);
        //if we are editing, subtract the total from the copied use so that it's total isn't included twice
        if (use.Quantity) {
            total = total - parseFloat(use.Quantity);
        }
        if (total > parseFloat(parcel.Quantity)) {
            valid = false;
            use.error = 'Total usages must not be more than remaining package quantity.';
        }
        return valid;
    };

    $scope.useDateButtonClass = 'btn-info';
    $scope.onChangeDateUsed = function(parcel, copy, use){
        var validDate = parcelUseValidationFactory.validateUsageDate(copy, parcel);
        $scope.useDateButtonClass = validDate.isValid ? 'btn-info' : 'btn-danger';
        copy.isValid = false;
        copy.DateError = validDate.error;
    };

    $scope.saveParcelUse = function (parcel, copy, use) {
        console.debug("saveParcelUse(parcel, copy, use)", parcel, copy, use);

        // Pass the original AND updated use for validation
        if (parcelUseValidationFactory.validateUseLogEntry(parcel, copy, use)) {
            console.debug("  Parcel Use is valid");
            af.saveParcelUse(parcel, copy, use).then(function (returned) {
                console.debug("  Parcel Use is saved: ", returned);
                if (!$rootScope.parcel.ParcelUses || !$rootScope.parcel.ParcelUses.length) {
                    $rootScope.parcel.ParcelUses = returned.ParcelUses;
                }
                $rootScope.parcelUses = {};
                $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
                $modalInstance.close();
            });
        }
        else{
            console.warn("  Parcel Use is invalid");
        }

        console.debug("end saveParcelUse");
    };

    var parcelUseHasUseAmountType = function (use, typeId) {
        var i = use.ParcelUseAmounts.length;
        while (i--) {
            var amt = use.ParcelUseAmounts[i];
            if (amt.Waste_type_id == typeId)
                return true;
        }
        return false;
    };

    $scope.selectContainer = function (amt, containers) {
        var container = containers[amt.ContainerIdx - 1];
        amt.Waste_bag_id = null;
        amt.Waste_type_id = null;
        amt.Carboy_id = null;
        amt.Scint_vial_collection_id = null;
        switch (container.Class) {
            case ("WasteBag"):
                amt.Waste_bag_id = container.Key_id;
                amt.Waste_type_id = Constants.WASTE_TYPE.SOLID;
                break;
            case ("CarboyUseCycle"):
                amt.Carboy_id = container.Key_id;
                amt.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
                break;
            case ("ScintVialCollection"):
                amt.Scint_vial_collection_id = container.Key_id;
                amt.Waste_type_id = Constants.WASTE_TYPE.VIAL;
                break;
            case ("OtherWasteContainer"):
                amt.Other_waste_container_id = container.Key_id;
                amt.Other_waste_type_id = container.Other_waste_type_id;
                amt.Waste_type_id = Constants.WASTE_TYPE.OTHER;
                console.log("OTHER WASTE FOUND", container, amt);
                break;
            default:
                amt.Waste_bag_id = null;
                amt.Waste_type_id = null;
                amt.Carboy_id = null;
                amt.Scint_vial_collection_id = null;
        }
    };
    $scope.setSampleUse = function (parcelUse) {
        var max = parseFloat(parcelUse.Quantity);
        if (isNaN(max))
            max = 0;
        var total = 0;
        parcelUse.error = "";
        parcelUse.ParcelUseAmounts.sort(function (a, b) { return b.Waste_type_id < a.Waste_type_id; }).forEach(function (amt, idx, arr) {
            if (amt.Waste_type_id != Constants.WASTE_TYPE.SAMPLE) {
                if (!isNaN(amt.Curie_level)){
                    total += parseFloat(amt.Curie_level);
                }
            }
            else {
                amt.Curie_level = max - total;
                amt.Curie_level = Math.round(amt.Curie_level * 10000000000) / 10000000000;
                if (max < total || total < 0)
                    parcelUse.error = "Total disposal amount must equal use amount.";
            }
        });
    };
    $scope.orderAmts = function () {
    };
    console.debug("End ModalParcelUseLogCtrl");
})
    .controller('ModalParcelUseLogOverrideCtrl', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, roleBasedFactory) {
    $scope.cm = convenienceMethods;
    var af = actionFunctionsFactory;
    $scope.selectPickups = function () {
        af.updateParcelUse($rootScope.parcel, $rootScope.ParcelUseCopy).then(function (r) {
            $modalInstance.close(r);
        });
    };
    $scope.cancel = function () { return $modalInstance.dismiss(); };
});

angular.module('00RsmsAngularOrmApp')
    .controller('PickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, radUtilitiesFactory, $q, convenienceMethods) {
        var af = actionFunctionsFactory;

        var loadPickups = function(){
            return af.getPickupsForPI($stateParams.pi)
            .then(function(pickups){
                console.debug('PI Pickups loaded', pickups);

                // merge pickups' containers
                pickups.forEach(p => {
                    p.includedContainers = radUtilitiesFactory.getAllWasteContainersFromPickup(p);
                });

                // group pickups by status
                $scope.pickup_groups = radUtilitiesFactory.groupPickupsByStatus(pickups);
    
                // Has a pickup been requested?
                var requestedGroup =  $scope.pickup_groups.filter(g => g.status === 'REQUESTED');
                var requestedPickups = requestedGroup[0].pickups
                    .sort((a,b) => a.Date_created < b.Date_created);

                $scope.requestedPickup = requestedPickups[0];

                console.debug("Requested pickup: ", $scope.requestedPickup);
                console.debug("PI Pickups ready", $scope.pickup_groups);
            });
        };

        var loadReadyContainers = function(){
            return af.getWasteContainersReadyForPickup($stateParams.pi)
            .then(function(containers){
                console.debug("PI Containers Loaded", containers);
                $scope.readyContainers = containers.map(container => {
                    container.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(container.Class);
                    return container;
                });

                return $scope.readyContainers;
            });
        };

        // Load PI Pickups
        $scope.piPickupsPromise = loadPickups();

        // Load containrs
        $scope.loadReadyContainersPromise = loadReadyContainers();

        $scope.savePickupNotes = function(pickup){
            $scope.PickupSaving = actionFunctionsFactory.savePickupNotes(pickup)
                .then(
                    function(){
                        // Notes saved
                        console.debug("Notes saved");
                    },
                    function(){
                        // Notes didn't save...
                        $scope.notes_saved_err = "Failed to save notes";
                    }
                );
        }

    });

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('SelectWasteCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    console.log($scope.modalData);
    $scope.remove = function (amt) {
        $rootScope.piPromise = af.removeWasteFromPickup(amt).then(function (returnedArray) {
            console.log(returnedArray);
            $modalInstance.close(returnedArray);
        });
    };
    $scope.close = function () {
        $modalInstance.dismiss();
    };
})
    .controller('PickupModalCtrl', function ($scope, actionFunctionsFactory, Collections, $modalInstance) {
    $scope.collections = Collections;
    $scope.confirm = function (trays) {
        $scope.error = "";
        //if (!isNaN(trays)) {
        $modalInstance.close($scope.collections);
        //} else {
        $scope.error = "Please enter a number here.";
        //}
    };
    $scope.close = function () {
        $modalInstance.dismiss();
    };
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
    .controller('PIWipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.constants = Constants;
    var getPI = function (id) {
        return af.getRadPIById(id)
            .then(function (pi) {
            $scope.pi = pi;
        }, function () { });
    };
    $rootScope.piPromise = getPI($stateParams.pi);
    $scope.editPIWipe = function (test, wipe) {
        $rootScope.PIWipeCopy = {};
        if (!test.PIWipes)
            test.PIWipes = [];
        var i = test.PIWipes.length;
        while (i--) {
            test.PIWipes[i].edit = false;
        }
        if (!wipe) {
            $rootScope.PIWipeCopy = new window.PIWipe();
            $rootScope.PIWipeCopy.Class = "PIWipe";
            $rootScope.PIWipeCopy.Is_active = true;
            $rootScope.PIWipeCopy.PI_wipe_test_id = test.Key_id;
            $rootScope.PIWipeCopy.edit = true;
            test.PIWipes.unshift($rootScope.PIWipeCopy);
        }
        else {
            wipe.edit = true;
            af.createCopy(wipe);
        }
    };
    $scope.addPIWipe = function (test) {
        $scope.pi.WipeTests.forEach(function (w) {
            w.showWipes = false;
            w.adding = false;
        });
        if (!test.PIWipes)
            test.PIWipes = [];
        //all wipe tests must have a background wipe
        if (!test.PIWipes[0] || !test.PIWipes[0].Location || test.PIWipes[0].Location != "Background") {
            var bgWipe = new window.PIWipe();
            bgWipe.PI_wipe_test_id = test.Key_id;
            bgWipe.Class = "PIWipe";
            bgWipe.edit = true;
            bgWipe.Location = "Background";
            test.PIWipes.unshift(bgWipe);
        }
        var piWipe = new window.PIWipe();
        piWipe.PI_wipe_test_id = test.Key_id;
        piWipe.Class = "PIWipe";
        piWipe.edit = true;
        test.PIWipes.push(piWipe);
        test.showWipes = true;
        test.adding = true;
    };
    $scope.cancelPIWipes = function (test) {
        console.log(test);
        for (var x = 0; x < test.PIWipes.length; x++) {
            if (!test.PIWipes[x].Key_id) {
                test.PIWipes.splice(x, 1);
            }
        }
        test.adding = false;
    };
    $scope.openModal = function (object) {
        var modalData = {};
        modalData.PI = $scope.pi;
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/pi-wipe-modal.html',
            controller: 'PIWipeTestModalCtrl'
        });
        modalInstance.result.then(function (obj) {
            if (obj && obj.Class == "CarboyUseCycle")
                $scope.pi.CarboyUseCycles.push(obj);
        });
    };
})
    .controller('PIWipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        if (!$scope.modalData.PIWipeTest) {
            $scope.modalData.PIWipeTest = new window.PIWipeTest();
            $scope.modalData.PIWipeTest.Class = "PIWipeTest";
            $scope.modalData.PIWipeTest.Is_active = true;
            $scope.modalData.PIWipeTest.Principal_investigator_id = $scope.modalData.PI.Key_id;
        }
        $scope.save = function (test) {
            af.savePIWipeTest(test)
                .then($scope.close);
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }]);
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
    .controller('QuarterlyInventoryCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.print = function () {
        window.print();
    };
    var getPi = function () {
        return af.getRadPIById($stateParams.pi)
            .then(function (pi) {
            $scope.pi = pi;
            return pi;
        }, function () { });
    };
    var getInventory = function (pi) {
        return af.getQuartleryInventory(pi.Key_id)
            .then(function (inventory) {
            $scope.pi_inventory = inventory;
            return inventory;
        });
    };
    $scope.openModal = function (object) {
        console.log(object);
        var modalData = {};
        if (object)
            modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/confirm-inventory.html',
            controller: 'InventoryConfirmationModalCtrl'
        });
    };
    $rootScope.inventoryPromise = getPi()
        .then(getInventory);
})
    .controller('InventoryConfirmationModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.savePiQuarterlyInventory = function (inventory, copy) {
            af.savePiQuarterlyInventory(inventory, copy)
                .then($scope.close);
        };
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        };
    }]);
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('ContainersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, roleBasedFactory, radUtilitiesFactory) {
    $scope.roleBasedFactory = roleBasedFactory;
    $scope.stuff = { showClosed: false };
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = af.getRadPIById($stateParams.pi)
        .then(
            function (pi) {
                $scope.pi = pi;
            },
            function () { }
        )
        .then(af.getAllDrums);
    $scope.openCloseContainerModal = function (container) {
        console.log(container);
        var modalData = { pi: null };
        modalData.pi = $scope.pi;
        if (container)
            modalData['Container'] = container;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/confirm-close-container.html',
            controller: 'RecepticalModalCtrl'
        });
        modalInstance.result.then(function () { $scope.pi.containers = $scope.getContainers($scope.pi); });
    };
    $scope.openContainerModal = function () {
        var modalData = { pi: null, Container: { Class: "", Principal_investigator_id: $scope.pi.Key_id } };
        modalData.pi = $scope.pi;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/pi/pi-modals/open-container.html',
            controller: 'RecepticalModalCtrl'
        });
        modalInstance.result.then(function () { $scope.pi.containers = $scope.getContainers($scope.pi); });
    };
    $scope.getContainers = function (pi, showClosed) {
        return pi.CarboyUseCycles.concat(pi.WasteBags).concat(pi.ScintVialCollections).concat(pi.OtherWasteContainers)
            .map(function (c, idx) {
            var container = angular.extend({}, c);
            //console.log(container.Contents, c.Contents);
            container.ViewLabel = c.Label || c.CarboyNumber;
            //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
            container.idx = idx + 1;
            switch (c.Class) {
                case ("WasteBag"):
                    container.ClassLabel = "Solid Waste";
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
    $scope.reopenContainer = function (container) {
        if( radUtilitiesFactory.isContainerDisposed(container) ){
            return container;
        }

        container.Close_date = null;
        return $rootScope.saving = af.save(container).then(function (r) {
            console.log(r, container);
            angular.extend(container, r);
            console.log(dataStore.WasteBag);
            $scope.pi.containers = $scope.getContainers($scope.pi);
            return container;
        });
    };
    $scope.filterFunction = function (container) {
        var showClosed = $scope.stuff.showClosed;
        return (container.Close_date == null) != showClosed;
    };
})
    .controller('RecepticalModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    $scope.types = Constants.CONTAINTER_TYPE.concat($scope.modalData.pi.OtherWasteTypes.filter(function (c) { return !c.Clearable || roleBasedFactory.getHasPermission([$rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN]]); }).map(function (c) { return { Label: c.Name, Class: "OtherWasteContainer", Other_waste_type_id: c.Key_id }; }));
    if (!$scope.modalData.SolidsContainerCopy) {
        $scope.modalData.SolidsContainerCopy = {
            Class: 'SolidsContainer',
            Room_id: null,
            Is_active: true
        };
    }
    $scope.getLabel = function (container) {
        if (container.Class == "WasteBag")
            return "solid waste container";
        if (container.Class == "ScintVialCollection")
            return "scintillation vial container";
        if (container.Class == "CarboyUseCycle")
            return "carboy";
        if (container.Class == "OtherWasteContainer")
            return "other";
        return false;
    };
    function isCyclic(obj) {
        var seenObjects = [];
        function detect(obj) {
            if (obj && typeof obj === 'object') {
                if (seenObjects.indexOf(obj) !== -1) {
                    return true;
                }
                seenObjects.push(obj);
                for (var key in obj) {
                    if (obj.hasOwnProperty(key) && detect(obj[key])) {
                        console.log(obj, 'cycle at ' + key);
                        return true;
                    }
                }
            }
            return false;
        }
        return detect(obj);
    }
    $scope.confirmCloseContainer = function (container) {
        console.log("Confirm close container ", container);
        return $rootScope.saving = af.closeWasteContainer(container)
            .then(function(r){
                angular.extend(container, r);
                $modalInstance.close(container);
                af.deleteModalData();
                return r;
            });
    };
    $scope.getValidTrays = function (num) {
        return num && num.length && !isNaN(num);
    };
    $scope.newContainer = function (container) {
        console.log(container);
        return $rootScope.saving = af.save(container).then(function (r) {
            af.deleteModalData();
            $scope.modalData.pi[container.Class + "s"].push(r);
            $modalInstance.close();
        });
    };
    $scope.selectContainer = function (container, type) {
        console.log(type);
        container.Class = type.Class;
        if (type.Class == "OtherWasteContainer") {
            container.Other_waste_type_id = type.Other_waste_type_id;
        }
        else {
            container.Other_waste_type_id = null;
        }
        console.log(container);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        af.deleteModalData();
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:UseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
    .controller('UseLogCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = $scope.parcelPromise = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        $scope.pi = dataStoreManager.getById("PrincipalInvestigator", $stateParams.pi);
        console.log(dataStore);
    }, function () { });

    $scope.getParcelAmountOnHand = function(parcel){
        // Ensure Number comparison
        return Number(parcel.AmountOnHand);
    }
});
