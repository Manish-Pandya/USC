'use strict';

//THESE FUNCTIONS WILL " 'ENABLE' 'CLASSICAL' 'INHERITANCE' "

//set up inheritance for child classes

function extend( Child, Parent ) {
  Child = inherit( Child, Parent )
  Child.prototype.constructor = Child
  Child.parent = Parent.prototype

}

function inherit( Child, Parent ) {

  for (var prop in Parent.prototype) {
    // Skip overwriting child props that already exist in the child,
    // as they should trump the parent's version of that prop...
    // you know... for inheritance goodness
    // Otherwise, push the Parent prop to the child

    if (Child.prototype[prop] == null) {
        Child.prototype[prop] = Parent.prototype[prop];
    }

  }

  //If a child has a className property, autoset its Class
  if(Child.prototype.className && !Child.prototype.Class)Child.prototype.Class = Child.prototype.className;

  return Child;

}
