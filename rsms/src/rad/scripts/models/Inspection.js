'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Inspection = function() {};
Inspection.prototype = {
    className: "Inspection",

    loadPrincipalInvestigator: function(){
        if(this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
    }
}

// inherit from GenericModel
extend(Inspection, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("inspectionWipe", [])
    .value("Inspection", Inspection);

