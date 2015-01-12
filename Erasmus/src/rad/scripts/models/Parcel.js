'use strict';

//constructor
var Parcel = function() {};
Parcel.prototype = {

    // TODO eager accessors, relationships, method names

}

// inherit from GenericModel
extend(Parcel, GenericModel);

angular
    .module("parcel", [])
    .value("Parcel", Parcel);
