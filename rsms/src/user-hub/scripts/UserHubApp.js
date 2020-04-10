angular
    .module('rsms-UserHub', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'convenienceMethodWithRoleBasedModule',
    'angular.filter',
    'ui.router',
    'ui.mask'
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
                <h2 ng-if="!Users">Loading Users <i class="icon-spinnery-dealie spinner"></i></h2>
                <ui-view ng-if="Users"/>`
        })
        .state('user-hub.users.category', {
            url: '/:category',
            template: `
                <p><span class='badge badge-inverse' style="margin-right: 5px;" ng-repeat="role in category.roles">{{role}}</span></p>
                <user-hub-category-table users="Users" category="category">
                </user-hub-category-table>
            `,
            controller: function ($rootScope, $scope, $stateParams){
                console.log("user-hub.users.category");
                $scope.category = $rootScope.categories.find( c => c.code == $stateParams.category);
            }
        });
})
.filter('categoryFilter', function(){
    return function( users, category ){
        if( !users || !category ) return users;

        // Filter to users who have any listed category role
        return users.filter(u => {
            // Ignore users without roles
            if( !u || !u.Roles ){ return false; }

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
.controller('AppCtrl', function ($rootScope, $scope, $modal, $timeout, UserHubAPI) {
    console.debug("rsms-UserHub running");

    // Expose Role Requirements
    $rootScope.RoleRequirements = RoleRequirements;

    // Expose Constants to views
    $rootScope.constants = Constants;

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

    function UserHubCategory( name, code, roles, columns ){
        this.name = name;
        this.code = code;
        this.roles = roles;
        this.columns = columns;
    }

    $rootScope.categories = [];
    $rootScope.categories.push(
        new UserHubCategory('Principal Investigators', 'pis', [ROLE.PRINCIPAL_INVESTIGATOR], [
            COL_LAST_NAME,
            COL_FIRST_NAME,
            COL_DEPARTMENT,
            COL_OFFICE_PHONE,
            COL_LAB_PHONE,
            COL_BUILDING,
            COL_EMAIL,
            COL_EMERGENCY_PHONE
        ])
    );

    $rootScope.categories.push(
        new UserHubCategory('Lab Contacts', 'contacts', [ROLE.LAB_CONTACT], [
            COL_LAST_NAME,
            COL_FIRST_NAME,
            COL_PRINCIPAL_INVESTIGATOR,
            COL_DEPARTMENT,
            COL_EMAIL,
            COL_LAB_PHONE,
            COL_EMERGENCY_PHONE
        ])
    );

    $rootScope.categories.push(
        new UserHubCategory('Lab Personnel', 'labPersonnel', [ROLE.LAB_PERSONNEL], [
            COL_LAST_NAME,
            COL_FIRST_NAME,
            COL_PRINCIPAL_INVESTIGATOR,
            COL_POSITION,
            COL_DEPARTMENT,
            COL_EMAIL,
            COL_LAB_PHONE
        ])
    );

    $rootScope.categories.push(
        new UserHubCategory('EHS Personnel', 'EHSPersonnel',
            [
                ROLE.ADMIN,
                ROLE.SAFETY_INSPECTOR,
                ROLE.RADIATION_USER,
                ROLE.RADIATION_ADMIN,
                ROLE.RADIATION_INSPECTOR,
                ROLE.OCCUPATIONAL_HEALTH,
                ROLE.READ_ONLY
            ], [
                COL_LAST_NAME,
                COL_FIRST_NAME,
                COL_ROLES,
                COL_POSITION,
                COL_OFFICE_PHONE,
                COL_EMAIL,
                COL_EMERGENCY_PHONE
            ]
        )
    );

    $rootScope.categories.push(
        new UserHubCategory('Department Chairs & Coordinators', 'departmentContacts',
            [
                ROLE.DEPARTMENT_CHAIR,
                ROLE.DEPARTMENT_COORDINATOR
            ],
            [
                COL_LAST_NAME,
                COL_FIRST_NAME,
                COL_ROLES,
                COL_DEPARTMENT,
                COL_OFFICE_PHONE,
                COL_EMAIL
            ]
        )
    );

    $rootScope.categories.push(
        new UserHubCategory('Teaching Lab Contacts', 'teachingLabContacts', [ROLE.TEACHING_LAB_CONTACT], [
            COL_LAST_NAME,
            COL_FIRST_NAME,
            COL_DEPARTMENT,
            COL_EMAIL,
            COL_OFFICE_PHONE,
            COL_EMERGENCY_PHONE
        ])
    );

    // Generate 'uncategorized' as any role not included in another category
    let categorized_roles = [];
    $rootScope.categories
        .map( cat => cat.roles )
        .forEach( roles => categorized_roles = categorized_roles.concat(roles));

    let uncategorized_roles = [];
    for( field in ROLE ){
        if( !categorized_roles.includes(ROLE[field]) ){
            uncategorized_roles.push(ROLE[field]);
        }
    }

    $rootScope.categories.push(
        new UserHubCategory('Uncategorized Users', 'uncategorized', uncategorized_roles,
        [
            COL_LAST_NAME,
            COL_FIRST_NAME
        ])
    );

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
.controller('EditUserModalCtrl', function($scope, $modalInstance, $timeout, UserHubAPI, category, user){

    // Set up scope
    $scope.category = category;
    $scope.user = user ? angular.copy(user)
                       : {};

    $scope.state = {
        allow_edit: $scope.user.Key_id,

        all_roles: null,
        selectedRole: null,

        all_departments: null,
        selectedDepartment: null,

        all_pis: null
    };

    /////////////////////
    // Async load data
    UserHubAPI.getAllPIs().then( pis => {
        $timeout(function(){
            $scope.state.all_pis = pis;
        });
    });

    UserHubAPI.getAllRoles().then( roles => {
        $timeout(function(){ $scope.state.all_roles = roles;});
    });

    UserHubAPI.getAllDepartments().then( depts => {
        $timeout(function(){ $scope.state.all_departments = depts;});
    });

    ////////////////////
    // Scope Functions
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
                angular.extend( $scope.user, details );
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
        console.log("TODO: Save", $scope.user);

        // TODO: Validate current category

        // Save user
        try{
            let saved = await UserHubAPI.saveUser( $scope.user );
            ToastApi.toast('Saved ' + saved.Username);

            $modalInstance.close( saved );
        }
        catch(error){
            ToastApi.toast('An error occured while saving this user.', ToastApi.ToastType.ERROR);
            console.error(error);
        }
    }

    $scope.tagHandler = function tagHandler(tag){return null;}

    $scope.validateCategory = function validateCategory(){
        // TODO: Validate rules of $scope.category
    };

    $scope.onSelectPI = function onSelectPI( pi ){
        $scope.user.Supervisor_id = pi.Key_id;
    }

    ////////////////////
    // Role management
    /** Can the role be removed from a User in this category? */
    $scope.canRemoveRole = function canRemoveRole(role){
        // TODO
        return true;
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

        if( !$scope.user.Roles.includes(role) ){
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

    /** Can the role be removed from a User in this category? */
    $scope.canRemoveDepartment = function canRemoveDepartment(role){
        // TODO
        return true;
    }

    $scope.removeDepartment = function removeDepartment( dept ){
        let idx = $scope.user.Departments.indexOf(dept);
        if( idx > -1 ){
            console.debug("Remove dept from list", dept);
            $timeout(() => $scope.user.Departments.splice(idx, 1));
        }
        else {
            console.debug("Dept to remove is not present in list");
        }
    }

    $scope.onSelectDepartment = function onSelectDepartment( dept ){
        if( !$scope.user.Departments ){
            $scope.user.Departments = [];
        }

        if( !$scope.user.Departments.includes(dept) ){
            console.debug("Push new dept", dept);
            $timeout(() => $scope.user.Departments.push(dept));
        }
        else{
            console.debug("Dept is already selected");
        }
    }
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
