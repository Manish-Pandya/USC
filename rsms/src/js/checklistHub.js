var checklistHub = angular.module('checklistHub', ['convenienceMethodWithRoleBasedModule','ui.bootstrap','once']);

function ChecklistHubController($scope, $rootElement, $location, convenienceMethods, roleBasedFactory) {

    $scope.rbf = roleBasedFactory;

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

        var url = '../../ajaxaction.php?action=getChecklistByHazardId&id='+id+'&callback=JSON_CALLBACK';
        convenienceMethods.getData( url, onGetChecklist, onFailGetChecklist );
    }

    function onGetChecklist(data){

        console.log(data);
        if(!data.Name){
            $scope.noChecklist = true;
            $scope.edit = false;
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

    $scope.editChecklist = function(){
        $scope.edit = true;
    }

    $scope.saveChecklist = function(dto, checklist){
        $scope.checklistCopy.IsDirty = true;
        var url = '../../ajaxaction.php?action=saveChecklist';
        convenienceMethods.updateObject( $scope.checklistCopy, checklist, onSaveChecklist, onFailSaveChecklist, url );
    }

    function onSaveChecklist(dto, checklist){
        if(!$scope.checklist)$scope.checklist = {};
        $scope.checklist.Name = $scope.checklistCopy.Name;
        $scope.checklist.Key_id = dto.Key_id;
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

    // moves question up or down in the list
    $scope.moveQuestion = function(direction, index) {
        direction = direction.toUpperCase();
        $scope.filteredQuestions[index].IsDirty=true;
        if(typeof index !== "number") {
            console.log("ERROR: index is not a number, given "+index);
        }

        // get key id of the question we're moving
        var initialId = $scope.filteredQuestions[index].Key_id;
        var newId;

        // determine which item we're swapping with
        if(direction === "UP") {
            newId = $scope.filteredQuestions[index - 1].Key_id;
        }
        else if(direction === "DOWN") {
            newId = $scope.filteredQuestions[index + 1].Key_id;
        }
        else {
            console.log("ERROR: Movement direction was detected as neither UP nor DOWN");
            return;
        }

        var url = "../../ajaxaction.php?action=swapQuestions&firstKeyId="+initialId+"&secondKeyId="+newId+"&callback=JSON_CALLBACK";

        // tell server to swap those questions and return the new checklist
        convenienceMethods.getDataAsDeferredPromise(url)
            .then(function(data) {
                // reset page with new checklist
                onGetChecklist(data);
            },
            function(errorData) {
                console.log("An error occurred while attempting to move question with index "+index+":");
                console.log(errorData);
            });

    }

    // Necessary for ordering questions
    $scope.order = function(question) {
        return parseFloat(question.Order_index);
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
