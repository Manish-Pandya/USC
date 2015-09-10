'use strict';

/**
 * @ngdoc function
 * @name rootApplicationController.controller:GenericModalCtrl
 * @description
 * # GengericModalCtrl
 * Controller of the rootApplicationController
 */
angular.module('rootApplicationController')
  .controller('GenericModalCtrl', function ($scope, $rootScope, $q, rootApplicationControllerFactory, $modalInstance) {
  	var af = rootApplicationControllerFactory;
  	$scope.af = af;
  	$rootScope.modalData = af.getModalData();
  	$scope.close = function(){
  		$modalInstance.dismiss();
  	}
  });