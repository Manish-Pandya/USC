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
                    var formData = new FormData();
                    formData.append('file', element[0].files[0]);
                    element.blur();
                    $("label[for='"+element.attr('id')+"']").blur();
                    scope.$emit("fileUpload",formData);
                    return;
                });

            }
        };
    })
    .filter('genericFilter', function () {
        return function (items,search) {
            if(search){
                var i = 0;
                if(items)i = items.length;
                var filtered = [];

                var isMatched = function(input, item){
                    if(item.Name == input)return true;
                    return false;
                }

                while(i--){

                    //we filter for every set search filter, looping through the collection only once

                    var item=items[i];
                    item.matched = true;

                    if(search.pi){
                        if( item.PrincipalInvestigator && item.PrincipalInvestigator.User.Name && item.PrincipalInvestigator.User.Name.toLowerCase().indexOf(search.pi.toLowerCase() ) < 0 ){
                            item.matched = false;
                        }
                    }

                    if(search.department){
                        if( item.Department && item.Department.Name && item.Department.Name.toLowerCase().indexOf(search.department.toLowerCase()) < 0 )  item.matched = false;
                    }

                    if(search.hazard){
                        if( item.Hazard && item.Hazard.Name && item.Hazard.Name.toLowerCase().indexOf(search.hazard.toLowerCase()) < 0 )  item.matched = false;
                    }

                    if(item.matched == true)filtered.push(item);

                }
                filtered.reverse();
                return filtered;
            }else{
                return items;
            }
        };
    })
        .controller('BiosafetyCommitteesCtrl', function ($scope, $q, $http, applicationControllerFactory, $modal, $location) {
        //do we have access to action functions?
        $scope.af = applicationControllerFactory;
        var af = applicationControllerFactory;

        $scope.contstants = Constants;
        dataStoreManager.store(Constants.PROTOCOL_HAZARDS)

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
                        //.then(getHazards)
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
    .controller('BiosafetyCommitteesModalCtrl', function ($scope, $q, $http, applicationControllerFactory,  $modalInstance, convenienceMethods, $rootScope) {
        $scope.constants = Constants;
        $scope.dataStore = dataStore;
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        if($scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator){
            $scope.pi = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator;
            $scope.pi.selected = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator;
        }

        if($scope.modalData.BiosafetyProtocolCopy.Department && $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator){
            var i = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator.Departments.length;
            while(i--){
                if($scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator.Departments[i].Key_id == $scope.modalData.BiosafetyProtocolCopy.Department.Key_id){
                    $scope.modalData.selectedDepartment = $scope.modalData.BiosafetyProtocolCopy.PrincipalInvestigator.Departments[i];
                }
            }
        }

        if($scope.modalData.BiosafetyProtocolCopy.Hazard){
            $scope.hazard = $scope.modalData.BiosafetyProtocolCopy.Hazard;
            $scope.hazard.selected = $scope.modalData.BiosafetyProtocolCopy.Hazard;
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
            if(!orginal)orginal = null;
            console.log(orginal);
            af.saveBiosafetyProtocol(copy, orginal)
                    .then(function(){$scope.close()})
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

        $scope.$on('fileUpload', function(event, formData) {
            console.log($scope.modalData.BiosafetyProtocolCopy);
            $scope.modalData.BiosafetyProtocolCopy.reportUploaded = false;
            $scope.modalData.BiosafetyProtocolCopy.reportUploading = true;
            $scope.$apply();

            var xhr = new XMLHttpRequest;
            var url = '../ajaxaction.php?action=uploadProtocolDocument';
            if($scope.modalData.BiosafetyProtocolCopy.Key_id)url = url + "&id="+$scope.modalData.BiosafetyProtocolCopy.Key_id;
            xhr.open('POST', url, true);
            xhr.send(formData);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== XMLHttpRequest.DONE) {
                    return;
                }
                if (xhr.status !== 200) {
                    return;
                }
                if (xhr.status == 200){
                    $scope.modalData.BiosafetyProtocolCopy.reportUploaded = true;
                    $scope.modalData.BiosafetyProtocolCopy.reportUploading = false;
                    $scope.modalData.BiosafetyProtocolCopy.Report_path = xhr.responseText.replace(/['"]+/g, '');
                    $scope.$apply();
                }
            }
        });

    });
