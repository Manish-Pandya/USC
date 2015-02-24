'use strict';
/* Auto-generated stub file for the Pickup class. */

//constructor
var Pickup = function() {};
Pickup.prototype = {

    // Future eager accessors or loader functions go here.

}

// inherit from GenericModel
extend(Pickup, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("pickup", [])
    .value("Pickup", Pickup);

