'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var PIAuthorization = function() {};
PIAuthorization.prototype = {
    className: "PIAuthorization",
    Class: "PIAuthorization",
    eagerAccessors: [{method:"instantiateAuthorizations", boolean: "Authorizations"}],

    instantiateAuthorizations: function(){
        this.Authorizations = this.inflator.instateAllObjectsFromJson(this.Authorizations);
    }
}

// inherit from GenericModel
extend(PIAuthorization, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("pIAuthorization", [])
    .value("PIAuthorization", InspectionWipe);
