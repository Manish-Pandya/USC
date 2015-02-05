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
  })
  .controller('NavCtrl', function ($rootScope, actionFunctionsFactory, $state) {
    console.log($state);
    $rootScope.$on('$stateChangeSuccess', 
        function(event, toState, toParams, fromState, fromParams){
            var viewMap = actionFunctionsFactory.getViewMap($state.current);
            console.log(viewMap);
            $rootScope.viewLabel = viewMap.Label;
            $rootScope.bannerClass = viewMap.Name;
            $rootScope.dashboardView = viewMap.Dashboard;
          });
   
  });;
