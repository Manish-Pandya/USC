'use strict';
/* Auto-generated stub file for the SolidsContainer class. */

//constructor
var SolidsContainer = function() {};
SolidsContainer.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(SolidsContainer, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("solidscontainer", [])
    .value("SolidsContainer", SolidsContainer);

