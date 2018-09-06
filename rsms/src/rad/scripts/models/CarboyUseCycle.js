'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyUseCycle = function() {};
CarboyUseCycle.prototype = {
    className: "CarboyUseCycle",

    eagerAccessors:[
        { method: "loadCarboy", boolean: 'Carboy_id' },
        { method: "loadRoom", boolean: 'Room_id' },
    ],

    CarboyReadingAmountsRelationship: {

        className:    'CarboyReadingAmount',
        keyReference:  'Carboy_use_cycle_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    // TODO eager accessors, relationships, method names.
    loadCarboy: function () {
        dataLoader.loadChildObject(this, 'Carboy', 'Carboy', this.Carboy_id);     
    },

    loadDrum: function () {
        dataLoader.loadChildObject(this, 'Drum', 'Drum', this.Drum_id);
    },

    loadCarboy_reading_amounts:function(){
        dataLoader.loadOneToManyRelationship( this, 'Carboy_reading_amounts', this.CarboyReadingAmountsRelationship);
    },

    loadPrincipalInvestigator: function () {
        dataLoader.loadChildObject(this, 'Principal_investigator', 'PrincipalInvestigator', this.Principal_investigator_id);
    },

    loadRoom: function () {
        dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
    }
}

// inherit from GenericModel
extend(CarboyUseCycle, GenericModel);