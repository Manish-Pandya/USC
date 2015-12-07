'use strict';
/* Auto-generated stub file for the BioSafetyCabinet class. */

//constructor
var BioSafetyCabinet = function() {};
BioSafetyCabinet.prototype = {
    
    
    
    loadRoom:  function() {
        if(!this.Room && this.Room_id) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }
    },
    loadPI:  function() {
        if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject( this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id );
        }
    },

}

// inherit from GenericModel
extend(BioSafetyCabinet, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("bioSafetyCabinet", [])
    .value("BioSafetyCabinet", BioSafetyCabinet);
