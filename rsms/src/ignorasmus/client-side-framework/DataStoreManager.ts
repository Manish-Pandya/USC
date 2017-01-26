////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

abstract class PermissionMap {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static Permissions: any[] = [];

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------

    private constructor() { } // Static class cannot be instantiated

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    /**
     * Returns the permissions of the given class type.
     *
     * @param className
     */
    static getPermission(className: string): any {
        if (!_.has(this.Permissions, className)) {
            this.Permissions[className] = {};
            var instance: FluxCompositerBase = InstanceFactory.createInstance(className);
            this.Permissions[className].getAll = instance.hasGetAllPermission();
            //TODO:  this.Permissions[className].save = instance.getHasSavePermissions();
        }
        return this.Permissions[className];
    }
}


//abstract specifies singleton in ts 1.x (ish)
abstract class DataStoreManager {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static classPropName: string = "Class";
    static uidString: string = "Key_id";
    static baseUrl: string = "http://erasmus.graysail.com/rsms/src/ajaxAction.php?action=";

    static CurrentRoles: any[];

    // NOTE: there's intentionally no getter. Only internal framework classes should have read access of actual model.
    public static _actualModel: any = {};
    static set ActualModel(value: any) {
        this._actualModel = InstanceFactory.convertToClasses(value);
    }

    private static _modalData: any;
    static get ModalData(): any {
        return this._modalData;
    }
    static set ModalData(value: any) {
        this._modalData = _.cloneDeep(value);
    }

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------


    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    /**
     * Gets all instances of a given type and passes them to the given viewModelParent.
     * Optionally composes child classes based on passed CompositionMapping.
     * 
     * @param type
     * @param viewModelParent
     * @param compMaps
     */
     //TODO:  Switch of allCompMaps when we hit circular structure in get alls, for instance, a PI can get its Rooms which can get its PIs, but we should stop there.
    static getAll(type: string, viewModelParent: FluxCompositerBase[], compMaps: CompositionMapping[] | boolean = null): Promise<FluxCompositerBase[]> {
        if (!PermissionMap.getPermission(type).getAll) {
            throw new Error("You don't have permission to call getAll for " + type);
        }
        if (!InstanceFactory._classNames) InstanceFactory.getClassNames("/models");

        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data || !DataStoreManager._actualModel[type].Data.length) {
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                DataStoreManager._actualModel[type].getAllPromise = XHR.GET(InstanceFactory._nameSpace[type].urlMapping.urlGetAll);
            }
        } else {       
            DataStoreManager._actualModel[type].getAllPromise = this.promisifyData( DataStoreManager._actualModel[type].Data );
        }

        return DataStoreManager._actualModel[type].getAllPromise
            .then((d: FluxCompositerBase[]): Promise<any> => {
                if (d.length) {
                    d = InstanceFactory.convertToClasses(d);
                    DataStoreManager._actualModel[type].Data = d;

                    return (compMaps ? this.resolveCompMaps(d[0], compMaps) : this.promisifyData(d))
                        .then((whateverGotReturned) => {
                            d.forEach((value: FluxCompositerBase, index: number) => {
                                value.doCompose(compMaps);
                                if (!value.viewModelWatcher) value.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(value);
                                viewModelParent[index] = value.viewModelWatcher;
                            });

                            return viewModelParent;
                        })
                        .catch((reason) => {
                            console.log("getAll (inner promise):", reason);
                        })
                }
            })
            .catch((d) => {
                console.log("getAll:", d);
                return d;
            })
    }

    /**
     * Gets instance of a given type by id and passes it to the given viewModelParent.
     * Optionally composes child classes based on passed CompositionMapping.
     * 
     * @param type
     * @param id
     * @param viewModelParent
     * @param compMaps
     */
    static getById(type: string, id: string | number, viewModelParent: any, compMaps: CompositionMapping[] | boolean = null): Promise<FluxCompositerBase> {
        if (!InstanceFactory._classNames) InstanceFactory.getClassNames("/models");

        id = id.toString();

        if (!this._actualModel[type].Data || !this._actualModel[type].Data.length) {
            DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(InstanceFactory._nameSpace[type].urlMapping.urlGetById + id);
        } else {
            DataStoreManager._actualModel[type].getByIdPromise = this.promisifyData( this.findByPropValue(this._actualModel[type].Data, this.uidString, id) );
        }

        return DataStoreManager._actualModel[type].getByIdPromise
            .then((d: FluxCompositerBase): Promise<any> => {
                d = InstanceFactory.convertToClasses(d);
                DataStoreManager._actualModel[type].Data.push(d);

                return (compMaps ? this.resolveCompMaps(d, compMaps) : this.promisifyData(d))
                    .then((whateverGotReturned) => {
                        d.doCompose(compMaps);
                        if (!d.viewModelWatcher) d.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(d);
                        viewModelParent = _.assign(viewModelParent, d.viewModelWatcher);
                        return viewModelParent;
                    })
                    .catch((reason) => {
                        console.log("getById (inner promise):", reason);
                    })
            })
            .catch((d) => {
                console.log("getById:", d);
                return d;
            })
    }

    static resolveCompMaps(fluxCompositerBase: FluxCompositerBase, compMaps: CompositionMapping[] | boolean): Promise<any[]> {
        var allComps: any[] = [];
        if (compMaps) {
            fluxCompositerBase.allCompMaps.forEach((compMap: CompositionMapping) => {
                // if compMaps == true or if it's an array with an approved compMap...
                if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && _.findIndex(compMaps, compMap) > -1)) {
                    if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                        if (!DataStoreManager._actualModel[compMap.ChildType].Data || !DataStoreManager._actualModel[compMap.ChildType].Data.length) {
                            console.log(fluxCompositerBase.TypeName + " fetching remote " + compMap.ChildType);
                            if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled) {
                                allComps.push(DataStoreManager._actualModel[compMap.ChildType].getAllPromise);
                            } else {
                                allComps.push(DataStoreManager.getAll(compMap.ChildType, [], typeof compMaps === "boolean"));
                            }
                        } else {
                            console.log(fluxCompositerBase.TypeName + " fetching local " + compMap.ChildType);
                            allComps.push(DataStoreManager._actualModel[compMap.ChildType].Data);
                        }
                        if (compMap.CompositionType == CompositionMapping.MANY_TO_MANY) {
                            if (!DataStoreManager._actualModel[compMap.GerundName] || !DataStoreManager._actualModel[compMap.GerundName].promise) {
                                DataStoreManager._actualModel[compMap.GerundName] = {}; // clear property
                                console.log(fluxCompositerBase.TypeName+"'s", compMap.GerundName, "gerund getting baked...");
                                DataStoreManager._actualModel[compMap.GerundName].promise = XHR.GET(compMap.GerundUrl)
                                    .then((gerundReturns: any[]) => {
                                        DataStoreManager._actualModel[compMap.GerundName].Data = gerundReturns;
                                    });
                                allComps.push(DataStoreManager._actualModel[compMap.GerundName].promise);
                            } else {
                                allComps.push(DataStoreManager._actualModel[compMap.GerundName].Data);
                            }
                        }
                    } else {
                        console.log(compMap.ChildType + " has no getAll permission, so resolving childUrl...");
                        fluxCompositerBase[compMap.PropertyName + "Promise"] = (fluxCompositerBase[compMap.PropertyName + "Promise"] || XHR.GET(fluxCompositerBase.getChildUrl(compMap)))
                            .then((d) => {
                                d = InstanceFactory.convertToClasses(d);
                                if (Array.isArray(d)) {
                                    var len: number = d.length;
                                    for (let i: number = 0; i < d.length; i++) {
                                        this.commitToActualModel(d[i]);
                                    }
                                }
                            })
                        allComps.push(fluxCompositerBase[compMap.PropertyName + "Promise"]);
                        //throw new Error("You don't have permission to call getAll for " + compMap.ChildType);
                    }
                }
            });
        }

        return Promise.all(allComps);
    }

    /**
     * Saves the passed viewModel instance and sets the actualModel after success.
     *
     * @param viewModel
     */
    static save(viewModel: FluxCompositerBase): Promise<FluxCompositerBase> | Promise<FluxCompositerBase[]> {
        return XHR.POST(viewModel.thisClass["urlMapping"].urlSave, viewModel)
            .then((d) => {
                if (Array.isArray(d)) {
                    d.forEach((value: any, index: number) => {
                        d[index] = DataStoreManager.commitToActualModel(value);
                    });
                    return d;
                }
                return DataStoreManager.commitToActualModel(d);
            });
    }

    /**
     * Returns the actualModel instance equivalent of a given viewModel, if found.
     *
     * @param viewModelObj
     */
    static getActualModelEquivalent(viewModelObj: FluxCompositerBase): FluxCompositerBase {
        if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName]) > -1) {
            var existingIndex: number = _.findIndex(DataStoreManager._actualModel[viewModelObj[this.classPropName]].Data, function (o) { return o.UID == viewModelObj.UID; });
            if (existingIndex > -1) {
                return DataStoreManager._actualModel[viewModelObj[this.classPropName]].Data[existingIndex];
            }
        } else {
            console.log("dang dude... I'm not familiar with this class or object type");
        }
    }

    // TODO... consider allowing array of instances rather than just 1 instance.
    /**
     * Copies the properties of viewModelParent to equivalent instance in actualModel, if found.
     * Otherwise, pushes viewModelParent to actualModel, if not already there.
     *
     * @param viewModelParent
     */
    static commitToActualModel(viewModelParent: any): FluxCompositerBase {
        var vmParent: FluxCompositerBase = InstanceFactory.convertToClasses(viewModelParent);
        if (!(vmParent instanceof FluxCompositerBase)) return;
        
        var actualModelEquivalent: FluxCompositerBase = this.getActualModelEquivalent(vmParent);
        if (!actualModelEquivalent && vmParent.TypeName) {
            DataStoreManager._actualModel[vmParent.TypeName].Data.push(_.cloneDeep(vmParent));
            actualModelEquivalent = this.getActualModelEquivalent(vmParent);
        }
        vmParent = InstanceFactory.copyProperties(actualModelEquivalent, vmParent);
        if (!actualModelEquivalent.viewModelWatcher) actualModelEquivalent.viewModelWatcher = DataStoreManager.buildNestedViewModelWatcher(actualModelEquivalent);
        InstanceFactory.copyProperties(actualModelEquivalent.viewModelWatcher, vmParent);

        return vmParent.viewModelWatcher;
    }

    /**
     * Returns fluxBase quazi-clone and recursively sets all sub-models to reference their respective viewModelWatchers, as opposed to independent copies or references to ActualModel.
     * So... changes on any deep-nested FluxCompositerBase will reflect Everywhere that FluxCompositerBase's viewModelWatcher is referenced.
     * Full roundtrip, depth-independent model syncing!
     *
     * @param fluxBase
     */

    static buildNestedViewModelWatcher(fluxBase: FluxCompositerBase): FluxCompositerBase {
        if (fluxBase.viewModelWatcher) fluxBase = fluxBase.viewModelWatcher;
        return _.cloneDeepWith(fluxBase, (value: any) => {
            if (value instanceof FluxCompositerBase) {
                if (value.viewModelWatcher) {
                    // set reference to nested FluxCompositerBase's viewModelWatcher, instead of cloning
                    return value.viewModelWatcher;
                }
                // otherwise, default deep-cloning happens
            }
        });
    }

    /**
     * Resets a given viewModel instance with the actualModel equivalent instance's properties.
     *
     * @param viewModelParent
     */
    static undo(viewModelParent: FluxCompositerBase): void {
        var actualModelInstance: FluxCompositerBase = this.getActualModelEquivalent(viewModelParent);
        if (actualModelInstance) {
            InstanceFactory.copyProperties(actualModelInstance.viewModelWatcher, actualModelInstance, ["viewModelWatcher"]);
        }
    }

    /**
     * Returns an object in a given complex object or collection by a property/value pair.
     * Also works for simply finding object by id: findByPropValue(obj, "id", "someId");
     *
     * @param obj
     * @param propName
     * @param value
     */
    private static findByPropValue(obj: any, propName: string, value: any): any {
        //Early return
        if (obj[propName] === value) {
            return obj;
        }
        var result: any;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && obj[prop] && typeof obj[prop] === 'object') {
                result = this.findByPropValue(obj[prop], propName, value);
                if (result) {
                    return result;
                }
            }
        }
        return result;
    }

    /**
     * Returns a Promise for data passed.
     * Also works fine if data passed is already a Promise.
     *
     * @param data
     */
    public static promisifyData(data: any): Promise<any> {
        var p = new Promise((resolve, reject) => {
            if (data) {
                resolve(data);
            } else {
                reject("bad in dsm");
            }
        });

        return p;
    }

}