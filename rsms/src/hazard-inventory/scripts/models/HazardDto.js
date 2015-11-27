'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var HazardDto = function(){};
HazardDto.prototype = {

    ID_prop: "Hazard_id",
    //eagerAccessors:[{method:"loadSubHazards",boolean:"HasChildren"}],
    Class: "HazardDto",

    SubHazardsRelationship: {
        className:    'HazardDto',
        keyReference:  'Parent_hazard_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    loadSubHazards: function(){
        if(!this.ActiveSubHazards) {
            return dataLoader.loadOneToManyRelationship( this, 'ActiveSubHazards', this.SubHazardsRelationship);
        }
    }

}

//inherit from and extend GenericModel
extend(HazardDto, GenericModel);
