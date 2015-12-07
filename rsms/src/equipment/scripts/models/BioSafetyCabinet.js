'use strict';
/* Auto-generated stub file for the BioSafetyCabinet class. */

//constructor
var BioSafetyCabinet = function() {};
BioSafetyCabinet.prototype = {
    
    eagerAccessors: [        
        {method:"loadRoom", boolean:"Room_id"},
        {method:"loadPI", boolean:"Principal_investigator_id"}
    ],
    
    loadRoom:  function() {
        if(!this.Room) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }
    },
    loadPI:  function() {
        if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject( this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id );
        }
    }

}

// inherit from GenericModel
extend(BioSafetyCabinet, GenericModel);