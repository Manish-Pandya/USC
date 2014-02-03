var piHub = angular.module('piHub', ['ui.bootstrap','convenienceMethodModule']);

piHub.config(function($routeProvider){
	$routeProvider
		.when('/rooms', 
			{
				templateUrl: '../piHubPartials/rooms.html', 
				controller: piHubRoomController
			}
		)
		//.when('', {template: '', controller: })
		.otherwise(
			{
				redirectTo: '/rooms'
			}
		);
});

piHubMainController = function($scope, $location, convenienceMethods){
	$scope.doneLoading = false;

	$scope.setRoute = function(route){
    	$location.path(route);
  	}

	init();

	function init(){
		//alert('main');
       	
        if($location.search().hasOwnProperty('pi')){
        	 console.log($location.search().pi);
        	 //getPI
        	 var url = '../../../ajaxaction.php?action=getPI&id='+$location.search().pi+'&callback=JSON_CALLBACK';
        	 convenienceMethods.getData( url, onGetPI, onFailGetPI );
        }


        	
	}

	function onGetPI(data){
		console.log(data);
		$scope.PI = data;
		$scope.doneLoading = data.doneLoading;
	}

	function onFailGetPI(){
		alert('The system couldn\'t find the Principal Investigator');
	}

}

piHubRoomController = function($scope, $location, convenienceMethods){
	
	init();

	function init(){
		
        //console.log($location.search());
	}


}