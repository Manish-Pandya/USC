'use strict';
angular.module('radUtilitiesModule', [
    'convenienceMethodWithRoleBasedModule'
])
    .factory('radUtilitiesFactory', function radUtilitiesFactory($rootScope, convenienceMethods, roleBasedFactory){
        var radUtilitiesFactory = {};

        radUtilitiesFactory.groupPickupsByStatus = function(pickups){
            return [
                radUtilitiesFactory.$$groupPickups(pickups, 'Requested', 'REQUESTED'),
                radUtilitiesFactory.$$groupPickups(pickups, 'Picked Up', 'PICKED_UP'),
                radUtilitiesFactory.$$groupPickups(pickups, 'At RSO', 'AT_RSO')
            ];
        };

        radUtilitiesFactory.$$groupPickups = function(pickups, label, statusName){
            var status = Constants.PICKUP.STATUS[statusName];
            return {
                active: true,

                label: label,
                status: status,
                statusName: statusName,

                allowStart: status == Constants.PICKUP.STATUS.REQUESTED,
                allowEdit: [Constants.PICKUP.STATUS.PICKED_UP, Constants.PICKUP.STATUS.AT_RSO].includes(status),
                allowComplete: status == Constants.PICKUP.STATUS.PICKED_UP,

                listAvailableContainers: [Constants.PICKUP.STATUS.REQUESTED, Constants.PICKUP.STATUS.PICKED_UP].includes(status),
                listIncludedContainers: [Constants.PICKUP.STATUS.PICKED_UP, Constants.PICKUP.STATUS.AT_RSO].includes(status),

                pickups: pickups.filter(p => p.Status == status)
            };
        };

        radUtilitiesFactory.getStatusNameByValue = function(statusValue){
            switch(statusValue){
                case Constants.PICKUP.STATUS.REQUESTED: return 'REQUESTED';
                case Constants.PICKUP.STATUS.PICKED_UP: return 'PICKED_UP';
                case Constants.PICKUP.STATUS.AT_RSO:    return 'AT_RSO';
                default: throw new Exception('No status value provided');
            }
        };

        radUtilitiesFactory.getFriendlyWasteLabel = function(wasteType){
            switch (wasteType) {
                case ("WasteBag"):            return "Waste Bags";
                case ("CarboyUseCycle"):      return "Carboys";
                case ("ScintVialCollection"): return "Scint Vial Containers";
                case ("OtherWasteContainer"): return "Other Waste";
                default:                      return "";
            }
        };

        radUtilitiesFactory.applyWasteTypeLabels = function(containers){
            containers.forEach(c => {
                // Apply 'friendly' labels by-type
                c.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(c.Class);
                return c;
            });

            return containers;
        };

        radUtilitiesFactory.getAllWasteContainersFromPickup = function(pickup){
            console.debug("Collect all containers from pickup ", pickup);
            var allContainers =
                (pickup.Carboy_use_cycles || [])
                .concat(pickup.Waste_bags || [])
                .concat(pickup.Scint_vial_collections || [])
                .concat(pickup.Other_waste_containers || [])
                .map(function (c, idx) {
                    var container = angular.extend({}, c);

                    // Consolidate label-name mismatches
                    container.ViewLabel = c.Label || c.CarboyNumber || c.Name;

                    //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
                    container.idx = idx + 1;

                    // Apply 'friendly' labels by-type
                    container.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(c.Class);

                    return container;
                }
            );

            console.debug(allContainers);
            return allContainers;
        }; // end getAllWasteContainersFromPickup

        radUtilitiesFactory.getAllWasteContainersFromDrum = function(drum){
            console.debug("Collect all containers from drum ", drum);
            var allContainers =
                (drum.WasteBags || [])
                .concat(drum.ScintVialCollections || [])
                .concat(drum.CarboyUseCycles || [])
                .concat(drum.OtherWasteContainers || [])
                .map(function (c, idx) {
                    var container = angular.extend({}, c);

                    // Consolidate label-name mismatches
                    container.ViewLabel = c.Label || c.CarboyNumber || c.Name;

                    //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
                    container.idx = idx + 1;

                    // Apply 'friendly' labels by-type
                    container.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(c.Class);

                    return container;
                }
            );

            console.debug(allContainers);
            return allContainers;
        };

        return radUtilitiesFactory;
    });
