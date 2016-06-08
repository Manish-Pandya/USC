'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var PIAuthorization = function() {};
PIAuthorization.prototype = {
    className: "PIAuthorization",
    Class: "PIAuthorization",
    eagerAccessors: [
        //{method:"loadRooms", boolean:true},
        {method:"loadAuthorizations", boolean:true},
        {method:"loadDepartments", boolean:true}
    ],
    AuthorizationsRelationship: {
        className:    'Authorization',
        keyReference:  'Pi_authorization_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    RoomsRelationship:{
        table: 	  'pi_authorization_room',
        childClass: 'Room',
        parentProperty: 'Rooms',
        isMaster: true
    },

    DepartmentsRelationship:{
        table: 	  'pi_authorization_department',
        childClass: 'Department',
        parentProperty: 'Departments',
        isMaster: true
    },

    instantiateAuthorizations: function(){
        this.Authorizations = this.inflator.instateAllObjectsFromJson(this.Authorizations);
    },

    loadAuthorizations: function() {
        dataLoader.loadOneToManyRelationship(this, "Authorizations", this.AuthorizationsRelationship);
    },

    loadRooms: function() {
        dataLoader.loadManyToManyRelationship( this, this.RoomsRelationship );
    },
    loadDepartments: function() {
        dataLoader.loadManyToManyRelationship( this, this.DepartmentsRelationship );
    },
}

// inherit from GenericModel
extend(PIAuthorization, GenericModel);
