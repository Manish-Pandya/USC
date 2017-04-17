var buildingHub = angular.module('buildingHub', ['ui.bootstrap','convenienceMethodWithRoleBasedModule']);


//called on page load, gets initial user data to list users
function buildingHubController($scope, $routeParams,$browser,$sniffer,$rootElement,$location, convenienceMethods) {
  $scope.users = [];

  init();

  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object

  function init(){
    $scope.newRoom = false;
    //get a building list
      convenienceMethods.getData('../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK',onGetBuildings,onFailGet);
  };

  //grab set user list data into the $scope object
  function onGetBuildings(data) {
    $scope.error = '';
      $scope.Buildings = data;
    $scope.building = false;
    if($location.search().building){
      angular.forEach($scope.Buildings, function(building, key){
        if(building.Key_id === $location.search().building){
          $scope.building = building;
          $scope.selectedBuilding = building;
        }
      });
    }
  }

  function onFailGet(){
    $scope.error="There was a problem retrieving the list of all the buildings in the system.  Please check your internet connection and try again."
  }

  $scope.onSelectBuilding = function(buildingDTO, $model, $label){
    $scope.building = buildingDTO;
    $location.search({building: buildingDTO.Key_id});
  }

  $scope.onSelectRoom = function(room, $model, $label){
    $scope.room = room;
    var url = '../../ajaxaction.php?action=getHazardsInRoom&roomId='+room.Key_id+'&subHazards=false&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetHazards, onFailGetHazards );

  }

  function onGetHazards(data){

    $scope.bioHazards = [];
    $scope.chemicalHazards = [];
    $scope.radHazards = []

    angular.forEach(data, function(hazard, key){
      console.log(hazard.ParentIds)
      if(hazard.ParentIds.indexOf("1") > -1){
        console.log(hazard)
        $scope.bioHazards.push(hazard);
      }
    });
  }

  function onFailGetHazards(){

  }

  function getBuilding(id){
    convenienceMethods.getData('../../ajaxaction.php?action=getBuildingById&id='+id+'&callback=JSON_CALLBACK',onGetBuilding,onFailGet);
  }

  function onGetBuilding(data){
    console.log(data);
  }

  $scope.showCreateBuilding = function(){

    $scope.showAdmin = !$scope.showAdmin;
    if($scope.building)$scope.newBuilding = $scope.building;

  }

  $scope.createBuilding = function(update){

    if($scope.newBuilding){
      var name = $scope.newBuilding.Name;
      $scope.newBuilding.IsDirty = true;
    }
    if($scope.buildingCopy){
      var name = $scope.buildingCopy.Name;
       $scope.buildingCopy.IsDirty = true;
    }

    buildingDto = {
      Class: "Building",
      Name: name,
      Is_active: 1
    }

    if($scope.building){
      building = $scope.building;
    }else{
      building = buildingDto
    }

    if(update)buildingDto.Key_id = $scope.building.Key_id;
    var url = '../../ajaxaction.php?action=saveBuilding';
    convenienceMethods.updateObject(  buildingDto, building, onSaveBuilding, onFailSaveBuilding, url  );
  }

  function onSaveBuilding(data){
    if($scope.newBuilding)$scope.newBuilding.IsDirty = false;
    if($scope.buildingCopy)$scope.buildingCopy.IsDirty = false;

    $scope.building = {};
    $scope.building = angular.copy(data);
  }

  function onFailSaveBuilding(){
    if($scope.newBuilding)$scope.newBuilding.IsDirty = false;
    if($scope.buildingCopy)$scope.buildingCopy.IsDirty = false;
    alert("There was an error when the system tried to save the building.");
  }

  $scope.reveal = function(building){
    angular.forEach($scope.buildings, function(thisBuilding, key){
        thisBuilding.showChildren = false;
    });
    building.showChildren = true;
  }

  $scope.deactivateBuilding = function(building){

  }

  $scope.editBuilding = function(building){
    building.edit = true;
    $scope.buildingCopy = angular.copy(building);
  }

  $scope.cancelEditBuilding = function(building){
    building.edit = false;
    $scope.buildingCopy = {};
  }

  $scope.editRoom = function(room){
    room.edit = true;
    $scope.roomCopy = angular.copy(room);
  }

  $scope.cancelEditRoom = function(room){
    room.edit = false;
    $scope.roomCopy = {};
  }

  $scope.saveEditedRoom = function(room){
    console.log($scope.roomCopy);
    $scope.roomCopy.IsDirty = true;
    var url = '../../ajaxaction.php?action=saveRoom';
    convenienceMethods.updateObject(  $scope.roomCopy, $scope.building, onAddRoom, onFailSaveRoom, url, room );
  }

  $scope.createRoom = function(building){

    roomDTO = {
      Name: $scope.roomCopy.Name,
      PIs:[],
      isNew: true,
      Class: "Room",
      Building_id: $scope.building.Key_id
    };

    var url = '../../ajaxaction.php?action=saveRoom';
    convenienceMethods.updateObject(  roomDTO, $scope.building, onSaveRoom, onFailSaveRoom, url  );
  }

  function onSaveRoom(data){
    $scope.building.Rooms.push(data);
  }

  function onFailSaveRoom(data){
    if(data) {
      $scope.error = data.Message;
    } else {
      $scope.error = 'Something went wrong when the system tried to save the room.';
    }
  }

  var onAddRoom = function( returnedFromServer, building ){

    if($scope.roomDTO)room = angular.copy($scope.roomDTO);
    if($scope.roomCopy)room = $scope.roomCopy;
    room.isNew = false;
    room.edit = false;
    room.IsDirty = false;
    $scope.building.Rooms.shift();
    if(!convenienceMethods.arrayContainsObject($scope.building.Rooms,room)){
      $scope.building.Rooms.unshift(room);
      $scope.newRoom = false;
    }else{
      var idx = convenienceMethods.arrayContainsObject($scope.building.Rooms,room, null, true);
      room = angular.copy(returnedFromServer);
      $scope.roomCopy = {};
    }
  }

  onFailAddRoom = function(obj){
    alert('There was a problem when saving '+obj);
  }

  $scope.removeRoomFromBuilding = function(room, building){
    //remove a room from a building?
  }

  $scope.addPItoRoom = function(room){

    $scope.piDTO = {
      KeyId: null,
      Hazards: [],
      isNew: true,
      Class: "PI"
    };

    room.PIs.unshift($scope.piDTO);
  }

  $scope.saveNewPI = function( room, customSelected ){
    console.log(customSelected);
   $scope.piDTO.Name = customSelected.Name;
    var url = '../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK';
    convenienceMethods.updateObject(  $scope.piDTO, room, onAddPI, onFailAddPI, url  );
  }

  onAddPI = function( objDTO, room ){
    console.log($scope.piDTO);
    PI = angular.copy($scope.piDTO);
    PI.isNew = false;
    room.PIs.shift();
    room.PIs.unshift(PI);
  }

  onFailAddPI = function(obj){
    alert('There was a problem when saving '+obj);
  }

  $scope.removePIfromRoom = function(pi, room){

  }
  $scope.setAddNewRoom = function(){
    console.log('new room')
    $scope.newRoom = true;
    $scope.roomCopy = {
      Class: 'Room',
      Building_id: $scope.building.Key_id,
      Name: ''
    }
    if(!$scope.building.Rooms){
      $scope.building.Rooms = [];
    }
  }
  $scope.cancelNewRoom = function(){
    $scope.newRoom = false;
  }
};

function buildingHubController(n,t,i,r,u,f,e){function h(){n.newRoom=!1;e.getData("../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK",c,o)}function c(t){n.error="";n.Buildings=t;n.building=!1;f.search().building&&angular.forEach(n.Buildings,function(t){t.Key_id===f.search().building&&(n.building=t,n.selectedBuilding=t)})}function o(){n.error="There was a problem retrieving the list of all the buildings in the system.  Please check your internet connection and try again."}function l(t){n.bioHazards=[];n.chemicalHazards=[];n.radHazards=[];angular.forEach(t,function(t){console.log(t.ParentIds);t.ParentIds.indexOf("1")>-1&&(console.log(t),n.bioHazards.push(t))})}function a(){}function y(t){n.newBuilding&&(n.newBuilding.IsDirty=!1);n.buildingCopy&&(n.buildingCopy.IsDirty=!1);n.building={};n.building=angular.copy(t)}function p(){n.newBuilding&&(n.newBuilding.IsDirty=!1);n.buildingCopy&&(n.buildingCopy.IsDirty=!1);alert("There was an error when the system tried to save the building.")}function w(t){n.building.Rooms.push(t)}function s(t){n.error=t?t.Message:"Something went wrong when the system tried to save the room."}n.users=[];h();n.onSelectBuilding=function(t){n.building=t;f.search({building:t.Key_id})};n.onSelectRoom=function(t){n.room=t;var i="../../ajaxaction.php?action=getHazardsInRoom&roomId="+t.Key_id+"&subHazards=false&callback=JSON_CALLBACK";e.getData(i,l,a)};n.showCreateBuilding=function(){n.showAdmin=!n.showAdmin;n.building&&(n.newBuilding=n.building)};n.createBuilding=function(t){var i,r;n.newBuilding&&(i=n.newBuilding.Name,n.newBuilding.IsDirty=!0);n.buildingCopy&&(i=n.buildingCopy.Name,n.buildingCopy.IsDirty=!0);buildingDto={Class:"Building",Name:i,Is_active:1};building=n.building?n.building:buildingDto;t&&(buildingDto.Key_id=n.building.Key_id);r="../../ajaxaction.php?action=saveBuilding";e.updateObject(buildingDto,building,y,p,r)};n.reveal=function(t){angular.forEach(n.buildings,function(n){n.showChildren=!1});t.showChildren=!0};n.deactivateBuilding=function(){};n.editBuilding=function(t){t.edit=!0;n.buildingCopy=angular.copy(t)};n.cancelEditBuilding=function(t){t.edit=!1;n.buildingCopy={}};n.editRoom=function(t){t.edit=!0;n.roomCopy=angular.copy(t)};n.cancelEditRoom=function(t){t.edit=!1;n.roomCopy={}};n.saveEditedRoom=function(t){console.log(n.roomCopy);n.roomCopy.IsDirty=!0;e.updateObject(n.roomCopy,n.building,b,s,"../../ajaxaction.php?action=saveRoom",t)};n.createRoom=function(){roomDTO={Name:n.roomCopy.Name,PIs:[],isNew:!0,Class:"Room",Building_id:n.building.Key_id};e.updateObject(roomDTO,n.building,w,s,"../../ajaxaction.php?action=saveRoom")};var b=function(t){if(n.roomDTO&&(room=angular.copy(n.roomDTO)),n.roomCopy&&(room=n.roomCopy),room.isNew=!1,room.edit=!1,room.IsDirty=!1,n.building.Rooms.shift(),e.arrayContainsObject(n.building.Rooms,room)){var i=e.arrayContainsObject(n.building.Rooms,room,null,!0);room=angular.copy(t);n.roomCopy={}}else n.building.Rooms.unshift(room),n.newRoom=!1};onFailAddRoom=function(n){alert("There was a problem when saving "+n)};n.removeRoomFromBuilding=function(){};n.addPItoRoom=function(t){n.piDTO={KeyId:null,Hazards:[],isNew:!0,Class:"PI"};t.PIs.unshift(n.piDTO)};n.saveNewPI=function(t,i){console.log(i);n.piDTO.Name=i.Name;e.updateObject(n.piDTO,t,onAddPI,onFailAddPI,"../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK")};onAddPI=function(t,i){console.log(n.piDTO);PI=angular.copy(n.piDTO);PI.isNew=!1;i.PIs.shift();i.PIs.unshift(PI)};onFailAddPI=function(n){alert("There was a problem when saving "+n)};n.removePIfromRoom=function(){};n.setAddNewRoom=function(){console.log("new room");n.newRoom=!0;n.roomCopy={Class:"Room",Building_id:n.building.Key_id,Name:""};n.building.Rooms||(n.building.Rooms=[])};n.cancelNewRoom=function(){n.newRoom=!1}}var buildingHub=angular.module("buildingHub",["ui.bootstrap","convenienceMethodWithRoleBasedModule"]);
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

var Constants = (function () {
    function Constants() {
    }
    return Constants;
}());
Constants.PENDING_CHANGE = {
    USER_STATUS: {
        NO_LONGER_CONTACT: "Still in this lab, but no longer a contact",
        NOW_A_CONTACT: "Still in this lab, but now a lab contact",
        MOVED_LABS: "In another PI's lab",
        LEFT_UNIVERSITY: "No longer at the univserity",
        ADDED: "Added",
        REMOVED: "Removed"
    },
    ROOM_STATUS: {
        ADDED: "Added",
        REMOVED: "Removed"
    },
    HAZARD_STATUS: {}
};
Constants.POSITION = ["Undergraduate",
    "Graduate Student",
    "Post-Doctoral Fellow",
    "Research Professor",
    "Research Associate",
    "Laboratory Technician",
    "Research Specialist",
    "Scientific Staff",
    "Intern",
    "Other"
];
Constants.ROLE = {
    NAME: {
        ADMIN: "Admin",
        SAFETY_INSPECTOR: "Safety Inspector",
        RADIATION_INSPECTOR: "Radiation Inspector",
        PRINCIPAL_INVESTIGATOR: "Principal Investigator",
        LAB_CONTACT: "Lab Contact",
        LAB_PERSONNEL: "Lab Personnel",
        RADIATION_USER: "Radiation User",
        RADIATION_ADMIN: "Radiation Admin",
        EMERGENCY_ACCOUNT: "Emergency Account",
        READ_ONLY: "Read Only",
        OCCUPATIONAL_HEALTH: "Occupational Health",
        IBC_MEMBER: "IBC Member",
        IBC_CHAIR: "IBC Chair"
    }
};
Constants.CARBOY_USE_CYCLE = {
    STATUS: {
        AVAILABLE: "Available",
        IN_USE: "In Use",
        DECAYING: "Decaying",
        PICKED_UP: "Picked Up",
        AT_RSO: "AT RSO",
        HOT_ROOM: "In Hot Room",
        MIXED_WASTE: "Mixed Waste"
    }
};
Constants.INSPECTION = {
    STATUS: {
        NOT_ASSIGNED: "NOT ASSIGNED",
        NOT_SCHEDULED: "NOT SCHEDULED",
        SCHEDULED: "SCHEDULED",
        OVERDUE_FOR_INSPECTION: "OVERDUE INSPECTION",
        INCOMPLETE_INSPECTION: "INCOMPLETE INSPECTION",
        INCOMPLETE_CAP: "INCOMPLETE CAP",
        OVERDUE_CAP: "OVERDUE CAP",
        SUBMITTED_CAP: "SUBMITTED CAP",
        CLOSED_OUT: "CLOSED OUT",
    },
    SCHEDULE_STATUS: {
        NOT_ASSIGNED: "NOT ASSIGNED"
    },
    TYPE: {
        BIO: "BioSafety Inspection",
        CHEM: "Chemical Inspection",
        RAD: "Radiation Inspection"
    },
    MONTH_NAMES: [
        { val: "01", string: "January" },
        { val: "02", string: "February" },
        { val: "03", string: "March" },
        { val: "04", string: "April" },
        { val: "05", string: "May" },
        { val: "06", string: "June" },
        { val: "07", string: "July" },
        { val: "08", string: "August" },
        { val: "09", string: "September" },
        { val: "10", string: "October" },
        { val: "11", string: "November" },
        { val: "12", string: "December" }
    ],
    OTHER_DEFICIENCY_ID: 100032
};
Constants.CORRECTIVE_ACTION = {
    STATUS: {
        INCOMPLETE: "Incomplete",
        PENDING: "Pending",
        COMPLETE: "Complete",
        ACCEPTED: "Accepted"
    },
    NO_COMPLETION_DATE_REASON: {
        NEEDS_EHS: { LABEL: "Completion date depends on EHS.", VALUE: "needs_ehs" },
        NEEDS_FACILITIES: { LABEL: "Completion date depends on Facilities.", VALUE: "needs_facilities" },
        INSUFFICIENT_FUNDS: { LABEL: "Insufficient funds for corrective action.", VALUE: "insuficient_funds" }
    }
};
Constants.DRUM = {
    STATUS: {
        SHIPPED: "Shipped",
    }
};
Constants.PICKUP = {
    STATUS: {
        PICKED_UP: "PICKED UP",
        AT_RSO: "AT RSO",
        REQUESTED: "REQUESTED",
    }
};
Constants.PARCEL = {
    STATUS: {
        REQUESTED: "Requested",
        ARRIVED: "Arrived",
        ORDERED: "Ordered",
        WIPE_TESTED: "Wipe Tested",
        DELIVERED: "Delivered",
        DISPOSED: "Disposed"
    }
};
Constants.INVENTORY = {
    STATUS: {
        LATE: "Late",
        COMPLETE: "Complete",
        NA: "N/A"
    }
};
Constants.ISOTOPE = {
    EMITTER_TYPE: {
        ALPHA: "Alpha",
        BETA: "Beta",
        GAMMA: "Gamma"
    }
};
Constants.WIPE_TEST = {
    READING_TYPE: {
        LSC: "LSC",
        ALPHA_BETA: "Alpha/Beta",
        MCA: "MCA",
        GM_METER: "GM Meter"
    }
};
//match the key_id for each waste type to a readable string
Constants.WASTE_TYPE = {
    LIQUID: 1,
    CADAVER: 2,
    VIAL: 3,
    OTHER: 4,
    SOLID: 5,
    TRANSFER: 6
};
//these have to be strings instead of ints because the server will return IDS as strings, and we don't want to have to convert them all
Constants.BRANCH_HAZARD_IDS = ['1', '9999', '10009', '10010'];
Constants.MASTER_HAZARDS_BY_ID = {
    1: { Name: 'Biological Safety', cssID: 'biologicalMaterialsHeader' },
    9999: { Name: 'Chemical Safety', cssID: 'chemicalSafetyHeader' },
    10009: { Name: 'Radiation Safety', cssID: 'radiationSafetyHeader' },
    10010: { Name: 'General Laboratory Safety', cssID: 'generalSafetyHeader' }
};
Constants.MASTER_HAZARD_IDS = {
    BIOLOGICAL: 1,
    CHEMICAL: 10009,
    RADIATION: 10010
};
Constants.CHECKLIST_CATEGORIES_BY_MASTER_ID = [
    { Key_id: 1, Label: 'Biological', Image: 'biohazard-white-con.png', cssID: 'biologicalMaterialsHeader' },
    { Key_id: 10009, Label: 'Chemical', Image: 'chemical-safety-large-icon.png', cssID: 'chemicalSafetyHeader' },
    { Key_id: 10010, Label: 'Radiation', Image: 'radiation-large-icon.png', cssID: 'radiationSafetyHeader' },
    { Key_id: 9999, Label: 'General', Image: 'gen-hazard-large-icon.png', cssID: 'generalSafetyHeader' }
];
Constants.HAZARD_PI_ROOM = {
    STATUS: {
        STORED_ONLY: "Stored Only",
        OTHER_PI: "Other Lab's Hazard",
        IN_USE: "In Use"
    }
};
Constants.BIOSAFETY_CABINET = {
    FREQUENCY: {
        ANNUALLY: "Annually",
        SEMI_ANNUALLY: "Semi-annually"
    },
    EQUIPMENT_CLASS: "BioSafetyCabinet",
    TYPE: ["Class I",
        "Class II, Type A1",
        "Class II, Type A2",
        "Class II, Type A/B3",
        "Class II, Type B1",
        "Class II, Type B2",
        "Horizontal Flow Clean Bench",
        "Vertical Flow Clean Bench"
    ],
    STATUS: {
        FAIL: "FAIL",
        PASS: "PASS",
        NEW_BSC: "NEW BSC",
        OVERDUE: "OVERDUE",
        PENDING: "PENDING"
    }
};
Constants.ROOM_HAZARDS = {
    BIO_HAZARDS_PRESENT: { label: "Biological Hazards", value: "Bio_hazards_present" },
    CHEM_HAZARDS_PRESENT: { label: "Chemical Hazards", value: "Chem_hazards_present" },
    RAD_HAZARDS_PRESENT: { label: "Radiation Hazards", value: "Rad_hazards_present" }
};
Constants.ROOM_HAZARD_STATUS = {
    IN_USE: { KEY: "IN_USE", LAB_LABEL: "Used by my lab in room", ADMIN_LABEL: "In use in room" },
    STORED_ONLY: { KEY: "STORED_ONLY", LAB_LABEL: "Stored in room", ADMIN_LABEL: "Stored only in room" },
    NOT_USED: { KEY: "NOT_USED", LAB_LABEL: "Not used by my lab in room", ADMIN_LABEL: "Not used in room" }
};
Constants.VERIFICATION = {
    STATUS: {
        COMPLETE: "COMPLETE",
        OVERDUE: "OVERDUE",
        PENDING: "PENDING"
    }
};
Constants.PROTOCOL_HAZARDS = [
    { Name: "Recombinant or Synthetic Nucleic Acids", Key_id: 1, Class: "Hazard" },
    { Name: "Risk Group 2 (RG2) or Higher Agents", Key_id: 2, Class: "Hazard" },
    { Name: "Human-Derived Materials", Key_id: 3, Class: "Hazard" },
    { Name: "HHS Biological Toxins", Key_id: 4, Class: "Hazard" }
];
Constants.IBC_PROTOCOL_REVISION = {
    STATUS: {
        NOT_SUBMITTED: "Not Submitted",
        SUBMITTED: "Submitted",
        RETURNED_FOR_REVISION: "Returned for Revision",
        IN_REVIEW: "In Review",
        APPROVED: "Approved"
    }
};
Constants.IBC_ANSWER_TYPE = {
    MULTIPLE_CHOICE: "MULTIPLE_CHOICE",
    TABLE: "TABLE",
    FREE_TEXT: "FREE_TEXT",
    MULTI_SELECT: "MULTI_SELECT"
};

angular.module('convenienceMethodWithRoleBasedModule', ['ngRoute','roleBased','ui.select','ngSanitize'])
.run(function($rootScope) {
    $rootScope.Constants = Constants;
})
.factory('convenienceMethods', function($http,$q,$rootScope){
    var methods =  {
        
        //
        /**
        * 	loop through an object, set its properties to match the DTO
        *
        *	@param (Object dto)     A data transfer object.  Has the properties which will be updated
        *	@param (Object obj)     The object to be updated in the AngularJS $scope
        *
        **/

        setPropertiesFromDTO: function(dto,obj){
            for (var key in dto) {
                if (dto.hasOwnProperty(key)) {
                    obj[key] = dto[key];
                }

            }
        },
        /**
        * 	UPDATE an object on server and in AngularJS $scope object
        *
        *	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
        *	@param (obj OBJ)          The object to be updated in the AngularJS $scope
        *   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
        *	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
        *   @param (String url)       The URL on the server to which we post
        *   @param (Object failParam) Object to be passed to failure function
        *
        **/
        saveNewObject: function( obj, onSave, onFail, url, failParam ){
            return $http.post(  url, obj )
            .success( function( returnedObj ) {
                onSave(returnedObj, obj );
            })
            .error(function(data, status, headers, config, hazard){
                //console.log(failParam);
                methods.userLoggedOut(data);
                onFail( obj, failParam );
            });
        },
        /**
        * 	UPDATE an object on server and in AngularJS $scope object
        *
        *	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
        *	@param (obj OBJ)          The object to be updated in the AngularJS $scope
        *   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
        *	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
        *   @param (String url)       The URL on the server to which we post
        *   @param (Object failParam) Object to be passed to failure function
        *
        **/
        updateObject: function( objDTO, obj, onSave, onFail, url, failParam, extra1, extra2, extra3){
            //console.log(objDTO);
            return $http.post(  url, objDTO )
            .success( function( returnedObj ) {
                if(returnedObj.IsError) {
                    onFail(returnedObj);
                } else {
                    onSave(returnedObj, obj, extra1, extra2, extra3);
                    methods.userLoggedOut(data);
                }
            })
            .error(function(data, status, headers, config, hazard){
                //console.log(failParam);
                onFail( obj, failParam );
            });
        },
        /**
        * 	DELETE an object on server and in AngularJS $scope object
        *
        *	@param (obj DTO)          A data transfer object.  Has the properties which will be updated
        *	@param (obj OBJ)          The object to be updated in the AngularJS $scope
        *   @param (function onSave)  AngularJS controller method to call if our server call returns a good code
        *	@param (function onFail)  AngularJS controller method to call if our server call returns a bad code
        *   @param (String url)       The URL on the server to which we post
        *   @param (Object failParam) Object to be passed to failure function
        *
        **/
        deleteObject: function( onSave, onFail, url, object, parent, parent2){
            return $http.delete(  url )
            .success( function( returnedObj ) {
                //console.log(returnedObj);
                onSave(returnedObj, object, parent, parent2);
            })
            .error(function (data, status, headers, config, hazard) {
                methods.userLoggedOut(data);
                onFail(object,parent);
            });
        },

        /**
        * 	Get data from the server via REST like call, call callback method of controller accordingly
        *
        *   @param (Function onSuccess)  AngularJS controller method to call if our server call returns a good code
        *	@param (Function onFail)     AngularJS controller method to call if our server call returns a bad code
        *   @param (String url)          The URL on the server to which we post
        *   @param (Object parentObject) An optional parent object.   If this is passed, we are doing an asynch query to load child data for a parent object, for example asychronously loading a hazard's SubHazards
        *
        **/

        getData: function( url, onSuccess, onFail, parentObject, adding ){
            //use jsonp method of the angularjs $http object to request data from service layer
            $http.jsonp(url)
                .success( function(data) {
                    data.doneLoading = true;
                    onSuccess(data, parentObject, adding);
                })
                .error(function (data, status, headers, config) {
                    methods.userLoggedOut(data);
                    onFail(data,parentObject);
                })
        },

        /**
        * 	Get data from the server via REST like call, call callback method of controller accordingly
        *
        *	@param (Function onFail)     method to call if our server call returns a bad code
        *   @param (String url)          The URL on the server to which we post
        *
        **/
        getDataAsPromise: function( url, errorCallback ){
            //use jsonp method of the angularjs $http object to request data from service layer
            var promise = $http.jsonp(url)
                .success( function(data) {
                    data.doneLoading = true;
                    return data;
                })
                .error(function(data, status, headers, config){
                    methods.userLoggedOut(data);
                    errorCallback();
                });
            return promise;
        },
        getDataAsDeferredPromise: function( url ){
            var deferred = $q.defer();
            //use jsonp method of the angularjs $http object to request data from service layer
            $http.jsonp(url)
                .success(function (data) {
                    deferred.resolve(data);
                })
                .error(function (data, status, headers, config) {
                    methods.userLoggedOut(data);
                    deferred.reject(data);
                });
            return deferred.promise;
        },
        saveDataAndDefer: function(url, obj){
            var deferred = $q.defer();
            var promise = $http.post(url,obj)
            .success( function(data) {
                data.doneLoading = true;
                deferred.resolve(data);
            })
                .error(function(data, status){
                    methods.userLoggedOut(data);
                    deferred.reject(data);
                });
            return deferred.promise;
        },
        getDataFromPostRequest: function(url, data, onSuccess, onFail ){
            //console.log(data);
            $http.post(url,data)
            .success( function(data) {
                data.doneLoading = true;
                onSuccess(data);
            })
            .error(function (data, status, headers, config) {
                methods.userLoggedOut(data);
                onFail(data);
            })
        },
        setData: function(data){
            data = data;
        },
        /**
        *
        *	Boolean to test if an object or property exists, and if so, if it has length
        *	@param (Object obj)
        *
        **/
        getHasLength: function(obj){
            if(obj){
                if(obj !== null){
                    if(obj.length > 0){
                        return true
                    }
                }
                return false;
            }
            return false;
        },
        /**
        *
        *	Boolean returns true if an array contains an object
        *	@param (Array, obj)  array to search for object
        *	@param (Object obj)  object to find in array
        *	@param (Array, props)	OPTIONAL THIRD PARAMETER -- an array of properties to evaluate if we are not using key ids -- index one should be the property searched in the array, index two the property of the object seached for in the array
        *		(ie [Key_id, Reponse_id] will evaluate the objects Response_id property against the Key_id proptery of each object in the array)
        *
        *	@param (Bool, returnIdx)	OPTIONAL FOURTH PARAM Setting to true will cause this method to return the index of the object in an array instead of a boolean true, if the array contains the object
        *
        **/
        arrayContainsObject: function (array, obj, props, returnIdx) {
            if(!props) {var props = ["Key_id","Key_id"];}

            for (var localI=0;localI<array.length;localI++) {
                if (array[localI][props[0]] === obj[props[1]]) {
                    if(returnIdx)return localI;
                    return true;
                }
            }
            return false;
        },
        /**
        *
        *	Set the relationship between two objects
        *	@param (Object, object1)  first object
        *	@param (Object object2)   second object
        *
        **/
        setObjectRelationship: function( object1, object2, onSuccess, onFail, url, failParam ){
            objDTO = {};
            objDTO.object1 = object1;
            objDTO.object1 = object2;
            return $http.post(  url, objDTO )
            .success( function( returnedObj ) {
                onSuccess(returnedObj, obj );
            })
            .error(function(data, status, headers, config, hazard){
                methods.userLoggedOut(data);
                onFail( obj, failParam );
            });
        },
        /**
      *
      *	returns an array of strings, each one the Name of one of the user's role objects
      *	@param (user, User)  user object
      *
      **/
        getUserTypes: function( user ){
            if(user.Roles){
                rolesArray = [];
                for(i=0;user.Roles.length>i;i++){
                    rolesArray.push(user.Roles[i].Name);
                }
                return rolesArray;
            }
            return false
        },
        /**
      *
      *	Converts a UNIX timestamp to a Javascript date object
      *	@param (time, int)  Unix timestamp to convert
      *
      **/
        getUnixDate: function(time){
            Date.prototype.getMonthFormatted = function() {
                var month = this.getMonth();
                return month < 10 ? '0' + month : month; // ('' + month) for string result
            }
            // create a new javascript Date object based on the timestamp
            // multiplied by 1000 so that the argument is in milliseconds, not seconds
            var date = new Date(time*1000);
            // hours part from the timestamp
            var hours = date.getHours();
            // minutes part from the timestamp
            var minutes = date.getMinutes();
            // seconds part from the timestamp
            var seconds = date.getSeconds();

            var month = date.getMonth()+1;
            var day = date.getDate();
            var year = date.getFullYear();

            // will display date in mm/dd/yy format
            var formattedTime = month + '/' + day + '/' + year;

            return formattedTime;
        },
        /**
        *
        *	Converts a MYSQL datetime to a Javascript date object
        *	@param (time, string)  MYSQL datetime to convert
        *
        **/
        getDateString: function(time){

            Date.prototype.getMonthFormatted = function() {
                var month = this.getMonth();
                return month < 10 ? '0' + month : month; // ('' + month) for string result
            }

            // Split timestamp into [ Y, M, D, h, m, s ]
            var t = time.split(/[- :]/);

            // Apply each element to the Date function
            // create a new javascript Date object based on the timestamp
            var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);


            // hours part from the timestamp
            var hours = date.getHours();
            // minutes part from the timestamp
            var minutes = date.getMinutes();
            // seconds part from the timestamp
            var seconds = date.getSeconds();

            var month = date.getMonth()+1;
            var day = date.getDate();
            var year = date.getFullYear();

            // will display date in mm/dd/yy format
            var formattedTime = {};
            formattedTime.formattedString = month + '/' + day + '/' + year;
            formattedTime.year = year;
            //console.log(formattedTime);
            return formattedTime;
        },
        /*
        *
        *	Converts a Javascript date object to a MYSQL datetime formatted string
        *	@param (date, Date)  JS Date to convert
        */
        setMysqlTime: function (date) {
            if (!date && date !== false) return null;
            //console.log(date);
            if (!date) var date = new Date();
            date = new Date(Date.parse(date));
            date = date.getFullYear() + '-' +
                ('00' + (date.getMonth()+1)).slice(-2) + '-' +
                ('00' + date.getDate()).slice(-2) + ' ' +
                ('00' + date.getHours()).slice(-2) + ':' +
                ('00' + date.getMinutes()).slice(-2) + ':' +
                ('00' + date.getSeconds()).slice(-2);
            return date;
        },
        setIsDirty: function(obj){
            obj.IsDirty = !obj.IsDirty;
            return obj;
        },
        sendEmail: function(emailDto, onSendEmail, onFailSendEmail, url){
            //use jsonp method of the angularjs $http object to ask the server to send an email
            return $http.post(  url, emailDto )
            .success( function( returnedObj ) {
                //console.log(returnedObj);
                onSendEmail(returnedObj, emailDto);
            })
            .error(function(data, status, headers, config, hazard){
                onFailSendEmail();
            });
        },
        watchersContainedIn: function(scope) {
            var watchers = (scope.$$watchers) ? scope.$$watchers.length : 0;
            var child = scope.$$childHead;
            while (child) {
                watchers += (child.$$watchers) ? child.$$watchers.length : 0;
                child = child.$$nextSibling;
            }
            return watchers;
        },

        //copy an object, not by reference
        copyObject: function(obj) {
            //var newObject = JSON.parse(JSON.stringify(obj));
            if (obj instanceof Array) {
                var array = [];
                var i = obj.length;
                while (i--) {
                    array.unshift($.extend(null, {}, obj[i]))
                }
                return array;
            } else {
                return $.extend(null, {}, obj);
            }
        },

        getDate: function(dateString){
            var seconds = Date.parse(dateString);
            //if( !dateString || isNaN(dateString) )return;
            var t = new Date(1970,0,1);
            t.setTime(seconds);
            return t;
        },

        userLoggedOut: function (data) {
            if (data.Class && data.Class == "ActionError") {
                $rootScope.requestError = data.Message;
                console.log(location)
                alert("Your session has expired. Please login again.");
                //window.location.replace( location.host + location.port + "/rsms" );
                window.location = "http://" + location.host + "/rsms";
            }
        },
        dateToIso: function (input, object, propertyName, setToString, nullable) {

            if (!input && !nullable) {
                return "N/A";
            } else if (!input && nullable) {
                return null;
            }

            // Split timestamp into [ Y, M, D, h, m, s ]
            var t = input.split(/[- :]/);
            // Apply each element to the Date function
            var d = new Date(t[0], t[1] - 1, t[2]);

            //at times like these, it's important to consider the nature of addition, concatonation and the universe in general.
            input = d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
            if (object && propertyName) {
                if (!setToString) {
                    object["view_" + propertyName] = d;
                } else {
                    object["view_" + propertyName] = input;
                }
            }
            if (t[0] == "0000" && !nullable) return "N/A";
            return input
        }
        
    }
    return methods;
})
.filter('dateToISO', function (convenienceMethods) {
    return function (input, object, propertyName, setToString) {
        return convenienceMethods.dateToIso(input, object, propertyName, setToString);
    };
})
.filter('dateToIso', function (convenienceMethods) {
    return function (input, object, propertyName, setToString) {
        return convenienceMethods.dateToIso(input, object, propertyName, setToString);
    };
})
.filter('activeOnly', function() {
    return function(array) {
            if(!array)return;
            var activeObjects = [];

            var i = array.length;
            while(i--){
                if(array[i].Is_active)activeObjects.unshift(array[i]);
        }
        return activeObjects;
    };
})
.filter('tel', function () {
    return function (tel) {
        if (!tel) { return ''; }

        var value = tel.toString().trim().replace(/^\+/, '');
        /*
        if (value.match(/[^0-9]/)) {
            console.log(tel);
            return tel;
        }
        */
        var city = value.slice(0, 3);
        var number = value.slice(3);

        number = number.slice(0, 3) + '-' + number.slice(3);
        return ("(" + city + ") " + number).trim();
    }
})
.filter("sanitize", ['$sce', function($sce) {
    return function(htmlCode){
        return $sce.trustAsHtml(htmlCode);
    }
}])
.directive('scrollTable', ['$window', '$location', '$rootScope', '$timeout', function($window, $location, $rootScope,$timeout) {
    return {
        restrict: 'A',
        scope: {
            watch: "="
        },
        link: function(scope, elem, attrs) {
             $(document).find('.container-fluid').prepend(
                '<div class="hidey-thing"></div>'
             )
             $('body').css({"minHeight":0})
             $(elem[0]).addClass('scrollTable');
             $(elem[0]).find('tbody').css({"marginTop": $(elem[0]).find('thead').height()});
             var setWidths = function(){
                var firstRow = elem.find('tbody').find('tr:first');
                $(elem).find('thead').find("th").each(function(index) {
                    $(this).width( firstRow.children("td").eq(index-1).width() );
                });
                $(elem[0]).find('> tbody').css({"marginTop": $(elem[0]).find('thead').height()});

             }
             $(window).load(function() {setWidths();});

             scope.$watch('watch', function() {
                 //console.log('length changed')
                 $timeout(function(){
                    setWidths();
                },300)

             });
             angular.element($window).bind('resize', function() {setWidths();})
        }
    }
}])
.directive('ulTableHeights', ['$window', '$location', '$rootScope', '$timeout', function ($window, $location, $rootScope, $timeout) {
    return {
        restrict: 'C',
        scope: {
            watch: "=",
            otherwatch: "="
        },
        link: function (scope, elem, attrs) {
            scope.attrs = attrs;
            scope.$watch('attrs', function (oldVal, newVal) {
                if (!newVal || newVal == 0) return false;
                resize(attrs, elem, newVal)
            });

            resize(attrs, elem, scope.watchedThing);

            function resize(attrs, elem) {
                var len = elem.find('ul').length;
                if (!attrs.h) {
                    attrs.$set('h', elem.outerHeight());
                    attrs.$set('len', len);
                }
                elem.find('ul > li').css({ 'paddingTop': (attrs.h / (len)) - 17 + 'px', 'paddingBottom': (attrs.h / (len)) -8 + 'px', 'height': 0 });
            }
        }
    }
}])
.filter('propsFilter', function () {

  return function(items, props) {
      var out = [];
      if (!items || !props) return out;
      var keys = Object.keys(props);
      if (keys[0].indexOf(".") > 0) {
          var properties = keys[0].split('.');
      } else {
          var properties = keys;
      }
    if (angular.isArray(items)) {
      items.forEach(function(item, key) {
        var itemMatches = false;
        if (item && item != null) {
            var myResultItem = item;

            for (var i = 0; i < properties.length; i++) {
                if (myResultItem[properties[i]]) {
                    myResultItem = myResultItem[properties[i]];
                }
            }
            if (myResultItem) {
                var text = props[properties.join('.')].toLowerCase();
                if (myResultItem.toString().toLowerCase().indexOf(text) !== -1) itemMatches = true;
            }

            if (itemMatches) {
                out.push(item);
            }
        }
      });
    } else {
      // Let the output be the input untouched
      out = items;
    }
    return out;
  }
})
.filter('roundFloat', function () {
    return function (item) {
        var number = parseFloat(item);
        return Math.round(number * 100000) / 100000 || "0";
    }
})
.filter('getDueDate', function () {
    return function (input) {
        var date = new Date(input);
        var duePoint = date.setDate(date.getDate() + 14);
        dueDate = new Date(duePoint).toISOString();
        return dueDate;
    };
})
.filter('getMonthName', function () {
    var monthNames = [{ val: "01", string: "January" },
                { val: "02", string: "February" },
                { val: "03", string: "March" },
                { val: "04", string: "April" },
                { val: "05", string: "May" },
                { val: "06", string: "June" },
                { val: "07", string: "July" },
                { val: "08", string: "August" },
                { val: "09", string: "September" },
                { val: "10", string: "October" },
                { val: "11", string: "November" },
                { val: "12", string: "December" }]
    return function (input) {
        var i = monthNames.length;
        while (i--) {
            if (input == monthNames[i].val) return monthNames[i].string;
        }
    };
})
//is a user a lab contact?  run this fancy filter to find out.
.filter('isContact',[function(){
  return function(users){
    if(!users)return;
    var contacts = [];
    var i = users.length
    while(i--){
        var j = users[i].Roles.length;
        while(j--){
            if(users[i].Roles[j].Name == Constants.ROLE.NAME.LAB_CONTACT){
                contacts.unshift(users[i]);
                break;
            }
        }
    }
    return contacts;
  }

}]);

angular.module('departmentHub', ['ui.bootstrap', 'convenienceMethodWithRoleBasedModule','ngRoute','once'])

.filter('specialtyLab_trueFalse', function(){
  return function(departments, bool){
    if(!departments)return;
    if(bool == null)bool = true;
    var changedThings = [];
    var i = departments.length;
    while(i--){
      if( departments[i].Specialty_lab == bool || (departments[i].Specialty_lab == null && !bool) ){
        changedThings.push(departments[i]);
      }
    }
    return changedThings;
  }
})
.filter("matchCampus", function(){
    return function(departments, campusName){
        if(!departments)return;
        if(!campusName)return departments;
        var i = departments.length;
        var matches = [];
        while(i--){
            if(departments[i].Campus_name == campusName){
                matches.unshift(departments[i]);
            }
        }
        return matches;
    }
})
.filter("removeNulls", function(){
    return function(departments){
        if(!departments)return;
        var i = departments.length;
        var matches = [];
        while(i--){
            var noPush = false;
            if(!departments[i].Campus_name){
                var j = departments.length
                while(j--){
                    if(j != i && departments[j].Department_name == departments[i].Department_name){
                        noPush = true;
                        break;
                    }
                }
            }
            if(!noPush){
                matches.push(departments[i]);
            }
        }
        return matches;
    }
})
.factory('departmentFactory', function(convenienceMethods,$q){
    var factory = {};
    var inspection = {};
    factory.getAllDepartments = function(url){
        var url = "../../ajaxaction.php?action=getAllDepartmentsWithCounts&callback=JSON_CALLBACK";
        var deferred = $q.defer();

        convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject(promise);
            }
        );
        return deferred.promise
    }
    factory.setDepartments = function(departments){
        this.departments = departments
    }

    factory.getDepartments = function(){
        return this.departments;
    }
    factory.editNoDepartments = function(){
        var len = this.departments.length;
        for(i=0;i<len;i++){
            this.departments[i].edit = false;
        }
        return this.departments;
    }
    factory.saveDepartment = function(department){
        var url = "../../ajaxaction.php?action=saveDepartment";
        var deferred = $q.defer();

        convenienceMethods.saveDataAndDefer(url, department).then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject(promise);
          }
        );
        return deferred.promise
    }
    factory.getAllCampuses = function(){
        var url = "../../ajaxaction.php?action=getAllCampuses&callback=JSON_CALLBACK";
        var deferred = $q.defer();

        convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject(promise);
            }
        );
        return deferred.promise
    }
    return factory;
});

departmentHubController = function($scope,departmentFactory,convenienceMethods, $modal){

    function init(){
        departmentFactory.getAllCampuses()
            .then(
                function(campuses){
                    $scope.campuses = campuses;
                    getDepartments();
                }
            )
    }

    init();

    function getDepartments(){
        departmentFactory.getAllDepartments().then(
            function(promise){
                departmentFactory.setDepartments(promise);
                $scope.departments = departmentFactory.getDepartments();
            },
            function(promise){
                $scope.error = 'The system couldn\'t get the list of departments.  Please check your internet connection and try again.'
            }
        )
    }

    $scope.editDepartment = function(department){
        $scope.departments = departmentFactory.editNoDepartments();
        department.edit = true;
        // create a copy of this department so we can cancel edit if necessary
        $scope.departmentCopy = angular.copy(department);
    }

    $scope.cancelEdit = function(department){
        department.edit = false;
        $scope.departmentCopy = {};
        $scope.departments = departmentFactory.editNoDepartments();
        $scope.creatingDepartment = false;
        $scope.newDepartment = false;
    }

    $scope.createDepartment = function(){
        $scope.creatingDepartment = true;
        $scope.newDepartment = {
            Name:'',
            Class:'Department',
            Is_active:true
        }
    }

    $scope.handleActive = function(department){
        department.isDirty = true;
        $scope.error = '';
        deptDto = {
            Class: "Department",
            Name: department.Department_name,
            Is_active: !department.Is_active,
            Key_id: department.Department_id
        }
        departmentFactory.saveDepartment(deptDto).then(
          function(promise){
              console.log(promise);
              department.Is_active = promise[0].Is_active;
              department.isDirty = false;

          },
          function(promise){
            $scope.error = 'There was a promblem saving the department.';
            department.isDirty = false;
          }
        );

    }

    $scope.openModal = function(dto, isSpecialtyLab){
        var instance = $modal.open({
            templateUrl: 'departmentModal.html',
            controller: 'modalCtrl',
            resolve: {
                departmentDto: function(){
                   if(dto) return dto;
                   return {};
                },
                specialtyLab:function(){
                    return isSpecialtyLab;
                }
            }
        });
        instance.result.then(function (returnedDto) {
            if(!convenienceMethods.arrayContainsObject($scope.departments,returnedDto, ["Department_id", "Department_id"])){
                $scope.departments.push(returnedDto)
            }else{
                angular.extend(dto, returnedDto);
            }
        });
    }
}
modalCtrl = function($scope, departmentDto, specialtyLab, $modalInstance, departmentFactory, convenienceMethods){
    $scope.department = {
        Class: "Department",
        Name:'',
        Is_active:true,
        Specialty_lab:specialtyLab
    }
    $scope.specialtyLab = specialtyLab;

    if(departmentDto.Department_id){
        $scope.department.Name =   departmentDto.Department_name;
        $scope.department.Key_id = departmentDto.Department_id;
        $scope.deptName = departmentDto.Department_name;
    }
    // overwrites department with modified $scope.departmentCopy
    // note that the department parameter is the department to be overwritten.
    $scope.saveDepartment = function(){
        var prevent = false;
        var depts = departmentFactory.getDepartments();
        for(var i = 0; i < depts.length; i++){
            if((depts[i].Department_name.toLowerCase() == $scope.department.Name.toLowerCase()) && (!$scope.department.Key_id || $scope.department.Key_id != depts[i].Department_id)){
                prevent = true;
            }
        }
        
        
        // prevent user from changing name to an already existing department
        if(prevent)         {
            $scope.error = "Department with name " + $scope.department.Name + " already exists!";
            // TODO: sort out department vs $scope.departmentCopy (ie department passed in, but still has to use departmentCopy, a scope variable)
            // Mixed up here, later in this method, and in departmentHub.php itself.
            return false;
        }
        $scope.isDirty = true;
        $scope.error = '';
        departmentFactory.saveDepartment($scope.department).then(
          function(promise){
              console.log(promise);
              $modalInstance.close(promise[0]);
          },
          function(promise){
            $scope.error = 'There was a promblem saving the department.';
            $scope.isDirty = false;
          }
        );
    }

    $scope.cancel = function(){
        $scope.error = '';
        $modalInstance.dismiss();
    }

}

var emergencyInfo = angular.module('emergencyInfo', ['ui.bootstrap','convenienceMethodWithRoleBasedModule'])

.factory('emergencyInfoFactory', function( convenienceMethods, $q, $rootScope ){

    var factory = {};

    factory.getAllPIs = function()
    {
        //if we don't have a the list of pis, get it from the server
        //var deferred = $q.defer();
        var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.getAllBuildings = function()
    {

        var url = '../../ajaxaction.php?action=getAllBuildings&skipRooms=true&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.getHazards = function(room)
    {

        //the server expects an array of roomIds, but we are only going to send one, so wrap it in an array;
        var rooms = [room.Key_id];
        var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );

    }

    factory.onSelectPIOrBuilding = function( object )
    {
        console.log(object);
        var len = object.Rooms.length;
        var displayRooms = [];

        while( len-- ){
            var room = object.Rooms[len];
            room.roomText = 'Room: '+room.Name;

            if(room.Building){
              room.roomText = room.roomText + ' | ' + room.Building.Name;
              if(room.Building.Physical_address) room.roomText = room.roomText + ' | ' + room.Building.Physical_address;
            }

        }

        if(!object.Rooms)$rootScope.error = "The selected location or PI has no rooms in the system."
        $rootScope.rooms = object.Rooms;

    }

    factory.onSelectPI = function( pi )
    {
        $rootScope.gettingRoomsForPI = true;
        this.getRoomsByPI( pi )
          .then(
            function( rooms ){
                console.log(rooms);
                pi.Rooms = rooms;
                var displayRooms = [];
                var len = pi.Rooms.length;
                while( len-- ){
                    var room = pi.Rooms[len];
                    room.roomText = 'Room: '+room.Name;
                    if(room.Building){
                      room.roomText = room.roomText + ' | ' + room.Building.Name;
                      if(room.Building.Physical_address) room.roomText = room.roomText + ' | ' + room.Building.Physical_address;
                    }
                }
              $rootScope.gettingRoomsForPI = false;
              if(!pi.Rooms)$rootScope.error = "The selected location or PI has no rooms in the system."
              $rootScope.rooms = pi.Rooms;
            }
          )
    }

    factory.getRoomsByPI = function(pi)
    {
        var url = '../../ajaxaction.php?action=getRoomsByPIId&piId='+pi.Key_id+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.onSelectBuilding = function( building )
    {
        $rootScope.rooms = null;
        $rootScope.gettingRooms = true;
        console.log(building);
        if(building.Rooms){
          factory.onSelectPIOrBuilding(building);
        }else{
          var url = '../../ajaxaction.php?action=getRoomsByBuildingId&id='+building.Key_id+'&callback=JSON_CALLBACK';
          convenienceMethods.getDataAsDeferredPromise(url).then(
              function(promise){
                building.Rooms = promise;
                $rootScope.gettingRooms = false;
                factory.onSelectPIOrBuilding(building );
              },
              function(promise){
              }
          );
        }
    }

    factory.getPIsByRoom = function( room )
    {

        var url = '../../ajaxaction.php?action=getPIsByRoomId&id='+room.Key_id+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.noSubHazardsPresent = function( hazard )
    {
        if(hazard.ActiveSubHazards.every(this.hazardIsNotPresent))return true;
        return false;
    }

    factory.hazardIsNotPresent = function( hazard )
    {
        console.log(hazard);
        if(!hazard.IsPresent)return true;
        return false;
    }

    return factory;
});

//called on page load, gets initial user data to list users
function emergencyInfoController(  $scope, $rootScope, convenienceMethods, emergencyInfoFactory ) {
  $scope.users = [];
  var eif = emergencyInfoFactory;
  $scope.eif = eif;

  init();

  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
      //get a building list
      eif.getAllBuildings()
        .then(
            function(buildings){
                $scope.buildings = buildings;
                return buildings;
            },
            function(e){
                $scope.error = 'The system couldn\'t load the list of Buildings.  Please check your internet connection and try again.'
            }
        );

      //get a PI list
      eif.getAllPIs()
        .then(
            function(pis){
                $scope.pis = pis;
                return pis;
            },
            function(e){
                $scope.error = 'The system couldn\'t load the list of Principal Investigators.  Please check your internet connection and try again.'
            }
        );
  };

  //grab set user list data into the $scrope object
  function onGetBuildings(data) {
    $scope.Buildings = data;
    $scope.error = '';

    if($location.search().building){
      angular.forEach($scope.Buildings, function(building, key){
        if(building.Key_id == $location.search().building){
          $scope.building = building;
          $scope.selectedBuilding = building;
        }
      });

    }

  }
  function onFailGet(){
   $scope.error = 'Something went wrong when we tried to build the list of buildings.';
  }

  $scope.onSelectRoom = function( room )
  {
        $scope.loading = true;
        $scope.selectedRoom = room;
        eif.getHazards( room ).
          then(
            function(rootHazard){
              console.log(room);
              $scope.hazards = rootHazard.ActiveSubHazards;

              eif.getPIsByRoom(room)
                .then(
                    function(pis){
                      $scope.pisByRoom = pis;
                      $scope.personnel = [];
                      var len = pis.length;
                      while(len--){
                          $scope.personnel = $scope.personnel.concat(pis[len].LabPersonnel);
                      }
                      $scope.loading = false;
                      $scope.showingHazards = true;
                      var i = $scope.buildings.length;
                      while(i--){
                        if(room.Building_id == $scope.buildings[i].Key_id)$scope.building = $scope.buildings[i];
                      }

                      $scope.room = room;
                    }
                )


            },
            function(){

            }

          )

  }


};

var hazardHub = angular.module('hazardHub', ['convenienceMethodWithRoleBasedModule','infinite-scroll','once']);

hazardHub.filter('makeUppercase', function () {
  return function (item) {
    return item.toUpperCase();
  };
});

hazardHub.directive('buttongroup', ['$window', function($window) {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs) {
            console.log(elem.find('.hazarNodeButtons').length);
            scope.onResize = function() {
                w = elem.width();
                if(w<1200 && $($window).width()>1365){
                     elem.addClass('small');
                }else if(w<1140 && $($window).width()<1365){
                     elem.addClass('small');
                }else{
                    elem.removeClass('small');
                }

                //this code ensures that the hazard names, buttons and toggle buttons all line up properly, displaying cleanly even with linebreaks

                //get the width of the container element of for our buttons
                var btnWidth  = elem.children().children().children('.hazarNodeButtons').width();
                //set the width of all the elements on the left side of our hazard li elements
                var leftWidth = w - btnWidth - 50;
                elem.children().children().children('.leftThings').width(leftWidth);
                elem.children().children().children('.leftThings').children('span').css({width:leftWidth-90+'px'});

            }
            scope.onResize();

            scope.$watch(
                function(){
                    return scope.onResize();
                }
            )

            angular.element($window).bind('resize', function() {
                scope.onResize();
            });
        }
    }
}])

hazardHub.factory('hazardHubFactory', function(convenienceMethods,$q){
    var factory = {};
    factory.saveHazard = function(hazard){
        var url = "../../ajaxaction.php?action=saveHazardWithoutReturningSubHazards";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, hazard).then(
            function(promise){
                console.log(promise);
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise;
    }
    return factory;
});

hazardHub.controller('TreeController', function ($scope, $timeout, $location, $anchorScroll, convenienceMethods, hazardHubFactory, roleBasedFactory, $rootScope) {

    init();

    //call the method of the factory to get users, pass controller function to set data inot $scope object
    //we do it this way so that we know we get data before we set the $scope object
    //
    function init(){
        $rootScope.rbf = roleBasedFactory;
        $scope.doneLoading = false;
        //we pass 10000 as the id to this request because 10000 will always be the key_id of the root hazard
        convenienceMethods.getData('../../ajaxaction.php?action=getHazardTreeNode&id='+10000+'&callback=JSON_CALLBACK', onGetHazards, onFailGet);
    }
    //grab set user list data into the $scrope object
    function onGetHazards (data) {
        delete data.doneLoading;
        $scope.SubHazards = data;
        $scope.doneLoading = true;
    }

    function onFailGet(){
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();
        }
    }

    $scope.toggleMinimized = function (child, adding) {
        $scope.error = null;
        $scope.openedHazard = child;
        child.minimized = !child.minimized;
        if(!child.SubHazards){
            child.loadingChildren = true;
            convenienceMethods.getDataAsDeferredPromise('../../ajaxaction.php?action=getHazardTreeNode&id='+child.Key_id+'&callback=JSON_CALLBACK')
                .then(function(subs){
                    child.SubHazards = subs;
                    child.loadingChildren = false;
                }, function(){
                    child.loadingChildren = false;
                    $scope.error = "There was a problem loading the list of Subhazard for " +child.Name+ ".  Please check your internet connection."
                });
        }
    };


    //call back for asynch loading of a hazard's suhazards
    function onGetSubhazards (data, hazard, adding){
        hazard.loadingChildren = false;

        hazard.SubHazardsHolder = data;
        hazard.numberOfPossibleSubs = hazard.SubHazardsHolder.length;
        hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].lastSub = true;

      //  //console.log( hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].Name);
        $scope.openedHazard = hazard;

       // var counter = Math.min(hazard.SubHazardsHolder.length-1, 2000 );
       // if(adding)buildSubsArray(hazard, 0, counter, adding);
        //if(!adding)buildSubsArray(hazard, 0, counter);

    }

    function onFailGetSubhazards(){
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();
        }
    }

    $scope.setSubs = function(hazard, handlerCheck, adding){
       // console.log(test);
        if($scope.openedHazard && hazard.SubHazardsHolder){

            if(!$scope.openedHazard.SubHazards)$scope.openedHazard.SubHazards = [];

            //get the number of subhazards loaded, get the number of possible subhazards
            //if they are the same, do nothing because we have loaded all possible hazards
            if(hazard.SubHazardsHolder.length > hazard.SubHazards.length){
                if(handlerCheck == 'addToBottom'){
                    hazard.firstIndex += 5;
                    var numberOfHazardsLeftToPush = hazard.SubHazardsHolder.length - (hazard.firstIndex+15);
                    var start = hazard.numberOfPossibleSubs-(hazard.firstIndex+15);

                    if(numberOfHazardsLeftToPush < 15){
                        start = hazard.SubHazardsHolder.length-15;
                    }else{
                        start = hazard.firstIndex;
                    }

                    limit = start + 14;
                    buildSubsArray(hazard, start, limit);

                }

             if(handlerCheck == 'addToTop'){

                   if(hazard.firstIndex > 4){
                      hazard.firstIndex -= 5;
                    }else{
                      hazard.firstIndex = 0;
                    }
                    var numberOfHazardsLeftToPush = hazard.SubHazardsHolder.length - (hazard.firstIndex+15);
                    var start = hazard.firstIndex;

                    limit = start + Math.min(15,hazard.SubHazardsHolder.length);
                    buildSubsArray(hazard, start, limit);
                }
            }
        }
    }

    function buildSubsArray(hazard, start, limit, adding){
        //console.log('building');
        hazard.firstIndex = start;
        hazard.SubHazards = [];
        for(start; start<=limit; start++){
            hazard.SubHazardsHolder[start].displayIndex = start;
            hazard.SubHazards.push(hazard.SubHazardsHolder[start]);
        }
        if(adding)addChildCallback(hazard);
    }

    $scope.SubHazards = {
        SubHazards: []
    }

    $scope.remove = function (child) {
        function walk(target) {
            var children = target.SubHazards,
                i;
            if (children) {
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i])
                    }
                }
            }
        }
        walk($scope.SubHazards);
    }

    $scope.editHazard = function(hazard){
        hazard.isBeingEdited = true;
        $scope.hazardCopy = angular.copy(hazard);
    }

    $scope.addChild = function (child) {
        $scope.parentHazard = {};

        if(!child.HasChildren){
            child.SubHazards = [];
            addChildCallback(child);
        }else{
            if(!child.SubHazards){
               $scope.toggleMinimized(child, true);
            }else{
               addChildCallback(child);
            }
        }

    };

    function addChildCallback(child, copy){

        child.minimized = false;

        $scope.hazardCopy = {};

        $scope.parentHazard = child.Key_id;

        $scope.parentHazardForSplice = child;

        $scope.hazardCopy = {
            isNew: true,
            isBeingEdited: true,
            Name: '',
            Parent_hazard_id: child.Key_id,
            SubHazards: [],
            Class: 'Hazard',
            Is_active: true,
            HasChildren:false
        }

        child.SubHazards.unshift($scope.hazardCopy);
        child.HasChildren = true;
        console.log( $scope.hazardCopy );
    }

    $scope.saveEditedHazard = function(hazard){
        if(!$scope.hazardCopy.Class){
            $scope.hazardCopy.Class = "Hazard";
        }
        if(!hazard.Name || hazard.Name.trim() == ''){
            hazard.Invalid = true;
        }else{
            hazard.IsDirty = true;

            // server lazy loads subhazards, save any subhazards present to re-add manually.
            var previousSubHazards = hazard.SubHazards;

            hazardHubFactory.saveHazard($scope.hazardCopy).then(
                function(returnedHazard){
                    hazard.isBeingEdited = false;
                    hazard.IsDirty = false;
                    hazard.Invalid = false;
                    $scope.hazardCopy = {};

                    if(previousSubHazards !== null && previousSubHazards.length !== 0) {
                        // restore subhazards
                        returnedHazard.SubHazards = previousSubHazards;
                        onGetSubhazards(previousSubHazards, hazard);
                    }

                    angular.extend(hazard, returnedHazard);
                    hazard.Key_id = returnedHazard.Key_id;
                },
                function(){
                    hazard.error = hazard.Name + ' could not be saved.  Please check your internet connection and try again.'
                }
            )

        }
    }
      //if this function is called, we have received a successful response from the server
    function onSaveHazard( dto, hazard, test ){

        //temporarily use our hazard copy client side to bandaid server side bug that causes subhazards to be returned as indexed instead of associative
        convenienceMethods.setPropertiesFromDTO( dto, hazard );
        console.log(hazard);
        console.log(dto);
        hazard.isBeingEdited = false;
        hazard.IsDirty = false;
        hazard.Invalid = false;
        $scope.hazardCopy = {};
    }

    function onFailSave(obj){
        alert('There was a problem saving '+obj.Name);
    }


    $scope.cancelHazardEdit = function(hazard, $index){
        console.log(hazard);
        if(hazard.isNew === true){
            return  $scope.parentHazardForSplice.SubHazards.splice( $scope.parentHazardForSplice.SubHazards.indexOf( hazard ), 1 );
        }

        hazard.isBeingEdited = false;
        $scope.hazardCopy = {};

    }

    $scope.handleHazardActive = function(hazard){
        hazard.IsDirty = true;
        $scope.hazardCopy = angular.copy(hazard);
        $scope.hazardCopy.Is_active = !$scope.hazardCopy.Is_active;
        if($scope.hazardCopy.Is_active === null)hazard.Is_active = false;
        hazard.IsDirty = true;

        // server lazy loads subhazards, save any subhazards present to re-add manually.
        var previousSubHazards = hazard.SubHazards;

        hazardHubFactory.saveHazard($scope.hazardCopy).then(
            function(returnedHazard){
                hazard.isBeingEdited = false;
                hazard.IsDirty = false;
                hazard.Invalid = false;
                $scope.hazardCopy = {};

                if(previousSubHazards !== null && previousSubHazards.length !== 0) {
                    // restore subhazards
                    returnedHazard.SubHazards = previousSubHazards;
                    onGetSubhazards(previousSubHazards, hazard);
                }

                angular.extend(hazard, returnedHazard);
                hazard.Key_id = returnedHazard.Key_id;
            },
            function(){
                hazard.error = hazard.Name + ' could not be saved.  Please check your internet connection and try again.'
            }
        )
    }

    //by default, this is true.  This means that we will display hazards with a TRUE Is_active property
    //returns boolean to determine if a hazard should be shown or hidden based on user input and the hazard's Is_active property
    $scope.getShowHazard = function(hazard){
        hazard.show = false;
        //return true for all hazards if $scope.SubHazards.activeMatch is null or undefined
        if(!$scope.SubHazards[$scope.SubHazards.length-1].activeMatch) hazard.show = true;
        //if we have a $scope.activeMatch, return true for the hazards that have a matchin Is_active property.
        //i.e. display only hazards with a FALSE for Is_active if $scope.SubHazards.activeMatch is false
        //The server will give us 0 or 1 boolean for these values.  0 and 1 are not actual boolean values in JS, so we must to a two step check here.
        if(hazard.Is_active == 0 || hazard.Is_active == false && $scope.SubHazards[$scope.SubHazards.length-1].activeMatch === false){
            hazard.show = true;
            return
        }
        if(hazard.Is_active == 1 || hazard.Is_active == true && $scope.SubHazards[$scope.SubHazards.length-1].activeMatch === true){
            hazard.show = true;
            return;
        }
        console.log(hazard);

    }

    $scope.hazardFilter = function(hazard){
      if($scope.hazardFilterSetting.Is_active == 'both'){
        return true;
      }else if($scope.hazardFilterSetting.Is_active == 'active'){
        if(hazard.Is_active == true)return true;
      }else if($scope.hazardFilterSetting.Is_active == 'inactive'){
        if(hazard.Is_active == false)return true;
      }
      return false;
    }

    $scope.moveHazard = function(idx, parent, direction, filteredSubHazards){
        //Make a copy of the hazard we want to move, so that it can be temporarily moved in the view
        var clickedHazard   = angular.copy(parent.SubHazards[idx]);
        filteredSubHazards[idx].IsDirty = true;
        if(direction == 'up'){
            //We are moving a hazard up. Get the indices of the two hazards above it.
            var afterHazardIdx = idx-1;
            var beforeHazardIdx = idx-2;
        }else if(direction == 'down'){
            //We are moving a hazard down.  Get the indices of the two hazards below it.
            var beforeHazardIdx = idx+1;
            var afterHazardIdx = idx+2;
        }else{
            return
        }

        //get the key_ids of the hazards involved so we can build the request.
        var hazardId       = filteredSubHazards[idx].Key_id;

        //if we are moving the hazard up to the first spot, the index for the before hazard will be - 1, so we can't get a key_id
        if(beforeHazardIdx > -1){
            var beforeHazardId = filteredSubHazards[beforeHazardIdx].Key_id;
        }else{
            var beforeHazardId = null
        }

        //if we are moving the hazard down to the last spot, the index for the before hazard will out of range, so we can't get a key_id
        if(afterHazardIdx < filteredSubHazards.length){
            var afterHazardId = filteredSubHazards[afterHazardIdx].Key_id;
       }else{
            var afterHazardId = null;
       }

        var url = '../../ajaxaction.php?action=reorderHazards&hazardId='+hazardId+'&beforeHazardId='+beforeHazardId+'&afterHazardId='+afterHazardId;

        //make the call
        convenienceMethods.saveDataAndDefer(url, clickedHazard).then(
            function(promise){
                filteredSubHazards[idx].IsDirty = false;
                filteredSubHazards[idx].Order_index = promise.Order_index;
            },
            function(){
                filteredSubHazards.error = true;
                $scope.error="The hazard could not be moved.  Please check your internet connection.";
            }
        );
    }

    $scope.order = function(hazard){
        return parseFloat(hazard.Order_index);
    }
});

var homeApp = angular.module('homeApp', ['ui.bootstrap','convenienceMethodWithRoleBasedModule']);

homeApp
    .config(function($routeProvider){
        $routeProvider
            .when('/home',
                {
                    templateUrl: '../views/rsmsCenterPartials/home.html',
                    controller: homeController
                }
            )
            .when('/admin',
                {
                    templateUrl: '../views/rsmsCenterPartials/admin.html',
                    controller: adminController
                }
            )
            .when('/inspections',
                {
                    templateUrl: '../views/rsmsCenterPartials/inspections.html',
                    controller: adminController
                }
            )
            .when('/safety-programs',
                {
                    templateUrl: '../views/rsmsCenterPartials/safety-programs.html',
                    controller: adminController
                }
            )
            .when('/biosafety-programs',
                {
                    templateUrl: '../views/rsmsCenterPartials/biosafety-programs.html',
                    controller: adminController
                }
            )
            .otherwise(
                {
                    redirectTo: '/home'
                }
            );
    })

var testController = function($location, $scope, $rootScope, roleBasedFactory){
    $scope.setRoute = function(route){
        $location.path(route);
    }
    $scope.setRoute = function(route){
        $location.path(route);
    }

}

var homeController = function($location, $scope, $rootScope){
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}

var adminController = function($location, $scope){
    $scope.view = 'home';
    $scope.setRoute = function(route){
        $location.path(route);
    }
}

$(".collapse").bind("transition webkitTransition oTransition MSTransition", function(){
    alert('transitions');
});


var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu', 'convenienceMethodWithRoleBasedModule', 'once', 'angular.filter', 'cgBusy', 'ui.tinymce'])
.filter('categoryFilter', function () {
    return function (items, category ) {
            if( !category ) return false;
            var i = items.length;
            var filtered = [];
            while(i--){
                var item = items[i];
                if( item.Master_hazard.toLowerCase().indexOf(category.toLowerCase()) > -1 )	filtered.unshift( item );
            }
            return filtered;

    }
})
.filter('countRecAndObs', function () {
    return function ( questions ) {
            if( !questions ) return;
            var i = questions.length;
            while(i--){
                var question = questions[i];
                question.checkedRecommendations = 0;
                if(question.Responses && question.Responses.Recommendations)question.checkedRecommendations = question.Responses.Recommendations.length;
                if(question.Responses && question.Responses.SupplementalRecommendations){
                    var j = question.Responses.SupplementalRecommendations.length;
                    while(j--){
                        if(question.Responses.SupplementalRecommendations[j].Is_active)question.checkedRecommendations++;
                    }
                }

                question.checkedNotes = 0;
                if(question.Responses && question.Responses.Observations)question.checkedNotes = question.Responses.Observations.length;
                if(question.Responses && question.Responses.SupplementalObservations){
                    var j = question.Responses.SupplementalObservations.length;
                    while(j--){
                        if(question.Responses.SupplementalObservations[j].Is_active)question.checkedNotes++;
                    }
                }
            }
            return questions;
    }
})
.filter('roomChecked', function (checklistFactory) {
    return function (rooms, question, deficiency ) {
        if (!rooms) return;
        matches = [];
        for (var i = 0; i < rooms.length; i++) {
            if (checklistFactory.evaluateDeficiencyRoomChecked(rooms[i], question, deficiency)) {
                rooms[i].checked = true;
                matches.push(rooms[i]);
            } else {
                rooms[i].checked = false;
            }
        }
        return matches;
    }
})
.filter('evaluateChecklist', function () {
    return function (questions, checklist) {
            checklist.completedQuestions = 0;
            if(!checklist.Questions) return questions;
            var i = checklist.Questions.length;
            checklist.activeQuestions = [];
            while(i--){
                var question = checklist.Questions[i];
                if(question.Is_active){
                    if( !question.Responses ){
                        question.isComplete = false;
                    }
                    else if( !question.Responses.Answer ){
                        question.isComplete = false;
                        //question doesn't have an answer but does have one or more recommendations selected
                        if(question.Responses.Recommendations && question.Responses.Recommendations.length){
                            question.isComplete = true;
                        }
                        if(question.Responses.SupplementalRecommendations && question.Responses.SupplementalRecommendations.length){
                            var j = question.Responses.SupplementalRecommendations.length;
                            while (j--) {
                                if (question.Responses.SupplementalRecommendations[j].Is_active) {
                                    question.isComplete = true;
                                    break;
                                }
                            }
                        }
                        if(question.isComplete){
                            checklist.completedQuestions++;
                        }
                    }
                    else if( question.Responses.Answer.toLowerCase() == "yes" || question.Responses.Answer.toLowerCase() == "n/a" ){
                        question.isComplete = true;
                        checklist.completedQuestions++;
                    }
                    //question is answered "no"
                    else{
                        //question has no deficiencies to select, or none selected if it does have ones to select
                        if ((!question.Responses.DeficiencySelections || !question.Responses.DeficiencySelections.length)
                            && ((!question.Responses.SupplementalDeficiencies || !question.Responses.SupplementalDeficiencies.length)
                            || (question.Responses.SupplementalDeficiencies.length && question.Responses.SupplementalDeficiencies.every(function (sd) { return !sd.Is_active })))) {
                            question.isComplete = false;
                        }
                        
                        //question has one or more deficiencies selected
                        else {

                            question.isComplete = true;
                            checklist.completedQuestions++;
                        }
                    }
                    checklist.activeQuestions.unshift(question);
                }
            }
            return checklist.activeQuestions;
    }
})
.filter("showNavItem", function(checklistFactory){
        return function (items, inspection){
            if(!items)return;
            var relevantItems = [];
            var lists = checklistFactory.inspection.Checklists;
            if(!lists)return;
            for (var i = 0; i < items.length; i++) {
                var push = false;

                for (var j = 0; j < lists.length; j++){
                    if(lists[j].Is_active && lists[j].Master_id == items[i].Key_id){
                        if(!checklistFactory.selectedCategory)checklistFactory.selectCategory( items[i] );
                        push = true;
                    }
                }
                var skips = [];
                if (inspection) {
                    if (inspection.Is_rad) {
                        skips = ["Biological", "Chemical", "General"];
                        //select radiation category
                        if (items[i].Label == "Radiation") {
                            checklistFactory.selectCategory(items[i]);
                        }
                    } else {
                        skips = ["Radiation"];
                    }

                    if (skips.indexOf(items[i].Label) > -1) {
                        push = false;
                    }
                }
               
                if (push) relevantItems.push(items[i]);
            }
            return relevantItems;
        }
    }
)
.filter("relevantLists", function(checklistFactory){
        return function (checklists){
            if(!checklists) return;
            if(!checklistFactory.selectedCategory)return;
            var relevantLists = [];
            for(var i = 0; i < checklists.length; i++){
                var push = false;
                if(checklists[i].Is_active && checklists[i].Master_id == checklistFactory.selectedCategory.Key_id){
                    relevantLists.push( checklists[i] );
                }
            }
            return relevantLists;
        }
    }
)
.directive("otherDeficiency",["checklistFactory", function(checklistFactory){
    return {
        restrict: "E"  , //E = element, A = attribute, C = class, M = comment
        replace: true,
        scope:{
            //scope variables we pass from view
            // i.e.  thing: '=' means that scope.thing, within the scope of the directive will be whatever you set the thing attribute of the directive markup to (<other-dificiency thing="someStuffFromTheViewScope")
            // thing: "="  //local scope.thing is a two-way bound reference to view scope
            // thing: "@"  //local scope.thing is bound one way and our local scope is isolated from the view
            // thing: "&"  //use this when passing a method of the view scope that you want to call in the directive
            selectionChange:"&",
            selectedTitle:"=",
            unselectedTitle:"@",
            textAreaContent:"@",
            param: "=",
            paramChild: "=",
            checkedOnInit:"&",
            textareaPlaceholder:"@",
            saveCall: "&"
        },
        templateUrl:'otherDeficiencyComponent.html',  //path to template
        link:function(){
            //stuff we want to do to the view
            //jQuery style DOM manipulation
        },
        controller: function($scope, checklistFactory, $parse){
            $scope.selectionChange = $parse($scope.selectionChange);
            //create a referenceless copy of the thing we want to edit
            $scope.checkboxChanged = function() {
                $scope.selectionChange();
            }
            $scope.$watch("selectedTitle", function(){
                $scope.param.Other_text = $scope.selectedTitle;
            })

            $scope.edit = function(){
                $scope.param.edit = true;
                $scope.param.freeText = $scope.param.Other_text;
            }

            $scope.cancel = function(){
                $scope.param.edit = false;
                $scope.param.selected = false;
            }
        }
    }
}])
.factory('checklistFactory', function(convenienceMethods,$q,$rootScope,$timeout,$location,$anchorScroll){

        var factory = {};
        factory.inspection = [];
        factory.categories = Constants.CHECKLIST_CATEGORIES_BY_MASTER_ID;

        factory.getHasOtherDeficiencyies = function (question) {
            alert('wtf')
            if (question.Responses && question.Responses.DeficiencySelections) {
                if (!question.otherDefIds) question.otherDefIds = [];
                var i = question.Responses.DeficiencySelections.length;
                while(i--){
                    if (question.Responses.DeficiencySelections[i].Other_text && question.otherDefIds.indexOf(question.Responses.DeficiencySelections[i].Key_id) < 0) {
                        var otherDef = {
                            Class: "Deficiency",
                            Is_active: true,
                            Question_id: question.Key_id,
                            Other_text: question.Responses.DeficiencySelections[i].Other_text,
                            Deficiency_selection_id: question.Responses.DeficiencySelections[i].Key_id,
                            Text: "Other",
                            Key_id: Constants.INSPECTION.OTHER_DEFICIENCY_ID, //the id of the "Other" deficiency,
                            Selected: question.Responses.DeficiencySelections[i].Is_active
                        }
                        
                        otherDef.saved = true;

                        question.Deficiencies.push(otherDef);
                        question.otherDefIds.push(question.Responses.DeficiencySelections[i].Key_id);
                        console.log(question.otherDefIds)
                        console.log(question.Responses.DeficiencySelections[i].Other_text)
                        //question.Other_text = question.Responses.DeficiencySelections[i].Other_text;
                        //question.selected = question.Responses.DeficiencySelections[i].Is_active;
                        //if(question.Responses.DeficiencySelections[i].Is_active)return true;
                    }
                }
                if (!question.otherDefIds || !question.otherDefIds.length && !question.hasOther) {
                    var otherDef = {
                        Class: "Deficiency",
                        Is_active: true,
                        Question_id: question.Key_id,
                        Text: "Other",
                        Key_id: Constants.INSPECTION.OTHER_DEFICIENCY_ID, //the id of the "Other" deficiency,
                    }
                    question.hasOther = true;
                    question.Deficiencies.push(otherDef);
                }
            }
            return false;
        }

        factory.getInspection = function( id )
        {
            var deferred = $q.defer();
            //lazy load
            if(this.inspection.length){
                deferred.resolve(this.inspection);
            }else{
                var url = '../../ajaxaction.php?action=resetChecklists&id='+id+'&callback=JSON_CALLBACK';
                $rootScope.loading = convenienceMethods.getDataAsDeferredPromise(url).then(
                    function(promise){
                        deferred.resolve(promise);
                    },
                    function(promise){
                        deferred.reject();
                    }
                );
            }
            deferred.promise.then(
                function(inspection){
                    factory.inspection = inspection;
                }
            )
            return deferred.promise;

        }

        factory.conditionallySaveOtherDeficiency = function( question, room, deficiency )
        {
//            var deficiency = question.activeDeficiencies[question.activeDeficiencies.length -1];
            //set saving flag so view displays spinner
            question.saving = true;

            //find the right DeficiencySelection and update it's other text or Is_active property
            //On the c
            //do we already have  DeficiencySelection for this Other Deficiency?
            //if, it will have been set by the client
            if (deficiency.Deficiency_selection_id) {
            } else {
            }


            if(question.Responses.DeficiencySelections && question.Responses.DeficiencySelections.length){
                var i = question.Responses.DeficiencySelections.length;
                while(i--){
                    if(question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id){
                        var defSelection = question.Responses.DeficiencySelections[i];
                    }
                }
            }else{
                question.Responses.DeficiencySelections = [];
            }


            //grab a collection of room ids
            if( !deficiency.InspectionRooms || !deficiency.InspectionRooms.length) deficiency.InspectionRooms = convenienceMethods.copyObject( factory.inspection.Rooms );
            var i = deficiency.InspectionRooms.length;
            var roomIds = [];
            if(!room){
                //we haven't passed in a room, so we should set relationships for all possible rooms
                while(i--){
                    roomIds.push( deficiency.InspectionRooms[i].Key_id );
                }
            }
            else{
                this.room = room;
                while(i--){
                    if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
                }
            }
            if (defSelection) {
                //find the right DeficiencySelection and update it's other text or Is_active property
                var i = question.Responses.DeficiencySelections.length;
                if(question.selected){
                    defSelection.Is_active = true;
                }else{
                    defSelection.Is_active = false;
                }

            } else {
                //no deficiency selection yet, build one
                var defSelection = {
                    Class: "DeficiencySelection",
                    Response_id: question.Responses.Key_id,
                    Deficiency_id: deficiency.Key_id,
                    Is_active: true,
                    Show_rooms: false
                }

            }
            defSelection.Other_text = question.freeText ? question.freeText : defSelection.Other_text;
            defSelection.RoomIds = roomIds;
            //make save call
            var url = '../../ajaxaction.php?action=saveOtherDeficiencySelection';
             return $rootScope.saving = convenienceMethods.saveDataAndDefer(url, defSelection).then(
                    function(returnedSelection){
                        if(!question.saved){
                            question.Responses.DeficiencySelections.push(returnedSelection);
                            factory.inspection.Deficiency_selections[0].push(returnedSelection.Deficiency_id);
                            var i = returnedSelection.Rooms.length;
                            while(i--){
                                returnedSelection.Rooms[i].checked = true;
                            }
                        }
                        question.edit = false;
                        question.freeText = returnedSelection.Other_text;
                        question.Other_text = returnedSelection.Other_text;
                        question.saving = false;
                        question.saved = true;
                        question.selected = returnedSelection.Is_active;
                    });

        }

        factory.setImage = function( id ) {
                if( id == 1 ){
                        return 'biohazard-largeicon.png';
                }else if( id == 10009  ){
                        return 'chemical-safety-large-icon.png';
                }else if( id == 9999 ){
                        return 'gen-hazard-large-icon.png';
                }else{
                        return 'radiation-large-icon.png';
                }
        }

        factory.selectCategory = function( category ) {
                $rootScope.loading = true;
                $rootScope.image = factory.setImage( category.Key_id );
                $rootScope.inspection = factory.inspection
                $rootScope.category = category;
                factory.selectedCategory = category;
                $rootScope.loading = false;

        }
        //pulls matching items out of an array and puts them in another array
        factory.findChecklistArray = function(checklists, parentId, idx){
            console.log(idx);
            console.log(checklists[idx]);
            var matches = [];
            for(var i = idx; i<checklists.length; i++){
                if(checklists[i].Parent_hazard_id == parentId)
                    matches.push(checklists[i]);
            }
            var i = matches.length;
            while(i--){
                checklists.splice(idx,0,matches[i]);
            }
        }

        factory.getParentIds = function(checklists){
            if(factory.parentIds == null){
                factory.parentIds = [];
                var i = checklists.length;
                while(i--){
                    if(factory.parentIds.indexOf(checklists[i].Parent_hazard_id < 0)){
                        factory.parentIds.push(checklists[i].Parent_hazard_id);
                    }
                }
            }
            return factory.parentIds;
        }

        factory.evaluateCategories = function ()
        {
                var i = this.inspection.Checklists.length;
                while(i--){
                    var list = this.inspection.Checklists[i].Master_hazard;
                    $rootScope[list.substring(0, list.indexOf(' ')).toLowerCase()] = true;
                }
        }

        factory.showRecommendations = function( question ){
            if(!question.showRecommendations)return;
            if(!question.Responses){
                question.showRecommendations = false;
                factory.saveResponse(question)
                    .then(
                        function(){
                            question.showRecommendations = true;
                        }
                    )
            }

        }

        factory.saveResponseSwitch = function( question )
        {
                var defer = $q.defer();

                if(question.Responses && question.Responses.Key_id){
                    defer.resolve(question.Responses.Key_id);
                    return defer.promise;
                }
                //the question doesn't have a reponse, so make a new one
                else{
                    return factory.saveResponse( question )
                        .then(
                            function(returnedResponse){
                                return returnedResponse.Key_id;
                            }
                        )
                }


        }

        factory.saveResponse = function( question )
        {
                question.error='';
                var copy = convenienceMethods.copyObject(question);
                if(!question.Responses){
                    question.Responses = {
                        Class: "Response",
                        Question_id: question.Key_id,
                    }
                }
                var copy = convenienceMethods.copyObject(question);

                var response = copy.Responses;

                question.IsDirty = true;

                var url = '../../ajaxaction.php?action=saveResponse';

                responseDto = convenienceMethods.copyObject(response);
                if(!response.Inspection_id)responseDto.Inspection_id = this.inspection.Key_id;
                if(!response.Question_id)responseDto.Question_id = question.Key_id;
                responseDto.Class = "Response";

                if(!responseDto.Answer)responseDto.Answer = '';

                question.Responses.Answer = null;
                var deferred = $q.defer();
                return $rootScope.saving = convenienceMethods.saveDataAndDefer(url, responseDto).then(
                    function(promise){
                        deferred.resolve(promise);
                        return deferred.promise
                            .then(
                                function(returnedResponse){
                                    question.IsDirty = false;
                                    response = convenienceMethods.copyObject( returnedResponse );
                                    if(!question.Responses.SupplementalObservations)question.Responses.SupplementalObservations = [];
                                    if(!question.Responses.SupplementalRecommendations)question.Responses.SupplementalRecommendations = [];
                                    if(!question.Responses.Observations)question.Responses.Observations = [];
                                    if(!question.Responses.Observations)question.Responses.Observations = [];
                                    question.Responses.Key_id = returnedResponse.Key_id;
                                    question.Responses.Answer = responseDto.Answer;
                                    if (returnedResponse.Answer.toLowerCase() != "no") {
                                        question.Responses.DeficiencySelections = [];
                                        question.Deficiencies.every(function (def) {
                                            def.selected = false;
                                        })

                                    }
                                    return returnedResponse;
                                }
                            )
                    },
                    function(promise){
                        question.IsDirty = false;
                        deferred.reject();
                        question.error = "The response could not be saved.  Please check your internet connection and try again."
                    }
                );

        }

        factory.evaluateDeficiency = function( def, question ){
                if(!question.Responses || !question.Responses.DeficiencySelections || !question.Responses.DeficiencySelections.length)return false;
                var i = question.Responses.DeficiencySelections.length;
                var id = def.Key_id;
                while(i--){
                    if( id == question.Responses.DeficiencySelections[i].Deficiency_id ){
                        def.selected = true;
                        return true;
                    }
                }
                return false;

        }

        factory.evaluateDeficienyShowRooms = function( id ){
                var i = this.inspection.Deficiency_selections[2].length;
                while(i--){
                    if( id == this.inspection.Deficiency_selections[2][i] )return true;
                }
                return false;

        }

        factory.saveDeficiencySelection = function( deficiency, question, checklist, room )
        {
                deficiency.IsDirty = true;
                question.error =  '';
                if( !deficiency.InspectionRooms || !deficiency.InspectionRooms.length) deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
                console.log(checklist);
                //grab a collection of room ids
                var i = deficiency.InspectionRooms.length;
                var roomIds = [];
                if(!room){
                    //we haven't passed in a room, so we should set relationships for all possible rooms
                    while(i--){
                        roomIds.push( deficiency.InspectionRooms[i].Key_id );
                    }
                }
                else{
                    while(i--){
                        if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
                    }
                    room.checked = !room.checked;
                    this.room = room;

                }
                console.log(roomIds);

                var showRooms = false;
                if(roomIds.length < deficiency.InspectionRooms.length){
                    showRooms = true;
                }

                var defDto = {
                    Class: "DeficiencySelection",
                    RoomIds: roomIds,
                    Deficiency_id:  deficiency.Key_id,
                    Response_id: question.Responses.Key_id,
                    Inspection_id: this.inspection.Key_id,
                    Show_rooms:  showRooms
                }

                //make sure we are persisting the state of Other deficiency selections
                if(deficiency.Text == "Other"){
                    //find the right DeficiencySelection and update it's other text or Is_active property
                    var i = question.Responses.DeficiencySelections.length;
                    while(i--){
                        if(question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id){
                           defDto = question.Responses.DeficiencySelections[i];
                           defDto.Show_rooms = deficiency.Show_rooms;
                           defDto.RoomIds    = roomIds;
                        }
                    }
                }


                if( deficiency.selected || deficiency.Text == "Other"  /*we never delete "Other" deficiency selections, only deactivate them*/){
                        if(question.Responses && question.Responses.DeficiencySelections){
                            var j = question.Responses.DeficiencySelections.length;
                            while(j--){
                                var ds = question.Responses.DeficiencySelections[j];
                                if(deficiency.Key_id == ds.Deficiency_id)defDto.Key_id = ds.Key_id;
                            }
                        }
                        console.log(defDto);

                        var url = '../../ajaxaction.php?action=saveDeficiencySelection';
                        $rootScope.saving = convenienceMethods.saveDataAndDefer(url, defDto)
                            .then(
                                function (returnedDeficiency) {
                                    deficiency.IsDirty = false;
                                    deficiency.selected = true;
                                    if( factory.inspection.Deficiency_selections[0].indexOf( deficiency.Key_id ) < 0){
                                        factory.inspection.Deficiency_selections[0].push( deficiency.Key_id );
                                    }
                                    if(!question.Responses.DeficiencySelections)question.Responses.DeficiencySelections = [];

                                    if(factory.room){
                                        room.checked = !room.checked;
                                        factory.room = null;
                                        //f no rooms are left checked for this deficiency, we remove it's key id from the Inspection's array of deficiency_selection ids
                                        if(roomIds.length == 0){
                                            factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections.indexOf( deficiency.Key_id, 1 ) )
                                        }
                                        for (var i = 0; i < deficiency.InspectionRooms.length; i++) {
                                            if (deficiency.InspectionRooms[i].Key_id == room.Key_id) {
                                                deficiency.InspectionRooms[i] = room;
                                            }
                                        }

                                    } else {
                                        for (var i = 0; i < returnedDeficiency.Rooms.length; i++) {
                                            returnedDeficiency.Rooms[i].checked = true;
                                        }
                                        deficiency.InspectionRooms = returnedDeficiency.Rooms;
                                        question.Responses.DeficiencySelections.push(returnedDeficiency);
                                    }

                                },
                                function(promise){
                                    question.IsDirty = false;
                                    deferred.reject();
                                    deficiency.selected = false;
                                    question.error = "The response could not be saved.  Please check your internet connection and try again."
                                }
                            );
                }else{
                    var j = question.Responses.DeficiencySelections.length;
                    //get the key_id for our DeficiencySelection
                    while(j--){
                    if( question.Responses.DeficiencySelections[j].Deficiency_id == defDto.Deficiency_id ){
                          defDto.Key_id = question.Responses.DeficiencySelections[j].Key_id;
                          var defSelectIdx = j;
                        }
                    }
                    var url = '../../ajaxaction.php?action=removeDeficiencySelection';
                      $rootScope.saving = convenienceMethods.saveDataAndDefer( url, defDto )
                          .then(
                              function(returnedBool){
                                  deficiency.IsDirty = false;
                                deficiency.selected = false;
                                factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections[0].indexOf( deficiency.Key_id ), 1 );
                                 question.Responses.DeficiencySelections.splice( defSelectIdx, 1 );
                              },
                              function(error){
                                deficiency.IsDirty = false;
                                deficiency.selected = true;
                                question.error = "The response could not be saved.  Please check your internet connection and try again."
                              }
                          )
                }
        }

        factory.handleCorrectedDurringInspection = function( deficiency, question )
        {
            question.error='';
            deficiency.IsDirty = true;
            var def_id = deficiency.Key_id;
            //deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection
            if( deficiency.correctedDuringInspection ){
              //we set corrected during inspection
              var url = '../../ajaxaction.php?action=addCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
            }else{
              //we unset corrected during inspection
              var url = '../../ajaxaction.php?action=removeCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
            }

            if (deficiency.Class == "SupplementalDeficiency") {
                url = url + "&supplemental=true";
            }

            convenienceMethods.getDataAsPromise( url )
                  .then(
                      function(){
                          deficiency.IsDirty = false;
                      },
                      function(){
                          deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection;
                          question.error = 'The deficiency could not be saved.  Please check your internet connection and try again.';
                          deficiency.IsDirty = false;
                      }
                  );
        }

        factory.changeChecklist = function( checklist )
        {
            checklist.currentlyOpen = !checklist.currentlyOpen;
            var insp = $location.search().inspection;
            //$location.hash(checklist.Key_id);
            $location.search('inspection',insp);
            $anchorScroll();
        }

        factory.evaluateDeficiencyRoomChecked = function( room, question, deficiency )
        {
            if (deficiency.Class == "Deficiency") {
                if (!question.Responses.DeficiencySelections) return false;
                var i = question.Responses.DeficiencySelections.length;
                while (i--) {
                    if (question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id) {
                        var j = question.Responses.DeficiencySelections[i].Rooms.length;
                        
                        while (j--) {
                            if (question.Responses.DeficiencySelections[i].Rooms[j].Key_id == room.Key_id) {
                                if (room.checked != false) {
                                    deficiency.checked = true;
                                    //room.checked = true;
                                    return true;
                                }
                            }
                        }
                    }
                }
            } else {
                if (!question.Responses.SupplementalDeficiencies) return false;
                var i = question.Responses.SupplementalDeficiencies.length;
                while (i--) {
                    if (question.Responses.SupplementalDeficiencies[i].Key_id == deficiency.Key_id) {
                        var j = question.Responses.SupplementalDeficiencies[i].Rooms.length;
                        while (j--) {
                            if (question.Responses.SupplementalDeficiencies[i].Rooms[j].Key_id == room.Key_id) {
                                if (room.checked != false) {
                                    deficiency.checked = deficiency.selected = deficiency.Is_active = true;
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            return false;
        }

        factory.copyForEdit = function( question, objectToCopy )
        {
            $rootScope[objectToCopy.Class+'Copy'] = convenienceMethods.copyObject( objectToCopy );
            $rootScope[objectToCopy.Class+'Copy'].edit = true;
            objectToCopy.edit = true;
            question.edit = true;
/*
            if(objectToCopy.Class.indexOf("Sup") < 0){
                question[objectToCopy.Class+'s'].push($rootScope[objectToCopy.Class+'Copy']);
            }
            else{
                question.Responses[objectToCopy.Class+'s'].push($rootScope[objectToCopy.Class+'Copy']);
            }
*/
        }

        factory.objectNullifactor = function( objectToNullify, question )
        {
            objectToNullify.edit = false;
            question.edit = false;
            $rootScope[objectToNullify.Class] = {};
        }

        factory.createRecommendation = function( question, id )
        {
            $rootScope.RecommendationCopy = {
                Class: "Recommendation",
                Question_id: question.Key_id,
                Text: question.newRecommendationText,
                edit: true,
                new: true,
                push: true,
                Is_active: true,
            }

            this.saveRecommendation( question, $rootScope.RecommendationCopy );

        }

        factory.createObservation = function( question )
        {
            $rootScope.ObservationCopy = {
                Class: "Observation",
                Question_id: question.Key_id,
                Text: question.newObservationText,
                edit: true,
                new: true,
                push: true,
                Is_active: true
            }

            this.saveObservation( question, $rootScope.ObservationCopy )
        }

        factory.createDeficiency = function (question, checklist) {
            $rootScope.DeficiencyCopy = {
                Class: "Deficiency",
                Question_id: question.Key_id,
                Text: question.newDeficiencyText,
                selected: true,
                edit: true,
                new: true,
                push: true,
                Is_active: true
            }

            this.saveDeficiency(question, $rootScope.DeficiencyCopy, checklist)
        }

        factory.saveObservation = function( question, observation )
        {
                if($rootScope.ObservationCopy.push)question.savingNew = true;
                question.error = '';
                observation.IsDirty = true;
                var url = '../../ajaxaction.php?action=saveObservation';
                      $rootScope.saving = convenienceMethods.saveDataAndDefer( url, $rootScope.ObservationCopy )
                          .then(
                              function(returnedObservation){
                                  factory.objectNullifactor($rootScope.ObservationCopy, question)
                                  if(!$rootScope.ObservationCopy.push){
                                      observation.edit = false;
                                      angular.extend(observation, returnedObservation)
                                  }
                                  else{
                                      returnedObservation.new = true;
                                      question.Observations.push(returnedObservation);
                                      question.newObservationText = '';
                                  }
                                  returnedObservation.IsDirty = false;
                                  returnedObservation.edit = false;
                                  returnedObservation.checked = true;
                                  observation.IsDirty = false;
                                  if(!observation.Key_id)factory.saveObservationRelation( question, returnedObservation );
                                  question.edit = false;
                                  question.savingNew = false;
                                  question.addNote = false;
                              },
                              function(error){
                                  returnedObservation.IsDirty = false;
                                question.error = "The note could not be saved.  Please check your internet connection and try again."
                                question.savingNew = false;
                              }
                          )

        }

        factory.saveRecommendation = function( question, recommendation )
        {
            if($rootScope.RecommendationCopy.push)question.savingNew = true;
            question.error = '';
            recommendation.IsDirty = true;
            var url = '../../ajaxaction.php?action=saveRecommendation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, $rootScope.RecommendationCopy )
                      .then(
                          function(returnedRecommendation){
                              factory.objectNullifactor($rootScope.RecommendationCopy, question)
                              if(!$rootScope.RecommendationCopy.push){
                                  recommendation.edit = false;
                                  angular.extend(recommendation, returnedRecommendation);
                              }
                              else{
                                  returnedRecommendation.new = true;
                                  question.Recommendations.push(returnedRecommendation);
                                  question.newRecommendationText = '';
                              }
                              returnedRecommendation.IsDirty = false;
                              returnedRecommendation.edit = false;
                              returnedRecommendation.checked = true;
                              recommendation.IsDirty = false;
                              if(!recommendation.Key_id)factory.saveRecommendationRelation( question, returnedRecommendation );
                              question.edit = false;
                              question.savingNew = false;
                              question.addRec = false;
                          },
                          function(error){
                              returnedRecommendation.IsDirty = false;
                            question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                            question.savingNew = false;
                          }
                      )
        }

        factory.saveDeficiency = function (question, deficiency, checklist) {
            if ($rootScope.DeficiencyCopy.push) question.savingNew = true;
            question.error = '';
            deficiency.IsDirty = true;
            var url = '../../ajaxaction.php?action=saveDeficiency';
            $rootScope.saving = convenienceMethods.saveDataAndDefer(url, $rootScope.DeficiencyCopy)
                .then(
                    function (returnedDef) {
                        factory.objectNullifactor($rootScope.DeficiencyCopy, question)
                        if (!$rootScope.DeficiencyCopy.push) {
                            deficiency.edit = false;
                            angular.extend(deficiency, returnedDef);
                        }
                        else {
                            returnedDef.new = true;
                            question.Deficiencies.push(returnedDef);
                            question.newDeficiencyText = '';
                        }
                        returnedDef.IsDirty = false;
                        returnedDef.edit = false;
                        returnedDef.selected = true;
                        deficiency.IsDirty = false;
                        if (!deficiency.Key_id) factory.saveDeficiencySelection(returnedDef, question, checklist);
                        question.edit = false;
                        question.savingNew = false;
                        question.addRec = false;
                    },
                    function (error) {
                        returnedDef.IsDirty = false;
                        question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                        question.savingNew = false;
                    }
                )
        }

        factory.saveSupplementalObservation = function( question, isNew, so )
        {
            if(!question.Responses.SupplementalObservations)question.Responses.SupplementalObservations=[];
            var soDto = {
                Class: "SupplementalObservation",
                Text: question.newObservationText,
                response_id: question.Responses.Key_id
            }
            if(isNew){
                soDto.Is_active = true;
                question.savingNew = true;
            }
            else{
                soDto.Is_active = so.checked;
                so.IsDirty = false;
                soDto.Text = $rootScope.SupplementalObservationCopy.Text;
                so.IsDirty = true;
                soDto.Key_id = so.Key_id
            }
            question.error = '';
            var url = '../../ajaxaction.php?action=saveSupplementalObservation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, soDto )
                      .then(
                          function( returnedSupplementalObservation ){
                              if( so ){
                                  soDto.checked = returnedSupplementalObservation.Is_active
                                  angular.extend(so, returnedSupplementalObservation)
                                  so.IsDirty = false;
                                  so.edit=false;
                              }
                              else{
                                  returnedSupplementalObservation.checked = true;
                                question.Responses.SupplementalObservations.push(returnedSupplementalObservation);
                                question.savingNew = false;
                              }
                              question.addNote = false;
                              if($rootScope.SupplementalObservationCopy)factory.objectNullifactor($rootScope.SupplementalObservationCopy, question)
                          },
                          function(error){
                              question.savingNew = false;
                              if(so)so.IsDirty = false;
                            question.error = "The note could not be saved.  Please check your internet connection and try again."
                          }
                      )

        }

        factory.saveSupplementalRecommendation = function( question, isNew, sr )
        {
            if(!question.Responses.SupplementalRecommendations)question.Responses.SupplementalRecommendations=[];
            var srDto = {
                Class: "SupplementalRecommendation",
                Text: question.newRecommendationText,
                response_id: question.Responses.Key_id
            }
            if(isNew){
                srDto.Is_active = true;
                question.savingNew = true;
            }
            else{
                srDto.Is_active = sr.checked
                srDto.Text = $rootScope.SupplementalRecommendationCopy.Text;
                sr.IsDirty = true;
                srDto.Key_id = sr.Key_id
            }
            question.error = '';
            var url = '../../ajaxaction.php?action=saveSupplementalRecommendation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, srDto )
                      .then(
                          function( returnedSupplementalRecommendation ){
                            question.addRec = false;
                              if( sr ){
                                  srDto.checked = returnedSupplementalRecommendation.Is_active
                                  angular.extend(sr, returnedSupplementalRecommendation);
                                  sr.edit = false;
                                  sr.IsDirty = false;
                              }
                              else{
                                  returnedSupplementalRecommendation.checked = true;
                                question.Responses.SupplementalRecommendations.push(returnedSupplementalRecommendation);
                                question.savingNew = false;
                              }
                              question.newRecommendationText = '';
                              if($rootScope.SupplementalRecommendationCopy)factory.objectNullifactor($rootScope.SupplementalRecommendationCopy, question)
                          },
                          function(error){
                              question.savingNew = false;
                              if(sr)sr.IsDirty = false;
                            question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                          }
                      )
        }

        factory.saveSupplementalDeficiency = function (question, isNew, sd, checklist, room, checked) {
            console.log(room);
            if (!question.Responses.SupplementalDeficiencies) question.Responses.SupplementalDeficiencies = [];

            var sdDto = {
                Class: "SupplementalDeficiency",
                Text: question.newDeficiencyText,
                response_id: question.Responses.Key_id,
            }
            if (isNew) {
                sdDto.Is_active = true;
                question.savingNew = true;
            }
            else {
                sdDto.Is_active = sd.checked
                sdDto.Text = $rootScope.SupplementalDeficiencyCopy ? $rootScope.SupplementalDeficiencyCopy.Text : sd.Text;
                sd.IsDirty = true;
                sdDto.Key_id = sd.Key_id
            }


            if (!sdDto.InspectionRooms || !sdDto.InspectionRooms.length) sdDto.InspectionRooms = convenienceMethods.copyObject(checklist.InspectionRooms);
            //grab a collection of room ids
            var i = sdDto.InspectionRooms.length;
            var roomIds = [];
            if (!room) {
                //we haven't passed in a room, so we should set relationships for all possible rooms
                if (checked || !sd) {
                    while (i--) {
                        roomIds.push(sdDto.InspectionRooms[i].Key_id);
                    }
                    sdDto.RoomIds = roomIds;
                } else {
                    sdDto.RoomIds = [];
                }
            }
            else {
                while (i--) {
                    if (sd.InspectionRooms[i].checked) roomIds.push(sdDto.InspectionRooms[i].Key_id);
                }
                sdDto.RoomIds = roomIds;
                room.checked = !room.checked;
                this.room = room;

            }

            var showRooms = false;
            if (roomIds.length < sdDto.InspectionRooms.length) {
                showRooms = true;
            }
            sdDto.Show_rooms = showRooms;

            console.log(sdDto);

            question.error = '';
            var url = '../../ajaxaction.php?action=saveSupplementalDeficiency';
            $rootScope.saving = convenienceMethods.saveDataAndDefer(url, sdDto)
                .then(
                    function (returnedSupplementalDeficiency) {
                        question.addDef = false;
                        if (sd) {
                            sdDto.checked = returnedSupplementalDeficiency.Is_active
                            angular.extend(sd, returnedSupplementalDeficiency);
                            sd.checked = sd.selected = sd.Is_active;
                            sd.edit = false;
                            sd.IsDirty = false;
                        }
                        else {
                            returnedSupplementalDeficiency.checked = true;
                            returnedSupplementalDeficiency.checked = returnedSupplementalDeficiency.selected = returnedSupplementalDeficiency.Is_active;
                            console.log(returnedSupplementalDeficiency);
                            question.Responses.SupplementalDeficiencies.push(returnedSupplementalDeficiency);
                            question.savingNew = false;
                        }
                        question.newDeficiencyText = '';
                        if ($rootScope.SupplementalDeficiencyCopy) factory.objectNullifactor($rootScope.SupplementalDeficiencyCopy, question)
                    },
                    function (error) {
                        question.savingNew = false;
                        if (sr) sr.IsDirty = false;
                        question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                    }
                )
        }

        factory.saveRecommendationRelation = function( question, recommendation )
        {
            factory.saveResponseSwitch( question )
                .then(function(responseId){
                    recommendation.IsDirty = true;
                    recommendation.checked = !recommendation.checked;
                    question.error = ''
                    var relationshipDTO = {
                        Class:          "RelationshipDto",
                        Master_id :     responseId,
                        Relation_id:    recommendation.Key_id,
                        add:            !recommendation.checked
                    }
                    var url = '../../ajaxaction.php?action=saveRecommendationRelation';
                    $rootScope.saving = convenienceMethods.saveDataAndDefer( url, relationshipDTO )
                          .then(
                              function(){
                                  recommendation.checked = !recommendation.checked;
                                  //if the recommendation was checked, it should be added to the response so we can track the number of recommendations selected
                                  if(recommendation.checked){
                                      question.Responses.Recommendations.push(recommendation);
                                  }
                                  //if the recommendation was unchecked, we removed it from the response
                                  else{
                                      var i = question.Responses.Recommendations.length;
                                      while(i--){
                                          if(question.Responses.Recommendations[i].Key_id == recommendation.Key_id){
                                              question.Responses.Recommendations.splice(i,1);
                                          }
                                      }
                                  }
                                  recommendation.IsDirty = false;
                              },
                              function(error){
                                  recommendation.IsDirty = false;
                                question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                              }
                          )
                });

        }

        factory.saveObservationRelation = function(question, observation)
        {
            observation.IsDirty = true;
            observation.checked = !observation.checked;
            question.error = ''
            var relationshipDTO = {
                Class:          "RelationshipDto",
                Master_id :     question.Responses.Key_id,
                Relation_id:    observation.Key_id,
                add:            !observation.checked
            }
            var url = '../../ajaxaction.php?action=saveObservationRelation';
            $rootScope.saving = convenienceMethods.saveDataAndDefer( url, relationshipDTO )
                      .then(
                          function(){
                              observation.checked = !observation.checked;
                              if(observation.checked){
                                  question.Responses.Observations.push(observation);
                              }
                              //if the recommendation was unchecked, we removed it from the response
                              else{
                                  var i = question.Responses.Observations.length;
                                  while(i--){
                                      if(question.Responses.Observations[i].Key_id == observation.Key_id){
                                          question.Responses.Observations.splice(i,1);
                                      }
                                  }
                              }
                              observation.IsDirty = false;
                          },
                          function(error){
                              observation.IsDirty = false;
                            question.error = "The observation could not be saved.  Please check your internet connection and try again."
                          }
                      )
        }

        factory.getRecommendationChecked = function( question, recommendation )
        {
            if(!question.Responses)return false;
            if(recommendation.checked)return true;
            if(!question.Responses.Recommendations)question.Responses.Recommendations=[];
            var i = question.Responses.Recommendations.length;
            if(i==0)return false;
            var ids = [];
            while(i--)
            {
                ids.push(question.Responses.Recommendations[i].Key_id);
            }
            if( ids.indexOf(recommendation.Key_id ) >-1 )return true;
            return false;

        }

        factory.getObservationChecked = function( question, observation )
        {
            if(!question.Responses)return false;
            if(observation.checked)return true;
            if(!question.Responses.Observations)question.Responses.Observations=[];
            var i = question.Responses.Observations.length;
            if(i==0)return false;
            var ids = [];
            while(i--)
            {
                ids.push(question.Responses.Observations[i].Key_id);
            }
            if( ids.indexOf(observation.Key_id ) >-1 )return true;
            return false;

        }

        factory.supplementalRecommendationChanged = function( question, recommendation )
        {
            $rootScope.SupplementalRecommendationCopy = convenienceMethods.copyObject(recommendation)
            this.saveSupplementalRecommendation( question, false, recommendation, true );
        }

        factory.supplementalObservationChanged = function( question, observation )
        {
            $rootScope.SupplementalObservationCopy = convenienceMethods.copyObject(observation)
            this.saveSupplementalObservation( question, false, observation, true );
        }

        factory.supplementalDeficiencyChanged = function (question, def, checklist) {
            $rootScope.SupplementalDeficiencyCopy = convenienceMethods.copyObject(def)
            this.saveSupplementalDeficiency(question, false, def, checklist, null, def.checked);
        }


        factory.savePi = function(pi)
        {
        var url = "../../ajaxaction.php?action=savePI";
        var deferred = $q.defer();
          $rootScope.saving = convenienceMethods.saveDataAndDefer(url, pi)
            .then(
              function(promise){
                deferred.resolve(promise);
              },
              function(promise){
                deferred.reject();
              }
            );
            return deferred.promise
        }

        return factory;
});

function checklistController($scope,  $location, $anchorScroll, convenienceMethods, $window, checklistFactory, $modal) {
    var cf = $scope.cf = checklistFactory;
    $scope.constants = Constants;

    if($location.search().inspection){
      $scope.inspId = $location.search().inspection;
      checklistFactory.getInspection( $scope.inspId )
          .then(
              function( inspection ){
                  checklistFactory.evaluateCategories();
              },
              function( error ){
                  $scope.error = "The system couldn't find the selected inspeciton.  Please check your internet connection and try again."
              }
          )
      }else{
          $scope.error = "No inspection specified."
      }

    $scope.showRooms = function( event, deficiency, element, checklist, question ){
        if(!deficiency.InspectionRooms){
            if(!checklist.InspectionRooms || !checklist.InspectionRooms.length)checklist.InspectionRooms = convenienceMethods.copyObject( cf.inspection.Rooms );
            //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
            deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
            console.log(checklist.InspectionRooms);
        }
       // checklistFactory.evaluateDeficiecnyRooms( question, checklist );

        event.stopPropagation();
        calculateClickPosition(event,deficiency,element);
        deficiency.showRoomsModal = !deficiency.showRoomsModal;
    }

    $scope.getNeedsRooms = function(deficiency, checklist, question){
        if(!deficiency.InspectionRooms){
            if(!checklist.InspectionRooms || !checklist.InspectionRooms.length)checklist.InspectionRooms = convenienceMethods.copyObject( cf.inspection.Rooms );
            //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
            deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
        }else{
            for (var i = 0; i < deficiency.InspectionRooms.length; i++ ){
                if(!cf.evaluateDeficiencyRoomChecked( deficiency.InspectionRooms[i], question, deficiency )) return true;
            }
                
        }
        return false;
        
        console.log(deficiency);
    }
    
    //get the position of a mouseclick, set a properity on the clicked hazard to position an absolutely positioned div
    function calculateClickPosition(event, deficiency, element){
        var x = event.clientX;
        var y = event.clientY+$window.scrollY;

        deficiency.calculatedOffset = {};
        deficiency.calculatedOffset.x = x-110;
        deficiency.calculatedOffset.y = y-185;
    }


  $scope.openNotes = function(){
     var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/inspection-notes-modal.html',
        controller: commentsController
      });
  }
}

function commentsController ($scope, checklistFactory, $modalInstance, convenienceMethods, $q){
  $scope.cf=checklistFactory;
  var pi = checklistFactory.inspection.PrincipalInvestigator;
  $scope.pi = pi;
  $scope.piCopy = {
    Key_id: $scope.pi.Key_id,
    Is_active: $scope.pi.Is_active,
    User_id: $scope.pi.User_id,
    //Inspection_notes: $scope.pi.Inspection_notes,
    Class:"PrincipalInvestigator"
  };

  $scope.tinymceOptions = {
      plugins: 'link lists',
      toolbar: 'bold | italic | underline | link | lists | bullist | numlist',
      menubar: false,
      elementpath: false,
      content_style: "p,ul li {font-size:14px}"
  };

  $scope.close = function () {
    $modalInstance.dismiss();
  };

  $scope.edit = function(state){
      $scope.pi.editNote = state;
      if (state != false) {
          $scope.piCopy.Inspection_notes = $scope.pi.Inspection_notes
      }
  }

  $scope.saveNote = function(){
    $scope.savingNote = true;
    $scope.error = null;
    $scope.close();

    checklistFactory.savePi($scope.piCopy)
      .then(
        function(returnedPi){
          angular.extend(checklistFactory.inspection.PrincipalInvestigator, returnedPi);
          $scope.savingNote = false;
          $scope.pi.editNote = false;
          $scope.pi.Inspection_notes = returnedPi.Inspection_notes;
        },
        function(){
          $scope.savingNote = false;
          $scope.error = "The Inspection Comments could not be saved.  Please check your internet connection and try again."
        }
      )
  }

}

var locationHub = angular.module('locationHub', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once'])

.config(function($routeProvider){
    $routeProvider
        .when('/rooms',
            {
                templateUrl: 'locationHubPartials/rooms.html',
                controller: roomsCtrl
            }
        )
        .when('/buildings',
            {
                templateUrl: 'locationHubPartials/buildings.html',
                controller: buildingsCtrl
            }
        )
        .when('/campuses',
            {
                templateUrl: 'locationHubPartials/campuses.html',
                controller: campusesCtrl
            }
        )
        .otherwise(
            {
                redirectTo: '/rooms'
            }
        );
})
.filter('genericFilter', function () {
    return function (items,search) {
        if(search){
            var i = 0;
            if(items)i = items.length;
            var filtered = [];

            var isMatched = function(input, item){
                if(item.Name == input)return true;
                return false;
            }

            while(i--){

                //we filter for every set search filter, looping through the collection only once

                var item=items[i];
                item.matched = true;

                if(search.building){
                    if( item.Building && item.Building.Name && item.Building.Name.toLowerCase().indexOf(search.building.toLowerCase() ) < 0 ){
                        item.matched = false;
                    }

                    if(item.Class == "Building" && item.Name.toLowerCase().indexOf(search.building.toLowerCase()) < 0 )  item.matched = false;

                }

                if (search.hazards) {
                    console.log(item.Name + ' | ' + item[search.hazards] + ' | ' + search.hazards)
                    if ( item.Class == "Room" && !item[search.hazards] || item[search.hazards] == false || item[search.hazards] == "0" ) item.matched = false;
                }

                if(search.room){
                    if( item.Class == 'Room' && item.Name && item.Name.toLowerCase().indexOf(search.room.toLowerCase()) < 0 )  item.matched = false;
                }

                if(search.purpose){
                    if( item.Class == 'Room' && !item.Purpose || item.Purpose.toLowerCase().indexOf(search.purpose.toLowerCase()) < 0 )  item.matched = false;
                }

                if (search.alias) {
                    if (item.Class == 'Building' && !item.Alias || item.Alias.toLowerCase().indexOf(search.alias.toLowerCase()) < 0) item.matched = false;
                }

                if( search.campus ) {
                    if( item.Class != "Building" && (!item.Building || !item.Building.Campus) ){
                        item.matched = false;
                        console.log('set false because no building or campus')
                    }
                    if (item.Building && item.Building.Campus && item.Building.Campus.Name && item.Building.Campus.Name.toLowerCase().indexOf(search.campus.toLowerCase()) < 0) {
                        item.matched = false;
                        console.log('set false because of lack of match');
                    }
                    if(item.Class == "Building" && item.Campus && item.Campus.Name && item.Campus.Name.toLowerCase().indexOf( search.campus.toLowerCase() ) < 0 ){
                        item.matched = false;
                        console.log('set false because of lack of match');
                    }
                }

                if( ( search.pi || search.department ) && item.PrincipalInvestigators){
                    if(!item.PrincipalInvestigators.length){
                        console.log('no pis in room '+item.Name);
                        item.PrincipalInvestigators = [{Class:"PrincipalInvestigator",User:{Name: 'Unassigned', Class:"User"}, Departments:[{Name: 'Unassigned'}] }];
                    }

                    var j = item.PrincipalInvestigators.length
                    item.matched = false;
                    var deptMatch = false;
                    while(j--){

                        var pi = item.PrincipalInvestigators[j];
                        if( search.pi && pi.User.Name && pi.User.Name.toLowerCase().indexOf(search.pi.toLowerCase()) > -1 ){
                            item.matched = true;
                            var piMatch = true;
                        }

                        if(search.department){
                            deptMatch = false;
                            if(!pi.Departments || !pi.Departments.length){
                                pi.Departments = [{Name: 'Unassigned'}];
                            }else{
                                var k = pi.Departments.length;
                                while(k--){
                                    if( pi.Departments && pi.Departments[k].Name && pi.Departments[k].Name.toLowerCase().indexOf(search.department.toLowerCase()) > -1 ) deptMatch = true;
                                }
                            }
                            if( ( !search.pi && deptMatch ) || ( piMatch && deptMatch ) )item.matched = true;
                        }
                    }

                }

                if(item.matched == true)filtered.push(item);

            }
            filtered.reverse();
            return filtered;
        }else{
            return items;
        }
    };
})
.factory('locationHubFactory', function(convenienceMethods,$q,$rootScope,$http){
    var factory = {};
    factory.rooms = [];
    factory.buildings = [];
    factory.campuss = [];
    factory.modalData;
    factory.isEditing = false;

    factory.editing = function(bool) {
        factory.isEditing = bool;
        console.log("dig", factory.isEditing);
    }

    factory.getRooms = function(){
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if(this.rooms.length){
            deferred.resolve(this.rooms);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllRooms&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function(rooms){
                factory.rooms = rooms;
            }
        )

        return deferred.promise;

    }


    factory.getBuildings = function()
    {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
                //lazy load
        if(this.buildings.length){
            deferred.resolve(this.buildings);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllBuildings&skipRooms=true&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }
        deferred.promise.then(
            function(buildings){
                factory.buildings = buildings;
            }
        )
        return deferred.promise;
    }

    factory.setBuildings = function(buildings)
    {
        this.buildings = buildings;
    }
    factory.getCampuses = function()
    {
        console.log(this);
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if(this.campuss.length){
            deferred.resolve(this.campuss);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllCampuses&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                    deferred.resolve(promise);
                },
                function(promise){
                    deferred.reject();
                }
            );
        }
        deferred.promise.then(
            function(campuses){
                factory.campuss = campuses;
            }
        )
        return deferred.promise;
    }
    factory.setCampuses = function( campuses )
    {
        this.campuss = campuses
    }

    factory.getBuildingByRoom = function( room )
    {
        if(room.Building)return room.Building;
        if(!room.trusted){
            var i = this.buildings.length
            while(i--){
                room.trusted = true;
                if(this.buildings[i].Key_id == room.Building_id){
                    room.Building = this.buildings[i];
                }
            }
        }

        return room.Building;
    }

    factory.getAllPis = function(){
        //lazy load

        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        if(factory.pis){
            deferred.resolve(factory.pis);
        }else{
            var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
                  convenienceMethods.getDataAsDeferredPromise(url).then(
                  function(promise){
                    deferred.resolve(promise);
                    factory.pis = promise;
                  },
                  function(promise){
                    deferred.reject();
                  }
            );
        }
        return deferred.promise;
    }

    factory.saveRoom = function(roomDto){
        $rootScope.validationError='';
        if(!roomDto.Key_id){
            var defer = $q.defer();
            if(this.roomAlreadyExists(roomDto)){
                $rootScope.validationError="Room "+roomDto.Name+" already exists in "+ this.getBuildingByRoom(roomDto).Name+'.';
                roomDto.IsDirty=false;
                return
            }
        }
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveRoom";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }

    factory.roomAlreadyExists = function(room)
    {
        var i=this.rooms.length;
        while(i--){
            if(this.rooms[i].Name.toLowerCase()==room.Name.toLowerCase() && this.rooms[i].Building_id == room.Building_id)return true;
        }
        return false;
    }

    factory.saveBuilding = function(buildingDto){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveBuilding";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, buildingDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }


    factory.saveCampus = function(campusDto){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveCampus";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, campusDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }

    factory.setModalData = function( data )
    {
        this.modalData = data;
    }

    factory.getModalData = function()
    {
        return this.modalData;
    }

    factory.handleObjectActive = function(object)
    {
        $rootScope.error = null;
        object.IsDirty = true;
        var copy = convenienceMethods.copyObject( object );
        copy.Is_active = !copy.Is_active;

        this['save'+object.Class](copy)
            .then(
                function(returned){
                    //TODO:  change factory's properties to uppercase, remove stupid toLowercase() calls
                    var i = factory[object.Class.toLowerCase()+'s'].length

                    while(i--){
                        copy.IsDirty = false;
                        if( factory[object.Class.toLowerCase()+'s'][i].Key_id ==  copy.Key_id) factory[object.Class.toLowerCase()+'s'][i] = copy;
                    }

                },
                function(){
                    $rootScope.error = 'The ' + object.Class.toLowerCase() + ' could not be saved.  Please check your internet connection and try again.';
                    object.IsDirty = false;
                }
            )

    }

    factory.setEditState = function(obj, scope)
    {
            var i = scope.length
            while(i--){
                scope[i].edit = false;
            }

            if(!obj.edit)obj.edit = false;
            obj.edit = !obj.edit;
            if(obj.Class == 'Building'  && obj.Campus == false)obj.Campus = '';

            $rootScope.copy = convenienceMethods.copyObject(obj);
    }

    factory.cancelEdit = function(obj, scope)
    {
            $rootScope.copy = null;
            obj.edit = false;

            //if this is a new object, we should pull it out of the collection
            if(obj.newObj && scope){

                var i = scope.length
                while(i--){
                    if(scope[i].newObj)scope.splice(i,1);
                }

            }
    }

    factory.getCSV = function(){
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=getLocationCSV";
        $http.get(url, function(status, response){
            // success
        }, function(status, response){
            $rootScope.error = 'The list of locations could not be retrieved.  Please check your internet connection and try again.';
        });
    }


    return factory;
});


routeCtrl = function($scope, $location,$rootScope){
    $scope.location = $location.path();
    $scope.setRoute = function(route){
        $location.path(route);
        $scope.location = route;
    }
    $rootScope.iterator=0;
}

roomsCtrl = function($scope, $rootScope, $location, convenienceMethods, $modal, locationHubFactory, roleBasedFactory){
    $rootScope.modal = false;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;
    $rootScope.rbf = roleBasedFactory;
    $scope.constants = Constants;

    locationHubFactory.getBuildings()
        .then(
            function(buildings){
                locationHubFactory.getRooms()
                .then(
                    function(rooms){
                        $scope.rooms = rooms;
                        $scope.loading = false;
                    }
                )
            }
        )

    $scope.openRoomModal = function(room){

        if(!room)room = {Is_active: true, Class:'Room', Name:'', Building:{Name:''}, PrincipalInvestigators:[]};
        locationHubFactory.setModalData(null);
        locationHubFactory.setModalData(room);

        var modalInstance = $modal.open({
          templateUrl: 'locationHubPartials/roomsModal.html',
          controller: modalCtrl
        });


        modalInstance.result.then(function () {
           locationHubFactory.getRooms()
                .then(
                    function(rooms){
                        $scope.rooms = rooms;
                        $scope.loading = false;
                    }
                )
        });

    }


}

var buildingsCtrl = function ($scope, $rootScope, $modal, locationHubFactory, roleBasedFactory) {
    $rootScope.rbf = roleBasedFactory;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;

    locationHubFactory.getBuildings()
        .then(
            function(buildings){
                console.log(buildings);
                $scope.buildings = buildings;
                $scope.loading = false;
                locationHubFactory.getCampuses().then(
                    function(campuses){
                        $scope.campuses = campuses;
                    }
                );
            }
        )

    $scope.saveBuilding = function(building){
            building.index = false;
            console.log(building);
            building.IsDirty = true;
            if(!$rootScope.copy.Is_active)$rootScope.copy.Is_active = true;
            locationHubFactory.saveBuilding($rootScope.copy)
                .then(
                    function (returned) {
                        console.log(returned);
                        building.IsDirty = false;
                        building.edit = false;
                        building.isNew = true;
                        angular.extend(building, returned);
                        building.Campus = returned.Campus;
                    },
                    function(error){
                        building.IsDirty = false;
                        building.edit = false;
                        $scope.error = 'The building could not be saved.  Please check your internet connection and try again.';
                    }
                )
    }

    $scope.onSelectCampus = function(campus,building){
        building.Campus = campus;
        building.Campus_id = campus.Key_id;
        console.log(building);
    }

    $scope.addBuilding = function(){
        $rootScope.copy = {Class:'Building', Is_active:true, edit:true, index:1, newObj:true}
        $scope.buildings.push($rootScope.copy);
    }

}


campusesCtrl = function($scope, $rootScope, locationHubFactory, roleBasedFactory){
    $rootScope.rbf = roleBasedFactory;
    $scope.loading = true;
    $scope.lhf = locationHubFactory;

    locationHubFactory.getCampuses()
        .then(
            function(campuses){
                console.log(campuses);
                $scope.campuses = campuses;
                $scope.loading = false;
            }
        )

    $scope.saveCampus = function(campus){
            campus.IsDirty = true;
            if(!$rootScope.copy.Is_active)$rootScope.copy.Is_active = true;
            locationHubFactory.saveCampus($rootScope.copy)
                .then(
                    function( returned ){
                        console.log(returned);
                        campus.IsDirty = false;
                        campus.edit = false;
                        campus.isNew = true;
                        campus.index = false;
                        angular.extend(campus, returned)
                    },
                    function(error){
                        campus.IsDirty = false;
                        campus.edit = false;
                        $scope.error = 'The building could not be saved.  Please check your internet connection and try again.';
                    }
                )
    }


    $scope.addCampus = function(){
        $rootScope.copy = {Class:'Campus', Is_active:true, edit:true, index:1, newObj:true}
        $scope.campuses.push($rootScope.copy);
    }

}

modalCtrl = function($scope, $rootScope, locationHubFactory, $modalInstance, convenienceMethods){
    $rootScope.validationError='';
    $rootScope.modal = true;

    $scope.roomUses = [
        {Name:"Chemical Storage"},
        {Name:"Cold Room"},
        {Name:"Dark Room"},
        {Name:"Equipment Room"},
        {Name:"Greenhouse"},
        {Name:"Growth Chamber"},
        {Name:"Rodent Housing"},
        {Name:"Rodent Surgery"},
        {Name:"Tissue Culture"}
    ];

    //make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
    $scope.modalData = convenienceMethods.copyObject( locationHubFactory.getModalData() );
    $scope.selectedUse = {Name:$scope.modalData.Purpose};

    locationHubFactory.getBuildings().then(
        function(buildings){
            $scope.buildings = buildings;
        }
    );

    if($scope.modalData.Class == "Room"){
        locationHubFactory.getAllPis()
            .then(
                function(pis){
                    $scope.pis = pis;
                    $scope.pis.selected = false;
                }
            ).then(
                function(){
                    $scope.departmentsHaveSpecialtyLab = false;
                    var i = $scope.modalData.PrincipalInvestigators.length;
                    while(i--){
                        var n = $scope.modalData.PrincipalInvestigators[i].Departments.length;
                        while(n--) {
                            var dept = $scope.modalData.PrincipalInvestigators[i].Departments[n];
                            if (dept.Specialty_lab != null && dept.Specialty_lab) {
                                console.log(dept.Name);
                                $scope.departmentsHaveSpecialtyLab = true;
                            }
                        }
                    }
                }
            )
    }

    $scope.cancel = function () {
       $rootScope.modal = false;

      $rootScope.validationError='';
      $modalInstance.dismiss();
        console.log($scope.use);
    };


    $scope.onSelectBuilding = function(building){
        $scope.modalData.Building_id = building.Key_id;
    }

    $scope.save = function(obj){
        $rootScope.modal = false;

        obj.IsDirty=true;
        //unset global error, if it exists.
        $scope.error = null;

        locationHubFactory['get'+obj.Class+'s']().then(
                function(stuff){
                    var collection = stuff;
                    locationHubFactory['save'+obj.Class]( obj ).then(
                        function(returned){
                        if( obj.Key_id ){
                            //we are editing an old object
                            var i = collection.length;
                            while(i--){
                                //var objectInCollection = collection[i];
                                if(collection[i].Key_id == returned.Key_id){
                                    collection[i] = returned;
                                    break;
                                }
                            }
                            obj.IsDirty=false;
                        }else{
                            //we are creating an new object
                            collection.push(returned);
                            obj.IsDirty=false;
                        }
                        $modalInstance.close();
                    },
                    function(){
                        $scope.error = 'The' + obj.Class + ' could not be saved.  Please check your internet connection and try again.';
                        obj.IsDirty=false;
                        $modalInstance.dismiss();
                    }
                )
            }

        );

    }

    $scope.handlePI = function(pi, adding){
        pi.saving = true;
        $scope.modalError="";
        var room = $scope.modalData;
        if(!room.Key_id){
            room.PrincipalInvestigators.push(pi);
            return;
        }
        var roomDto = {
          Class: "RelationshipDto",
          relation_id: room.Key_id,
          master_id: pi.Key_id,
          add: adding
        }
        var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=savePIRoomRelation';
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(room){
                var rooms = locationHubFactory.rooms;
                var i = rooms.length;
                while(i--){
                    if (room.Key_id === rooms[i].Key_id) {
                        console.log(room);
                        var originalRoom = $scope.modalData = room;
                        break;
                    }
                }
                pi.saving = false;
                $scope.pis.selected = null;
            },
            function(){
                pi.saving = false;
                var added = adding ? "added" : "removed";
                $scope.error = "The PI could not be " + added + ".  Please check your internet connection and try again.";
            }
        );

    }

}

//
// To-Do example
// You can replace this file with any of the examples from agilityjs.com...
// ... they work right out of the box!
//

//
// Item prototype
//

var message = $$({
  model: {},
  view: {
    format: $('#my-format').html()
  },
  controller: {}
});
$$.document.append(message);
/*
//
// List of items
//
var list = $$({}, '<div> <button id="new">New item</button> <ul></ul> </div>', {
  'click #new': function(){
    var newItem = $$(item, {content:'Click to edit'});
    this.append(newItem, 'ul'); // add to container, appending at <ul>
  }
});

$$.document.append(list);
/*
// Hello World
var message = $$({
  model: {},
  view: {
    format: $('#my-format').html()
  },
  controller: {}
});
$$.document.append(message);

// Prototype
var person = $$({}, '<li data-bind="collection"/>').persist($$.adapter.restful, {collection:'apitest.php'});

// Container
var people = $$({
  model: {},
  view: {
    format: 
      '<div>\
        <span>Loading ...</span>\
        <button>Load people</button><br/><br/>\
        People: <ul/>\
      </div>',
    style:
      '& {position:relative}\
       & span {position:absolute; top:0; right:0; padding:3px 6px; background:red; color:white; display:none; }'
  }, 
  controller: {
    'click button': function(){
      this.empty();
      this.gather(person, 'append', 'ul');
    },
    'persist:start': function(){
      this.view.$('span').show();
    },
    'persist:stop': function(){
      this.view.$('span').hide();
    }
  }
}).persist();
$$.document.append(people);
*/
var monthNames = [ "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December" ];
var monthNames2 = [{val:"01", string:"January"},
                {val:"02", string:"February"},
                {val:"03", string:"March"},
                {val:"04", string:"April"},
                {val:"05", string:"May"},
                {val:"06", string:"June"},
                {val:"07", string:"July"},
                {val:"08", string:"August"},
                {val:"09", string:"September"},
                {val:"10", string:"October"},
                {val:"11", string:"November"},
                {val:"12", string:"December"}]
var getDate = function(time){
            Date.prototype.getMonthFormatted = function() {
                var month = this.getMonth();
                return month < 10 ? '0' + month : month; // ('' + month) for string result
            }

            // Split timestamp into [ Y, M, D, h, m, s ]
            var t = time.split(/[- :]/);

            // Apply each element to the Date function
            // create a new javascript Date object based on the timestamp
            var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

            var hours = date.getHours(); // hours part from the timestamp
            var minutes = date.getMinutes(); // minutes part from the timestamp
            var seconds = date.getSeconds(); // seconds part from the timestamp
            var month = date.getMonth()+1;
            var day = date.getDate();
            var year = date.getFullYear();

            // preserve initial zero
            month = month < 10 ? '0' + month : month;
            day = day < 10 ? '0' + day : day;

            // will display date in mm/dd/yyyy format
            var formattedTime = {};
            formattedTime.formattedString = month + '/' + day + '/' + year;
            formattedTime.year = year;
            formattedTime.monthString = monthNames[date.getMonth()];
            //console.log(formattedTime);
            return formattedTime;
        }

var manageInspections = angular.module('manageInspections', ['convenienceMethodWithRoleBasedModule', 'once', 'ui.bootstrap'])
.filter('toArray', function () {
    return function (object) {
        var array = [];
        for (var prop in object) {
            array.push(object[prop]);
        }
        return array;
    }
})
.filter('getDueDate', function () {
    return function (input) {
        var date = new Date(input);
        var duePoint = date.setDate(date.getDate() + 14);
        dueDate = new Date(duePoint).toISOString();
        return dueDate;
    };
})
.filter('getMonthName', function () {
    return function (input) {
        var i = monthNames2.length;
        while (i--) {
            if (input == monthNames2[i].val) return monthNames2[i].string;
        }
    };
})
.filter('onlyUnselected', function () {
    return function (inspectors, selectedInspectors) {
        if (!selectedInspectors) return inspectors;
        var unselectedInspectors = [];
        var selectedInsepctorIds = [];
        var i = selectedInspectors.length;
        while (i--) {
            selectedInsepctorIds.push(selectedInspectors[i].Key_id);
        }

        var j = inspectors.length;
        while (j--) {
            if (selectedInsepctorIds.indexOf(inspectors[j].Key_id) < 0) unselectedInspectors.push(inspectors[j]);
        }
        return unselectedInspectors;
    };
})
.directive('blurIt', function () {
    return {
        template: '<i class="icon-search></i>test"',
        replace:false,
        link: function (scope, element, attrs) {
            element.keyup(function (event) {
                if (event.which === 13 && element.val() != '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }

                //backspace and delete
                if ((event.which === 8 || event.which === 46) && element.val() == '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }
            });

            element.blur(function (event) {
                if (element.val() != '') {
                    scope.$apply(function () {
                        scope.$eval(attrs.blurIt);
                    });
                    event.preventDefault();
                }
            });
        }
    };
})

.factory('manageInspectionsFactory', function (convenienceMethods, $q, $rootScope) {
    var factory = {};
    factory.InspectionScheduleDtos = [];
    factory.Inspections = [];
    factory.currentYear;
    factory.years = [];
    factory.Inspectors = [];
    factory.minYear = 2015;
    factory.months = [];

    factory.getCurrentYear = function () {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if (this.years.length) {
            deferred.resolve(this.years);
        } else {
            var url = '../../ajaxaction.php?action=getCurrentYear&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function (promise) {
                    deferred.resolve(promise);
                },
                function (promise) {
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function (currentYear) {
                factory.currentYear = { Name: parseInt(currentYear) };
            }
        )

        return deferred.promise;
    }

    factory.getYears = function () {
        var defer = $q.defer();

        this.getCurrentYear()
            .then(
                function (currentYear) {
                    var maxYear = parseInt(currentYear) + 1;
                    var years = [];
                    while (maxYear-- && maxYear >= factory.minYear) {
                        var year = { Name: parseInt(maxYear) }
                        years.push(year);
                    }
                    defer.resolve(years)
                },
                function (error) {

                }

            );

        defer.promise
            .then(
                function (years) {
                    factory.years = years;
                }
            );
        return defer.promise;
    }

    factory.getInspectionScheduleDtos = function (year) {
        factory.year = year;
        //if we don't have the list of pis, get it from the server
        return factory.getDtos(year)
                .then(factory.getInspectionsByYear)
                .then(factory.mapInspectionsToDtos)
    }

    factory.getDtos = function (year) {
        var deferred = $q.defer();
        var url = '../../ajaxaction.php?action=getInspectionSchedule&year=' + year.Name + '&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
            function (promise) {
                factory.InspectionScheduleDtos = promise;
                deferred.resolve(promise);
            },
            function (promise) {
                deferred.reject();
            }
        );
        return deferred.promise;
    }

    factory.getInspectionsByYear = function () {
        var deferred = $q.defer();
        var url = '../../ajaxaction.php?action=getInspectionsByYear&year=' + factory.year.Name + '&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
            function (promise) {
                factory.Inspections = promise;
                deferred.resolve(promise);
            },
            function (promise) {
                deferred.reject();
            }
        );
        return deferred.promise;
    }

    factory.mapInspectionsToDtos = function () {
        var deferred = $q.defer();

        if (!factory.InspectionScheduleDtos || !factory.Inspections) {
            return $q.reject("There was a problem loading the inspections");
        }

        factory.InspectionScheduleDtos.forEach(function (d) {
            d.Inspections = _.find(factory.Inspections, function (i) { return i.Key_id == d.Inspection_id; });
        })

        deferred.resolve(factory.InspectionScheduleDtos);
        return deferred.promise;
    }

    factory.getAllInspectors = function () {
        //if we don't have a the list of pis, get it from the server
        var deferred = $q.defer();
        //lazy load
        if (this.Inspectors.length) {
            deferred.resolve(this.Inspectors);
        } else {
            var url = '../../ajaxaction.php?action=getAllInspectors&callback=JSON_CALLBACK';
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function (promise) {
                    deferred.resolve(promise);
                },
                function (promise) {
                    deferred.reject();
                }
            );
        }

        deferred.promise.then(
            function (inspectors) {
                factory.Inspectors = inspectors;
            }
        )

        return deferred.promise;
    }

    factory.getMonths = function () {
        this.months = [
            { val: "01", string: "January" },
            { val: "02", string: "February" },
            { val: "03", string: "March" },
            { val: "04", string: "April" },
            { val: "05", string: "May" },
            { val: "06", string: "June" },
            { val: "07", string: "July" },
            { val: "08", string: "August" },
            { val: "09", string: "September" },
            { val: "10", string: "October" },
            { val: "11", string: "November" },
            { val: "12", string: "December" },
        ];

        return this.months;
    }

    factory.scheduleInspection = function (dto, year, inspectorIndex) {
        $rootScope.saving = true;
        $rootScope.error = null;
        if (!dto.Inspectors) dto.Inspectors = [];
        var inspectors = dto.Inspections ? dto.Inspections.Inspectors : [];
        if (inspectorIndex) {
            factory.getAllInspectors()
                .then(
                    function (allInspectors) {
                        inspectors.push(allInspectors[inspectorIndex]);
                    }
                )
        }

        dto.Inspections = {
            Class: "Inspection",
            Key_id: dto.Inspection_id,
            Schedule_month: dto.Schedule_month || dto.Inspections.Schedule_month,
            Schedule_year: year.Name,
            Principal_investigator_id: dto.Pi_key_id,
            Inspectors: inspectors,
            Is_active: true
        }

        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, dto)
            .then(
                function (inspection) {
                    dto.Inspections = inspection;
                    dto.Inspection_id = inspection.Key_id;
                    $rootScope.saving = false;
                },
                function (error) {
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.replaceInspector = function (dto, year, oldInspector, newInspector, inspector) {
        $rootScope.saving = true;
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //push the replacement inspector into the list
        $rootScope.dtoCopy.Inspections.Inspectors.push(newInspector);
        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    inspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    inspector.edit = false;
                    $rootScope.dtoCopy = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.removeInspector = function (dto, year, inspector) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        $rootScope.saving = true;
        //find the inspector when need to replace and remove them from the copy
        var i = $rootScope.dtoCopy.Inspections.Inspectors.length;
        while (i--) {
            if (inspector.Key_id == $rootScope.dtoCopy.Inspections.Inspectors[i].Key_id) {
                $rootScope.dtoCopy.Inspections.Inspectors.splice(i, 1);
            }
        }

        //save the inspection, then set the dto's inspection object to the returned inspection
        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    inspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    inspector.edit = false;
                    $rootScope.dtoCopy = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.addInspector = function (dto, year, newInspector) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        $rootScope.saving = true;
        $rootScope.error = null;
        $rootScope.dtoCopy.Inspections.Inspectors.push(newInspector);

        var url = '../../ajaxaction.php?action=scheduleInspection';
        return convenienceMethods.saveDataAndDefer(url, $rootScope.dtoCopy)
            .then(
                function (inspection) {
                    dto.addInspector = false;
                    newInspector.edit = false;
                    dto.Inspections.Inspectors = [];
                    dto.Inspections.Inspectors = inspection.Inspectors;
                    $rootScope.saving = false;
                    $rootScope.dtoCopy = false;
                },
                function (error) {
                    dto.addInspector = false;
                    newInspector.edit = false;
                    $rootScope.saving = false;
                    $rootScope.error = "The Inspection could not be saved.  Please check your internet connection and try again."
                }
            );
    }

    factory.editInspector = function (inspector, dto) {
        $rootScope.dtoCopy = convenienceMethods.copyObject(dto);
        inspector.edit = true;
    }

    factory.cancelEditInspector = function (inspector) {
        inspector.edit = false;
        $rootScope.dtoCopy = false;
    }

    factory.parseDtos = function (dto) {
        var dtos = [];
        var l = dto.Pis.length;
        for (var i = 0; i < l; i++) {
            var pi = dto.Pis[i];
            pi = factory.getInspectionsByPi(pi, dto.Inpsections);       
            
            //create a dto obj for each inspection that the pi has
            //cache an obj of uninspected rooms, grouped by building
            var n = pi.Inspections.length;
            for(var j = 0; j < n; j++){
                var dtoTemplate = {
                    Pi_name: pi.User.Name,
                    pi_key_id: pi.User.Key_id,
                }
            }
        }
        //create a dto obj for each inspection the pi still needs
        dtos = dto.Pis;
        return dtos;
    }

    factory.getInspectionsByPi = function (pi, inspections) {
        var l = inspections.length;
        pi.Inspections = [];
        for (var i = 0 ; i < l; i++) {
            var insp = inspections[i];
            if (insp.Principal_investigator_id == pi.Key_id) {
                pi.Inspections.push(insp);
            }
        }
        return pi;
    }

    factory.collapseDtos = function (dtos) {
        var l = dtos.length;
        var ids = [];
        var duplicateIds = [];
        for (var i = 0; i < l; i++) {
            var d = dtos[i];
            invertRoomForNonMultiples(d);
            if (!d.Inspections) continue;
            if (ids.indexOf(d.Inspections.Key_id) < 0) {
                ids.push(d.Inspections.Key_id);
            } else if (duplicateIds.indexOf(d.Inspections.Key_id) < 0) {
                duplicateIds.push(d.Inspections.Key_id);
            }
        }
        
        var masterIndex;
        var l = duplicateIds.length;
        for (var i = 0; i < l; i++) {
            var id = duplicateIds[i];
            var relevantDtos = dtos.reduce(function (relevantDtos, dto, index) {
                if (dto.Inspections && dto.Inspections.Key_id == id) {
                    relevantDtos.push(dto);
                    if (!masterIndex) {
                        var masterIndex = index;
                    }
                    dtos.splice(index, 1);
                }
                return relevantDtos;
            }, []);
            var masterDto = JSON.parse(JSON.stringify(relevantDtos[0]));
            map = {
                Building_rooms: null,
                Campus_name: null,
                Building_name: null,
                Campus_key_id: null,
                Building_key_id: null,
                IsMultiple:true,
                Bio_hazards_present: relevantDtos.some(function(dto){return dto.Bio_hazards_present }),
                Chem_hazards_present: relevantDtos.some(function (dto) { return dto.Chem_hazards_present }),
                Rad_hazards_present: relevantDtos.some(function (dto) { return dto.Rad_hazards_present }),
                Inspection_rooms:relevantDtos.every(function(room){return room}),
                Deficiency_selection_count: null,
                Campuses: invertRooms(relevantDtos)
            }
            angular.extend(masterDto, map);
            dtos.splice(masterIndex, 0, masterDto);
        }

        function invertRooms(dtos) {
            var campuses = [];
            var campusIds = [];
            var buildingIds = [];
            var buildings = [];
            var rooms = [];
            var inspectionRooms = [];
            dtos.forEach(function (dto) {
                rooms = rooms.concat(dto.Building_rooms);
                inspectionRooms = inspectionRooms.concat(dto.Inspection_rooms);
                top: // loop through all rooms and flag those notInspected
                for (var i = 0; i < rooms.length; i++) {
                    for (var j = 0; j < inspectionRooms.length; j++) {
                        if (rooms[i].Key_id == inspectionRooms[j].Key_id) continue top;
                    }
                    rooms[i].notInspected = true;
                }
                if (campusIds.indexOf(dto.Campus_key_id) == -1) {
                    var campus = {
                        Campus_key_id: dto.Campus_key_id,
                        Campus_name: dto.Campus_name,
                        Buildings: []
                    }
                    campuses.push(campus);
                    campusIds.push(dto.Campus_key_id);
                }
                if (buildingIds.indexOf(dto.Building_id) == -1) {
                    var bldg = {
                        Building_name: dto.Building_name,
                        Building_id: dto.Building_key_id,
                        Campus_id: dto.Campus_key_id,
                        Campus_name: dto.Campus_name,
                        Rooms: []
                    }
                    buildings.push(bldg);
                }
            });
            rooms.forEach(function (room, idx) {
                buildings.forEach(function(bldg){
                    if (room && room.Building_id == bldg.Building_id) {
                        bldg.Rooms.push(room);
                        campuses.forEach(function (c) {
                            if (c.Buildings.indexOf(bldg) == -1 && c.Campus_key_id == bldg.Campus_id) {
                                c.Buildings.push(bldg);
                            }
                        })
                    }
                })
            })
            
            return campuses;
        }

        function invertRoomForNonMultiples(dto) {
            var rooms = dto.Inspection_rooms || dto.Building_rooms;
            var l = rooms.length;
            dto.Campuses = [{
                Campus_key_id: dto.Campus_key_id,
                Campus_name: dto.Campus_name,
                Buildings: [
                    {
                        Building_name: dto.Building_name,
                        Building_id: dto.Building_id,
                        Rooms: rooms.map(function (room, idx) {
                            return room;
                        })
                    }
                ]
            }]

            return dto.Campuses;
        }
        return dtos;
    }

    return factory;
})

.controller('manageInspectionCtrl', function ($scope, $timeout, manageInspectionsFactory, convenienceMethods, roleBasedFactory, $q) {
    $scope.rbf = roleBasedFactory;
    $scope.mif = manageInspectionsFactory;
    $scope.convenienceMethods = convenienceMethods;
    $scope.constants = Constants;
    $scope.years = [];
    $scope.search = {init:true};
    $scope.run = false;

    

    var getDtos = function (year) {
        return manageInspectionsFactory.getInspectionScheduleDtos(year)
            .then(
                function (dtos) {
                    //$scope.dtos = manageInspectionsFactory.parseDtos(dto);
                    $scope.dtos = manageInspectionsFactory.collapseDtos(dtos);
                    //$scope.dtos = dtos;
                    $scope.loading = false;
                    $scope.genericFilter(true);
                }
            )
    },

    getYears = function () {
        return manageInspectionsFactory.getYears()
            .then(
                function (years) {
                    $scope.yearHolder = {};
                    $scope.yearHolder.years = years;
                    $scope.yearHolder.selectedYear = $scope.yearHolder.years[0];
                    return $scope.yearHolder.selectedYear;
                },
                function (error) {
                    $scope.error = 'Uh oh';
                }
            )
    },

    getAllInspectors = function () {
        return manageInspectionsFactory.getAllInspectors()
            .then(
                function (inspectors) {
                    $scope.inspectors = inspectors;
                }
            )
    },

    getMonths = function () {
        $scope.months = manageInspectionsFactory.getMonths();

    },

    getInspectionsByYear = function () {

    }

    var init = function () {
        $scope.loading = true;
        getAllInspectors()
            .then(getYears)
            .then(getDtos)
            .then(getMonths)
    }

    init();

    $scope.selectYear = function () {
        $scope.loading = true;
        $scope.dtos = [];

        manageInspectionsFactory.getInspectionScheduleDtos($scope.yearHolder.selectedYear)
            .then(
                function (dtos) {
                    //$scope.dtos = dtos;
                    $scope.dtos = manageInspectionsFactory.collapseDtos(dtos);
                    $scope.loading = false;
                    $scope.genericFilter(true);

                },
                function (error) {
                    $scope.error = "The system could not retrieve the list of inspections for the selected year.  Please check your internet connection and try again."
                }
            )
    }

    $scope.genericFilter = function (init) {
        var filtered = [];
        var defer = $q.defer();
        if (init) {
            filtered = $scope.dtos;
            defer.resolve(filtered);
            $scope.filtered = filtered;
            return;
        }

        var search = $scope.search;
        var items = $scope.dtos;
       
        if (search) {
            $scope.filtering = true;
            window.setTimeout(function () {
                var i = items.length;
                var filtered = [];
                var matched;
                while (i--) {
                    //we filter for every set search filter, looping through the collection only once
                    var item = items[i];
                    matched = true;

                    if (search.building) {
                        matched = false
                        if (item.Campuses && item.Campuses.length) {
                            item.Campuses.forEach(function (campus) {
                                campus.Buildings.forEach(function (b) {
                                    if (b.Building_name.toLowerCase().indexOf(search.building.toLowerCase()) > -1) {
                                        matched = true;
                                        return;
                                    }
                                })
                            });
                        }
                    }

                    if (search.type) {
                        if (!item.Inspections) {
                            matched = false;
                            continue;
                        }                 
                        
                        if (search.type == Constants.INSPECTION.TYPE.BIO) {
                            //only items with inspections that aren't rad inspection that have bio hazards
                            if (item.Inspections.Is_rad || !item.Bio_hazards_present) {
                                matched = false;
                                continue;
                            }
                        } else if (search.type == Constants.INSPECTION.TYPE.CHEM) {
                            //only items with inspections that aren't rad inspection that have bio hazards
                            if (item.Inspections.Is_rad || !item.Chem_hazards_present) {
                                matched = false;
                                continue;
                            }
                        } else if(!item.Inspections.Is_rad) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.inspector) {
                        if (item.Inspections) {
                            if (item.Inspections.Inspectors && item.Inspections.Inspectors.length) {
                                var z = item.Inspections.Inspectors.length;
                                var longString = "";
                                while (z--) {
                                    longString += item.Inspections.Inspectors[z].Name;
                                }
                                if (longString.toLowerCase().indexOf(search.inspector.toLowerCase()) < 0) matched = false;
                            } else {
                                if (Constants.INSPECTION.SCHEDULE_STATUS.NOT_ASSIGNED.toLowerCase().indexOf(search.inspector.toLowerCase()) < 0) {
                                    matched = false;
                                    continue;
                                }
                            }
                        } else {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.campus) {
                        matched = false
                        if (item.Campuses && item.Campuses.length) {
                            item.Campuses.forEach(function (campus) {
                                if (campus.Campus_name.toLowerCase().indexOf(search.campus.toLowerCase()) > -1) {
                                    matched = true;
                                    return;
                                }
                            });
                        }
                    }

                    if (matched && search.pi && item.Pi_name) {
                        if (item.Pi_name.toLowerCase().indexOf(search.pi.toLowerCase()) < 0) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.status) {
                        if (item.Inspections) var status = item.Inspections.Status;
                        if (!item.Inspections) var status = Constants.INSPECTION.STATUS.NOT_SCHEDULED;
                        if (status.toLowerCase() != search.status.toLowerCase()) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched && search.date) {
                        if (!item.Inspections || !item.Inspections.Date_started && !item.Inspections.Schedule_month) {
                            matched = false;
                            continue;
                        } else {
                            if (item.Inspections && item.Inspections.Date_started) var tempDate = getDate(item.Inspections.Date_started);
                            if (tempDate && tempDate.formattedString.indexOf(search.date) < 0) {
                                var goingToMatch = false;
                            } else {
                                var goingToMatch = true;
                            }
                            if (item.Inspections && item.Inspections.Schedule_month) {
                                //console.log(item.Inspections.Schedule_month);
                                var j = monthNames2.length
                                while (j--) {
                                    if (monthNames2[j].val == item.Inspections.Schedule_month) {
                                        if (monthNames2[j].string.toLowerCase().indexOf(search.date.toLowerCase()) > -1) var goingToMatch = true;
                                    }
                                }
                            }
                            if (!goingToMatch) {
                                matched = false;
                                continue;
                            }
                        }
                    }
                    if (matched && search.hazards) {
                        if (!item[search.hazards]) {
                            matched = false;
                            continue;
                        }
                    }

                    if (matched == true) filtered.unshift(item);
                }
            
                $scope.filtered = filtered;
                defer.resolve(filtered);
                
            }, 100);
            defer.promise.then(function () {
                $scope.filtering = false;
            });
            return;
        }
    }

    $scope.getRoomUrlString = function (dto) {
        roomIds = [];
        //console.log(dto);
        dto.Inspection_rooms.forEach(function (r) {
            roomIds.push(r.Key_id);
        })

        dto.roomUrlParam = $.param({"room":roomIds});
        return dto;
    }

});
//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('modalPosition', [])

.directive('modal', ['$window', function ($window) {
    return {
        restrict: 'C',
        link: function (scope, element, attributes) {
            var onResize = function () {
                var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({ maxHeight: $window.innerHeight * .95, minHeight: '250px' });
                $(element[0]).find('.modal-content').css({ maxHeight: ($window.innerHeight * .95 - 50), minHeight: '250px' });
                $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                $(element[0]).find('.modal-body').css({ maxHeight: $window.innerHeight * .85 - 150 });
                //overflowY: 'auto',
                if ($(element[0]).find('.modal-body').css('overflowY') != 'visible') {
                    $(element[0]).find('.modal-body').css({ overflowY: 'auto'});
                }
                $(element[0]).find('.modal-body ul').css({ maxHeight: $window.innerHeight * .85 - 210 });

                if ($('.wide-modal').length) {
                    if ($window.innerWidth > 1370) {
                        $(element[0]).width($window.innerWidth * .8);
                        $(element[0]).css({ 'left': $window.innerWidth * .1 + 'px', 'marginLeft': 0 });
                    } else {
                        $(element[0]).width($window.innerWidth * .98);
                        $(element[0]).css({ 'left': $window.innerWidth * .005 + 'px', 'marginLeft': 0 });
                    }
                }
                if ($('.use-log-modal').length) {
                    $(element[0]).width(800);
                    $(element[0]).css({ 'left': ($window.innerWidth - 800) / 2 + 'px', 'marginLeft': 0 });
                    $(element[0]).find('.modal-body').css({ maxHeight: $window.innerHeight * .85 });
                    var topMargin = $window.innerHeight - element[0].clientHeight;
                    $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                }
            }


            onResize();


            var relevantSelectMatches,
            selectMap = [],
            relevantSelectArrows,
            arrowMap = [];
            var body = $(element[0]).find('.modal-body');

            function isElementInViewport(el) {

                //special bonus for those using jQuery
                if (typeof jQuery === "function" && el instanceof jQuery) {
                    el = el[0];
                }   
                var rect = el.getBoundingClientRect();
                return (
                    $(el).position().top >= 40 &&
                    rect.bottom <= (body.outerHeight() + 83)  /*or $(window).height() */
                );
            }


            var drops;
            var positionUISelects = function () {
                scope.things = body.find(".ui-select-container");
                if (!scope.things || !scope.things.length) return;
                if (!body.hasClass("scrolled")) {
                    body.addClass("scrolled");
                    setTimeout(function () {
                        onResize();
                        var h = body.height();
                        body.css({ 'height': 10000 + 'px' });
                        body.animate({
                            scrollTop: 2000
                        }, .1);
                        body.animate({
                            scrollTop: 0
                        }, .1);
                        body.css({ 'height': h + 'px' });
                        onResize();
                    }, 301)

                }
                var setDrops = function (dropDowns) {
                    drops = dropDowns;
                }
                setDrops(scope.things);
                drops.each(function (i) {
                    var $top = body.scrollTop();
                    var $this = $(this);
                    var arrow = $this.find(".icon-arrow-down.dropdown-arrow");
                    var match = $this.find(".ui-select-match");
                    var drop = $this.find(".ui-select-dropdown");

                    if (!isElementInViewport($this)) {
                        arrow.css({ "visibility": "hidden", 'top': $this.position().top});
                        match.css({ "visibility": "hidden", 'top': $this.position().top, "width":$this.width()*.9 });
                        drop.css({ "visibility": "hidden", 'top': $this.position().top });
                    } else {
                        arrow.css({ "visibility": "visible", 'top': $this.position().top });
                        match.css({ "visibility": "visible", 'top': $this.position().top, "width":$this.width()*.9 });
                        drop.css({ "visibility": "visible", 'top': $this.position().top + 28 });
                    }
                });
                return false;
                
            }

            body.on('scroll', function () {
                positionUISelects();
            });
            positionUISelects();

            $(window).on("orientationchange", function () {
                window.setTimeout(function () { onResize(); }, 300);
            });
            angular.element($(element[0])).bind('DOMNodeInserted', function () {
                onResize();
                positionUISelects();
            })
            angular.element($window).bind('resize', function () {
                onResize();
                positionUISelects();
            });

        }
    }
}]);
var pi = $$({id:123}, '<p>Name: <span data-bind="name"/></p>', '& span {background:blue; color:white; padding:3px 6px;}');

// Initialize plugin with RESTful adapter, load model with above id:
pi.persist($$.adapter.restful, {collection:'people'}).load();

$$.document.append(person);
var piHub = angular.module('piHub', ['ui.bootstrap', 'convenienceMethodWithRoleBasedModule', 'userList', 'cgBusy'])

.config(function($routeProvider){
    $routeProvider
        .when('/rooms',
            {
                templateUrl: 'piHubPartials/rooms.html',
                controller: piHubRoomController
            }
        )
        .when('/personnel',
            {
                templateUrl: 'piHubPartials/personnel.html',
                controller: piHubPersonnelController
            }
        )
        .when('/departments',
            {
                templateUrl: 'piHubPartials/departments.html',
                controller: piHubDepartmentsController
            }
        )
        .otherwise(
            {
                redirectTo: '/rooms'
            }
        );
})
.filter("noSupervisor", function (userHubFactory) {
    return function (users) {
        if (!users || !users.length) return;
        var l = users.length;
        var matchedUsers = [];
        for (var i = 0; i < l; i++) {
            var u = users[i];
            if (!u.Supervisor_id && (userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_PERSONNEL))) {
                matchedUsers.push(u);
            }
        }
        return matchedUsers;
    }
})
.factory('piHubFactory', function(convenienceMethods,$q, userHubFactory){
    var factory = {};
    factory.setPI = function(pi){
        this.pi = pi;
    }
    factory.getPI = function(){
        return this.pi;
    }
    factory.setUser = function(user){
        this.user = user;
    }
    factory.getUser = function(){
        return this.user;
    }

    factory.createRoom = function(roomDto){
        var url = "../../ajaxaction.php?action=saveRoom";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }

    factory.addRoom = function(roomDto){
        var url = "../../ajaxaction.php?action=savePIRoomRelation";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );
        return deferred.promise
    }

    factory.getAllUsers = function () {
        var deferred = $q.defer();
        if (!factory.users) {
            userHubFactory.getAllUsers().then(
                function (promise) {
                    factory.users = promise;
                    deferred.resolve(promise);
                },
                function (promise) {
                    deferred.reject();
                }
            );
        } else {
            deferred.resolve(factory.users);
        }
        return deferred.promise
    }

    //factory

    return factory;
});

piHubMainController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory, userHubFactory){
    $scope.doneLoading = false;

    $scope.setRoute = function(route){
        $location.path(route);
    }

    init();

    $scope.order='Last_name';

    $scope.getRoomUrlString = function (room) {
        console.log(room);
        roomIds = [room.Key_id];        
        room.roomUrlParam = $.param({ "room": roomIds });
        return room;
    }

    function init(){
        if($location.search().hasOwnProperty('pi')){
             //getPI if there is a "pi" index in the GET
             getPi($location.search().pi);
        }else{
            $scope.noPiSet = true;
        }

        if($location.search().hasOwnProperty('inspection')){
            $scope.inspectionId = $location.search().inspection;
        }

        console.log($location.search());

        //always get a list of all PIs so that a user can change the PI in scope
        var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
           convenienceMethods.getData( url, onGetAllPIs, onFailGetAllPIs );

        var url = '../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK';
        convenienceMethods.getData( url, onGetBuildings, onFailGetBuildings );
    }

    function onGetBuildings(data){
        $scope.buildings = data;
        $rootScope.buildings = data;
    }

    function onFailGetBuildings(){
        alert('There was a problem when the system tried to get the list of buildings.')
    }


    function getPi(PIKeyID){
        $scope.noPiSet = false;
        $scope.PI = false;
        var url = '../../ajaxaction.php?action=getPIById&id='+PIKeyID+'&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url)
            .then(getRoomsByPi);
    }

    function getRoomsByPi(pi){
        var url = '../../ajaxaction.php?action=getRoomsByPIId&piId='+pi.Key_id+'&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url)
            .then(
                function( rooms ){
                    pi.Rooms = rooms;
                    $scope.PI = pi;
                    $scope.noPiSet = false;
                },
                function( error ){
                    $scope.error = "The system couldn't retrieve the selected Principal Investigator.  Please check your internet connection and try again."
                }
            );

    }

    function onGetPI(data){
        console.log(data);
        $scope.PI = data;
        piHubFactory.setPI($scope.PI);
        $scope.doneLoading = data.doneLoading;
        $location.search("pi", $scope.PI.Key_id);
    }

    function onFailGetPI(){
        alert('The system couldn\'t find the Principal Investigator');
    }

    function onGetAllPIs(data){
        $scope.PIs = data;
        $scope.doneLoadingAll = data.doneLoading;
    }

    function onFailGetAllPIs(){
        alert('Something went wrong getting the list of all Principal Investigators');
    }

    //callback function called when a PI is selected in the typeahead
    $scope.onSelectPi = function($item, $model, $label){
        $location.search("pi", $item.Key_id);
        getPi($item.Key_id);
    }

    $scope.removeRoom = function(room){
        var modalInstance = $modal.open({
          templateUrl: 'roomConfirmation.html',
          controller: roomConfirmationController,
          resolve: {
            PI: function () {
              return $scope.PI;
            },
            room: function(){
                return room;
            }
          }
        });

        modalInstance.result.then(function (PI) {
            console.log(PI);
             $scope.PI.Rooms = [];
             $scope.PI.Rooms = PI.Rooms;
        }, function () {

          //$log.info('Modal dismissed at: ' + new Date());
        });
    }

    function onRemoveRoom(returned, room){
        room.IsDirty = false;
        var idx = convenienceMethods.arrayContainsObject($scope.PI.Rooms, room, null, true);
        console.log(idx);
        console.log($scope.PI.Rooms[idx]);
        $scope.PI.Rooms.splice(idx,1);
    }

    function onFailRemoveRoom(){
        alert("There was a problem when the system attempted to remove the room.");
    }

    $scope.modalify = function(pi,adding){

          var modalInstance = $modal.open({
          templateUrl: 'roomHandlerModal.html',
          controller: ModalInstanceCtrl,
          resolve: {
            PI: function () {
              return $scope.PI;
            },
            adding: function (){
                if(adding)return adding;
            }
          }
        });

        modalInstance.result.then(function (PI) {
             $scope.PI = {};
             $scope.PI = PI;
        }, function () {

          //$log.info('Modal dismissed at: ' + new Date());
        });

    }

    $scope.showHazards = function(room){
        console.log(room);

          var modalInstance = $modal.open({
          templateUrl: 'roomHazardsModal.html',
          controller: hazardDisplayModalInstanceController,
          resolve: {
            room: function () {
              return room;
            },

          }
        });

        modalInstance.result.then(function (hazards) {
            console.log(hazards);
        }, function () {

          //$log.info('Modal dismissed at: ' + new Date());
        });
    }

    $scope.openModal = function(pi){
        var user = pi.User;
        // pump in PIs Departments
        user.PrincipalInvestigator = {Departments:pi.Departments};
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/piModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
            angular.extend(user, returnedUser)
        });
    }

  };

var ModalInstanceCtrl = function ($scope, $rootScope, $modalInstance, PI, adding, convenienceMethods, piHubFactory, $q) {
    $scope.PI = PI;
    console.log(adding);

    if(adding)$scope.addRoom = true;
    if($rootScope.buildings)$scope.buildings = $rootScope.buildings;
    if(!$scope.buildings){
        var url = '../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK';
        convenienceMethods.getData( url, onGetBuildings, onFailGetBuildings );
    }


    function onGetBuildings(data){
        $scope.buildings = data;
        //loop through pi rooms, disable rooms pi has already
    }

    function onFailGetBuildings(){
        alert('There was a problem when the system tried to get the list of buildings.')
    }

    $scope.onSelectBuilding = function (item) {
        $scope.chosenBuilding = angular.copy(item);
        checkRooms($scope.chosenBuilding, $scope.PI);
    }

    function checkRooms(building, pi) {
        $scope.roomsByFloor = {};
        var lastLabel = '';
        angular.forEach(building.Rooms, function (room, key) {
            if (convenienceMethods.arrayContainsObject(pi.Rooms, room)) room.piHasRel = true;            
            var floorLabel = room.Name.charAt(0);
            if (lastLabel != floorLabel && !$scope.roomsByFloor.hasOwnProperty(floorLabel)) {
                $scope.roomsByFloor[floorLabel] = [];
            }
            $scope.roomsByFloor[floorLabel].push(room);
            lastLabel = floorLabel;
        });
    }

    $scope.handleRoomChecked = function(room,building){
        room.IsDirty = true;
        var roomCopy = angular.copy(room);
        var add = false;
        if(room.piHasRel) var add = true;

        roomDto = {
          Class: "RelationshipDto",
          relation_id: room.Key_id,
          master_id: $scope.PI.Key_id,
          add: add
        }

        //room.piHasRel = !room.piHasRel;

        piHubFactory.addRoom(roomDto).then(
            function(promise){
                console.log(room);
                room.Building = {};
                room.Building.Name = building.Name;
                //room.piHasRel = !room.piHasRel;
                console.log(roomDto);
                if(room.piHasRel){
                    $scope.PI.Rooms.push(room);
                }else{

                    var idx = convenienceMethods.arrayContainsObject($scope.PI.Rooms, room, null, true);
                    console.log(idx);
                    console.log($scope.PI.Rooms[idx]);
                    $scope.PI.Rooms.splice(idx,1);
                }
                console.log($scope.PI);

                room.IsDirty = false;
            },
            function(){
                $scope.error = "The room could not be added to the PI.  Please check your internet connection and try again."
            }
        )

    }

    function onSaveRoomRelation(data,room,building){
        console.log(data);
        console.log(room);

        /*
        angular.forEach(building.Rooms, function(room, key){
            if(convenienceMethods.arrayContainsObject(pi.Rooms,room))room.piHasRel = true;
        });
*/
    }

    function onFailSaveRoomRelation(){

    }

    $scope.addRoomToBuidling = function(newRoom){
        newRoom.IsDirty = true;
        roomDto = {
          Class: "Room",
          Building_id: $scope.chosenBuilding.Key_id,
          Name: newRoom.Name,
          Is_active:true
        }
        $scope.error = "";

        var len = $scope.chosenBuilding.Rooms.length;
        for (var i = 0; i < len; i++) {
            var room = $scope.chosenBuilding.Rooms[i];
            console.log(roomDto.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase(), room.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase());
            if (roomDto.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase() == room.Name.replace(/[^A-Za-z0-9]/g, '').toLowerCase()) {
                $scope.error = "Room " + roomDto.Name + " has already been created";
                newRoom.IsDirty = false;
                return false;
            }
        }

        var createDefer = $q.defer();
        piHubFactory.createRoom(roomDto).then(
            function(room){
                room.IsDirty = false;
                $scope.chosenBuilding.Rooms.push(room);
                newRoom.IsDirty = false;
                createDefer.resolve(room);
                $scope.onSelectBuilding($scope.chosenBuilding);

                for (var i = 0; i < $rootScope.buildings.length; i++) {
                    if ($scope.chosenBuilding.Key_id == $rootScope.buildings[i].Key_id) {
                        $rootScope.buildings[i].Rooms.push(room);
                    }
                }

                return createDefer.promise;
            },
            function(){
                newRoom.IsDirty = false;
                $scope.error="The room could not be created.  Please check your internet connection.";
                createDefer.reject();
                return createDefer.promise;
            }
        ).then(
            function(room){
                console.log(room);
                room.piHasRel = true;

                //add room to pi
                $scope.handleRoomChecked(room,$scope.chosenBuilding);
            }
        )
    }

    function onSaveRoom(data, room){

    }

    function onFailSaveRoom(){
        alert("Something went wrong when the system tried to create the new room.");
    }

    $scope.close = function(){
        $modalInstance.close($scope.PI);
    }

}

piHubRoomController = function($scope, $location, convenienceMethods){

    init();
    function init(){
        //var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
        //convenienceMethods.getData( url, onGetDepartemnts, onFailGetDepartments );
    }

}

piHubPersonnelController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory, userHubFactory){

    init();
    function init(){
        var url = '../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK';
        convenienceMethods.getData( url, onGetUsers, onFailGetUsers );
        $rootScope.userPromise = piHubFactory.getAllUsers();
    }

    function onGetUsers(data){
        $scope.users = data;
    }

    function onFailGetUsers(){
        alert("Something went wrong when the system tried to get the list of users.");
    }

    $scope.editUser = function(i){
        var modalInstance = $modal.open({
          templateUrl: 'personnelModal.html',
          controller: personnelModalController,
          resolve: {
            items: function () {
              return i;
            }
          }
        });

        modalInstance.result.then(function (i) {
           console.log(piHubFactory.getUser());
           $scope.PI.LabPersonnel[i] = angular.copy(piHubFactory.getUser());
           piHubFactory.setPI($scope.PI);
        });

    }

    $scope.onSelectUser = function(user){
        $scope.selectedUser.IsDirty = true;
        userCopy = angular.copy(user);
        userCopy.Supervisor_id = $scope.PI.Key_id;

        convenienceMethods.updateObject( userCopy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );

    }

    function onSaveUser(data, user){
        $scope.selectedUser.IsDirty = false;
        if(!convenienceMethods.arrayContainsObject($scope.PI.LabPersonnel, data)){
            $scope.PI.LabPersonnel.push(data);
        }

    }

    function onFailSaveUser(){
        alert('There was a problem trying to save the user.')
    }

    $scope.deactivateUser = function(user){

        piHubFactory.setUser(user);
        var functionType = 'inactivate';
        var modalInstance = $modal.open({
          templateUrl: 'confirmationModal.html',
          controller: confirmationController,
          resolve: {
            items: function () {
              return functionType;
            }
          }
        });

        modalInstance.result.then(function (user) {
           onRemoveUser(user);
        });

    }

    $scope.confirmRemoveUser = function(user){

        piHubFactory.setUser(user);
        var functionType = 'remove';
        var modalInstance = $modal.open({
          templateUrl: 'confirmationModal.html',
          controller: confirmationController,
          resolve: {
            items: function () {
              return functionType;
            }
          }
        });

        modalInstance.result.then(function (user) {
           onRemoveUser(user);
        });

    }

    $scope.removeUser = function(user){
        user.IsDirty = true;
        userCopy = angular.copy(user);
        userCopy.Supervisor_id = null;

        convenienceMethods.updateObject( userCopy, user, onRemoveUser, onFailRemoveUser, '../../ajaxaction.php?action=saveUser' );

    }

    function onRemoveUser(user){
        user.IsDirty = false;
        var idx = convenienceMethods.arrayContainsObject($scope.PI.LabPersonnel, user, null,true);
        console.log(idx);
        if(idx>-1)$scope.PI.LabPersonnel.splice(idx,1);
    }

    function onFailRemoveUser(){
        alert('There was a problem trying to save the user.');
    }

    $scope.openModal = function(user, role){
        if(!user){
          var user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          $rootScope.userPromise = getUsers()
          .then(getRoles)
          .then(fireModal);
        } else {
            $rootScope.userPromise = getUsers()
            .then(fireModal);
        }

        function getUsers(){
            return piHubFactory.getAllUsers()
              .then(
                function(users){
                  return user;
                }
              )
        }

        function fireModal(user) {
            user.piHub = true;
            if (user.Class == "PrincipalInvestigator"){
                // pump in PIs Departments
                var pi = user;
                user = pi.User;
                user.PrincipalInvestigator = {Departments:pi.Departments};
            }
            userHubFactory.setModalData(user);
            //determine which modal we should open based on the user's role(s)
            if(userHubFactory.hasRole(user, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
                templateString = "piModal";
            }else if(userHubFactory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT)){
                templateString = "labContactModal";
            }else{
                templateString = "labPersonnelModal";
            }

            var modalInstance = $modal.open({
              templateUrl: 'userHubPartials/'+templateString+'.html',
              controller: modalCtrl
            });

            modalInstance.result.then(function (returnedUser) {
              if(user.Key_id){
                angular.extend(user, returnedUser)
              }else{
                pi.LabPersonnel.push(returnedUser);
              }
            });
        }

        function getRoles(user) {

            return userHubFactory.getAllRoles()
                .then(
                    function (roles) {
                        var i = userHubFactory.roles.length;
                        while(i--){
                            if(userHubFactory.roles[i].Name.indexOf(role)>-1){
                                user.Roles.push(userHubFactory.roles[i]);
                                return user;
                            }
                        }
                    }
                )
        }
    }

    $scope.openAssignModal = function(type){
            var modalInstance = $modal.open({
              templateUrl: 'piHubPartials/assign-user.html',
              controller: assignUserCtrl,
              resolve: {
                modalData: function () {
                  $scope.PI.type = type;
                  return $scope.PI;
                }
              }
            });

            modalInstance.result.then(function (returnedUser) {
                $scope.PI.LabPersonnel.push(returnedUser);
            });
    }

}
roomConfirmationController = function(PI, room, $scope, piHubFactory, $modalInstance, convenienceMethods, $q){
    $scope.PI = PI;
    $scope.room = room;

    $scope.confirm = function(){
        $scope.saving = true;

        $scope.error=false;

        roomDto = {
          Class: "RelationshipDto",
          relation_id: room.Key_id,
          master_id: PI.Key_id,
          add: false
        }
        console.log(PI);
        var url = '../../ajaxaction.php?action=savePIRoomRelation';
        convenienceMethods.saveDataAndDefer(url, roomDto).then(
            function(){
                var idx = convenienceMethods.arrayContainsObject(PI.Rooms, room, null, true);
                PI.Rooms.splice(idx,1);
                console.log(PI)
                $scope.saving = false;
                $modalInstance.dismiss();
            },
            function(){
                $scope.saving = false;
                $scope.error = "The room could not be removed.  Please check your internet connection and try again."
            }
        );

    }

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    }

}

confirmationController = function(items, $scope, piHubFactory, $modalInstance, convenienceMethods){
    $scope.userCopy = piHubFactory.getUser();
    var functionType = items;
    if(functionType.toLowerCase() == 'inactivate'){
        $scope.message =  "Do you want to remove "+$scope.userCopy.Name+" from the PI's lab personnel list?";
    }else{
        $scope.message =  'Do you want to inactivate  '+$scope.userCopy.Name+' everywhere in the Research Safety Management System user list?';
    }

    $scope.confirm = function(){
        $scope.userCopy.IsDirty = true;
        //are we deactivating this user?  Set the user's Is_active property to false, if so.
        if(functionType.toLowerCase() == 'remove')$scope.userCopy.Is_active = false;

        //get rid of the user's PI relationship.
        $scope.userCopy.Supervisor_id = null;

        //save the user
        convenienceMethods.updateObject( $scope.userCopy, null, onConfirmRemoveUser, onFailRemoveUser, '../../ajaxaction.php?action=saveUser' );
    }

    //save call succeeded.  go back to the normal view
    function onConfirmRemoveUser(user){
        $scope.userCopy.IsDirty = false;
        $modalInstance.close(user);
    }

    function onFailRemoveUser(){
        $scope.userCopy.IsDirty = false;
        $scope.error='There was a problem when the system tried to remove the user.  Please check your internet connection and try again.';
    }

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    }

}

piHubDepartmentsController = function($scope, $location, convenienceMethods,$modal){
    init();
    function init(){
        $scope.doneLoadingDepartments = false;
        var url = '../../ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
        convenienceMethods.getData( url, onGetDepartemnts, onFailGetDepartments );
    }

    function onGetDepartemnts(data){
        $scope.departments = data;
        $scope.doneLoadingDepartments = true;
    }

    function onFailGetDepartments(){
        alert('There was a problem getting the list of departments');
    }

    $scope.onSelectDepartment = function($item, $model, $label){
        $scope.selectedDepartment.IsDirty = true;

        piDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.PI.Key_id,
          add: true
        }

        convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation',null, $item );
    }


    function onAddDepartment(returned,dept){
        $scope.selectedDepartment.IsDirty = false;
        if(!convenienceMethods.arrayContainsObject($scope.PI.Departments,dept))$scope.PI.Departments.push(dept);
    }

    function onFailAddDepartment(){

    }

    $scope.removeDepartment = function(department){
        department.IsDirty = true;

        piDTO = {
          Class: "RelationshipDto",
          relation_id: department.Key_id,
          master_id: $scope.PI.Key_id,
          add: false
        }

        convenienceMethods.updateObject( piDTO, department, onRemoveDepartment, onFailRemoveDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation',null, department );
    }

    function onRemoveDepartment(returned,dept){
        console.log(dept);
        dept.IsDirty = false;
        var idx = convenienceMethods.arrayContainsObject($scope.PI.Departments, dept,true)
        if(idx>-1)$scope.PI.Departments.splice(idx,1);
    }

    function onFailRemoveDepartment(){

    }


  }

  personnelModalController = function($scope, $modalInstance, convenienceMethods, piHubFactory, items){
      var pi = piHubFactory.getPI();
      $scope.userCopy = angular.copy(pi.LabPersonnel[items]);
      piHubFactory.setUser($scope.userCopy);
      $scope.userCopy.Supervisor = pi;

    $scope.saveUser = function(){
        $scope.userCopy.IsDirty = true;
        //save the user
        convenienceMethods.updateObject( $scope.userCopy, null, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );
    }

    //save call succeeded.  go back to the normal view
    function onSaveUser(user){
        $scope.userCopy.IsDirty = false;
        $scope.userCopy = angular.copy(user);
        console.log($scope.userCopy);
        piHubFactory.setUser($scope.userCopy);
        $modalInstance.close(items);
    }

    function onFailSaveUser(){
        $scope.userCopy.IsDirty = false;
        $scope.error='There was a problem when the system tried to save the user.  Please check your internet connection and try again.';
    }

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    }

  }

  var hazardDisplayModalInstanceController = function( $scope, $modalInstance, room, convenienceMethods ){

      $scope.room = room;
    //the server expects an array of roomIds, but we are only going to send one, so wrap it in an array;
    var rooms = [room.Key_id];
      var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetHazards, onFailGetHazards );

    function onGetHazards(data){
        console.log(data);
        $scope.hazards = data.ActiveSubHazards;
    }

    function onFailGetHazards(){
        $scope.hazards = false;
        $scope.noHazards = "No hazards have been selected for this room."
       }

       $scope.close = function(){
           $modalInstance.close($scope.hazards);
       }
  }

  var assignUserCtrl = function($scope, $rootScope,modalData, $modalInstance, userHubFactory, piHubFactory){
      $scope.modalData = modalData;

      $scope.gettingUsers = true;
      piHubFactory.getAllUsers()
        .then(function(users){$scope.users = users;$scope.modalError="";$scope.gettingUsers = false},function(){$scope.modalError="There was an error getting the list of users.  Please check your internet connection and try again.";})

      $scope.save = function (user, confirmed) {
          if (!confirmed) {
              $scope.selectedUser = user;
          } else {
              user = $scope.selectedUser;
          }
          if(!confirmed && !checkUserForSave(user)) return;
          if(user.Supervisor_id){
              if(!$scope.needsConfirmation){
                  $scope.selectedUser = user;
                  $scope.needsConfirmation = true;
                  return;
              }
          }

          user.Supervisor_id = modalData.Key_id
          user.Is_active = true;

          $rootScope.saving = userHubFactory.saveUser(user)
            .then(
              function(user){
                  user.new = true;
                  $modalInstance.close(user);
              }
            )
      }

      function checkUserForSave(user) {
          if (user.Is_active && !user.Supervisor) return true;
          $scope.message = user.Name + " already exists as ";
          if (!user.Is_active) {
              $scope.message = $scope.message + "an innactive ";
          }else{
              $scope.message = $scope.message + "a ";  
          }

          if (userHubFactory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT)) {
              $scope.message = $scope.message + "Lab Contact ";
          }else{
              $scope.message = $scope.message + "Lab Personnel ";
          }

          if (user.Supervisor) {
              $scope.message = $scope.message + "for " + user.Supervisor.Name;
          }

          $scope.message = $scope.message + ".  Would you like to ";

          if (!user.Is_active) {
              $scope.message = $scope.message + "activate and ";
          }

          if (user.Supervisor) {
              $scope.message = $scope.message + "re-";
          }

          $scope.message = $scope.message + "assign them to " + modalData.User.Name + "?" ;

          console.log(modalData, $scope.message);

          return false;
      }


      $scope.cancel = function(){
          $modalInstance.dismiss();
      }
  }

angular
    .module("poptop", [])
        .directive("poptop", function(){
            return {
                restrict: 'E',
                scope: {
                    content: "=",
                    label: "@",
                    title: "@",
                    wait: '@',
                    event: '@',
                    duration: '@'
                },
                template: '<span class="poptop-label"><a ng-if="label">{{label}}</a><div class="poptop" style="color:#555"><div class="poptop-title" ng-if="title">{{title}}<a class="icon-cancel-2 close pull-right" ng-if="event == \'click\' || event == \'touchstart\'"></a></div><div class="poptop-content">{{content}}</div></div></span>',
                replace:true,
                link: function (scope, element, attrs, controller) {
                    var event = scope.event || 'mouseover || mouseout';
                    //always support touch events

                    event = event + ' touchstart || touchend';

                    var wait = scope.wait || 100;
                    var duration = scope.duraction || 100;
                    var h = element.outerHeight();
                    var p = element.find(('.poptop'));

                    //position, then hide, the poptop
                    positionPopTop(element, p);
                    p.hide();
                    
                    if (event.indexOf('mouse') > -1) {
                        if (typeof element.hoverIntent == "function") {
                            element.hoverIntent(function () { $('.poptop').removeClass('popper-open'); p.toggle(duration).addClass('popper-open'); $('.poptop').not($(".popper-open")).hide(); }, function () { p.toggle(duration).removeClass('popper-open') });
                        } else {
                            element.hover(function () { $(".poptop").hide(); p.toggle(duration) }, function () { p.toggle(duration) });
                        }
                    }

                    element.on(event, function (e) { console.log('asdf'); if ($(e.target).hasClass('poptop-label') || $(e.target).parent().hasClass('poptop-label') || $(e.target).hasClass('close')) { $('.poptop').removeClass('popper-open'); p.toggle(duration).addClass('popper-open'); $('.poptop').not($(".popper-open")).hide(); } });
                    

                    function positionPopTop(e,p) {
                        window.setTimeout(function () {
                            p.css({ marginTop: -(p.height() + h + 35), marginLeft: -(p.outerWidth() / 2) + 50, position: 'absolute' });
                        }, 10);
                    }
                    //reposition if content changes
                    scope.$watch("content", function (newVal, oldVal) {
                        if (oldVal != newVal) {
                            p.show();
                            positionPopTop(element, p);
                            p.hide();
                        }
                    })

                   
                }
            }
        });
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
        obj['view' + dateProperty] = dateHolder;
        return obj;
    }

    factory.setDateForCalWidget = function (obj, dateProperty) {
        //console.log(obj);
        if (obj[dateProperty]) {
            obj['view' + dateProperty] = new Date(obj[dateProperty]);
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
            var dateSent;
            $scope.inspection.Notification_date ? dateSent = moment($scope.inspection.Notification_date) : dateSent = moment();
            var dueDate = dateSent.add(14, "days").format("MMMM Do, YYYY");
            $scope.defaultNote.Text = "We appreciate you taking the time to meet with EHS for your annual laboratory safety inspection on " + date + ". You can access the lab safety inspection report using your University username and password at the following link: http://radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection=" + id + ". \n\n"
                                 + "Please submit your lab's corrective action plan for each deficiency included in the report on or before "+ dueDate +".\n\n"
                                 + "Thank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\n\n"
                                 + "Best regards,\n"
                                 + "EHS Research Safety\n"
        }
        //all corrected
        else {
            var deficiencyCount = postInspectionFactory.getIsReadyToSubmit();
            var ending = deficiencyCount.correcteds === 1 ? "y" : "ies";
            var lingIter = ending == "y" ? "it" : "each deficiency";

            $scope.defaultNote.Text = "We appreciate you taking the time to meet with EHS for your annual laboratory safety inspection on " + date + ". During this inspection EHS identified " + deficiencyCount.correcteds +  " deficienc" + ending +", but "+ lingIter +" was appropriately corrected during the time we were conducting the inspection. No further actions are required at this time. You can access the lab inspection report using your University username and password at the following link: http://radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection=" + id + " .\n\n"
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
        if ($scope.copy && $scope.copy.Promised_date) $scope.dates.promisedDate = convenienceMethods.getDate($scope.copy.Promised_date);
        if ($scope.copy && $scope.copy.Completion_date) $scope.dates.completionDate = convenienceMethods.getDate($scope.copy.Completion_date);

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


var questionHub = angular.module('questionHub', ['convenienceMethodWithRoleBasedModule', 'once', 'ui.tinymce']);

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

    $scope.tinymceOptions = {
        plugins: 'link lists',
        toolbar: 'bold | italic | underline | link | bullist | numlist',
        menubar: false,
        elementpath: false,
        content_style: "p,ul li, ol li {font-size:14px}"
    };

    $scope.tinymceOptionsComplianceReference = {
        plugins: 'link',
        toolbar: 'link',
        menubar: false,
        elementpath: false,
        content_style: "p,ul li, ol li {font-size:14px}; html{max-height:100px}",
        max_height: 100
    };

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
                        $location.search("id",returnedQuestion.Key_id);
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

var roleBased = angular.module('roleBased', ['ui.bootstrap'])
    .directive('uiRoles', ['roleBasedFactory', function(roleBasedFactory) {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs, test) {
               console.log(scope);
               console.log(elem);
               console.log(test);
            }
         }
    }])

    .factory('roleBasedFactory', function( $q, $rootScope ){
        var factory = {};
        factory.roles = {};
        factory.U;
        //expose this factory to all views
        $rootScope.rbf = factory;

        //store the current user's permissions as an int
        factory.userPermissions = GLOBAL_SESSION_ROLES["userPermissions"];

        factory.getRoles = function(){
            if(factory.roles.length != 0){
                var i = GLOBAL_SESSION_ROLES["allRoles"].length;
                while(i--){
                    for(var prop in GLOBAL_SESSION_ROLES["allRoles"][i]){
                        factory.roles[prop] = GLOBAL_SESSION_ROLES["allRoles"][i][prop];
                    }
                }
            }
            console.log(factory.roles);
            return factory.roles;
        }

        //expose an object map of all possible roles to all the views
        $rootScope.R = factory.getRoles();
        //expose the currently logged in user to the view
        $rootScope.U = GLOBAL_SESSION_USER;
        console.log(factory.U);

        factory.getUser = function () {
            if (!factory.U) {
                factory.U = GLOBAL_SESSION_USER;
            }
            return factory.U;
        }

        factory.sumArray = function(array){
            var i = array.length;
            var total = 0;
            while(i--){
                if(typeof array[i] == "object")return;
                total += parseInt(array[i]);
            }
            return total;
        }

        factory.getHasPermission = function (elementRoles) {
            return factory.sumArray(elementRoles) & factory.userPermissions;
        }

        return factory;
    })
    .controller('roleBasedCtrl', function ($scope, $rootScope) {
    });


(function (angular) {
  'use strict';
  angular.module('scrollabletable', [])
    .directive('scrollableTable', ['$timeout', '$q', '$parse', function ($timeout, $q, $parse) {
      return {
        transclude: true,
        restrict: 'E',
        scope: {
          rows: '=watch',
          sortFn: '='
        },
        template: '<div class="scrollableContainer">' +
                    '<div class="headerSpacer"></div>' +
                    '<div class="scrollArea" ng-transclude></div>' +
                  '</div>',
        controller: ['$scope', '$element', '$attrs', function ($scope, $element, $attrs) {
           alert('asdfasdfasdf');
          // define an API for child directives to view and modify sorting parameters
          this.getSortExpr = function () {
            return $scope.sortExpr;
          };
          this.isAsc = function () {
            return $scope.asc;
          };
          this.setSortExpr = function (exp) {
            $scope.asc = true;
            $scope.sortExpr = exp;
          };
          this.toggleSort = function () {
            $scope.asc = !$scope.asc;
          };

          this.doSort = function (comparatorFn) {
            if (comparatorFn) {
              $scope.rows.sort(function (r1, r2) {
                var compared = comparatorFn(r1, r2);
                return $scope.asc ? compared : compared * -1;
              });
            } else {
              $scope.rows.sort(function (r1, r2) {
                var compared = defaultCompare(r1, r2);
                return $scope.asc ? compared : compared * -1;
              });
            }
          };

          this.renderTalble = function (){
            return waitForRender().then(fixHeaderWidths);
          };

          this.getTableElement = function (){
            return $element;
          };

          /**
           * append handle function to execute after table header resize.
           */
          this.appendTableResizingHandler = function (handler){
            var handlerSequence = $scope.headerResizeHanlers || [];
            for(var i = 0;i < handlerSequence.length;i++){
              if(handlerSequence[i].name === handler.name){
                return;
              }
            }
            handlerSequence.push(handler);
            $scope.headerResizeHanlers = handlerSequence;
          };

          function defaultCompare(row1, row2) {
            var exprParts = $scope.sortExpr.match(/(.+)\s+as\s+(.+)/);
            var scope = {};
            scope[exprParts[1]] = row1;
            var x = $parse(exprParts[2])(scope);

            scope[exprParts[1]] = row2;
            var y = $parse(exprParts[2])(scope);

            if (x === y) return 0;
            return x > y ? 1 : -1;
          }

          function scrollToRow(row) {
            var offset = $element.find(".headerSpacer").height();
            var currentScrollTop = $element.find(".scrollArea").scrollTop();
            $element.find(".scrollArea").scrollTop(currentScrollTop + row.position().top - offset);
          }

          $scope.$on('rowSelected', function (event, rowId) {
            var row = $element.find(".scrollArea table tr[row-id='" + rowId + "']");
            if (row.length === 1) {
              // Ensure that the headers have been fixed before scrolling, to ensure accurate
              // position calculations
              $q.all([waitForRender(), headersAreFixed.promise]).then(function () {
                scrollToRow(row);
              });
            }
          });

          // Set fixed widths for the table headers in case the text overflows.
          // There's no callback for when rendering is complete, so check the visibility of the table
          // periodically -- see http://stackoverflow.com/questions/11125078
          function waitForRender() {
            var deferredRender = $q.defer();
            function wait() {
              if ($element.find("table:visible").length === 0) {
                $timeout(wait, 100);
              } else {
                deferredRender.resolve();
              }
            }

            $timeout(wait);
            return deferredRender.promise;
          }

          var headersAreFixed = $q.defer();

          function fixHeaderWidths() {
            if (!$element.find("thead th .th-inner").length) {
              $element.find("thead th").wrapInner('<div class="th-inner"></div>');
            }
            if($element.find("thead th .th-inner:not(:has(.box))").length) {
              $element.find("thead th .th-inner:not(:has(.box))").wrapInner('<div class="box"></div>');
            }

            $element.find("table th .th-inner:visible").each(function (index, el) {
              el = angular.element(el);
              var width = el.parent().width(),
                lastCol = $element.find("table th:visible:last"),
                headerWidth = width;
              if (lastCol.css("text-align") !== "center") {
                var hasScrollbar = $element.find(".scrollArea").height() < $element.find("table").height();
                if (lastCol[0] == el.parent()[0] && hasScrollbar) {
                  headerWidth += $element.find(".scrollArea").width() - $element.find("tbody tr").width();
                  headerWidth = Math.max(headerWidth, width);
                }
              }
              var minWidth = _getScale(el.parent().css('min-width')),
                title = el.parent().attr("title");
              headerWidth = Math.max(minWidth, headerWidth);
              el.css("width", headerWidth);
              if (!title) {
                // ordinary column(not sortableHeader) has box child div element that contained title string.
                title = el.find(".title .ng-scope").html() || el.find(".box").html();
              }
              el.attr("title", title.trim());
            });
            headersAreFixed.resolve();
          }

          // when the data model changes, fix the header widths.  See the comments here:
          // http://docs.angularjs.org/api/ng.$timeout
          $scope.$watch('rows', function (newValue, oldValue) {
            if (newValue) {
              renderChains($element.find('.scrollArea').width());
              // clean sort status and scroll to top of table once records replaced.
              $scope.sortExpr = null;
              // FIXME what is the reason here must scroll to top? This may cause confusing if using scrolling to implement pagination.
              $element.find('.scrollArea').scrollTop(0);
            }
          });

          $scope.asc = !$attrs.hasOwnProperty("desc");
          $scope.sortAttr = $attrs.sortAttr;

          $element.find(".scrollArea").scroll(function (event) {
            $element.find("thead th .th-inner").css('margin-left', 0 - event.target.scrollLeft);
          });

          $scope.$on("renderScrollableTable", function() {
            renderChains($element.find('.scrollArea').width());
          });

          angular.element(window).on('resize', function(){
            $scope.$apply();
          });
          $scope.$watch(function(){
            return $element.find('.scrollArea').width();
          }, function(newWidth, oldWidth){
            if(newWidth * oldWidth <= 0){
              return;
            }
            renderChains();
          });

          function renderChains(){
            var resizeQueue = waitForRender().then(fixHeaderWidths),
              customHandlers = $scope.headerResizeHanlers || [];
            for(var i = 0;i < customHandlers.length;i++){
              resizeQueue = resizeQueue.then(customHandlers[i]);
            }
            return resizeQueue;
          }
        }]
      };
    }])
    .directive('sortableHeader', [function () {
      return {
        transclude: true,
        scope: true,
        require: '^scrollableTable',
        template:
          '<div class="box">' +
            '<div ng-mouseenter="enter()" ng-mouseleave="leave()">' +
              '<div class="title" ng-transclude></div>' +
              '<span class="orderWrapper">' +
                '<span class="order" ng-show="focused || isActive()" ng-click="toggleSort($event)" ng-class="{active:isActive()}">' +
                  '<i ng-show="isAscending()" class="glyphicon glyphicon-chevron-up"></i>' +
                  '<i ng-show="!isAscending()" class="glyphicon glyphicon-chevron-down"></i>' +
                '</span>' +
              '</span>' +
            '</div>' +
          '</div>',
        link: function (scope, elm, attrs, tableController) {
          var expr = attrs.on || "a as a." + attrs.col;
          scope.element = angular.element(elm);
          scope.isActive = function () {
            return tableController.getSortExpr() === expr;
          };
          scope.toggleSort = function (e) {
            if (scope.isActive()) {
              tableController.toggleSort();
            } else {
              tableController.setSortExpr(expr);
            }
            tableController.doSort(scope[attrs.comparatorFn]);
            e.preventDefault();
          };
          scope.isAscending = function () {
            if (scope.focused && !scope.isActive()) {
              return true;
            } else {
              return tableController.isAsc();
            }
          };

          scope.enter = function () {
            scope.focused = true;
          };
          scope.leave = function () {
            scope.focused = false;
          };

          scope.isLastCol = function() {
            return elm.parent().find("th:last-child").get(0) === elm.get(0);
          };
        }
      };
    }])
    .directive('resizable', ['$compile', function($compile){
      return {
        restrict: 'A',
        priority: 0,
        scope: false,
        require: 'scrollableTable',
        link: function postLink(scope, elm, attrs, tableController){
          tableController.appendTableResizingHandler(function(){
            _init();
          });

          tableController.appendTableResizingHandler(function relayoutHeaders(){
            var tableElement = tableController.getTableElement().find('.scrollArea table');
            if(tableElement.css('table-layout') === 'auto'){
              initRodPos();
            }else{
              _resetColumnsSize(tableElement.parent().width());
            }
          });

          scope.resizing = function(e){
            var screenOffset = tableController.getTableElement().find('.scrollArea').scrollLeft(),
              thInnerElm =  angular.element(e.target).parent(),
              thElm = thInnerElm.parent(),
              startPoint = _getScale(thInnerElm.css('left')) + thInnerElm.width() - screenOffset,
              movingPos = e.pageX,
              _document = angular.element(document),
              _body = angular.element('body'),
              coverPanel = angular.element('.scrollableContainer .resizing-cover'),
              scaler = angular.element('<div class="scaler">');

            _body.addClass('scrollable-resizing');
            coverPanel.addClass('active');
            angular.element('.scrollableContainer').append(scaler);
            scaler.css('left', startPoint);

            _document.bind('mousemove', function (e){
              var offsetX = e.pageX - movingPos,
                movedOffset = _getScale(scaler.css('left')) - startPoint,
                widthOfActiveCol = thElm.width(),
                nextElm = thElm.nextAll('th:visible').first(),
                minWidthOfActiveCol = _getScale(thElm.css('min-width')),
                widthOfNextColOfActive = nextElm.width(),
                minWidthOfNextColOfActive = _getScale(nextElm.css('min-width'));
              movingPos = e.pageX;
              e.preventDefault();
              if((offsetX > 0 && widthOfNextColOfActive - movedOffset <= minWidthOfNextColOfActive)
                || (offsetX < 0 && widthOfActiveCol + movedOffset <= minWidthOfActiveCol)){
                //stopping resize if user trying to extension and the active/next column already minimised.
                return;
              }
              scaler.css('left', _getScale(scaler.css('left')) + offsetX);
            });
            _document.bind('mouseup', function (e) {
              e.preventDefault();
              scaler.remove();
              _body.removeClass('scrollable-resizing');
              coverPanel.removeClass('active');
              _document.unbind('mousemove');
              _document.unbind('mouseup');

              var offsetX = _getScale(scaler.css('left')) - startPoint,
                newWidth = thElm.width(),
                minWidth = _getScale(thElm.css('min-width')),
                nextElm = thElm.nextAll('th:visible').first(),
                widthOfNextColOfActive = nextElm.width(),
                minWidthOfNextColOfActive = _getScale(nextElm.css('min-width')),
                tableElement = tableController.getTableElement().find('.scrollArea table');

              //hold original width of cells, to display cells as their original width after turn table-layout to fixed.
              if(tableElement.css('table-layout') === 'auto'){
                console.log(tableElement);
                tableElement.find("th .th-inner").each(function (index, el) {
                  el = angular.element(el);
                  var width = el.parent().width();
                  el.parent().css('width', width);
                });
              }

              tableElement.css('table-layout', 'fixed');

              if(offsetX > 0 && widthOfNextColOfActive - offsetX <= minWidthOfNextColOfActive){
                offsetX = widthOfNextColOfActive - minWidthOfNextColOfActive;
              }
              nextElm.removeAttr('style');
              newWidth += offsetX;
              thElm.css('width', Math.max(minWidth, newWidth));
              nextElm.css('width', widthOfNextColOfActive - offsetX);
              tableController.renderTalble().then(resizeHeaderWidth());
            });
          };

          function _init(){
            var thInnerElms = elm.find('table th:not(:last-child) .th-inner');
            if(thInnerElms.find('.resize-rod').length == 0){
              tableController.getTableElement().find('.scrollArea table').css('table-layout', 'auto');
              var resizeRod = angular.element('<div class="resize-rod" ng-mousedown="resizing($event)"></div>');
              thInnerElms.append($compile(resizeRod)(scope));
            }
          }

          function initRodPos(){
            var tableElement = tableController.getTableElement();
            var headerPos = 1;//  1 is the width of right border;
            tableElement.find("table th .th-inner:visible").each(function (index, el) {
              el = angular.element(el);
              var width = el.parent().width(),   //to made header consistent with its parent.
              // if it's the last header, add space for the scrollbar equivalent unless it's centered
                minWidth = _getScale(el.parent().css('min-width'));
              width = Math.max(minWidth, width);
              el.css("left", headerPos);
              headerPos += width;
            });
          }

          function resizeHeaderWidth(){
            var headerPos = 1,//  1 is the width of right border;
              tableElement = tableController.getTableElement();
            tableController.getTableElement().find("table th .th-inner:visible").each(function (index, el) {
              el = angular.element(el);
              var width = el.parent().width(),   //to made header consistent with its parent.
              // if it's the last header, add space for the scrollbar equivalent unless it's centered
                lastCol = tableElement.find("table th:visible:last"),
                minWidth = _getScale(el.parent().css('min-width'));
              width = Math.max(minWidth, width);
              //following are resize stuff, to made th-inner position correct.
              //last column's width should be automatically, to avoid horizontal scroll.
              if (lastCol[0] != el.parent()[0]){
                el.parent().css('width', width);
              }
              el.css("left", headerPos);
              headerPos += width;
            });
          }

          function _resetColumnsSize(tableWidth){
            var tableElement = tableController.getTableElement(),
              columnLength = tableElement.find("table th:visible").length,
              lastCol = tableElement.find("table th:visible:last");
            tableElement.find("table th:visible").each(function (index, el) {
              el = angular.element(el);
              if(lastCol.get(0) == el.get(0)){
                //last column's width should be automaically, to avoid horizontal scroll.
                el.css('width', 'auto');
                return;
              }
              var _width = el.data('width');
              if(/\d+%$/.test(_width)){    //percentage
                _width = Math.ceil(tableWidth * _getScale(_width) / 100);
              } else {
                // if data-width not exist, use average width for each columns.
                _width = tableWidth / columnLength;
              }
              el.css('width', _width + 'px');
            });
            tableController.renderTalble().then(resizeHeaderWidth());
          }
        }
      }
    }]);

  function _getScale(sizeCss){
    return parseInt(sizeCss.replace(/px|%/, ''), 10);
  }
})(angular);
var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once'])
.directive('tableRow', ['$window', function($window) {
    return {
      restrict : 'A',
      link : function(scope, element, attributes) {
      }
    }
 }])
.config(function($routeProvider){
  $routeProvider
    .when('/pis',
      {
        templateUrl: 'userHubPartials/pis.html',
        controller: piController
      }
    )
    .when('/contacts',
      {
        templateUrl: 'userHubPartials/contacts.html',
        controller: labContactController
      }
    )
    .when('/EHSPersonnel',
      {
        templateUrl: 'userHubPartials/EHSPersonnel.html',
        controller: personnelController
      }
    )
    .when('/labPersonnel',
      {
        templateUrl: 'userHubPartials/labPersonnel.html',
        controller: labPersonnelController
      }
    )
    .when('/uncategorized',
      {
        templateUrl: 'userHubPartials/uncategorized.html',
        controller: uncatController
      }
    )
    .otherwise(
      {
        redirectTo: '/pis'
      }
    );
})
.filter('isPI',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var pis = [];             /* more code, to make code better  */
    var i = users.length
    while(i--){
      if(userHubFactory.hasRole(users[i], Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
        if(users[i].PrincipalInvestigator){
          userHubFactory.getBuildingsByPi(users[i].PrincipalInvestigator);
          pis.unshift(users[i]);
        }else{
          users[i].isUncat = true;
        }
      }
    }
    return pis;
  }
}])
.filter('isEHSPersonnel',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length;

    while(i--){
      var shouldPush = false;
      if(userHubFactory.hasRole(users[i], Constants.ROLE.NAME.ADMIN) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.RADIATION_ADMIN) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.RADIATION_USER) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.READ_ONLY) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.OCCUPATIONAL_HEALTH)){
        shouldPush = true;
      }

      if( userHubFactory.hasRole(users[i], Constants.ROLE.NAME.SAFETY_INSPECTOR) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.RADIATION_INSPECTOR) ){
        if(users[i].Inspector){
          shouldPush = true;
        }else{
          users[i].isUncat = true;
        }
      }
      if(shouldPush)personnel.unshift(users[i]);
    }
    return personnel;
  }
}])
.filter('isNotContact',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( !userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) && userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_PERSONNEL) ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
      }
    }
    return personnel;
  }
}])
.filter('isLabContact',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
      }
    }
    return personnel;
  }
}])
.filter('isLabPersonnel',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var personnel = [];
    var i = users.length
    while(i--){
      if( userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_PERSONNEL) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) ){
        userHubFactory.getSupervisor(users[i]);
        personnel.unshift(users[i]);
        if( userHubFactory.hasRole(users[i], Constants.ROLE.NAME.LAB_CONTACT) ) users[i].isContact = true;
      }
    }
    return personnel;
  }
}])
.filter('isUncat',['userHubFactory', function(userHubFactory){
  return function(users){
    if(!users)return;
    var uncat = [];
    var i = users.length
    while (i--) {
        console.log(users[i].Inspector);
        if (!users[i].Roles || !users[i].Roles.length) {
        console.log(users[i].Name, "no roles")
        uncat.unshift(users[i]);
      }

      if(userHubFactory.hasRole(users[i], Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
          if (!users[i].PrincipalInvestigator) {
              console.log(users[i].Name, "no pi")

          uncat.unshift(users[i]);
        }
      }

      if( userHubFactory.hasRole(users[i], Constants.ROLE.NAME.RADIATION_INSPECTOR) || userHubFactory.hasRole(users[i], Constants.ROLE.NAME.SAFETY_INSPECTOR) ){
          if (!users[i].Inspector) {
              console.log(users[i].Name, "no inspector")

          uncat.unshift(users[i]);
        }
      }
    }
    return uncat;
  }
}])
.filter('tel', function () {
    return function (phoneNumber) {
        if (!phoneNumber)
            return phoneNumber;

        return formatLocal('US', phoneNumber);
    }
})
.factory('userHubFactory', function(convenienceMethods,$q, $rootScope, roleBasedFactory){

  var factory = {};
  factory.roles = [];
  factory.departments = [];
  factory.pis = [];
  factory.users = [];
  factory.labContacts = [];
  factory.personnel = [];
  factory.modalData = {};
  factory.uncategorizedUsers = [];
  factory.openedModal = false;

  factory.getSupervisor = function(user){
    var i = factory.users.length;
    while(i--){
      if(factory.users[i].PrincipalInvestigator){
          if(user.Supervisor_id == factory.users[i].PrincipalInvestigator.Key_id)user.Supervisor = factory.users[i];
      }
    }
  }

  factory.getPIs = function(){
    var pis = [];
    var i = factory.users.length;
    while(i--){
      if(factory.users[i].PrincipalInvestigator)pis.unshift(factory.users[i]);
    }
    return pis;
  }


  factory.getAllUsers = function(){
    var deferred = $q.defer();

      //lazy load
      if(factory.users.length){
        deferred.resolve(factory.users);
        return deferred.promise;
      }

      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getUsersForUserHub&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(users){
          factory.users = users;
          deferred.resolve(users);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.hasRole = function(user, role)
  {
    var j = user.Roles.length;
    while(j--){
      var userRole = user.Roles[j];
      if(userRole.Name.toLowerCase().indexOf(role.toLowerCase())>-1) return true
    }
    return false;
  }

  factory.getRelation = function(object, objIndex, foreignKey, collectionToSearch )
  {
      var i = collectionToSearch.length;
      while(i--){
        if(object[foreignKey] == collectionToSearch[i].Key_id)object[objIndex]=collectionToSearch[i];
      }
  }

  factory.getUserByPiUser_id = function(id)
  {
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].Key_id == id)return factory.users[i];
      }
  }

  factory.getUserByPIId = function(id){
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].PrincipalInvestigator && factory.users[i].PrincipalInvestigator.Key_id == id)return factory.users[i];
      }
  }

  factory.getUserId = function(id){
      var i = factory.users.length;
      while(i--){
        if(factory.users[i].Key_id == id)return factory.users[i];
      }
  }

  factory.getBuildingsByPi = function(pi)
  {
      pi.Buildings = [];
      if(!pi.Rooms || !pi.Rooms.length)return;
      var i = pi.Rooms.length;
      var buildingIds = [];

      while(i--){
          var room = pi.Rooms[i];
          if( room && buildingIds.indexOf( room.Building.Key_id ) < 0 ){
            buildingIds.push(room.Building.Key_id);
            pi.Buildings.push(room.Building);
          }
      }
  }

  factory.saveUser = function(userDto)
  {
    console.log(userDto);
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUser";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, userDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePi = function(pi)
  {
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=savePI";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, pi)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }


  factory.setModalData = function( data )
  {
    this.modalData = data;
  }

  factory.getModalData = function()
  {
    return this.modalData;
  }

  factory.getAllRoles = function()
  {
      var deferred = $q.defer();

      //lazy load
      if(factory.roles.length){
        deferred.resolve(factory.roles);
        return deferred.promise;
      }

      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(roles){
          factory.roles = roles;
          deferred.resolve(roles);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.getAllDepartments = function()
  {
      var deferred = $q.defer();

      //lazy load
      if(factory.departments.length){
        deferred.resolve(factory.departments);
        return deferred.promise;
      }

      var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getAllDepartments&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(departments){
          factory.departments = departments;
          deferred.resolve(departments);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise;
  }

  factory.saveUserRoleRelations = function(userId, rolesToAdd){
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUserRoleRelations&callback=JSON_CALLBACK&userId="+userId+'&'+$.param({roleIds:rolesToAdd});
    var deferred = $q.defer();
      convenienceMethods.getDataAsDeferredPromise(url)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePIDepartmentRelations = function(piId, departmentIds){
    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?callback=JSON_CALLBACK&action=savePIDepartmentRelations&piId="+piId+'&'+$.param({departmentIds:departmentIds});
    var deferred = $q.defer();
      convenienceMethods.getDataAsDeferredPromise(url)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.saveUserRoleRelation = function(user, role, add)
  {
    relDto = {
        Class: "RelationshipDto",
        relation_id: role.Key_id,
        master_id: user.Key_id,
        add: add
    }

    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=saveUserRoleRelation";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, relDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.savePIDepartmentRelation = function(pi, dept, add)
  {
    relDto = {
        Class: "RelationshipDto",
        relation_id: dept.Key_id,
        master_id: pi.Key_id,
        add: add
    }

    var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=savePIDepartmentRelation";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, relDto)
        .then(
          function(promise){
            deferred.resolve(promise);
          },
          function(promise){
            deferred.reject();
          }
        );
    return deferred.promise
  }

  factory.getPIByUserId = function(user_id)
  {
    var url = GLOBAL_WEB_ROOT+'ajaxaction.php?action=getPIByUserId&id='+user_id+'&callback=JSON_CALLBACK'
    return convenienceMethods.getDataAsDeferredPromise(url)
      .then(
        function(pi){
          return pi;
        },
        function(promise){
          return 'error';
        }
      )
  }

  factory.lookUpUser = function(string)
  {
        var url = GLOBAL_WEB_ROOT+"ajaxaction.php?action=lookupUser&username="+string+"&callback=JSON_CALLBACK";
        var deferred = $q.defer();
          convenienceMethods.getDataAsDeferredPromise(url)
            .then(
              function(promise){
                deferred.resolve(promise);
              },
              function(promise){
                deferred.reject();
              }
            );
        return deferred.promise
  }

  factory.placeUser = function(user, previousFlag)
  {
      var defer = $q.defer();
      var i = user.Roles.length;
      if(i==0 && factory.notInCollection(user, factory.uncategorizedUsers)){
        factory.uncategorizedUsers.push(user);
      }
      while(i--){
        if(factory.hasRole(user, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
          factory.getPIByUserId(user.Key_id)
            .then(
              function(pi){
                if(factory.notInCollection(pi, factory.pis)){
                  pi.User = user;
                  factory.pis.push(pi);
                }
              }
            )
        }
        if(factory.hasRole(user, Constants.ROLE.NAME.ADMIN) || factory.hasRole(user, Constants.ROLE.NAME.RADIATION_INSPECTOR) || factory.hasRole(user, Constants.ROLE.NAME.SAFETY_INSPECTOR) || factory.hasRole(user, Constants.ROLE.NAME.RADIATION_ADMIN) || factory.hasRole(user, Constants.ROLE.NAME.RADIATION_USER) && factory.notInCollection(user, factory.personnel)) {
            factory.personnel.push(user);
        }
        if(factory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT) && factory.notInCollection(user, factory.labContacts)) {
            factory.labContacts.push(user);
        }
      }
  }

  factory.removeUserFromCollections = function(user)
  {
      var defer = $q.defer();
      var i = user.Roles.length;

      if(i!=0 && factory.notInCollection(user, factory.uncategorizedUsers)){
        var j = factory.uncategorizedUsers.length;
        while(j--){
          if(factory.uncategorizedUsers[j].Key_id == user.Key_id)factory.uncategorizedUsers.splice(j,1);
        }
      }

      while(i--){
        if(!factory.hasRole(user, Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)){
          factory.getPIByUserId(user.Key_id)
            .then(
              function(pi){
                //find pi in pis collection and remove
                var j = factory.pis.length;
                while(j--){
                  if(factory.pis[j].Key_id == pi.Key_id)factory.pis.splice(j,1);
                }
              }
            )
        }
        if(!factory.hasRole(user, Constants.ROLE.NAME.ADMIN) && !factory.hasRole(user, Constants.ROLE.NAME.RADIATION_INSPECTOR) || !factory.hasRole(user, Constants.ROLE.NAME.SAFETY_INSPECTOR) && !factory.hasRole(user, Constants.ROLE.NAME.RADIATION_ADMIN) && !factory.hasRole(user, Constants.ROLE.NAME.RADIATION_USER) && !factory.notInCollection(user, factory.personnel)){
            //find user in admin and remove
            var j = factory.personnel.length;
            while(j--){
              if (factory.personnel[j].Key_id == user.Key_id)factory.personnel.splice(j,1);
            }
        }
        if(!factory.hasRole(user, Constants.ROLE.NAME.LAB_CONTACT) && !factory.notInCollection(user, factory.labContacts)){
            //find user in contacts and remove
            //find user in admin and remove
            var j = factory.labContacts.length;
            while(j--){
              if (factory.labContacts[j].Key_id == user.Key_id)factory.labContacts.splice(j,1);
            }
        }
      }
  }

  factory.notInCollection = function(object, collection)
  {
      var i = collection.length;
      while(i--){
        if(collection[i].Key_id == object.Key_id)return false;
      }
      return true;
  }

  factory.iterate = function(num){
    return num+1;
  }

  return factory

});

var MainUserListController = function(userHubFactory, $scope, $rootScope, $location, convenienceMethods, $route) {
    $rootScope.uhf=userHubFactory;
    $rootScope.order = 'Last_name';

    //----------------------------------------------------------------------
    //
    // ROUTING
    //
    //----------------------------------------------------------------------
    $scope.setRoute = function(){
      $location.path($scope.selectedRoute);
    }

    if(!$location.path()) {
      // by default pis are loaded, so set path to this, and update selectedRoute accordingly
      $location.path("/pis");
    }
    if(!$scope.selectedRoute)$scope.selectedRoute = $location.path();

    $rootScope.showInactive = false;

    $rootScope.handleUserActive = function(user){
      $rootScope.error = '';
      user.IsDirty = true;
      var userCopy = convenienceMethods.copyObject(user);
      userCopy.Is_active = !userCopy.Is_active;
      userHubFactory.saveUser(userCopy)
        .then(
          function(returnedUser){
            user.Is_active = !user.Is_active;
            user.IsDirty = false;
          },
          function(){
            user.IsDirty = false;
            $rootScope.error = 'The user could not be saved.  Please check your internet connection and try again.'
          }
        )
    }

    userHubFactory.getAllRoles()
      .then(
        function(roles){
          return roles;
        },
        function(){
          $rootScope.error = 'The system could not retrieve the list of roles.  Please check your internet connection and try again.'
        }
      )
    userHubFactory.getAllDepartments()
      .then(
        function(departments){
          return departments;
        },
        function(){
          $rootScope.error = 'The system could not retrieve the list of roles.  Please check your internet connection and try again.'
        }
      )

    $scope.activeFilter = function(showInactive, pis){
      return function(obj) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!pis && obj.Is_active != showInactive)show = true;
        if(pis && obj.PrincipalInvestigator && obj.PrincipalInvestigator.Is_active != showInactive){
          show = true;
        }
        return show;
    }
  }


}

var piController = function($scope, $modal, userHubFactory, $rootScope, convenienceMethods, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $rootScope.renderDone = false;
    userHubFactory.getAllUsers()
      .then(
          function(users){
            $scope.pis = userHubFactory.users;
            $rootScope.neededUsers = true;
            if($location.search().pi && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserByPIId($location.search().pi));
            }
            $rootScope.renderDone = true;
            return users;
          },
          function(){
            $rootScope.error="There was a problem getting the list of Principal Investigators.  Please check your internet connection and try again."
          }
        )

    $scope.openModal = function(pi){
        if(!pi){
          pi = {Is_active: true, Is_new:true, Class:'User', Roles:[], PrincipalInvestigator:{Is_active:true, Departments:[], Class:'PrincipalInvestigator'}};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf(Constants.ROLE.NAME.PRINCIPAL_INVESTIGATOR)>-1) pi.Roles.push(userHubFactory.roles[i]);
          }
        }
        userHubFactory.setModalData(pi);

        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/piModal.html',
          controller: modalCtrl
        });


        modalInstance.result.then(function (returnedPi) {
          if(pi.Key_id){
            console.log(returnedPi)
            angular.extend(pi, returnedPi);
          }else{
            userHubFactory.users.push(returnedPi);
          }
        });

    }

  $scope.departmentFilter = function() {
    if(!$scope.search)$scope.search = {};
    return function(user) {
        var show = false;
        //for pis that don't have departments, don't filter them unless the filter has some text
        if(!user.PrincipalInvestigator.Departments)user.PrincipalInvestigator.Departments = [];
        if(!user.PrincipalInvestigator.Departments.length){
          if(typeof $scope.search.selectedDepartment == 'undefined' || $scope.search.selectedDepartment.length == 0){
            show = true;
          }
        }

        angular.forEach(user.PrincipalInvestigator.Departments, function(department, key){
          if(typeof $scope.search.selectedDepartment == 'undefined'|| department.Name.toLowerCase().indexOf($scope.search.selectedDepartment.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.buildingFilter = function() {
    if(!$scope.search)$scope.search = {};

    return function(user) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!user.PrincipalInvestigator.Buildings)pi.Buildings = [];
        if(!user.PrincipalInvestigator.Buildings.length){
          if(typeof $scope.search.selectedBuilding == 'undefined' || $scope.search.selectedBuilding.length == 0){
            show = true;
          }
        }
        angular.forEach(user.PrincipalInvestigator.Buildings, function(building, key){
          if(typeof $scope.search.selectedBuilding == 'undefined' || building.Name.toLowerCase().indexOf($scope.search.selectedBuilding.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.handlePiActive = function(pi){
      $rootScope.error = '';
      pi.IsDirty = true;
      var piCopy = convenienceMethods.copyObject(pi);
      piCopy.Is_active = !pi.Is_active;
      userHubFactory.savePi(piCopy)
        .then(
          function(returnedPi){
            pi.Is_active = !pi.Is_active;
            pi.IsDirty = false;
          },
          function(){
            pi.IsDirty = false;
            $rootScope.error = 'The Principal Investigator could not be saved.  Please check your internet connection and try again.'
          }
        )
  }

  $scope.order = 'Last_name';

}

var labContactController = function($scope, $modal, $rootScope, userHubFactory, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $scope.order = 'Last_name';
    $rootScope.renderDone = false;

    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.LabContacts = userHubFactory.users;
          $rootScope.neededUsers = true;
            if($location.search().contactId && $location.search().piId && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserId($location.search().contactId), $location.search().piId);
            }
          $rootScope.renderDone = true;
        }
      )

    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf(Constants.ROLE.NAME.LAB_CONTACT)>-1) user.Roles.push(userHubFactory.roles[i]);
          }
        }
        if(!user.Supervisor_id){
          user.Supervisor_id = piId;
          user.Supervisor = userHubFactory.getUserByPIId($location.search().piId);
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/labContactModal.html',
          controller: modalCtrl
        });
        modalInstance.result.then(function (returnedUser) {
          if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var personnelController = function($scope, $modal, $rootScope, userHubFactory, convenienceMethods, $timeout, $location) {
    $rootScope.neededUsers = false;
    $rootScope.order="Last_name";
    $rootScope.error="";
    $rootScope.renderDone = false;

    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.Admins = userHubFactory.users;
          $rootScope.neededUsers = true;
          $timeout(function() {
                $rootScope.renderDone = true;
            }, 300);
        },
        function(){
          $rootScope.error="There was problem getting the lab contacts.  Please check your internet connection and try again.";
        }
      )


    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/personnelModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
         if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var labPersonnelController = function($scope, $modal, $rootScope, userHubFactory, $location) {
    $rootScope.neededUsers = false;
    $rootScope.error="";
    $scope.order = 'Last_name';
    $rootScope.renderDone = false;


    userHubFactory.getAllUsers()
      .then(
        function(users){
          $scope.LabPersonnel = userHubFactory.users;
          $rootScope.neededUsers = true;
            if($location.search().personnelId && $location.search().piId && !userHubFactory.openedModal){
              userHubFactory.openedModal = true;
              $scope.openModal(userHubFactory.getUserId($location.search().personnelId), $location.search().piId);
            }
          $rootScope.renderDone = true;
        }
      )

    $scope.openModal = function(user,piId){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
          var i = userHubFactory.roles.length;
          while(i--){
            if(userHubFactory.roles[i].Name.indexOf(Constants.ROLE.NAME.LAB_PERSONNEL)>-1) user.Roles.push(userHubFactory.roles[i]);
          }
        }
        if(!user.Supervisor_id){
          user.Supervisor_id = piId;
          user.Supervisor = userHubFactory.getUserByPIId($location.search().piId);
        }
        userHubFactory.setModalData(user);
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/labPersonnelModal.html',
          controller: modalCtrl
        });
        modalInstance.result.then(function (returnedUser) {
          if(user.Key_id){
            angular.extend(user, returnedUser)
          }else{
            userHubFactory.users.push(returnedUser);
          }
        });

    }
}

var uncatController = function($scope, $modal, $rootScope, userHubFactory, convenienceMethods) {
    $rootScope.order="Last_name";
    $rootScope.neededUsers = false;
    $rootScope.error="";

    var getUncategorizedUsers = function(){
      return userHubFactory.getUncategorizedUsers()
        .then(
          function(users){
            console.log(users);
            $scope.user = userHubFactory.users;
            $rootScope.neededUsers = true;
          }
        )
    }

    userHubFactory.getAllUsers()
      .then(function(users){
        $rootScope.neededUsers = true;
        $scope.users = users;
      });


    $scope.openModal = function(user,$index){
        if(!user){
          user = {Is_active:true, Roles:[], Class:'User', Is_new:true};
        }
        userHubFactory.setModalData(user);
        user.Is_incategorized = true;
        var modalInstance = $modal.open({
          templateUrl: 'userHubPartials/personnelModal.html',
          controller: modalCtrl
        });

        modalInstance.result.then(function (returnedUser) {
          console.log(returnedUser);
          angular.extend(user, returnedUser);
        });
    }
}
modalCtrl = function($scope, userHubFactory, $modalInstance, convenienceMethods, $q, $location){

    if($location.$$host.indexOf('graysail')<0){
      $scope.isProductionServer = true;
    }else{
      $scope.isProductionServer = false;
    }

    $scope.modalError="";
    //make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
    $scope.modalData = convenienceMethods.copyObject( userHubFactory.getModalData() );
    $scope.order="Last_name";
    $scope.phoneNumberPattern = /^\(?\d{3}\)?[- ]?\d{3}[- ]?\d{4}$/;
    $scope.phoneNumberErrorMsg = "E.G. 123-555-5555 or (123) 555-5555";
    $scope.emailPattern = /^[_a-z0-9]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i;
    $scope.emailErrorMsg = "Invalid email address";
    $scope.pis = userHubFactory.getPIs();

    userHubFactory.getAllRoles()
      .then(
        function(roles){
          $scope.roles = roles;
        }
      )
    userHubFactory.getAllDepartments()
      .then(
        function(departments){
          $scope.departments = departments;
            //if the user has a department, set the selected Department for ui-select elements to the matching index of $scope.departments
            if($scope.modalData.Primary_department){
                var i = $scope.departments.length;
                while(i--){
                    if($scope.departments[i].Key_id === $scope.modalData.Primary_department.Key_id){
                        $scope.departmentIdx = i;
                        break;
                    }
                }
            }
        }
      )

    //if the user has a supervisor, set the selected PI for ui-select elements to the matching index of $scope.pis
    if($scope.modalData.Supervisor_id){
        var i = $scope.pis.length;
        while(i--){
            if($scope.pis[i].PrincipalInvestigator.Key_id === $scope.modalData.Supervisor_id){
                $scope.piIndex = i;
                break;
            }
        }
    }





    $scope.cancel = function () {
        $modalInstance.dismiss();
    };

    $scope.savePi = function(){
      $scope.modalData.IsDirty=true;
      $scope.modalError=""
      console.log($scope.modalData)
      var userDto = $scope.modalData;

      saveUser( userDto )
        .then(saveRoles)
        .then(savePiDepartmentRelations)
        .then(closeModal)
    }

    $scope.onSelectRole = function(role, $model, $label, id){
      $scope.modalError=""
      //console.log('we are in the role branch');
      if(userHubFactory.getModalData().Class=="PrincipalInvestigator"){
          var user = $scope.modalData.User;
      }else{
          var user = $scope.modalData;
      }
      if(userHubFactory.getModalData().Key_id){
          userHubFactory.saveUserRoleRelation(user, role, true)
          .then(
              function(){
                user.Roles.push(role);
                //all lab contacts are also lab personnel.  Server side application logic automatically adds the role, but saveUserRoleRelation on the server only returns a boolean, so we add here as well
                if(role.Name == Constants.ROLE.NAME.LAB_CONTACT){
                    var i = userHubFactory.roles.length;
                    while(i--){
                        if(userHubFactory.roles[i].Name.indexOf(Constants.ROLE.NAME.LAB_PERSONNEL)>-1) user.Roles.push(userHubFactory.roles[i]);
                    }
                }
                if(user.Is_incategorized){
                  userHubFactory.placeUser(user);
                }
                $model.IsDirty=false;
              },
              function(){
                $scope.modalError = 'The role could not be added.  Please check your internet connection and try again.'
              }
            )
       }

       //we don't have a user, because we are creating a new one.  cache the roles for save on callback when the user is saved.
       else{
          if($model)$model.IsDirty = false;
          if(!user.Roles)user.Roles = [];
          user.Roles.push(role);
       }
    }

    $scope.saveUser = function(){
        $scope.modalData.IsDirty = true;

        if($scope.isPIRequired($scope.modalData) && !$scope.modalData.Supervisor){
            $scope.modalError = 'A Lab Contact must be assigned to a Principal Investigator.';
            $scope.modalData.IsDirty = false;
            return;
        }
        var user = $scope.modalData;

        $scope.modalError="";
        saveUser( user )
                .then(saveRoles)
                .then(closeModal)
    }

    $scope.onAddDepartmentToPi = function(department){
        console.log(department);
        $scope.modalError=""
        var deptToAdd = convenienceMethods.copyObject(department);
        $scope.modalData.PrincipalInvestigator.Departments.push(deptToAdd);
        if($scope.modalData.Key_id){
          deptToAdd.IsDirty=true;
          userHubFactory.savePIDepartmentRelation($scope.modalData.PrincipalInvestigator, deptToAdd, true)
            .then(
              function(){
                deptToAdd.IsDirty=false;
                userHubFactory.setModalData($scope.modalData.PrincipalInvestigator);
              },
              function(){
                deptToAdd.IsDirty=false;
                var i = $scope.modalData.PrincipalInvestigator.Departments;
                while(i--){
                  if($scope.modalData.PrincipalInvestigator.Departments[i].Key_id == deptToAdd.Key_id)$scope.modalData.PrincipalInvestigator.Departments(i,1);
                }
                $scope.modalError="The department could not be added to the Principal Investigator.  Please check your internet connection and try again."
              }
            )
        }
    }

    $scope.removeDepartment = function(department){
        $scope.modalError="";
        var pi = $scope.modalData;
        var i = pi.PrincipalInvestigator.Departments.length;
        console.log(pi);
        if(!pi.PrincipalInvestigator.Key_id){
          while(i--){
            if(pi.PrincipalInvestigator.Departments[i].Key_id == department.Key_id)pi.PrincipalInvestigator.Departments.splice(i,1);
          }
        }else{
          department.IsDirty = true;
          userHubFactory.savePIDepartmentRelation(pi.PrincipalInvestigator, department, false)
            .then(
              function(){
                  department.IsDirty = false;
                  while(i--){
                    if(pi.PrincipalInvestigator.Departments[i].Key_id == department.Key_id)pi.PrincipalInvestigator.Departments.splice(i,1);
                  }
              },
              function(){
                department.IsDirty = false;
                $scope.modalError = "The department could not be removed.  Please check your internet connection and try again.";
              }
            )
        }
    }

    $scope.removeRole = function(user, role){
        $scope.modalError="";
        var i = user.Roles.length;
        if(!user.Key_id){
          while(i--){
            if(user.Roles[i].Key_id == role.Key_id)user.Roles.splice(i,1);
          }
        }else{
          role.IsDirty = true;
          userHubFactory.saveUserRoleRelation(user, role, false)
            .then(
              function(){
                  role.IsDirty = false;
                  while(i--){
                    if(user.Roles[i].Key_id == role.Key_id)user.Roles.splice(i,1);
                  }
              },
              function(){
                role.IsDirty = false;
                $scope.modalError = "The role could not be removed.  Please check your internet connection and try again.";
              }
            )
        }
    }

    $scope.isPIRequired = function(user) {
        for (var i = 0; i < user.Roles.length; i++) {
            if (user.Roles[i].Name == Constants.ROLE.NAME.LAB_CONTACT || user.Roles[i].Name == Constants.ROLE.NAME.LAB_PERSONNEL) {
                return true;
            }
        }
        return false;
    }

    $scope.onSelectPI = function(pi,user){
      $scope.modalData.Supervisor = pi;
      $scope.modalData.Supervisor_id = pi.PrincipalInvestigator.Key_id;
    }

    $scope.onSelectDepartment = function(dept,user){
      $scope.modalData.Primary_department_id = dept.Key_id;
      $scope.modalData.Primary_department = dept;
    }
    $scope.getAuthUser = function(user){
     $scope.lookingForUser = true;
     $scope.modalError = false;
     var i = userHubFactory.users.length;
      while(i--){
        if( userHubFactory.users[i].Username && $scope.modalData.userNameForQuery.toLowerCase() == userHubFactory.users[i].Username.toLowerCase()){
          $scope.modalError='The username '+$scope.modalData.userNameForQuery+' is already taken by another user in the system.';
          return;
        }
      }
      userHubFactory.lookUpUser($scope.modalData.userNameForQuery)
        .then(
          function(returnedUser){
            if(returnedUser==null){
               $scope.modalError='No user with that username was found.';
               $scope.lookingForUser = false;
               return;
            }
            $scope.lookingForUser = false;
            if($scope.modalData.Class=="PrincipalInvestigator"){
              $scope.modalData.User = returnedUser;
              $scope.modalData.User.Roles = user.Roles;
              console.log(returnedUser);
            }else{
              $scope.modalData=returnedUser;
              $scope.modalData.Roles = user.Roles;
              if(user.PrincipalInvestigator)$scope.modalData.PrincipalInvestigator = user.PrincipalInvestigator;
              if(user.Inspector)$scope.modalData.Inspector = user.Inspector;
            }
          },
          function(){
            $scope.lookingForUser = false;
            $scope.modalError='There was a problem querying for the user.  Please check your internet connection and try again.';
          }
        )

    }

    function saveUser( userDto )
    {
        return userHubFactory.saveUser( userDto )
          .then(
            function( returnedUser ){
              console.log(returnedUser);
              returnedUser.Roles = userDto.Roles;
              if(userDto.PrincipalInvestigator && returnedUser.PrincipalInvestigator){
                returnedUser.PrincipalInvestigator.Departments = userDto.PrincipalInvestigator.Departments;
                returnedUser.PrincipalInvestigator.Rooms = userDto.PrincipalInvestigator.Rooms;
              }
              return returnedUser;
            },
            function(){
              $scope.modalError="The user could not be saved.  Please check your internet connection and try again."
            }
          )
    }

    function getPiByUser( user ){
        console.log('getting pi')
        var defer = $q.defer();
        if($scope.modalData.Class=="PrincipalInvestigator"){
          if($scope.modalData.Is_new){
            return userHubFactory.getPIByUserId(user.Key_id)
              .then(
                function(returnedPi){
                 return returnedPi;
                },
                function(){
                  $scope.modalError = "The system couldn't get the Principal Investigator record.  Please check your internet connection and try again.";
                }
              )
          }else{
            $scope.modalData.User = user;
            defer.resolve( $scope.modalData );
            return defer.promise
          }
        }
        else{
          $defer.resolve(user);
          return defer.promise;
        }
    }

    function saveRoles( user ){
      console.log(user);
      var userCopy, oldRoles
      var oldRoleIds = [];
      var idsToAdd = [];

      userCopy = userHubFactory.getModalData();
      oldRoles = $scope.modalData.Roles;


      if(!userHubFactory.getModalData().Is_new){
        //get the ids of the roles the user already had, if the user is not new
        var i = oldRoles.length;
        while(i--){
          oldRoleIds.push(oldRoles[i].Key_id);
        }

      }
      //get the ids of the roles to add
      var j = user.Roles.length;
      while(j--){
        if(oldRoleIds.indexOf(user.Roles[j].Key_id)<0)idsToAdd.push(user.Roles[j].Key_id);
      }
      console.log(idsToAdd);

      if(!idsToAdd.length){
        var defer = $q.defer();
        defer.resolve(user);
        return defer.promise;
      }

      return userHubFactory.saveUserRoleRelations(user.Key_id, idsToAdd)
        .then(
          function(){
            console.log(user);
            return user;
          },
          function(){
            $scope.modalError = 'The user was saved, but there was a problem adding one or more of the roles.  Please check your internet connection and try again.'
          }
        )


    }

    function savePiDepartmentRelations(pi){
      console.log('saving dept relations')
      //save deparments added to pi
      var oldDepartments = [];
      var newDepartmentIds = [];
      var piCopy = convenienceMethods.copyObject(pi);
      console.log(pi)
      var i = piCopy.PrincipalInvestigator.Departments.length;
      while(i--){
        oldDepartments.push(piCopy.PrincipalInvestigator.Departments[i].Key_id);
      }

      var j = pi.PrincipalInvestigator.Departments.length;
      while(j--){
        if(oldDepartments.indexOf(pi.PrincipalInvestigator.Departments[j].Key_id)<0)newDepartmentIds.push(pi.PrincipalInvestigator.Departments[j].Key_id)
      }
      console.log(newDepartmentIds);
      if(!newDepartmentIds.length){
        var defer = $q.defer();
        defer.resolve(pi);
        return defer.promise;
      }else{
        return userHubFactory.savePIDepartmentRelations(pi.PrincipalInvestigator.Key_id, newDepartmentIds)
          .then(
            function(){
              return pi;
            },
            function(){
              $scope.modalError = 'The PI was saved, but there was a problem adding one or more of the departments.  Please check your internet connection and try again.'
            }
          )
      }
    }

    function closeModal( dataToReturn ){
        console.log(dataToReturn);
        $scope.modalData.IsDirty = false;
        $modalInstance.close(dataToReturn);
    }

}

///////////to do: develop a local factory to share data between views

var userList = angular.module('userList', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once'])


.config(function($routeProvider){
  $routeProvider
    .when('/pis',
      {
        templateUrl: 'userHubPartials/pis.html',
        controller: piController
      }
    )
    .when('/contacts',
      {
        templateUrl: 'userHubPartials/contacts.html',
        controller: labContactController
      }
    )
    .when('/EHSPersonnel',
      {
        templateUrl: 'userHubPartials/EHSPersonnel.html',
        controller: personnelController
      }
    )
    .otherwise(
      {
        redirectTo: '/pis'
      }
    );
})
.factory('userHubFactory', function(convenienceMethods,$q){

  var factory = {};
  var allPis = [];
  var pis = [];

  factory.setPIs = function(pis){
    this.pis = pis;
  }

  factory.getPIs = function(){
    return this.pis;
  }

  factory.getAllPis = function(){

    //if we don't have a the list of pis, get it from the server
    var deferred = $q.defer();

    //lazy load
    if(this.allPis){
      deferred.resolve(this.allPis);
      return deferred.promise;
    }

    var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
      convenienceMethods.getDataAsDeferredPromise(url).then(
      function(promise){
        deferred.resolve(promise);
      },
      function(promise){
        deferred.reject();
      }
    );
    return deferred.promise;
  }

  return factory

});
//called on page load, gets initial user data to list users
var MainUserListController = function(userHubFactory,$scope,$rootScope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route,$window,userHubFactory) {
  $scope.showInactive = false;
  $scope.users = [];
  $scope.order='Last_name';

  init();

  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  function init(){

    convenienceMethods.getData('../../ajaxaction.php?action=getAllPIs&rooms=true&callback=JSON_CALLBACK',onGetPis,onFailGetPis);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllRoles&callback=JSON_CALLBACK',onGetRoles,onFailGetRoles);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllUsers&callback=JSON_CALLBACK',onGetUsers,onFailGetUsers);
    convenienceMethods.getData('../../ajaxaction.php?action=getAllActiveDepartments&callback=JSON_CALLBACK',onGetDepartments,onFailGetDepartments);

    // sometimes $location.path() isn't set yet, so check for this
    if(!$location.path()) {
      // by default pis are loaded, so set path to this, and update selectedRoute accordingly
      $location.path("/pis");
    }
    if(!$scope.selectedRoute)$scope.selectedRoute = $location.path();
  }

  function onGetDepartments(data){
    $scope.departments = data;
  }

  function onFailGetDepartments(data){
    alert("Something went wrong when the system tried to get the list of departments");
  }

  function onGetRoles(data){
    $scope.roles = data;
  }

  function onFailGetRoles(){

  }

  //grab set user list data into the $scrope object
  function onGetUsers(data) {
    //console.log(data);
    $scope.users = data;
  }

  function onFailGetUsers(){
    alert('Something went wrong when we tried to build the list of users.');
  }

  function onFailGetPIs(){
    alert('Something went wrong when we tried to build the list of Principal Investigators.');
  }

  function onGetPis(data){
     userHubFactory.setPIs(data);
     angular.forEach(data, function(pi, key){
      pi.Buildings = [];
      angular.forEach(pi.Rooms, function(room, key){
       if(room&&!convenienceMethods.arrayContainsObject(pi.Buildings, room.Building))pi.Buildings.push(room.Building);
      });
    });

    $rootScope.pis = data;
    console.log($scope.pis);
    //do this only if we have not yet looped through our users, otherwise we will append the list of users to itself when we switch routes
    if(!$scope.run){
      $scope.setUsers();
    }else{
      alert('already run');
    }
    $scope.run = true;
  }

  function onFailGetPis(){
    alert('Something went wrong when the system tried to get the list of all Principal Investigators.')
  }

  //fix up scope user collections
  $scope.setUsers = function(){
    //push users into correct arrays based on role
    angular.forEach($scope.users, function(user, key){
      user.userTypes = convenienceMethods.getUserTypes(user);
     // //console.log(user);
      $scope.putUserInRightPlace(user);
    });
    ////console.log($scope.LabContacts);
  }

  $scope.putUserInRightPlace = function(user){

     if(user.Class == 'PrincipalInvestigator'){
        if(!convenienceMethods.arrayContainsObject($scope.PIs, user)){
          $scope.PIs.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.PIs, user, null, true);
          $scope.PIs[idx] = angular.copy(user);
        }

        $scope.putUserInRightPlace(user.User);
     }

     if(!user.userTypes)user.userTypes = convenienceMethods.getUserTypes(user);
     if(!$scope.LabContacts)$scope.LabContacts = [];
      if(user.userTypes.indexOf(Constants.ROLE.NAME.LAB_CONTACT) > -1){

        if(!convenienceMethods.arrayContainsObject($scope.LabContacts, user)){
          $scope.LabContacts.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.LabContacts, user, null, true);
          $scope.LabContacts[idx] = angular.copy(user);
        }


        //lab contacts have supervising pi's, but the user object only comes with a key_id for the supervising pi, so we find the right pi
        angular.forEach($scope.pis, function(pi, key){
           pi.User.userTypes = convenienceMethods.getUserTypes( pi.User);
           if(user.Supervisor_id == pi.Key_id){
             user.Supervisor = {};
             user.Supervisor.User = {};
             user.Supervisor.User.Name = pi.User.Name;
              user.Supervisor.User.Lab_phone = pi.User.Lab_phone;
             user.Supervisor.Key_id = pi.Key_id;
           }
        });
      }

      if(!$scope.Admins)$scope.Admins = [];
      if(user.userTypes.indexOf(Constants.ROLE.NAME.ADMIN) > -1 || user.userTypes.indexOf(Constants.ROLE.NAME.RADIATION_INSPECTOR > -1 || user.userTypes.indexOf(Constants.ROLE.NAME.SAFETY_INSPECTOR) > -1){
        if(!convenienceMethods.arrayContainsObject($scope.Admins, user)){
          $scope.Admins.push(user);
        }else{
          var idx = convenienceMethods.arrayContainsObject($scope.Admins, user, null, true);
          $scope.Admins[idx] = angular.copy(user);
        }
      }
  }

  //----------------------------------------------------------------------
  //
  // ROUTING
  //
  //----------------------------------------------------------------------
  $scope.setRoute = function(){
    $location.path($scope.selectedRoute);
  }

};

var labContactController = function(userHubFactory, $scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route) {

  //look at GET parameters to determine if we should alter the view accordingly
  //if we have linked to this view from the PI hub to manage a PI's lab personnel, filter the view to only those PI's associated with th

  if($location.search().piId){
    $scope.piId = $location.search().piId;
  }

  if($location.$$host.indexOf('graysail')<0)$scope.isProductionServer = true;


  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/contacts';

  $scope.addUser = function (user) {
    $scope.items = [];
    if(user){
      //we are editing a user that already exists
      var userCopy = angular.copy(user);
    }else{
      //we are creating a new user
      var userCopy = {}
      userCopy.Class = "User";
      userCopy.Roles = [];
      userCopy.Roles.push($scope.roles[4]);
    }

    $scope.items.push(userCopy);
    $scope.items.push(user);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'labContactModal.html',
      controller: labContactModalInstanceController,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
       selectedItem.IsDirty = false;
       //console.log(selectedItem);
       convenienceMethods.getUserTypes(selectedItem);
       $scope.putUserInRightPlace(selectedItem);
    });
  };

  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    //console.log(user);
    var userCopy = angular.copy(user);
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(userCopy.Is_active == false){
      userCopy.Is_active = true;
    }else{
      userCopy.Is_active = false;
    }
    convenienceMethods.updateObject( userCopy, user, onSetUserActive, onFailSetUserActive, '../../ajaxaction.php?action=saveUser' );
  }

  function onSetUserActive(returned, old){
    //console.log(returned);
    old.IsDirty = false;
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(returned.Is_active == 0){
      returned.Is_active = false;
    }else{
      returned.Is_active = true;
    }
    old.Is_active = returned.Is_active;
    //console.log(old);
  }

  function onFailSetUserActive(){
    alert("The user could not be saved");
  }

  $scope.deactiveUser = function(user){
    $scope.handleUserActive(user);
    var userCopy = angular.user(user);
    userCopy.Is_active = false;
    convenienceMethods.updateObject (userCopy, user, onDeactivateUser, onFailDeactivateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onDeactivateUser(userDTO,user){
    idx = convenienceMethods.arrayContainsObject(user, $scope.PI.LabPersonnel, null, true);
    $scope.PI.LabPersonnel.splice(idx, 1);
  }

  function onFailDeactivateUser(){
    $scope.error = 'There was a problem when the system tried to deactivate the user.  Check your internet connection.'
  }
}


//controller for modal instance for lab contacts
var labContactModalInstanceController = function ($scope, $modalInstance, items, $rootScope,convenienceMethods, $location, $window, userHubFactory) {
  if($location.$$host.indexOf('graysail')<0)$scope.isProductionServer = true;

  $scope.failFindUser = false;
  //console.log(items);

  $scope.getAuthUser = function(){
    //console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.userCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    //console.log(data);
    if(!data.Roles)data.Roles = [];
    angular.forEach($scope.userCopy.Roles, function(role, key){
      data.Roles.push(role);
    });
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    //console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  //$location.path('/contacts');

  $scope.userCopy = items[0];
  if(items[1])$scope.user = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  if($location.search().piId){
    $scope.piId = $location.search().piId;
    $scope.pis = userHubFactory.getPIs();

    if(!$scope.userCopy.Supervisor){
      var piLen = $scope.pis.length;
      for(i=0;i<piLen;i++){
        if($location.search().piId === $scope.pis[i].Key_id){
          $scope.userCopy.Supervisor = $scope.pis[i];
        }
      }
    }
  }

  $scope.saveUser = function (userCopy, user) {
    //console.log(userCopy);
    var roles;
    userCopy.Is_active = true;
    userCopy.IsDirty = true;
    if(userCopy.Primary_department)userDTO.Primary_department_id = userCopy.Primary_department.Key_id;
    if(userCopy.Supervisor)userDTO.Supervisor_id = userCopy.Supervisor.Key_id;

    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    //console.log(userCopy);
    if(!userCopy.Key_id)userCopy.Is_active = true;
    userCopy.Supervisor = {};
    convenienceMethods.updateObject( userCopy, user, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    $scope.userCopy.Key_id = data.Key_id;
    //console.log( data );
    //console.log( $scope.userCopy);
    var rolesToAdd = $scope.userCopy.Roles;
    //see if we have new roles, but only if the user is not new, in which case all roles are new
    if($scope.user){
      //console.log('right here');
      var rolesToAdd = [];
      angular.forEach($scope.userCopy.Roles, function(role, key){
        if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
      });
    }
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });

    if($scope.userCopy.isPI && !convenienceMethods.arrayContainsObject($scope.pis,userCopy)){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }
/*
    if(userCopy.userTypes.indexOf(Constants.ROLE.NAME.SAFETY_INSPECTOR > -1)){
      var inspectorDTO = {
        Class: "Inspector",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( inspectorDTO, userCopy.Departments, onSaveNewInspector, onFailSaveNewInspector, '../../ajaxaction.php?action=saveInspector');
    }
    */


    $modalInstance.close($scope.userCopy);
  }

  $scope.onSelectPI = function($item, $model, $label){
    //console.log($item);
    //console.log($model);
  }

  $scope.onSelectRole = function($item, $model, $label,id){
      //console.log('we are in the role branch');
      if($model)$model.IsDirty = true;

      if($scope.userCopy.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.userCopy.Key_id,
          add: true
      }

      //console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        //console.log('here in the no key branch');
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
        if(convenienceMethods.arrayContainsObject($scope.userCopy.Roles,$scope.roles[3]))$scope.userCopy.isPI = true;
     }
  }

  function onAddRole(returned,role,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.userCopy.Roles,role))$scope.userCopy.Roles.push(role);
    if(role.Name.indexOf('rincip')>-1){
      console.log($scope.userCopy);
      $scope.userCopy.isPI = true;
    }
  }

  function onFailAddRole(){
    alert("There was a problem when trying to add a role to the user.");
  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    //console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.userCopy.Key_id,
        add: false
      }

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    ////console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
   // //console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert("There was a problem when trying to remove a role from the user.");
  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    userCopy.IsDirty = true;
    userCopy.Is_active = true;
    if(userCopy.Supervisor)userCopy.Supervisor_id = userCopy.Supervisor.Key_id;
    if(userCopy.Primary_department)userCopy.Primary_department_id = userCopy.Primary_department.Key_id;
    ////console.log(userCopy);

    var userDTO = {
      Class: "User",
      Is_active: true,
      Key_id: userCopy.Key_id,
      First_name: userCopy.First_name,
      Last_name: userCopy.Last_name,
      Email: userCopy.Email,
      Emergency_phone: userCopy.Emergency_phone,
      Lab_phone: userCopy.Lab_phone,
      Office_phone: userCopy.Office_phone,
      Username: userCopy.Username
    }

    //we separate properties that belong to sub-objects so that we don't throw js errors if they are not set
    if(userCopy.Primary_department)userDTO.Primary_department_id = userCopy.Primary_department.Key_id;
    if(userCopy.Supervisor)userDTO.Supervisor_id = userCopy.Supervisor.Key_id;

    convenienceMethods.updateObject( userDTO, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onSaveNewPI(piDTO, depts){
    ////console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    //console.log($scope.piCopy);
    angular.forEach($scope.departmentToAdd, function(department, key){
      //console.log(department);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });

    if(!convenienceMethods.arrayContainsObject($scope.pis,$scope.userCopy))$rootScope.pis.push(piDTO);

    $modalInstance.close($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    //console.log('inspector');
  }

  function onFailSaveNewInspector(){
    alert('There was a problem creating the new Inspector.');
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};
var personnelController = function($scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route) {
  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/contacts';

  $scope.addUser = function (user) {
    $scope.items = [];
    if(user){
      //we are editing a user that already exists
      var userCopy = angular.copy(user);
    }else{
      //we are creating a new user
      var userCopy = {}
      userCopy.Class = "User";
      userCopy.Roles = [];
      userCopy.Roles.push($scope.roles[0]);
    }

    $scope.items.push(userCopy);
    $scope.items.push(user);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'personnelModal.html',
      controller: labContactModalInstanceController,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
       selectedItem.IsDirty = false;
       //console.log(selectedItem);
       convenienceMethods.getUserTypes(selectedItem);
       $scope.putUserInRightPlace(selectedItem);
    });
  };

  $scope.handleUserActive = function(user){
    user.IsDirty = true;
    //console.log(user);
    var userCopy = angular.copy(user);
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(userCopy.Is_active == false){
      userCopy.Is_active = true;
    }else{
      userCopy.Is_active = false;
    }
    //console.log(userCopy);
    convenienceMethods.updateObject( userCopy, user, onSetUserActive, onFailSetUserActive, '../../ajaxaction.php?action=saveUser' );
  }

  function onSetUserActive(returned, old){
    //console.log(returned);
    old.IsDirty = false;
    //we use the == syntax instead of shorthand because server will return booleans as 1/0 as opposed to true/false, and JS interprets those as integers instead of booleans
    //0 will evaluate to false if tested with ==
    if(returned.Is_active == 0){
      returned.Is_active = false;
    }else{
      returned.Is_active = true;
    }
    old.Is_active = returned.Is_active;
    //console.log(old);
  }

  function onFailSetUserActive(){
    alert("The user could not be saved");
  }
}


//controller for modal instance for lab contacts
var personnelModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  if($location.$$host.indexOf('graysail')<0)$scope.isProductionServer = true;

  $scope.failFindUser = false;

  $scope.getAuthUser = function(){
    //console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.userCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    //console.log(data);
    if(!data.Roles)data.Roles = [];
    data.Roles.push($scope.roles[1]);
    $scope.userCopy = data;
    $scope.failFindUser = false;
  }

  function onFailFindUser(){
    //console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/EHSPersonnel');

  $scope.userCopy = items[0];
  if(items[1])$scope.user = items[1];
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.saveUser = function (userCopy, user) {
    var roles;
    userCopy.Is_active = true;
    userCopy.IsDirty = true;
    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    //console.log(userCopy);
    if(!userCopy.Key_id)userCopy.Is_active = true;
    convenienceMethods.updateObject( userCopy, user, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    userCopy.Key_id = data.Key_id;
    var rolesToAdd = $scope.userCopy.Roles;
    //see if we have new roles, but only if the user is not new, in which case all roles are new
    if($scope.user){
      var rolesToAdd = [];
      angular.forEach($scope.userCopy.Roles, function(role, key){
        if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
      });
    }
    angular.forEach(rolesToAdd, function(role, key){
      $scope.onSelectRole(role);
    });
    console.log($scope.userCopy);

    if($scope.userCopy.isPI && !convenienceMethods.arrayContainsObject($scope.pis,$scope.userCopy)){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }

    $modalInstance.close($scope.userCopy);
  }

  function onSaveNewPI(piDTO, depts){
    //console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    piDTO.User=$scope.userCopy;
    if(!convenienceMethods.arrayContainsObject($scope.pis,$scope.userCopy))$rootScope.PIs.push(piDTO);
    //console.log($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    //console.log('pi');
    $scope.inspectorCopy = angular.copy(inspectorDTO);
    //console.log($scope.piCopy);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }


  $scope.onSelectPI = function($item, $model, $label){
    //console.log($item);
    //console.log($model);
  }

  $scope.onSelectRole = function($item, $model, $label,id){
      if($model)$model.IsDirty = true;

      if($scope.userCopy.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.userCopy.Key_id,
          add: true
      }

      //console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
        if(convenienceMethods.arrayContainsObject($scope.userCopy.Roles,$scope.roles[3]))$scope.userCopy.isPI = true;
     }
  }

  function onAddRole(returned,dept,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.userCopy.Roles,dept))$scope.userCopy.Roles.push(dept);
  }

  function onFailAddRole(){
    alert("There was a problem when trying to add a role to the user.");
  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    //console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.userCopy.Key_id,
        add: false
      }

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
    //console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert("There was a problem when trying to remove a role from the user.");
  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    //console.log(userCopy);
    userCopy.IsDirty = true;
    userCopy.Is_active = true;
    userCopy.Supervisor_id = userCopy.Supervisor.Key_id;
    userCopy.Primary_department_id = userCopy.Primary_department.Key_id;
    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

};

var piController = function($scope, $modal, $routeParams, $browser,  $rootElement, $location, convenienceMethods, $filter, $route, $timeout) {


  //have we come here from piHub, by clicking the edit PI button?
  //if so, we should have a pi's last name in our $location.search()

  if($location.search().pi)$scope.searchText = $location.search().pi;

  $scope.wcount = function() {
    $timeout(function() {
      $scope.watchers = convenienceMethods.watchersContainedIn($scope);
    });
  };

  //create a modal instance for editing a user or creating a new one.
  //hold the current route in scope so we can be sure we display the right user type
  $scope.currentRoute = '/pis';
  $scope.order = 'User.Last_name';
  $scope.addPi = function (pi) {
    $scope.items = [];
    if(pi){
      //we are editing a PI that already exists
      //console.log(pi);
      var piCopy = angular.copy(pi);
    }else{
      //we are creating a PI user
      var piCopy = {}
      piCopy.Class = "PrincipalInvestigator";
      piCopy.User = {};
      piCopy.User.Class = "User";
      piCopy.User.Roles = [];
      piCopy.User.Roles.push($scope.roles[3]);
    }

    $scope.items.push(piCopy);
    $scope.items.push(pi);
    $scope.items.push($scope.roles);
    $scope.items.push($scope.pis);
    $scope.items.push($scope.departments);

    var modalInstance = $modal.open({
      templateUrl: 'piModal.html',
      controller: piModalInstanceController,
      resolve: {
        items: function () {
          return $scope.items;
        }
      }
    });

    modalInstance.result.then(function (selectedItem) {
       //console.log(selectedItem);
       selectedItem.IsDirty = false;
       convenienceMethods.getUserTypes(selectedItem.User);
       $scope.putUserInRightPlace(selectedItem.User);

      //console.log(selectedItem);
      //a new pi, push into the pis array
      if(!convenienceMethods.arrayContainsObject($scope.pis,selectedItem)){
        //console.log('new pi');
        $scope.pis.push(selectedItem);
      }else{
        //an edited pi, find in scope and update accordingly
        var idx = convenienceMethods.arrayContainsObject($scope.pis,selectedItem, null, true);
        //console.log(idx);
        $scope.pis[idx] = angular.copy(selectedItem);
      }

      //console.log($scope.pis);
    });
  }

  $scope.departmentFilter = function() {

    return function(pi) {
         var show = false;
        //for pis that don't have departments, don't filter them unless the filter has some text
        if(!pi.Departments)pi.Departments = [];
        if(!pi.Departments.length){
          if(typeof $scope.selectedDepartment == 'undefined' || $scope.selectedDepartment.length == 0){
            show = true;
          }
        }

        angular.forEach(pi.Departments, function(department, key){
          if(typeof $scope.selectedDepartment == 'undefined'|| department.Name.toLowerCase().indexOf($scope.selectedDepartment.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.buildingFilter = function() {
    return function(pi) {
        var show = false;
        //for pis that don't have buildings, don't filter them unless the filter has some text
        if(!pi.Buildings)pi.Buildings = [];
        if(!pi.Buildings.length){
          if(typeof $scope.selectedBuilding == 'undefined' || $scope.selectedBuilding.length == 0){
            show = true;
          }
        }
        angular.forEach(pi.Buildings, function(building, key){
          if(typeof $scope.selectedBuilding == 'undefined' || building.Name.toLowerCase().indexOf($scope.selectedBuilding.toLowerCase())>-1)show = true;
        });
        return show;
    }
  }

  $scope.handlePiActive = function(pi){
    pi.testFlag = 'test';
    pi.IsDirty = true;
    //console.log(pi);
    var pi = angular.copy(pi);
    var piDTO = {
          Class: "PrincipalInvestigator",
          User_id: pi.User_id,
          Is_active: !pi.Is_active,
          Key_id: pi.Key_id
    }
    convenienceMethods.updateObject( piDTO, pi, onSetPiActive, onFailSetPiActive, '../../ajaxaction.php?action=savePI', pi );
  }


  function onSetPiActive(returned, old){
    //console.log(old);
    //console.log(returned);
    old.IsDirty = false;
    old.Is_active = !old.Is_active;

    var idx = convenienceMethods.arrayContainsObject($scope.pis, old, null, true);
    $scope.pis[idx] = angular.copy(old);
  }

  function onFailSetPiActive(){
    $scope.piCopy.IsDirty = false;
    alert("The PI could not be saved.");
  }
  $scope.wcount();
}


//controller for modal instance for lab contacts
var piModalInstanceController = function ($scope, $modalInstance, items, convenienceMethods, $location, $window) {
  console.log(items);
  if($location.$$host.indexOf('graysail')<0)$scope.isProductionServer = true;

  $scope.failFindUser = false;
  //console.log(items[0]);

  $scope.formError = function(error){
    console.log(error);
    $scope.frmError = error;
  }

  $scope.getAuthUser = function(){
    //console.log('lookingForUser');
    $scope.lookingForUser = true;
    var userName = $scope.piCopy.userNameForQuery;
    convenienceMethods.getData('../../ajaxaction.php?action=lookupUser&username='+userName+'&callback=JSON_CALLBACK',onFindUser,onFailFindUser);
  }

  function onFindUser(data){
    $scope.lookingForUser = false;
    if(!data.Roles)data.Roles = [];
    data.Roles.push($scope.roles[3]);
    //console.log(data);
    $scope.piCopy.User = data;
    $scope.failFindUser = false;
    $scope.frmError = false;
  }

  function onFailFindUser(){
    //console.log('failed');
    $scope.lookingForUser = false;
    $scope.failFindUser = true;
  }

  $location.path('/pis');

  $scope.piCopy = items[0];
  if(items[1]){
    $scope.pi = items[1];
    $scope.userCopy = $scope.pi.User;
  }
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  $scope.savePi = function(){
    $scope.frmError = false;
    console.log( $scope.piCopy.User);
    $scope.piCopy.IsDirty = true;
    //save the user record
    var userDTO = {
      Class: "User",
      Is_active: true,
      Key_id: $scope.piCopy.User.Key_id,
      First_name: $scope.piCopy.User.First_name,
      Last_name: $scope.piCopy.User.Last_name,
      Email: $scope.piCopy.User.Email,
      Emergency_phone: $scope.piCopy.User.Emergency_phone,
      Lab_phone: $scope.piCopy.User.Lab_phone,
      Office_phone: $scope.piCopy.User.Office_phone,
      Username: $scope.piCopy.User.Username,
      Roles: $scope.piCopy.User.Roles
    }
    //console.log(userDTO);

    convenienceMethods.updateObject( userDTO, $scope.piCopy.User, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );

  }

  function onSaveUser(returned, old){

    $scope.userCopy = angular.copy(returned);
    if(returned.Key_id && returned.Key_id > 0){
      //if the pi exists already, we don't need to save it
      if(!$scope.piCopy.Key_id){
         var piDTO = {
            Class: "PrincipalInvestigator",
            User_id: returned.Key_id,
            Is_active: true
         }
        convenienceMethods.updateObject( piDTO, returned, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
      }else{
        onSaveNewPI($scope.piCopy);
      }
    }else{
      onFailSaveUser()
    }
  }

  function onFailSaveUser(){
    alert('There was a problem saving the PI');
  }

  function onSaveNewPI(returned, old){
   // convenienceMethods.setPropertiesFromDTO($scope.piCopy, returned);
    $scope.piCopy.Key_id = returned.Key_id;
    $scope.piCopy.Is_active = returned.Is_active;
    //$scope.piCopy.User = angular.copy($scope.userCopy);

    var rolesToAdd = [];
    angular.forEach($scope.piCopy.User.Roles, function(role, key){
      if(!convenienceMethods.arrayContainsObject(rolesToAdd,role))rolesToAdd.push(role);
    });
    angular.forEach(rolesToAdd, function(role, key){
      console.log(role);
      $scope.onSelectRole(role, returned.User.Key_id);
    });
   // $scope.onSelectRole({Class:'Role', Key_id:4}, returned.User.Key_id);

    var deptsToAdd = [];
    angular.forEach($scope.piCopy.Departments, function(dept, key){
      if(!convenienceMethods.arrayContainsObject(deptsToAdd,dept))deptsToAdd.push(dept);
    });
    angular.forEach(deptsToAdd, function(dept, key){
      $scope.onSelectDepartment(dept);
    });

    $scope.piCopy.IsDirty = false;
    //if we have a new inspector to save, save it
    //convenienceMethods.getUserTypes
    $scope.piCopy.User = angular.copy($scope.userCopy);
    $modalInstance.close($scope.piCopy);

  }

  function onFailSaveNewPi(){
    alert('The PI could not be saved.');
  }


  $scope.onSelectDepartment = function($item, $model, $label){
      ////console.log($scope.piCopy);

    if($scope.piCopy && $scope.piCopy.Key_id){
      if($model)$model.IsDirty = true;

      piDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.piCopy.Key_id,
          add: true
      }
     // //console.log(piDTO);
      convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, $model  );

    }else{
        if(!$scope.piCopy.Departments)$scope.piCopy.Departments = [];
        if(!convenienceMethods.arrayContainsObject($scope.piCopy.Departments,$item))$scope.piCopy.Departments.push($item);
        ////console.log($scope.piCopy);
      }
  }

  function onAddDepartment(returned,dept,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.piCopy.Departments,dept))$scope.piCopy.Departments.push(dept);
  }

  function onFailAddDepartment(){

  }

  $scope.removeDepartment = function(department, item, model){
    department.IsDirty = true;
    //console.log(department);

    piDTO = {
      Class: "RelationshipDto",
        relation_id: department.Key_id,
        master_id: $scope.piCopy.Key_id,
        add: false
      }

    convenienceMethods.updateObject( piDTO, department, onRemoveDepartment, onFailRemoveDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, department );
  }

  function onRemoveDepartment(returned,dept){
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.piCopy.Departments, dept, null, true);
    //console.log(idx);
    if(idx>-1)$scope.piCopy.Departments.splice(idx,1);
  }

  function onFailRemoveDepartment(){

  }

  $scope.onSelectRole = function($item, $model, $label, id){
      console.log($item);
      //console.log('we are in the role branch');
      if($model)$model.IsDirty = true;

      if($scope.userCopy.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.userCopy.Key_id,
          add: true
      }

      //console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
        if(convenienceMethods.arrayContainsObject($scope.userCopy.Roles,$scope.roles[3]))$scope.userCopy.isPI = true;
     }
  }

  function onAddRole(returned,dept,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.piCopy.User.Roles,dept))$scope.piCopy.User.Roles.push(dept);
  }

  function onFailAddRole(){
    alert("There was a problem when trying to add a role to the user.");
  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    //console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.piCopy.User.Key_id,
        add: false
      }

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.piCopy.User.Roles, dept, null, true);
    //console.log(idx);
    if(idx>-1)$scope.piCopy.User.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){
    alert('There was a problem when attempting to remove the role.');
  }


  $scope.cancel = function () {
    //console.log('closing');
    $modalInstance.dismiss('cancel');
  }

  function onSaveNewInspector(inspectorDTO, depts){
    //console.log('inspector');
  }

  function onFailSaveNewInspector(){
    alert('There was a problem creating the new Inspector.');
  }

};

// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.

var ModalInstanceCtrl = function ($scope, $modalInstance, items, convenienceMethods) {
  //console.log($modalInstance);
  //console.log(items);

  $scope.items = items;
  $scope.roles = items[2]
  $scope.pis = items[3];
  $scope.departments = items[4];

  //set the type of user
  if(items[0].Class == "PrincipalInvestigator"){
    //console.log('pi');
    if(!items[0].User)items[0].User = {Class: 'User'}
    if(!items[0].User.Roles){
       items[0].User.Roles = [];
      items[0].User.Roles.push($scope.roles[3])
    }
    $scope.userCopy = angular.copy(items[0].User);
    $scope.piCopy   = angular.copy(items[0]);

  }else{
    $scope.userCopy = items[0]
    if(items[0].Supervisor){
      //console.log('here');
      $scope.userType = items[2][4];
    }
  }
  //console.log( $scope.userCopy );

  $scope.selected = {
    item: $scope.items[0]
  };


  $scope.saveUser = function (userCopy, user) {
    var roles;
    userCopy.IsDirty = true;
    //$modalInstance.close($scope.items);
    if(!userCopy.Key_id)roles = userCopy.Roles;
    //save user
    //console.log(userCopy);
    if(!userCopy.Key_id)userCopy.Is_active = true;
    convenienceMethods.updateObject( userCopy, user, onSaveUser, onFailSaveUser, '../../ajaxaction.php?action=saveUser' );
  };

  function onSaveUser(returnedData, oldData, roles){
    //console.log(oldData);
    if(!oldData){
       returnedData.Roles = $scope.userCopy.Roles;
       oldData = angular.copy(returnedData);
    }

     //console.log(oldData);

    if(!returnedData.Roles)returnedData.Roles = $scope.userCopy.Roles;
    //if user is a PI, save that record
    if(returnedData.Roles && convenienceMethods.arrayContainsObject($scope.userCopy.Roles, $scope.roles[3])){
      //save pi

      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: returnedData.Key_id,
      }
      //console.log(piDTO);
      convenienceMethods.updateObject( piDTO, piDTO, onSavePI,onFailSavePi, '../../ajaxaction.php?action=savePI',roles );


    }else{
      data = [];
      data[0] = returnedData;
      data[1] = oldData;
      $modalInstance.close(data);
    }

    oldData.IsDirty = false;


    if(oldData.Class == "PrincipalInvestigator"){
      data = [];
      data[0] = oldData;
      data[0].User = returnedData;
      data[0].Departments = $scope.piCopy.Departments;
    }

    //else cclose and send back user object and copy as param of close funciton


  }

  function onFailSaveUser(){

  }

  function onSavePI(returnedData, oldData){
    //console.log('pi');
    $scope.piCopy = angular.copy( returnedData );
    angular.forEach(oldData.Departments, function(department, key){
      //console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });
    $modalInstance.close($scope.items);
  }

  function onFailSavePi(){
    alert("There was a problem saving the Principal Investigator.")
  }

  function setProperties(data){

    //unset dirty flag

    //close modal, passing back updated user/pi objects
  }

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };

  $scope.onSelectDepartment = function($item, $model, $label){

    if($scope.piCopy && $scope.piCopy.Key_id){
      if($model)$model.IsDirty = true;

      piDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.piCopy.Key_id,
          add: true
      }

      convenienceMethods.updateObject( piDTO, $item, onAddDepartment, onFailAddDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, $model  );

    }else{
        if(!$scope.userCopy.isPI){
        $scope.userCopy.Supervisor = {}
        if(!$scope.userCopy.Supervisor.Departments)$scope.userCopy.Supervisor.Departments = [];
        $scope.userCopy.Supervisor.Departments.push($item);
        //console.log($scope.userCopy);
      }else{

      }
    }
  }

  function onAddDepartment(returned,dept,model){
    if($model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.piCopy.Departments,dept))$scope.piCopy.Departments.push(dept);
  }

  function onFailAddDepartment(){

  }

  $scope.removeDepartment = function(department, item, model){
    department.IsDirty = true;
    //console.log(department);

    piDTO = {
      Class: "RelationshipDto",
        relation_id: department.Key_id,
        master_id: $scope.piCopy.Key_id,
        add: false
      }

    convenienceMethods.updateObject( piDTO, department, onRemoveDepartment, onFailRemoveDepartment, '../../ajaxaction.php?action=savePIDepartmentRelation', null, department );
  }

  function onRemoveDepartment(returned,dept){
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.piCopy.Departments, dept, null, true);
    //console.log(idx);
    if(idx>-1)$scope.piCopy.Departments.splice(idx,1);
  }

  function onFailRemoveDepartment(){

  }

  $scope.onSelectRole = function($item, $model, $label,id){
      if($model)$model.IsDirty = true;

      if($scope.userCopy.Key_id){

      userDTO = {
          Class: "RelationshipDto",
          relation_id: $item.Key_id,
          master_id: $scope.userCopy.Key_id,
          add: true
      }

      //console.log( userDTO );
      convenienceMethods.updateObject( userDTO, $item, onAddRole, onFailAddRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, $model  );

     }else{
        if($model)$model.IsDirty = false;
        $scope.userCopy.Key_id = id;
        if(!$scope.userCopy.Roles)$scope.userCopy.Roles = [];
        $scope.userCopy.Roles.push($item);
        if(convenienceMethods.arrayContainsObject($scope.userCopy.Roles,$scope.roles[3]))$scope.userCopy.isPI = true;
     }
  }

  function onAddRole(returned,dept,model){
    if(model)model.IsDirty = false;
    if(!convenienceMethods.arrayContainsObject($scope.userCopy.Roles,dept))$scope.userCopy.Roles.push(dept);
  }

  function onFailAddRole(){

  }

  $scope.removeRole = function(Role, item, model){
    Role.IsDirty = true;
    //console.log(Role);

    userDTO = {
      Class: "RelationshipDto",
        relation_id: Role.Key_id,
        master_id: $scope.userCopy.Key_id,
        add: false
      }

    if(userDTO.master_id){
       convenienceMethods.updateObject( userDTO, Role, onRemoveRole, onFailRemoveRole, '../../ajaxaction.php?action=saveUserRoleRelation', null, Role );
    }else{
        var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, Role, null, true);
        if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
    }
  }

  function onRemoveRole(returned,dept){
    //console.log(dept);
    dept.IsDirty = false;
    var idx = convenienceMethods.arrayContainsObject($scope.userCopy.Roles, dept, null, true);
    //console.log(idx);
    if(idx>-1)$scope.userCopy.Roles.splice(idx,1);
  }

  function onFailRemoveRole(){

  }

  //new user save methods
  $scope.saveNewUser = function(userCopy){
    //console.log(userCopy);
    userCopy.IsDirty = true;
    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }

  function onFailCreateUser(){
    alert("There was a problem creating the new user.");
  }

  function onCreateUser(data,userCopy){
    userCopy.Key_id = data.Key_id;
    angular.forEach(userCopy.Roles, function(role, key){
      $scope.onSelectRole(role, $scope.selectedRole);
    });

    if(userCopy.isPI){
      var piDTO = {
        Class: "PrincipalInvestigator",
        User_id: data.Key_id,
        Is_active: true
      }
      convenienceMethods.updateObject( piDTO, userCopy.Departments, onSaveNewPI, onFailSaveNewPi, '../../ajaxaction.php?action=savePI');
    }

    $scope.items[0] = $scope.userCopy;
    $modalInstance.close($scope.items);

  }

  function onSaveNewPI(piDTO, depts){
    //console.log('pi');
    $scope.piCopy = angular.copy(piDTO);
    //console.log($scope.piCopy);
    angular.forEach(depts, function(department, key){
      //console.log(dept);
      $scope.onSelectDepartment( department, $scope.selectedDepartment );
    });
    $modalInstance.close($scope.items);
  }

  function onFailSaveNewPi(){
    alert('There was a problem creating the new Principal Investigator.');
  }

  $scope.saveNewPi = function(){
    //console.log(userCopy);
    userCopy.IsDirty = true;

    //console.log(userCopy);

    convenienceMethods.updateObject( userCopy, userCopy, onCreateUser, onFailCreateUser, '../../ajaxaction.php?action=saveUser' );
  }


};