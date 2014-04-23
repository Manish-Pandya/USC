var checklistHub = angular.module('checklistHub', ['convenienceMethodModule','ui.bootstrap']);

function ChecklistHubController($scope, $rootElement, $location, convenienceMethods) {
	
	function init(){		
		if($location.search().id){
			getChecklistById($location.search().id);
		}
		$scope.checklistCopy = {};
	}

	init();

	$scope.onSelectHazard = function(hazard,m,label){
		getChecklistById(hazard.Key_id);
	}

	function getChecklistById(id){
		$scope.doneLoading = false;

		var url = '../../ajaxaction.php?action=getHazardById&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetHazard, onFailGetHazard );

		var url = '../../ajaxaction.php?action=getChecklistByHazardId&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetChecklist, onFailGetChecklist );

		var url = '../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetHazards, onFailGetHazards );
	}

	function onGetChecklist(data){

		console.log(data);
		if(!data.Name){
			$scope.noChecklist = true;
			$scope.edit = true;
		}else{
			$scope.checklist = data;
			$scope.checklistCopy = angular.copy($scope.checklist);
		}
		$scope.doneLoading = true;
	}

	function onFailGetChecklist(){
		console.log('here');
	}
	function onGetHazard(data){
		console.log(data);
		$scope.hazard = data;
		if($scope.checklist)$scope.doneLoading = true;
	}
	function onFailGetHazard(){

	}

	function onGetHazards(data){
		console.log(data);
		$scope.hazards = data;
	}

	function onFailGetHazards(){
		alert('There was a problem getting the list of hazards.');
	}

	$scope.saveChecklist = function(dto, checklist){
		$scope.checklistCopy.IsDirty = true;
		var url = '../../ajaxaction.php?action=saveChecklist';		
		convenienceMethods.updateObject( $scope.checklistCopy, checklist, onSaveChecklist, onFailSaveChecklist, url );
	}

	function onSaveChecklist(dto, checklist){
		if(!$scope.checklist)$scope.checklist = {};
	 	$scope.checklist.Name = $scope.checklistCopy.Name;
        $scope.checklistCopy = false;
        $scope.edit = false;
        $scope.checklist.IsDirty = false;
	}

	function onFailSaveChecklist(){
		alert("There was a problem saving the checklist.");
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

  $scope.$watch(
        "hazard",
        function( newValue, oldValue ) {
        	if($scope.hazard){
	        	if($scope.checklist){
	        		$scope.checklistCopy = angular.copy($scope.checklist)
	        	}else{
	        		$scope.checklistCopy = {
	        			Name: $scope.hazard.Name,
	        			Hazard_id: $scope.hazard.Key_id,
	        			Class: "Checklist"
	        		}
	        	}
        	}
        }
    );
}

checklistHub.controller('ChecklistHubController',ChecklistHubController);