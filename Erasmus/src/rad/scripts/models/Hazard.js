'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function(){};
Hazard.prototype = {

    subHazardsRelationship: {
        className:    'Hazard',
        keyReference:  'Parent_hazard_id',
        methodString:  'getHazardTreeNode',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    saveUrl:  'saveHazard',

    loadSubHazards: function() {
        if(!this.subHazards) {
            dataLoader.loadChildrenFromRelationship( this, 'subHazards', this.subHazardsRelationship);
        }
    }

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("hazard",[])
	.value("Hazard",Hazard);

