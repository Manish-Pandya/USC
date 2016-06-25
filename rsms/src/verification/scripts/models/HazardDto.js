'use strict';

//constructor
var HazardDto = function () { };
HazardDto.prototype = {
    className: "HazardDto",

    eagerAccessors:[
        {method:"loadPendingHazardDtoChange", boolean:'Name'}
    ],

    loadPendingHazardDtoChange: function()
    {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PendingHazardDtoChange) {
            this.PendingHazardDtoChange = dataStoreManager.getChildByParentProperty("PendingHazardDtoChange", "Parent_id", this.Key_id);
            if(this.PendingHazardDtoChange){
                this.PendingHazardDtoChangeCopy = dataStoreManager.createCopy(this.PendingHazardDtoChange)
            }else{
                this.PendingHazardDtoChangeCopy = this.inflator.instantiateObjectFromJson(new window.PendingHazardDtoChange());
                if (!this.PendingHazardDtoChangeCopy.hasOwnProperty("Parent_class")) this.PendingHazardDtoChangeCopy.Parent_class = "HazardDto";
                this.PendingHazardDtoChangeCopy.Parent_id = this.Key_id;
            }
            this.PendingHazardDtoChangeCopy.Is_active = true;
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
