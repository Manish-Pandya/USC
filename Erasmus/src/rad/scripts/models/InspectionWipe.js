'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var InspectionWipe = function() {};
InspectionWipe.prototype = {
	className: "InspectionWipe",

	eagerAccessors:[
		{method:'loadRoom',boolean:"Room_id"}
	],

	loadRoom: function(){
		console.log(dataStore.Room);
		dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
		console.log(this.Room);
	}
}

// inherit from GenericModel
extend(InspectionWipe, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("inspectionWipe", [])
    .value("InspectionWipe", InspectionWipe);

