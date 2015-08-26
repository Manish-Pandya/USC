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
    $urlRouterProvider.otherwise("/personnel");
    $stateProvider
    .state('verification', {
        url: "",
        abstract: true,
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
    $rootScope.greatestAllowedStep = 1;
    $rootScope.states = [
            {
                Name: 'verification.step1',
                Label: 'Verify Personnel',
                Message: 'Please verify the following personnel still work in your lab(s).',
                ConfirmationMessage: 'Please confirm.',
                NavLabel: 'Personnel',
                Dashboard:false,
                Step:1
            },
            {
                Name: 'verification.step2',
                Label: 'Verify Emergency Contacts',
                Message: 'Please verify the emergency phone numbers for your lab(s).',
                NavLabel:'Emergency Phone Numbers',
                Dashboard:false,
                Step:2
            },
            {
                Name: 'verification.step3',
                Label: 'Verify Lab Locations',
                Message: 'Please verify the following personnel still work in your lab(s).',
                Dashboard:false,
                NavLabel:'Locations',
                Step:3
            },
            {
                Name: 'verification.step4',
                Label: 'Verify Hazard Inventory',
                Message: 'Please verify the following personnel still work in your lab(s).',
                Dashboard:false,
                NavLabel:'Inventory',
                Step:4
            },
            {
                Name: 'verification.step5',
                Label: 'Confirmation',
                Message: 'Please verify the following personnel still work in your lab(s).',
                Dashboard:false,
                NavLabel:'Confirmation',
                Step:5
            }
    ]

    $rootScope.navigate = function(int){
        $rootScope.selectedView = $rootScope.states[int];
        $state.go($rootScope.states[int].Name);
    }

    //get the right state on page load
    var i = $rootScope.states.length;
    while(i--){
        if($rootScope.states[i].Name == $state.current.name){
            $rootScope.navigate(i);
            return;
        }
    }

    $rootScope.setStepDone = function(step){
        if(!$rootScope.greatestAllowedStep || ( step.Done && ( step.Step >= $rootScope.greatestAllowedStep ) ) ){
            $rootScope.greatestAllowedStep = step.Step + 1;
        }
    }

  });
