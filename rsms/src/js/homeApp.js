var homeApp = angular.module('homeApp', ['ui.bootstrap','convenienceMethodModule']);

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
    .run(function(roleBasedFactory, $rootScope){
        var rbf = roleBasedFactory;
        rbf.getCurrentRoles()
            .then(
                function(roles){
                    console.log(roles);
                    $rootScope.roles = roles;
                }
            )
    });

var testController = function($location, $scope, $rootScope, roleBasedFactory){

    init();
    function init(){}

    $scope.setRoute = function(route){
        $location.path(route);
    }
    $rootScope.rbf = roleBasedFactory;

}

var homeController = function($location, $scope, $rootScope, roleBasedFactory){
    var rbf = roleBasedFactory;
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}

var adminController = function($location, $scope){
    var rbf = roleBasedFactory;
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}
