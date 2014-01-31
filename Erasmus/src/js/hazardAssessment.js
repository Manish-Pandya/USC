//convenience method to parse the data for rooms and hazards into 
var parseRoomsAndHazards = function (rooms, hazards){
  function camelCase(input) { 
    return input.toLowerCase().replace(/ (.)/g, function(match, group1) {
        return group1.toUpperCase();
    });
  }

   getLength = function(obj){
    if(obj !== null){
      if(obj.length > 0){
        return true
      }
    }
    return false;
  }

  var hotParentkey_ids = [];
  //properties:

  //object literal to hold final, fully populated list of hazards, including the rooms each one is present in
  inspectionHazards = {};

  function roomContainsHazard(array, obj) {
    //console.log(array);
      presentHazards = 0;
      for (hazardCount=0;hazardCount<array.length;hazardCount++) {
          if (array[hazardCount].Name === obj.Name) {
            presentHazards++;
          }   
      }
      if(presentHazards > 0){
        return true;
      }else{
        //console.log('falses');
        return false;
      }
  };

  setParentkey_ids = function(parent){
    if (getLength(parent.SubHazards)){
      for(var i = 0; parent.SubHazards.length > i; i++){
        parent.SubHazards[i].parentkey_id = parent.KeyId;
        if (getLength(parent.SubHazards)){
          setParentkey_ids(parent.SubHazards[i]);
        }
      }
    }
  };

  iterator = 0;

  checkHazardAndChildren = function(hazard){
    
    //create a unique DOM ID for each hazard for styling
    hazard.cssId = camelCase(hazard.Name);
   
    hazard.Rooms = [];
    hazard.isPresent = false;

    //loop through rooms.  see if each contains this hazard, set properties accordingly
    for(roomCount=0;roomCount<rooms.length;roomCount++){
        hazard.Rooms[roomCount] = {};
          if(roomContainsHazard(rooms[roomCount].Hazards, hazard)){
            //console.log('here');
            hazard.isPresent = true;
            hotParentkey_ids.push(hazard.parent_id);
            hazard.Rooms[roomCount].presentInThisRoom = true;
        }else{
         // console.log('here');
          hazard.Rooms[roomCount].presentInThisRoom = false;
        }
        hazard.Rooms[roomCount].Name = rooms[roomCount].Name;
        hazard.Rooms[roomCount].KeyId = rooms[roomCount].KeyId;

    }

    if (getLength(hazard.SubHazards)){
      hazard.isLeaf = false;
      for(var i = 0; hazard.SubHazards.length > i; i++){
          parentHazard = hazard;
          checkHazardAndChildren(hazard.SubHazards[i]);
      }
    }else{
      hazard.isLeaf = true;
    }

    if(hotParentkey_ids.indexOf(hazard.KeyId) > -1){
     // console.log(hazard.key_id);
        hazard.containsHotChildren = true;
        hazard.isPresent = true;
        hotParentkey_ids.push(hazard.parent_id); 

        for(childCount = 0;childCount<hazard.SubHazards.length; childCount++){
          if(hazard.SubHazards[childCount].isPresent == true){
              for(roomCount = 0; roomCount<hazard.SubHazards[childCount].Rooms.length;roomCount++){
                if( hazard.Children[childCount].Rooms[roomCount].presentInThisRoom == true ){
                  hazard.Rooms[roomCount].presentInThisRoom = true;
              }
            }
          }
        }
      }else{
        hazard.containsHotChildren = false;     
      }
  }
 
  finalizeTree = function(hazards, rooms){
    //array to hold fully populated hazard tree
    finalHazards = [];

    //loop through the hazards
    for (var i=0;i<hazards.length;i++){
      //console.log(hazards[i].label+'\'s key is: '+hazards[i].key_id);
      checkHazardAndChildren(hazards[i]);
    }
    
    return hazards;
  }
 return finalizeTree(hazards, rooms);
}




var hazardAssesment = angular.module('hazardAssesment', ['ui.bootstrap']);
hazardAssesment.factory('testFactory', function($http,$q){
	
	//initialize a factory object
	var tempFactory = {};
	
	//simple 'getter' to grab data from service layer
  tempFactory.getRoomAndHazardData = function(onGetHazards, url, url2){
	//console.dir(onGetHazards);
	//use jsonp method of the angularjs $http object to request data from service layer
  var hazards =	$http.jsonp(url)
  		.success( function(data) {	
  	    })
  	    .error(function(data, status, headers, config){
              alert('error');
              console.dir(headers());
              console.dir(status);
              console.dir(config);
       });
   var rooms = $http.jsonp(url2)
      .success( function(data) {   
      })
      .error(function(data, status, headers, config){
            alert('error');
            console.dir(headers());
            console.dir(status);
            console.dir(config);
      });

    return $q.all([rooms, hazards]).then(function(responses) {
        
       // console.log(responses[0].data.PrincipalInvestigator.Rooms);
        var data = parseRoomsAndHazards(responses[0].data.PrincipalInvestigator.Rooms,responses[1].data);
        onGetHazards(data);
    });

    return tempFactory;
	};

  tempFactory.setHazardFromDom = function(hazard){

  }

  tempFactory.saveRelationship = function(url, data, headers,config){

    return $http.post( url, data, headers, config)
      .success( function( data, headers ) {  
      })
      .error(function(data, status, headers, config){
          alert('error');
          console.dir(headers());
          console.dir(status);
          console.dir(config);
      });
  } 
	return tempFactory;
});
hazardAssesment.directive('customControl', function(){
   return {
     restrict: 'E',
     scope: {
        innerFoo: '&customClick'
     },
     template: '<button ng-click="innerFoo()">Call From Control</button>'
   };
});
hazardAssesment.directive('stopEvent', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attr) {
      //console.log(element);
     // console.log(scope);
        element.bind(attr.stopEvent, function (e) {
            e.stopPropagation();
        });
    }
  }
});





controllers = {};

//called on page load, gets initial user data to list users
controllers.hazardAssessmentController = function ($scope, $timeout, $dialog, $filter, testFactory) {
  $scope.hazards = [];
  $scope.navType = 'pills';

  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  function init(){
	  testFactory.getRoomAndHazardData(onGetHazards,'../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK','../../ajaxaction.php?action=getInspectionById&id=3366&callback=JSON_CALLBACK');
    //console.log($scope.data);
  }
  //grab set user list data into the $scrope object
  function onGetHazards (data) {
	  $scope.hazards = data;
  }

  function modalHazards(){
    $dialog.dialog({}).open('hazards-modal.html');  
  }

  $scope.filteredSubhazards = function(hazards) {
       // console.log(hazards);
        var result = {};
        angular.forEach(hazards, function(value, key) {
            if (value.containsHotChildren == true || value.isPresent == true) {
                result[key] = value;               
            }
        });

        return result;
    }   
  
  /*
   * HAZARD SAVE METHODS
   * used for creating and updating users
   * 
   */
  function saveHazardRoomRelationship(data){
	  testFactory.saveRelationship(onSaveHazardRoomRelationship,'../../ajaxaction.php?callback=JSON_CALLBACK',data);
  }

  function onSaveHazardRoomRelationship(data){
    $scope.newRelationships = data;
   
  }

  $scope.toggleHazardState = function(hazard){
      console.log(hazard);
  };
  
  $scope.checked_hazards = [];

  //handles adding or removing hazards from a room
  $scope.handleHazardRelationship = function(hazard) {
    hazard.checked = true;
    $scope.curentSubhazard = hazard;


   // console.log(hazard.containsHotChildren);
    if(hazard.containsHotChildren != true){
      hazard.checked = false;
    }else{
      hazard.checked = true;
    }
  }
  $scope.setRooms = function(hazard){

    $scope.opts       = {
        backdrop:      true,
        keyboard:      true,
        dialogFade:    true,
        backdropFade:  true,
        backdropClick: true,
        templateUrl:   'rooms-modal.html',
        controller:    'DialogController',
        resolve:       {hazard: function() {return hazard; console.log('hazard:'+hazard);}}
      };      
     // console.log($scope.curentSubhazard);
      
     // $dialog.dialog().open('hazards-modal.html');
      $scope.openDialog = function(hazard){
      console.log(hazard);
      var d = $dialog.dialog($scope.opts);

      //if(hazard.isLeaf != true){
        console.log('adsfasdfasdfadsfasdf');
        d.open().then(function(result){
            if(result){
           // console.log('dialog closed with result: ' + result);
          }
        }); 
      }
   // } 
    $scope.openDialog(hazard);
  }

  $scope.openModal = function(hazard,room){
    $scope.parentHazard = hazard;
    console.log('modal');
    $scope.opts       = {
        backdrop:      true,
        keyboard:      true,
        dialogFade:    true,
        backdropFade:  true,
        backdropClick: true,
        templateUrl:   'hazards-modal.html',
        controller:    'DialogController',
        resolve:       {hazard: function() {return hazard; console.log('hazard:'+hazard);}}
      };      
     // console.log($scope.curentSubhazard);
      
     // $dialog.dialog().open('hazards-modal.html');
      $scope.openDialog = function(hazard){
        //console.log(hazard);
      var d = $dialog.dialog($scope.opts);

    //  if(hazard.isLeaf != true){
        d.open().then(function(result){
            if(result){
            }
        }); 
      }
   // }
    $scope.openDialog(hazard);
  }
};

controllers.DialogController = function($scope, $rootScope, dialog, hazard,testFactory) {
  $scope.parentHazard = hazard;
  //console.log($scope.parentHazard);

$scope.walkHazardBranch = function(hazard, childHazard){

  console.log(hazard.Name);
  for(i=0;i<hazard.SubHazards.length;i++){
   // console.log(i);
    subhazard = hazard.SubHazards[i];
    console.log(subhazard.Name+', IsActive: '+ subhazard.IsActive);

    for(x=0;x<hazard.Rooms.length;x++){
      var room = hazard.Rooms[x];
      for(y=0;y<subhazard.Rooms.length;y++){
        if(subhazard.Rooms[y].Name == room.Name){
           // console.log('room match');
            if(subhazard.Rooms[y].presentInThisRoom == true){
              room.presentInThisRoom = subhazard.Rooms[y].presentInThisRoom;
              hazard.isPresent = true;
            }
        }
      }
     }
     if(subhazard.IsActive === true && subhazard.isLeaf !== true && childHazard.Name !== subhazard.Name){
      //console.log(subhazard);
        $scope.walkHazardBranch(subhazard);
    }
  }
}

$scope.handleRooms = function(hazard, room){
    //console.log(hazard);
    hazard.isPresent = true;

    if(room){
      console.log('room is true');
      data={};
      data.room_key_id = room.key_id;
      data.hazard_key_id = hazard.key_id;
      data.presentInThisRoom = room.presentInThisRoom;

     // console.log($scope.parentHazard);
      //room.Hazards.push(hazard);

      $scope.walkHazardBranch($scope.parentHazard,hazard);

      testFactory.saveRelationship('../../ajaxaction.php?callback=JSON_CALLBACK&update=true',data);
    }

    for(i=0;i<hazard.Rooms.length;i++){
      if(hazard.Rooms[i].presentInThisRoom == true){
        //console.log('adsf');
        hazard.isPresent = true;
       // console.log(hazard);
      }
    }
  }

  function init(hazard){
    $scope.handleRooms(hazard);
  }
  init(hazard);


  $scope.stopPropagation = function(e) {
    console.log(e);
      // to make sure it is a checkbox
      if (angular.element(e.currentTarget).prop('tagName') == 'INPUT') {
          e.stopPropagation();
      }
    }

  

  $scope.checker = {};
  hazard.currentChildren = [];
  //console.log('in second controller');
  $scope.subhazard = hazard;

  $scope.close = function(result){
    dialog.close(result);
  };

  $scope.toggleSubhazardState = function(hazard) {
    //console.log(room);   

    if(hazard.checked == false){
      hazard.checked = true;
    }else{
      hazard.checked = false;
    }
   
  };

  $scope.subhazardChecked = function(hazard,$event){


    if(hazard.checked != true){
      hazard.checked = false;
    }else if(hazard.isLeaf == true){
      hazard.checked = true;
    }else{
      //console.log(hazard.children);
      hazard.checked = true;
      //console.log(hazard.currentChildren);
    }
   
  };
/*  
    if(hazard.containsHotChildren == true){
      alert('true');
      hazard.checked = false;
      hazard.containsHotChildren = false;
    }else{
      hazard.checked = true;
      hazard.containsHotChildren = true;
    }


    if ($scope.checked_hazards.indexOf(hazard) != -1){
      //hazard.containsHotChildren = false;
      $scope.checked_hazards.splice( $scope.checked_hazards.indexOf(hazard), 1 );      
      return;
    } 

    if(hazard.isLeaf == true){
      console.log(hazard);
      //todo: call to factory method that creates relationship between hazard and room

    }

    $scope.checked_hazards.push(hazard);
    

  };

  //convert labels to camel case strings to use in css styling
  $scope.convertToCamelCase = function(str){
    str = str.trim();
    return str;  
  }

  $scope.modalHider = function($element){
    console.log($element);
  }

  };*/
};
//set controller
hazardAssesment.controller( controllers );