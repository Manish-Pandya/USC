'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Inspection = function(){};
Inspection.prototype = {
    Class: "Inspection"
}

//inherit from and extend GenericModel
extend(Inspection, GenericModel);
