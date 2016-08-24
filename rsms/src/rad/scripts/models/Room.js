'use strict';

//constructor
var Room = function () {
    Room.url = "";
    Room.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllRooms";
};
Room.prototype = {

    // many-to-many relationship for rooms-pi
    PIRelationship: {
        name: 'PrincipalInvestigatorRoomRelation',
        className: 'PrincipalInvestigator',
        keyReference: 'Room_id',
        otherKey:     'Principal_investigator_id',
        paramValue: 'Key_id'
    },

    // one-to-many relationship
    ContainerRelationship: {
        className: 'SolidsContainer',
        keyReference: 'Room_id',
        paramValue: 'Key_id'
    },

    loadPrincipalInvestigators: function() {
        if(!this.PrincipalInvestigators) {
            dataLoader.loadManyToManyRelationship(this, 'PrincipalInvestigators', this.PIRelationship);
        }
    },

    loadSolidsContainers: function() {
        if(!this.SolidsContainers) {
            dataLoader.loadOneToManyRelationship(this, 'SolidsContainers', this.ContainerRelationship);
        }
    }

}

extend(Room, GenericModel);

Room();

angular
    .module("room", [])
    .value("Room", Room);