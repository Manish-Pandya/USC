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
    
    // TODO eager accessors, relationships, method names.
    loadCarboyUseCycles:function(){
        return dataLoader.loadOneToManyRelationship( this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship );
    }
}

// inherit from GenericModel
extend(Carboy, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("carboy", [])
    .value("Carboy", Carboy);

