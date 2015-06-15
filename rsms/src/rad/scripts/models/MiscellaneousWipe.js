'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var MiscellaneousWipe = function() {};
MiscellaneousWipe.prototype = {
	className: "MiscellaneousWipe",
}

// inherit from GenericModel
extend(MiscellaneousWipe, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("miscellaneousWipe", [])
    .value("MiscellaneousWipe", MiscellaneousWipe);

