'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:GenericModalCtrl
 * @description
 * # GengericModalCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
  .controller('GenericModalCtrl', function ($scope, $rootScope, $q, actionFunctionsFactory, $modalInstance) {
  	var af = actionFunctionsFactory;
  	$scope.af = af;
  	$rootScope.modalData = af.getModalData();
  	$scope.close = function(){
  		$modalInstance.dismiss();
  	}
  });