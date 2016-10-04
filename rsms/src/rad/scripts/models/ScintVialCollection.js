'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var ScintVialCollection = function(){};

ScintVialCollection.prototype = {
    className: "ScintVialCollection",
    loadPickup: function() {
        if(!this.Pickup && this.Pickup_id) {
            dataLoader.loadChildObject(this, 'Pickup', 'Pickup', this.Pickup_id);
        }
    },


}

//inherit from and extend GenericModel
extend(ScintVialCollection, GenericModel);