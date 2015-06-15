'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var PIQuarterlyInventory = function() {};
PIQuarterlyInventory.prototype = {
    className: "PIQuarterlyInventory"
}

// inherit from GenericModel
extend(PIQuarterlyInventory, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("pIQuarterlyInventory", [])
    .value("PIQuarterlyInventory", Drum);

