var myLab = angular.module('myLab', [
  'ui.router',
  'ui.bootstrap',
  'shoppinpal.mobile-menu',
  'convenienceMethodWithRoleBasedModule',
  'once',
  'cgBusy',
  'angular.filter',
  'text-mask',
  'rsms-AuthDirectives',
  'rsms-UserHub'])
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

        factory.getUserById = function getUserById(id){
          let url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getUserById&id=" + id;
          return convenienceMethods.getDataAsDeferredPromise(url);
        }

        return factory;
})
.factory('widgetFunctionsFactory', function($q, $modal, $timeout, myLabFactory, userHubFactory, UserCategoryFactory, convenienceMethods){
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
    },

    openAssignUserModal: function(pi, personnelList){
      let assignmentModal = $modal.open({
        templateUrl: 'views/assign-lab-user.html',
        controller: 'AssignLabUserCtrl',
        resolve: { pi: () => pi }
      });

      assignmentModal.result.then(
        assigned => {
          personnelList.push(assigned);
        },
        cancel   => { console.debug("Cancelled assignment", cancel); }
      );
    },

    unassignUserFromSupervisor: async function unassignUserFromSupervisor( user, inactivate, sourcelist ){
      // Set edit flag
      user._editing = true;

      // 1. Confirm unassignment (and inactivation)
      let title = "Confirm Unassignment";
      let message = undefined;
      let note = undefined;

      if( inactivate ){
        title += " and Inactivation";
        message = "Do you want " + user.Name + " to be removed from the PI’s lab personnel list and inactivated in the Research Safety Management System?";
        note = "This user will become unassigned and inactive with a Lab Personnel role";
      }
      else {
        message = "Do you want " + user.Name + " to be removed from the PI's lab personnel list?";
        note = "This user will become unassigned but remain active with a Lab Personnel role.";
      }

      let body = '<h3>' + message + '</h3>'
               + '<div class="spacer"/>'
               + '<p><span>Note:</span>' + note + '</p>';

      try {
        await convenienceMethods.modalConfirm( title, body, 'Confirm', 'Cancel' );

        // 2. Save user, severing link
        let unassignedUser = await userHubFactory.unassignLabUser( user.Key_id, inactivate );

        // 3. Update model
        console.debug("Remove user from source list");
        let idx = sourcelist.indexOf(user);
        if( idx > -1){
          $timeout(() => sourcelist.splice(idx, 1));
        }

        ToastApi.toast(unassignedUser.Name + ' has been Unassigned' + (!unassignedUser.Is_active ? ' and Inactivated' : ''));
      }
      catch(err){}
      finally {
        // Always unset edit flag
        user._editing = undefined;
      }
    },

    /** Edit a user using UserHub tools */
    editUserAsRole: async function editUserAsRole( user, role, supervisor, sourcelist ){
      console.debug("Edit", user, role);

      if( !role ) throw "Missing role - unable to categorize user";

      let roleName = role;
      let _user = undefined;
      if( user ){
        user._editing = true;
        let editing_pi = user.Class == 'PrincipalInvestigator';

        // Lookup user details
        let uid = user.Key_id;

        // If we're editing the PI, lookup the PI's user
        if( editing_pi ){
          uid = user.User.Key_id;
        }

        _user = await myLabFactory.getUserById( uid );
        console.debug("Retrieved user details", _user);

        if( !editing_pi ){
          // If we're editing a personnel/contact, ensure that the PI is referenced
          if( !_user.Supervisor ){
            _user.Supervisor = {
                Class: supervisor.Class,
                Key_id: supervisor.Key_id,
                Name: supervisor.Name
            };
          }
          else if( _user.Supervisor_id != supervisor.Key_id ){
              console.error("User references other PI...");
          }
        }
      }
      // else we're creating a new user?

      ////////////////////////////////
      // Prep the userhub edit modal

      // Look up category for the incoming role
      let categories = UserCategoryFactory.getCategories();
      let category = categories.find( c => c.roles[0] == roleName );

      // Open the UserHub edit modal, passing in our user reference
      // If there is no user, the modal will initialize it, applying our defaults

      let modalInstance = $modal.open({
          templateUrl: GLOBAL_WEB_ROOT + '/user-hub/scripts/modals/edit-user-modal.html',
          controller: 'EditUserModalCtrl',
          resolve: {
              category: function(){ return category; },
              user: function(){ return _user; },
              newUserDefaults: function(){
                  return {
                      Is_active: true,
                      Supervisor: {
                          Class: supervisor.Class,
                          Key_id: supervisor.Key_id,
                          Name: supervisor.Name
                      },
                      Supervisor_id: supervisor.Key_id
                  };
              }
          }
      });

      // What to do with the saved user?
      modalInstance.result.then(
        saved => {
          // 0. If supervisor is not this PI, then remove
          if( saved.Supervisor_id != supervisor.Key_id ){
            if(sourcelist && Array.isArray(sourcelist) ) {
              console.debug("Remove unassigned user from source list");
              let idx = sourcelist.indexOf(user);
              if( idx > -1 ){
                sourcelist.splice(idx, 1);
              }
            }
          }

          // 1. If editing; apply any changed user details
          if( _user && _user.Key_id ){
            console.debug("Extend existing user");
            angular.extend(user, saved)

            user._editing = undefined;
          }

          // 2. New user; add to source list (if provided)
          else if(sourcelist && Array.isArray(sourcelist) ) {
            console.debug("Add new user to source list");
            sourcelist.push(saved);
          }

          // 3. New user, but no source. nothing to do...
          else {
            console.warn("Nothing to do with saved user");
          }
        },
        err_cancel => {
          // Cancel or Error editing; remove flag
          if( user ) user._editing = undefined;
        }
      );
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
.controller('AssignLabUserCtrl', function AssignLabUserCtrl($scope, $modalInstance, $filter, $timeout, UserCategoryFactory, UserHubAPI, userHubFactory, pi){
  
  /////////////////////
  // Setup

  // TODO: User-selectable category: Personnel or Contact
  let type = Constants.ROLE.NAME.LAB_PERSONNEL;

  let categories = UserCategoryFactory.getCategories();
  let personnel_category = categories.find( c => c.roles[0] == type);

  $scope.pi = pi;
  $scope.selected = {
    user: null,
    isContact: false
  };

  // TODO: Filter users when isContact changes
  $scope.gettingUsers = true;
  UserHubAPI.getAllUsers()
    .then(
      users => {
        console.debug("Filtering users to type of '" + type + "'...");
        let filtered = $filter('categoryFilter')(users, personnel_category);
        console.debug("Users filtered to LabPersonnel", $scope.labPersonnel);

        $timeout(() => {
          $scope.labPersonnel = filtered;
          $scope.modalError="";
          $scope.gettingUsers = false;
        });
      }
    );

  /////////////////////
  // Scope functions
  $scope.checkUserForSave = function checkUserForSave(user) {
    console.debug("Selected user: ", user);

    // Determine if confirmation is required
    // Show a message if we're re-activating or re-assigning a user
    $scope.needsConfirmation = !user.Is_active || user.Supervisor;

    // Confirmation may not be required, but build the confirmation message anyway

    var currentRoleName = userHubFactory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT)
      ? Constants.ROLE.NAME.LAB_CONTACT
      : Constants.ROLE.NAME.LAB_PERSONNEL;

    var supervisor_stmt = user.Supervisor
      ? "is currently assigned to " + user.Supervisor.Name
      : "is an unassigned " + currentRoleName

    var inactive_stmt = user.Is_active ? undefined : "is inactive";
    var question_stmt = "Assign to " + $scope.pi.Name + "?";

    // Construct message
    var changes = [supervisor_stmt, inactive_stmt]
      .filter(s => s)
      .join(' and ') + '.';

    $scope.message = [
      user.Name,
      changes,
      question_stmt
    ].join(' ');

    console.debug($scope.pi, $scope.message);

    return !$scope.needsConfirmation;
  }

  $scope.save = function(user, confirmed){
    if(!confirmed && !checkUserForSave(user)){
        console.warn("Requested User edit requires confirmation");
        return;
    }

    let type = $scope.selected.isContact ? Constants.ROLE.NAME.LAB_CONTACT : Constants.ROLE.NAME.LAB_PERSONNEL;
    console.debug("Assign lab user: ", user.Key_id, $scope.pi.Key_id, type);

    $scope.savingAssignment = true;
    userHubFactory.assignLabUser(user.Key_id, $scope.pi.Key_id, type)
      .then(
        savedUser => {
          $scope.savingAssignment = undefined;
          console.debug("Assigned user: ", savedUser);
          ToastApi.toast("Assigned " + savedUser.Name);

          // Update user in our cache
          angular.extend(user, savedUser);

          $modalInstance.close(user);
        },
        error => {
          $scope.savingAssignment = undefined;
        }
      );
  };

  $scope.cancel = function(){
    $modalInstance.dismiss();
  }
})
;
