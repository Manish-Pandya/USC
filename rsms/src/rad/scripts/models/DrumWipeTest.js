'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var DrumWipeTest = function () { };
DrumWipeTest.prototype = {
    className: "DrumWipeTest",
    Class: 'DrumWipeTest',
    eagerAccessors: [
        {
            method: "loadDrum_wipes",
            boolean: 'HasWipes'
        }
    ],

    DrumWipesRelationship: {
        className: 'DrumWipe',
        keyReference: 'Drum_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    loadDrum_wipes: function () {
        this.Drum_wipes = [];
        dataLoader.loadOneToManyRelationship(this, 'Drum_wipes', this.DrumWipesRelationship);
    },

    createWipeTests: function () {
        if (!this.Drum_wipes) this.Drum_wipes = [];
        for (var i = 0; i < 3; i++) {
            var wipe = new window.DrumWipe();
            wipe.Drum_wipe_test_id = this.Key_id ? this.Key : null;
            wipe.Rading_type = "LSC";
            wipe.edit = true;
            wipe.Class = 'DrumWipe';
            this.Drum_wipes.push(wipe);
        }
    },

    addWipe: function () {
        if (!this.Drum_wipes) this.Drum_wipes = [];
        var wipe = this.inflator.instantiateObjectFromJson(new window.DrumWipe());
        wipe.Class = 'DrumWipe';
        wipe.Drum_wipe_test_id = this.Key_id ? this.Key : null;
        wipe.Rading_type = "LSC";
        wipe.edit = true;
        this.Drum_wipes.push(wipe);
        return wipe;
    }
}

// inherit from GenericModel
extend(DrumWipeTest, GenericModel);