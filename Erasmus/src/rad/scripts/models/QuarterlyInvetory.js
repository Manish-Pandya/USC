'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var QuarterlyInventory = function() {};
QuarterlyInventory.prototype = {
    className: "QuarterlyInventory",
    WasteBagsRelationship: {
        className: 'WasteBag', 
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    loadWasteBags: function() {
        if(!this.WasteBags) {
            dataLoader.loadOneToManyRelationship(this, 'WasteBags', this.WasteBagsRelationship);
        }
    }

}

// inherit from GenericModel
extend(QuarterlyInventory, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("quarterlyInventory", [])
    .value("QuarterlyInventory", Drum);

