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
        }
    }
}

// inherit from GenericModel
extend(Hazard, GenericModel);
