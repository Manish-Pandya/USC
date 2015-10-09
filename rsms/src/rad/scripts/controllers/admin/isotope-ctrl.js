'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('IsotopeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		var getAllIsotopes = function(){
  			af.getAllIsotopes()
  			.then(
  				function(isotopes){  	
  					$scope.isotopes = dataStore.Isotope;
  				},
  				function(){}
  			)
  		}

  		$scope.af = af;
  		$rootScope.isotopesPromise = getAllIsotopes();
    
        $scope.deactivate = function(isotope){
            var copy = dataStoreManager.createCopy(isotope);
            copy.Is_active = !copy.Is_active;
            af.saveCarboy(copy, isotope);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Carboy();
                object.Class = "Carboy";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/isotope-modal.html',
                controller: 'IsotopeModalCtrl'
            });
        }

  })
  .controller('IsotopeModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;
		$scope.modalData = af.getModalData();
    
        if(!af.getModalData().Isotope){
            $scope.modalData.IsotopeCopy = new window.Isotope();
            $scope.modalData.IsotopeCopy.Class="Isotope";
        }
    
        console.log($scope.modalData);
        $scope.save = function(copy, isotope) {
            af.saveIsotope(copy, isotope)
                .then($scope.close);
        }

		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});
