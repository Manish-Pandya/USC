'use strict';

//constructor
var Isotope = function() {};
Isotope.prototype = {

    // Really? Nothing to go here? Surely I'm forgetting something, this is too easy...

}

// inherit from GenericModel
extend(Isotope, GenericModel);

angular
    .module("Isotope", [])
    .value("PrincipalInvestigator", PrincipalInvestigator);
