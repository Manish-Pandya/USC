'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var Drum = function() {};
Drum.prototype = {

    WasteBagsRelationship: {
        className: 'WasteBag', 
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    SVCollectionRelationship: {
        className: 'ScintVialCollection',
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    loadWasteBags: function() {
        if(!this.WasteBags) {
            dataLoader.loadOneToManyRelationship(this, 'WasteBags', this.WasteBagsRelationship);
        }
    },
    loadScintVialCollections: function() {
        if (!this.ScintVialCollections) {
            dataLoader.loadOneToManyRelationship(this, 'ScintVialCollections', this.SVCollectionRelationship);
    }
}

}

// inherit from GenericModel
extend(Drum, GenericModel);

// create an angular module for the model, so it can be injected downstream
angular
    .module("drum", [])
    .value("Drum", Drum);

