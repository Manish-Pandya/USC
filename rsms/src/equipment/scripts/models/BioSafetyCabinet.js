'use strict';
/* Auto-generated stub file for the BioSafetyCabinet class. */

//constructor
var BioSafetyCabinet = function() {};
BioSafetyCabinet.prototype = {
    
}

// inherit from GenericModel
extend(BioSafetyCabinet, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("bioSafetyCabinet", [])
    .value("BioSafetyCabinet", BioSafetyCabinet);
