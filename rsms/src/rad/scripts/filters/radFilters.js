angular.module('00RsmsAngularOrmApp')
	.filter('activePickups', function () {
	    return function (pickups, all) {
            // nothing to filter...
            if (!pickups) return;

            // requested not to filter; show all
            if (all) return pickups;

            // Filter to only 'active' pickups
            // TODO: DEFINE 'ACTIVE'!
	        var activePickups = pickups.filter(function (pickup) {
                var d = moment(pickup.Pickup_date);
                var isAfter = moment(d).add(1, 'day').isAfter();
                var isPickedUp = pickup.Status == Constants.PICKUP.STATUS.PICKED_UP;
                var isRequested = pickup.Status == Constants.PICKUP.STATUS.REQUESTED;
	            return ( isAfter ) || (isPickedUp || isRequested);
	        })
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
                    || cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM
                    || (cycle.Status == Constants.CARBOY_USE_CYCLE.STATUS.MIXED_WASTE && !cycle.Drum_id)) {

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
            if (!statuses) return;
            var disposalStatuses = [];
            var i = statuses.length;
            for (var prop in statuses) {
                var status = statuses[prop];
                if (status == Constants.CARBOY_USE_CYCLE.STATUS.DECAYING
                    || status == Constants.CARBOY_USE_CYCLE.STATUS.HOT_ROOM
                    || status == Constants.CARBOY_USE_CYCLE.STATUS.MIXED_WASTE) {
                    disposalStatuses.unshift(status);
                }
            }
            return disposalStatuses.sort(function (a, b) { return a > b;});
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
.filter('disposalMiscs', function () {
    return function (miscs) {
        if (!miscs) return;
        var disposalMiscs = miscs.filter(function (misc) {
            return !misc.Drum_id;
        })
        return disposalMiscs;
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
    })
    //parcels that are transfers from other insitutions
    .filter('transferInParcels', function () {
        return function (parcels) {
            if (!parcels) return;
            filteredParcels = parcels.filter(function (parcel) {
                return (parcel.Transfer_in_date && moment(parcel.Transfer_in_date).year() > 2016 && !parcel.Transfer_amount_id);
            });
            return filteredParcels;
        };
    })
    //parcels that are transfers from other insitutions
    .filter('transferInventoryParcels', function () {
        return function (parcels) {
            if (!parcels) return;
            filteredParcels = parcels.filter(function (parcel) {
                return (parcel.Transfer_in_date && moment(parcel.Transfer_in_date).year() < 2017 && !parcel.Transfer_amount_id);
            });
            return filteredParcels;
        };
    })
    //parcels that are transfers from other one pi to another
    .filter('transferBetweenUses', function () {
        return function (uses) {
            if (!uses) return;
            filteredUses = uses.filter(function (use) {
                return use.Is_transfer && use.Destination_parcel_id;
            });
            return filteredUses;
        };
    })
    //transfers out to other institutions
    .filter('transferOutUses', function () {
        return function (uses) {
            if (!uses) return;
            filteredUses = uses.filter(function (use) {
                return use.Is_transfer && !use.Destination_parcel_id;
            });
            return filteredUses;
        };
    
    })
    .filter('parcelsInLab', function () {
        return function (parcels) {
            filteredParcels = parcels.filter(function (p) {
                return p.Status == Constants.PARCEL.STATUS.DELIVERED;
            })
            return filteredParcels;
        }
    })
    .filter('availableBags', function () {
        return function (bags, solid) {
            var overridingRoles = [Constants.ROLE.NAME.ADMIN, Constants.ROLE.NAME.RADIATION_ADMIN, Constants.ROLE.NAME.RADIATION_INSPECTOR];
            return overridingRoles.filter(function (r) {
                return GLOBAL_SESSION_ROLES.userRoles.indexOf(r) != -1;
            }).length ? bags : bags.filter(function (b) { return solid ? (!b.Pickup_id || b.Key_id == solid.Waste_bag_id) : !b.Pickup_id})
        }
    })
    .filter('matchingIsotope', function () {
        return function (auths, parcel) {
            if (!auths) return;
            var auth = dataStoreManager.getById("Authorization", parcel.Authorization_id);
            var id = auth.Isotope_id;
            return auths.filter( function (a) { return a.Isotope_id == id; } )
        }
    })
    .filter('availableTypes', function (convenienceMethods) {
        return function (owts, pi) {
            if (!owts) return;
            if (!pi) return owts
            return owts.filter( function( owt ){ return owt.Is_active && !convenienceMethods.arrayContainsObject(pi.OtherWasteTypes, owt); })
        }
    })
    .filter('parcelFilter', function() {
        return function (parcels, filterObj) {
            if (!parcels) return
            if (!filterObj) return parcels;
            return parcels.filter(function (p) {
                let include = true;

                if (filterObj.rs) {
                    include = p.Rs_number != null && p.Rs_number.toLowerCase().indexOf(filterObj.rs.toLowerCase()) != -1;
                }

                if (include && filterObj.isotope) {
                    include = p.Authorization && p.Authorization.IsotopeName && p.Authorization.IsotopeName.toLowerCase().indexOf(filterObj.isotope.toLowerCase()) != -1;
                }

                /*
                if (include && filterObj.date) {
                    include = p.Rs_number && p.Rs_number.indexOf(filterObj.rs) != -1;
                }
                */

                return include;
            })
        }
    }).filter('pickupPi', function () {
        return function (pickups, pi) {
            if ('undefined' == typeof pi) return pickups;
            return pickups && pickups.length ? pickups.filter(function (pu) {
                console.log(pu, pi)
                return pu.PrincipalInvestigator && pu.PrincipalInvestigator.Name && pu.PrincipalInvestigator.Name.toLowerCase().indexOf(pi.toLowerCase()) != -1;
            }) : []
        }
    }).filter('openContainers', function () {
        return function (containers, reverse) {
            if (!reverse) reverse = false;
            return typeof containers != "undefined" ? containers.filter(function (c) {
                return (c.Close_date == null) != reverse;
            }) : []
        }
    }).filter('unit', function () {
        return function (str, obj) {
            if (str == null) return "";
            return obj.Is_mass ?  str + "g" : str + "mCi";
        }
    }).filter('shippedOrNot', function () {
        return function (drums, showShipped) {
            if (!drums) return;
            return drums.filter(function (d) {
                return showShipped ? d.Pickup_date != null : d.Pickup_date == null;
            })
        }
    }).filter('orderEmpty', () => {
        return (things, prop) => {
            if (!things) return;
            if (!prop) return things;
            return things.sort((a, b) => {
                if (!a[prop]) return 1;
                if (!b[prop]) return -1;
                return a[prop] > b[prop];
            })
        }
    }).filter('sortIsotopes', () => {
        return (auths, reverse) => {
            if (!auths) return;
            if (!reverse) reverse = false;

            return auths.sort((a, b) => {
                return a.License_line_item && b.License_line_item && a.License_line_item.length > b.License_line_item.length && a.License_line_item > b.License_line_item;
            })
        }
    })

    .filter('sumContainersActivityPerIsotope', () => {
        // Since this is TRANSFORMING the incoming containers rather than FILTERING per se,
        //   run the results through _.memoize in order to properly cache/hash the values
        //   to avoid Angular getting confuzed by the new-but-equal objects
        return _.memoize(
            function(containers){
                if(!containers) return [];

                var totals = [];
                containers.forEach(function(container){
                    container.Contents.forEach( function(content){
                        if( totals.filter(c => c.Isotope_name == content.Isotope_name).length == 0){
                            totals.push({
                                Isotope_name: content.Isotope_name,
                                Is_mass: content.Is_mass,
                                Total: 0
                            });
                        }

                        var iso = totals.filter(c => c.Isotope_name == content.Isotope_name)[0];

                        iso.Total = iso.Total + parseFloat(content.Curie_level);
                    });
                });

                return totals;
            },
            function(containers){
                // Identify this set by the length of the containers array prefixed by the first 'other' waste type
                var prefix = containers.length ? containers[0].Other_waste_type_id : '0';

                return prefix + ':' + containers.length;
            }
        );
    })

    .filter('filterInactiveTypes', () => {
        return function(containers, types){
            if( !containers || !containers.length || !types ){
                return containers;
            }

            // Filter only those containers whose types are active
            var filtered = containers.filter(container => {
                var type = types.find(t => t.Class == container.Class
                    || (container.Other_waste_type_id && container.Other_waste_type_id == t.Key_id));
                return type && type.active;
            });
            return filtered;
        };
    })

    .filter('filterOnlyDisposedContainers', function(radUtilitiesFactory) {
        return function(containers, limitToDisposed){
            if( !limitToDisposed || !containers ){
                return containers;
            }

            // Limit to disposed containers
            return containers.filter(c => radUtilitiesFactory.isContainerDisposed(c));
        };
    })

    .filter('containerContainsText', () => {
        return function(containers, searchText){
            if( !searchText || searchText.length == 0 || !containers ){
                return containers;
            }

            var searchUpper = searchText.toUpperCase();
            function match(str){
                return str != null && str.length && str.toUpperCase().includes(searchUpper);
            }

            return containers.filter(c => {
                var fields = [
                    c.PI && c.PI.Name,
                    c.ViewLabel,
                    c.CarboyNumber,
                    c.Comments
                ];

                return fields.filter(value => match(value)).length > 0;
            });
        }
    });
