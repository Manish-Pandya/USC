'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var ParcelWipeTest = function () {};
ParcelWipeTest.prototype = {
    className: "ParcelWipeTest",
    Class: 'ParcelWipeTest',
    eagerAccessors: [
        {
            method: "loadParcel_wipes",
            boolean: 'Key_id'
        }
    ],

        ParcelWipesRelationship: {
        className: 'ParcelWipe',
        keyReference: 'Parcel_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    loadParcel_wipes: function () {
        this.Parcel_wipes = [];
        dataLoader.loadOneToManyRelationship(this, 'Parcel_wipes', this.ParcelWipesRelationship);
    },

    loadPrincipal_investigator: function () {

    },

    createWipeTests: function () {
        if (!this.Parcel_wipes) this.Parcel_wipes = [];
        for (var i = 0; i < 7; i++) {
            var wipe = new window.ParcelWipe();
            wipe.Parcel_wipe_test_id = this.Key_id ? this.Key : null;
            wipe.Rading_type = "LSC";
            wipe.edit = true;
            wipe.Class = 'ParcelWipe';
            if (i == 0) wipe.Location = "Background";
            this.Parcel_wipes.push(wipe);
        }
    },

    addWipe: function () {
        var wipe = this.inflator.instantiateObjectFromJson(new window.ParcelWipe());
        wipe.Class = 'ParcelWipe';
        wipe.Parcel_wipe_test_id = this.Key_id ? this.Key : null;
        wipe.Rading_type = "LSC";
        wipe.edit = true;
        this.Parcel_wipes.push(wipe);
    }
}

// inherit from GenericModel
extend(ParcelWipeTest, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("parcelWipeTest", [])
    .value("ParcelWipeTest", InspectionWipe);
