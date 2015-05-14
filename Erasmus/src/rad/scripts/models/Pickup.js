'use strict';
/* Auto-generated stub file for the Pickup class. */

//constructor
var Pickup = function() {};
Pickup.prototype = {
	className: "Pickup",

	eagerAccessors:[
		{method:"loadPrincipalInvestigator", boolean:"Principal_investigator_id"},
	],	

	loadPrincipalInvestigator: function()
	{
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
	}

}

// inherit from GenericModel
extend(Pickup, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("pickup", [])
    .value("Pickup", Pickup);

