'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Authorization = function() {};
Authorization.prototype = {


    loadIsotope: function() {
        if(!this.isotope) {
            dataLoader.loadObjectById(this, 'isotope', 'Isotope', this.Isotope_id);
        }
    }

}

// inherit from GenericModel
extend(Authorization, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("authorization", [])
    .value("Authorization", Authorization);

