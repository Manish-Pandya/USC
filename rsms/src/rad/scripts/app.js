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
    'filtersApp',
    'angular.filter',
    'actionFunctionsModule',
    'radUtilitiesModule',
    'radValidationFunctionsModule',
    'dataSwitchModule', 
    'cgBusy',
    'ui.bootstrap',
    'once',
    'ui.router',
    'ui.tinymce',
    'ngQuickDate',
    'modalPosition',
    'convenienceMethodWithRoleBasedModule',
    //'ngMockE2E'
  ])
  .config(function ($stateProvider, $urlRouterProvider, $qProvider, $provide, $httpProvider, $sceDelegateProvider, dataSwitchFactoryProvider, modelInflatorFactoryProvider) {
    $urlRouterProvider.otherwise("/home");
    $stateProvider
      .state('rad-home', {
        url: "/home",
        templateUrl: "views/rad-center.html",
        controller: "RadHomePageRoutingCtrl"
      })
      .state('radmin', {
        url: "/admin",
        templateUrl: "views/admin/radmin.html",
        controller: "RadminMainCtrl"
      })
      .state('radmin.pi-detail', {
        url: "/pi-detail:pi",
        templateUrl: "views/admin/pi-detail.html",
        controller: "PiDetailCtrl"
      })
      .state('radmin.wipe-tests', {
        url: "/wipe-tests",
        templateUrl: "views/admin/wipe-tests.html",
        controller: "WipeTestController"
      })
      .state('radmin.other-waste', {
          url: "/other-waste",
          templateUrl: "views/admin/other-waste.html",
          controller: "OtherWasteCtrl"
      })
      .state('radmin.disposals', {
        url: "/disposals",
        templateUrl: "views/admin/disposals.html",
        controller: "disposalCtrl"
      })
      .state('radmin.disposals-history', {
        url: "/disposals/history",
        templateUrl: "views/admin/disposals-history.html",
        controller: "DisposalHistoryCtrl"
      })
      .state('radmin.drum-detail', {
        url: "/drum-detail:drumId",
        templateUrl: "views/admin/drum-detail.html",
        controller: "drumDetailCtrl"
      })
      .state('radmin.orders', {
        url: "/packages",
        templateUrl: "views/admin/parcels.html",
        controller: "AllOrdersCtrl"
      })
     .state('radmin.auth-report', {
        url: "/auth-report",
        templateUrl: "views/admin/auth-report.html",
        controller: "AuthReportCtrl"
     })
     .state('radmin.isotope-report', {
        url: "/isotope-report",
        templateUrl: "views/admin/isotope-report.html",
        controller: "IsotopeReportCtrl"
     })
     .state('auth-report-print', {
        url: "/auth-report-print",
        templateUrl: "views/admin/auth-report-print.html",
        controller: "AuthReportCtrl"
      })
      //admin overview for a QuarterlyInventories
      .state('radmin.inventories', {
        url: "/inventories",
        templateUrl: "views/admin/inventories.html",
        controller: "InventoriesCtrl"
      })
      //detail admin view for a single PIQuarterlyInventory
      .state('radmin-quarterly-inventory', {
        url:'/admin/pi-quarterly-inventory:pi_inventory',
        templateUrl: "views/pi/quarterly-inventory.html",
        controller: "InventoriesCtrl"
      })
      .state('radmin.carboys', {
        url: "/carboys",
        templateUrl: "views/admin/carboys.html",
        controller: "CarboysCtrl"
      })
      .state('radmin.conditions', {
            url: "/conditions",
            templateUrl: "views/admin/conditions.html",
            controller: "ConditionsCtrl"
      })
      .state('radmin.zap', {
           url: "/zap",
           templateUrl: "views/admin/zap.html",
           controller: "ZapCtrl"
      })
      .state('radmin.admin-pickups', {
        url: "/pickups",
        templateUrl: "views/admin/pickups.html",
        controller: "AdminPickupCtrl"
      })
      .state('pi-rad-management', {
        url:'/my-lab:pi',
        templateUrl: "views/pi/pi-rad-home.html",
        controller: "PiRadHomeCtrl"
      })
      .state('pi-orders', {
        url:'/my-lab:pi/orders',
        templateUrl: "views/pi/orders.html",
        controller: "OrdersCtrl"
      })
      .state('use-log', {
        url:'/my-lab:pi/use-log',
        templateUrl: "views/pi/use-log.html",
        controller: "UseLogCtrl"
      })
      .state('radmin.isotopes', {
        url: "/isotopes",
        templateUrl: "views/admin/isotopes.html",
        controller: "IsotopeCtrl"
      })
      .state('radmin.transfers', {
        url: "/transfers",
        templateUrl: "views/admin/transfers.html",
        controller: "TransferCtrl"
      })
      .state('parcel-use-log', {
        url:'/my-lab:pi/use-log:parcel',
        templateUrl: "views/pi/parcel-use-log.html",
        controller: "ParcelUseLogCtrl"
      })
      .state('quarterly-inventory', {
        url:'/my-lab:pi/quarterly-inventory',
        templateUrl: "views/pi/quarterly-inventory.html",
        controller: "QuarterlyInventoryCtrl"
       })
      .state('containers', {
        url: '/my-lab:pi/containers',
        templateUrl: "views/pi/containers.html",
        controller: "ContainersCtrl"
      })
      .state('carboys', {
        url:'/my-lab:pi/carboys',
        templateUrl: "views/pi/carboys.html",
        controller: "PiCarboyCtrl"
      })
      .state('pickups', {
        url:'/my-lab:pi/pickups',
        templateUrl: "views/pi/pickups.html",
        controller: "PickupCtrl"
      })
      .state('current-inventories', {
        url: '/my-lab:pi/current-inventories',
        templateUrl: "views/pi/CurrentInventories.html",
        controller: "InventoryViewCtrl"
      })
      .state('lab-wipes', {
        url: '/my-lab:pi/wipe-tests',
        templateUrl: "views/pi/wipe-tests.html",
        controller: "PIWipeTestController"
      })
      .state('pi-auths', {
        url: '/my-lab:pi/authorizations',
        templateUrl: "views/pi/auths.html",
        controller: "AuthCtrl"
       })
      .state('inspection-wipes:inspection', {
        url: "/inspection-wipes:inspection",
        templateUrl: "views/inspection/inspection-wipes.html",
        controller: "InspectionWipeCtrl"
      })



       $provide.decorator('$q', function ($delegate) {
        var defer = $delegate.defer;
        $delegate.defer = function() {
          var deferred = defer();

          deferred.promise.state = deferred.state = 'pending';

          deferred.promise.then(function() {
            deferred.promise.state = deferred.state = 'fulfilled';
          }, function () {
            deferred.promise.state = deferred.state = 'rejected';
          });

          return deferred;
        };
        return $delegate;
      });

  })
  .controller('NavCtrl', function ($rootScope, actionFunctionsFactory, $state, roleBasedFactory) {
      $rootScope.constants = Constants;
    $rootScope.$on('$stateChangeStart ',function(){
      $rootScope.loading = true;
    });
    $rootScope.$on('$stateChangeSuccess',
        function (event, toState, toParams, fromState, fromParams) {
            console.log("on naviGATE",event);
            console.log("    to", toState, toParams);
            console.log("  from", fromState, fromParams);

            $rootScope.state = $state;
            $rootScope.loading = false;
            var viewMap = actionFunctionsFactory.getViewMap($state.current);
            $rootScope.viewLabel = viewMap.Label;
            $rootScope.bannerClass = viewMap.Name;
            $rootScope.dashboardView = viewMap.Dashboard;
            $rootScope.noHead = viewMap.NoHead;
            $rootScope.showPiNav = viewMap.showPiNav;

            var newNavPi = 1;

            if( toParams && toParams.pi ){
              newNavPi = toParams.pi;
            }
            else if( fromParams && fromParams.pi ){
              newNavPi = fromParams.pi;
            }

            if( $rootScope.navPi === undefined || $rootScope.navPi != newNavPi ){
              console.debug("Change (nav) PI from " + $rootScope.navPi + " to " + newNavPi);
              $rootScope.navPi = newNavPi;
            }
        });

      //global authorization getter function used by multiple controllers
    $rootScope.getHighestAuth = function (pi) {
        console.log(pi);
        if (pi.Pi_authorization && pi.Pi_authorization.length) {
            var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                return moment(amendment.Approval_date).valueOf();
            }]);

            return auths[auths.length - 1];
        }
    }

    $rootScope.isAdminUser = function(){
      return roleBasedFactory.getHasPermission([
        $rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN],
        $rootScope.R[Constants.ROLE.NAME.ADMIN]]
      );
    }

    $rootScope.rsmsCenter = function(){
      window.location.href = GLOBAL_WEB_ROOT + 'views/RSMSCenter.php';
    }
  })
  .controller('RadHomePageRoutingCtrl', function($rootScope, $state, $location, roleBasedFactory){
    //noop
  })
  .config(function (ngQuickDateDefaultsProvider) {
    return ngQuickDateDefaultsProvider.set({
      closeButtonHtml: "<i class='icon-cancel-2'></i>",
      buttonIconHtml: "<i class='icon-calendar-2'></i>",
      nextLinkHtml: "<i class='icon-arrow-right'></i>",
      prevLinkHtml: "<i class='icon-arrow-left'></i>",
      parseDateFunction: function (str) {
        return new Date(Date.parse(str));
      }
    });
  });

