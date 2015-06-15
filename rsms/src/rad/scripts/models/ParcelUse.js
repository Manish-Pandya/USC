'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var ParcelUse = function() {};
ParcelUse.prototype = {

    loadParcel: function() {
        if(!this.Parcel) {
            dataLoader.loadChildObject(this, 'Parcel', 'Parcel', this.Parcel_id);
        }
    }
}

// inherit from GenericModel
extend(ParcelUse, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("parceluse", [])
    .value("ParcelUse", ParcelUse);

