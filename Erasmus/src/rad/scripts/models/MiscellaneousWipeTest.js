'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var MiscellaneousWipeTest = function() {};
MiscellaneousWipeTest.prototype = {
	className: "MiscellaneousWipeTest",

	MiscellaneousWipeRelationship: {
        className:    'MiscellaneousWipe',
        keyReference:  'Miscellaneous_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },
	loadMiscellaneous_wipes: function(){
		alert('yo')
		this.Miscellaneous_wipes = [];
		console.log(dataStore);
        dataLoader.loadManyToManyRelationship( this, 'Miscellaneous_wipes', this.MiscellaneousWipeRelationship );
	}
}

// inherit from GenericModel
extend(MiscellaneousWipeTest, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("miscellaneousWipeTest", [])
    .value("MiscellaneousWipeTest", InspectionWipe);

