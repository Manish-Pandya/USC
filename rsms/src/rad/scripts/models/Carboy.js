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

    eagerAccessors: [{method:"instantiateCurrentCarboyUseCycle", boolean:'Carboy_use_cycles'}],

    // TODO eager accessors, relationships, method names.
    loadCarboyUseCycles:function(){
        return dataLoader.loadOneToManyRelationship( this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship );
    },

    // TODO eager accessors, relationships, method names.
    instantiateCurrentCarboyUseCycle:function(){
        var i = this.Carboy_use_cycles.length;
        while(i--){
            var cycle = this.Carboy_use_cycles[i];
           //the cycle is the current one if it hasn't been poured
            if(!cycle.Pour_date && (cycle.Status.toLowerCase() == "in use" || cycle.Status.toLowerCase() == "available")){
                console.log(cycle);
                this.Current_carboy_use_cycle = this.inflator.instantiateObjectFromJson(cycle);
                dataStoreManager.store(this.Current_carboy_use_cycle);
            }
        }
    }
}

// inherit from GenericModel
extend(Carboy, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboy", [])
    .value("Carboy", Carboy);

