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
        console.log(dateHolder);
        obj['view' + dateProperty] = dateHolder;
        return obj;
    }

    factory.setDateForCalWidget = function (obj, dateProperty) {
        //console.log(obj);
        if (obj[dateProperty]) {
            obj['view' + dateProperty] = new Date(obj[dateProperty]);
            console.log(obj['view' + dateProperty]);
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
        console.log('calculation score');
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

        var url = "../../ajaxaction.php?action=submitCAP";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, inspectionDto).then(
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
            totals: 0, 
            pendings: 0, 
            completes:0, 
            correcteds: 0,
            uncorrecteds:0,
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
                        if (selection.CorrectiveActions && selection.CorrectiveActions.length && !selection.Corrected_in_inspection) {
                            if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING) {
                                ready.pendings++;
                            } else if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.COMPLETE) {
                                ready.completes++;
                            } 

                        } else if (selection.Corrected_in_inspection) {
                            ready.correcteds++;
                        }
                    }
                    var k = question.Responses.SupplementalDeficiencies.length;
                    while (k--) {
                        ready.totals++;
                        question.hasDeficiencies = true;
                        var selection = question.Responses.SupplementalDeficiencies[k];
                        if (selection.CorrectiveActions && selection.CorrectiveActions.length && !selection.Corrected_in_inspection) {
                            if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.PENDING) {
                                ready.pendings++;
                            } else if (selection.CorrectiveActions[0].Status == Constants.CORRECTIVE_ACTION.STATUS.COMPLETE) {
                                ready.completes++;
                            }
                        } else if (selection.Corrected_in_inspection) {
                            ready.correcteds++;
                        }
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

mainController = function ($scope, $location, postInspectionFactory, convenienceMethods, $rootScope, roleBasedFactory) {
    $scope.route = $location.path();
    $scope.loc = $location.search();
    $scope.setRoute = function (route) {
        $location.path(route);
        $scope.route = route;
    }
    $rootScope.rbf = roleBasedFactory;
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
        $scope.error = "The system couldn't find the inspection.  Check your internet connection."
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

inspectionConfirmationController = function ($scope, $location, $anchorScroll, convenienceMethods, postInspectionFactory, $rootScope) {
    if ($location.search().inspection) {
        var id = $location.search().inspection;

        if (!postInspectionFactory.getInspection()) {
            $scope.doneLoading = false;
            convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=' + id + '&callback=JSON_CALLBACK', onFailGetInspeciton)
              .then(function (promise) {
                  $rootScope.inspection = promise.data;
                  if (promise.data.Date_started) promise.data = postInspectionFactory.setDateForView(promise.data, "Date_started");
                  //console.log(promise.data);
                  //set view init values for email
                  $scope.others = [{ email: '' }];
                  $scope.defaultNote = {};
                  var date = new Date($scope.inspection.viewDate_started).toLocaleDateString();
                  postInspectionFactory.setInspection($rootScope.inspection);

                  setEmailText(postInspectionFactory.getIsReadyToSubmit());

                  $scope.doneLoading = true;
                  // call the manager's setter to store the inspection in the local model
                  $scope.doneLoading = true;
              });
        } else {
            //set view init values for email
            $scope.others = [{ email: '' }];
            $scope.defaultNote = {};
            $scope.inspection = postInspectionFactory.getInspection();
            setEmailText(postInspectionFactory.getIsReadyToSubmit());
        }
    } else {
        $scope.error = 'No inspection has been specified';
    }

    function onFailGetInspeciton() {
        $scope.doneLoading = true;
        $scope.error = "The system couldn't find the inspection.  Check your internet connection."
    }


    function setEmailText(inspectionState) {
        var dateStarted = moment($scope.inspection.Date_started)
        var date = dateStarted.format("MMMM Do, YYYY");
        var id = postInspectionFactory.getInspection().Key_id
        console.log(date);
        if (inspectionState.totals == 0) {
            $scope.defaultNote.Text = "We appreciate you taking the time to meet with EHS for your annual laboratory safety inspection on " + date + ". Overall your lab was in excellent compliance with the research safety policies and procedures, and no deficiencies were identified during this inspection. No further actions are required at this time. You can access the lab inspection report using your University username and password at the following link: http://radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection=" + id + ". \n\n"
                                      + "Thank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\n\n"
                                       + "Best regards,\n"
                                        + "EHS Research Safety\n"
        }
        else if (inspectionState.totals > inspectionState.correcteds) {
            var dueDate = dateStarted.add(14, "days").format("MMMM Do, YYYY");
            $scope.defaultNote.Text = "We appreciate you taking the time to meet with EHS for your annual laboratory safety inspection on " + date + ". You can access the lab safety inspection report using your University username and password at the following link: http://radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection=" + id + ". \n\n"
                                 + "Please submit your lab's corrective action plan for each deficiency included in the report on or before "+ dueDate +".\n\n"
                                 + "Thank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\n\n\n"
                                 + "Best regards,\n"
                                 + "EHS Research Safety\n"
        }
        //all corrected
        else {
            $scope.defaultNote.Text = "We appreciate you taking the time to meet with EHS for your annual laboratory safety inspection on " + date + ". During this inspection EHS identified one or more deficiencies, but each deficiency was appropriately corrected during the time we were conducting the inspection. No further actions are required at this time. You can access the lab inspection report using your University username and password at the following link: http://radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection=" + id + " .\n\n"
                                      + "Thank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\n\n"
                                      + "Best regards,\n" 
                                      + "EHS Research Safety\n"
        }


    }

    $scope.contactList = [];

    $scope.sendEmail = function () {

        othersToSendTo = [];

        angular.forEach($scope.others, function (other, key) {
            othersToSendTo.push(other.email);
        });

        var contactList = [];

        if ($scope.inspection.PrincipalInvestigator.User.include) contactList.push($scope.inspection.PrincipalInvestigator.User.Key_id)

        var i = $scope.inspection.PrincipalInvestigator.LabPersonnel.length;
        while (i--) {
            if ($scope.inspection.PrincipalInvestigator.LabPersonnel[i].include) contactList.push($scope.inspection.PrincipalInvestigator.LabPersonnel[i].Key_id);
        }

        var emailDto = {
            Class: "EmailDto",
            Entity_id: $scope.inspection.Key_id,
            Recipient_ids: contactList,
            Other_emails: othersToSendTo,
            Text: $scope.defaultNote.Text
        }
        console.log(emailDto);
        var url = '../../ajaxaction.php?action=sendInspectionEmail';
        convenienceMethods.sendEmail(emailDto, onSendEmail, onFailSendEmail, url);
        $scope.sending = true;
    }

    function onSendEmail(data) {
        $scope.sending = false;
        $scope.emailSent = 'success';
        console.log(data);
        //postInspectionFactory.inspection.Notification_date =

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

inspectionReviewController = function ($scope, $location, convenienceMethods, postInspectionFactory, $rootScope, $modal) {
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
        $scope.error = "The system couldn't find the inspection.  Check your internet connection."
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

    $scope.handleInspectionOpen = function (inspection) {
        $scope.handlingInspectionOpen = true;
        var inspectionDto = {
            Date_created: inspection.Date_created,
            Date_closed: inspection.Date_closed ? null : convenienceMethods.setMysqlTime(Date()),
            Key_id: postInspectionFactory.inspection.Key_id,
            Principal_investigator_id: postInspectionFactory.inspection.Principal_investigator_id,
            Date_started: postInspectionFactory.inspection.Date_started,
            Notification_date: convenienceMethods.setMysqlTime(Date()),
            Schedule_month: postInspectionFactory.inspection.Schedule_month,
            Schedule_year: postInspectionFactory.inspection.Schedule_year,
            Cap_submitted_date: postInspectionFactory.inspection.Cap_submitted_date,
            Cap_complete: postInspectionFactory.inspection.Cap_complete,
            Class: "Inspection"
        };
        postInspectionFactory.saveInspection(inspection, inspectionDto).then(function () { $scope.handlingInspectionOpen = false; });
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
   
    if (data != null) {
        $scope.question = data.question || null;
        $scope.def = data.deficiency || null;
        $scope.dates = {};

        if ($scope.def && $scope.def.CorrectiveActions && $scope.def.CorrectiveActions[0]) {
            $scope.copy = {};
            for (var prop in $scope.def.CorrectiveActions[0]) {
                $scope.copy[prop] = $scope.def.CorrectiveActions[0][prop];
            }
            console.log($scope.def, $scope.copy);

        } else {
            $scope.copy = {
                Class: "CorrectiveAction",
                Is_active: true,
                Text: "",
                Deficiency_selection_id: $scope.def.Class == "DeficiencySelection" ? $scope.def.Key_id : null,
                Supplemental_deficiency_id: $scope.def.Class == "SupplementalDeficiency" ? $scope.def.Key_id : null,
            }
        }
        if ($scope.copy.Promised_date) $scope.dates.promisedDate = convenienceMethods.getDate($scope.copy.Promised_date);
        if ($scope.copy.Completion_date) $scope.dates.completionDate = convenienceMethods.getDate($scope.copy.Completion_date);

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

    $scope.saveCorrectiveAction = function (copy, orig) {

        if ($scope.dates.promisedDate) copy.Promised_date = convenienceMethods.setMysqlTime($scope.dates.promisedDate);
        if ($scope.dates.completionDate) copy.Completion_date = convenienceMethods.setMysqlTime($scope.dates.completionDate);

        //custom validation, because the validation is complex
        $scope.validationError = validateCorrectiveAction(copy);
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

    function validateCorrectiveAction(action) {
        console.log(action)
        errorObject = null;
        if (!action) {
            errorObj = {formBlank:true}
        } else {
            errorObj = {
                statusError: action.Status == null,
                textError: action.Text == null || action.Text == "",
                dateError: (action.Status && action.Text && action.Text != "") && (action.Promised_date == null && action.Completion_date == null && (!action.Needs_ehs && !action.Needs_facilities && !action.Insuficient_funds && !action.Other)),
                otherTextError: action.Other && !action.Other_reason
            }
        }
        return errorObj;
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

    $scope.cancel = function () {
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

