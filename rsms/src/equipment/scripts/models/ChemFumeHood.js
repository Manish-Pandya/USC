'use strict';
/* Auto-generated stub file for the ChemFumeHood class. */

//constructor
var ChemFumeHood = function() {};
ChemFumeHood.prototype = {
    
}

// inherit from GenericModel
extend(ChemFumeHood, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("chemFumeHood", [])
    .value("ChemFumeHood", ChemFumeHood);
