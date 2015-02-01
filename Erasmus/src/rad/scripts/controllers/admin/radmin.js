'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
  .controller('RadminMainCtrl', function ($scope, $q, $http, actionFunctionsFactory) {
    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;

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

    var init = function(){
        getAllPIs()
            .then(
                function(pis){
                    $scope.pis = pis;
                }
            )

        getAllIsotopes()
            .then(getAllParcels)
            .then(getAllIsotopes)
            .then(getAllPOs)
    }

    init();

  });
