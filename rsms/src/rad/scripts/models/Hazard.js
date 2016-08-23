'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function () {
    Hazard.url = "";
    Hazard.urlAll = "http://erasmus.graysail.com:9080/rsms/src/ajaxaction.php?action=getAllHazards";
};
Hazard.prototype = {

    ID_prop: "Hazard_id",
    //eagerAccessors:[{method:"loadSubHazards",boolean:"HasChildren"}],

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
extend(Hazard, GenericModel);

Hazard();
