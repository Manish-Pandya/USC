'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function(){};
Hazard.prototype = {

	SubHazardsRelationship: {

		Class: 	  'Hazard',
		keyReference:  'Parent_hazard_id',
		queryString:  'getHazardTreeNode'	

	},

	SaveUrl:  'saveHazard'

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("hazard",[])
	.value("Hazard",Hazard);

