'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var Carboy = function() {};
Carboy.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(Carboy, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboy", [])
    .value("Carboy", Carboy);

