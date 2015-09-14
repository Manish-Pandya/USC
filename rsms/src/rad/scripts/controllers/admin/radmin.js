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

    var getAllAuthorizations = function(){
        return actionFunctionsFactory.getAllAuthorizations()
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

    var getAllUsers = function(){
        return actionFunctionsFactory.getAllUsers()
            .then(
                function( users ){
                    return users;
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
                        //$scope.pis[i].loadUser();
                        if($scope.pis[i].User)var pi = {Name:pis[i].User.Name, Key_id:pis[i].Key_id};
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

    var getAllSolidsContainers = function(){
        return actionFunctionsFactory.getAllSolidsContainers()
        .then(
            function( containers ){
                return containers;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Isotopes.  Please check your internet connection and try again.'
            }

        );
    }

    var getAllCarboys = function(){
        return actionFunctionsFactory.getAllCarboys()
        .then(
            function( carboys ){
                return carboys;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Isotopes.  Please check your internet connection and try again.'
            }

        );
    }

    var getAllCarboyUseCycles = function(){
        return actionFunctionsFactory.getAllCarboyUseCycles()
        .then(
            function( cycles ){
                return cycles;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Isotopes.  Please check your internet connection and try again.'
            }

        );
    }

    var getAllRooms = function(){
        return actionFunctionsFactory.getAllRooms()
        .then(
            function( rooms ){
                return rooms;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Rooms.  Please check your internet connection and try again.'
            }

        );
    }

    $rootScope.pisPromise = getAllUsers()
            .then(getAllPIs)
            .then(getAllRooms)
    /*
            .then(getAllIsotopes)
            .then(getAllAuthorizations)
            .then(getAllPOs)
            .then(getAllParcels)
            .then(getAllParcels)
            .then(getAllParcels)
            .then(getAllCarboys)
            .then(getAllSolidsContainers)
            .then(getAllCarboyUseCycles)
    */
    $scope.onSelectPi = function (pi)
    {
        $state.go('radmin.pi-detail',{pi:pi.Key_id});
    }

  });
