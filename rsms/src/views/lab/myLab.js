var myLab = angular.module('myLab', [
  'ui.router',
  'ui.bootstrap',
  'shoppinpal.mobile-menu',
  'convenienceMethodWithRoleBasedModule',
  'once',
  'cgBusy',
  'angular.filter',
  'text-mask',
  'rsms-AuthDirectives'])
  .config(function($stateProvider, $urlRouterProvider, $httpProvider){
    $urlRouterProvider.otherwise(function(){
        return "/lab";
    });

    $stateProvider
      .state('mylab', {
        abstract: true,
        url: '',
        template: '<ui-view/>'
      })

      .state('mylab.current-lab', {
        url: '/lab',
        templateUrl: 'views/user-lab.html',
        controller: 'MyLabCtrl'
      })

      .state('mylab.browser', {
        url: '/browse/:id?',
        templateUrl: 'views/lab-browser.html',
        controller: 'BrowseLabsCtrl'
      })

      .state('mylab.browser.user-lab', {
        url: '/lab',
        templateUrl: 'views/user-lab.html',
        controller: 'MyLabCtrl'
      });
  })
.filter('openInspections', function () {
  return function (inspections) {
        if(!inspections)return;
        var i = inspections.length;
        var matches = [];
        while(i--){
            if(!inspections[i].Cap_submitted_date && !inspections[i].Date_closed)matches.push(inspections[i]);
        }
        return matches;
  };
})
.filter('closedInspections', function () {
  return function (inspections) {
        if(!inspections)return;
        var i = inspections.length;
        var matches = [];
        while(i--){
            if(inspections[i].Cap_submitted_date || inspections[i].Date_closed)matches.push(inspections[i]);
        }
        return matches;
  };
})
.filter('recentEquipmentInspections', function(){
  // Get current year from client
  var current_year = new Date().getFullYear();

  // Define EquipmentInspection date fields to check
  var eq_inspection_date_fields = ['Certification_date', 'Due_date', 'Fail_date'];

  /**
   * Retrieves all inspections dated for this year or earlier
   */
  return function(equipmentInspections){
    if(!equipmentInspections) return;

    return equipmentInspections.filter( i => {
        return eq_inspection_date_fields.find(f => {
            return i[f] && new Date(i[f]).getFullYear() <= current_year;
        });
    });
  };
})
.filter('hasReports', function(){
  // EquipmentInspection fields which define file paths
  var eq_inspection_report_fields = ['Report_path', 'Quote_path', 'Decon_path'];

  /**
   * Retrieves all inspections which include report data
   */
  return function(inspections){
    if(!inspections) return inspections;

    // Return any inspection which has any report field populated
    return inspections.filter(i => eq_inspection_report_fields.find(f => i[f]));
  };
})
.filter('cleanTypeEquipment', function(){
  function isCleanType(q){
    return q.Type.toUpperCase().includes('CLEAN');
  }

  return function( equipmentObjOrArray ){
    if( !equipmentObjOrArray ){
      return equipmentObjOrArray;
    }

    if( Array.isArray(equipmentObjOrArray) ){
      return equipmentObjOrArray.filter(isCleanType);
    }
    else {
      return isCleanType(equipmentObjOrArray);
    }

  };
})
.controller('ActionWidgetModalCtrl', function($scope, $modalInstance, widget, widget_functions){

  $scope.widget = widget;
  $scope.widget_functions = widget_functions;

  $scope.closeModal = function(data){
    $modalInstance.close(data);
  }

})
.factory('widgetModalActionFactory', function($q, $modal, widgetFunctionsFactory){

  var factory = {};
  factory._enabled = true;
  factory.actionChain = null;

  /**
   * Enable/Disable the modal action factory
   * @param {boolean} isEnabled 
   */
  factory.enable = function(isEnabled){
    this._enabled = isEnabled == true;
  };

  /**
   * Display an action modal
   * @param {*} parentWidget 
   * @param {*} actionWidget 
   */
  factory.addAction = function(parentWidget, actionWidget){
    if( !this._enabled ){
      console.debug("Modal action factory is disabled; omitting action");
      return;
    }

    if( !factory.actionChain ){
      var deferred = $q.defer();
      deferred.resolve();
      factory.actionChain = deferred.promise;
    }

    return factory.actionChain.then(function(){
      var instance = $modal.open({
        templateUrl: "widgets/action-widget-modal.html",
        controller: 'ActionWidgetModalCtrl',
        windowClass: 'widget-modal',
        resolve: {
          widget_functions: function() { return widgetFunctionsFactory; },
          widget: function () { return actionWidget; }
        }
      });

      return instance.result;
    });

  };

  return factory;
})
.factory('myLabFactory', function(convenienceMethods,$q,$rootScope){

        var factory = {};

        factory.archivedInspections = [];
        factory.user;
        factory.pi;

        factory.getMyLabWidgets = function(id){
          var deferred = $q.defer();

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getMyLabWidgets";
          if( id ){
            url += '&pi=' + id;
          }

          convenienceMethods.getDataAsDeferredPromise(url).then(
            function(MyLabWidgets){
              factory.MyLabWidgets = MyLabWidgets;
              deferred.resolve(MyLabWidgets);
            },
            function(MyLabWidgets){
              deferred.reject();
            }
          );
          return deferred.promise
        };

        factory.saveMyProfile = function( profile ){
          var deferred = $q.defer();

          var url = "../../ajaxaction.php?&action=saveMyProfile";
          convenienceMethods.saveDataAndDefer(url, profile).then(
            function(saved){
              deferred.resolve(saved);
            },
            function(error){
              deferred.reject(error);
            }
          );

          return deferred.promise;
        };

        
        factory.getAllPIs = function getAllPIs(){
          let pisWillLoad = $q.defer();

          if( factory.PIs ){
            pisWillLoad.resolve(factory.PIs);
          }
          else {
            var pis_url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getAllPINames";
            convenienceMethods.getDataAsDeferredPromise(pis_url)
              .then(
                pis => {
                  factory.PIs = pis;
                  pisWillLoad.resolve(pis);
                },
                err => pisWillLoad.reject(err)
            );
          }

          return pisWillLoad.promise;
        }

        return factory;
})
.factory('widgetFunctionsFactory', function($q, myLabFactory){
  var widget_functions = {
    getPhoneMaskConfig: function(){
      return {
        mask: ['(', /[1-9]/, /\d/, /\d/, ')', ' ', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/],
        keepCharPositions: true,
        guide: true,
        showMask: false
      };
    },

    getProfilePositionRequiredRole: function(){
      if( GLOBAL_SESSION_ROLES.userRoles.indexOf(Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR) > -1){
        return Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR;
      }
      else if( GLOBAL_SESSION_ROLES.userRoles.indexOf(Constants.ROLE.NAME.LAB_PERSONNEL) > -1){
        return Constants.ROLE.NAME.LAB_PERSONNEL;
      }
    },

    getProfilePositionOptions: function(){
      switch( widget_functions.getProfilePositionRequiredRole() ){
        case Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR:
          return Constants.POSITION.PI;

        case Constants.ROLE.NAME.LAB_PERSONNEL:
          return Constants.POSITION.LAB_PERSONNEL;

        default: return [];
      }
    },

    validateUserProfile: function(profile){
      var validation = {
        valid: true,
        errorFields: {}
      };

      // Validate the phone numbers
      var phones = ['Office_phone', 'Lab_phone', 'Emergency_phone'];
      for( var i = 0; i < phones.length; i++){
        // Skip numbers which aren't provided
        // Skip empty numbers, as users are allowed to empty them
        if( profile[phones[i]] !== undefined && profile[phones[i]] != '' ){
          var digits = profile[phones[i]].trim().replace(/[^0-9.]/g, '');
          if( digits.length < 10 ){
            // loose validation; invalid only if they're too short
            validation.valid = false;
            validation.errorFields[phones[i]] = true;
            validation.error = "Phone Number is too short";
          }
        }
      }

      return validation;
    },

    saveUserProfile: function(profile){
      var profileWillSave = $q.defer();

      myLabFactory.saveMyProfile(profile)
        .then(
          saved => {
            profileWillSave.resolve(saved);
          },
          error => {
            console.error(error);
            profileWillSave.reject(error);
          });

      return profileWillSave.promise;
    },

    inspectionHasHazard: function(inspection, field){
      return inspection.HazardInfo[field] > 0;
    }
  };

  return widget_functions;
})
.controller('MyLabAppCtrl', function MyLabAppCtrl ($rootScope){
  // Populate nav items only for Admins
  if( GLOBAL_SESSION_ROLES.userRoles.includes(Constants.ROLE.NAME.ADMIN) ){
    $rootScope.mylabViews = [
      {
        name: 'My Dashboard',
        route: '/lab'
      },
      {
        name: 'Browse Labs',
        route: '/browse/'
      }
    ]; 
  }
})
.controller('BrowseLabsCtrl', function BrowseLabsCtrl($scope, $state, $stateParams, myLabFactory, widgetModalActionFactory){
  console.debug("Lab Browser");
  let id = undefined;
  if( $stateParams ){
    id = $stateParams.id;
  }

  // Get lab-list details
  //   This is intended to replace the PI-search from PI Hub
  //   Limit list to PIs

  $scope.onSelectPi = function onSelectPi(pi){
    console.debug("GO TO LAB FOR", pi);
    $scope.pi = pi;
    $state.go('mylab.browser.user-lab', {id: pi.Key_id});
  }

  myLabFactory.getAllPIs()
    .then( (pis) => $scope.PIs = pis )
    .then( () => {
      if( id ){
        $scope.pi = $scope.PIs.find( pi => pi.Key_id == id);
      }
    });

  // Disable the widgetModalActionFactory in the 'lab browser' view
  widgetModalActionFactory.enable(false);
})
.controller('MyLabCtrl', function MyLabCtrl($scope, $stateParams, widgetModalActionFactory, myLabFactory, widgetFunctionsFactory) {
  console.debug("My Lab loading", $stateParams);
  let id = undefined;
  if( $stateParams.id ){
    id = $stateParams.id;
  }
  else {
    widgetModalActionFactory.enable(true);
  }

  var mlf = myLabFactory
  $scope.mlf = mlf;

  $scope.getWidgets = function(id){
    return  mlf.getMyLabWidgets(id)
    .then(
        function(MyLabWidgets){
            $scope.MyLabWidgets = MyLabWidgets;
            if( $scope.MyLabWidgets ){
              $scope.AllAlerts = [];
              $scope.MyLabWidgets.forEach(w => {
                if( w.Alerts && w.Alerts.length ){
                  $w.Alerts.forEach(alert => {
                    $scope.AllAlerts.push({ group: w.Group, message: alert});
                  });
                }
              });
            }
        }
    );
  };

  $scope.widget_functions = widgetFunctionsFactory;

  //init call
  $scope.inspectionPromise = $scope.getWidgets(id);
})
;
