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
            console.log(dataStore);
        }
    }
}

// inherit from GenericModel
extend(User, GenericModel);

