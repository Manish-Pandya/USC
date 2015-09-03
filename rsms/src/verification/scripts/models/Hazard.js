'use strict';

//constructor
var Hazard = function() {};
Hazard.prototype = {
    className: "Hazard",

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
                if(!this.PendingHazardChangeCopy.hasOwnProperty("Parent_class"))this.PendingHazardChangeCopy.Parent_class = "Hazard";
                this.PendingHazardChangeCopy.Parent_id = this.Key_id;
            }
            this.PendingHazardChangeCopy.Is_active = true;        
        }
    }
}

// inherit from GenericModel
extend(Hazard, GenericModel);
