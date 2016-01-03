'use strict';

/**
 * @ngdoc function
 * @name BiosafetyCommittees.controller:BiosafetyCommitteesCtrl
 * @description
 * # BiosafetyCommitteesCtrl
 * Controller of BiosafetyCommittees
 */
angular.module('BiosafetyCommittees')
    .controller('BiosafetyCommitteesCtrl', function ($scope, $q, $http, applicationControllerFactory, $modal, $location) {
        //do we have access to action functions?
        $scope.af = applicationControllerFactory;
        var af = applicationControllerFactory;
    
        var getPIs = function () {
            return af
                .getAllPIs()
                .then(function (pis) {
                        //we have to set this equal to the promise rather than the getter, because the getter will return a promise, and that breaks the typeahead because of a ui-bootstrap bug
                        $scope.PIs = dataStoreManager.get("PrincipalInvestigator");
                        return pis;
                    },
                    function () {
                        $scope.error = 'There was a problem getting the list of Principal Investigators.  Please check your internet connection.'
                    });
        },
        getHazards = function () {
            return af
                .getAllHazards()
                .then(
                    function (hazards) {
                        console.log(dataStore);
                        $scope.hazards = dataStoreManager.get('Hazard');
                        return hazards
                    },
                    function () {
                        $scope.error = 'Couldn\'t get all hazards.'
                    }
                )
        },
        getDepartments = function () {
            return af
                .getAllDepartments()
                .then(
                    function (depts) {
                        $scope.departments = dataStoreManager.get('Department');
                        return depts
                    },
                    function () {
                        $scope.error = 'Couldn\'t get all hazards.'
                    }
                )
        },
        getProtocols = function () {
            return af
                .getAllProtocols()
                .then(
                    function (protocols) {
                        $scope.protocols = dataStoreManager.get('BiosafetyProtocol');
                        return protocols
                    },
                    function () {
                        $scope.error = 'Couldn\'t get all hazards.'
                    }
                )
        }

        $scope.init = getHazards()
                        .then(getDepartments)
                        .then(getHazards)
                        .then(getPIs)
                        .then(getProtocols);

    })
    .controller('BiosafetyCommitteesModalCtrl', function ($scope, $q, $http, applicationControllerFactory,  $modalInstance,convenienceMethods) {
        $scope.constants = Constants;
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.processRooms = function(inspection, rooms){
            for(var j = 0; j<inspection.Rooms.length; j++){
                inspection.Rooms[j].checked = true;
            }
            for(var k = 0; k<rooms.length; k++){
                if(!convenienceMethods.arrayContainsObject(inspection.Rooms, rooms[k])){
                    inspection.Rooms.push(rooms[k]);
                }
            }
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }


    });
