'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Authorization = function() {};
Authorization.prototype = {

    eagerAccessors:[
        {method:"loadIsotope", boolean:"Isotope_id"}
    ],

    RoomsRelationship:{
        name: 	  'PrincipalInvestigatorRoomRelation',
        className: 'Room',
        keyReference: 'Principal_investigator_id',
        otherKey:     'Room_id',
        paramValue:  'Key_id'
    },

    loadIsotope: function() {
        if(!this.Isotope) {
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    },

   loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, 'Rooms', this.RoomsRelationship, "getRoomsByPIId&id="+this.Key_id );
    },

    loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, 'Rooms', this.RoomsRelationship, "getRoomsByPIId&id="+this.Key_id );
    },

    loadActiveParcels:function(){

    }

}

// inherit from GenericModel
extend(Authorization, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("authorization", [])
    .value("Authorization", Authorization);

