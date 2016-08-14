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
    'filtersApp',
    'actionFunctionsModule',
    'dataSwitchModule',
    'cgBusy',
    'ui.bootstrap',
    'once',
    'ui.router',
    'modalPosition',
    'convenienceMethodWithRoleBasedModule',
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $qProvider, $provide, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
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
      .state('radmin.disposals', {
        url: "/disposals",
        templateUrl: "views/admin/disposals.html",
        controller: "disposalCtrl"
      })
      .state('radmin.drum-detail', {
        url: "/drum-detail:drumId",
        templateUrl: "views/admin/drum-detail.html",
        controller: "drumDetailCtrl"
      })
      .state('radmin.orders', {
        url: "/packages",
        templateUrl: "views/admin/parcels.html",
        controller: "AllOrdersCtrl"
      })
      //admin overview for a QuarterlyInventories
      .state('radmin.inventories', {
        url: "/inventories",
        templateUrl: "views/admin/inventories.html",
        controller: "InventoriesCtrl"
      })
      //detail admin view for a single PIQuarterlyInventory
      .state('radmin-quarterly-inventory', {
        url:'/admin/pi-quarterly-inventory:pi_inventory',
        templateUrl: "views/pi/quarterly-inventory.html",
        controller: "InventoriesCtrl"
      })
      .state('radmin.carboys', {
        url: "/carboys",
        templateUrl: "views/admin/carboys.html",
        controller: "CarboysCtrl"
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
      .state('pi-orders', {
        url:'/my-lab:pi/orders',
        templateUrl: "views/pi/orders.html",
        controller: "OrdersCtrl"
      })
      .state('use-log', {
        url:'/my-lab:pi/use-log',
        templateUrl: "views/pi/use-log.html",
        controller: "UseLogCtrl"
      })
      .state('radmin.isotopes', {
        url: "/isotopes",
        templateUrl: "views/admin/isotopes.html",
        controller: "IsotopeCtrl"
      })
      .state('parcel-use-log', {
        url:'/my-lab:pi/use-log:parcel',
        templateUrl: "views/pi/parcel-use-log.html",
        controller: "ParcelUseLogCtrl"
      })
      .state('quarterly-inventory', {
        url:'/my-lab:pi/quarterly-inventory',
        templateUrl: "views/pi/quarterly-inventory.html",
        controller: "QuarterlyInventoryCtrl"
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
      .state('current-inventories', {
        url: '/my-lab:pi/current-inventories',
        templateUrl: "views/pi/CurrentInventories.html",
        controller: "InventoryViewCtrl"
      })
      .state('lab-wipes', {
        url: '/my-lab:pi/wipe-tests',
        templateUrl: "views/pi/wipe-tests.html",
        controller: "PIWipeTestController"
      })
      .state('inspection-wipes:inspection', {
        url: "/inspection-wipes:inspection",
        templateUrl: "views/inspection/inspection-wipes.html",
        controller: "InspectionWipeCtrl"
      })



       $provide.decorator('$q', function ($delegate) {
        var defer = $delegate.defer;
        $delegate.defer = function() {
          var deferred = defer();

          deferred.promise.state = deferred.state = 'pending';

          deferred.promise.then(function() {
            deferred.promise.state = deferred.state = 'fulfilled';
          }, function () {
            deferred.promise.state = deferred.state = 'rejected';
          });

          return deferred;
        };
        return $delegate;
      });

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
          });

  });;
