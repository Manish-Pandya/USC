Class Blueprint:

1. 'use strict';

2. Constructor:
var ClassName = function() {};

Add everything else to ClassName.prototype:
Hazard.prototype = {
	.....
}


3. eagerAccessors (if any)
	Array of objects with:

    method: string for method name to call
    boolean: string of property to check.
				(whether or not that property is called will determine whether 
				or not to call that method)
	
(For now, leaving eagerAccessors empty, will add as necessary.)


4. Relationships. Should look like:

	EntityRelationship: {
		className: _____,
		keyReference: _____,
		methodString: _____,
		paramValue: _____,
		paramName: _____,
	},

Definitions:

className - name of the child's class
keyReference - property of the child to be checked
methodString - method to call on the server
paramValue - property of this class to be checked against keyReference
paramName - name of the paramValue property to insert into the request url.
		

5. SaveUrl - String containing server method to call when saving this entity.


6. Getters and Setters
	example:

	getSubEntity: function() {
			if(this.SubEntity) {
				return this.SubEntity;
			}
			else {
				return dataSwitch.getChildObject( this, 'PropertyName', EntityRelationship_;
			}
	}

	setSubEntity: function(newValue) {
			this.SubEntity = newValue;
	}

('PropertyName' - name of the property on this object for the result to be assigned to.
 EntityRelationship - defined in #4.)


7. Inherit from and extend GenericModel

extend(Class, GenericModel);


8. Create an angular module for the model, so it can be injected downstream

angular
    .module("className",[])
	.value("ClassName", Class);

