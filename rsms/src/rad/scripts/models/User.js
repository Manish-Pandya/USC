'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var User = function (api) {
    User.url = "";
    User.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllUsers";
};

User.prototype = {
    className: "User",
	loadSupervisor: function()
	{
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.Supervisor && this.Supervisor_id) {
            dataLoader.loadChildObject(this, 'Supervisor', 'PrincipalInvestigator', this.Supervisor_id);
        }
	}
}

//inherit from and extend GenericModel
extend(User, GenericModel);