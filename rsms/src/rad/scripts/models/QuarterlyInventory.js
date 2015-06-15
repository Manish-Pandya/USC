'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var QuarterlyInventory = function() {};
QuarterlyInventory.prototype = {
    className: "QuarterlyInventory",
    PIQaurterlyInventoriesRelationship: {
        className: 'PIQuarterlyInventory', 
        keyReference: 'quarterly_inventory_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    loadPi_quarterly_inventories: function() {
       dataLoader.loadOneToManyRelationship(this, 'Pi_quarterly_inventories', this.PIQaurterlyInventoriesRelationship);
    }

}

// inherit from GenericModel
extend(QuarterlyInventory, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("quarterlyInventory", [])
    .value("QuarterlyInventory", Drum);

