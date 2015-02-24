'use strict';

//constructor
var Room = function() {};
Room.prototype = {

    // many-to-many relationship for rooms-pi
    PIRelationship: {
        name: 'PrincipalInvestigatorRoomRelation',
        className: 'PrincipalInvestigator',
        keyReference: 'Room_id',
        otherKey:     'Principal_investigator_id',
        paramValue: 'Key_id'
    },

    loadPrincipalInvestigators: function() {
        if(!this.PrincipalInvestigators) {
            dataLoader.loadManyToManyRelationship(this, 'PrincipalInvestigators', this.PIRelationship);
        }
    }

}

extend(Room, GenericModel);

angular
    .module("room", [])
    .value("Room", Room);