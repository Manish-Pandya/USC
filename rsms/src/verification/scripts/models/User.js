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
        if(!this.PendingUserChange) {
            this.PendingUserChange = dataStoreManager.getChildByParentProperty("PendingUserChange", "Parent_id", this.Key_id);

            if(this.PendingUserChange){
                this.PendingUserChangeCopy = dataStoreManager.createCopy(this.PendingUserChange)
            }else{
                this.PendingUserChangeCopy = this.inflator.instantiateObjectFromJson(new window.PendingUserChange());
                if(!this.PendingUserChangeCopy.hasOwnProperty("Parent_class"))this.PendingUserChangeCopy.Parent_class = "User";
                this.PendingUserChangeCopy.Parent_id = this.Key_id;
            }
        }
    }
}

// inherit from GenericModel
extend(User, GenericModel);

