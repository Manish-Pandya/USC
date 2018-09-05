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

        /**
         * Retrieves all waste containers in the given Pickup
         * @param {Pickup} pickup
         */
        radUtilitiesFactory.getAllWasteContainersFromPickup = function(pickup){
            console.debug("Collect all containers from pickup ", pickup);
            var allContainers = radUtilitiesFactory._mergeContainerArrays([
                pickup.Carboy_use_cycles,
                pickup.Waste_bags,
                pickup.Scint_vial_collections,
                pickup.Other_waste_containers
            ]);

            allContainers = radUtilitiesFactory._extendWasteContainersInfo(allContainers);

            console.debug(allContainers);
            return allContainers;
        }; // end getAllWasteContainersFromPickup

        /**
         * Retrieves all waste containers which are mapped to the given Drum
         * @param {Drum} drum
         */
        radUtilitiesFactory.getAllWasteContainersFromDrum = function(drum){
            console.debug("Collect all containers from drum ", drum);
            // filter only containers in this drum
            var allContainers = radUtilitiesFactory.getAllWasteContainers()
                .filter(c => c.Drum_id == drum.Key_id);

            allContainers = radUtilitiesFactory._extendWasteContainersInfo(allContainers);

            console.debug(allContainers);
            return allContainers;
        };

        /**
         * Retrieves all waste containers from the dataStore
         */
        radUtilitiesFactory.getAllWasteContainers = function(){
            // Read all containers from datastore and merge them into one array
            var containers = radUtilitiesFactory._mergeContainerArrays([
                dataStore.WasteBag,
                dataStore.ScintVialCollection,
                dataStore.CarboyUseCycle,
                dataStore.OtherWasteContainer
            ]);

            containers = radUtilitiesFactory._extendWasteContainersInfo(containers);

            return containers;
        };

        /**
         * Merges the given array of arrays into a single array
         * @param {Array[Array]} arrays
         */
        radUtilitiesFactory._mergeContainerArrays = function(arrays){
            // concat all elements of 2D array
            return arrays.reduce( (_all, _arr) => _all.concat(_arr || []), []);
        };

        /**
         * Extends each container to include 'friendly' labels and a 1-based index
         * @param {Array} containers
         */
        radUtilitiesFactory._extendWasteContainersInfo = function(containers){
            return containers.map( (c, idx) => {
                var container = angular.extend({}, c);

                // Consolidate label-name mismatches
                container.ViewLabel = c.Label || c.CarboyNumber || c.Name;

                //we index at 1 because JS can't tell the difference between false and the number 0 (see return of $scope.getContainer method below)
                container.idx = idx + 1;

                // Apply 'friendly' labels by-type
                container.ClassLabel = radUtilitiesFactory.getFriendlyWasteLabel(c.Class);

                return container;
            });
        }

        return radUtilitiesFactory;
    });
