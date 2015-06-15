var questionHub = angular.module('questionHub', ['convenienceMethodModule', 'once']);

function QuestionHubController($scope, $q, $rootElement, $location, convenienceMethods) {
	
	function init(){
		if($location.search().id){
			getQuestionById($location.search().id);
		}else if($location.search().checklist_id){
		    getChecklist($location.search().checklist_id);
			$scope.noQuestion = true;
			$scope.questionCopy = {
				Class: "Question",
				Checklist_id: $location.search().checklist_id
			};
		}
		$scope.newDeficiency = {};
		$scope.newDeficiency.reference;
		$scope.newDeficiency.description;
	}


	init();

	function getQuestionById(id){
		$scope.doneLoading = false;
		var url = '../../ajaxaction.php?action=getQuestionById&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetQuestion, onFailGetQuestion );
	}

	function onGetQuestion(data){
		$scope.question = data;
		getChecklist(data.Checklist_id);
		$scope.noQuestion = false;

	}

	function onFailGetQuestion(){
		alert('There was a problem getting the question.');
	}

	function getChecklist(id){
		var url = '../../ajaxaction.php?action=getChecklistById&id='+id+'&callback=JSON_CALLBACK';
		convenienceMethods.getData( url, onGetChecklist, onFailGetChecklist );
	}

	function onGetChecklist(data){
		$scope.checklist = data;
		$scope.doneLoading = true;
	}

	function onFailGetChecklist(){
		alert("There was a problem gettting this question's checklist.");
	}

	$scope.editDef = function(def){
		def.edit = !def.edit;
		$scope.question.newDeficiency = angular.copy(def);
		$scope.question.newDeficiency.IsDirty = false;
	}

	$scope.cancelEdit = function(obj){
		obj.edit = !obj.edit;
		obj.beingEdited = false;
		if(obj.Class == "Question")$scope.questionCopy = {};
		if(obj.Class == "Recommendation")$scope.question.newRecommendation = {};
		if(obj.Class == "Deficiency")$scope.question.newDeficiency = {};
		if(obj.Class == "Observation")$scope.question.newObservation = {};
	}

	$scope.addDeficiency = function(question){
		$scope.savingDeficiency = true;
		console.log(question);
		console.log(newDeficiency);
		question.IsDirty = true;

		$scope.newDef = {
			Class:  'Deficiency',
			Question: question,
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newDeficiency.Text,
			Reference: question.newDeficiency.Reference,
			Description: question.newDeficiency.Description
		}
        if($scope.question.newDeficiency.Key_id){
        	$scope.newDef.Key_id = $scope.question.newDeficiency.Key_id;
        	var url = '../../ajaxaction.php?action=saveDeficiency';
        	convenienceMethods.updateObject( $scope.newDef, question.newDeficiency, onUpdateDef, onFailAddDef, url );
        }else{
        	var url = '../../ajaxaction.php?action=saveDeficiency';
        	convenienceMethods.updateObject( $scope.newDef, question, onAddDef, onFailAddDef, url );
        }
        console.log($scope.newDef);
	}

	function onUpdateDef(data,def){
		$scope.savingDeficiency = false;
		console.log(def);
		console.log($scope.question.Deficiencies);
		var idx = convenienceMethods.arrayContainsObject($scope.question.Deficiencies, def, null, true);
		console.log(idx);
		def.edit = false;
		$scope.question.Deficiencies[idx] = angular.copy(def);
		$scope.question.newDeficiency = {};
	}

	function onAddDef(def, question){
		$scope.savingDeficiency = false;
		$scope.question.newDeficiency = {};
		$scope.addDef = false;
		$scope.savingDeficiency = false;
		if(!question.Deficiencies)question.Deficiencies = [];
		question.Deficiencies.push(def);
		question.IsDirty = false;
	}

	function onFailAddDef(){
		$scope.savingDeficiency = false;
		alert("There was a problem when attempting to add the deficiency.");
	}

	$scope.addObservation = function(question){
		question.IsDirty = true;
		$scope.savingObservation = true;
		$scope.newObs = {
			Class:  'Observation',
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newObservation.Text
		}
        if(question.newObservation.Key_id){
        	$scope.newObs.Key_id = question.newObservation.Key_id;
        	 var url = '../../ajaxaction.php?action=saveObservation';
       	     convenienceMethods.updateObject( $scope.newObs, question, onUpdateObs, onFailAddPbs, url );
        }else{	
	        var url = '../../ajaxaction.php?action=saveObservation';
	        convenienceMethods.updateObject( $scope.newObs, question, onAddObs, onFailAddPbs, url );
        }
        console.log($scope.newObs);

	}

	$scope.editObs = function(obs){
		$scope.savingObservation = false;
		console.log(obs);
		obs.edit = !obs.edit;
		$scope.question.newObservation = angular.copy(obs);
		$scope.question.newObservation.IsDirty = false;
	}

	function onAddObs(def, question){
		$scope.savingObservation = false;
		$scope.addObvs = false;
		$scope.question.newObservation.IsDirty = false;
		$scope.question.newObservation = {};
		$scope.addObs = false;
		if(!question.Observations)question.Observations = [];
		question.Observations.push(def);
		def.IsDirty = false;
	}

	function onUpdateObs(obs, question){
		$scope.savingObservation = false;
		$scope.savingObservation = false;
		$scope.question.newObservation.IsDirty = false;
		$scope.addObvs = false;
		console.log(obs);
		console.log($scope.question.Observations);
		var idx = convenienceMethods.arrayContainsObject($scope.question.Observations, obs, null, true);
		console.log(idx);
		obs.edit = false;
		$scope.question.Observations[idx] = angular.copy(obs);
		$scope.question.newObservation = {};
	}

	function onFailAddPbs(){
		$scope.savingObservation = false;
		alert("There was a problem when attempting to add the note.");
	}
	$scope.addRecommendation = function(question){
		question.IsDirty = true;
		$scope.savingRecommendation = true;
		$scope.newRec = {
			Class:  'Recommendation',
			Is_active: true,
			Question_id: question.Key_id,
			Text: question.newRecommendation.Text
		}

		if(question.newRecommendation.Key_id){
			$scope.newRec.Key_id = question.newRecommendation.Key_id
			var url = '../../ajaxaction.php?action=saveRecommendation';
			convenienceMethods.updateObject( $scope.newRec, question, onUpdateRec, onFailAddPbs, url );
        }else{	
	        var url = '../../ajaxaction.php?action=saveRecommendation';
	        convenienceMethods.updateObject( $scope.newRec, question, onAddRec, onFailAddPbs, url );
        }
	}

	$scope.editRec = function(rec){
		rec.edit = !rec.edit;
		$scope.question.newRecommendation = angular.copy(rec);
	}

	function onAddRec(rec, question){
		$scope.savingRecommendation = false;
		$scope.addRec = false;
		$scope.savingRecommendation = false;
		if(!question.Recommendations)question.Recommendations = [];
		question.Recommendations.push(rec);
		question.IsDirty = false;
		$scope.question.newRecommendation = false;
	}

	function onUpdateRec(rec, question){
		$scope.savingRecommendation = false;
		$scope.addRec = false;
		console.log($scope.question.Recommendations);
		var idx = convenienceMethods.arrayContainsObject($scope.question.Recommendations, rec, null, true);
		console.log(idx);
		rec.edit = false;
		$scope.question.Recommendations[idx] = angular.copy(rec);
		$scope.question.newRecommendation = {};
	}

	function onFailAddRec(){
		$scope.savingRecommendation = false;
		alert("There was a problem when attempting to add the recommendation.");
	}


	$scope.handleObjActive = function(obj){
 		obj.IsDirty = true;
        $scope.objCopy = angular.copy(obj);
        $scope.objCopy.Is_active = !$scope.objCopy.Is_active;
        if($scope.objCopy.Is_active === null)question.Is_active = false;

        var url = '../../ajaxaction.php?action=save'+obj.Class;
        convenienceMethods.updateObject( $scope.objCopy, obj, onSetActive, onFailSetActive, url );
	}
	function onSetActive(dto, obj){

		//temporarily use our question copy client side to bandaid server side bug that causes subquestions to be returned as indexed instead of associative
        dto = angular.copy($scope.objCopy);
        convenienceMethods.setPropertiesFromDTO( dto, obj );
        obj.IsDirty = false;
        obj.Invalid = false;
	}
	function onFailSetActive(){

	}

	function onSaveQuestion(dto, question){
	     //temporarily use our question copy client side to bandaid server side bug that causes subquestions to be returned as indexed instead of associative
        $scope.question = angular.copy(dto);
        $scope.question.IsDirty = false;
        $scope.questionCopy.IsDirty = false;
        $scope.noQuestion = false;
        $scope.question.beingEdited = false;
        $scope.questionCopy = {};
        $location.search('id='+dto.Key_id);
	}

	function onFailSaveQuestion(){
		alert('There was a problem when the system tried to save the question.');
	}

	$scope.editQuestion = function(){
		$scope.question.beingEdited = !$scope.question.beingEdited;
		$scope.questionCopy = angular.copy($scope.question);
	}
	$scope.saveEditedQuestion = function( question ){

		if(!question){
			question = $scope.questionCopy;
			newQuestion = true;
		}

		$scope.questionCopy.IsDirty = true;
		$scope.questionCopy.Is_active = true;
		var url = '../../ajaxaction.php?action=saveQuestion';

		var defer = $q.defer();
		convenienceMethods.saveDataAndDefer( url, $scope.questionCopy )
			.then(
				function( returnedQuestion ){
					//succes
					console.log( returnedQuestion );
					defer.resolve( returnedQuestion );
					question.Text 	     = returnedQuestion.Text;
					question.Reference   = returnedQuestion.Reference;
					question.Description = returnedQuestion.Description;
					question.Key_id      = returnedQuestion.Key_id;
					$scope.questionCopy.IsDirty = false;
					question.beingEdited = false;

					//if this question is new, set up the view booleans so that we don't show the form after saving
					if(newQuestion){
						$scope.question = angular.copy( question );
						$scope.noQuestion = false;
					} 
				},
				function( fail ){
					//failure
					defer.reject( fail );
					question.beingEdited = false;
				}
			);

	}
/*
	$scope.$watch('checklist', function(oldValue, newValue){
     	if($scope.checklist){
     		$scope.questionCopy = {
     			Class: "Question",
     			Checklist_id: $scope.checklist.Key_id,
     			Order_index: 0
     		}
     	}
  	}, true);
*/
}

questionHub.controller('QuestionHubController',QuestionHubController);
