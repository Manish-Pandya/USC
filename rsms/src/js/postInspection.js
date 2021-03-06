angular.module('postInspections', ['sticky', 'ui.bootstrap', 'convenienceMethodWithRoleBasedModule', 'ngQuickDate', 'ngRoute', 'once', 'angular.filter', 'ui.tinymce'])
.filter('joinBy', function () {
    return function (input, delimiter) {
        return (input || []).join(delimiter || ',');
    };
})
.filter('toArray', function () {
    return function (obj, addKey) {
        if (!angular.isObject(obj)) return obj;
        if (addKey === false) {
            return Object.keys(obj).map(function (key) {
                return obj[key];
            });
        } else {
            return Object.keys(obj).map(function (key) {
                var value = obj[key];
                return angular.isObject(value) ?
                  Object.defineProperty(value, '$key', { enumerable: false, value: key }) :
          { $key: key, $value: value };
            });
        }
    };
})

//configure datepicker util
.config(function (ngQuickDateDefaultsProvider) {
    return ngQuickDateDefaultsProvider.set({
        closeButtonHtml: "<i class='icon-cancel-2'></i>",
        buttonIconHtml: "<i class='icon-calendar-2'></i>",
        nextLinkHtml: "<i class='icon-arrow-right'></i>",
        prevLinkHtml: "<i class='icon-arrow-left'></i>",
        // Take advantage of Sugar.js date parsing
        parseDateFunction: function (str) {
            console.log(str);
            return new Date(Date.parse(str));
        }
    });
})

.config(function ($routeProvider) {

    $routeProvider
    .when('/confirmation',
      {
          templateUrl: 'post-inspection-templates/inspectionConfirmation.html',
          controller: inspectionConfirmationController
      }
    )
    .when('/report',
      {
          templateUrl: 'post-inspection-templates/standardView.html',
          controller: inspectionReviewController
      }
    )

    .when('/details',
      {
          templateUrl: 'post-inspection-templates/inspectionDetails.html',
          controller: inspectionDetailsController
      }
    )
    .otherwise(
      { redirectTo: '/report' }
    );
})
.directive('nestedTable', ['$window', function ($window) {
    return {
        scope: { watched: "@" },
        restrict: 'C',
        link: function (scope, elem, attrs) {
            window.setTimeout(
                function () {
                    var td = elem.parents('td');
                    var h = td.next()[0].offsetHeight;
                    elem.css({ 'height': h });
                }, 10
            )

        }
    }
}])

.filter('isNegative', function () {
    return function (questions) {
        if (!questions) return;
        var matches = [];
        var i = questions.length;
        while (i--) {
            var push = false;
            if (questions[i].Responses && questions[i].Responses.Answer == 'no') {
                var j = questions[i].Responses.DeficiencySelections.length;
                while (j--) {
                    var def = questions[i].Responses.DeficiencySelections[j];
                    if (def.Deficiency.Text == 'Other') {
                        if (def.Is_active) push = true;
                    } else {
                        push = true;
                    }
                }

                var j = questions[i].Responses.SupplementalDeficiencies.length;
                while (j--) {
                    var def = questions[i].Responses.SupplementalDeficiencies[j];
                    if (def.Is_active) push = true;
                }
            }
            if (push) matches.push(questions[i]);
        }
        return matches;
    }
})
.factory('postInspectionFactory', function (convenienceMethods, $q) {

    var factory = {};
    var inspection = {};
    factory.recommendations = [];
    factory.observations = [];
    factory.modalData;

    factory.getInspectionData = function (url) {
        //return convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById3&id=132&callback=JSON_CALLBACK', this.onFailGet);
    };

    factory.getInspection = function () {
        return this.inspection;
    };

    factory.updateInspection = function () {
        //this should call convenienceMethods call to update an object on the server
    };

    factory.setInspection = function (inspection) {
        return this.inspection = inspection;
    };

    factory.saveCorrectiveAction = function (action) {
        var url = "../../ajaxaction.php?action=saveCorrectiveAction";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, action).then(
          function (promise) {
              deferred.resolve(promise);
          },
          function (promise) {
              deferred.reject(promise);
          }
        );
        return deferred.promise
    }

    factory.saveInspection = function (inspection, copy) {
        var url = "../../ajaxaction.php?action=saveInspection";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, copy).then(
          function (promise) {
              angular.extend(inspection, copy);
              deferred.resolve(promise);
          },
          function (promise) {
              deferred.reject(promise);
          }
        );
        return deferred.promise
    }

    factory.onFailGet = function () {
        return { 'data': error }
    }

    factory.deleteCorrectiveAction = function (def) {
        var url = "../../ajaxaction.php?action=deleteCorrectiveActionFromDeficiency";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, def).then(
          function (promise) {
              deferred.resolve(promise);
          },
          function (promise) {
              deferred.reject(promise);
          }
        );
        return deferred.promise
    }

    factory.organizeChecklists = function (checklists) {

        //set a checklists object that we can use elsewhere
        factory.checklists = checklists;

        //object with array properties to contain the checklists
        checklistHolder = {};
        checklistHolder.biologicalHazards = { name: "Biological Safety", checklists: [] };
        checklistHolder.chemicalHazards = { name: "Chemical Safety", checklists: [] };
        checklistHolder.radiationHazards = { name: "Radiation Safety", checklists: [] };
        checklistHolder.generalHazards = { name: "General Lab Safety", checklists: [] };

        //group the checklists by parent hazard
        //get the questions for each checklist and store them in a property that the view can access easily
        for (var i = 0; i < checklists.length; i++) {
            var checklist = checklists[i];
            checklist.masterOrder = i;
            if (checklist.Master_hazard.toLowerCase().indexOf('biological') > -1) {
                if (!checklistHolder.biologicalHazards.Questions) checklistHolder.biologicalHazards.Questions = [];
                checklistHolder.biologicalHazards.checklists.push(checklist);
                checklistHolder.biologicalHazards.Questions = checklistHolder.biologicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
            }
            else if (checklist.Master_hazard.toLowerCase().indexOf('chemical') > -1) {
                if (!checklistHolder.chemicalHazards.Questions) checklistHolder.chemicalHazards.Questions = [];
                checklistHolder.chemicalHazards.checklists.push(checklist);
                checklistHolder.chemicalHazards.Questions = checklistHolder.chemicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
            }
            else if (checklist.Master_hazard.toLowerCase().indexOf('radiation') > -1) {
                if (!checklistHolder.radiationHazards.Questions) checklistHolder.radiationHazards.Questions = [];
                checklistHolder.radiationHazards.checklists.push(checklist);
                checklistHolder.radiationHazards.Questions = checklistHolder.radiationHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
            }
            else if (checklist.Master_hazard.toLowerCase().indexOf('general') > -1) {
                if (!checklistHolder.generalHazards.Questions) checklistHolder.generalHazards.Questions = [];
                checklistHolder.generalHazards.checklists.push(checklist);
                checklistHolder.generalHazards.Questions = checklistHolder.generalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
            }
        }
        this.evaluateChecklistCategory(checklistHolder.biologicalHazards);
        this.evaluateChecklistCategory(checklistHolder.chemicalHazards);
        this.evaluateChecklistCategory(checklistHolder.radiationHazards);
        this.evaluateChecklistCategory(checklistHolder.generalHazards);

        return checklistHolder;
    };

    factory.getQuestionsByChecklist = function (checklist) {
        return checklist.Questions;
    }

    factory.evaluateChecklistCategory = function (category) {
        if (!category.Questions) {
            //there weren't any hazards in this category
            //hide the whole category
            //console.log(category.name+' had no hazards in these labs');
            category.message = false;
            category.show = false
        } else if (category.Questions.some(this.isAnsweredNo)) {
            //some questions are answered no
            //display as normal
            category.show = true;
            category.message = false;
        } else if (category.Questions.every(this.notAnswered)) {
            //console.log(category.name+' no questions were answered');
            //there were checklists but no questions were answered
            category.show = true;
            category.message = category.name + ' hazards were not evaluated during this laboratory inspection.';
        } else {
            //console.log(category.name+' there were no deficiencies');
            //there were no deficiencies
            category.show = true;
            category.message = 'No ' + category.name + ' deficiencies were identified during this laboratory inspection.';
        }

    }

    factory.isAnsweredNo = function (question) {
        if (question.Responses && question.Responses.Answer == 'no') return true;
        return false;
    }

    factory.notAnswered = function (question) {
        if (!question.Responses || question.Responses && !question.Responses.Answer) return true
        return false;
    }

    //set a matching view property for a mysql datetime property of an object
    factory.setDateForView = function (obj, dateProperty) {
        var dateHolder = convenienceMethods.getDate(obj[dateProperty]);
        obj['view' + dateProperty] = dateHolder;
        return obj;
    }

    factory.setDateForCalWidget = function (obj, dateProperty) {
        //console.log(obj);
        if (obj[dateProperty]) {
            console.log(dateProperty, obj[dateProperty]);
            var date = convenienceMethods.getDate(obj[dateProperty]);
            obj['view' + dateProperty] = new Date(date);
            return obj;
        }
    }

    factory.setDatesForServer = function (obj, dateProperty) {
        //by removing the string 'view' from the date property, we access the orginal MySQL datetime from which the property was set
        //i.e. corrective_action.viewPromised_date is the matching property to corrective_action.Promised_date
        if (!obj[dateProperty]) {
            obj[dateProperty] = new Date();
            return obj;
        }
        obj[dateProperty.replace('view', '')] = convenienceMethods.setMysqlTime(obj[dateProperty]);
        return obj;
    }

    //calculate the inspection's scores
    factory.calculateScore = function (inspection) {
        if (!inspection.score) inspection.score = {};
        inspection.score.itemsInspected = 0;
        inspection.score.deficiencyItems = 0;
        inspection.score.compliantItems = 0;
        angular.forEach(inspection.Checklists, function (checklist, key) {
            if (checklist.Is_active != false) {
                angular.forEach(checklist.Questions, function (question, key) {
                    if (question.Responses && question.Responses.Answer) {
                        inspection.score.itemsInspected++;
                        if (question.Responses && question.Responses.Answer && question.Responses.Answer == 'no') {
                            inspection.score.deficiencyItems++;
                            var i = question.Responses.DeficiencySelections.length;
                            while (i--) {
                                if (question.Responses.DeficiencySelections[i].CorrectiveActions.length) {
                                    factory.setDateForCalWidget(question.Responses.DeficiencySelections[i].CorrectiveActions[0], 'Completion_date');
                                    factory.setDateForCalWidget(question.Responses.DeficiencySelections[i].CorrectiveActions[0], 'Promised_date');
                                }
                            }
                        } else /*if(question.Responses && question.Responses.Answer)*/ {
                            inspection.score.compliantItems++;
                        }
                    }
                });
            }
        });

        //javascript does not believe that 0 is a number in spite of my long philosophical debates with it
        //if either compliantItems or itemsInspected is 0, we cannot calculate because they are undefined according to JS
        if (inspection.score.compliantItems && inspection.score.itemsInspected) {
            //we have both numbers, so we can calculate a score
            inspection.score.score = Math.round(parseInt(inspection.score.compliantItems) / parseInt(inspection.score.itemsInspected) * 100);
        } else {
            //since 0 is undefined, we se this property to the String "0"
            inspection.score.score = '0';
        }
        return this.inspection = inspection;
    }

    factory.setRecommendationsAndObservations = function () {

        var defer = $q.defer();

        var checklistLength = this.inspection.Checklists.length;

        for (var i = 0; i < checklistLength; i++) {

            var checklist = this.inspection.Checklists[i];

            var questions = checklist.Questions;
            var qLength = questions.length

            for (var j = 0; j < qLength; j++) {

                var question = questions[j];
                if (question.Responses && question.Responses.Recommendations) {
                    //now the time-wasting step of getting the question text for every recommendation.  this could be done by reference in the new orm framekwork

                    var recLen = question.Responses.Recommendations.length;

                    for (var k = 0; k < recLen; k++) {
                        question.Responses.Recommendations[k].Question = question.ChecklistName;
                    }

                    this.recommendations = this.recommendations.concat(question.Responses.Recommendations);
                }
                if (question.Responses && question.Responses.SupplementalRecommendations) {
                    //now the time-wasting step of getting the question text for every recommendation.  this could be done by reference in the new orm framekwork
                    var recLen = question.Responses.SupplementalRecommendations.length;

                    for (var k = 0; k < recLen; k++) {
                        question.Responses.SupplementalRecommendations[k].Question = question.ChecklistName;;
                    }

                    this.recommendations = this.recommendations.concat(question.Responses.SupplementalRecommendations);
                }

                if (question.Responses && question.Responses.Observations) {
                    this.observations = this.observations.concat(question.Responses.Observations);
                }
                if (question.Responses && question.Responses.SupplementalObservations) {
                    this.observations = this.observations.concat(question.Responses.SupplementalObservations);
                }

            }
        }

        defer.resolve();
        return defer.promise;
    }

    factory.getRecommendations = function () {
        return this.recommendations;
    }

    factory.getObservations = function () {
        return this.observations;
    }

    factory.getNumberOfRoomsForQuestionByChecklist = function (question) {
        var i = this.inspection.Checklists.length;
        while (i--) {
            if (question.Checklist_id == this.inspection.Checklists[i].Key_id) return this.inspection.Checklists[i].InspectionRooms.length;
        }
        return false;
    }

    factory.setModalData = function (data) {
        if(!data)factory.modalData = null;
        factory.modalData = convenienceMethods.copyObject(data);
    }

    factory.getModalData = function () {
        return factory.modalData;
    }


    factory.submitCap = function (inspection) {
        var inspectionDto = angular.copy(inspection);
        inspectionDto.Cap_submitted_date = convenienceMethods.setMysqlTime(Date());
        inspectionDto.Cap_submitter_id = GLOBAL_SESSION_USER.Key_id;

        var url = "../../ajaxaction.php?action=submitCAP&id=" + inspection.Key_id;
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url).then(
          function (promise) {
              deferred.resolve(promise);
          },
          function (promise) {
              deferred.reject(promise);
          }
        );
        return deferred.promise
    }

    factory.approveCAP = function (inspection) {
        var url = "../../ajaxaction.php?action=approveCAP&id=" + inspection.Key_id;
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url).then(
          function (promise) {
              deferred.resolve(promise);
          },
          function (promise) {
              deferred.reject(promise);
          }
        );
        return deferred.promise
    }

    factory.getHotWipes = function (inspection) {
        inspection.hotWipes = 0;
        if (!inspection.Inspection_wipe_tests[0]) return
        var i = inspection.Inspection_wipe_tests[0].Inspection_wipes.length;
        while (i--) {
            //if a wipe is 3 times background level, it is hot
            if (inspection.Inspection_wipe_tests[0].Inspection_wipes[i].Curie_level >= (inspection.Inspection_wipe_tests[0].Background_level * 3)) {
                //if the wipe has had a rewipe, and that rewipe is not 3 times the lab's background level, it is no longer hot
                if (!inspection.Inspection_wipe_tests[0].Lab_background_level || inspection.Inspection_wipe_tests[0].Inspection_wipes[i].Lab_curie_level >= (inspection.Inspection_wipe_tests[0].Lab_background_level * 3)) {
                    inspection.hotWipes++;
                }
            }
        }
    }

    factory.getIsReadyToSubmit = function (inspection) {
        var ready = {
            Class: 'LabInspectionStateDto',
            totals: 0, 
            pendings: 0, 
            completes:0, 
            correcteds: 0,
            uncorrecteds: 0,
            unSelectedSumplementals: [],
            noDefs: [],
            noDefIDS:[],
            unselectedIDS:[],
            readyToSubmit: false
        }

        if (!inspection) var inspection = factory.getInspection();
        var i = inspection.Checklists.length;
        while (i--) {
            var checklist = inspection.Checklists[i];
            var j = checklist.Questions.length;
            while (j--) {

                var question = checklist.Questions[j];
                if (question.Responses && question.Responses.Answer.toLowerCase() == "no") {
                    var k = question.Responses.DeficiencySelections.length;
                    while (k--) {
                        ready.totals++;
                        question.hasDeficiencies = true;
                        var selection = question.Responses.DeficiencySelections[k];
                        if (selection.CorrectiveActions && selection.CorrectiveActions.length && !selection.Corrected_in_inspection ) {
                            if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING) {
                                ready.pendings++;
                            } else if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.COMPLETE) {
                                ready.completes++;
                            }
                        } else if (selection.Corrected_in_inspection) {
                            ready.correcteds++;
                        }
                    }

                    var l = question.Responses.SupplementalDeficiencies.length;
                    while (l--) {
                        var selection = question.Responses.SupplementalDeficiencies[l];
                        if (selection.Is_active) {
                            ready.totals++;
                        } else {
                            if (ready.unselectedIDS.indexOf(question.Key_id) < 0) {
                                ready.unselectedIDS.push(question.Key_id);
                                ready.unSelectedSumplementals.push({ checklist: checklist.Name, question: question.Text });
                            }
                        }
                        question.hasDeficiencies = true;
                        if (selection.Is_active && selection.CorrectiveActions && selection.CorrectiveActions.length && !selection.Corrected_in_inspection) {
                            if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING) {
                                ready.pendings++;
                            } else if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.COMPLETE) {
                                ready.completes++;
                            }
                        } else if (selection.Corrected_in_inspection) {
                            ready.correcteds++;
                        }
                    }

                    //question is answered "No" with no Defiency or SupplementalDeficiency selectd
                    if (ready.noDefIDS.indexOf(question.Key_id) < 0 &&
                        (!question.Responses.DeficiencySelections || !question.Responses.DeficiencySelections.length)
                        && (!question.Responses.SupplementalDeficiencies || !question.Responses.SupplementalDeficiencies.length)) {
                        ready.noDefIDS.push(question.Key_id);
                        ready.noDefs.push({ question_id: question.Key_id, checklist: checklist.Name, question: question.Text });
                    }
                    
                }

            }
        }

        if (ready.pendings + ready.completes + ready.correcteds >= ready.totals || ready.totals == 0) {
            ready.readyToSubmit = true;
        }

        ready.uncorrecteds = ready.totals - (ready.pendings + ready.completes + ready.correcteds);

        return ready;
    }

    return factory;
});

mainController = function ($scope, $location, postInspectionFactory, convenienceMethods, $rootScope, roleBasedFactory, $modal, $q) {
    $scope.route = $location.path();
    $scope.loc = $location.search();

    $scope.setRoute = function (route) {
        $location.path(route);
        $scope.route = route;
    }

    $rootScope.rbf = roleBasedFactory;

    $scope.allowReturnToInspection = function allowReturnToInspection( inspection ){
        // Disallow returning-to-inspection for closed-out inspections
        return inspection.Status != Constants.INSPECTION.STATUS.CLOSED_OUT;
    }

    // Lab Contact Verification - pre-Finalization requirement
    $scope.openUserHub = function openUserHub(){
        return window.open(window.GLOBAL_WEB_ROOT + 'views/hubs/UserHub.php#/labPersonnel');
    }

    $scope.showEditPersonnelModal = function showEditPersonnelModal(){
        var modalData = {
            inspection: $scope.inspection
        }

        postInspectionFactory.setModalData(modalData);

        // Do Lab Contacts require updating?
        var modalInstance = $modal.open({
            templateUrl: 'post-inspection-templates/confirm-inspection-contacts-modal.html',
            controller: modalCtrl
        });

        $scope.contactsWillBeUpdated = $q.defer();

        modalInstance.result.then(
            function(){
                // Yes, contacts should be updated

                // Flag that we're editing contacts
                $scope.editingContacts = true;
                $scope._editingContactsStatus = 'Contacts are being edited in a new window';

                var userhub = $scope.openUserHub();

                userhub.onbeforeunload = function(){
                    console.debug("User Hub window has closed");
                    $scope._editingContactsStatus = 'Loading changes to Inspection Contacts';

                    // Retrieve new Inspection contacts
                    var onFailedLoad = function(){
                        $scope._editingContactsStatus = "Failed to load Inspection updated. Reload the page and try again.";
                        $scope.contactsWillBeUpdated.reject();
                    };

                    convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=' + $scope.inspection.Key_id + '&callback=JSON_CALLBACK', onFailedLoad)
                        .then(function (promise) {
                            var updatedInspection = promise.data;
                            console.debug("Retrieved updated Inspection details:", updatedInspection);

                            // Update contact details in our local inspection
                            $scope.inspection.LabPersonnel = updatedInspection.LabPersonnel;
                            console.debug("Updated lab personel in inspection: ", $scope.inspection);

                            $scope._editingContactsStatus = 'Inspection Contact changes loaded';
                            $scope.contactsWillBeUpdated.resolve();
                        })
                        .then(function() {
                            $scope.editingContacts = false;
                        });
                };
            },
            function(){
                // No, contacts are correct
                $scope.contactsWillBeUpdated.resolve();
            });

            // Return deferred promise for chaining
            return $scope.contactsWillBeUpdated.promise;
    };
}
inspectionDetailsController = function ($scope, $location, $anchorScroll, convenienceMethods, postInspectionFactory, $rootScope) {
    function init() {
        if ($location.search().inspection) {
            var id = $location.search().inspection;
            if (!postInspectionFactory.getInspection()) {
                $scope.doneLoading = false;
                convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=resetChecklists&id=' + id + '&report=true&callback=JSON_CALLBACK', onFailGetInspeciton)
                  .then(function (promise) {
                      //console.log(promise.data);

                      //set the inspection date as a javascript date object
                      if (promise.data.Date_started) promise.data = postInspectionFactory.setDateForView(promise.data, "Date_started");
                      $scope.inspection = promise.data;
                      $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
                      $scope.doneLoading = true;
                      // call the manager's setter to store the inspection in the local model
                      postInspectionFactory.setInspection($scope.inspection);
                      postInspectionFactory.setRecommendationsAndObservations()
                          .then(
                            function () {
                                $scope.recommendations = postInspectionFactory.getRecommendations();
                            });


                      $scope.doneLoading = true;
                      //postInspection factory's organizeChecklists method will return a list of the checklists for this inspection
                      //organized by parent hazard
                      //each group of checklists will have a Questions property containing all questions for each checklist in a given category
                      $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);

                      //console.log($scope.questionsByChecklist);
                  });
            } else {
                $scope.inspection = postInspectionFactory.getInspection();
                $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
                $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);
                $scope.doneLoading = true;
            }
            $scope.options = [Constants.CORRECTIVE_ACTION.STATUS.INCOMPLETE, Constants.CORRECTIVE_ACTION.STATUS.PENDING, Constants.CORRECTIVE_ACTION.STATUS.COMPLETE];
        } else {
            $scope.error = 'No inspection has been specified';
        }
    }
    init();


    function onFailGetInspeciton() {
        $scope.doneLoading = true;
        $scope.error = "The system couldn't find this inspection.";
        $scope.errorCauses = [
            "Requesting an inspection you do not have access to view (did you follow a link to someone else's inspection?)",
            "Requesting an inspection that does not exist (did you type in the URL by hand?)",
            "Unstable internet connection"
        ];
    }

    $scope.someAnswers = function (checklist) {
        if (checklist.Questions.some(isAnswered)) return true;
        return false;
    }

    function isAnswered(question) {
        if (question.Responses && (question.Responses.Answer || question.Responses.Recommendations.length)) return true;
        return false;
    }


}

inspectionConfirmationController = function ($scope, $location, $anchorScroll, convenienceMethods, postInspectionFactory, $rootScope, $q) {
    $scope.confirmEmailTinymceOptions = {
        branding: false,
        plugins: ['link lists', 'autoresize', 'contextmenu'],
        contextmenu_never_use_native: true,
        toolbar: 'bold | italic | underline | link | lists | bullist | numlist',
        menubar: false,
        elementpath: false,
        content_style: "p,ul li, ol li {font-size:14px}"
    };

    // Retrieve Inspection
    var inspectionWillLoad = $q.defer();

    if ($location.search().inspection) {
        var id = $location.search().inspection;

        if (!postInspectionFactory.getInspection()) {
            // Load Inspection
            convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=' + id + '&callback=JSON_CALLBACK', onFailGetInspeciton)
              .then(function (promise) {
                  $rootScope.inspection = promise.data;

                  if (promise.data.Date_started){
                      promise.data = postInspectionFactory.setDateForView(promise.data, "Date_started");
                  }

                  postInspectionFactory.setInspection($rootScope.inspection);

                  inspectionWillLoad.resolve();
              });
        }
        else{
            // Inspection is already loaded
            inspectionWillLoad.resolve();
        }

        // Load Inspection Email Message
        var templateWillLoad = $q.defer();

        // Get all data
        $scope.doneLoading = false;
        inspectionWillLoad.promise
            // Confirm that Lab Contacts are correct before preparing email
            .then( function(){
                return $scope.showEditPersonnelModal();
            })

            .then( function(){
                $scope.loadingEmail = true;

                // TODO: Move inspection stat collection server-side
                var inspinfo = postInspectionFactory.getIsReadyToSubmit();

                var templateLoaded = function(data){
                    console.debug(data);

                    $scope.inspectionEmailContext = data;

                    console.debug("Loaded inspection report email template:", $scope.inspectionEmailContext);
                    templateWillLoad.resolve(data);
                };

                var templateFailed = function(){
                    console.error("Failed to load email template");
                    templateWillLoad.reject();
                };

                // Load default email content, call callback
                convenienceMethods.getDataFromPostRequest(
                    '../../ajaxaction.php?action=getInspectionReportEmail&id=' + id,
                    inspinfo,
                    templateLoaded,
                    templateFailed);

                return templateWillLoad.promise;
            })
            .then( function(){
                // Prepare View
                $scope.others = [{ email: '' }];
                $scope.defaultNote = {};
                $scope.inspection = postInspectionFactory.getInspection();

                // Set the default email text
                $scope.defaultNote.Text = $scope.inspectionEmailContext.Email.Body;
            })
            .then(function(){
                // Stop loading
                $scope.loadingEmail = false;
                $scope.doneLoading = true;
            });
    } else {
        $scope.error = 'No inspection has been specified';
    }

    function onFailGetInspeciton() {
        $scope.doneLoading = true;
        $scope.error = "The system couldn't find this inspection.";
        $scope.errorCauses = [
            "Requesting an inspection you do not have access to view (did you follow a link to someone else's inspection?)",
            "Requesting an inspection that does not exist (did you type in the URL by hand?)",
            "Unstable internet connection"
        ];
    }

    $scope.contactList = [];

    $scope.sendEmail = function () {

        othersToSendTo = [];

        angular.forEach($scope.others, function (other, key) {
            othersToSendTo.push(other.email);
        });

        var contactList = [];

        if ($scope.inspection.PrincipalInvestigator.User.include) {
            contactList.push($scope.inspection.PrincipalInvestigator.User.Key_id);
        }

        $scope.inspection.LabPersonnel
            .filter( p => p.include )
            .forEach(p => contactList.push(p.Key_id));

        var emailDto = {
            Class: "EmailDto",
            Entity_id: $scope.inspection.Key_id,
            Recipient_ids: contactList,
            Other_emails: othersToSendTo,
            Text: $scope.defaultNote.Text
        }


        if( $scope.inspectionEmailContext.Email.Body == emailDto.Text ){
            // If no change was made to the email body, omit it and let the tamplating engine generate it
            console.debug("No changes were made to email Body");
            emailDto.Text = undefined;
        }

        console.log(emailDto);

        // Clone the context and replace the email preview with DTO
        var dto = angular.copy($scope.inspectionEmailContext);
        dto.Email = emailDto;

        var url = '../../ajaxaction.php?action=sendInspectionEmail';
        convenienceMethods.sendEmail(dto, onSendEmail, onFailSendEmail, url);
        $scope.sending = true;
    }

    function onSendEmail(data) {
        $scope.sending = false;
        $scope.emailSent = 'success';
        console.log(data);
        //postInspectionFactory.inspection.Notification_date =
        if (!postInspectionFactory.inspection.Notification_date) postInspectionFactory.inspection.Notification_date = convenienceMethods.setMysqlTime(Date());
        if (evaluateCloseInspection() == true) {
            setInspectionClosed();
        }

    }

    function onFailSendEmail() {
        $scope.sending = false;
        $scope.emailSent = 'error';
        alert('There was a problem when the system tried to send the email.');
    }


    function evaluateCloseInspection() {
        var setCompletedDate = true;
        console.log(postInspectionFactory.inspection.Checklists.length);
        //return false;
        var i = postInspectionFactory.inspection.Checklists.length;
        while (i--) {
            var checklist = postInspectionFactory.inspection.Checklists[i];
            var j = checklist.Questions.length;
            while (j--) {
                var question = checklist.Questions[j];
                if (question.Responses && question.Responses.DeficiencySelections) {
                    var k = question.Responses.DeficiencySelections.length;
                    while (k--) {
                        if (!question.Responses.DeficiencySelections[k].Corrected_in_inspection) {
                            console.log(question);
                            return false;
                        }
                    }
                }

                if (question.Responses && question.Responses.SupplementalDeficiencies) {
                    var k = question.Responses.SupplementalDeficiencies.length;
                    while (k--) {
                        if (!question.Responses.SupplementalDeficiencies[k].Corrected_in_inspection) {
                            console.log(question);
                            return false;
                        }
                    }
                }

            }
        }
        return true;
    }

    function setInspectionClosed() {
        var inspectionDto = {
            Date_closed: convenienceMethods.setMysqlTime(Date()),
            Key_id: postInspectionFactory.inspection.Key_id,
            Principal_investigator_id: postInspectionFactory.inspection.Principal_investigator_id,
            Date_started: postInspectionFactory.inspection.Date_started,
            Is_rad: postInspectionFactory.inspection.Is_rad,
            Notification_date: convenienceMethods.setMysqlTime(Date()),
            Schedule_month: postInspectionFactory.inspection.Schedule_month,
            Schedule_year: postInspectionFactory.inspection.Schedule_year,
            Cap_submitted_date: postInspectionFactory.inspection.Cap_submitted_date,
            Cap_complete: postInspectionFactory.inspection.Cap_complete,
            Class: "Inspection"
        };
        console.log(inspectionDto);
        var url = "../../ajaxaction.php?action=saveInspection";
        convenienceMethods.updateObject(inspectionDto, null, onSetInspectionClosed, onFailSetInspecitonClosed, url);
    }

    function onSetInspectionClosed(data) {
        //console.log('saved');
        data.Checklists = angular.copy($rootScope.Checklists);
        $rootScope.inspection = data;
        $rootScope.inspection.closed = true;
        $scope.inspection = $rootScope.inspection;
        //console.log($rootScope.inspection);
    }

    function onFailSetInspecitonClosed() {
        alert("There was an issue when the system tried to set the Inpsection's closeout date");
    }

}

inspectionReviewController = function ($scope, $location, convenienceMethods, postInspectionFactory, $rootScope, $modal, roleBasedFactory, $q) {
    $scope.getNumberOfRoomsForQuestionByChecklist = postInspectionFactory.getNumberOfRoomsForQuestionByChecklist;
    function init() {
        if ($location.search().inspection) {
            var id = $location.search().inspection;
            $scope.pf = postInspectionFactory;
            if (!postInspectionFactory.getInspection()) {
                $scope.doneLoading = false;
                convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=resetChecklists&id=' + id + '&report=true&callback=JSON_CALLBACK', onFailGetInspeciton)
                  .then(function (promise) {

                      //if this is a radiation inspection, find any hot InspectionWipes
                      if (promise.data.Is_rad) postInspectionFactory.getHotWipes(promise.data);

                      //set the inspection date as a javascript date object
                      if (promise.data.Date_started) promise.data = postInspectionFactory.setDateForView(promise.data, "Date_started");
                      $rootScope.inspection = postInspectionFactory.calculateScore(promise.data);
                      // call the manager's setter to store the inspection in the local model
                      postInspectionFactory.setInspection($scope.inspection);
                      postInspectionFactory.setRecommendationsAndObservations()
                          .then(
                            function () {                                
                                $scope.recommendations = postInspectionFactory.getRecommendations();
                                if (postInspectionFactory.getIsReadyToSubmit($scope.inspection).unSelectedSumplementals.length || postInspectionFactory.getIsReadyToSubmit($scope.inspection).noDefs.length) {
                                    var modalData = {
                                        inspection:$scope.inspection,
                                        uncheckeds: postInspectionFactory.getIsReadyToSubmit($scope.inspection).unSelectedSumplementals
                                    }
                                    postInspectionFactory.setModalData(modalData);
                                    var modalInstance = $modal.open({
                                        templateUrl: 'post-inspection-templates/unselected-supplemental-deficiencies.html',
                                        controller: modalCtrl
                                    });
                                }
                            });

                      //turn off the loading spinner
                      $scope.doneLoading = true;
                      //postInspection factory's organizeChecklists method will return a list of the checklists for this inspection
                      //organized by parent hazard
                      //each group of checklists will have a Questions property containing all questions for each checklist in a given category
                      $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($rootScope.inspection.Checklists);



                  });
            } else {
                $scope.inspection = postInspectionFactory.getInspection();
                $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
                $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);
                $scope.doneLoading = true;
                postInspectionFactory.getHotWipes($scope.inspection);
                
            }
            $scope.options = [Constants.CORRECTIVE_ACTION.STATUS.INCOMPLETE, Constants.CORRECTIVE_ACTION.STATUS.PENDING, Constants.CORRECTIVE_ACTION.STATUS.COMPLETE];
        } else {
            $scope.error = 'No inspection has been specified';
        }
    }
    init();


    function onFailGetInspeciton() {
        $scope.doneLoading = true;
        $scope.error = "The system couldn't find this inspection.";
        $scope.errorCauses = [
            "Requesting an inspection you do not have access to view (did you follow a link to someone else's inspection?)",
            "Requesting an inspection that does not exist (did you type in the URL by hand?)",
            "Unstable internet connection"
        ];
    }

    //parse function to ensure that users cannot set the date for a corrective action before the date of the inspection
    $scope.afterInspection = function (d) {
        var calDate = Date.parse(d);
        //inspection date pased into seconds minus the number of seconds in a day.  We subtract a day so that the inspection date will return true
        var inspectionDate = Date.parse($scope.inspection.viewDate_started) - 864000;
        var now = new Date();
        if (calDate >= inspectionDate && calDate <= now) {
            return true;
        }
        return false;
    }

    $scope.userCanEditInspectionPersonnel = function userCanEditInspectionPersonnel(){
        return $scope.userCanEditInspectionDate();
    };

    $scope.userCanEditInspectionDate = function userCanEditInspectionDate(){
        //Does this user have the roles to edit the date?
        if( GLOBAL_SESSION_ROLES && GLOBAL_SESSION_ROLES.userRoles ){
            var _roles = [
                Constants.ROLE.NAME.ADMIN,
                Constants.ROLE.NAME.SAFETY_INSPECTOR,
            ];
            for( var i = 0; i < _roles.length; i++){
                if( GLOBAL_SESSION_ROLES.userRoles.includes(_roles[i]) ){
                    return true;
                }
            }
        }

        return false;
    };

    $scope.updateInspectionDate = function () {
        if( !$scope.userCanEditInspectionDate() ){
            return;
        }

        var inspectionDto = angular.copy($scope.inspection);
        inspectionDto.Date_started = convenienceMethods.setMysqlTime($scope.inspection.view_Date_started);
        console.log(inspectionDto);
        $scope.saving = postInspectionFactory.saveInspection($scope.inspection, inspectionDto).then(function (i) { $scope.inspection.Date_started = i.Date_started; $scope.inspection.editDate = false; });
    }
    $scope.todayOrAfter = function (d) {
        var calDate = Date.parse(d);
        //today's date parsed into seconds minus the number of seconds in a day.  We subtract a day so that today's date will return true
        var now = new Date(),
        then = new Date(
            now.getFullYear(),
            now.getMonth(),
            now.getDate(),
            0, 0, 0),
        diff = now.getTime() - then.getTime()

        var today = Date.parse(now) - diff;
        if (calDate >= today) {
            return true;
        }
        return false;
    }

    $scope.saveCorrectiveAction = function (def) {
        def.CorrectiveActionCopy.isDirty = true;

        //if this is a new corrective action (we are not editing one), we set it's class and Deficiency_selection_id properties
        if (def.Class == "Deficiency") {
            if (!def.CorrectiveActionCopy.Deficiency_selection_id) def.CorrectiveActionCopy.Deficiency_selection_id = def.Key_id;
        } else {
            if (!def.CorrectiveActionCopy.Supplemental_deficiency_id) def.CorrectiveActionCopy.Supplemental_deficiency_id = def.Key_id;
        }
        if (!def.CorrectiveActionCopy.Class) def.CorrectiveActionCopy.Class = "CorrectiveAction";

        //parse the dates for MYSQL
        if (def.CorrectiveActionCopy.viewCompletion_date) def.CorrectiveActionCopy = postInspectionFactory.setDatesForServer(def.CorrectiveActionCopy, "viewCompletion_date");
        if (def.CorrectiveActionCopy.viewPromised_date) def.CorrectiveActionCopy = postInspectionFactory.setDatesForServer(def.CorrectiveActionCopy, "viewPromised_date");
        console.log(def.CorrectiveActionCopy);

        var test = postInspectionFactory.saveCorrectiveAction(def.CorrectiveActionCopy).then(
          function (promise) {

              if (promise.Completion_date) {
                  promise = postInspectionFactory.setDateForView(promise, "Completion_date");
              }

              if (promise.Promised_date) {
                  promise = postInspectionFactory.setDateForView(promise, "Promised_date");
              }

              def.CorrectiveActionCopy.isDirty = false;
              def.CorrectiveActionCopy = angular.copy(promise);
              def.CorrectiveActions[0] = angular.copy(promise);
              postInspectionFactory.setInspection($scope.inspection);
              $scope.data = postInspectionFactory.getIsReadyToSubmit()
          },
          function (promise) {
              def.error = 'There was a promblem saving the Corrective Action';
              def.CorrectiveActionCopy.isDirty = false;
          }
        );
    }

    $scope.setCorrectiveActionCopy = function (def) {
        def.CorrectiveActionCopy = angular.copy(def.CorrectiveActions[0]);
    }

    $scope.setViewDate = function (date) {
        // if(!date)return convenienceMethods.getDate(convenienceMethods.setMysqlTime(Date()));
        //console.log(new Date(date));
        return new Date(date);
    }

    function answerIsNotNo(answer) {
        if (answer != no) return true;
        return false;
    }

    $scope.openModal = function (question, def) {
        var modalData = {
            question: question,
            deficiency: def
        }
        postInspectionFactory.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'post-inspection-templates/corrective-action-modal.html',
            controller: modalCtrl
        });

        modalInstance.result.then(function (returnedCA) {
            if (def.CorrectiveActions.length && def.CorrectiveActions[0].Key_id) {
                angular.extend(def.CorrectiveActions[0], returnedCA);
            } else {
                def.CorrectiveActions.push(returnedCA);
            }
            $scope.data = postInspectionFactory.getIsReadyToSubmit();
            if (postInspectionFactory.getIsReadyToSubmit().readyToSubmit) {
                $scope.data = postInspectionFactory.getIsReadyToSubmit();
                console.log($scope.data)
                var modalInstance = $modal.open({
                    templateUrl: 'post-inspection-templates/submit-cap.html',
                    controller: modalCtrl
                });
                modalInstance.result.then(function (closed) {
                    $scope.capSubmitted(closed);
                    $scope.data = postInspectionFactory.getIsReadyToSubmit();
                })
            }
            
        });
    }

    $scope.capSubmitted = function (closed) {
        if (closed) {
            var modalInstance = $modal.open({
                templateUrl: 'post-inspection-templates/cap-submitted.html',
                controller: modalCtrl
            });
            modalInstance.result.then(function(){
                location.reload();
            });
        }
    }

    $scope.openDeleteModal = function (def) {
        var modalData = {
            deficiency: def
        }
        postInspectionFactory.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'post-inspection-templates/confirm-delete-corrective-action.html',
            controller: modalCtrl
        });

        modalInstance.result.then(function (returnedDef) {
            def.CorrectiveActions = [];
        });
    }

    $scope.submit = function () {
        var modalInstance = $modal.open({
            templateUrl: 'post-inspection-templates/submit-cap.html',
            controller: modalCtrl
        });

        modalInstance.result.then(function (returnedInspection) {
            $scope.readyToSubmit = false;
            angular.extend($rootScope.inspection, returnedInspection);
            postInspectionFactory.setInspection($rootScope.inspection);
        });
    }

    $scope.getPendingCorrectiveActions = function getPendingCorrectiveActions( inspection ){
        // Reduce the checklists to any Pending CorrectiveActions
        return inspection.Checklists.reduce((acc, chk) => {
            if( !acc ){
                acc = [];
            }
            chk.Questions.forEach( q => {
                if( q.Responses ){
                    q.Responses.DeficiencySelections
                        .concat(q.Responses.SupplementalDeficiencies)
                        .forEach(ds => ds.CorrectiveActions
                            .filter(ca => ca.Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING)
                            .forEach(ca => acc.push(ca)));
                }
            });

            return acc;
        }, []);
    }

    $scope.approveCAP = function( inspection ){
        // Ask for confirmation if required
        $scope._confirmApproveCAP( inspection )
        .then(function () {
            $scope.handlingApproveCap = true;
            postInspectionFactory.approveCAP(inspection).then(function () {
                //...
                location.reload();
            });
        });
    }

    $scope._confirmApproveCAP = function (inspection) {
        var deferred = $q.defer();
        // Check if there are Pending actions
        var pendingActions = $scope.getPendingCorrectiveActions(inspection);
        var requireApproval = pendingActions.length;

        if( requireApproval ){
            console.debug("Inspection approval requires user confirmation");
            postInspectionFactory.setModalData({
                inspection: inspection,
                pendingActions: pendingActions
            });

            var modalInstance = $modal.open({
                templateUrl: 'post-inspection-templates/confirm-approve-pending-cap-modal.html',
                controller: modalConfirmCtrl
            });

            modalInstance.result.then(
                function (inspection) {
                    console.log("User confirmed CAP approval");
                    deferred.resolve();
                },
                function(err){
                    console.log("User rejected CAP approval");
                    deferred.reject();
                }
            );
        }
        else {
            console.debug("No confirmation required for inspection approval");
            // No approval required
            deferred.resolve();
        }

        return deferred.promise;
    }

    $scope.handleInspectionOpen = function (inspection, isReopen) {
        $scope.handlingInspectionOpen = true;
        var inspectionDto = {
            Date_created: inspection.Date_created,
            Date_closed: inspection.Date_closed || isReopen ? null : convenienceMethods.setMysqlTime(Date()),
            Key_id: postInspectionFactory.inspection.Key_id,
            Principal_investigator_id: postInspectionFactory.inspection.Principal_investigator_id,
            Date_started: postInspectionFactory.inspection.Date_started,
            Notification_date: postInspectionFactory.inspection.Notification_date || convenienceMethods.setMysqlTime(Date()),
            Schedule_month: postInspectionFactory.inspection.Schedule_month,
            Schedule_year: postInspectionFactory.inspection.Schedule_year,
            Cap_submitted_date: isReopen ? null : postInspectionFactory.inspection.Cap_submitted_date,
            Cap_submitter_id: postInspectionFactory.inspection.Cap_submitter_id,
            Cap_complete: isReopen ? null : postInspectionFactory.inspection.Cap_complete,
            Is_rad: postInspectionFactory.inspection.Is_rad,
            Class: "Inspection"
        };
        postInspectionFactory.saveInspection(inspection, inspectionDto).then(function () {
            $scope.handlingInspectionOpen = false;
            //...
            location.reload();
        });
    }

    

    $scope.complete = function (action) {
        var copy = convenienceMethods.copyObject(action);
        action.dirty = true;
        copy.Completion_date = convenienceMethods.setMysqlTime(Date());
        copy.Status = Constants.CORRECTIVE_ACTION.STATUS.COMPLETE;
        $scope.error = ''
        //call to factory to save, return, then close modal, passing data back
        postInspectionFactory.saveCorrectiveAction(copy)
          .then(
            function (returnedAction) {
                action.dirty = false;
                angular.extend(action, returnedAction);
            },
            function () {
                action.dirty = false;
                $scope.error = "The corrective action could not be saved.  Please check your internet connection and try again."
            }
          )
    }

    $scope.hasNegativeRespones = function (questions) {
        if (!questions || questions.length == 0) return false;
        var i = questions.length;
        while (i--) {
            if (questions[i].Responses && questions[i].Responses.Answer == 'no') return true;
        }
        return false;
    }

    $scope.closeOut = function () {
        $scope.dirty = true;
        $scope.closing = postInspectionFactory.submitCap($rootScope.inspection)
          .then(
            function (inspection) {
                $rootScope.inspection.Cap_submitted_date = inspection.Cap_submitted_date;
                $scope.dirty = false;
                $scope.capSubmitted(true);

            },
            function () {
                $scope.validationError = "The CAP could not be submitted.  Please check your internet connection and try again."
                $scope.dirty = false;
            }
          );
    }

    $scope.getDeficiencyCAPControls = function getDeficiencyCAPControls(inspection, def){
        var ctrls = {
            edit: false,
            delete: false
        };

        // check user role
        var isAdmin = roleBasedFactory.getHasPermission([
            $rootScope.R[Constants.ROLE.NAME.ADMIN],
            $rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN],
            $rootScope.R[Constants.ROLE.NAME.SAFETY_INSPECTOR],
            $rootScope.R[Constants.ROLE.NAME.RADIATION_INSPECTOR]
        ]);

        switch( inspection.Status ){
            case Constants.INSPECTION.STATUS.CLOSED_OUT:
                // No edits allowed after approval
                ctrls.edit = isAdmin;
                ctrls.delete = isAdmin;
                break;

            case Constants.INSPECTION.STATUS.SUBMITTED_CAP:
                // Allow only Edit for non-admin; Allow all for Admin
                ctrls.edit = true;
                ctrls.delete = isAdmin;
                break;

            default:
                // Allow all CAP controls
                ctrls.edit = true;
                ctrls.delete = true;
                break;
        }

        return ctrls;
    }
}

modalConfirmCtrl = function($scope, $modalInstance, postInspectionFactory){
    var data = postInspectionFactory.getModalData();
    $scope.inspection = data.inspection;
    $scope.pendingActions = data.pendingActions;
    $scope.pendingActionCount = data.pendingActions.length;

    $scope.confirm = function(){
        $modalInstance.close();
    }

    $scope.cancel = function () {
        $modalInstance.dismiss();
    }
}

modalCtrl = function ($scope, $location, convenienceMethods, postInspectionFactory, $rootScope, $modalInstance) {
    var data = postInspectionFactory.getModalData();
    $scope.options = [{ Value: Constants.CORRECTIVE_ACTION.STATUS.PENDING, Label: "Corrective action will be completed soon" }, { Value: Constants.CORRECTIVE_ACTION.STATUS.COMPLETE, Label: "Corrective action completed" }];
    $scope.validationError = '';

    $scope.tinymceOptions = {
        plugins: '',
        toolbar: 'bold | italic | underline',
        menubar: false,
        elementpath: false,
        content_style: "p {font-size:14px}"
    };

    $scope.data = postInspectionFactory.getIsReadyToSubmit();
    console.log($scope.data);
    if (data != null) {
        if (data.inspection) $scope.inspection = data.inspection;
        $scope.question = data.question || null;
        $scope.def = data.deficiency || null;
        $scope.dates = {};

        if ($scope.def && $scope.def.CorrectiveActions && $scope.def.CorrectiveActions[0]) {
            $scope.copy = {};
            for (var prop in $scope.def.CorrectiveActions[0]) {
                $scope.copy[prop] = $scope.def.CorrectiveActions[0][prop];
            }
            console.log($scope.def, $scope.copy);

        } else if ($scope.def) {
            $scope.copy = {
                Class: "CorrectiveAction",
                Is_active: true,
                Text: "",
                Deficiency_selection_id: $scope.def.Class == "DeficiencySelection" ? $scope.def.Key_id : null,
                Supplemental_deficiency_id: $scope.def.Class == "SupplementalDeficiency" ? $scope.def.Key_id : null,
            }
        }

        if ($scope.copy && $scope.copy.Promised_date)
            $scope.dates.promisedDate = convenienceMethods.getDate($scope.copy.Promised_date);
        if ($scope.copy && $scope.copy.Completion_date)
            $scope.dates.completionDate = convenienceMethods.getDate($scope.copy.Completion_date);

        $scope.closeOut = function () {
            $scope.dirty = true;
            $scope.closing = postInspectionFactory.submitCap($rootScope.inspection)
              .then(
                function (inspection) {
                    $rootScope.inspection.Cap_submitted_date = inspection.Cap_submitted_date;
                    $scope.dirty = false;
                    $modalInstance.close(true);
                },
                function () {
                    $scope.validationError = "The CAP could not be submitted.  Please check your internet connection and try again."
                    $scope.dirty = false;
                }
              );
        }
    }

    $scope.validateCorrectiveAction = function (action) {
        errorObject = null;
        if (!action) {
            errorObj = { formBlank: true }
        } else {
            errorObj = {
                statusError: action.Status == null,
                textError: action.Text == null || action.Text == "",
                dateError: function () {
                    if (!action.Status || !action.Text || action.Text == "") return false;
                    if (action.Status == Constants.CORRECTIVE_ACTION.STATUS.COMPLETE) {
                        console.log($scope.dates, action);
                        return !action.Completion_date && !$scope.dates.completionDate;
                    } else if (action.Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING) {
                        return (!action.Promised_date && !$scope.dates.promisedDate) && (!action.Needs_ehs && !action.Needs_facilities && !action.Insuficient_funds && !action.Other);
                    }
                }(),
                otherTextError: action.Other && !action.Other_reason
            }
        }
        return $scope.validationError = errorObj;
    }

    $scope.clearValidationError = function () {
        $scope.validationError = {};
    }

    $scope.saveCorrectiveAction = function (copy, orig) {

        if ($scope.dates.promisedDate) copy.Promised_date = convenienceMethods.setMysqlTime($scope.dates.promisedDate);
        if ($scope.dates.completionDate) copy.Completion_date = convenienceMethods.setMysqlTime($scope.dates.completionDate);

        //custom validation, because the validation is complex
        $scope.validationError = $scope.validateCorrectiveAction(copy);
        for (var prop in $scope.validationError) {
            if ($scope.validationError[prop]) return false;
        }

        if (!copy.Other) copy.Other_reason = null;

        $scope.dirty = true;

        //call to factory to save, return, then close modal, passing data back
        postInspectionFactory.saveCorrectiveAction(copy)
          .then(
            function (returnedAction) {
                $scope.dirty = false;
                $modalInstance.close(returnedAction);
            },
            function () {
                $scope.dirty = false;
                $scope.validationError = "The corrective action could not be saved.  Please check your internet connection and try again."
            }
          )
    }

    $scope.deleteCorrectiveAction = function (def) {
        $scope.dirty = true;
        $scope.validationError = ''
        //call to factory to save, return, then close modal, passing data back
        postInspectionFactory.deleteCorrectiveAction(def)
          .then(
            function (returnedDef) {
                $scope.dirty = false;
                $modalInstance.close(returnedDef);
            },
            function () {
                $scope.dirty = false;
                $scope.validationError = "The corrective action could not be removed.  Please check your internet connection and try again."
            }
          )
    }

    $scope.closeModal = function(){
        $modalInstance.close();
    }

    $scope.cancel = function () {
        console.log($scope.modalData, "asdf")
        $modalInstance.dismiss();
    }

    //parse function to ensure that users cannot set the date for a corrective action before the date of the inspection
    $scope.afterInspection = function (d) {
        var calDate = moment(d);
        //inspection date pased into seconds minus the number of seconds in a day.  We subtract a day so that the inspection date will return true
        var inspectionDate = moment(postInspectionFactory.getInspection().Date_started).startOf('day');
        var now = moment();
        return calDate >= inspectionDate && calDate <= now;
    }

    $scope.todayOrAfter = function (d) {
        return moment(d) >= moment().startOf('day');
    }
}

