'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyUseCycle = function() {};
CarboyUseCycle.prototype = {
	className: "CarboyUseAmount",

	eagerAccessors:[
		{method:"loadIsotope", boolean: 'Isotope_id'},
	],

    // TODO eager accessors, relationships, method names.
    loadIsotope:function(){
    	if(!this.Carboy){
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope_id', this.Isotope_id);
        }
    }
}

// inherit from GenericModel
extend(CarboyUseCycle, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboyUseCycle", [])
    .value("CarboyUseCycle", Carboy);

