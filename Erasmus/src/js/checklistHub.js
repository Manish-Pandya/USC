var checklistHub = angular.module('checklistHub', ['convenienceMethodModule']);

function ChecklistHubController($scope, $rootElement, $location, convenienceMethods) {
	
	function init(){		
		if($location.search().checklist){
			getChecklistById($location.search().checklist);
		}
	}


	init();

	function getChecklistById(id){
		var url = '../../ajaxaction.php?action=getChecklistByHazardId&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetChecklist, onFailGetChecklist );
	}

	function onGetChecklist(data){
		$scope.checklist = data;
	}

	function onFailGetChecklist(){

	}

}

checklistHub.controller('ChecklistHubController',ChecklistHubController);