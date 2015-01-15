'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiDetailCtrl', function ($scope, actionFunctionsFactory, $stateParams) {
    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;


    //get the root hazard node
    $scope.pi = actionFunctionsFactory.getById("PrincipalInvestigator", $stateParams.pi);  

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
