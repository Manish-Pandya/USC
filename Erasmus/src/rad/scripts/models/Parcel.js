'use strict';

//constructor
var Parcel = function() {};
Parcel.prototype = {

	className: "Parcel",

	eagerAccessors:[
		{method:"loadIsotope", boolean: 'Isotope_id'},
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

	loadIsotope: function() {
        if(!this.Isotope){
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    },

	loadPurchaseOrder: function() {
        if(!this.PurchaseOrder){
            dataLoader.loadChildObject(this, 'PurchaseOrder', 'PurchaseOrder', this.Purchase_order_id);
        }
    },

    loadPrincipalInvestigator: function() {
        if(!this.PrincipalInvestigator) {
            dataLoader.loadChildObject(this, 'Principal_investigator',
                'PrincipalInvestigator', this.Principal_investigator_id);
        }
    },
}

// inherit from GenericModel
extend(Parcel, GenericModel);

angular
    .module("parcel", [])
    .value("Parcel", Parcel);
