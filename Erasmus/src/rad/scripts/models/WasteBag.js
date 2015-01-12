'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var WasteBag = function() {};
WasteBag.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(WasteBag, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("wastebag", [])
    .value("WasteBag", WasteBag);

