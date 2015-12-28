'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var BiosafetyProtocol = function(){};

BiosafetyProtocol.prototype = {
    eagerAccessors: [
        {method:"loadPI", boolean:"Principal_investigator_id"},
        {method:"loadDepartment", boolean:"Department_id"},
        {method:"loadHazard", boolean:"Hazard_id"},

    ],

    loadPI:  function() {
        if(!this.PrincipalInvestigator && this.User_id) {
            dataLoader.loadChildObject( this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id );
        }
    },

    loadDepartment:  function() {
        if(!this.Department && this.Department_id) {
            dataLoader.loadChildObject( this, 'Department', 'Department', this.Department_id );
        }
    },

    loadHazard:  function() {
        if(!this.Hazard && this.Hazard_id) {
            dataLoader.loadChildObject( this, 'Hazard', 'Hazard', this.Hazard_id );
        }
    }

}

//inherit from and extend GenericPrincipalInvestigator
extend(BiosafetyProtocol, GenericModel);
