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
    Child.prototype[prop] = Parent.prototype[prop];
  }

  //If a child has a className property, autoset its Class
  if(Child.prototype.className && !Child.prototype.Class)Child.prototype.Class = Child.prototype.className;

  return Child;
  
}
