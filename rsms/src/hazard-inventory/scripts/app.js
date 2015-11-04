'use strict';

/**
 * @ngdoc overview
 * @name HazardInventory
 * @description
 * # HazardInventory
 *
 * Main module of the application.
 */
angular
    .module('HazardInventory', [
        'modelInflator',
        'genericAPI',
        'applicationControllerModule',
        'dataSwitchModule',
        'cgBusy',
        'ui.bootstrap',
        'once',
        'modalPosition',
        'convenienceMethodWithRoleBasedModule'
    ])
    .controller('NavCtrl', function ($rootScope, applicationControllerFactory) {});
