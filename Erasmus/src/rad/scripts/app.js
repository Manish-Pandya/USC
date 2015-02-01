'use strict';

/**
 * @ngdoc overview
 * @name 00RsmsAngularOrmApp
 * @description
 * # 00RsmsAngularOrmApp
 *
 * Main module of the application.
 */
angular
  .module('00RsmsAngularOrmApp', [
    'ngRoute',
    'modelInflator',
    'genericAPI',
    'actionFunctionsModule',
    'dataSwitchModule',
    'cgBusy',
    'ui.bootstrap',
    'once',
    'ui.router'
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $httpProvider, $sceDelegateProvider) {

    console.log('config')
    $urlRouterProvider.otherwise("/home");

    $stateProvider
      .state('rad-home', {
        url: "/home",
        templateUrl: "rad-center.html"
      })
      .state('radmin', {
        url: "/admin",
        templateUrl: "admin/radmin.html",
        controller: "RadminMainCtrl"
      })
      .state('radmin.pi-detail', {
        url: "/pi-detail:pi",
        templateUrl: "admin/pi-detail.html",
        controller: "PiDetailCtrl"
      })
    /*
    $routeProvider
      .when('/', {
        templateUrl: 'views/users.html',
        controller: 'UserCtrl'
      })
      .when('/about', {
        templateUrl: 'views/about.html',
        controller: 'AboutCtrl'
      })
      .when('/users', {
        templateUrl: 'views/users.html',
        controller: 'UserCtrl'
      })      
      .when('/hazardhub', {
        templateUrl: 'views/hazardhub.html',
        controller: 'HazardHubCtrl'
      })
      .when('/hazardinventory', {
        templateUrl: 'views/hazardinventory.html',
        controller: 'HazardInventoryCtrl'
      })
      .otherwise({
        redirectTo: '/'
      }); 
  */
/*
      $httpProvider.interceptors.push(function( $q, $rootScope ) {
          return {
              'request': function(config) {
                  $rootScope.$broadcast('loading-started');
                  if(config.method=="POST"){
                    console.log(config);

                  }
                  return config || $q.then(config);
                  
              },
              'response': function(response) {
                  //console.log(response)
                  $rootScope.$broadcast('loading-complete');
                  return response || $q.then(response);
              }
          };
      });

      $sceDelegateProvider.resourceUrlWhitelist([
        // Allow same origin resource loads.
        'self',
        // Allow loading from our assets domain.  Notice the difference between * and **.
        'http://srv*.assets.example.com/**'
      ]);
*/
  });