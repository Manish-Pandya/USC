var postInspection = angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate']);
postInspection.filter('joinBy', function () {
  return function (input,delimiter) {
    return (input || []).join(delimiter || ',');
  };
});

//configure datepicker util
postInspection.config(function(ngQuickDateDefaultsProvider) {
  return ngQuickDateDefaultsProvider.set({
    closeButtonHtml: "<i class='icon-cancel-2'></i>",
    buttonIconHtml: "<i class='icon-calendar-2'></i>",
    nextLinkHtml: "<i class='icon-arrow-right'></i>",
    prevLinkHtml: "<i class='icon-arrow-left'></i>",
    // Take advantage of Sugar.js date parsing
    parseDateFunction: function(str) {
      d = Date.create(str);
      return d.isValid() ? d : null;
    }
  });
});


postInspection.config(function($routeProvider){

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
    {redirectTo: '/report'}
  );

});

postInspection.factory('testFactory', function($http){
  
  //initialize a factory object
  var tempFactory = {};
  
  //simple 'getter' to grab data from service layer
  tempFactory.getChecklists = function(onSuccess, url){
  
  //user jsonp method of the angularjs $http object to request data from service layer
  $http.jsonp(url)
    .success( function(data) {
      //onSuccess is the method we have passed from the controller.  Binds data from service layer to angularjs $scope object
      onSuccess(data);
    })
    .error(function(data, status, headers, config){
      alert('error');
      console.log(headers());
      console.log(status);
      console.log(config);
    });
        
  };
  
  tempFactory.saveUser = function(onSuccess, url){
    alert('saving user');
  }
  
  return tempFactory;
});


controllers = {};

mainController = function($scope, $location, convenienceMethods, $rootScope){
 // console.log($location);
  $scope.setRoute = function(route){
    $location.path(route);
  }

  init();
  function init(){
    $scope.doneLoading = false;
    insp = $location.search().inspection;
    convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    convenienceMethods.getData('../../ajaxaction.php?action=getUserById&id=1&callback=JSON_CALLBACK',onGetUser, onFailGetUser);
  };
  function onGetUser(data){
    $scope.User = data;
   // console.log($scope.User);
  }
  function onFailGetUser(){
    alert("There was a problem retrieving your user information");
  }
  function onGetInspection(data){
  //  console.log(data);
    data.DateCreated = convenienceMethods.getDate(data.DateCreated);
    $scope.Inspection = data;
    $rootScope.Inspection = data;
    $scope.doneLoading = data.doneLoading;
    console.log($rootScope);
  }
  function onFailGet(){
    alert('There was an error finding the inspection.');
  }

  //initialize date controls
  $scope.today = function() {
    $scope.dt = new Date();
  };

  $scope.today();

  $scope.showWeeks = true;
  $scope.toggleWeeks = function () {
    $scope.showWeeks = ! $scope.showWeeks;
  };

  $scope.clear = function () {
    $scope.dt = null;
  };

  // Disable weekend selection
  $scope.disabled = function(date, mode) {
    return ( mode === 'day' && ( date.getDay() === 0 || date.getDay() === 6 ) );
  };

  $scope.toggleMin = function() {
    $scope.minDate = ( $scope.minDate ) ? null : new Date();
  };
  $scope.toggleMin();

  $scope.open = function($event) {
    $event.preventDefault();
    $event.stopPropagation();
    $scope.opened = true;
  };

  $scope.dateOptions = {
    'year-format': "'yy'",
    'starting-day': 1
  };

  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'shortDate'];
  $scope.format = $scope.formats[0];

}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods,$rootScope){
  
  init();
  function init(){
    console.log($rootScope);
    $scope.doneLoading = false;
    if(!$rootScope.Inspection){
       insp = $location.search().inspection;
       convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    }else{
      console.log('here');
      $scope.Inspection = $rootScope.Inspection;
      onGetInspection($scope.Inspection);
    }
  };
  
  //grab set user list data into the $scrope object
  function onGetInspection(data) {  
    
  	$scope.PrincipalInvestigator = data.PrincipalInvestigator.User;
    $scope.Contacts = data.PrincipalInvestigator.LabPersonnel
    $scope.doneLoading = data.doneLoading;
  }

  function onFailGet(data){
  	alert('There was a problem trying to get your getData');
  }

  $scope.contactList = [];

  $scope.handleContactList = function(obj, $index){

  	if(!convenienceMethods.arrayContainsObject($scope.contactList,obj)){
  		$scope.contactList.push(obj);
  	}else{
  		angular.forEach($scope.contactList, function(value, key){
  			if(value.KeyId === obj.KeyId){
  				console.log(key);
  				$scope.contactList.splice(key,1);
  			}
  		});
  	}

  }

  $scope.sendEmail = function(){
  	//todo:  send email
  	//convenienceMethods.updateObject();
    $scope.setRoute('/review');
  }

}

inspectionReviewController = function($scope, $location, $anchorScroll, convenienceMethods, $filter, $rootScope){
  
  init();

  function init(){
    $scope.doneLoading = false;

    if(!$rootScope.Inspection){
      insp = $location.search().inspection;
      convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);;
    }else{
      onGetInspection($rootScope.Inspection);
    }
    $scope.options = ['Incomplete','Pending','Complete'];

  };

  function onGetInspection(data){

    angular.forEach(data.Checklists, function(checklist, key){
      console.log(checklist);
      if(!checklist.Responses)checklist.Responses = [];
      angular.forEach(checklist.Questions, function(question, key){
        if(question.Responses.Answer.toLowerCase() == "no"){
          angular.forEach(question.Responses.DeficiencySelections, function(def, key){
             def.questionText = question.Text;
             if(!def.CorrectiveActions.length){
              def.CorrectiveActions[0]={
                Class: "CorrectiveAction",
                Deficiency_selection_id: def.Key_id,
                Status: "Incomplete"
              }
              def.CorrectiveActionCopy =  def.CorrectiveActions[0];
             }
             checklist.Responses.push(def);
          });
        }
      });
    });

   

    var parentHazards = ['BIOLOGICAL SAFETY', 'CHEMICAL SAFETY', 'RADIATION SAFETY'];

    orderedChecklists = [];

    $scope.typeFlag = '';
  //  angular.forEach($scope.checklists, function(checklist, key){
    for(var i=0;data.Checklists.length>i;i++){
      var checklist = data.Checklists[i];
      console.log(checklist);
      if(checklist.Responses){
        console.log('true');
        if(checklist.Name.indexOf('iological') > -1){
          //set bio flag
          $scope.typeFlag = 'bioHazards';
        }
        if(checklist.Name.indexOf('hemical') > -1){
          //set checm flag
           $scope.typeFlag = 'chemHazards';
        }

        if(checklist.Name.indexOf('adiation') > -1){
          //set radiation flag
           $scope.typeFlag = 'radHazards';
        }

        if(checklist.Name.indexOf('eneral') > -1){
          //set general flag
           $scope.typeFlag = 'genHazards';
        }

        //interpret flag, push checklist into proper array
        if(!$scope[ $scope.typeFlag ])$scope[ $scope.typeFlag ] = [];
        $scope[ $scope.typeFlag ].push(checklist);

        //push all the responses foreach question in the checklist to a master array of reponses foreach hazard type
        if(checklist.Responses.length){
          $scope[ $scope.typeFlag ].Responses = [];
          angular.forEach(checklist.Responses, function(response, key){
            $scope[ $scope.typeFlag ].Responses.push(response);
          });
        }

      }
    }
//   });

  // if(orderedChecklists.bioHazards)$scope.bioHazards = orderedChecklists.bioHazards;
   console.log($scope.bioHazards);
   $scope.Inspection = data;

   /* var complianceDescriptions = ['All biological safety cabinets must be certified annually. This certification involves a process of inspection and testing by trained personnel, following strict protocols, to verify that it is working properly. This certification should be scheduled by contacting Tom Gardner with Biological Control Services at (919) 906-3046.',
    'Not established The OSHA Laboratory Standard requires that each laboratory establish and maintain a Chemical Hygiene Plan. This plan should include procedures, equipment, PPE and work practices that are designed to protect employees from the health hazards presented by hazardous chemicals used in the lab. The plan must be accessible to lab personnel at all times. Chemical Hygiene Plan: http://ehs.sc.edu/LabSafety.htm. This plan must be reviewed and updated annually and signed by all lab staff.',
    'Weekly wipe surveys from radioisotope work areas must be properly documented, maintained, and available upon request by EH&S.'
    ]
/*
    angular.forEach($scope.responses, function(value, key){

      value.complianceReference = Math.random().toString(36).substring(7);
      value.CorrectiveAction = {};
      var randomParentHazard = parentHazards[Math.floor(Math.random()*parentHazards.length)];
      value.ParentHazard = randomParentHazard;
      value.CorrectiveAction.beingEdited = false;
      console.log(value);
      if(randomParentHazard == 'BIOLOGICAL SAFETY'){
        value.complianceDescription = complianceDescriptions[0];
        value.CorrectiveAction.Status = 'incomplete';
        value.CorrectiveAction.Description = 'Re-certification scheduled for 07/01/12';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }else if(randomParentHazard == 'CHEMICAL SAFETY'){
        value.complianceDescription = complianceDescriptions[1];

        value.CorrectiveAction.Status = 'notStarted';
        value.CorrectiveAction.Description = '';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }else if(randomParentHazard == 'RADIATION SAFETY'){
        value.complianceDescription = complianceDescriptions[2];

        value.CorrectiveAction.Status = 'complete';
        value.CorrectiveAction.Description = 'Chemical Hygiene Plan was established, and is now accessible to all lab staff';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }
    });*/
  
    $scope.doneLoading = data.doneLoading;

    $scope.criteria = 'Replace mercury thermometers';
    $scope.recommendation = 'Mercury from broken thermometers presents a hazard to laboratory personnel, and hazardous waste that is costly for clean-up and disposal. Non-mercury thermometers are available that are accurate, safe, and less toxic. EH&S recommends that mercury thermometers be replaced with non-mercury alternatives.';
    $rootScope.Inspection = $scope.Inspection;
  }

  function onFailGet(data){
    alert('There was a problem when trying to get your data');
  }

  $scope.editCorrectiveAction = function(CorrectiveAction){
    console.log(CorrectiveAction);
    CorrectiveAction.beingEdited = true;
    $scope.CorrectiveActionCopy = angular.copy(CorrectiveAction);
    console.log($scope.CorrectiveActionCopy);
  }

  $scope.saveCorrectiveAction = function(CorrectiveActionCopy,CorrectiveAction){
    console.log(CorrectiveActionCopy);
    $scope.CorrectiveActionCopy = angular.copy(CorrectiveActionCopy);

    var url = "../../ajaxaction.php?action=saveCorrectiveAction&callback=JSON_CALLBACK";
    convenienceMethods.updateObject(CorrectiveActionCopy, $scope.CorrectiveActionCopy, onSaveCorrectiveAction, onSaveFailCorrectiveAction, url, $scope.CorrectiveActionCopy);


    /*
    CorrectiveAction.beingEdited = false;
    console.log(CorrectiveAction);
    CorrectiveAction.Description = $scope.CorrectiveActionCopy.Description;
    CorrectiveAction.Date = $scope.CorrectiveActionCopy.Date;
    CorrectiveAction.Status = $scope.CorrectiveActionCopy.Status;
    console.log(CorrectiveAction);
    */
  }

  function onSaveCorrectiveAction(data){
    console.log(data);
  }

  function onSaveFailCorrectiveAction(data){
    alert("Something went wrong when the system tried to save the Corrective Action");
    data = angular.copy($scope.CorrectiveActionCopy);
  }

  $scope.cancelEdit = function(action){
    action.beingEdited = false;
  }

  $scope.afterInspection = function(d){
    console.log( Date.parse(d));
    console.log(Date.parse($scope.Inspection.DateCreated));
    if(Date.parse(d)>Date.parse($scope.Inspection.DateCreated)){
      return true;
    }
    return false;
  }

}
function inspectionDetailsController($scope, $location, $anchorScroll, convenienceMethods, $rootScope, $http) {
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    if(!$rootScope.Inspection){
      insp = $location.search().inspection;
      convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    }else{
      console.log('here');
      $scope.Inspection = $rootScope.Inspection;
      onGetInspection($scope.Inspection);
    }
  };
  
  //grab set user list data into the $scrope object
  function onGetInspection(data) {
    console.log(data);
    /*
    data.Checklists = [ 
    { "key_id" : 200,
        "label" : "STANDARD MICROBIOLOGICAL PRACTICES",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Lab supervisor is not controlling access to the laboratory" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 300,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 1,
              "Recommendations" : [ { "key_id" : 224,
                    "Text" : "Test recommendation"
                  },
                  { "key_id" : 2454,
                    "Text" : "Test recommendation"
                  }
                ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Lab supervisor enforces policies that control access to the laboratory"
            },
            { "Deficiencies" : [ { "Text" : "Lab personnel are not washing their hands after working with samples" },
                  { "Text" : "Lab personnel are not washing their hands before leaving the lab" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 301,
              "orderIndex" : 2,
              "Recommendations" : [ { "Text" : "Test Recommendation" } ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Persons wash their hands after working with hazardous materials and before leaving the lab"
            },
            { "Deficiencies" : [ { "Text" : "Lab personnel are eating in lab areas" },
                  { "Text" : "Lab personnel are drinking in lab areas" },
                  { "Text" : "Lab personnel are storing food for human consumption in lab areas" }
                ],
              "DeficiencyRootCauses" : [ { "Text" : "Test Root Cause" } ],
              "isMandatory" : true,
              "key_id" : 302,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 3,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Eating, drinking, and storing food for consumption are not permitted in lab areas"
            }
          ],
        "rooms" : [ "101",
            "102",
            "103"
          ]
      },
      { "key_id" : 201,
        "label" : "SHIPPING BIOLOGICAL MATERIALS",
        "Questions" : [ { "Deficiencies" : [ { "key_id" : 222,
                    "Text" : "Personnel shipping biological samples have not completed biological shipping training"
                  },
                  { "key_id" : 223,
                    "Text" : "Personnel shipping biological samples are overdue for completing biological shipping training"
                  }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 310,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "International Air Transport Association (IATA) & DOT",
              "Text" : "Personnel shipping biological samples have completed biological shipping training in the past two years"
            } ],
        "rooms" : [ "101",
            "102"
          ]
      },
      { "key_id" : 202,
        "label" : "BLOODBORNE PATHOGENS (e.g. research involving human blood, body fluids, unfixed tissue)",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 203,
        "label" : "Test Checklist 1",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 204,
        "label" : "Test Checklist 2",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 205,
        "label" : "Test Checklist 3",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],
              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      }
    ];
   
  */
    $scope.Inspection = data;
   

    //onFinishLoop(data.Checklists);
  }

  function onFailGet(){
    alert('There was an error getting the Inspection');
  }

  function onFinishLoop(data){
   $scope.checklists = data;
    var answers = ['Yes','No','N/A'];
    for(i=0;i<$scope.checklists.length;i++){
      var checklist = $scope.checklists[i];
      for(x=0;x<checklist.Questions.length;x++){
        var question = checklist.Questions[x];
        question.Response = answers[Math.floor(Math.random()*answers.length)];
        if(question.Response == 'No'){
          question.Deficiency = question.Deficiencies[Math.floor(Math.random()*question.Deficiencies.length)].text;
          question.RootCause = question.DeficiencyRootCauses[Math.floor(Math.random()*question.DeficiencyRootCauses.length)];
          console.log(question);
        }
      }
    }
    $scope.doneLoading = true;
  }

   $scope.imgNumber = "1";
    $scope.change = function(imgNumber, checklist) {
      console.log(imgNumber);
      $scope.imgNumber = imgNumber;
      checklist.open = !checklist.open;
      console.log(checklist);
  }

  $scope.questionAnswered = function(checklist, response, question){
    
    checklist.answeredQuestions = 0;

    for(i=0;checklist.questions.length > i;i++){
      checklist.questions[i].complete = false;

        answer = checklist.questions[i].userResponse.answer;

        //if a user answers "yes" or "n/a", a question is valid
        if(answer == 'true' || answer.toLowerCase() == 'n/a'){
          checklist.questions[i].complete = true;
          //send to server
        }else{
          //if a user answers "no", a question is not valid until the user speficies one or more deficiencies
          
          //loop through deficiencies.  if any are checked, the question is valid
          checkedCount = 0;
          for(defCount=0;checklist.questions[i].deficiencies.length > defCount;defCount++){
            if(checklist.questions[i].deficiencies[defCount].checked == true){
              checkedCount++;
            }
          }
           if(checkedCount > 0){
            console.log(i);
              checklist.questions[i].complete = true;
           }
        }
        
        if(checklist.questions[i].complete == true){
          checklist.answeredQuestions++;
        }
    }
  }

  $scope.handleNotesAndRecommendations = function(question){

    if(question.noteText != null && question.noteText != ''){
      question.notes.push({text:question.noteText, checked:true});
      question.noteText = '';
      //send question.userRepsonse to server
    }

    if(question.recommendationText != null  && question.recommendationText != ''){
      question.recommendations.push({text:question.recommendationText, checked:true});
      question.recommendationText = '';
      //send question.userRepsonse to server
    }
  }


  $scope.$watch('checklists', function(checklists){
    angular.forEach(checklists, function(checklist, idx){
      checklist.currentlyOpen = false;
      if (checklist.open) {
        checklist.currentlyOpen = true;
        checklist.hasBeenSelected = true;
      }

      if(checklist.hasBeenSelected == true){
          if(checklist.answeredQuestions < checklist.questions.length){
            checklist.countClass = 'red';
          }else{
            checklist.countClass = 'green';
          }
      }
    })   
  }, true);


};
/*
inspectionStandardViewController = function($scope,  $location, $anchorScroll, convenienceMethods){
  
  $scope.doneLoading = false;
  init();
  function init(){
    convenienceMethods.getData('/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&checklists=true',onGetChecklists, onFailGet);
  };
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {
    console.log(data);
    console.log('controlled');
    $scope.Checklists = data.Checklists;
    $scope.doneLoading = data.doneLoading;
  }
  function onFailGet(data){
    alert('There was a problem trying to get your getData');
  }
}*/

//postInspection.controller(controllers);