angular
    .module('rsms-UserHub', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'convenienceMethodWithRoleBasedModule',
    'angular.filter',
    'ui.router',
    'ui.mask',
    'rsms-AuthDirectives'
])
.config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
    console.debug("Configure rsms-UserHub");

    $stateProvider
        .state('user-hub', {
            abstract: true,
            url: '',
            template: `<ui-view/>`
        })
        .state('user-hub.users', {
            abstract: true,
            url: '/users',
            template: `
                <h1 ng-if="!Users" style="display:flex; align-items:center;">
                    <span style="padding-right: 10px;">Loading Users</span>
                    <i class="icon-spinnery-dealie spinner large"></i>
                </h1>
                <ui-view ng-if="Users"/>`
        })
        .state('user-hub.users.category', {
            url: '/:category',
            template: `
                <user-hub-category-table users="Users" category="category">
                </user-hub-category-table>
            `,
            controller: function ($rootScope, $scope, $stateParams){
                console.log("user-hub.users.category");
                $scope.category = $rootScope.categories.find( c => c.code == $stateParams.category);
            }
        })
        .state('user-hub.access-requests',{
            url: '/access-requests',
            templateUrl: 'views/user-access-request-table.html',
            controller: function($scope, $timeout, UserHubAPI){
                console.log('access requests ctrl');

                $scope.search = {
                    Status: 'PENDING'
                };
                $scope.config = {
                    sorter: {
                        expr: 'Date_created',
                        asc: false
                    },
                };

                $scope.getDate = function( d ){
                    return new Date(d);
                };

                // Load data
                UserHubAPI.getAllAccessRequests()
                    .then( uacs => {
                        console.log('Loaded requests:', uacs);
                        $timeout(() => {
                            $scope.AccessRequests = uacs;
                        });
                    });
            }
        });
})
.filter('hasRole', function(){
    return function( user, roleName ){
        return user.Roles.map(r => r.Name).includes(roleName);
    }
})
.filter('categoryFilter', function(){
    return function( users, category ){
        if( !users || !category ) return users;

        // Filter to users who have any listed category role
        return users.filter(u => {
            // Ignore/Include users without roles based on category configuration
            if( !u || !u.Roles || !u.Roles.length ){
                return category.config.includeRoleless;
            }

            return u.Roles.filter( user_role => category.roles.includes(user_role.Name)).length > 0;
        });
    }
})
.filter('userCategories', function( $filter ){
    return function( categories, user ){
        return categories.filter( c => {
            return $filter('categoryFilter')([user], c).length == 1;
        });
    }
})
.filter('restrictRolesFilter', function(){
    return function( roles, category ){
        if( !roles || !category ) return roles;

        // If unrestricted, leave roles as-is
        if( !category.config.restrictRoles ) return roles;

        // Otherwise restrict roles to those listed in category
        return roles.filter(r => category.roles.includes(r.Name));
    }
})
.filter('flagCategoryRoles', function(){
    return function( roles, categoryOrCategories ){
        if( !roles || !categoryOrCategories ) return roles;

        // Look at the incoming roles and flag any which are included in the incoming category (or categories)
        let cats = Array.isArray(categoryOrCategories)
            ? categoryOrCategories
            : [categoryOrCategories];

        let referencedRoleNames = cats
            .map( c => c.roles )
            .reduce( (referencedRoleNames, categoryRoleNames) => {
                categoryRoleNames.forEach( r => {
                    if( !referencedRoleNames.includes(r) ){
                        referencedRoleNames.push(r);
                    }
                });

                return referencedRoleNames;
            }, []);

        // Flag category roles
        roles.forEach( r => r._category_role = referencedRoleNames.includes(r.Name) );

        return roles;
    }
})
.filter('incompatibleRoles', function( UserCategoryFactory ){

    let RoleIncompatibilities = UserCategoryFactory.getRoleIncompatibilities();

    /**
     * Find user roles which are incompatible to this one
     */
    return function( roleOrRoles, user ){
        if( !roleOrRoles || !user ) return [];

        let role_names = (Array.isArray(roleOrRoles) ? roleOrRoles : [roleOrRoles]).map(r => r.Name);

        return user.Roles
            // map user role objects to tuples listing their incompatibilities
            .map( r => {
                return { RoleName: r.Name, Incompatibilities: RoleIncompatibilities[r.Name] };
            })
            // Filter down to roles whith list the incoming role(s) as imcompatible
            .filter( r => {
                return r.Incompatibilities
                    && r.Incompatibilities.filter( i => role_names.includes(i) ).length > 0;
            })
            // Map filtered list to the incompatible role name
            .map( r => r.RoleName);
    }
})
.filter('userSearchFilter', function( $filter ){
    let complex_filters = [
        'SupervisorName',
        'BuildingName',
        'RoleName',
        'DepartmentName',
    ];
    return function( users, search ){
        if( !users || !search ) return users;

        /*
            search.Name => 'Name'
            search.Position => 'Position'
            search.SupervisorName => 'Supervisor.Name'
            search.BuildingName => 'PrincipalInvestigator.Building.Name'
            search.RoleName => 'Roles.Name'
            search.DepartmentName => [
                'PrincipalInvestigator.Departments.Name'
                'Primary_department.Name'
            ]
        */

        // Defer to $filter('filter') to handle filtering
        let f = $filter('filter');

        // Simple filters look just at users; nested ones need to look elsewhere
        let simple_searches = Object.keys(search)
            .filter( k => !complex_filters.includes(k) )
            .map(k => { return { key:k, value:search[k] }; })
            .filter(s => s.value)
            .reduce( (obj, s) => {
                obj[s.key] = s.value;
                return obj;
            }, {});

        let results = f(users, simple_searches);

        if( search.SupervisorName ){
            let obj = { Name:search.SupervisorName };
            results = results.filter(u => f([u.Supervisor], obj).length );
        }

        if( search.BuildingName ){
            let obj = { Name: search.BuildingName };
            results = results.filter( u => u.PrincipalInvestigator && f(u.PrincipalInvestigator.Buildings, obj ).length );
        }

        if( search.RoleName ){
            let obj = { Name: search.RoleName };
            results = results.filter( u => u.Roles && f(u.Roles, obj ).length );
        }

        if( search.DepartmentName ){
            // Search User.Primary_department OR User.PrincipalInvestigator.Departments
            let obj = { Name: search.DepartmentName };
            results = results.filter( u => {
                let user_depts = [];
                
                if( u.PrincipalInvestigator )
                    user_depts = u.PrincipalInvestigator.Departments || [];

                if( u.Primary_department )
                    user_depts.push(u.Primary_department);

                return f(user_depts, obj).length;
            });
        }

        return results;
    }
})

////////////////////////////////////
// Legacy Filters, used by PI Hub //
// TODO: Remove/replace these     //
.filter('isNotContact', function( $filter ){
    // users do NOT have lab contact but DO have lab personnel
    return function(users){
      if(!users)return;

      // return personnel users who are not contacts
      return users.filter( u => {
        return !$filter('hasRole')(u, Constants.ROLE.NAME.LAB_CONTACT)
            && $filter('hasRole')(u, Constants.ROLE.NAME.LAB_PERSONNEL);
      });
    }
  })
  .filter('isLabContact', function($filter){
      // users DO have contact role
    return function(users){
      if(!users)return;

      // return contact users
      return users.filter( u => {
        return $filter('hasRole')(u, Constants.ROLE.NAME.LAB_CONTACT);
      });
    }
})
.filter('isLabPersonnel', function($filter){
    // users DO have contact role
  return function(users){
    if(!users)return;

    // return contact users
    return users.filter( u => {
      return $filter('hasRole')(u, Constants.ROLE.NAME.LAB_PERSONNEL);
    });
  }
})
/////////////////////////////////////////
.controller('AppCtrl', function ($rootScope, $scope, $modal, $timeout, UserCategoryFactory, UserHubAPI) {
    console.debug("rsms-UserHub running");

    // Expose Role Requirements
    $rootScope.RoleRequirements = UserCategoryFactory.getRoleRequirements();
    $rootScope.RoleIncompatibilities = UserCategoryFactory.getRoleIncompatibilities();

    // Expose Constants to views
    $rootScope.constants = Constants;

    $rootScope.categories = UserCategoryFactory.getCategories();

    // Eagerly load all users
    UserHubAPI.getAllUsers().then( all_users => {
        $timeout(() => $rootScope.Users = all_users);
    });

    // Init hub view array and clone each category 
    $rootScope.hubNavViews = [];

    $rootScope.categories.forEach(cat => {
        let view = {
            name: cat.name,
            route: '/users/' + cat.code
        }

        $rootScope.hubNavViews.push(view);
    });

    $rootScope.hubNavViews.push({});
    $rootScope.hubNavViews.push({
        name: 'Access Requests',
        route: '/access-requests'
    });

    $scope.openUserLookupModal = function openUserLookupModal(){
        $modal.open({
            templateUrl: 'scripts/modals/userLookupModal.html',
            controller: 'UserLookupModalCtrl'
        });
    }
})
.controller('UserLookupModalCtrl', function( $scope, $state, $modalInstance, UserHubAPI){
    $scope.selection = {};
    UserHubAPI.getAllUsers().then( users => $scope.users = users );

    $scope.goToUserCategory = function goToUserCategory(user, category){
        $state.go('user-hub.users.category', { category: category.code});
        $modalInstance.close(user);
    }

    $scope.cancel = function cancel(){
        $modalInstance.dismiss();
    }
})

.controller('EditUserModalCtrl', function(
    $rootScope, $scope, $modalInstance, $timeout, $q, $filter, UserHubAPI, UserCategoryFactory,
    category, user, newUserDefaults
){

    // Set up scope
    $scope.category = category;
    $scope.categories = UserCategoryFactory.getCategories();
    $scope.RoleRequirements = UserCategoryFactory.getRoleRequirements();
    $scope.RoleIncompatibilities = UserCategoryFactory.getRoleIncompatibilities();

    // Prepare new user
    if( !user ){
        $scope.user = {
            Class: 'User',
            Roles: [],
        };
    }
    else {
        $scope.originalUser = user;
        $scope.user = angular.copy(user);
    }

    // Configure
    $scope.config = {};

    // Configure fields to display
    function configureFields(){

        // Look at the modal's Category as well as user Categories
        let cats = $filter('userCategories')($scope.categories, $scope.user) || [];

        if( !cats.includes($scope.category) ){
            cats.push($scope.category);
        }

        let fields = cats.map(cat => cat.editFields)
            .reduce( (showFields, catFields) => {
                catFields.forEach( f => {
                    if( !showFields.includes(f)){
                        showFields.push(f);
                    }
                });

                return showFields;
            }, []);

        $timeout(() => {
            // Clear out fields
            $scope.config.fields = {};
            fields.forEach( col => $scope.config.fields['show_field_' + col] = true)
        });

        return fields;
    }

    function CategoryDiff( category, diff_val ){
        this.name = category.name;
        this.value = diff_val;
    }

    /**
     * Identify if there are categorical changes and, if so, what categories will be used post-save.
     */
    function diffUserCategories(){
        let diff = [];

        if( $scope.categories && $scope.user && $scope.originalUser && $scope.originalUser.Key_id ){

            // Only care about names..
            let current_role_names = $scope.user.Roles.map(r => r.Name);
            let original_role_names = $scope.originalUser.Roles.map(r => r.Name);

            // Categories are based on roles, so we'll diff the roles before we look at cats
            let roles_added = current_role_names.find( r => !original_role_names.includes(r));
            let roles_removed = original_role_names.find( r => !current_role_names.includes(r));

            if( roles_added || roles_removed ){
                // Roles are different, so categories are different
                let new_categories = $filter('userCategories')($scope.categories, $scope.user);
                let original_categories = $filter('userCategories')($scope.categories, $scope.originalUser);

                // list all 'current' categories, flagging those which are newly-added (i.e. not present in original)
                // Merge this with original categories which were removed, flagging those as well
                // flag is an integer:
                //    -1: removed
                //     0: unchanged
                //     1: added
                diff = new_categories.map( c => {
                    let diff_val = original_categories.includes(c.Name) ? 0 : 1;
                    return new CategoryDiff(c, diff_val);
                });
            }
        }

        $scope.userCategoryDiff = diff;
    }

    // Initial configuration of fields & role diff
    configureFields();
    diffUserCategories();

    // Configure fields to require

    // Initialize state
    $scope.state = {
        allow_edit: $scope.user.Key_id,

        all_roles: null,
        selectedRole: null,

        all_departments: null,
        selectedDepartment: null,

        all_pis: null,

        cache: {}
    };

    /////////////////////
    // Async load data
    $q.all([
        UserHubAPI.getAllPIs(),
        UserHubAPI.getAllRoles(),
        UserHubAPI.getAllDepartments()
    ])
    .then( (data) => {
        let pis = data[0];
        let roles = data[1];
        let depts = data[2];

        $timeout( () => {
            $scope.state.all_pis = pis;
            $scope.state.all_roles = roles;
            $scope.state.all_departments = depts;
        });
    });

    //////////////
    // Build role-related triggers which perform updates when certain roles are present
    function RoleUpdateTrigger( name, fn ){
        this.name = name;
        this.fn = fn;
    }

    let role_triggers = [
        new RoleUpdateTrigger('Init PI', function(newRoles, scope){
            // Includes PI? Create PrincipalInvestigator obj
            // PI users must have a PrincipalInvestigator child
            if( includesNamedItem(newRoles, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR) ){
                // This is a PI; ensure a PI child exists
                if( !scope.user.PrincipalInvestigator ){
                    if( scope.state.cache.PrincipalInvestigator ){
                        console.debug("Copy cached PrincipalInvestigator object");
                        scope.user.PrincipalInvestigator = scope.state.cache.PrincipalInvestigator;
                    }
                    else {
                        console.debug("Initialize PrincipalInvestigator object");
                        scope.user.PrincipalInvestigator = {
                            Class: 'PrincipalInvestigator',
                            Departments: []
                        };

                        // Move Primary_department as first PI Department
                        if( scope.user.Primary_department ){
                            scope.user.PrincipalInvestigator.Departments.push( scope.user.Primary_department );
                            scope.user.Primary_department = null;
                        }
                    }
                }
            }
        }),

        new RoleUpdateTrigger('Tear Down PI', function(newRoles, scope){
            // Excludes PI? Remvoe PrincipalInvestigator obj
            // Non-PI users should not contain PI object
            if( !includesNamedItem(newRoles, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR) ){
                if( scope.user.PrincipalInvestigator ){
                    // cache object in case user adds PI role back
                    scope.state.cache.PrincipalInvestigator = scope.user.PrincipalInvestigator;

                    console.debug("Remove user.PrincipalInvestigator object");
                    scope.user.PrincipalInvestigator = null;
                }
            }
        }),

        new RoleUpdateTrigger('Add Lab Personnel to Lab Contact', function(newRoles, scope){
            // Add Lab Contact? Also add Lab Personnel
            if( includesNamedItem(newRoles, Constants.ROLE.NAME.LAB_CONTACT) ){
                if( !includesNamedItem(newRoles, Constants.ROLE.NAME.LAB_PERSONNEL) ){
                    console.debug("Adding Lab Personnel to Lab Contact user");
                    let personnel_role = scope.state.all_roles.find( r => r.Name == Constants.ROLE.NAME.LAB_PERSONNEL);
                    newRoles.push( personnel_role );
                }
            }
        }),

        new RoleUpdateTrigger('Clear Supervisor from non-Lab Personnel', function(newRoles, scope){
            // Remove Lab Personnel? Clear Supervisor/Supervisor_id
            if( !includesNamedItem(newRoles, Constants.ROLE.NAME.LAB_PERSONNEL) ){
                if( scope.user.Supervisor_id ){
                    console.debug("Clear user supervisor");
                    scope.user.Supervisor = null;
                    scope.user.SupervisorName = null;
                    scope.user.Supervisor_id = null;
                }
            }
        }),

        new RoleUpdateTrigger('Remind user of Lab Contact/Personnel relationship', function(newRoles, scope, oldRoles){
            // If Contact was removed (present in old, not in new) AND Personnel is still present in new, display notice
            let is_personnel = includesNamedItem(oldRoles, Constants.ROLE.NAME.LAB_PERSONNEL);
            let removed_contact = !includesNamedItem(newRoles, Constants.ROLE.NAME.LAB_CONTACT)
                                && includesNamedItem(oldRoles, Constants.ROLE.NAME.LAB_CONTACT);

            scope.state.show_on_remove_contact_notice = is_personnel && removed_contact;
        }),
    ];

    // Watch for changes to user Roles
    $scope.$watchCollection('user.Roles', (newRoles, oldRoles, scope) => {
        console.debug('User.Roles have changed');

        //////////////////////////////////////////
        // Apply any special-case considerations
        role_triggers.forEach( rule => rule.fn(newRoles, scope, oldRoles));

        //////////////////////////////////////////
        // Recalculate and validate field requirements any time roles change
        getUserRoleRequirements(scope.user);
        scope.validateRoleRequirements();

        // Reconfigure fields and category-diff
        configureFields();
        diffUserCategories();
    });

    // Validate role requirements whenever anything changes
    let watch_paths = [
        'user',
        'user.Roles',
        'user.PrincipalInvestigator',
        'user.PrincipalInvestigator.Departments'
    ];

    watch_paths.forEach( expr => {
        $scope.$watchCollection(expr, (newData, oldData, scope) => {
            scope.validateRoleRequirements();
        });
    });

    ////////////////////
    // Utility Functions
    function includesItem( list, keyed_object ){
        return list.find( i => i.Key_id == keyed_object.Key_id );
    }

    function includesNamedItem( list, name ){
        return list.find( i => i.Name == name );
    }

    function validateSubjectValue( subject, operator, property, value ){
        require_value = (v) => {
            // If value is provided, v must equal value
            if( value ) return v == value;

            // Otherwise v must have any value
            return v != null && v != undefined;
        };

        prohibit_value = (v) => {
            // If value is provided, v must not equal value
            if( value ) return v != value;

            // Otherwise v must have an empty value
            return v == null || v == undefined;
        };

        let is_valid;
        switch( operator ){
            case 'required': {
                is_valid = require_value( subject[property] );
                break;
            }

            case 'prohibited':{
                is_valid = prohibit_value( subject[property] );
                break;
            }

            default: {
                // any value or non-value is fine
                is_valid = true;
                break;
            }
        }

        return is_valid;
    }

    function validateRoleRequirement( role_requirement, user ){
        console.debug("Validate ", role_requirement);

        // Match property special-cases
        if( role_requirement.Property == 'Role.Name' ){
            // Look at each Role
            let is_valid = false;
            for( idx in user.Roles ){
                is_valid = validateSubjectValue( user.Roles[idx], role_requirement.Operator, 'Name', role_requirement.Value );
                if( is_valid ){
                    break;
                }
            }

            return is_valid;
        }
        else if( role_requirement.Property == 'Department' ){
            // Department is applied in 2 possible ways -
            //   1. Principal Investigators have a list of Departments at User.PrincipalInvestigator.Departments
            //   2. Non-principal-investigators have a single 'primary' department at User.Primary_department_id
            // Note that both of these possibilites may exist for a given user, depending on how that user's department(s) have been applied

            let dept_ids = [];
            let depts = [];
            if( user.Primary_department ){
                depts.push(user.Primary_department);
                dept_ids.push(user.Primary_department_id);
            }

            if( user.PrincipalInvestigator ){
                depts = depts.concat(user.PrincipalInvestigator.Departments);
                dept_ids = dept_ids.concat( user.PrincipalInvestigator.Departments.map(d => d.Key_id));
            }

            // There is not currently a use-case for requiring specific departments, so this is a binary check
            let is_valid = dept_ids.length > 0;
            return is_valid;
        }

        else {
            // Validate this requirement as a simple path
            let is_valid = validateSubjectValue( user, role_requirement.Operator, role_requirement.Property, role_requirement.Value );
            return is_valid;
        }
      
    }

    function getUserRoleRequirements( user ){
        console.debug("Collect role-based requirements for ", user);

        $scope.role_requirements = user.Roles
            // Collect all requirements for each role
            .map(role => {
                return $scope.RoleRequirements.filter(req => req.RoleName == role.Name);
            })
            // Remove any empty lists
            .filter( reqs => reqs.length > 0 )
            .reduce( (_arr, _reqs) => {
                return _arr.concat( _reqs );
            }, []);

        return $scope.role_requirements;
    }

    ////////////////////
    // Scope Functions
    $scope.validateRoleRequirements = function validateRoleRequirements(){
        // Wrap in timeout to ensure $scope.user will be up-to-date here
        $timeout( () => {
            // If there are requirements, validate current state
            if( $scope.role_requirements.length ){
                $scope.validation = $scope.role_requirements.map(req => {
                    // Check for special-case placeholders for 'friendly' terms
                    let prop = req.Property;
                    if     ( prop == 'Role.Name' ) prop = 'Role';
                    else if( prop == 'Supervisor_id' ) prop = 'Principal Investigator';

                    // Build descriptiong for this rule
                    let desc = prop + (req.Value ? ' of "' + req.Value + '" ' : ' ')
                                + ' is ' + req.Operator
                                + ' for ' + req.RoleName + ' users';

                    // Validate this rule
                    return {
                        valid: validateRoleRequirement(req, $scope.user),
                        field: req.Property,
                        desc: desc
                    };
                });

                if( $scope.validation.find(v => !v.valid) ){
                    return false;
                }
            }
            else {
                console.debug("No role requirements to check");
                $scope.validation = [];
            }

            return true;
        });
    }

    $scope.lookupUserDetails = async function lookupUserDetails(){
        // Ignore if there's no username to look up
        if( !$scope.user.Username ) return;

        // Ignore if we're already looking up
        if( $scope.state.lookup_user ) return;

        // First validate that the username isn't already in-use
        let capname = $scope.user.Username.toUpperCase();
        let all_users = await UserHubAPI.getAllUsers();
        let existing_user = all_users.find( u => u.Username.toUpperCase() == capname );

        if( existing_user ){
            ToastApi.toast('The username ' + $scope.user.Username + ' is already taken by another user in the system.', ToastApi.ToastType.ERROR);
            return;
        }

        // Lookup user details
        try{
            $scope.state.lookup_user = true;
            let details = await UserHubAPI.lookupUserDetails( $scope.user.Username );
            if( !details ){
                ToastApi.toast('No user with that username was found.', ToastApi.ToastType.ERROR);
                return;
            }

            $timeout(() => {
                // Apply new-user Details
                angular.extend( $scope.user, details );

                // Apply parameter user defaults
                if( newUserDefaults ){
                    angular.extend( $scope.user, newUserDefaults );
                }

                // Apply category defaults
                if( $scope.category.config.newUserRoles.length ){
                    $scope.user.Roles = [];
                    $scope.category.config.newUserRoles
                        .map( defaultRoleName => $scope.state.all_roles.find(r => r.Name == defaultRoleName))
                        .forEach( role => $scope.user.Roles.push(role) );
                }

                $scope.state.allow_edit = true;
                $scope.state.lookup_user = undefined;
            });
        }
        catch(error){
            console.error(error);

            $timeout(() => {
                $scope.state.lookup_user = undefined;
            });
        }
    }

    $scope.cancel = function(){
        $modalInstance.dismiss();
    }

    $scope.submit = async function submit(){
        console.log("Save", $scope.user);

        // Validate current category
        if( !$scope.validation ){
            console.warn("Cannot submit; no post-validation data");
            return;
        }
        else if( $scope.validation.find( v => !v.valid )){
            console.warn("Cannot submit due to failed validation");
            return;
        }

        // Save user
        try{
            $scope.saving = true;
            let saved = await UserHubAPI.saveUser( $scope.user );
            ToastApi.toast('Saved ' + saved.Username);
            $scope.saving = false;

            $modalInstance.close( saved );
        }
        catch(error){
            ToastApi.toast('An error occured while saving this user.', ToastApi.ToastType.ERROR);
            console.error(error);
        }
    }

    $scope.onSelectPI = function onSelectPI( pi ){
        if( pi ){
            $scope.user.Supervisor_id = pi.Key_id;
        }
        else {
            $scope.user.Supervisor_id = null;
            $scope.user.Supervisor = null;
        }
    }

    ////////////////////
    // Role management
    $scope.isRoleIncompatible = function isRoleIncompatible(role){
        // Determine if the incoming role is incompatible with any other selected

        let incompatible_role_names = $filter('incompatibleRoles')(role, $scope.user);
        return incompatible_role_names.length > 0;
    }

    /** Can the role be removed from a User in this category? */
    $scope.canRemoveRole = function canRemoveRole(role){
        // Check if other user roles reference this role as required
        let requirement = $scope.role_requirements.find( req => {
            return req.Property == 'Role.Name' && req.Value == role.Name;
        });

        return !requirement;
    }

    $scope.removeRole = function removeRole( role ){
        let idx = $scope.user.Roles.indexOf(role);
        if( idx > -1 ){
            console.debug("Remove role from list", role);
            $timeout(() => $scope.user.Roles.splice(idx, 1));
        }
        else {
            console.debug("Role to remove is not present in list");
        }
    }

    $scope.onSelectRole = function onSelectRole( role ){
        if( !$scope.user.Roles ){
            $scope.user.Roles = [];
        }

        if( !includesItem($scope.user.Roles, role) ){
            console.debug("Push new role", role);
            $timeout(() => $scope.user.Roles.push(role));
        }
        else{
            console.debug("Role is already selected");
        }
    }
    ////////////////////

    ////////////////////
    // Dept Management
    $scope.removeDepartment = function removeDepartment( dept ){
        // Check both 'primary department' and 'Departments' list
        if( $scope.user.Primary_department_id && $scope.user.Primary_department_id == dept.Key_id ){
            console.debug("Remove primary dept", dept);
            $scope.user.Primary_department_id = null;
            $scope.user.Primary_department = null;
            return;
        }

        if( $scope.user.PrincipalInvestigator ){
            let idx = $scope.user.PrincipalInvestigator.Departments.indexOf(dept);
            if( idx > -1 ){
                console.debug("Remove dept from list", dept);
                $timeout(() => $scope.user.PrincipalInvestigator.Departments.splice(idx, 1));
            }
            else {
                console.debug("Dept to remove is not present in list");
            }
        }
    }

    $scope.onSelectDepartment = function onSelectDepartment( dept ){
        // Does this user support sinlge- or multiple-departments?
        // Only PIs support multiple depts
        if( $scope.user.PrincipalInvestigator ){
            console.debug("Add dept to PI dept list");
            if( !$scope.user.PrincipalInvestigator.Departments ){
                $scope.user.PrincipalInvestigator.Departments = [];
            }
    
            if( !includesItem($scope.user.PrincipalInvestigator.Departments, dept) ){
                console.debug("Push new dept", dept);
                $timeout(() => $scope.user.PrincipalInvestigator.Departments.push(dept));
            }
            else{
                console.debug("Dept is already selected");
            }
        }
        else {
            console.debug("Set user primary dept");
            $scope.user.Primary_department = dept;
            $scope.user.Primary_department_id = dept.Key_id;
        }

        $timeout( () => $scope.state.selectedDepartment = null );
    }
})

// Utility functions shared by other modules
.factory('userHubFactory', function( UserHubAPI ){
    let factory = {};

    factory.hasRole = function( user, roleName ){
        let match = user.Roles.find( r => r.Name.toLowerCase() == roleName.toLowerCase());
        return match ? true : false;
    };

    factory.setModalData = function( data ) { this.modalData = data; };
    factory.getModalData = function() { return this.modalData; };

    factory.assignLabUser = (userId, piId, roleName) => UserHubAPI.assignLabUser(userId, piId, roleName);
    factory.unassignLabUser =  (userId, inactive) => UserHubAPI.unassignLabUser(userId, inactive);

    return factory;
})

.factory('UserCategoryFactory', function(){
    let COL_CONTACT_ICONS = 'contact_icons';
    let COL_USERNAME = 'username';
    let COL_LAST_NAME = 'last_name';
    let COL_FIRST_NAME = 'first_name';
    let COL_EMAIL = 'email';
    let COL_PRINCIPAL_INVESTIGATOR = 'principal_investigator';
    let COL_POSITION = 'position';
    let COL_LAB_PHONE = 'lab_phone';
    let COL_OFFICE_PHONE = 'office_phone';
    let COL_EMERGENCY_PHONE = 'emergency_phone';
    let COL_ROLES = 'roles';
    let COL_DEPARTMENT = 'department';
    let COL_BUILDING = 'building';

    let ROLE = Constants.ROLE.NAME;

    // TODO: Add configuration for user-editing:
    /*
        ** Added config.newUserRoles to specify roles to be added to new users
        ** Added config.restrictRoles to specify that a category's Roles should be filtered to list only pertinent ones
        Should category-roles be allowed to be removed?
    */
    function UserHubCategory( name, code, roles, columns, editFields, config ){
        this.name = name;
        this.code = code;
        this.roles = roles;
        this.columns = columns;
        this.editFields = editFields;
        this.config = config;
    }

    let factory = {
        getRoleRequirements: function(){
            if( RoleRequirements ){
                return RoleRequirements;
            }
            else return [];
        },
        getRoleIncompatibilities: function(){
            let role_requirements = this.getRoleRequirements();

            //////////////////////
            // Compare requirements to determine compatibilities
            // A role is incompatible with another if both have conflicting rules for the same property
            let requirementsPerProperty = role_requirements
                // Ignore 'optional' rules
                .filter( requirement => requirement.Operator != 'optional')

                // Group requirements by Property
                .reduce( (grouped, requirement) => {
                    let group_name = [requirement.Property, (requirement.Value || '*')].join(':');
                    let group = grouped[group_name] || [];
                    group.push(requirement);

                    grouped[group_name] = group;
                    return grouped;
                }, {});

            // Groups are placed into an object for convenience of the previous instr.
            // We don't care about the keys going forward, so just look at its values

            // Compare each property rule to determine incompatibilities
            let role_incompatibilities = Object.values(requirementsPerProperty)
                .reduce( (incompatibilities, group) => {

                    for( let i = 0; i < group.length; i++ ){
                        let rule = group[i];
                        let incompatible_roles = group
                            .filter( r => r.Operator != rule.Operator )
                            .map( r => r.RoleName);

                        if( incompatible_roles.length ){
                            incompatibilities[rule.RoleName] = (incompatibilities[rule.RoleName] || []).concat(incompatible_roles);
                        }
                    }

                    return incompatibilities;
                }, []);

            console.debug('role_incompatibilities:', role_incompatibilities);
            //////////////////////

            return role_incompatibilities;
        },
        getCategories: function(){
            let categories = [];

            categories.push(
                new UserHubCategory('Principal Investigators', 'pis', [ROLE.PRINCIPAL_INVESTIGATOR],
                [ // display fields
                    COL_LAST_NAME,
                    COL_FIRST_NAME,
                    COL_DEPARTMENT,
                    COL_OFFICE_PHONE,
                    COL_LAB_PHONE,
                    COL_BUILDING,
                    COL_EMAIL,
                    COL_EMERGENCY_PHONE
                ],
                [ // edit fields
                    COL_DEPARTMENT,
                    COL_OFFICE_PHONE,
                    COL_LAB_PHONE,
                    COL_EMERGENCY_PHONE
                ],
                {
                    restrictRoles: false,
                    newUserRoles: [ROLE.PRINCIPAL_INVESTIGATOR]
                })
            );
        
            categories.push(
                new UserHubCategory('Lab Contacts', 'contacts', [ROLE.LAB_CONTACT],
                [ // display fields
                    COL_LAST_NAME,
                    COL_FIRST_NAME,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_DEPARTMENT,
                    COL_EMAIL,
                    COL_LAB_PHONE,
                    COL_EMERGENCY_PHONE
                ],
                [ // edit fields
                    COL_EMERGENCY_PHONE,
                    COL_LAB_PHONE,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_POSITION
                ],
                {
                    restrictRoles: false,
                    newUserRoles: [ROLE.LAB_CONTACT, ROLE.LAB_PERSONNEL],
                    positionOptions: Constants.POSITION.LAB_PERSONNEL
                })
            );
        
            categories.push(
                new UserHubCategory('Lab Personnel', 'labPersonnel', [ROLE.LAB_PERSONNEL],
                [ // display fields
                    COL_CONTACT_ICONS,
                    COL_LAST_NAME,
                    COL_FIRST_NAME,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_POSITION,
                    COL_DEPARTMENT,
                    COL_EMAIL,
                    COL_LAB_PHONE
                ],
                [ // edit fields
                    COL_EMERGENCY_PHONE,
                    COL_LAB_PHONE,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_POSITION
                ],
                {
                    restrictRoles: false,
                    newUserRoles: [ROLE.LAB_PERSONNEL],
                    positionOptions: Constants.POSITION.LAB_PERSONNEL
                })
            );
        
            categories.push(
                new UserHubCategory('Rad Contacts', 'radContacts', [ROLE.RADIATION_CONTACT],
                [ // display fields
                    COL_CONTACT_ICONS,
                    COL_LAST_NAME,
                    COL_FIRST_NAME,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_POSITION,
                    COL_DEPARTMENT,
                    COL_EMAIL,
                    COL_LAB_PHONE
                ],
                [ // edit fields
                    COL_EMERGENCY_PHONE,
                    COL_LAB_PHONE,
                    COL_PRINCIPAL_INVESTIGATOR,
                    COL_POSITION
                ],
                {
                    restrictRoles: false,
                    newUserRoles: [ROLE.LAB_PERSONNEL, ROLE.RADIATION_CONTACT],
                    positionOptions: Constants.POSITION.LAB_PERSONNEL
                })
            );
        
            categories.push(
                new UserHubCategory('EHS Personnel', 'EHSPersonnel',
                    [ // included roles
                        ROLE.ADMIN,
                        ROLE.SAFETY_INSPECTOR,
                        ROLE.RADIATION_USER,
                        ROLE.RADIATION_ADMIN,
                        ROLE.RADIATION_INSPECTOR,
                        ROLE.OCCUPATIONAL_HEALTH,
                        ROLE.READ_ONLY
                    ],
                    [ // display fields
                        COL_LAST_NAME,
                        COL_FIRST_NAME,
                        COL_ROLES,
                        COL_POSITION,
                        COL_OFFICE_PHONE,
                        COL_EMAIL,
                        COL_EMERGENCY_PHONE
                    ],
                    [ // edit fields
                        COL_OFFICE_PHONE,
                        COL_EMERGENCY_PHONE,
                        COL_POSITION
                    ],
                    {
                        restrictRoles: false,
                        newUserRoles: [],
                        positionOptions: Constants.POSITION.EHS_PERSONNEL
                    }
                )
            );
        
            categories.push(
                new UserHubCategory('Department Chairs & Coordinators', 'departmentContacts',
                    [ // included roles
                        ROLE.DEPARTMENT_CHAIR,
                        ROLE.DEPARTMENT_COORDINATOR
                    ],
                    [ // display fields
                        COL_LAST_NAME,
                        COL_FIRST_NAME,
                        COL_ROLES,
                        COL_DEPARTMENT,
                        COL_OFFICE_PHONE,
                        COL_EMERGENCY_PHONE,
                        COL_EMAIL
                    ],
                    [ // edit fields
                        COL_OFFICE_PHONE,
                        COL_EMERGENCY_PHONE,
                        COL_DEPARTMENT
                    ],
                    {
                        restrictRoles: true,
                        newUserRoles: []
                    }
                )
            );
        
            categories.push(
                new UserHubCategory('Teaching Lab Contacts', 'teachingLabContacts', [ROLE.TEACHING_LAB_CONTACT],
                    [ // display fields
                        COL_LAST_NAME,
                        COL_FIRST_NAME,
                        COL_DEPARTMENT,
                        COL_EMAIL,
                        COL_OFFICE_PHONE,
                        COL_EMERGENCY_PHONE
                    ],
                    [ // edit fields
                        COL_OFFICE_PHONE,
                        COL_EMERGENCY_PHONE,
                        COL_DEPARTMENT
                    ],
                    {
                        restrictRoles: true,
                        newUserRoles: [ROLE.TEACHING_LAB_CONTACT]
                    }
                )
            );
        
            // Generate 'uncategorized' as any role not included in another category
            let categorized_roles = [];
            categories
                .map( cat => cat.roles )
                .forEach( roles => categorized_roles = categorized_roles.concat(roles));
        
            let uncategorized_roles = [];
            for( field in ROLE ){
                if( !categorized_roles.includes(ROLE[field]) ){
                    uncategorized_roles.push(ROLE[field]);
                }
            }
        
            categories.push(
                new UserHubCategory('Uncategorized Users', 'uncategorized', uncategorized_roles,
                    [ // display fields
                        COL_LAST_NAME,
                        COL_FIRST_NAME,
                        COL_ROLES
                    ],
                    [ /* edit fields */ ],
                    {
                        includeRoleless: true
                    }
                )
            );

            return categories;
        }
    };

    return factory;
})

.factory('UserHubAPI', function($http){
    return {
        cache:{},

        endpoint_url: GLOBAL_WEB_ROOT + 'ajaxaction.php',
        api_headers: {'Content-Type': 'application/json' },
        formdata_headers: {'Content-Type': 'application/x-www-form-urlencoded' },

        _get: async function( url ){
            return $http({
                method: 'GET',
                url: url
            });
        },

        _post_action: async function (action, data){
            try{
                let url = this.endpoint_url + '?action=' + action;

                let resp = await $http({
                    method: 'POST',
                    url: url,
                    data: data
                });

                return resp.data;
            }
            catch(error){
                console.error("Error posting " + action, error);
                return null;
            }
        },

        _get_action: async function (action){
            try{
                let resp = await this._get(this.endpoint_url + '?action=' + action);
                return resp.data;
            }
            catch(error){
                console.error("Error loading " + action, error);
                return null;
            }
        },

        _get_or_cache_action: async function (action){
            if( !this.cache[action] ){
                let results = await this._get_action( action );
                this.cache[action] = results;
            }

            return this.cache[action];
        },

        getAllRoles: async function (){
            return this._get_or_cache_action('getAllRoles');
        },

        getAllDepartments: async function(){
            return this._get_or_cache_action('getAllDepartments');
        },

        getAllUsers: async function(){
            return this._get_or_cache_action('getUsersForUserHub');
        },

        getAllPIs: async function(){
            return this._get_or_cache_action('getAllPINames');
        },

        lookupUserDetails: async function( username ){
            return this._get_or_cache_action('lookupUser' + '&username=' + username);
        },

        saveUser: async function( user ){
            return this._post_action('saveUser', user);
        },

        toggleUserActivation: async function (user){
            let parts = [
                'setUserActivation',
                'user_id=' + user.Key_id,
                'active=' + (user.Is_active ? 'false':'true')

            ];
            return this._post_action(parts.join('&'));
        },

        getAllAccessRequests: async function (){
            return this._get_or_cache_action('getAllAccessRequests');
        },

        assignLabUser: async function(userId, piId, roleName){
            let parts = [
                'assignLabUserToPI',
                'piid=' + piId,
                'uid=' + userId,
                'labContact=' + (roleName == Constants.ROLE.NAME.LAB_CONTACT)
            ];

            return this._post_action(parts.join('&'));
        },

        unassignLabUser: async function(userId, inactive){
            let parts = [
                'unassignLabUser',
                'uid=' + userId
            ];

            if( inactive ){
                parts.push('inactive=true');
            }

            return this._post_action(parts.join('&'));
        }
    };
})
.filter('tel', function () {
    return function (phoneNumber) {
        if (!phoneNumber)
            return phoneNumber;

        return formatLocal('US', phoneNumber);
    }
});
