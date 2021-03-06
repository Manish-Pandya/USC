'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyReadingAmount = function() {};
CarboyReadingAmount.prototype = {
	className: "CarboyReadingAmount",

	eagerAccessors:[
		{method:"loadIsotope", boolean: 'Isotope_id'},
	],

    // TODO eager accessors, relationships, method names.
    loadIsotope:function(){
    	if(this.Isotope_id){
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    }
}

// inherit from GenericModel
extend(CarboyReadingAmount, GenericModel);
