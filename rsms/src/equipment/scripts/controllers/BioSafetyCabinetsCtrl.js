'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:AutoclavesCtrl
 * @description
 * # AutoclavesCtrl
 * Controller of the EquipmentModule Biological Safety Cabinets view
 */
angular.module('EquipmentModule')
  .controller('BioSafetyCabinetsCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		$scope.af = af;
    
        $scope.cabinets = [];
    
        $scope.deactivate = function(cabinet) {
            
        }
        
        $scope.report = function(cabinet) {
            
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.BioSafetyCabinet();
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
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, bioSafetyCabinet) {
            af.saveAutoclave(copy, bioSafetyCabinet)
                .then($scope.close);
        }

		$scope.close = function(){
           $modalInstance.dismiss();
            dataStore.BioSafetyCabinet.push($scope.modalData.BioSafetyCabinetCopy);
           af.deleteModalData();
		}

	});
