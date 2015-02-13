'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiDetailCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;

    var getRadPi = function(){
        var pi = af.getById("PrincipalInvestigator",$stateParams.pi);
        return actionFunctionsFactory.getRadPI(pi)
                .then(
                    function(){
                        $scope.pi = pi;
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

    $scope.openModal = function(templateName, object){
        var modalData = {};
        modalData.pi = $scope.pi;
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: templateName+'.html',
          controller: 'GenericModalCtrl'
        });
    }


  });
