'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Inspection = function() {};
Inspection.prototype = {
    className = "inspection"
}

// inherit from GenericModel
extend(Inspection, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("inspectionWipe", [])
    .value("Inspection", InspectionWipe);

