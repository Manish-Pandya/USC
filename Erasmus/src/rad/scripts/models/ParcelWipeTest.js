'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var ParcelWipeTest = function() {};
ParcelWipeTest.prototype = {
	className: "ParcelWipeTest",
	ParcelWipesRelationship: {

        className:    'ParcelWipe',
        keyReference:  'Parcel_wipe_test_id',
        methodString:  'getAuthorizationsByPIId',
        paramValue: 'Key_id',
        paramName: 'id'

    },
	loadParcel_wipes: function(){
		this.Parcel_wipes = [];
        dataLoader.loadManyToManyRelationship( this, 'Parcel_wipes', this.ParcelWipesRelationship );
	}
}

// inherit from GenericModel
extend(ParcelWipeTest, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("parcelWipeTest", [])
    .value("ParcelWipeTest", InspectionWipe);

