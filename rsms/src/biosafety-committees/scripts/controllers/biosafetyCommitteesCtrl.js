'use strict';

/**
 * @ngdoc function
 * @name BiosafetyCommittees.controller:BiosafetyCommitteesCtrl
 * @description
 * # BiosafetyCommitteesCtrl
 * Controller of BiosafetyCommittees
 */
angular.module('BiosafetyCommittees')
    .directive('fileUpload', function (applicationControllerFactory) {
        return {
            restrict: 'A',
            scope: true,
            link: function (scope, element, attr) {

                element.bind('change', function () {
                    var id;
                    var formData = new FormData();
                    formData.append('file', element[0].files[0]);
                    if(element.attr("key_id") != null){
                        id = element.attr("key_id");
                    }
                    applicationControllerFactory.uploadBiosafteyProtocol( formData,id );
                });

            }
        };
    })
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

        $scope.openProtocolModal = function (protocol) {
            var modalData = {};
            if(!protocol){
                var protocol = new window.BiosafetyProtocol();
                protocol.Is_active = true;
                protocol.Class = "BiosafetyProtocol";
            }
            modalData.BiosafetyProtocol = protocol;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/protocol-modal.html',
                controller: 'BiosafetyCommitteesModalCtrl'
            });
        }

    })
    .controller('BiosafetyCommitteesModalCtrl', function ($scope, $q, $http, applicationControllerFactory,  $modalInstance,convenienceMethods) {
        $scope.constants = Constants;
        $scope.dataStore = dataStore;
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        if($scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator){
            $scope.pi = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator;
            $scope.pi.selected = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator;
        }

        $scope.onSelectPi = function(pi){
            $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator = pi;
            $scope.modalData.BiosafetyProtocolCopy.Principal_investigator_id = pi.Key_id;
        }

        $scope.onSelectDepartment = function(department){
            $scope.modalData.BiosafetyProtocolCopy.Department = department;
            $scope.modalData.BiosafetyProtocolCopy.Department_id = department.Key_id;
        }

        $scope.onSelectHazard = function(hazard){
            $scope.modalData.BiosafetyProtocolCopy.Hazard = hazard;
            $scope.modalData.BiosafetyProtocolCopy.Hazard_id = hazard.Key_id;
        }

       $scope.save = function(copy, orginal){
           console.log(copy);
           return;
            if(!orginal)orginal = null;
            console.log(orginal);
            af.saveBiosafetyProtocol(copy, orginal)
                    .then(function(){$scope.close()})
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }


    });
