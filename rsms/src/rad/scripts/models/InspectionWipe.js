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
		dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
	}
}

// inherit from GenericModel
extend(InspectionWipe, GenericModel);