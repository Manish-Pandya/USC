'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .controller('RadminMainCtrl', function ($scope, $rootScope, actionFunctionsFactory, $state, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.$state = $state;
        af.getRadModels()
            .then(
                function (models) {
                    var pis = dataStoreManager.get('PrincipalInvestigator');
                    console.log(dataStore);
                    $scope.typeAheadPis = [];
                    var i = pis.length;
                    while (i--) {
                            if (pis[i].User) {
                                var pi = {
                                Key_id:pis[i].Key_id,
                                User:{
                                    Name: pis[i].User.Name,
                                    Key_id: pis[i].Key_id
                                }
                            };
                        }
                        $scope.typeAheadPis.push(pi);
                    }
                }
            )

        $scope.onSelectPi = function (pi) {
            $state.go('radmin.pi-detail', {
                pi: pi.Key_id
            });
        }

    });
