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

    var getAllAuthorizations = function(){
        return actionFunctionsFactory.getAllAuthorizations
            .then(
                function(authorizations){
                    return authorizations
                },
                function(){
                    $scope.error = 'There was an error when the system tried to get the list of Authorizations.  Please check your internet connection and try again.'
                }
            )
    }

    var getAllParcels = function(){
        return actionFunctionsFactory.getAllParcels()
            .then(
                function( parcels ){
                    return parcels;
                },
                function(){
                    $scope.error = 'There was an error when the system tried to get the list of Principal Investigators.  Please check your internet connection and try again.'
                }

            );
    }

    var getAllPOs = function(){
        return actionFunctionsFactory.getAllPurchaseOrders()
            .then(
                function( pos ){
                    return pos;
                },
                function(){
                    $scope.error = 'There was an error when the system tried to get the list of Purchase Orders.  Please check your internet connection and try again.'
                }

            );
    }

    var getAllPIs = function(){
        return actionFunctionsFactory.getAllPIs()
            .then(
                function( pis ){
                   $scope.pis = af.getCachedCollection('PrincipalInvestigator');
                   $scope.typeAheadPis = [];
                   var i = $scope.pis.length;
                   while(i--){
                        var pi = {Name:pis[i].User.Name, Key_id:pis[i].Key_id};
                        console.log(pi);
                        $scope.typeAheadPis.push(pi);
                }
                return pis;
                },
                function(){
                    $scope.error = 'There was an error when the system tried to get the list of Principal Investigators.  Please check your internet connection and try again.'
                }

            );
    }

    var getAllIsotopes = function(){
        return actionFunctionsFactory.getAllIsotopes()
        .then(
            function( isotopes ){
                return isotopes;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Isotopes.  Please check your internet connection and try again.'
            }

        );
    }

    $rootScope.pisPromise = getAllPIs()
            .then(getAllIsotopes)
            .then(getAllParcels)
            .then(getAllIsotopes)
            .then(getAllPOs)
    

    $scope.onSelectPi = function (pi)
    {
        $state.go('radmin.pi-detail',{pi:pi.Key_id});
    }

  });
