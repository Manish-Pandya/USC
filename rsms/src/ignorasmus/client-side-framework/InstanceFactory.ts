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
            //console.log("No such class as " + className);
            return null;
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
                            instance = InstanceFactory.copyProperties(instance, parentNode[prop]);
                            parentNode[prop] = instance; // set instance
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
        if (compMap.CompositionType == CompositionMapping.ONE_TO_MANY) {
            var childStore: FluxCompositerBase[] = DataStoreManager._actualModel[compMap.ChildType].Data;
            parent[compMap.PropertyName] = []; // clear property
            parent.viewModelWatcher[compMap.PropertyName] = [];

            var len: number = childStore.length;
            for (let i: number = 0; i < len; i++) {
                //TODO, don't push members of ActualModel, instead create new childWatcher view model thinguses
                if (DataStoreManager._actualModel[compMap.ChildType].Data[i][compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    parent[compMap.PropertyName].push(childStore[i]);
                    parent.viewModelWatcher[compMap.PropertyName].push(childStore[i].viewModelWatcher);
                }
            }
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (PermissionMap.getPermission(compMap.ChildType).getAll) {
                if (!DataStoreManager[compMap.ChildType] || !DataStoreManager[compMap.ChildType].getAllCalled || !DataStoreManager[compMap.ChildType].Data) {
                    parent[compMap.PropertyName] = []; // clear property
                    parent.viewModelWatcher[compMap.PropertyName] = [];

                    // Get the gerunds.then
                    var manyTypeToManyChildType: string = parent.TypeName + "To" + compMap.ChildType;
                    if (typeof DataStoreManager._actualModel[manyTypeToManyChildType] == "undefined" || !DataStoreManager._actualModel[manyTypeToManyChildType].promise) {
                        DataStoreManager._actualModel[manyTypeToManyChildType] = {};
                        DataStoreManager._actualModel[manyTypeToManyChildType].promise = XHR.GET(compMap.GerundUrl)
                            .then(function (d: any[]) {
                                DataStoreManager._actualModel[manyTypeToManyChildType].Data = d;
                                var childStore: FluxCompositerBase[] = DataStoreManager._actualModel[compMap.ChildType].Data;
                                var gerundLen: number = d.length;
                                //loop through all the gerunds
                                for (let i: number = 0; i < gerundLen; i++) {
                                    let childLen: number = childStore.length;
                                    for (let x: number = 0; x < childLen; x++) {
                                        if (parent.UID == d[i].ParentId && childStore[x].UID == d[i].ChildId) {
                                            parent[compMap.PropertyName].push(childStore[x]);
                                            parent.viewModelWatcher[compMap.PropertyName].push(childStore[i].viewModelWatcher);
                                        }
                                    }
                                }
                            })
                            .catch((f) => {
                                console.log("getChildInstances:", f);
                            })

                    } else {
                        parent[compMap.PropertyName] = []; // clear property
                        parent.viewModelWatcher[compMap.PropertyName] = [];

                        DataStoreManager._actualModel[manyTypeToManyChildType].promise.then((d: any[]) => {
                            var childStore: FluxCompositerBase[] = DataStoreManager._actualModel[compMap.ChildType].Data;
                            var d: any[] = DataStoreManager._actualModel[manyTypeToManyChildType].Data;
                            var gerundLen: number = d.length;
                            //loop through all the gerunds
                            for (let i: number = 0; i < gerundLen; i++) {
                                let childLen: number = childStore.length;
                                for (let x: number = 0; x < childLen; x++) {
                                    let child: FluxCompositerBase = childStore[x];
                                    if (child.UID == d[i].ChildId && parent.UID == d[i].ParentId) {
                                        parent[compMap.PropertyName].push(child);
                                    }
                                }
                            }
                            // init collection in viewModel to be replaced with referenceless actualModel data
                            parent.viewModelWatcher[compMap.PropertyName] = [];
                            // clone collection from actualModel to viewModel
                            parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
                        })
                    }

                    return;
                }
            } else {
                if (typeof parent[compMap.PropertyName + "Promise"] == "undefined") {
                    parent[compMap.PropertyName + "Promise"] = XHR.GET( parent.getChildUrl(compMap) ).then((d) => {
                        parent[compMap.PropertyName] = []
                        parent.viewModelWatcher[compMap.PropertyName] = [];

                        d = InstanceFactory.convertToClasses(d);
                        var len: number = d.length;
                        for (let i: number = 0; i < len; i++) {
                            var current: any = d[i];
                            var existingIndex: number = _.findIndex(DataStoreManager._actualModel[compMap.ChildType].Data, function (o) { return o.UID == current.UID; });
                            if (existingIndex > -1) {
                                DataStoreManager._actualModel[compMap.ChildType].Data[existingIndex] = current;
                            } else {
                                DataStoreManager._actualModel[compMap.ChildType].Data.push(current);
                            }
                            if (!current.viewModelWatcher) {
                                current.viewModelWatcher = _.cloneDeep(current);
                            }
                            parent[compMap.PropertyName].push(current);
                            parent.viewModelWatcher[compMap.PropertyName].push(current.viewModelWatcher);
                        }
                        
                        return d;
                    })
                } else {
                    parent[compMap.PropertyName + "Promise"].then((d) => {
                        parent[compMap.PropertyName] = []
                        parent.viewModelWatcher[compMap.PropertyName] = [];
                        d = InstanceFactory.convertToClasses(d);
                        var len: number = d.length;
                        for (let i: number = 0; i < len; i++) {
                            var current = d[i];
                            var existingIndex: number = _.findIndex(DataStoreManager._actualModel[compMap.ChildType].Data, function (o) { return o.UID == current.UID; });
                            if (existingIndex > -1) {
                                DataStoreManager._actualModel[compMap.ChildType].Data[existingIndex] = current;
                            } else {
                                DataStoreManager._actualModel[compMap.ChildType].Data.push(current);
                            }
                            if (!current.viewModelWatcher) {
                                current.viewModelWatcher = _.cloneDeep(current);
                            }
                            parent[compMap.PropertyName].push(current);
                            parent.viewModelWatcher[compMap.PropertyName].push(current.viewModelWatcher);
                        }

                        return d;
                    })
                }
            }
        } else {
            // clone collection from actualModel to viewModel
            parent[compMap.PropertyName] = parent.viewModelWatcher[compMap.PropertyName] = InstanceFactory.copyProperties(parent.viewModelWatcher[compMap.PropertyName], parent[compMap.PropertyName]);
        }
    }

    /**
     * Copies properties/values from source to target.
     * It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy NOT working.
     *
     * @param target
     * @param source
     * @param exclusions
     */
    static copyProperties(target: any, source: any, exclusions: string[] = []): any {
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