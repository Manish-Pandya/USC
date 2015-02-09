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
  .config(function ($stateProvider, $urlRouterProvider,$qProvider, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
    $urlRouterProvider.otherwise("/home");
    console.log(modelInflatorFactoryProvider.$get());
    $stateProvider
      .state('rad-home', {
        url: "/home",
        templateUrl: "rad-center.html"
      })
      .state('radmin', {
        url: "/admin",
        templateUrl: "admin/radmin.html",
        controller: "RadminMainCtrl",
        resolve:{
          pis: function($http){
            return $http({method: 'GET', url: 'http://erasmus.graysail.com/Erasmus/src/ajaxaction.php?action=getAllPIs'})
               .then (function (pis) {
                  dataStoreManager.store(modelInflatorFactoryProvider.$get().instateAllObjectsFromJson(pis.data));
                  return dataStoreManager.get('PrincipalInvestigator');
               });
          }
        }
      })
      .state('radmin.pi-detail', {
        url: "/pi-detail:pi",
        templateUrl: "admin/pi-detail.html",
        controller: "PiDetailCtrl"
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
          });
   
  });;
