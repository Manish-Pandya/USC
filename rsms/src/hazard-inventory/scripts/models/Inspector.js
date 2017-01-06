'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Inspector = function () { };
Inspector.prototype = {
    Class: "Inspector",
    User: {}
}

//inherit from and extend GenericModel
extend(Inspector, GenericModel);
