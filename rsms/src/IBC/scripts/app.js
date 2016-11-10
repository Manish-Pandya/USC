'use strict';

/**
 * @ngdoc overview
 * @name IBC
 * @description
 * # IBC
 *
 * Main module of the application.
 */
angular
    .module('ng-IBC', [
        'cgBusy',
        'ui.bootstrap',
        'once',
        'modalPosition',
        'convenienceMethodWithRoleBasedModule',
        'angular.filter',
        'ui.tinymce',
        'ui.router',

    ])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
        $urlRouterProvider.otherwise("/home");
        $stateProvider
          .state('home', {
              url: "/home",
              templateUrl: "views/home.html",
              controller: "IBCCtrl"
          })
          .state('detail', {
              url: "/detail:id/",
              templateUrl: "views/detail.html",
              controller: "IBCDetailCtrl"
          })
          .state('emails', {
              url: "/emails",
              templateUrl: "views/emails.html",
              controller: "IBCEmailCtrl"
          })
    })
    .controller('NavCtrl', function ($rootScope) { });