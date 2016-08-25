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
        var drillDown = (parentNode: any): void => {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance: any = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            instance.onFulfill();
                            // Run composition routine here based on instance's CompositionMapping //
                            for (var instanceProp in instance) {
                                if (instance[instanceProp] instanceof CompositionMapping) {
                                    var compMap: CompositionMapping = instance[instanceProp];
                                    this.getChildInstances(compMap, instance);
                                }
                            }
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
            //TODO:  wrap in promise if IsPromisified
            if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
            if (DataStoreManager.ActualModel[compMap.ChildType].getAllPromise) {
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
            }
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            //console.log(parent.TypeName,DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType]);
			if (!parent[compMap.PropertyName]) parent[compMap.PropertyName] = [];
            //Get the gerunds.then
            if (typeof DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType] == "undefined" || !DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise) {
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType] = {};
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise = $.getJSON(DataStoreManager.baseUrl + compMap.GerundUrl)
                    .done(function (dookie) {
                        DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].stuff = dookie;
                        //find relevant gerunds for this parent instance
                    })

            } else {
                DataStoreManager.ActualModel[parent.TypeName + "To" + compMap.ChildType].promise.then(function (d) {
                    //find relevant gerunds for this parent instance
                })
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
                */
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