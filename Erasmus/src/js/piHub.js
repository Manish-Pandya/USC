var piHub = angular.module('piHub', ['ui.bootstrap','convenienceMethodModule']);

piHub.config(function($routeProvider){
	$routeProvider
		.when('/rooms', 
			{
				templateUrl: '../piHubPartials/rooms.html', 
				controller: piHubRoomController
			}
		)
		.when('/personnel', 
			{
				templateUrl: '../piHubPartials/personnel.html', 
				controller: piHubPersonnelController
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
        if($location.search().hasOwnProperty('pi')){
        	 //getPI if there is a "pi" index in the GET
        	 getPi($location.search().pi);
        }else{
        	$scope.noPiSet = true;
        }

        //always get a list of all PIs so that a user can change the PI in scope
        var url = '../../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
       	convenienceMethods.getData( url, onGetAllPIs, onFailGetAllPIs );
        	
	}

	function getPi(PIKeyID){
		$scope.PI = false;
		var url = '../../../ajaxaction.php?action=getPI&id='+PIKeyID+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetPI, onFailGetPI );
		$scope.noPiSet = false;
	}

	function onGetPI(data){
		console.log(data);
		$scope.PI = data;
		$scope.doneLoading = data.doneLoading;
	}

	function onFailGetPI(){
		alert('The system couldn\'t find the Principal Investigator');
	}

	function onGetAllPIs(data){
		$scope.PIs = data;
		$scope.doneLoadingAll = data.doneLoading;
	} 

	function onFailGetAllPIs(){
		alert('Something went wrong getting the list of all Principal Investigators');
	}

	//callback function called when a PI is selected in the typeahead
	$scope.onSelectPi = function($item, $model, $label){
		getPi($item.Key_Id);
	}
/*
	$scope.PIs = function(brandName){
       return $http.post('/brands/getbrandsviewmodel', { query : brandName})
                     .then(function(response){
                        return limitToFilter(response.data, 15);
                      });
    };
*/
}

piHubRoomController = function($scope, $location, convenienceMethods){
	
	init();
	function init(){
		
	}
}

piHubPersonnelController = function($scope, $location, convenienceMethods){
	init();
	function init(){

	}
}