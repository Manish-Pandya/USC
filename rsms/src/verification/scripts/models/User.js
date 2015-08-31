'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var User = function() {};
User.prototype = {
    className: "User",

    eagerAccessors:[
        {method:"loadPendingUserChange", boolean:'Name'}
    ],

    loadPendingUserChange: function()
    {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PendingUserChange) {
            this.PendingUserChange = dataStoreManager.getChildByParentProperty("PendingUserChange", "Parent_id", this.Key_id);

            if(this.PendingUserChange){
                this.PendingUserChangeCopy = dataStoreManager.createCopy(this.PendingUserChange)
            }else{
                this.PendingUserChangeCopy = new window.PendingUserChange();
                this.PendingUserChangeCopy.Parent_id = this.Key_id;
            }
        }
    }
}

// inherit from GenericModel
extend(User, GenericModel);

