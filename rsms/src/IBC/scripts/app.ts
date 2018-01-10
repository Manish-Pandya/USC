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
        'ngQuickDate'
    ])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
        $urlRouterProvider.otherwise("/my-protocols1");
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
              url: "/detail/:id",
              templateUrl: "views/detail.html",
              controller: "IBCDetailCtrl"
            })
            .state('ibc.my-protocols', {
                url: "/my-protocols/:id",
                templateUrl: "views/my-protocols.html",
                controller: "IBCMyProtocolsCtrl"
            })
            .state('ibc.email-management', {
                url: "/email-management",
              templateUrl: "views/email-management.html",
              controller: "IBCEmailMgmtCtrl"
            })
            .state('ibc.meetings', {
                url: "/meetings",
                templateUrl: "views/meetings.html",
                controller: "IBCMeetingsCtrl"
            })
            .state('ibc.test', {
                url: "/test/:id",
                templateUrl: "views/test.html",
                controller: "TestCtrl"
            })
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        //expose lodash to views
        $rootScope._ = _;
        $rootScope.DataStoreManager = DataStoreManager;
        $rootScope.constants = Constants;

        $rootScope.tinymceOptions = {
            plugins: ['link lists', 'autoresize'],
            contextmenu_never_use_native: true,
            toolbar: 'bold | italic | underline | link | lists | bullist | numlist',
            menubar: false,
            elementpath: false,
            content_style: "p,ul li, ol li {font-size:14px}"
        };

        //register classes with app
        console.log("approved classNames:", InstanceFactory.getClassNames(ibc));
        // method to async fetch current roles
        $rootScope.getCurrentRoles = function () {
            if (!DataStoreManager.CurrentRoles) {
                return $q.all(
                    [XHR.GET("getCurrentRoles").then((roles) => {
                        DataStoreManager.CurrentRoles = roles;
                        console.log("Current Roles:", roles);
                        return roles;
                    })]
                )
            } else {
                return $q.all(
                    [new Promise(function (resolve, reject) {
                        resolve(DataStoreManager.CurrentRoles);
                    }).then(() => {
                        return DataStoreManager.CurrentRoles;
                    })]
                )
            }
        }

        $rootScope.loadQuestionsChain = function (sectionId: any, revisionId: any): Promise<any> | void {
            return $q.all([DataStoreManager.getById("IBCSection", sectionId, new ViewModelHolder(), true)])
                .then(
                function (section) {
                    console.log(DataStoreManager._actualModel);
                    return section;
                })
        }

        $rootScope.saveReponses = function (responses: ibc.IBCResponse[], revision: ibc.IBCProtocolRevision): Promise<any> {
            return $q.all([$rootScope.save(responses)]).then((returnedResponses: ibc.IBCResponse[]) => {
                revision.responsesMapped = {}; // clear previous mapped responses
                revision.getResponsesMapped();
                return revision;
            })
        }

        $rootScope.returnForRevision = (copy: ibc.IBCProtocolRevision): Promise<any> | any => {
            copy["Date_returned"] = convenienceMethods.setMysqlTime(new Date());
            console.log(copy, convenienceMethods);
            return $rootScope.saving = $rootScope.save(copy).then(() => { $state.go("ibc.home")});
        }

        $rootScope.submitProtocol = (copy: ibc.IBCProtocolRevision): Promise<any> | any => {
            copy["Date_submitted"] = convenienceMethods.setMysqlTime(new Date());
            console.log(copy, convenienceMethods);
            return $rootScope.saving = $rootScope.save(copy).then(() => {
                alert("Thank you for submitting.")
            });
        }

        $rootScope.save = function (copy): Promise<any> {
            return $rootScope.saving = $q.all([DataStoreManager.save(copy)]).then(
                function (someReturn) {
                    console.log("save result:", someReturn);
                    console.log(DataStoreManager._actualModel);
                    return someReturn;
                });
        }

        // returns true if any of passed roles is in CurrentRoles
        $rootScope.hasRole = function (...roles): boolean {
            return DataStoreManager.CurrentRoles.some((value) => {
                for (var n = 0; n < roles.length; n++) {
                    if (value == roles[n]) {
                        return true;
                    }
                }
                return false;
            });
        }

    });