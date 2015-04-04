'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var ScintVialCollection = function(){};

ScintVialCollection.prototype = {
    className: "ScintVialCollection",

}

//inherit from and extend GenericModel
extend(ScintVialCollection, GenericModel);


//create an angular module for the model, so it can be injected downstream
angular
	.module("ScintVialCollection",[])
	.value("ScintVialCollection",ScintVialCollection);

