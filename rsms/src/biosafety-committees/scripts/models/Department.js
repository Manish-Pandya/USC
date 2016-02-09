'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Department = function(){};

Department.prototype = {}

//inherit from and extend GenericPrincipalInvestigator
extend(Department, GenericModel);
