'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PrincipalInvestigator = function(){};

PrincipalInvestigator.prototype = {

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
		className: 	  'Room',
		keyReference: 'principalInvestigator_id',
		queryString:  'getRooms'	
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

	Buildings: {},

	loadAuthorizations: function() {
        if(!this.Authorizations){
            dataLoader.loadChildObject( this, 'Authorizations', this.AuthorizationsRelationship);
        }
    },

	loadActiveParcels: function() {
        if(!this.ActiveParcels) {
            console.log(this);
            dataLoader.loadChildObject( this, 'Parcels', this.ActiveParcelsRelationship);
        }
    },

    loadPurchaseOrders: function() {
        if(!this.PurchaseOrders) {
            dataLoader.loadChildObject( this, 'PurchaseOrders', this.PurchaseOrdersRelationship);
        }
    },

    loadUser:  function() {
        alert('yo');
        if(!this.User && this.User_id) {
            dataLoader.loadObjectById( this, 'User', 'User', this.User_id );
        }
    }

}

//inherit from and extend GenericModel
extend(PrincipalInvestigator, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("principalInvestigator",[])
	.value("PrincipalInvestigator",PrincipalInvestigator);

