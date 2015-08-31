'use strict';

//constructor
var Room = function() {};
Room.prototype = {
    className: "Room",

    eagerAccessors:[
        {method:"loadPendingRoomChange", boolean:'Name'}
    ],

    loadPendingRoomChange: function()
    {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PendingRoomChange) {
            this.PendingRoomChange = dataStoreManager.getChildByParentProperty("PendingRoomChange", "Parent_id", this.Key_id);
        }
    }
}

// inherit from GenericModel
extend(Room, GenericModel);
