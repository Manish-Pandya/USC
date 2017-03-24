//'use strict';
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
        .state('ibc', {
        abstract: true,
        url: '',
        template: '<ui-view/>'
    })
        .state('ibc.home', {
        url: "/home",
        templateUrl: "views/home.html",
        controller: "IBCCtrl"
    })
        .state('ibc.assign-protocols-for-review', {
        url: "/assign-protocols-for-review",
        templateUrl: "views/assign-protocols-for-review.html",
        controller: "IBCAssignCtrl"
    })
        .state('ibc.detail', {
        url: "/detail:id/",
        templateUrl: "views/detail.html",
        controller: "IBCDetailCtrl"
    })
        .state('ibc.emails', {
        url: "/emails",
        templateUrl: "views/emails.html",
        controller: "IBCEmailCtrl"
    });
})
    .controller('AppCtrl', function ($rootScope, $q) {
    //expose lodash to views
    $rootScope._ = _;
    $rootScope.DataStoreManager = DataStoreManager;
    $rootScope.constants = Constants;
    //register classes with app
    console.log("approved classNames:", InstanceFactory.getClassNames(ibc));
    // method to async fetch current roles
    $rootScope.getCurrentRoles = function () {
        if (!DataStoreManager.CurrentRoles) {
            return $q.all([XHR.GET("getCurrentRoles").then(function (roles) {
                    DataStoreManager.CurrentRoles = roles;
                    return roles;
                })]);
        }
        else {
            return $q.all([new Promise(function (resolve, reject) {
                    resolve(DataStoreManager.CurrentRoles);
                }).then(function () {
                    return DataStoreManager.CurrentRoles;
                })]);
        }
    };
    $rootScope.loadQuestionsChain = function (sectionId, revisionId) {
        return $q.all([DataStoreManager.getById("IBCSection", sectionId, new ViewModelInstance(), true)])
            .then(function (section) {
            console.log(DataStoreManager._actualModel);
            return section;
        });
    };
    $rootScope.saveReponses = function (responses, revision, thing) {
        return $q.all([$rootScope.save(responses)]).then(function (returnedResponses) {
            revision.getResponsesMapped();
            return revision;
        });
    };
    $rootScope.save = function (copy) {
        return $rootScope.saving = $q.all([DataStoreManager.save(copy)]).then(function (responses) {
            console.log(DataStoreManager._actualModel);
            return responses;
        });
    };
});
