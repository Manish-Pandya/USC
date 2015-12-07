'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var ParcelUse = function() {};
ParcelUse.prototype = {
    eagerAccessors:[
        {method:"loadParcelUseAmounts", boolean: 'Key_id'}
    ],
    AmountsRelationship:{
        className: 	  'ParcelUseAmount',
        keyReference:  'Parcel_use_id',
        queryString:  'getParcelUseAmountById',
        paramValue: 'Key_id',
        queryParam:   ''
    },
    loadParcel: function() {
        if(!this.Parcel) {
            dataLoader.loadChildObject(this, 'Parcel', 'Parcel', this.Parcel_id);
        }
    },
    loadParcelUseAmounts: function() {
        if(!this.ParcelUseAmounts) {
            console.log('hello');
            dataLoader.loadOneToManyRelationship(this,"ParcelUseAmounts",this.AmountsRelationship);
        }
    }

}

// inherit from GenericModel
extend(ParcelUse, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("parceluse", [])
    .value("ParcelUse", ParcelUse);

