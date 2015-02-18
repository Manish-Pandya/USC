'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {
    className: "PrincipalInvestigator",

	eagerAccessors:[
		{method:"getPrincipalInvestigatorRoomRelations"},
		{method:"loadUser", boolean:"User_id"},
		{method:"getLabPersonnel"},
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
        methodString:  'getPurchaseOrdersByPIId',
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
            dataLoader.loadOneToManyRelationship( this, 'Parcels', this.ActiveParcelsRelationship);
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

