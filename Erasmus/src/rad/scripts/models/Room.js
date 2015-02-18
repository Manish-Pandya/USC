'use strict';

//constructor
var Room = function() {};
Room.prototype = {
    //className: "Room"


}

extend(Room, GenericModel);

angular
    .module("room", [])
    .value("Room", Room);