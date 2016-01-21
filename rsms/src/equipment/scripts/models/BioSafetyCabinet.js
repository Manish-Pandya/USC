'use strict';
/* Auto-generated stub file for the BioSafetyCabinet class. */

//constructor
var BioSafetyCabinet = function() {};
BioSafetyCabinet.prototype = {
    
    EquipmentInspectionRelationship: {
        className:    'EquipmentInspection',
        keyReference:  'Equipment_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },
    
    eagerAccessors: [        
        {method:"loadEquipmentInspections", boolean:"Key_id"}
    ],
    loadRoom:  function() {
        /*if(!this.Room) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }*/
    },
    loadPI:  function() {
        /*if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject( this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id );
        }*/
    },
    loadEquipmentInspections:  function() {
        dataLoader.loadOneToManyRelationship( this, 'EquipmentInspections', this.EquipmentInspectionRelationship, [{Equipment_class:"BioSafetyCabinet"}]);
    }

}

// inherit from GenericModel
extend(BioSafetyCabinet, GenericModel);