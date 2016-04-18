'use strict';

/**
 * @ngdoc overview
 * @name BiosafetyCommittees
 * @description
 * # BiosafetyCommittees
 *
 * Main module of the application.
 */
angular
    .module('BiosafetyCommittees', [
        'modelInflator',
        'genericAPI',
        'applicationControllerModule',
        'dataSwitchModule',
        'cgBusy',
        'ui.bootstrap',
        'once',
        'modalPosition',
        'convenienceMethodWithRoleBasedModule',
        'angular.filter',
        'poptop'
    ])
    .controller('NavCtrl', function ($rootScope, applicationControllerFactory) {});
