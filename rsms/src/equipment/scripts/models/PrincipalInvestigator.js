'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    eagerAccessors: [],

    RoomsRelationship:{
        name: 	  'PrincipalInvestigatorRoomRelation',
        className: 'Room',
        keyReference: 'Principal_investigator_id',
        otherKey:     'Room_id',
        paramValue:  'Key_id'
    },

    Buildings: {},

    loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, 'Room', this.RoomsRelationship, "getRoomsByPIId&id="+this.Key_id );
    }

}


//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericModel);