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
    //get the all the pis
    $scope.piPromise = actionFunctionsFactory.getAllPIs()
      .then(
            function( pis ){
                actionFunctionsFactory.getRadPIById($stateParams.pi)
                    .then(
                        function(pi){
                            $scope.pi = pi;
                            pi.loadAuthorizations();
                        },
                        function(){
                        }
                    );  
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Principal Investigators.  Please check your internet connection and try again.'
            }

        );

    //local functions for ordering hazards.  in controller because it's only for the view ordering
    $scope.order = function(hazard){
        return parseFloat(hazard.Order_index);
    }

    $scope.name = function(hazard){
        return parseFloat(hazard.Name);
    }

    $scope.onSelectPi = function (pi)
    {
        $state.go('.pi-detail',{pi:pi.Key_id});
    }


  });
