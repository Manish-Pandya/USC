'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Authorization = function() {};
Authorization.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(Authorization, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("authorization", [])
    .value("Authorization", Authorization);

