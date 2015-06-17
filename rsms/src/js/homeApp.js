var homeApp = angular.module('homeApp', ['ui.bootstrap','convenienceMethodWithRoleBasedModule']);

homeApp
    .config(function($routeProvider){
        $routeProvider
            .when('/home',
                {
                    templateUrl: '../views/rsmsCenterPartials/home.html',
                    controller: homeController
                }
            )
            .when('/admin',
                {
                    templateUrl: '../views/rsmsCenterPartials/admin.html',
                    controller: adminController
                }
            )
            .when('/inspections',
                {
                    templateUrl: '../views/rsmsCenterPartials/inspections.html',
                    controller: adminController
                }
            )
            .otherwise(
                {
                    redirectTo: '/home'
                }
            );
    })

var testController = function($location, $scope, $rootScope, roleBasedFactory){
    $scope.setRoute = function(route){
        $location.path(route);
    }
    $scope.setRoute = function(route){
        $location.path(route);
    }

    $scope.rbf = roleBasedFactory;
    $scope.roles = roleBasedFactory.roles;

}

var homeController = function($location, $scope, $rootScope){
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}

var adminController = function($location, $scope){
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}
