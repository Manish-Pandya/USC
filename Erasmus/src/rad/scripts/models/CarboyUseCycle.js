'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyUseCycle = function() {};
CarboyUseCycle.prototype = {
	className: "CarboyUseCycle",

	eagerAccessors:[
		{method:"loadCarboy", boolean: 'Carboy_id'},
	],

    // TODO eager accessors, relationships, method names.
    loadCarboy:function(){
    	if(!this.Carboy){
            dataLoader.loadChildObject(this, 'Carboy', 'Carboy', this.Carboy_id);
        }
    }
}

// inherit from GenericModel
extend(CarboyUseCycle, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboyUseCycle", [])
    .value("CarboyUseCycle", Carboy);

