//
// To-Do example
// You can replace this file with any of the examples from agilityjs.com...
// ... they work right out of the box!
//

//
// Item prototype
//

var message = $$({
  model: {},
  view: {
    format: $('#my-format').html()
  },
  controller: {}
});
$$.document.append(message);
/*
//
// List of items
//
var list = $$({}, '<div> <button id="new">New item</button> <ul></ul> </div>', {
  'click #new': function(){
    var newItem = $$(item, {content:'Click to edit'});
    this.append(newItem, 'ul'); // add to container, appending at <ul>
  }
});

$$.document.append(list);
/*
// Hello World
var message = $$({
  model: {},
  view: {
    format: $('#my-format').html()
  },
  controller: {}
});
$$.document.append(message);

// Prototype
var person = $$({}, '<li data-bind="collection"/>').persist($$.adapter.restful, {collection:'apitest.php'});

// Container
var people = $$({
  model: {},
  view: {
    format: 
      '<div>\
        <span>Loading ...</span>\
        <button>Load people</button><br/><br/>\
        People: <ul/>\
      </div>',
    style:
      '& {position:relative}\
       & span {position:absolute; top:0; right:0; padding:3px 6px; background:red; color:white; display:none; }'
  }, 
  controller: {
    'click button': function(){
      this.empty();
      this.gather(person, 'append', 'ul');
    },
    'persist:start': function(){
      this.view.$('span').show();
    },
    'persist:stop': function(){
      this.view.$('span').hide();
    }
  }
}).persist();
$$.document.append(people);
*/