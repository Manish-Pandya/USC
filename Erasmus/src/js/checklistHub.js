var checklistHub = angular.module('checklistHub', ['convenienceMethodModule']);

function ChecklistHubController($scope, $rootElement, $location, convenienceMethods) {
	
	function init(){		
		if($location.search().id){
			getChecklistById($location.search().id);
		}
	}


	init();

	function getChecklistById(id){
		$scope.doneLoading = false;
		var url = '../../ajaxaction.php?action=getChecklistByHazardId&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetChecklist, onFailGetChecklist );
	}

	function onGetChecklist(data){
		$scope.checklist = data;
		$scope.doneLoading = true;
	}

	function onFailGetChecklist(){

	}

	$scope.handleQuestionActive = function(question){
 		question.IsDirty = true;
        $scope.questionCopy = angular.copy(question);
        $scope.questionCopy.Is_active = !$scope.questionCopy.Is_active;
        if($scope.questionCopy.Is_active === null)question.Is_active = false;

        var url = '../../ajaxaction.php?action=saveQuestion';
        convenienceMethods.updateObject( $scope.questionCopy, question, onSaveQuestion, onFailSaveQuestion, url );
	}

	function onSaveQuestion(dto, question){
	     //temporarily use our question copy client side to bandaid server side bug that causes subquestions to be returned as indexed instead of associative
        dto = angular.copy($scope.questionCopy);
        convenienceMethods.setPropertiesFromDTO( dto, question );
        question.isBeingEdited = false;
        question.IsDirty = false;
        question.Invalid = false;
	}

	function onFailSaveQuestion(){

	}
}

checklistHub.controller('ChecklistHubController',ChecklistHubController);