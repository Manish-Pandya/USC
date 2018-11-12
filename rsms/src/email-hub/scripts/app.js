//'use strict';
/**
 * @ngdoc overview
 * @name EmailHub
 * @description
 * # EmailHub
 */
angular
    .module('ng-EmailHub', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'convenienceMethodWithRoleBasedModule',
    'angular.filter',
    'ui.router',
    'ui.tinymce'
])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
        console.debug("Configure ng-EmailHub");

        $urlRouterProvider.otherwise("/");

        $stateProvider
            .state('email', {
                url: '/',
                templateUrl: "views/home.html",
                controller: 'EmailHubHomeCtrl'
            })

            .state('templates', {
                url: '/templates',
                templateUrl: "views/templates.html",
                controller: 'EmailHubTemplateCtrl'
            })

            .state('queue', {
                url: '/queue',
                templateUrl: "views/queue.html",
                controller: 'EmailHubQueueCtrl'
            });
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        console.debug("ng-EmailHub running");

        // Expose Constants to views
        $rootScope.constants = Constants;

        // Initialize tinymce options
        $rootScope.tinymceOptions = {
            branding: false,
            plugins: ['link lists', 'autoresize', 'contextmenu'],
            contextmenu_never_use_native: true,
            toolbar: 'bold | italic | underline | link | lists | bullist | numlist',
            menubar: false,
            elementpath: false,
            content_style: "p,ul li, ol li {font-size:14px}"
        };

        $rootScope.getNavLinks = function(){
            var links = [
                {
                    text: 'Home',
                    expression: 'email()',
                    name: 'email',
                    icon: 'icon-email'
                },
                {
                    text: 'Templates',
                    expression: 'templates()',
                    name: 'templates',
                    icon: 'icon-code'
                },
                {
                    text: 'Queue',
                    expression: 'queue()',
                    name: 'queue',
                    icon: 'icon-clock-2'
                }
            ];

            // Flag the current state so we can disable its link(s)
            links.forEach(link => link.active = link.name == $state.current.name);

            $rootScope.navLinks = links;
            return $rootScope.navLinks;
        }

        $rootScope.$on('$stateChangeSuccess',
            function (event, toState, toParams, fromState, fromParams) {

                // Build nav links
                $rootScope.moduleNavLinks = $rootScope.getNavLinks();
            }
        );
    });
