'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiDetailCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope) {
    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;
    var getRadPi = function(){
        return actionFunctionsFactory.getRadPIById($stateParams.pi)
                .then(
                    function(pi){
                        $scope.pi = pi;
                        pi.loadAuthorizations();
                        return pi;
                    },
                    function(){
                    }
                );  
    }

    //get the all the pis
    $rootScope.piPromise = actionFunctionsFactory.getAllPIs()
      .then(getRadPi);

    $scope.onSelectPi = function (pi)
    {
        $state.go('.pi-detail',{pi:pi.Key_id});
    }


  });
