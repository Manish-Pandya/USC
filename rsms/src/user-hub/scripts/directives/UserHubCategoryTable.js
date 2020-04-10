// Get the path to this script file so that we can assume path to template
// This allows the directive to be included by other modules
var scripts = document.getElementsByTagName("script");
var currentScriptPath = scripts[scripts.length-1].src;

angular.module('rsms-UserHub')
.directive('userHubCategoryTable', function(){
    return {
        restrict: 'E',
        scope: {
            category: "=",
            users: "="
        },
        replace: false,
        transclude: false,
        templateUrl: currentScriptPath.replace('UserHubCategoryTable.js', 'UserHubCategoryTable.html'),
        controller: function($scope, $modal, $timeout, roleBasedFactory){
            console.debug("UserHubCategoryTable controller");
            $scope.GLOBAL_WEB_ROOT = window.GLOBAL_WEB_ROOT;

            console.log($scope.config);

            function UserHubTableField( name, property, sortable ){
                this.name = name;
                this.property = property;
                this.sortable = sortable;
            }

            // TODO: Analyze our Category to determine Fields
            $scope.table = {};
            $scope.table.fields = [
                new UserHubTableField( 'Last Name', 'Last_name', true ),
                new UserHubTableField( 'First Name', 'First_name', false ),
                new UserHubTableField( 'Role', 'Roles', false ),
                new UserHubTableField( 'Lab PI', 'Supervisor', true ),
                new UserHubTableField( 'Position', 'Position', true ),
                new UserHubTableField( 'Department(s)', 'Departments', true ),
                new UserHubTableField( 'Office Phone', 'Office_phone', false ),
                new UserHubTableField( 'Lab Phone', 'Lab_phone', false ),
                new UserHubTableField( 'Building(s)', 'Buildings', false ),
                new UserHubTableField( 'Email', 'Email', false ),
                new UserHubTableField( 'Emergency Phone', 'Emergency_phone', false ),
            ];

            // Set up configuration
            $scope.config = {};

            let roles = roleBasedFactory.getRoles();
            let admin_roles = [
                roles['Admin'],
                roles['Radiation Admin']
            ];

            $scope.config.show_admin_controls = roleBasedFactory.getHasPermission(admin_roles);

            // TODO: Configure columns based on Category
            $scope.category.columns.forEach( col => {
                $scope.config['show_field_' + col] = true;
            });

            ////////////////////
            // Scope functions
            $scope.toggleUserActive = function toggleUserActive( user ){
                console.log("TODO: Flip active status of ", user);
                user.Is_active = !user.Is_active;
            }

            $scope.editUser = function editUser(user, $user_index){
                let modalInstance = $modal.open({
                    templateUrl: 'scripts/modals/edit-user-modal.html',
                    controller: 'EditUserModalCtrl',
                    windowClass: 'modal-dialog-wide',
                    resolve: {
                        category: function(){ return $scope.category; },
                        user: function(){ return user; }
                    }
                });

                modalInstance.result.then( saved => {
                    // Insert or update?
                    if( user ){
                        console.debug("Update existing user");
                        angular.extend(user, saved);
                    }
                    else {
                        console.debug("Insert new user");
                        $scope.users.push( saved );
                    }
                });
            }
        }
    };
});
