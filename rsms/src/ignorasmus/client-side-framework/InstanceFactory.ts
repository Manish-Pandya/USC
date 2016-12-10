////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

//abstract specifies singleton in ts 1.x (ish)
abstract class InstanceFactory extends DataStoreManager {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static _classNames: string[];

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------

    private constructor() { super(); } // Static class cannot be instantiated

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    /**
     * Gets array of class names from script tags with src containing the provided basePath.
     *
     * @param basePath
     */
    static getClassNames(basePath: string = ""): string[] {
        if (!this._classNames) {
            this._classNames = [];
            var scripts: NodeListOf<HTMLScriptElement> = document.getElementsByTagName('script');
            if (scripts && scripts.length) {
                for (var i in scripts) {
                    if (scripts[i].src && scripts[i].src.indexOf(basePath) > -1) {
                        var pathArray: string[] = scripts[i].src.split("/");
                        var className: string = pathArray.pop().split(".")[0];
                        if (this._classNames.indexOf(className) == -1) {
                            this._classNames.push(className);
                            //init DataStoreManager holders
                            DataStoreManager._actualModel[className] = {};
                            DataStoreManager._actualModel[className].Data = [];
                            // initting promises below shouldn't actually be necessary, but is here for completion
                            DataStoreManager._actualModel[className].getAllPromise = new Promise<any>(() => {});
                            DataStoreManager._actualModel[className].getByIdPromise = new Promise<any>(() => {});
                        }
                    }
                }
            }
        }
        return this._classNames;
    }

    /**
     * Creates and returns class instance of given className, if possible.
     *
     * @param className
     */
    static createInstance(className: string): FluxCompositerBase {
        if (this._classNames && this._classNames.indexOf(className) > -1) {
            return new window[className]();
        } else if (window[className]) {
            console.log(className + " not in approved ClassNames, but exists. Trying to create...");
            return new window[className]();
        } else {
            console.log("No such class as " + className);
        }
    }

    /**
     * Crawls through passed data and its children, creating class instances as needed.
     *
     * @param data
     */
    static convertToClasses(data: any): any {
        if (data && data[DataStoreManager.classPropName]) {
            var instance: FluxCompositerBase = InstanceFactory.createInstance(data[DataStoreManager.classPropName]);
            InstanceFactory.copyProperties(instance, data);
            instance.onFulfill();
            return instance;
        }
        
        var drillDown = (parentNode: any): void => {
            for (var prop in parentNode) {
                if (parentNode[prop] && typeof parentNode[prop] === 'object') {
                    if (parentNode[prop].hasOwnProperty(DataStoreManager.classPropName)) {
                        var instance: FluxCompositerBase = InstanceFactory.createInstance(parentNode[prop][DataStoreManager.classPropName]);
                        if (instance) {
                            instance = parentNode[prop] = InstanceFactory.copyProperties(instance, parentNode[prop]); // set instance
                            instance.onFulfill();
                        }
                    }
                    drillDown(parentNode[prop]);
                }
            }
        }
        drillDown(data);
        
        return data;
    }

    /**
     * Creates child instances based on passed CompositionMapping and adds them to the appropriate property of parent class.
     *
     * @param compMap
     * @param parent
     */
    static getChildInstances(compMap: CompositionMapping, parent: FluxCompositerBase): void {
        parent[compMap.PropertyName] = []; // clear property
        var childStore: FluxCompositerBase[] = DataStoreManager._actualModel[compMap.ChildType].Data;

        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
            var len: number = childStore.length;
            for (let i: number = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (childStore[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    parent[compMap.PropertyName].push(childStore[i]);
                }
            }
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                // Get the gerunds
                var manyTypeToManyGerundType: string = parent.TypeName + "To" + compMap.ChildType;
                if (DataStoreManager._actualModel[manyTypeToManyGerundType] && DataStoreManager._actualModel[manyTypeToManyGerundType].Data) {
                    var d: any[] = DataStoreManager._actualModel[manyTypeToManyGerundType].Data;
                    var gerundLen: number = d.length;
                    //loop through all the gerunds
                    for (let i: number = 0; i < gerundLen; i++) {
                        let childLen: number = childStore.length;
                        for (let x: number = 0; x < childLen; x++) {
                            if (parent.UID == d[i].ParentId && childStore[x].UID == d[i].ChildId) {
                                parent[compMap.PropertyName].push(childStore[x]);
                            }
                        }
                    }
                }

                return;
            } else {
                parent[compMap.PropertyName + "Promise"] = ( parent[compMap.PropertyName + "Promise"] || XHR.GET(parent.getChildUrl(compMap)) )
                    .then((d) => {
                        d = InstanceFactory.convertToClasses(d);
                        var len: number = d.length;
                        for (let i: number = 0; i < len; i++) {
                            var current: FluxCompositerBase = d[i];
                            this.commitToActualModel(current);
                            parent[compMap.PropertyName].push(current);
                        }
                        
                        return d;
                    })
            }
        } else { // CompMap is CompositionMapping.ONE_TO_ONE
            var len: number = childStore.length;
            for (let i: number = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (childStore[i][compMap.ParentIdProp] == parent[compMap.ChildIdProp]) {
                    parent[compMap.PropertyName] = childStore[i];
                }
            }
        }
    }

    /**
     * Copies properties/values from source to target and returns modified target.
     * It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy NOT working.
     *
     * @param target
     * @param source
     * @param exclusions
     */
    static copyProperties(target: any, source: any, exclusions: string[] = []): any {
        if (!target) target = {}; // init target, if doesn't already exist
        var sourceCopy: any = {};
        for (var prop in source) {
            if (exclusions.indexOf(prop) == -1) {
                // only copy over props that are not excluded
                sourceCopy[prop] = source[prop];
            }
        }

        Object.defineProperties(
            target,
            Object.getOwnPropertyNames(sourceCopy).reduce(
                (descriptors: { [index: string]: any }, key: string) => {
                    descriptors[key] = Object.getOwnPropertyDescriptor(sourceCopy, key);
                    return descriptors;
                },
                {}
            )
        );
        return target;
    }

}