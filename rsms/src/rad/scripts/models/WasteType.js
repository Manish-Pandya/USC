'use strict';
/* Auto-generated stub file for the WasteType class. */

//constructor
var WasteType = function() {};
WasteType.prototype = {

    // Future accessors, eager loaders, etc will go here.
}

// inherit from GenericModel
extend(WasteType, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("wastetype", [])
    .value("WasteType", WasteType);

