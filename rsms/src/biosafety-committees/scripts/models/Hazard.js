'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function(){};

Hazard.prototype = {}

//inherit from and extend GenericPrincipalInvestigator
extend(Hazard, GenericModel);
