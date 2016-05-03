'use strict';
/* Auto-generated stub file for the EquipmentInspection class. */


//constructor
var EquipmentInspection = function() {};
EquipmentInspection.prototype = {
    
    Room_id: 0,
    Principal_investigator_id: 0,
    Certification_date: 0,
    Due_date: 0,
    Report_path: 0,
    Equipment_id: 0,
    Equipment_class: "thing",
    
    eagerAccessors:[
		{method:"loadPrincipalInvestigator", boolean:"Principal_investigator_id"},
        {method:"loadRoom", boolean:"Room_id"},
	],
    
	loadPrincipalInvestigator: function() {
        if(!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
	},
    
	loadRoom: function () {
        if(!this.Room && this.Room_id) {
            dataLoader.loadChildObject( this, 'Room', 'Room', this.Room_id );
        }
    },
}

// inherit from GenericModel
extend(EquipmentInspection, GenericModel);
