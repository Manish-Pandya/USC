'use strict';

/**
 * @ngdoc overview
 * @name EquipmentModule
 * @description
 * # Equipment Module
 *
 * Main module of the equipment application.
 */
angular
  .module('EquipmentModule', [
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
    'filtersApp',
    'convenienceMethodWithRoleBasedModule'
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $qProvider, $provide, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
    $urlRouterProvider.otherwise("/home");
    $stateProvider
      .state('equipment-home', {
        url: "/home",
        templateUrl: "views/equipment-center.html"
      })
      .state('autoclaves', {
        url: "/autoclaves",
        templateUrl: "views/autoclaves.html",
        controller: "AutoclavesCtrl"
      })
      .state('bio-safety-cabinets', {
        url: "/bio-safety-cabinets",
        templateUrl: "views/bio-safety-cabinets.html",
        controller: "BioSafetyCabinetsCtrl"
      })
      .state('chem-fume-hoods', {
        url: "/chem-fume-hoods",
        templateUrl: "views/chem-fume-hoods.html",
        controller: "ChemFumeHoodsCtrl"
      })
      .state('lasers', {
        url: "/lasers",
        templateUrl: "views/lasers.html",
        controller: "LasersCtrl"
      })
      .state('x-ray', {
        url: "/x-ray",
        templateUrl: "views/x-ray.html",
        controller: "X-RayCtrl"
      })
      .state('testpage', {
        url: '/testpage',
        templateUrl: 'views/testpage.php',
        controller: "TestCtrl"
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
            console.log($rootScope);
          });

  });;
