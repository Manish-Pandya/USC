var questionHub = angular.module('questionHub', ['convenienceMethodModule']);

function QuestionHubController($scope, $rootElement, $location, convenienceMethods) {
	
	function init(){		
		if($location.search().question){
			getQuestionById($location.search().question);
		}
	}


	init();

	function getQuestionById(id){
		$scope.doneLoading = false;
		var url = '../../ajaxaction.php?action=getQuestionById&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetQuestion, onFailGetQuestion );
	}

	function onGetQuestion(data){
		$scope.question = data;
		$scope.doneLoading = true;
	}

	function onFailGetQuestion(){

	}

	$scope.addDeficiency = function(question){

		console.log(question);

		question.IsDirty = true;

		$scope.newDef = {
			Class:  'Deficiency',
			Question: question,
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newDeficiency
		}
        
        console.log($scope.newDef);

        var url = '../../ajaxaction.php?action=saveDeficiency';
        convenienceMethods.updateObject( $scope.newDef, question, onAddDef, onFailAddDef, url );
	}

	function onAddDef(def, question){
		question.Deficiencies.push(def);
		question.IsDirty = false;
	}

	function onFailAddDef(){
		alert("There was a problem when attempting to add the deficiency.");
	}

	$scope.addObservation = function(question){
		question.IsDirty = true;

		$scope.newObs = {
			Class:  'Observation',
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newObservation
		}
        
        console.log($scope.newObs);

        var url = '../../ajaxaction.php?action=saveObservation';
        convenienceMethods.updateObject( $scope.newObs, question, onAddObs, onFailAddPbs, url );
	}

	function onAddObs(def, question){
		question.Observations.push(def);
		question.IsDirty = false;
	}

	function onFailAddPbs(){
		alert("There was a problem when attempting to add the note.");
	}
	$scope.addRecommendation = function(question){
		question.IsDirty = true;

		$scope.newRec = {
			Class:  'Recommendation',
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newRecommendation
		}
 
        var url = '../../ajaxaction.php?action=saveRecommendation';
        convenienceMethods.updateObject( $scope.newRec, question, onAddDef, onFailAddDef, url );
	}

	function onAddDef(rec, question){
		question.Recommendations.push(rec);
		question.IsDirty = false;
	}

	function onFailAddDef(){
		alert("There was a problem when attempting to add the recommendation.");
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

questionHub.controller('QuestionHubController',QuestionHubController);
