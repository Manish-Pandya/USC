var myLab = angular.module('myLab', [
  'ui.bootstrap',
  'shoppinpal.mobile-menu',
  'convenienceMethodWithRoleBasedModule',
  'once',
  'cgBusy',
  'angular.filter',
  'text-mask'])
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
  factory.actionChain = null;

  factory.addAction = function(parentWidget, actionWidget){
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

        factory.getMyLabWidgets = function(){
          var deferred = $q.defer();

          if(factory.MyLabWidgets != null){
            deferred.resolve( factory.MyLabWidgets );
            return deferred.promise;
          }

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getMyLabWidgets";
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
});

function myLabController($scope, $rootScope, convenienceMethods, myLabFactory, widgetFunctionsFactory) {
    var mlf = myLabFactory
    $scope.mlf = mlf;

    var getWidgets = function(){
      return  mlf.getMyLabWidgets()
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
    $scope.inspectionPromise = getWidgets();
}
