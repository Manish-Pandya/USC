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

        radUtilitiesFactory.getIconClassByContainer = function(container) {
            return radUtilitiesFactory.getIconClassByContainerType(container.Class);
        };

        radUtilitiesFactory.getIconClassByContainerType = function(containerClass){
            switch(containerClass){
                case "WasteBag":            return "icon-remove-2 solids-containers";
                case "ScintVialCollection": return "icon-sv scint-vials";
                case "CarboyUseCycle":      return "icon-carboy carboys";
                case "OtherWasteContainer": return "other icon-beaker-alt red";
                default: return "";
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
            var allContainers = radUtilitiesFactory._mergeArrays([
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
            var containers = radUtilitiesFactory._mergeArrays([
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
        radUtilitiesFactory._mergeArrays = function(arrays){
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

        /**
         * Returns true if the given container is Disposed
         * @param {Container} container 
         */
        radUtilitiesFactory.isContainerDisposed = function(c){
            /*
                Disposed means:
                carboy cycle is DRUMMED or POURED
                generic container is drummed (and drum shipped)
            */

            if( c.Class == 'OtherWasteContainer'){
                // Other waste containers disposed if they are closed ('cleared')
                return c.Close_date != null;
            }
            else if( c.Class == 'CarboyUseCycle' ){
                // Carboy is Disposed if...
                // ... it is Poured
                if( c.Status == Constants.CARBOY_USE_CYCLE.STATUS.POURED){
                    return true;
                }

                // ... or it is Drummed and the drum is shipped
                if( c.Status == Constants.CARBOY_USE_CYCLE.STATUS.DRUMMED ){
                    return radUtilitiesFactory._isDrumShippedById(c.Drum_id);
                }
            }
            // 'generic' container is Disposed if it is drummed and shipped
            else if( c.Drum_id ){
                return radUtilitiesFactory._isDrumShippedById(c.Drum_id);
            }

            return false;
        }

        radUtilitiesFactory._isDrumShippedById = function(drumId){
            if( !drumId ){
                return false;
            }

            var drum = dataStoreManager.getById('Drum', drumId);
            if( !drum ){
                return false;
            }

            return drum.Pickup_date != null;
        }

        radUtilitiesFactory.getPIAuthorization = function getPIAuthorization( pi ){
            // Get the most-recent PIAuthorization
            // TODO: Why not get 'current' auth from pi?
            var piAuth = null;
            if (pi.Pi_authorization && pi.Pi_authorization.length) {
                var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                        return moment(amendment.Approval_date).valueOf();
                    }]);
                piAuth = auths[auths.length - 1];
            }

            if( piAuth && piAuth.Termination_date ){
                console.debug("PI Authorization is terminated", piAuth);
            }

            return piAuth;
        }

        return radUtilitiesFactory;
    });
