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
  .module('VerificationApp', [
    'ngRoute',
    'modelInflator',
    'genericAPI',
    'applicationControllerModule',
    'dataSwitchModule',
    'cgBusy',
    'ui.bootstrap',
    'once',
    'ui.router',
    'modalPosition',
    'convenienceMethodWithRoleBasedModule',
    'filtersApp'
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $qProvider, $provide, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
    $urlRouterProvider.otherwise("/home");
    $stateProvider
      .state('verification-home', {
        url: "/home",
        templateUrl: "views/verification-home.html"
      })

  })
  .controller('NavCtrl', function ($rootScope, $state) {
    $rootScope.$on('$stateChangeStart ',function(){
      $rootScope.loading = true;
    });
    $rootScope.$on('$stateChangeSuccess',
        function(event, toState, toParams, fromState, fromParams){
            $rootScope.loading = false;
            var viewMap = getViewMap($state.current);
            $rootScope.viewLabel = viewMap.Label;
            $rootScope.bannerClass = viewMap.Name;
            $rootScope.dashboardView = viewMap.Dashboard;
            $rootScope.noHead = viewMap.NoHead;
            console.log($rootScope);
        }
    );

    function getViewMap(current){
        var viewMap = [
            {
                Name: 'rad-home',
                Label: 'Radiation Center',
                Dashboard:false
            }
        ]

        var i = viewMap.length;
        while(i--){
            if(current.name == viewMap[i].Name){
                return viewMap[i];
            }
        }
    }

    function setSelectedView(view){
        $rootScope.selectedView = view;
    }

  });
