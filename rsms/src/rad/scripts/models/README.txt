Class Blueprint:

1. 'use strict';

2. Constructor:
var ClassName = function() {};

Add everything else from this point on to ClassName.prototype:
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

Note: above define how Relationships are used to describe many to many relationships.
    In one to many relationships, methodString and paramName are left unused and empty.
		

5. Loaders
	example:

	loadSubEntity: function() {
			if(!this.SubEntity) {
			    dataLoader.getChildObject( this, 'PropertyName', this.EntityRelationship );
			}
	}

('PropertyName' - name of the property on this object for the result to be assigned to.
 EntityRelationship - defined in #4.)


6. Inherit from and extend GenericModel

extend(className, GenericModel);