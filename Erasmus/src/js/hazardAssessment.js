//convenience method to parse the data for rooms and hazards into 
var parseRoomsAndHazards = function (rooms, hazards){

  function camelCase(input) { 
    return input.toLowerCase().replace(/ (.)/g, function(match, group1) {
        return group1.toUpperCase();
    });
  }

  var hotParentkey_ids = [];
  //properties:

  //object literal to hold final, fully populated list of hazards, including the rooms each one is present in
  inspectionHazards = {};

  function roomContainsHazard(array, obj) {
      presentHazards = 0;
      for (hazardCount=0;hazardCount<array.length;hazardCount++) {
          if (array[hazardCount].key_id === obj.key_id) {
            presentHazards++;
          }   
      }
      if(presentHazards > 0){
        return true;
      }else{
        return false;
      }
  };

  setParentkey_ids = function(parent){
    if (parent.hasOwnProperty('children') && parent.children.length > 0){
      for(var i = 0; parent.children.length > i; i++){
        parent.children[i].parentkey_id = parent.key_id;
        if (parent.children[i].hasOwnProperty('children') && parent.children[i].children.length > 0){
          setParentkey_ids(parent.children[i]);
        }
      }
    }
  };

  iterator = 0;

  checkHazardAndChildren = function(hazard){
    //create a unique DOM ID for each hazard for styling
    hazard.cssId = camelCase(hazard.label);
   
    hazard.rooms = [];
    hazard.isPresent = false;

    //loop through rooms.  see if each contains this hazard, set properties accordingly
    for(roomCount=0;roomCount<rooms.length;roomCount++){
        hazard.rooms[roomCount] = {};
          if(roomContainsHazard(rooms[roomCount].hazards, hazard)){
            hazard.isPresent = true;
            hotParentkey_ids.push(hazard.parent_id);
            hazard.rooms[roomCount].presentInThisRoom = true;
        }else{
          hazard.rooms[roomCount].presentInThisRoom = false;
        }
        hazard.rooms[roomCount].room = rooms[roomCount].room;
        hazard.rooms[roomCount].key_id = rooms[roomCount].key_id;

    }

    if (hazard.hasOwnProperty('children') && hazard.children.length > 0){
      hazard.isLeaf = false;
      for(var i = 0; hazard.children.length > i; i++){
          parentHazard = hazard;
          checkHazardAndChildren(hazard.children[i]);
      }
    }else{
      hazard.isLeaf = true;
    }

    if(hotParentkey_ids.indexOf(hazard.key_id) > -1){
     // console.log(hazard.key_id);
        hazard.containsHotChildren = true;
        hazard.isPresent = true;
        hotParentkey_ids.push(hazard.parent_id); 

        for(childCount = 0;childCount<hazard.children.length; childCount++){
          if(hazard.children[childCount].isPresent == true){
              for(roomCount = 0; roomCount<hazard.children[childCount].rooms.length;roomCount++){
                if( hazard.children[childCount].rooms[roomCount].presentInThisRoom == true ){
                  hazard.rooms[roomCount].presentInThisRoom = true;
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
        var data = parseRoomsAndHazards(responses[0].data,responses[1].data);
        onGetHazards(data);
    });

    return tempFactory;
	};

  tempFactory.setHazardFromDom = function(hazard){

  }

  tempFactory.saveRelationship = function(url, data, headers,config){

    return $http.post( url, data, headers, config)
      .success( function( data, headers ) {  
          console.log(data);
          console.log(headers);
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
  //
  function init(){
	  testFactory.getRoomAndHazardData(onGetHazards,'http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&hazards=true','http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&rooms=true');
    //console.dir($scope.data);
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
	  testFactory.saveRelationship(onSaveHazardRoomRelationship,'http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK',data);
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
    //console.log('relationship');
   // console.log(hazard);

   // console.log(hazard.containsHotChildren);
    if(hazard.containsHotChildren != true){
      hazard.checked = false;
    }else{
      hazard.checked = true;
    }
  }
  $scope.setRooms = function(hazard){
   // console.log('modal');
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
        //console.log(hazard);
      var d = $dialog.dialog($scope.opts);

      if(hazard.isLeaf != true){
        d.open().then(function(result){
            if(result){
           // console.log('dialog closed with result: ' + result);
          }
        }); 
      }
    }
    $scope.openDialog(hazard);
  }

  $scope.openModal = function(hazard){
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

      if(hazard.isLeaf != true){
        d.open().then(function(result){
            if(result){
           // console.log('dialog closed with result: ' + result);
          }
        }); 
      }
    }
    $scope.openDialog(hazard);
  }
};

controllers.DialogController = function($scope, $rootScope, dialog, hazard,testFactory) {

$scope.handleRooms = function(hazard, room){
    console.log('handling room');
  //  console.log(hazard);
   // console.log(room);
    hazard.isPresent = false;

    if(room){

      data={};
      data.room_key_id = room.key_id;
      data.hazard_key_id = hazard.key_id;
      data.presentInThisRoom = room.presentInThisRoom;

      testFactory.saveRelationship('http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&update=true',data);
    }

    for(i=0;i<hazard.rooms.length;i++){
      if(hazard.rooms[i].presentInThisRoom == true){
        hazard.isPresent = true
      }
    }
  }

  function init(hazard){
    $scope.handleRooms(hazard);
  }
  init(hazard);


  $scope.stopPropagation = function(e) {
      // to make sure it is a checkbox
      if (angular.element(e.currentTarget).prop('tagName') == 'INPUT') {
          e.stopPropagation();
      }
    }

  

  $scope.checker = {};
  hazard.currentChildren = [];
  console.log('in second controller');
  $scope.subhazard = hazard;

  $scope.close = function(result){
    dialog.close(result);
  };

  $scope.toggleSubhazardState = function(hazard) {
    //console.log(room);
    if(hazard.checked == false){
      console.log('setting to true');
      console.log(hazard.checked);
      hazard.checked = true;
      console.log('now true');
      console.log(hazard.checked);
    }else{
      console.log('setting to false');
      console.log(hazard.checked);
      hazard.checked = false;
      console.log('now false');
      console.log(hazard.checked);
    }
   
  };

  $scope.subhazardChecked = function(hazard,$event){

    alert('checkHazardAndChildren');

    if(hazard.checked != true){
      hazard.checked = false;
    }else if(hazard.isLeaf == true){
      hazard.checked = true;
    }else{
      console.log(hazard.children);
      hazard.checked = true;
      console.log(hazard.currentChildren);
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