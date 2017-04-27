////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
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

    static _nameSpace: any = window;

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
    static getClassNames(basePath: string | Object = ""): string[] {
        if (!this._classNames) {
            if (basePath && typeof basePath == 'string') {
                this._classNames = [];
                var scripts: NodeListOf<HTMLScriptElement> = document.getElementsByTagName('script');
                if (scripts && scripts.length) {
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
            } else {
                this._nameSpace = basePath;
                this._classNames = Object.keys(basePath);
            }
            this._classNames.forEach((className: string) => {
                //init DataStoreManager holders
                DataStoreManager._actualModel[className] = {};
                DataStoreManager._actualModel[className].Data = [];
                DataStoreManager._actualModel[className].ViewModelWatcher = [];
                // initting promises below shouldn't actually be necessary, but is here for completion
                DataStoreManager._actualModel[className].getAllPromise = new Promise<any>(() => { });
                DataStoreManager._actualModel[className].getByIdPromise = new Promise<any>(() => { });
            });
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
            return new this._nameSpace[className]();
        } else if (this._nameSpace[className]) {
            console.log(className + " not in approved ClassNames, but exists. Trying to create...");
            return new this._nameSpace[className]();
        } else {
            //console.log("No such class as " + className);
        }
    }

    /**
     * Crawls through passed data and its children, creating class instances as needed.
     * TODO: Needs to be optimized. Can check if conversion has already been done at current depth and 'continue' to next, if so.
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
                    // if either parentNode isn't a FluxCompositerBase, OR it is and parentNode[prop] is NOT its viewModelWatcher, OR parentNode[prop] is the viewModelWatcher, but isn't composed yet...
                    if (!(parentNode instanceof FluxCompositerBase && parentNode.viewModelWatcher == parentNode[prop] && parentNode.viewModelWatcher instanceof FluxCompositerBase)) {
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
            childStore.forEach((value: FluxCompositerBase) => {
                if (value[compMap.ChildIdProp] == parent[compMap.ParentIdProp]) {
                    parent[compMap.PropertyName].push(value.viewModelWatcher);
                }
            })
        } else if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                // Get the gerunds
                if (DataStoreManager._actualModel[compMap.GerundName]) {
                    if (DataStoreManager._actualModel[compMap.GerundName].Data) {
                        var d: any[] = DataStoreManager._actualModel[compMap.GerundName].Data;
                        var gerundLen: number = d.length;
                        //loop through all the gerunds
                        for (let i: number = 0; i < gerundLen; i++) {
                            childStore.forEach((value: FluxCompositerBase) => {
                                if (value.UID == d[i].ChildId && parent.UID == d[i].ParentId) {
                                    parent[compMap.PropertyName].push(value.viewModelWatcher);
                                }
                            });
                        }
                    } else {
                        DataStoreManager._actualModel[compMap.GerundName].promise = (DataStoreManager._actualModel[compMap.GerundName].promise || XHR.GET(compMap.GerundUrl))
                            .then((gerundReturns: any[]) => {
                                if (gerundReturns) {
                                    var d: any[] = DataStoreManager._actualModel[compMap.GerundName].Data = gerundReturns;
                                    var gerundLen: number = d.length;
                                    //loop through all the gerunds
                                    for (let i: number = 0; i < gerundLen; i++) {
                                        childStore.forEach((value: FluxCompositerBase) => {
                                            if (value.UID == d[i].ChildId && parent.UID == d[i].ParentId) {
                                                parent[compMap.PropertyName].push(value.viewModelWatcher);
                                            }
                                        });
                                    }
                                }
                            });
                        console.log(compMap.GerundName + " doesn't exist in actualModel. Running GET to resolve...");
                    }
                } else {
                    DataStoreManager.getById(parent.TypeName, parent.UID, new ViewModelHolder(parent), [compMap]);
                    console.log(compMap.GerundName + " doesn't exist in actualModel. Running getById to resolve...");
                }
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
            childStore.forEach((value: FluxCompositerBase) => {
                if (value[compMap.ParentIdProp] == parent[compMap.ChildIdProp]) {
                    parent[compMap.PropertyName] = value.viewModelWatcher;
                }
            })
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