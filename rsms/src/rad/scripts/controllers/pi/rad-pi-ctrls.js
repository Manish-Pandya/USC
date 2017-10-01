'use strict';
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
    .controller('OrdersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.constants = Constants;
    $rootScope.parcelPromise = af.getAllIsotopes()
        .then(getPI);
    var getPI = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        console.log(pi);
        $scope.pi = pi;
    }, function () { });
    $scope.openModal = function (object) {
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
    .controller('OrderModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
    var af = actionFunctionsFactory;
    $scope.constants = Constants;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    $scope.getHighestAuth = function (pi) {
        if (pi.Pi_authorization && pi.Pi_authorization.length) {
            var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                    return moment(amendment.Approval_date).valueOf();
                }]);
            return auths[auths.length - 1];
        }
    };
    if (!$scope.modalData.ParcelCopy) {
        $scope.modalData.ParcelCopy = {
            Class: 'Parcel',
            Is_active: true,
            Status: Constants.PARCEL.STATUS.REQUESTED,
            Principal_investigator_id: $scope.modalData.pi.Key_id
        };
    }
    $scope.selectRoom = function () {
        $scope.modalData.ParcelCopy.Room_id = $scope.modalData.ParcelCopy.Room.Key_id;
    };
    $scope.checkMaxOrder = function (parcel) {
        $scope.quantityExceeded = false;
        var pi = $scope.modalData.pi;
        var i = pi.CurrentIsotopeInventories.length;
        while (i--) {
            if (pi.CurrentIsotopeInventories[i].Authorization_id == parcel.Authorization_id) {
                console.log(pi.CurrentIsotopeInventories[i].Max_order);
                if (parseFloat(pi.CurrentIsotopeInventories[i].Max_order) < parseFloat(parcel.Quantity)) {
                    $scope.relevantInventory = pi.CurrentIsotopeInventories[i];
                    return false;
                }
                else {
                    return true;
                }
            }
        }
        return true;
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
    .controller('ParcelUseLogCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, roleBasedFactory) {
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
        use.ParcelUseAmounts.forEach(function (pu) {
            total -= parseFloat(pu.Curie_level);
        });
        total = Math.round(total * 100000) / 100000;
        if (total > 0)
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
        $rootScope.ParcelUseCopy = {};
        $rootScope.use = use;
        af.createCopy(use);
        if ($rootScope.ParcelUseCopy.ParcelUseAmounts.filter(function (pu) {
            return pu.Waste_type_id == Constants.WASTE_TYPE.SAMPLE;
        }).length == 0) {
            var sampleUsageAmount = new window.ParcelUseAmount();
            sampleUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SAMPLE;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(sampleUsageAmount);
        }
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
        return sum.toString() + "mCi";
    };
    $scope.deactivate = function (pu) {
        pu.Is_active = !pu.Is_active;
        console.log(pu);
        pu.ParcelUseAmounts.forEach(function (pua) { console.log(pua); pua.Is_active = !pua.Is_active; });
        $scope.saving = af.saveParcelUse($rootScope.parcel, pu, pu).then(function (returned) {
            if (!$rootScope.parcel.ParcelUses || !$rootScope.parcel.ParcelUses.length) {
                console.log(returned);
                $rootScope.parcel.ParcelUses = returned.ParcelUses;
            }
            $rootScope.parcelUses = {};
            $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
        });
    };
});
angular.module('00RsmsAngularOrmApp')
    .controller('ModalParcelUseLogCtrl', function ($scope, $rootScope, $modalInstance, $modal, actionFunctionsFactory, convenienceMethods, roleBasedFactory) {
    $scope.roleBasedFactory = roleBasedFactory;
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.getContainers = function (pi) {
        return pi.CarboyUseCycles.concat(pi.WasteBags).concat(pi.ScintVialCollections).concat(pi.OtherWasteContainers)
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
    $scope.saveParcelUse = function (parcel, copy, use) {
        //console.log(copy, use); return;
        if ($scope.validateUseAmounts(copy, use)) {
            af.saveParcelUse(parcel, copy, use).then(function (returned) {
                if (!$rootScope.parcel.ParcelUses || !$rootScope.parcel.ParcelUses.length) {
                    $rootScope.parcel.ParcelUses = returned.ParcelUses;
                }
                $rootScope.parcelUses = {};
                $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
                $modalInstance.close();
            });
        }
    };
    //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
    //which if you think about it, has sort of become an MVCVM
    $scope.validateUseAmounts = function (use, orig) {
        use.error = null;
        use.isValid = false;
        var total = 0;
        var i = use.ParcelUseAmounts.length;
        while (i--) {
            if (use.ParcelUseAmounts[i].Curie_level)
                total = total + parseFloat(use.ParcelUseAmounts[i].Curie_level);
        }
        total = Math.round(total * 100000) / 100000;
        if (parseFloat(use.Quantity) == total) {
            use.isValid = true;
        }
        else {
            use.error = 'Total disposal amount must equal use amount.';
        }
        use.isValid = validateUsageDate(use, $rootScope.parcel);
        return use.isValid;
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
    function validateUsageDate(use, parcel) {
        var valid = true;
        use.DateError = "";
        var usageDateString = convenienceMethods.setMysqlTime(use.view_Date_used);
        if (usageDateString < parcel.Arrival_date || usageDateString < parcel.Transfer_in_date) {
            use.DateError = "The date you entered is before this package arrived.<br>";
            valid = false;
        }
        //verify that the usage date isn't before the most recent pickup
        var pu = $rootScope.pi.Pickups.sort(function (a, b) { return a.Pickup_date > b.Pickup_date; })[0];
        if (pu && pu.Pickup_date > usageDateString) {
            use.DateError += "The date you entered is before your most recent pickup. If you need to make changes to uses that have already been picked up, please contact RSO.<br>";
            valid = false;
            if (roleBasedFactory.getHasPermission([$rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN]])) {
                var mi = $modal.open({
                    templateUrl: 'views/pi/pi-modals/parcel-use-log-override-modal.html',
                    controller: 'ModalParcelUseLogOverrideCtrl'
                });
                mi.result.then(function (r) {
                    $rootScope.parcelUses = {};
                    $rootScope.parcelUses = $rootScope.mapUses($rootScope.parcel.ParcelUses);
                    $modalInstance.close();
                });
            }
        }
        return use.isValid = valid;
    }
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
                if (!isNaN(amt.Curie_level))
                    total += parseFloat(amt.Curie_level);
            }
            else {
                amt.Curie_level = max - total;
                amt.Curie_level = Math.round(amt.Curie_level * 10000000000) / 10000000000;
                if (max < total)
                    parcelUse.error = "Total disposal amount must equal use amount.";
            }
        });
    };
    $scope.orderAmts = function () {
    };
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
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        //pi.loadRooms();
        pi = dataStoreManager.getById("PrincipalInvestigator", $stateParams.pi);
        if (pi.Pickups && pi.Pickups.length) {
            $scope.CurrentPickup = pi.Pickups.filter(function (p) {
                return p.Pickup_date == null;
            }).sort(function (a, b) {
                return a.Date_created < b.Date_created;
            })[0] || null;
        }
        $scope.pi = pi;
        $scope.containers = $scope.getContainers($scope.pi);
    }, function () { });
    $scope.solidsContainerHasPickups = function (container) {
        if (!container)
            return false;
        if ($scope.hasPickupItems(container.WasteBagsForPickup))
            return true;
        return false;
    };
    $scope.hasPickupItems = function (collection) {
        if (!collection || !collection.length)
            return false;
        var hasPickupItems = false;
        if (!collection)
            return false;
        var i = collection.length;
        while (i--) {
            // TODO: Should collection[i].Contents ever be null? Had to add null check here before getting length, because it's null sometimes.
            if (!collection[i].Pickup_id && collection[i].Class == "WasteBag" || (!collection[i].Pickup_id && collection[i].Contents && collection[i].Contents.length)) {
                hasPickupItems = true;
            }
        }
        return hasPickupItems;
    };
    $scope.setSVCollection = function (pi) {
        if (!$scope.CurrentPickup || !$scope.CurrentPickup.Scint_vial_collections) {
            if (pi.CurrentScintVialCollections && pi.CurrentScintVialCollections.length)
                return;
            var collection = new window.ScintVialCollection();
            collection.Principal_investigator_id = pi.Key_id;
            collection.new = true;
            $scope.CurrentScintVialCollections = [collection];
        }
        else {
            console.log($scope.CurrentPickup);
            $scope.CurrentScintVialCollections = $scope.CurrentPickup.Scint_vial_collections;
        }
    };
    $scope.svTrays = 0;
    $scope.wasteInContainersScheduledScheduled = function (containers) {
        var i = containers.length;
        while (i--) {
            if (containers[i].Pickup_id)
                return true;
        }
        return false;
    };
    $scope.getCurrentPickup = function (pi) {
        $scope.CurrentPickup = !pi.Pickups || !pi.Pickups.length ? null : pi.Pickups.filter(function (p) { return p.Status == Constants.PICKUP.STATUS.REQUESTED; })[0];
    };
    $scope.createPickup = function (containers, pi, notes) {
        //collection of things to be picked up
        if ($scope.CurrentPickup)
            var pickup = $scope.CurrentPickup;
        if (!pickup) {
            var pickup = new window.Pickup();
            pickup.Is_active = true;
            pickup.Class = "Pickup";
            pickup.Carboy_use_cycles = [];
            pickup.Scint_vial_collections = [];
            pickup.Waste_bags = [];
            pickup.Principal_investigator_id = null;
            pickup.Requested_date = convenienceMethods.setMysqlTime(Date());
            pickup.Status = Constants.PICKUP.STATUS.REQUESTED;
            pickup.Principal_investigator_id = pi.Key_id;
        }
        var pickupCopy = {
            Class: "Pickup",
            Key_id: pickup.Key_id || null,
            Scint_vial_collections: pickup.Scint_vial_collections,
            Waste_bags: pickup.Waste_bags,
            Bags: pickup.Bags,
            Status: pickup.Status,
            Principal_investigator_id: pickup.Principal_investigator_id,
            Scint_vial_trays: pickup.Scint_vial_trays,
            Requested_date: convenienceMethods.setMysqlTime(new Date())
        };
        if ($scope.CurrentPickup) {
            pickupCopy.Notes = notes || $scope.CurrentPickup.Notes;
        }
        else {
            pickupCopy.Notes = notes;
        }
        pickupCopy.Carboy_use_cycles = [];
        containers.forEach(function (c) {
            var collectionClass = "";
            switch (c.Class) {
                case ("WasteBag"):
                    pickupCopy.Waste_bags.push(c);
                    break;
                case ("CarboyUseCycle"):
                    pickupCopy.Carboy_use_cycles.push(c);
                    break;
                case ("ScintVialCollection"):
                    pickupCopy.Scint_vial_collections.push(c);
                    break;
            }
        });
        console.log(pickupCopy);
        $rootScope.saving = af.savePickup(pickup, pickupCopy, true).then(function (newPickup) {
            console.log(newPickup);
            if (!$scope.CurrentPickup || !$scope.CurrentPickup.Key_id) {
                $scope.pi.Pickups.push(newPickup);
                $scope.CurrentPickup = newPickup;
            }
            else {
                angular.extend($scope.CurrentPickup, newPickup);
            }
            console.log($scope.CurrentPickup);
        });
    };
    $scope.selectWaste = function (waste, pickupId) {
        $scope.pi.ActiveParcels = [];
        $scope.pi.loadActiveParcels().then(function () {
            var modalData = { pi: { ActiveParcels: [] }, waste: {}, amts: [] };
            modalData.pi = $scope.pi;
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
                            if (amt.IsPickedUp == pickupId && amt.Waste_type_id == Constants.WASTE_TYPE.SOLID) {
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
                    $scope.pi.CurrentWasteBag = arr[1];
                $scope.CurrentPickup.Waste_bags = [];
                $scope.CurrentPickup.Waste_bags = arr[0].Waste_bags;
            });
        });
    };
    $scope.hasContents = function (bags) {
        return bags.some(function (b) { return b.Contents && b.Contents.length; });
    };
    $scope.getContainers = function (pi) {
        return pi.CarboyUseCycles.concat(pi.WasteBags).concat(pi.ScintVialCollections)
            .filter(function (c) {
            return c.Close_date != null && (($scope.CurrentPickup && $scope.CurrentPickup.Key_id) ? c.Pickup_id == $scope.CurrentPickup.Key_id : !c.Pickup_id);
        })
            .map(function (c, idx) {
            var container = angular.extend({}, c);
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
                default:
                    container.ClassLabel = "";
            }
            return container;
        });
    };
    $scope.getClassByContainerType = function (container) {
        var classList = "";
        if (container.Class == "WasteBag")
            classList = "icon-remove-2 solids-containers";
        if (container.Class == "ScintVialCollection")
            classList = "icon-lab scint-vials";
        if (container.Class == "CarboyUseCycle")
            classList = "icon-carboy carboys";
        if (container.Class == "Other")
            classList = "other";
        return classList;
    };
})
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
    .controller('PickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
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
    $scope.requestPickup = function (pickup) {
        $scope.close();
        //var pickupCopy = dataStoreManager.createCopy(pickup);
        var pickupCopy = {
            Class: "Pickup",
            Key_id: pickup.Key_id || null,
            Scint_vial_collections: pickup.Scint_vial_collections,
            Waste_bags: pickup.Waste_bags,
            Bags: pickup.Bags,
            Status: pickup.Status,
            Principal_investigator_id: pickup.Principal_investigator_id,
            Scint_vial_trays: pickup.Scint_vial_trays,
            Requested_date: convenienceMethods.setMysqlTime(new Date())
        };
        pickupCopy.Carboy_use_cycles = [];
        var i = pickup.Carboy_use_cycles.length;
        while (i--) {
            var originalCycle = pickup.Carboy_use_cycles[i];
            var cycle = new CarboyUseCycle();
            for (var prop in originalCycle) {
                if (typeof originalCycle[prop] != "object" && typeof originalCycle[prop] != "array") {
                    cycle[prop] = originalCycle[prop];
                }
            }
            pickupCopy.Carboy_use_cycles[i] = cycle;
        }
        af.savePickup(pickup, pickupCopy, true);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        af.deleteModalData();
    };
});
'use strict';
/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PiRadHomeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        console.log(pi);
        $scope.pi = pi;
    }, function () { });
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
    .controller('ContainersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, roleBasedFactory) {
    $scope.roleBasedFactory = roleBasedFactory;
    $scope.stuff = { showClosed: false };
    var af = actionFunctionsFactory;
    $scope.af = af;
    $rootScope.piPromise = af.getRadPIById($stateParams.pi)
        .then(function (pi) {
        $scope.pi = pi;
    }, function () { });
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
                default:
                    container.ClassLabel = "";
            }
            return container;
        });
    };
    $scope.reopenContainer = function (container) {
        container.Close_date = null;
        return $rootScope.saving = af.save(container).then(function (r) {
            console.log(r, container);
            angular.extend(container, r);
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
    $scope.confirmCloseContainer = function (container, copy) {
        container.Close_date = convenienceMethods.setMysqlTime(new Date());
        return $rootScope.saving = af.save(container).then(function (r) {
            angular.extend(container, r);
            $modalInstance.dismiss();
            af.deleteModalData();
            return r;
        });
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
});
