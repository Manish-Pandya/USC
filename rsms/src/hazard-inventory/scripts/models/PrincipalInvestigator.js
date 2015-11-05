'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    eagerAccessors: [
        {method:"loadUser", boolean:"User_id"},
    ],

    UserRelationship: {
        className: 	  'User',
        keyReference:  'User_id',
        queryString:  'getUserById',
        queryParam:   ''
    },

    LabPersonnelRelationship: {
        className: 	  'User',
        keyReference:  'Supervisor_id',
        queryString:  'getUserById',
        queryParam:  ''
    },

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
    },

    loadUser:  function() {
        if(!this.User && this.User_id) {
            dataLoader.loadChildObject( this, 'User', 'User', this.User_id );
        }
    }

}


//inherit from and extend GenericPrincipalInvestigator
extend(PrincipalInvestigator, GenericPrincipalInvestigator);

//create an angular module for the model, so it can be injected downstream
angular
    .module("principalInvestigator",[])
    .value("PrincipalInvestigator",PrincipalInvestigator);
