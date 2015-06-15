var homeApp = angular.module('homeApp', ['ui.bootstrap','convenienceMethodModule']);

homeApp.config(function($routeProvider){
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
});

var testController = function($location, $scope, $rootScope,roleBasedFactory){
	console.log($location);

	init();
	function init(){
		console.log('adsf');
	}

	$scope.setRoute = function(route){
    	$location.path(route);
  	}
	$rootScope.rbf = roleBasedFactory;

}

var homeController = function($location, $scope){
	console.log($location);

	init();
	function init(){
		console.log('yowza');
		$scope.view = 'home';
	}


}

var adminController = function($location, $scope){
	console.log($location);


	init();
	function init(){
		$scope.view = 'home';
	}


}