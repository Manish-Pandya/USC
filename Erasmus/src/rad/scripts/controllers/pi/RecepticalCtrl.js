'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('RecepticalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getRadPIById($stateParams.pi)
  			.then(
  				function(pi){
  					console.log(pi);
  					var i = pi.SolidsContainers.length;
  					while(i--){
						pi.SolidsContainers[i].loadRoom();
  					}
  					pi.loadRooms();
					$scope.pi = pi;
  				},
  				function(){}
  			)

	    $scope.openModal = function(templateName, object){
	        var modalData = {};
	        modalData.pi = $scope.pi;
	        if(object)modalData[object.Class] = object;
	        af.setModalData(modalData);
	        var modalInstance = $modal.open({
	          templateUrl: templateName+'.html',
	          controller: 'RecepticalModalCtrl'
	        });
	    }

  })
  .controller('RecepticalModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
		var af = actionFunctionsFactory;
		$scope.af = af;

		$scope.modalData = af.getModalData();

		if(!$scope.modalData.SolidsContainerCopy){
		    $scope.modalData.SolidsContainerCopy = {
		        Class: 'SolidsContainer',
		        Room_id:null,
		        Is_active: true
		    }
		}

		$scope.selectRoom = function(){
			$scope.modalData.SolidsContainerCopy.Room_id = $scope.modalData.SolidsContainerCopy.Room.Key_id;
		}

	});
