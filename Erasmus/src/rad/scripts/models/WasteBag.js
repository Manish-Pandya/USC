'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var WasteBag = function() {};
WasteBag.prototype = {

    eagerAccessors:[
        {method:'loadContainerName', boolean:"Container_id"}
    ],

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
    },

    loadContainerName:function(){
        var container = dataStoreManager.getById('SolidsContainer', this.Container_id);
        this.ContainerName = container.Name;
    }

}

// inherit from GenericModel
extend(WasteBag, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("wastebag", [])
    .value("WasteBag", WasteBag);

