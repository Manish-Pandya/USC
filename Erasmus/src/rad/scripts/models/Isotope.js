'use strict';

//constructor
var Isotope = function() {};
Isotope.prototype = {

    // no loaders or eager accesors to add currently

}

// inherit from GenericModel
extend(Isotope, GenericModel);

angular
    .module("isotope", [])
    .value("Isotope", Isotope);
