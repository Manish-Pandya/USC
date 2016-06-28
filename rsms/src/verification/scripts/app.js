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
    'filtersApp',
    'userList',
    'locationHub'
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $qProvider, $provide, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
      $urlRouterProvider.otherwise("/personnel");
      $stateProvider
      .state('verification', {
          url: "/vid:id",
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
          templateUrl: "views/hazards.html",
          controller: "HazardVerificationCtrl"
      })
      .state('verification.step5', {
          url: "/confirmation",
          templateUrl: "views/confirm.html",
          controller: "ConfirmationCtrl"
      })
      //admin stuff
      .state('verificiation-admin', {
          url: "/admin:id",
          templateUrl: "views/admin.html",
          controller: "AdminCtrl"
      })
      .state('verificiation-admin-list', {
          url: "/list",
          templateUrl: "views/admin-list.html",
          controller: "AdminListCtrl"
      })

  })
  .controller('NavCtrl', function ($rootScope, $state, applicationControllerFactory, $stateParams, $scope) {
      var ac = applicationControllerFactory;
      function getVerification(id) {
          return ac.getVerification(id)
                  .then(
                      function (verification) {
                          if (!id) return;
                          $rootScope.greatestAllowedStep = parseInt(ac.getCachedVerification().Step + 1);
                          if ($state.$current.name != "verificiation-admin" && $state.$current.name != "verificiation-admin-list") {
                              $rootScope.navigate(parseInt(ac.getCachedVerification().Step));
                          }
                          var i = 0
                          for (i; i < ac.getCachedVerification().Step; i++) {
                              if (typeof $rootScope.states[i].Done != "function") {
                                  $rootScope.states[i].Done = true;
                              } else {
                                  $rootScope.states[i].Done();
                              }
                          }
                      }
                  )
      }
      getVerification($stateParams.id);

      $rootScope.stepDone = function (step) {
          return ac.saveVerification(ac.getCachedVerification(), step)
                      .then(
                          function () {
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
                  Step: 1
              },
              {
                  Name: 'verification.step2',
                  Label: 'Verify Emergency Contacts',
                  Message: 'Please verify the emergency phone numbers for your lab(s).',
                  ConfirmationMessage: 'The above emergency phone numbers are accurate and can be used to contact me in the event of a lab emergency or incident. I have verified this is the best phone number to reach me any time, including nights, weekends or holidays (e.g. cell phone).',
                  NavLabel: 'Emergency Phone Numbers',
                  Step: 2
              },
              {
                  Name: 'verification.step3',
                  Label: 'Verify Lab Locations',
                  Message: 'Please verify the following personnel still work in your lab(s).',
                  ConfirmationMessage: 'The above list of laboratory rooms accurately includes all locations where my laboratory conducts research experiments (including rooms where my lab members use shared equipment or rooms where my lab uses research animals).',
                  NavLabel: 'Locations',
                  Step: 3
              },
              {
                  Name: 'verification.step4',
                  Label: 'Verify Hazard Inventory',
                  Message: 'Please verify the following hazards are present in your lab(s).',
                  ConfirmationMessage: 'I verify that all information about the inventory of lab hazards provided is accurate and complete to the best of my knowledge.',
                  NavLabel: 'Inventory',
                  Step: 4
              },
              {
                  Name: 'verification.step5',
                  Label: 'Confirmation',
                  Message: 'Please confirm any changes...',
                  ConfirmationMessage: 'I verify that all information provided is accurate and complete to the best of my knowledge',
                  NavLabel: 'Confirmation',
                  Step: 5
              }
      ]

      $rootScope.navigate = function (int) {
          if (int < 0) {
              int = 1;
          }
          console.log(int);
          $rootScope.selectedView = $rootScope.states[int-1];
          $state.go($rootScope.states[int-1].Name);
      }

  });
