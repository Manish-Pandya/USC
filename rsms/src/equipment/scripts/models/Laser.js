'use strict';
/* Auto-generated stub file for the Laser class. */

//constructor
var Laser = function() {};
Laser.prototype = {
    
}

// inherit from GenericModel
extend(Laser, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("laser", [])
    .value("Laser", Laser);
