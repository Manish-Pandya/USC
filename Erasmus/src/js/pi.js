var pi = $$({id:123}, '<p>Name: <span data-bind="name"/></p>', '& span {background:blue; color:white; padding:3px 6px;}');

// Initialize plugin with RESTful adapter, load model with above id:
pi.persist($$.adapter.restful, {collection:'people'}).load();

$$.document.append(person);