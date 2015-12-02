'use strict';
/* Auto-generated stub file for the XRay class. */

//constructor
var XRay = function() {};
XRay.prototype = {
    
}

// inherit from GenericModel
extend(XRay, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("xray", [])
    .value("XRay", XRay);
