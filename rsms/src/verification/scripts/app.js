'use strict';

/**
 * @ngdoc overview
 * @name VerificationApp
 * @description
 * # VerificationApp
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
        abstract: true,
        templateUrl: "views/verification-nav.html",
        controller: "NavCtrl",
    })
    .state('verification.step1', {
        url: "/personnel",
        templateUrl: "views/personnel.html",
        controller: "PersonnelCtrl"
    })
    .state('verification.step2', {
        url: "/contact",
        templateUrl: "views/emergency-phone.html",
        controller: "EmergencyPhoneCtrl"
    })
    .state('verification.step3', {
        url: "/laboratories",
        templateUrl: "views/rooms.html",
        controller: "LocationCtrl"
    })
    .state('verification.step4', {
        url: "/inventory",
        templateUrl: "views/hazards.html"
    })
    .state('verification.step5', {
        url: "/confirmation",
        templateUrl: "views/confirm.html",
        controller: "ConfirmationCtrl"
    })
    //admin stuff
    .state('verificiation-admin', {
        url: "/admin",
        templateUrl: "views/admin.html"
    })

  })
  .controller('NavCtrl', function ($rootScope, $state, applicationControllerFactory) {
    var ac = applicationControllerFactory;
    ac.getVerification(1)
        .then(
            function(){
                $rootScope.greatestAllowedStep = parseInt(ac.getCachedVerification().Step + 1);
                $rootScope.navigate(parseInt(ac.getCachedVerification().Step -1));
                var i = 0
                for(i; i < ac.getCachedVerification().Step; i++){
                    $rootScope.states[i].Done = true;
                }
            }
        )
    
    $rootScope.stepDone = function(step){
        return ac.saveVerification(ac.getCachedVerification(), step)
                    .then(
                        function(){
                            $rootScope.greatestAllowedStep = parseInt(ac.getCachedVerification().Step + 1);
                        }
                    )
    }
    
    $rootScope.greatestAllowedStep = 1;
    $rootScope.states = [
            {
                Name: 'verification.step1',
                Label: 'Verify Personnel',
                Message: 'Please verify the following personnel still work in your lab(s).',
                ConfirmationMessage: 'Please confirm.',
                NavLabel: 'Personnel',
                Step:1
            },
            {
                Name: 'verification.step2',
                Label: 'Verify Emergency Contacts',
                Message: 'Please verify the emergency phone numbers for your lab(s).',
                ConfirmationMessage: 'The above emergency phone numbers are accurate and can be used to contact me in the event of a lab emergency or incident. I have verified this is the best phone number to reach me any time, including nights, weekends or holidays (e.g. cell phone).',
                NavLabel:'Emergency Phone Numbers',
                Step:2
            },
            {
                Name: 'verification.step3',
                Label: 'Verify Lab Locations',
                Message: 'Please verify the following personnel still work in your lab(s).',
                ConfirmationMessage: 'The above list of laboratory rooms accurately includes all locations where my laboratory conducts research experiments (including rooms where my lab personnel use shared equipment or rooms where my lab uses research animals).',
                NavLabel:'Locations',
                Step:3
            },
            {
                Name: 'verification.step4',
                Label: 'Verify Hazard Inventory',
                Message: 'Please verify the following personnel still work in your lab(s).',
                NavLabel:'Inventory',
                Step:4
            },
            {
                Name: 'verification.step5',
                Label: 'Confirmation',
                Message: 'Please confirm any changes...',
                ConfirmationMessage: 'I verify that all information provided is true on penalty of being launch into the sun...',
                NavLabel:'Confirmation',
                Step:5
            }
    ]

    $rootScope.navigate = function(int){
        if(int<0){
            int=0;
        }
        $rootScope.selectedView = $rootScope.states[int];
        $state.go($rootScope.states[int].Name);
    }

  });
