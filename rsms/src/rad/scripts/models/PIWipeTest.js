'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var PIWipeTest = function () { };
PIWipeTest.prototype = {
    eagerAccessors: [
        { method: "loadPIWipes", boolean: 'Key_id' }
    ],
    PIWIpesRelationship: {
        className: 'PIWipe',
        keyReference: 'Pi_wipe_test_id',
        paramValue: 'Key_id',
        queryParam: ''
    },

    loadPIWipes: function () {
        console.log(this.PIWipes);
        dataLoader.loadOneToManyRelationship(this, "PIWipes", this.PIWIpesRelationship);
    }
}

// inherit from GenericModel
extend(PIWipeTest, GenericModel);