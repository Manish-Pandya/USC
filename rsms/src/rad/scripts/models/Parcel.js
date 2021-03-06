'use strict';

//constructor
var Parcel = function() {};
Parcel.prototype = {

    className: "Parcel",

    eagerAccessors:[
        {method:"loadPurchaseOrder", boolean: 'Purchase_order_id'},
        {method:"loadAuthorization", boolean: 'Authorization_id'},
        {method:"loadParcelWipeTest", boolean: 'HasTests'}
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

    ParcelWipeTestRelationship: {

        className:    'ParcelWipeTest',
        keyReference:  'Parcel_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

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
            return dataLoader.loadChildObject(this, 'Principal_investigator','PrincipalInvestigator', this.Principal_investigator_id);

        }
    },

    loadUses: function () {
        return;
        return dataLoader.loadOneToManyRelationship(this, 'ParcelUses', this.ParcelUsesRelationship);        
    },
    loadAuthorization: function() {
        if (!this.Authorization) {
            dataLoader.loadChildObject (this, "Authorization", "Authorization", this.Authorization_id);
        }
    },
    loadParcelWipeTest: function() {
        if(!this.Wipe_test) {
            dataLoader.loadOneToManyRelationship( this, 'Wipe_test', this.ParcelWipeTestRelationship);
        }
    }
}

// inherit from GenericModel
extend(Parcel, GenericModel);
