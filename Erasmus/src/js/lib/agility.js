/*

  Agility.js    
  Copyright (c) Artur B. Adib, 2011
  http://agilityjs.com

  Licensed under the MIT license
  http://www.opensource.org/licenses/mit-license.php

*/

(function(window,undefined){if(!window.jQuery){throw"agility.js: jQuery not found";}
var document=window.document,location=window.location,$=jQuery,agility,util={},defaultPrototype={},idCounter=0,ROOT_SELECTOR='&';if(!Object.create||Object.create.toString().search(/native code/i)<0){Object.create=function(obj){var Aux=function(){};$.extend(Aux.prototype,obj);return new Aux();};}
if(!Object.getPrototypeOf||Object.getPrototypeOf.toString().search(/native code/i)<0){if(typeof"test".__proto__==="object"){Object.getPrototypeOf=function(object){return object.__proto__;};}else{Object.getPrototypeOf=function(object){return object.constructor.prototype;};}}
util.isAgility=function(obj){return obj._agility===true;};util.proxyAll=function(obj,dest){if(!obj||!dest){throw"agility.js: util.proxyAll needs two arguments";}
for(var attr1 in obj){var proxied=obj[attr1];if(typeof obj[attr1]==='function'){proxied=obj[attr1]._noProxy?obj[attr1]:$.proxy(obj[attr1]._preProxy||obj[attr1],dest);proxied._preProxy=obj[attr1]._noProxy?undefined:(obj[attr1]._preProxy||obj[attr1]);obj[attr1]=proxied;}
else if(typeof obj[attr1]==='object'){for(var attr2 in obj[attr1]){var proxied2=obj[attr1][attr2];if(typeof obj[attr1][attr2]==='function'){proxied2=obj[attr1][attr2]._noProxy?obj[attr1][attr2]:$.proxy(obj[attr1][attr2]._preProxy||obj[attr1][attr2],dest);proxied2._preProxy=obj[attr1][attr2]._noProxy?undefined:(obj[attr1][attr2]._preProxy||obj[attr1][attr2]);proxied[attr2]=proxied2;}}
obj[attr1]=proxied;}}};util.reverseEvents=function(obj,eventType){var events=$(obj).data('events');if(events!==undefined&&events[eventType]!==undefined){var reverseEvents=[];for(var e in events[eventType]){if(!events[eventType].hasOwnProperty(e))continue;reverseEvents.unshift(events[eventType][e]);}
events[eventType]=reverseEvents;}};util.size=function(obj){var size=0,key;for(key in obj){size++;}
return size;};util.extendController=function(object){for(var controllerName in object.controller){(function(){var matches,extend,eventName,previousHandler,currentHandler,newHandler;if(typeof object.controller[controllerName]==='function'){matches=controllerName.match(/^(\~)*(.+)/);extend=matches[1];eventName=matches[2];if(!extend)return;previousHandler=object.controller[eventName]?(object.controller[eventName]._preProxy||object.controller[eventName]):undefined;currentHandler=object.controller[controllerName];newHandler=function(){if(previousHandler)previousHandler.apply(this,arguments);if(currentHandler)currentHandler.apply(this,arguments);};object.controller[eventName]=newHandler;delete object.controller[controllerName];}})();}};defaultPrototype={_agility:true,_container:{_insertObject:function(obj,selector,method){var self=this;if(!util.isAgility(obj)){throw"agility.js: append argument is not an agility object";}
this._container.children[obj._id]=obj;this.trigger(method,[obj,selector]);obj._parent=this;obj.bind('destroy',function(event,id){self._container.remove(id);});return this;},append:function(obj,selector){return this._container._insertObject.call(this,obj,selector,'append');},prepend:function(obj,selector){return this._container._insertObject.call(this,obj,selector,'prepend');},after:function(obj,selector){return this._container._insertObject.call(this,obj,selector,'after');},before:function(obj,selector){return this._container._insertObject.call(this,obj,selector,'before');},remove:function(id){delete this._container.children[id];this.trigger('remove',id);return this;},each:function(fn){$.each(this._container.children,fn);return this;},empty:function(){this.each(function(){this.destroy();});return this;},size:function(){return util.size(this._container.children);}},_events:{parseEventStr:function(eventStr){var eventObj={type:eventStr},spacePos=eventStr.search(/\s/);if(spacePos>-1){eventObj.type=eventStr.substr(0,spacePos);eventObj.selector=eventStr.substr(spacePos+1);}
return eventObj;},bind:function(eventStr,fn){var eventObj=this._events.parseEventStr(eventStr);if(eventObj.selector){if(eventObj.selector===ROOT_SELECTOR){this.view.$().bind(eventObj.type,fn);}
else{this.view.$().delegate(eventObj.selector,eventObj.type,fn);}}
else{$(this._events.data).bind(eventObj.type,fn);}
return this;},trigger:function(eventStr,params){var eventObj=this._events.parseEventStr(eventStr);if(eventObj.selector){if(eventObj.selector===ROOT_SELECTOR){this.view.$().trigger(eventObj.type,params);}
else{this.view.$().find(eventObj.selector).trigger(eventObj.type,params);}}
else{$(this._events.data).trigger('_'+eventObj.type,params);util.reverseEvents(this._events.data,'pre:'+eventObj.type);$(this._events.data).trigger('pre:'+eventObj.type,params);util.reverseEvents(this._events.data,'pre:'+eventObj.type);$(this._events.data).trigger(eventObj.type,params);if(this.parent())
this.parent().trigger((eventObj.type.match(/^child:/)?'':'child:')+eventObj.type,params);$(this._events.data).trigger('post:'+eventObj.type,params);}
return this;}},model:{set:function(arg,params){var self=this;var modified=[];if(typeof arg==='object'){var _clone=false;if(params&&params.reset){_clone=this.model._data;this.model._data=$.extend({},arg);}
else{$.extend(this.model._data,arg);}
for(var key in arg){delete _clone[key];modified.push(key);}
for(key in _clone){modified.push(key);}}
else{throw"agility.js: unknown argument type in model.set()";}
if(params&&params.silent===true)return this;this.trigger('change');$.each(modified,function(index,val){self.trigger('change:'+val);});return this;},get:function(arg){if(arg===undefined){return this.model._data;}
if(typeof arg==='string'){return this.model._data[arg];}
throw'agility.js: unknown argument for getter';},reset:function(){this.model.set(this.model._initData,{reset:true});return this;},size:function(){return util.size(this.model._data);},each:function(fn){$.each(this.model._data,fn);return this;}},view:{format:'<div/>',style:'',$:function(selector){return(!selector||selector===ROOT_SELECTOR)?this.view.$root:this.view.$root.find(selector);},render:function(){if(this.view.format.length===0){throw"agility.js: empty format in view.render()";}
if(this.view.$root.size()===0){this.view.$root=$(this.view.format);}
else{this.view.$root.html($(this.view.format).html());}
if(this.view.$root.size()===0){throw'agility.js: could not generate html from format';}
return this;},_parseBindStr:function(str){var obj={key:null,attr:[]},pairs=str.split(','),regex=/([a-zA-Z0-9_\-]+)(?:[\s=]+([a-zA-Z0-9_\-]+))?/,keyAssigned=false,matched;if(pairs.length>0){for(var i=0;i<pairs.length;i++){matched=pairs[i].match(regex);if(matched){if(typeof(matched[2])==="undefined"||matched[2]===""){if(keyAssigned){throw new Error("You may specify only one key ("+
keyAssigned+" has already been specified in data-bind="+
str+")");}else{keyAssigned=matched[1];obj.key=matched[1];}}else{obj.attr.push({attr:matched[1],attrVar:matched[2]});}}}}
return obj;},bindings:function(){var self=this;var $rootNode=this.view.$().filter('[data-bind]');var $childNodes=this.view.$('[data-bind]');var createAttributePairClosure=function(bindData,node,i){var attrPair=bindData.attr[i];return function(){node.attr(attrPair.attr,self.model.get(attrPair.attrVar));};};$rootNode.add($childNodes).each(function(){var $node=$(this);var bindData=self.view._parseBindStr($node.data('bind'));var bindAttributesOneWay=function(){if(bindData.attr){for(var i=0;i<bindData.attr.length;i++){self.bind('_change:'+bindData.attr[i].attrVar,createAttributePairClosure(bindData,$node,i));}}};if($node.is('input:checkbox')){self.bind('_change:'+bindData.key,function(){$node.prop("checked",self.model.get(bindData.key));});$node.change(function(){var obj={};obj[bindData.key]=$(this).prop("checked");self.model.set(obj);});bindAttributesOneWay();}
else if($node.is('select')){self.bind('_change:'+bindData.key,function(){var nodeName=$node.attr('name');var modelValue=self.model.get(bindData.key);$node.val(modelValue);});$node.change(function(){var obj={};obj[bindData.key]=$node.val();self.model.set(obj);});bindAttributesOneWay();}
else if($node.is('input:radio')){self.bind('_change:'+bindData.key,function(){var nodeName=$node.attr('name');var modelValue=self.model.get(bindData.key);$node.siblings('input[name="'+nodeName+'"]').filter('[value="'+modelValue+'"]').prop("checked",true);});$node.change(function(){if(!$node.prop("checked"))return;var obj={};obj[bindData.key]=$node.val();self.model.set(obj);});bindAttributesOneWay();}
else if($node.is('input[type="search"]')){self.bind('_change:'+bindData.key,function(){$node.val(self.model.get(bindData.key));});$node.keypress(function(){setTimeout(function(){var obj={};obj[bindData.key]=$node.val();self.model.set(obj);},50);});bindAttributesOneWay();}
else if($node.is('input:text, textarea')){self.bind('_change:'+bindData.key,function(){$node.val(self.model.get(bindData.key));});$node.change(function(){var obj={};obj[bindData.key]=$(this).val();self.model.set(obj);});bindAttributesOneWay();}
else{if(bindData.key){self.bind('_change:'+bindData.key,function(){if(self.model.get(bindData.key)){$node.text(self.model.get(bindData.key).toString());}else{$node.text('');}});}
bindAttributesOneWay();}});return this;},sync:function(){var self=this;this.model.each(function(key,val){self.trigger('_change:'+key);});if(this.model.size()>0){this.trigger('_change');}
return this;},stylize:function(){var objClass,regex=new RegExp(ROOT_SELECTOR,'g');if(this.view.style.length===0||this.view.$().size()===0){return;}
if(this.view.hasOwnProperty('style')){objClass='agility_'+this._id;var styleStr=this.view.style.replace(regex,'.'+objClass);$('head',window.document).append('<style type="text/css">'+styleStr+'</style>');this.view.$().addClass(objClass);}
else{var ancestorWithStyle=function(object){while(object!==null){object=Object.getPrototypeOf(object);if(object.view.hasOwnProperty('style'))
return object._id;}
return undefined;};var ancestorId=ancestorWithStyle(this);objClass='agility_'+ancestorId;this.view.$().addClass(objClass);}
return this;}},controller:{_create:function(event){this.view.stylize();this.view.bindings();this.view.sync();},_destroy:function(event){this._container.empty();this.view.$().remove();},_append:function(event,obj,selector){this.view.$(selector).append(obj.view.$());},_prepend:function(event,obj,selector){this.view.$(selector).prepend(obj.view.$());},_before:function(event,obj,selector){if(!selector)throw'agility.js: _before needs a selector';this.view.$(selector).before(obj.view.$());},_after:function(event,obj,selector){if(!selector)throw'agility.js: _after needs a selector';this.view.$(selector).after(obj.view.$());},_remove:function(event,id){},'_change':function(event){}},destroy:function(){this.trigger('destroy',this._id);},parent:function(){return this._parent;},append:function(){this._container.append.apply(this,arguments);return this;},prepend:function(){this._container.prepend.apply(this,arguments);return this;},after:function(){this._container.after.apply(this,arguments);return this;},before:function(){this._container.before.apply(this,arguments);return this;},remove:function(){this._container.remove.apply(this,arguments);return this;},size:function(){return this._container.size.apply(this,arguments);},each:function(){return this._container.each.apply(this,arguments);},empty:function(){return this._container.empty.apply(this,arguments);},bind:function(){this._events.bind.apply(this,arguments);return this;},trigger:function(){this._events.trigger.apply(this,arguments);return this;}};agility=function(){var args=Array.prototype.slice.call(arguments,0),object={},prototype=defaultPrototype;if(typeof args[0]==="object"&&util.isAgility(args[0])){prototype=args[0];args.shift();}
object=Object.create(prototype);object.model=Object.create(prototype.model);object.view=Object.create(prototype.view);object.controller=Object.create(prototype.controller);object._container=Object.create(prototype._container);object._events=Object.create(prototype._events);object._id=idCounter++;object._parent=null;object._events.data={};object._container.children={};object.view.$root=$();object.model._data=prototype.model._data?$.extend(true,{},prototype.model._data):{};object._data=prototype._data?$.extend(true,{},prototype._data):{};if(args.length===0){}
else if(args.length===1&&typeof args[0]==='object'&&(args[0].model||args[0].view||args[0].controller)){for(var prop in args[0]){if(prop==='model'){$.extend(object.model._data,args[0].model);}
else if(prop==='view'){$.extend(object.view,args[0].view);}
else if(prop==='controller'){$.extend(object.controller,args[0].controller);util.extendController(object);}
else{object[prop]=args[0][prop];}}}
else{if(typeof args[0]==='object'){$.extend(object.model._data,args[0]);}
else if(args[0]){throw"agility.js: unknown argument type (model)";}
if(typeof args[1]==='string'){object.view.format=args[1];}
else if(typeof args[1]==='object'){$.extend(object.view,args[1]);}
else if(args[1]){throw"agility.js: unknown argument type (view)";}
if(typeof args[2]==='string'){object.view.style=args[2];args.splice(2,1);}
if(typeof args[2]==='object'){$.extend(object.controller,args[2]);util.extendController(object);}
else if(args[2]){throw"agility.js: unknown argument type (controller)";}}
object.model._initData=$.extend({},object.model._data);util.proxyAll(object,object);object.view.render();var bindEvent=function(ev,handler){if(typeof handler==='function'){object.bind(ev,handler);}};for(var eventStr in object.controller){var events=eventStr.split(';');var handler=object.controller[eventStr];$.each(events,function(i,ev){ev=ev.trim();bindEvent(ev,handler);});}
object.trigger('create');return object;};agility.document=agility({view:{$:function(selector){return selector?$(selector,'body'):$('body');}},controller:{_create:function(){}}});agility.fn=defaultPrototype;agility.isAgility=function(obj){if(typeof obj!=='object')return false;return util.isAgility(obj);};window.agility=window.$$=agility;agility.fn.persist=function(adapter,params){var id='id';this._data.persist=$.extend({adapter:adapter},params);this._data.persist.openRequests=0;if(params&&params.id){id=params.id;}
this.save=function(){var self=this;if(this._data.persist.openRequests===0){this.trigger('persist:start');}
this._data.persist.openRequests++;this._data.persist.adapter.call(this,{type:this.model.get(id)?'PUT':'POST',id:this.model.get(id),data:this.model.get(),complete:function(){self._data.persist.openRequests--;if(self._data.persist.openRequests===0){self.trigger('persist:stop');}},success:function(data,textStatus,jqXHR){if(data[id]){self.model.set({id:data[id]},{silent:true});}
else if(jqXHR.getResponseHeader('Location')){self.model.set({id:jqXHR.getResponseHeader('Location').match(/\/([0-9]+)$/)[1]},{silent:true});}
self.trigger('persist:save:success');},error:function(){self.trigger('persist:error');self.trigger('persist:save:error');}});return this;};this.load=function(){var self=this;if(this.model.get(id)===undefined)throw'agility.js: load() needs model id';if(this._data.persist.openRequests===0){this.trigger('persist:start');}
this._data.persist.openRequests++;this._data.persist.adapter.call(this,{type:'GET',id:this.model.get(id),complete:function(){self._data.persist.openRequests--;if(self._data.persist.openRequests===0){self.trigger('persist:stop');}},success:function(data,textStatus,jqXHR){self.model.set(data);self.trigger('persist:load:success');},error:function(){self.trigger('persist:error');self.trigger('persist:load:error');}});return this;};this.erase=function(){var self=this;if(this.model.get(id)===undefined)throw'agility.js: erase() needs model id';if(this._data.persist.openRequests===0){this.trigger('persist:start');}
this._data.persist.openRequests++;this._data.persist.adapter.call(this,{type:'DELETE',id:this.model.get(id),complete:function(){self._data.persist.openRequests--;if(self._data.persist.openRequests===0){self.trigger('persist:stop');}},success:function(data,textStatus,jqXHR){self.destroy();self.trigger('persist:erase:success');},error:function(){self.trigger('persist:error');self.trigger('persist:erase:error');}});return this;};this.gather=function(proto,method,selectorOrQuery,query){var selector,self=this;if(!proto)throw"agility.js plugin persist: gather() needs object prototype";if(!proto._data.persist)throw"agility.js plugin persist: prototype doesn't seem to contain persist() data";if(query){selector=selectorOrQuery;}
else{if(typeof selectorOrQuery==='string'){selector=selectorOrQuery;}
else{selector=undefined;query=selectorOrQuery;}}
if(this._data.persist.openRequests===0){this.trigger('persist:start');}
this._data.persist.openRequests++;proto._data.persist.adapter.call(proto,{type:'GET',data:query,complete:function(){self._data.persist.openRequests--;if(self._data.persist.openRequests===0){self.trigger('persist:stop');}},success:function(data){$.each(data,function(index,entry){var obj=$$(proto,entry);if(typeof method==='string'){self[method](obj,selector);}});self.trigger('persist:gather:success',{data:data});},error:function(){self.trigger('persist:error');self.trigger('persist:gather:error');}});return this;};return this;};agility.adapter={};agility.adapter.restful=function(_params){var params=$.extend({dataType:'json',url:(this._data.persist.baseUrl||'api/')+this._data.persist.collection+(_params.id?'/'+_params.id:'')},_params);$.ajax(params);};})(window);