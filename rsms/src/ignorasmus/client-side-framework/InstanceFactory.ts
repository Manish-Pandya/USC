////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

//abstract specifies singleton in ts 1.x (ish)
abstract class InstanceFactory {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static _classNames: string[];

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    // Gets array of class names from script tags with src containing the provided basePath.
    static getClassNames(basePath: string = ""): string[] {
        if (!this._classNames) {
            this._classNames = [];
            var scripts: NodeListOf<HTMLScriptElement> = document.getElementsByTagName('script');
            if (scripts && scripts.length > 0) {
                for (var i in scripts) {
                    if (scripts[i].src && scripts[i].src.indexOf(basePath) > -1) {
                        var pathArray: string[] = scripts[i].src.split("/");
                        var className: string = pathArray.pop().split(".")[0];
                        if (this._classNames.indexOf(className) == -1) {
                            this._classNames.push(className);
                            //init DataStoreManager holders
                            DataStoreManager.ActualModel[className] = {};
                            DataStoreManager.ActualModel[className].getAllPromise = new Promise(function(){}, function(){});
                            console.log(DataStoreManager.ActualModel[className]);
                        }
                    }
                }
            }
        }
        return this._classNames;
    }

    // Creates class instance if possible
    static createInstance(className: string): any {
        if (this._classNames && this._classNames.indexOf(className) > -1) {
            return new window[className]();
        } else if (window[className]) {
            console.log("Class not in approved ClassNames, but exists. Trying to create...");
            return new window[className]();
        } else {
            //console.log("No such class as " + className);
            return null;
        }
    }

    // Crawls through data and its children, creating class instances as needed.
    static convertToClasses(data: any): any {
        if (data && data[0] && data[0][DataStoreManager.classPropName]) {
            var instance: any = InstanceFactory.createInstance(data[0].Class);
            InstanceFactory.copyProperties(instance, data[0]);
            instance.onFulfill();
            for (var instanceProp in instance) {
                if (instance[instanceProp] instanceof CompositionMapping) {
                    //console.log("dig:", instanceProp);
                    console.log("the scoped CompositionMapping should be", compMap);

                    var compMap: CompositionMapping = instance[instanceProp];
                    // Do it here
                    if (compMap.callGetAll) {
                        //console.log("oh shit, boi wattup");

                        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
                            //TODO:  store this promise somewhere way up in scope
                            /*Promise.all([DataStoreManager.getAll(compMap.ChildType, []), compMap]).then((d) => {
                                this.getChildInstances(d[1], instance);
                            })*/
                            //TODO:  SECOND INDEX is a reference to compMap in the parent scope on the correct index of this loop, but we are insane for passing it this way
                            Promise.all([DataStoreManager.getAll(compMap.ChildType, []), compMap]).then((d) => {
                                this.getChildInstances(d[1], instance);
                            })

                        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
                            var getGerunds = XHR.GET(compMap.ChildUrl);
                            Promise.all([getGerunds, DataStoreManager.getAll(compMap.ChildType, []), compMap]).then((d) => {
                                //console.log(DataStoreManager.ActualModel)
                                //console.log("the scoped comp map in many to many is", d[2]);

                                this.getChildInstances(d[2], instance);
                            })
                        }
                    }

                }
            }
        }

        var drillDown = (parentNode: any): void => {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance: any = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            instance.onFulfill();
                            // Run composition routine here based on instance's CompositionMapping //
                           
                            // set instance
                            parentNode[prop] = instance;
                        }
                    }
                    drillDown(parentNode[prop]);
                }
            }
        }
        drillDown(data);
        return data;
    }

    static getChildInstances(compMap: CompositionMapping, parent: any): void {
        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {

            if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
            var len: number = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
            for (let i: number = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                    //perhaps use a DataStore manager method that leverages findByPropValue here
                    parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                }
            }
                
            
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            var stamp = new Date().getMilliseconds();
            var len: number = DataStoreManager.ActualModel[parent.Class].Data.length;
            var otherLen: number = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
            for (let i: number = 0; i < len; i++) {
                for (let j: number = 0; j < otherLen; j++) {
                    var test = true;
                    console.log(test);
                }
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                /*
                if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                    //perhaps use a DataStore manager method that leverages findByPropValue here
                    parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                }*/
            }
            var stamp2 = new Date().getMilliseconds();
            console.log(stamp2 - stamp);
            return;


           if (!DataStoreManager[compMap.ChildType] || !DataStoreManager[compMap.ChildType].getAllCalled || !DataStoreManager[compMap.ChildType].Data) {
                if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
                //Get the gerunds.then
                var manyTypeToManyChildType: string = parent.TypeName + "To" + compMap.ChildType;
                if (typeof DataStoreManager.ActualModel[manyTypeToManyChildType] == "undefined" || !DataStoreManager.ActualModel[manyTypeToManyChildType].promise) {
                    DataStoreManager.ActualModel[manyTypeToManyChildType] = {};
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise = XHR.GET(compMap.GerundUrl)
                        .then(function (d) {
                            DataStoreManager.ActualModel[manyTypeToManyChildType].stuff = d;
                            //find relevant gerunds for this parent instance

                        })
                        .catch((f) => {
                            console.log(f);
                        })

                } else {
                    DataStoreManager.ActualModel[manyTypeToManyChildType].promise.then(function (d) {
                        //find relevant gerunds for this parent instance
                    })
                }
                parent["test"] = "l;aksjfl;akjsdlf";
                if (DataStoreManager.ActualModel[manyTypeToManyChildType].stuff) {
                    var len: number = DataStoreManager.ActualModel[manyTypeToManyChildType].stuff.length;
                    console.log(parent.Key_id);
                    return;
                    for (let i = 0; i < len; i++) {
                        if (DataStoreManager.ActualModel[manyTypeToManyChildType].stuff[i][compMap.DEFAULT_MANY_TO_MANY_PARENT_ID] == parent.UID) {
                            compMap.LinkingMaps.push(DataStoreManager.ActualModel[manyTypeToManyChildType].stuff[i]);
                        }
                        console.log(parent.Class, parent.UID, compMap.LinkingMaps)
                        /*
                        for (let n = 0; n < compMap.LinkingMaps.length; n++) {
                            var mapping = compMap.LinkingMaps[n];
                            if (mapping[compMap.DEFAULT_MANY_TO_MANY_PARENT_ID] == parent.UID) {
                                if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
                                console.log("matched");
                                //parent[compMap.PropertyName].push(DataStoreManager.getById(compMap.ChildType, 1));
                                //DataStoreManager.getById(parent.TypeName, mapping[compMap.DEFAULT_MANY_TO_MANY_CHILD_ID], parent[compMap.PropertyName]);
                            }
                        }
                        */
                    }
                }
            }

            /*
            $.getJSON(DataStoreManager.baseUrl + window[compMap.ChildType].urlMapping.urlGetAll)
                .done(function (d) {
                    d = InstanceFactory.convertToClasses(d);
                    //DIG:  DataStoreManager._actualModel[compMap.ChildType].Data is the holder for the actual data of this type.
                    //Time to decide for sure.  Do we have a seperate hashmap object, is Data a mapped object, or do we not need the performance boost of mapping at all?
                    DataStoreManager.ActualModel[compMap.ChildType].Data = d;
					parent[compMap.PropertyName].splice(0, parent[compMap.PropertyName].length);
					// Dig this neat way to use parent[compMap.PropertyName] as a reference instead of a value!
					Array.prototype.push.apply(parent[compMap.PropertyName], _.cloneDeep(d));
                })
                .fail(function (d) {
                    console.log("dang... getJSON failed:", d.statusText);
                })
               

            if (compMap.callGetAll) {
                if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
                DataStoreManager.getAll(compMap.ChildType, parent[compMap.PropertyName]).then(function () {
                    var len: number = DataStoreManager.ActualModel[compMap.ChildType].Data.length;
                    for (let i: number = 0; i < len; i++) {
                        //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                        if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                            //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                            //perhaps use a DataStore manager method that leverages findByPropValue here
                            parent[compMap.PropertyName].push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                        }
                    }
                })
            } else {
                //get the matching subset
            }
             */
            //local method for finding relevant children

        } else {
            var childInstance = this.createInstance(compMap.ChildType);
            childInstance.Email = "foo@yoo.poo";
        }
    }

    // Copies properties/values from sources to target.
    // It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy test pending."
    static copyProperties(target: any, ...sources: any[]): any {
        sources.forEach(source => {
            Object.defineProperties(
                target,
                Object.getOwnPropertyNames(source).reduce(
                    (descriptors: { [index: string]: any }, key: string) => {
                        descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
                        /*
                        PUMP IN OBSERVERS AT GRANULAR LEVEL HERE BECAUSE IT WILL BE PERFORMANT EVEN IF DAVID DOESN'T THINK WE NEED IT TO BE
                        if (!actualModelThing.flatWatcherMap.indexOf(viewModelThing.uid)) {
                                actualModelThing.
                                actualModelThing.Watchers.push();
                        }
                        */
                        return descriptors;
                    },
                    {}
                )
            );
        });
        return target;
    }

    /*static affixWatchers(): void {
        var masterPi = new PrincipalInvestigator();
        masterPi.testProperty = "test";

        masterPi.observers = [];

        masterPi.testProperty = "updated";
        var i = 0;
        var childPis = [];
        for (i; i < 10000; i++) {
            childPis[i] = new PrincipalInvestigator();
            masterPi.observers[i] = childPis[i];
           // childPis[i].watcher = masterPi.watch();
        }
        masterPi.watch("testProperty", function (it, oldValue, newValue) {
            console.log(it, oldValue, newValue);
            for (var i = 0; i < masterPi.observers.length; i++) {
                masterPi.observers[i]["testProperty"] = masterPi["testProperty"];
            }
        })
        masterPi.testProperty = "updated";
        console.log(childPis[100].testProperty);
    }*/

}