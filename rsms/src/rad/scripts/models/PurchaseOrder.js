'use strict';
/* Auto-generated stub file for the PurchaseOrder class. */

//constructor
var PurchaseOrder = function() {};
PurchaseOrder.prototype = {

    loadPrincipalInvestigator: function() {
        if(!this.Principal_investigator) {
            dataLoader.loadChildObject(this, 'Principal_investigator',
                'PrincipalInvestigator', this.Principal_investigator_id);
        }
    }

}

// inherit from GenericModel
extend(PurchaseOrder, GenericModel);