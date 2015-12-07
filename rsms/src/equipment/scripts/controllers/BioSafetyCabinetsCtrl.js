'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:BioSafetyCabinetsCtrl
 * @description
 * # BioSafetyCabinetsCtrl
 * Controller of the EquipmentModule Biological Safety Cabinets view
 */
angular.module('EquipmentModule')
  .controller('BioSafetyCabinetsCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;

        var getAllBioSafetyCabinets = function(){
            return af.getAllBioSafetyCabinets()
                .then(
                    function(){
                         $scope.cabinets = dataStoreManager.get("BioSafetyCabinet");
                    }
                )
              
        },
        getAllPis = function(){
            return af.getAllPrincipalInvestigators()
                        .then(function(){$scope.pis = dataStoreManager.get("PrincipalInvestigator");})
        },
        getAllRooms = function(){
            return af.getAllRooms();
        },
        getAllBuildings = function(){
            return af.getAllBuildings();
        }
    
        //init load
        $scope.loading = getAllPis()
                            .then(getAllBuildings())
                            .then(getAllRooms())
                            .then(getAllBioSafetyCabinets());        

        $scope.deactivate = function(cabinet) {
            var copy = dataStoreManager.createCopy(cabinet);
            copy.Retirement_date = convenienceMethods.getUnixDate(new Date());
            console.log(copy);
            af.saveBioSafetyCabinet(cabinet.pi, copy, cabinet);
        }

        $scope.report = function(cabinet) {

        }

        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.BioSafetyCabinet();
                object.Is_active = true;
                object.Class = "BioSafetyCabinet";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/bio-safety-cabinet-modal.html',
                controller: 'BioSafetyCabinetsModalCtrl'
            });
        }

  })
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modalInstance) {
        var af = $scope.af = applicationControllerFactory;

        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        $scope.close = function(){
            $modalInstance.dismiss();
            af.deleteModalData();
        }

    });
