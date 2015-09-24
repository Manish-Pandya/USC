'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyUseCycle = function() {};
CarboyUseCycle.prototype = {
    className: "CarboyUseCycle",

    eagerAccessors:[
        {method:"loadCarboy", boolean: 'Carboy_id'},
        {method:"loadPrincipal_investigator", boolean: 'Principal_investigator_id'},
    ],

    CarboyReadingAmountsRelationship: {

        className:    'CarboyReadingAmount',
        keyReference:  'Carboy_use_cycle_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    // TODO eager accessors, relationships, method names.
    loadCarboy:function(){
        if(!this.Carboy){
            dataLoader.loadChildObject(this, 'Carboy', 'Carboy', this.Carboy_id);
        }
    },

    loadCarboy_reading_amounts:function(){
        dataLoader.loadOneToManyRelationship( this, 'Carboy_reading_amounts', this.CarboyReadingAmountsRelationship);
    },

    loadPrincipal_investigator: function(){
        if(!this.Principal_investigator)dataLoader.loadChildObject(this, "Principal_investigator", "PrincipalInvestigator", this.Principal_investigator_id);
    }
}

// inherit from GenericModel
extend(CarboyUseCycle, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboyUseCycle", [])
    .value("CarboyUseCycle", Carboy);

