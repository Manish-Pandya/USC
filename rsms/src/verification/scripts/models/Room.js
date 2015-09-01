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
        if(!this.PendingRoomChange) {
            this.PendingRoomChange = dataStoreManager.getChildByParentProperty("PendingRoomChange", "Parent_id", this.Key_id);

            if(this.PendingRoomChange){
                this.PendingRoomChangeCopy = dataStoreManager.createCopy(this.PendingRoomChange)
            }else{
                this.PendingRoomChangeCopy = this.inflator.instantiateObjectFromJson(new window.PendingRoomChange());
                if(!this.PendingRoomChangeCopy.hasOwnProperty("Parent_class"))this.PendingRoomChangeCopy.Parent_class = "Room";
                this.PendingRoomChangeCopy.Parent_id = this.Key_id;
            }
        }
    }
}

// inherit from GenericModel
extend(Room, GenericModel);
