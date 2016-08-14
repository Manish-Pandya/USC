'use strict';
/* Auto-generated stub file for the ParcelUseAmount class. */

//constructor
var ParcelUseAmount = function() {};
ParcelUseAmount.prototype = {

	eagerAccessors:[
		{method: "loadCarboy", boolean:"Carboy_id"},
	],

    // Any future accessors, eager loaders, etc. will go here.
	loadCarboy: function () {
    	dataLoader.loadChildObject(this, "Carboy", "CarboyUseCycle", this.Carboy_id);
    },

    // Any future accessors, eager loaders, etc. will go here.
    loadSolidsContainer: function(){
    	dataLoader.loadChildObject(this, "SolidsContainer", "SolidsContainer", this.SolidsContainer_id);
    },
}

// inherit from GenericModel
extend(ParcelUseAmount, GenericModel);