'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    className: "PrincipalInvestigator",

	eagerAccessors:[
		{method:"getPrincipalInvestigatorRoomRelations"},
		{method:"loadUser", boolean:"User_id"},
        {method:"loadCarboys", boolean:true},
        {method:"loadSolidsContainers", boolean:true},
		{method:"getRooms", boolean:"PrincipalInvestigatorRoomRelations"}
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

	PrincipalInvestigatorRoomRelationRelationship: {
		Class: 	  'PrincipalInvestigatorRoomRelation',
		foreignKey:  'Principal_investigator_id',
		queryString:  'getPrincipalInvestigatorRoomRelationsByPiId&id=',
		queryParam:   'Key_id'
	},	

	RoomsRelationship:{
		name: 	  'PrincipalInvestigatorRoomRelation',
        className: 'Room',
		keyReference: 'Principal_investigator_id',
        otherKey:     'Room_id',
		paramValue:  'Key_id'	
	},

	AuthorizationsRelationship: {

        className:    'Authorization',
        keyReference:  'Principal_investigator_id',
        methodString:  'getAuthorizationsByPIId',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    ActiveParcelsRelationship: {

        className:    'Parcel',
        keyReference:  'Principal_investigator_id',
        methodString:  'getActiveParcelsFromPIById',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    PurchaseOrdersRelationship: {

        className:    'PurchaseOrder',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    SolidsContainersRelationship: {

        className:    'SolidsContainer',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },


    CarboyUseCyclesRelationship: {

        className:    'CarboyUseCycle',
        keyReference:  'Principal_investigator_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },


	Buildings: {},

	loadAuthorizations: function() {
        if(!this.Authorizations){
            dataLoader.loadOneToManyRelationship( this, 'Authorizations', this.AuthorizationsRelationship);
        }
    },

	loadActiveParcels: function() {
        if(!this.ActiveParcels) {
            dataLoader.loadOneToManyRelationship( this, 'ActiveParcels', this.ActiveParcelsRelationship);
        }
    },

    loadRooms: function() {
        if(!this.Rooms) {
            dataLoader.loadManyToManyRelationship( this, 'Rooms', this.RoomsRelationship );
        }
    },

    loadPurchaseOrders: function() {
        if(!this.PurchaseOrders) {
            dataLoader.loadOneToManyRelationship( this, 'PurchaseOrders', this.PurchaseOrdersRelationship);
        }
    },

    loadSolidsContainers: function() {
        if(!this.SolidsContainers) {
            dataLoader.loadOneToManyRelationship( this, 'SolidsContainers', this.SolidsContainersRelationship);
        }
    },

    loadCarboyUseCycles: function() {
        if(!this.Carboys) {
            dataLoader.loadOneToManyRelationship( this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship);
        }
    },

    loadUser:  function() {
        if(!this.User && this.User_id) {
            dataLoader.loadChildObject( this, 'User', 'User', this.User_id );
        }
    }

}

//inherit from and extend GenericModel
extend(PrincipalInvestigator, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("principalInvestigator",[])
	.value("PrincipalInvestigator",PrincipalInvestigator);

