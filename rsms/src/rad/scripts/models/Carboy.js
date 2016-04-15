'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var Carboy = function() {};
Carboy.prototype = {

    CarboyUseCyclesRelationship: {
        className:    'CarboyUseCycle',
        keyReference:  'Carboy_id',
        methodString:  '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    eagerAccessors:[
        {method:"loadCurrentCarboyUseCycle", boolean:"Current_carboy_use_cycle"}
    ],
    
    // TODO eager accessors, relationships, method names.
    loadCarboyUseCycles:function(){
        return dataLoader.loadOneToManyRelationship( this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship );
    },

    //if this carboy has a current use cycle, make sure it is a reference to the appropriate on in the dataStore
    loadCurrentCarboyUseCycle: function () {
        if (this.Current_carboy_use_cycle && dataStoreManager && dataStore.CarboyUseCycle) {
            this.Current_carboy_use_cycle = dataStoreManager.getById("CarboyUseCycle", this.Current_carboy_use_cycle.Key_id);
        }
    }
}

// inherit from GenericModel
extend(Carboy, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboy", [])
    .value("Carboy", Carboy);

