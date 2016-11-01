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

    DrumWipeTestRelationship: {

        className: 'DrumWipeTest',
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'

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
    },
    loadDrumWipeTest: function () {
        dataLoader.loadOneToManyRelationship(this, 'Wipe_test', this.DrumWipeTestRelationship);
    }
}

// inherit from GenericModel
extend(Drum, GenericModel);

