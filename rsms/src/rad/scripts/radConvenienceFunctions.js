'use strict';
angular.module('radUtilitiesModule', [
    'convenienceMethodWithRoleBasedModule'
])
    .factory('radUtilitiesFactory', function radUtilitiesFactory($rootScope, convenienceMethods, roleBasedFactory){
        var radUtilitiesFactory = {};

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
                }
            );

            console.debug(allContainers);
            return allContainers;
        }; // end getAllWasteContainersFromPickup

        return radUtilitiesFactory;
    });
