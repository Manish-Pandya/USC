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

    SaveUrl:  'saveHazard',

    getSubHazards: function() {
            if(this.SubHazards) {
                return this.SubHazards;
            }
            else {
                return dataSwitch.getChildObject( this, 'SubHazards', this.SubHazardsRelationship);
            }
    }

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("hazard",[])
	.value("Hazard",Hazard);

