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
    $urlRouterProvider.otherwise("");
    $stateProvider
    .state('verification', {
        url: "",
        templateUrl: "views/verification-nav.html",
        controller: "NavCtrl"
    })
    .state('verification.step1', {
        url: "/personnel",
        templateUrl: "views/personnel.html",
        controller: "PersonnelCtrl"
    })
    .state('verification.step2', {
        url: "/contact",
        templateUrl: "views/emergency-phone.html"
    })
    .state('verification.step3', {
        url: "/laboratories",
        templateUrl: "views/rooms.html"
    })
    .state('verification.step4', {
        url: "/inventory",
        templateUrl: "views/hazards.html"
    })
    .state('verification.step5', {
        url: "/confirmation",
        templateUrl: "views/confirm.html"
    })
    //admin stuff
    .state('verificiation-admin', {
        url: "/admin",
        templateUrl: "views/admin.html"
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
            $rootScope.viewName = viewMap.Name;
            $rootScope.viewLabel = viewMap.Label;
            $rootScope.bannerClass = viewMap.Name;
            $rootScope.dashboardView = viewMap.Dashboard;
            $rootScope.noHead = viewMap.NoHead;
            $rootScope.step = parseInt(viewMap.Step);
        }
    );

    function getViewMap(current){
        var viewMap = [
            {
                Name: 'verification',
                Label: '',
                Dashboard:false,
                Step: false
            },
            {
                Name: 'verification.step1',
                Label: 'Verify Personnel',
                Dashboard:false,
                Step:1
            },
            {
                Name: 'verification.step2',
                Label: 'Verify e cons',
                Dashboard:false,
                Step:2
            },
            {
                Name: 'verification.step3',
                Label: 'Verify Personnel',
                Dashboard:false,
                Step:3
            },
            {
                Name: 'verification.step4',
                Label: 'Verify Personnel',
                Dashboard:false,
                Step:4
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
