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

    static A: any = { thing: "I'm A" };
    static B: any = { thing: "I'm B", butt: "I'm an ass" };
    static C: any = { thing: "I'm C" };

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
                            // Run composition routine here based on instance's CompositionMapping //
                            for (var instanceProp in instance) {
                                if (instance[instanceProp] instanceof CompositionMapping) {
                                    var compMap: CompositionMapping = instance[instanceProp];
                                    instance[compMap.PropertyName] = this.getChildInstances(compMap, instance);
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

    static getChildInstances(compMap: CompositionMapping, parent: any): any {
        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
            //TODO:  wrap in promise if IsPromisified
            var children = [];
            if (DataStoreManager.ActualModel[compMap.ChildType].getAllPromise) {
                DataStoreManager.ActualModel[compMap.ChildType].getAllPromise.then(function () {
                    for (let i = 0; i < DataStoreManager.ActualModel[compMap.ChildType].Data.length; i++) {
                        //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                        if (DataStoreManager.ActualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                            //console.log(parent.Class, parent.Key_id, parent[compMap.ParentIdProp], DataStoreManager.ActualModel[compMap.ChildType].Data[i].Class,DataStoreManager.ActualModel[compMap.ChildType].Data[i].Supervisor_id);
                            //perhaps use a DataStore manager method that leverages findByPropValue here
                            children.push(DataStoreManager.ActualModel[compMap.ChildType].Data[i]);
                        }
                    }
                })
            }
            return children;
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            return [];
        } else {
            var childInstance = this.createInstance(compMap.ChildType);
            childInstance.Email = "foo@yoo.poo";
            return childInstance;
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

    static testCopy(): void {
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        this.copyProperties(this.A, this.B, this.C);
        console.log("after copy");
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        this.C.thing = "C Poot";
        console.log("after setting C to 'C Poot'");
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        console.log("after setting A to 'A Poot'");
        this.A.thing = "A Poot";
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        console.log("Suck it, fools! It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy test pending.");
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