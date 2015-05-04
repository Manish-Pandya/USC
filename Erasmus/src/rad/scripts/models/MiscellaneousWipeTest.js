'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var MiscellaneousWipeTest = function() {};
MiscellaneousWipeTest.prototype = {
	className: "MiscellaneousWipeTest",
}

// inherit from GenericModel
extend(MiscellaneousWipeTest, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("miscellaneousWipeTest", [])
    .value("MiscellaneousWipeTest", InspectionWipe);

