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
		.otherwise(
			{
				redirectTo: '/home'
			}
		);
});

var testController = function($location, $scope){
	console.log($location);

	init();
	function init(){
		console.log('adsf');
	}

	$scope.setRoute = function(route){
    	$location.path(route);
  	}


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
		console.log('yowza');
		$scope.view = 'home';
	}


}