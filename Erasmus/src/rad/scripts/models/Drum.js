'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var Drum = function() {};
Drum.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(Drum, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("drum", [])
    .value("Drum", Drum);

