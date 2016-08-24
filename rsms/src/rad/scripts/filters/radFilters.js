angular.module('00RsmsAngularOrmApp')
	.filter('activePickups', function () {
	    return function (pickups) {
	        if (!pickups) return;
	        var activePickups = [];
	        var i = pickups.length;

	        while (i--) {
	            var pickup = pickups[i];
	            if (pickup.Status == Constants.PICKUP.STATUS.PICKED_UP || pickup.Status == Constants.PICKUP.STATUS.REQUESTED && pickup.Waste_bags.length || pickup.Scint_vial_collections.length || pickup.Carboy_use_cycles.length) {
	                activePickups.push(pickup);
	            }
	        }
	        return activePickups;
	    };
	})
    .filter('carboyHasNoRetireDate', function () {
        return function (carboys) {
            if (!carboys) return;
            var availableCarboys = [];
            var i = carboys.length;

            while (i--) {
                var carboy = carboys[i];
                if (!carboy.Retirement_date || carboy.Retirement_date == null) {
                    availableCarboys.push(carboy);
                }
            }

            return availableCarboys;
        };
    })
    .filter('carboyIsAvailable', function () {
        return function (carboys) {
            if (!carboys) return;
            var availableCarboys = [];
            var i = carboys.length;
            console.log(i);
            while (i--) {
                var carboy = carboys[i];
                if (carboy.Is_active == true && carboy.Status == Constants.CARBOY_USE_CYCLE.STATUS.AVAILABLE) {
                    availableCarboys.unshift(carboy);
                }
            }

            return availableCarboys;
        };
    })
    .filter('disposalCycles', function (convenienceMethods) {
        return function (cycles) {
            if (!cycles) return;
            var disposalCycles = [];
            var i = cycles.length;
            while (i--) {
                var cycle = cycles[i];
                cycle.pourable = false;
                if (cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.DECAYING
                    || cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.AT_RSO
                    || cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.PICKED_UP
                    || cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM) {

                    if (cycle.Pour_allowed_date) {
                        pourDay = convenienceMethods.getDate(cycle.Pour_allowed_date)
                        var pourSeconds = pourDay.getTime();
                        var now = new Date(),
                        beginningOfPourDay = new Date(
                            pourDay.getFullYear(),
                            pourDay.getMonth(),
                            pourDay.getDate(),
                            0, 0, 0);
                        if (beginningOfPourDay.getTime() <= now.getTime()) cycle.pourable = true;
                    }
                    disposalCycles.unshift(cycle);
                }
            }
            return disposalCycles;
        };
    })
    .filter('disposalStatuses', function () {
        return function (statuses) {
            console.log(statuses)
            if (!statuses) return;
            var disposalStatuses = [];
            var i = statuses.length;
            for (var prop in statuses) {
                var status = statuses[prop];
                if (status == Constants.CARBOY_USE_CYCLE.STATUS.DECAYING
  					|| status == Constants.CARBOY_USE_CYCLE.STATUS.AT_RSO
                    || status == Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM) {
                    disposalStatuses.unshift(status);
                }
            }
            return disposalStatuses;
        };
    })
.filter('disposalSolids', function () {
    return function (solids) {
        if (!solids) return;
        var disposalSolids = [];
        var i = solids.length;
        while (i--) {
            var solid = solids[i];
            if (solid.Pickup_id && !solid.Drum_id) {
                disposalSolids.unshift(solid);
            }
        }
        return disposalSolids;
    };
})
.filter('inventoryStatus', function (convenienceMethods) {
    return function (piInventories, inventory) {
        if (!piInventories) return;
        var i = piInventories.length;
        if (inventory.Due_date) var dueDate = convenienceMethods.getDate(inventory.Due_date);
        if (!dueDate) {
            alert('no due date');
            return piInventories;
        }
        var curDate = new Date();
        while (i--) {
            var piInventory = piInventories[i];
            if (dueDate.getTime() < curDate.getTime()) {
                if (!piInventory.Sign_off_date) {
                    piInventory.Status = Constants.INVENTORY.STATUS.LATE;
                } else {
                    piInventory.Status = Constants.INVENTORY.STATUS.COMPLETE;
                }
            } else {
                piInventory.Status = Constants.INVENTORY.STATUS.NA;
            }
            console.log(piInventory);
        }
        return piInventories;
    };
})
.filter('miscWipeTests', function () {
    return function (tests) {
        if (!tests) return;
        var availableTests = [];
        var i = tests.length;

        while (i--) {
            var test = tests[i];
            if (test.Is_active == true && (!test.Closeout_date || test.Closeout_date == "0000-00-00 00:00:00")) {
                availableTests.push(test);
            }
        }
        return availableTests;
    };
})
.filter('needsWipeTest', function () {
    return function (parcels) {
        if (!parcels) return;
        var parcelsThatNeedWipeTests = [];
        var i = parcels.length;

        while (i--) {
            var parcel = parcels[i];
            if (parcel.Status && parcel.Status == Constants.PARCEL.STATUS.ARRIVED || parcel.Status == Constants.PARCEL.STATUS.PRE_ORDER || parcel.Status == "") {
                parcelsThatNeedWipeTests.unshift(parcel);
            }
        }

        return parcelsThatNeedWipeTests;
    };
})
.filter('notDelivered', function () {
    return function (parcels) {
        if (!parcels) return;
        var j = parcels.length;
        var filtered = [];
        var matchedStatuses = [
          Constants.PARCEL.STATUS.REQUESTED,
          Constants.PARCEL.STATUS.ARRIVED,
          Constants.PARCEL.STATUS.PRE_ORDER,
          Constants.PARCEL.STATUS.ORDERED,
          Constants.PARCEL.STATUS.WIPE_TESTED,
        ]
        while (j--) {
            if (matchedStatuses.indexOf(parcels[j].Status) > -1) {
                filtered.unshift(parcels[j]);
            }
        }
        return filtered;
    };
})
    .filter('pisNeedingPackages', function () {
        return function (pis) {
            if (!pis) return;
            var j = pis.length;
            var filtered = [];
            var matchedStatuses = [
              Constants.PARCEL.STATUS.REQUESTED,
              Constants.PARCEL.STATUS.ARRIVED,
              Constants.PARCEL.STATUS.PRE_ORDER,
              Constants.PARCEL.STATUS.ORDERED,
              Constants.PARCEL.STATUS.WIPE_TESTED,
            ]
            while (j--) {
                if (pis[j].ActiveParcels) {
                    var i = pis[j].ActiveParcels.length;
                    while (i--) {
                        if (matchedStatuses.indexOf(pis[j].ActiveParcels[i].Status) > -1) {
                            filtered.unshift(pis[j]);
                            break;
                        }
                    }
                }
            }
            return filtered;
        };
    })
    .filter('parcelParser', function () {
        return function (uses) {
            if (!uses) return;
            var j = uses.length;
            var filteredUses = [];
            while (j--) {
                var use = uses[j];
                use.Solids = [];
                use.Liquids = [];
                use.Vials = [];
                var i = use.ParcelUseAmounts.length;
                while (i--) {
                    var amt = use.ParcelUseAmounts[i];
                    if (amt.Waste_type_id == 4) use.Solids.push(amt);
                    if (amt.Waste_type_id == 3) use.Vials.push(amt);
                    if (amt.Waste_type_id == 1) use.Liquids.push(amt);
                }
                filteredUses.unshift(use);
            }
            return filteredUses;
        };
    });