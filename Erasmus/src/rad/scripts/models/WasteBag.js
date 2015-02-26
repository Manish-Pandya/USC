'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var WasteBag = function() {};
WasteBag.prototype = {

    loadContainer: function() {
        if(!this.Container && this.Container_id) {
            dataLoader.loadChildObject(this, 'Container', 'SolidsContainer', this.Container_id);
        }
    },

    loadDrum: function() {
        if(!this.Drum && this.Drum_id) {
            dataLoader.loadChildObject(this, 'Drum', 'Drum', this.Drum_id);
        }
    },

    loadPickup: function() {
        if(!this.Pickup && this.Pickup_id) {
            dataLoader.loadChildObject(this, 'Pickup', 'Pickup', this.Pickup_id);
        }
    }

}

// inherit from GenericModel
extend(WasteBag, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("wastebag", [])
    .value("WasteBag", WasteBag);

