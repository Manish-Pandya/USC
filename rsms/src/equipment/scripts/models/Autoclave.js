'use strict';
/* Auto-generated stub file for the Autoclave class. */

//constructor
var Autoclave = function() {};
Autoclave.prototype = {
    
}

// inherit from GenericModel
extend(Autoclave, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("autoclave", [])
    .value("Autoclave", Autoclave);
