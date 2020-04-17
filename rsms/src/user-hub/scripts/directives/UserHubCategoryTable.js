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

            // Set up configuration
            $scope.config = {
                active_status: true,
                sorter: {
                    expr: 'Last_name',
                    asc: true
                }
            };

            let roles = roleBasedFactory.getRoles();
            let admin_roles = [
                roles['Admin'],
                roles['Radiation Admin']
            ];

            $scope.config.show_admin_controls = roleBasedFactory.getHasPermission(admin_roles);

            // Configure columns based on Category
            $scope.category.columns.forEach( col => {
                $scope.config['show_field_' + col] = true;
            });

            // Prep filtering
            $scope.search = {};

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
                    //windowClass: 'modal-dialog-wide',
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
})
.directive('userHubSortField', function(){
    return {
        restrict: 'E',
        scope: {
            fieldExpr: "@",
            sorter: "=",
        },
        replace: false,
        transclude: true,
        template: `
            <a ng-click="toggleSort()">
                <span ng-transclude/>
                <i ng-if="sorter.expr == fieldExpr" ng-class="{
                    'icon-arrow-up': sorter.asc,
                    'icon-arrow-down': !sorter.asc
                }"></i>
            </a>
        `,
        controller: function($scope){
            $scope.toggleSort = function toggleSort(){
                if( $scope.sorter.expr == $scope.fieldExpr ){
                    // Simply toggle our order
                    $scope.sorter.asc = !$scope.sorter.asc;
                }
                else {
                    // Init new sort
                    $scope.sorter.expr = $scope.fieldExpr;
                    $scope.sorter.asc = true;
                }
            }
        }
    };
});
