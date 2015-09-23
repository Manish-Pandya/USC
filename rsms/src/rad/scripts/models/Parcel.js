'use strict';

//constructor
var Parcel = function() {};
Parcel.prototype = {

	className: "Parcel",

	eagerAccessors:[
		{method:"loadAuthorization", boolean: 'Authorization_id'},
		{method:"loadPurchaseOrder", boolean: 'Purchase_order_id'}
	],

	IsotopeRelationship:{
		className: 	  'Isotope',
		keyReference:  'Isotope_id',
		queryString:  'getIsotopeById',
		queryParam:   ''	
	},

	PurchaseOrderRelationship:{
		className: 	  'PurchaseOrder',
		keyReference:  'Purchase_order_id',
		queryString:  'getPurchaseOrderById',
		queryParam:   ''	
	},
    
    AuthorizationRelationship:{
		className: 	  'Authorization',
		keyReference:  'Parcel_id',
		queryParam:   ''	
	},

    ParcelUsesRelationship: {

        className:    'ParcelUse',
        keyReference:  'Parcel_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },

	loadPurchaseOrder: function() {
        if(!this.PurchaseOrder){
            dataLoader.loadChildObject(this, 'PurchaseOrder', 'PurchaseOrder', this.Purchase_order_id);
        }
    },

    loadPrincipalInvestigator: function() {
        if(!this.PrincipalInvestigator) {
            dataLoader.loadChildObject(this, 'Principal_investigator','PrincipalInvestigator', this.Principal_investigator_id);
        }
    },

    loadUses: function() {
        if(!this.Uses) {
            dataLoader.loadOneToManyRelationship( this, 'ParcelUses', this.ParcelUsesRelationship);
        }
    },
    loadAuthorization: function() {
        if(!this.Authorization) {
            dataLoader.loadChildObject(this, 'Authorization','Authorization', this.Authorization_id);
        }
    },
}

// inherit from GenericModel
extend(Parcel, GenericModel);

angular
    .module("parcel", [])
    .value("Parcel", Parcel);
