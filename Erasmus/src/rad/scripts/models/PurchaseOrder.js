'use strict';
/* Auto-generated stub file for the PurchaseOrder class. */

//constructor
var PurchaseOrder = function() {};
PurchaseOrder.prototype = {

    // TODO eager accessors, relationships, method names.

}

// inherit from GenericModel
extend(PurchaseOrder, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("purchaseorder", [])
    .value("PurchaseOrder", PurchaseOrder);

