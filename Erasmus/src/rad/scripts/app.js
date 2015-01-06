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
  .module('00RsmsAngularOrmApp', [
    'ngRoute',
    'modelInflator',
    'genericAPI',
    'actionFunctionsModule',
    'cgBusy',
    'ui.bootstrap',
    'once'
    //'ngMockE2E'
  ])
  .config(function ($routeProvider,$httpProvider,$sceDelegateProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/users.html',
        controller: 'UserCtrl'
      })
      .when('/about', {
        templateUrl: 'views/about.html',
        controller: 'AboutCtrl'
      })
      .when('/users', {
        templateUrl: 'views/users.html',
        controller: 'UserCtrl'
      })      
      .when('/hazardhub', {
        templateUrl: 'views/hazardhub.html',
        controller: 'HazardHubCtrl'
      })
      .when('/hazardinventory', {
        templateUrl: 'views/hazardinventory.html',
        controller: 'HazardInventoryCtrl'
      })
      .otherwise({
        redirectTo: '/'
      }); 

      $httpProvider.interceptors.push(function( $q, $rootScope ) {
          return {
              'request': function(config) {
                  $rootScope.$broadcast('loading-started');
                  if(config.method=="POST"){
                    console.log(config);

                  }
                  return config || $q.then(config);
                  
              },
              'response': function(response) {
                  //console.log(response)
                  $rootScope.$broadcast('loading-complete');
                  return response || $q.then(response);
              }
          };
      });

      $sceDelegateProvider.resourceUrlWhitelist([
        // Allow same origin resource loads.
        'self',
        // Allow loading from our assets domain.  Notice the difference between * and **.
        'http://srv*.assets.example.com/**'
      ]);

  })/*
  .value('cgBusyDefaults',{
    backdrop: false,
    templateUrl: '/bower_components/angular-busy/dist/custom-template.html',
    delay: 300,
    minDuration: 700
  });
  .run(function($httpBackend) {
    var phones = [{name: 'phone1'}, {name: 'phone2'}];

    
    // adds a new phone to the phones array
    $httpBackend.whenPOST('/phones').respond(function(method, url, data) {
      var phone = angular.fromJson(data);
      phones.push(phone);
      return [200, phone, {}];
    });

    $httpBackend.whenGET(/^\/views\//).passThrough();
    $httpBackend.whenGET(/^views\//).passThrough();
    $httpBackend.whenGET(/^\/images\//).passThrough();
    $httpBackend.whenGET(/^images\//).passThrough();
    $httpBackend.whenGET("http://erasmus.graysail.com/Erasmus/src/ajaxaction.php").passThrough();

    //...
  })
  .directive("loading", function(dataStoreManagerFactory) {
      return {
          restrict : "C",
          template: "<div class=\"modal show in\"><div class=\"modal-dialog\"><div class=\"modal-content\"><div class=\"alert alert-info\"><h1>Please Wait</h1><img src=\"../images/loading.gif\"></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->",
          link : function(scope, element, attrs, $transclude) {

              scope.$on("loading-started", function(e) {
                  console.log('started');
                  element.show();
              });

              scope.$on("loading-complete", function(e) {
                  console.log('finished')
                  element.hide();
              });

          }
      };
  });*/
