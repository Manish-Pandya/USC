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
    'ui.router',
    'modalPosition',
    'convenienceMethodModule'
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider,$qProvider, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
    $urlRouterProvider.otherwise("/home");
    $stateProvider
      .state('rad-home', {
        url: "/home",
        templateUrl: "views/rad-center.html"
      })
      .state('radmin', {
        url: "/admin",
        templateUrl: "views/admin/radmin.html",
        controller: "RadminMainCtrl"
      })
      .state('radmin.pi-detail', {
        url: "/pi-detail:pi",
        templateUrl: "views/admin/pi-detail.html",
        controller: "PiDetailCtrl"
      })
      .state('radmin.wipe-tests', {
        url: "/wipe-tests",
        templateUrl: "views/admin/wipe-tests.html",
        controller: "WipeTestController"
      })
      .state('admin-pickups', {
        url: "/admin/pickups",
        templateUrl: "views/admin/pickups.html",
        controller: "AdminPickupCtrl"
      })
      .state('pi-rad-management', {
        url:'/my-lab:pi',
        templateUrl: "views/pi/pi-rad-home.html",
        controller: "PiRadHomeCtrl"
      })     
      .state('use-log', {
        url:'/my-lab:pi/use-log',
        templateUrl: "views/pi/use-log.html",
        controller: "UseLogCtrl"
      })
      .state('parcel-use-log', {
        url:'/my-lab:pi/use-log:parcel',
        templateUrl: "views/pi/parcel-use-log.html",
        controller: "ParcelUseLogCtrl"
      })
      .state('solids', {
        url:'/my-lab:pi/waste-recepticals',
        templateUrl: "views/pi/recepticals.html",
        controller: "RecepticalCtrl"
      })
      .state('carboys', {
        url:'/my-lab:pi/carboys',
        templateUrl: "views/pi/carboys.html",
        controller: "PiCarboyCtrl"
      })
      .state('pickups', {
        url:'/my-lab:pi/pickups',
        templateUrl: "views/pi/pickups.html",
        controller: "PickupCtrl"
      })
      .state('inspection-wipes:inspection', {
        url: "/inspection-wipes:inspection",
        templateUrl: "views/inspection/inspection-wipes.html",
        controller: "InspectionWipeCtrl"
      })

      .state('testpage', {
        url: '/testpage',
        templateUrl: 'views/testpage.php',
        controller: "TestCtrl"
      })

  })
  .controller('NavCtrl', function ($rootScope, actionFunctionsFactory, $state) {
    $rootScope.$on('$stateChangeStart ',function(){
      $rootScope.loading = true;
    });
    $rootScope.$on('$stateChangeSuccess', 
        function(event, toState, toParams, fromState, fromParams){
            $rootScope.loading = false;
            var viewMap = actionFunctionsFactory.getViewMap($state.current);
            $rootScope.viewLabel = viewMap.Label;
            $rootScope.bannerClass = viewMap.Name;
            $rootScope.dashboardView = viewMap.Dashboard;
            $rootScope.noHead = viewMap.NoHead;
            console.log($rootScope);
          });
   
  });;
