'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:HazardHubCtrl
 * @description
 * # HazardHubCtrl
 * Controller of the 00RsmsAngularOrmApp Hazard Hub
 */
angular.module('00RsmsAngularOrmApp')
  .controller('HazardHubCtrl', function ($scope, $q, $http, actionFunctionsFactory) {

    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;


    //get the root hazard node
    $scope.hazardPromise = actionFunctionsFactory.getAllHazards()
    	.then( 
    		function( hazards ){
                return hazards
    		},
            function(){
                $scope.error = 'Couldn\'t get all hazards.'
            }
    	).then(
            function( hazards ){
                    var hazard = dataStoreManager.getById('Hazard', 10000);
                    $scope.hazards = hazard.getSubHazards();
                    console.log($scope.hazards);
            },
            function(){
                $scope.error = 'Couldn\'t find the right hazards.'
            }

        );

    //local functions for ordering hazards.  in controller because it's only for the view ordering
    $scope.order = function(hazard){
        return parseFloat(hazard.Order_index);
    }

    $scope.name = function(hazard){
        return parseFloat(hazard.Name);
    }

    //view filter for displaying hazards with the matching Is_active state
    $scope.hazardFilter = function(hazard){
      if(hazard.Is_active == true)return true;
      return false;
    }


  });
