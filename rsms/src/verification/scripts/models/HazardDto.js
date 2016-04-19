'use strict';

//constructor
var HazardDto = function () { };
HazardDto.prototype = {
    className: "HazardDto",

    eagerAccessors:[
        {method:"loadPendingHazardChange", boolean:'Name'}
    ],

    loadPendingHazardChange: function()
    {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PendingHazardChange) {
            this.PendingHazardChange = dataStoreManager.getChildByParentProperty("PendingHazardChange", "Parent_id", this.Key_id);
            if(this.PendingHazardChange){
                this.PendingHazardChangeCopy = dataStoreManager.createCopy(this.PendingHazardChange)
            }else{
                this.PendingHazardChangeCopy = this.inflator.instantiateObjectFromJson(new window.PendingHazardChange());
                if (!this.PendingHazardChangeCopy.hasOwnProperty("Parent_class")) this.PendingHazardChangeCopy.Parent_class = "HazardDto";
                this.PendingHazardChangeCopy.Parent_id = this.Key_id;
            }
            this.PendingHazardChangeCopy.Is_active = true;
        }
    },

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

// inherit from GenericModel
extend(HazardDto, GenericModel);
