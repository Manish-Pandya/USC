'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var InspectionWipeTest = function() {};
InspectionWipeTest.prototype = {
	className: "InspectionWipeTest",
	InspectionWipesRelationship: {

        className:    'InspectionWipe',
        keyReference:  'Inspection_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },
	loadInspection_wipes: function(){
		this.Inspection_wipes = [];
        dataLoader.loadOneToManyRelationship( this, 'Inspection_wipes', this.InspectionWipesRelationship );
	},
}

// inherit from GenericModel
extend(InspectionWipeTest, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("inspectionWipeTest", [])
    .value("InspectionWipeTest", InspectionWipeTest);

