'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var ParcelWipe = function() {};
ParcelWipe.prototype = {
	className: "ParcelWipe",
}

// inherit from GenericModel
extend(ParcelWipe, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("parcelWipe", [])
    .value("ParcelWipe", InspectionWipe);

