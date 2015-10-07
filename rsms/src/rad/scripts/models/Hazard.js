'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function(){};
Hazard.prototype = {

    SubHazardsRelationship: {
        className:    'Hazard',
        keyReference:  'Parent_hazard_id',
        methodString:  'getHazardTreeNode',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    loadSubHazards: function() {
        if(!this.SubHazards) {
            return dataLoader.loadOneToManyRelationship( this, 'ActiveSubHazards', this.SubHazardsRelationship);
        }
    }

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
    .module("hazard",[])
    .value("Hazard",Hazard);

