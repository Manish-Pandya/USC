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
    //  Methods
    //
    //----------------------------------------------------------------------

    static getPermission(className: string): any {
        if (!_.has(this.Permissions, className)) {
            this.Permissions[className] = {};
            this.Permissions[className].getAll = new window[className]().hasGetAllPermission();
            //TODO:  this.Permissions[className].save = new window[className].getHasSavePermissions();
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
    static isPromisified: boolean = true;

    // NOTE: there's intentionally no getter. Only internal framework classes should have read access of actual model.
    protected static _actualModel: any = {};
    static set ActualModel(value: any) {
        this._actualModel = InstanceFactory.convertToClasses(value);
    }

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    // TODO: Consider method overload to allow multiple types and viewModelParents
    static getAll(type: string, viewModelParent: FluxCompositerBase[], compMaps: CompositionMapping[] | boolean = null): FluxCompositerBase[] | Promise<any> {
        if (!PermissionMap.getPermission(type).getAll) {
            throw new Error("You don't have permission to call getAll for " + type);
        }

        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data || !DataStoreManager._actualModel[type].Data.length) {
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                return DataStoreManager._actualModel[type].getAllPromise = XHR.GET(window[type].urlMapping.urlGetAll)
                    .then((d: FluxCompositerBase[]): FluxCompositerBase[] | Promise<any> => {
                        DataStoreManager._actualModel[type].Data = InstanceFactory.convertToClasses(d);
                        if (compMaps) {
                            var allComps: any[] = [];
                            var allCompMaps: CompositionMapping[] = (<FluxCompositerBase>DataStoreManager._actualModel[type].Data[0]).allCompMaps;
                            var l: number = allCompMaps.length;
                            for (let n: number = 0; n < l; n++) {
                                var compMap: CompositionMapping = allCompMaps[n];
                                if (compMap.CompositionType != CompositionMapping.ONE_TO_ONE) {
                                    if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                                        // if compMaps == true or if it's an array with an approved compMap...
                                        if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(compMap) > -1)) {
                                            if (!DataStoreManager._actualModel[compMap.ChildType].Data || !DataStoreManager._actualModel[compMap.ChildType].Data.length) {
                                                console.log(type + " in if looking for " + compMap.ChildType);
                                                if (DataStoreManager._actualModel[compMap.ChildType].getAllCalled) {
                                                    allComps.push(DataStoreManager._actualModel[compMap.ChildType].getAllPromise);
                                                } else {
                                                    allComps.push(DataStoreManager.getAll(compMap.ChildType, []));
                                                }

                                            } else {
                                                console.log(type + " in else looking for " + compMap.ChildType);
                                                allComps.push(DataStoreManager._actualModel[compMap.ChildType].Data);
                                            }
                                        }
                                    }
                                }
                            }
                            return Promise.all(allComps)
                                .then((whateverGotReturned) => {
                                    DataStoreManager._actualModel[type].Data.forEach((value: any, index: number, array: FluxCompositerBase[]) => {
                                        if (!value.viewModelWatcher) {
                                            value.viewModelWatcher = _.cloneDeep(value);
                                        }
                                        viewModelParent[index] = value.viewModelWatcher;
                                        value.doCompose(compMaps);
                                    });
                                    DataStoreManager._actualModel[type].Data = d;
                                    return viewModelParent;
                                })
                                .catch(
                                function (reason) {
                                    console.log("getAll (inner promise):", reason);
                                })
                        } else {
                            d = InstanceFactory.convertToClasses(d);
                            //DIG: DataStoreManager._actualModel[type].Data is the holder for the actual data of this type.
                            DataStoreManager._actualModel[type].Data = d;
                            // Dig this neat way to use viewModelParent as a reference instead of a value!
                            Array.prototype.push.apply(viewModelParent, _.cloneDeep(d));
                            return viewModelParent;
                        }
                    })
                    .catch((d: FluxCompositerBase[]) => {
                        console.log("getAll:", d);
                        return d;
                    })
            }
        } else {       
            var d: FluxCompositerBase[] = DataStoreManager._actualModel[type].Data;
            d.forEach((value: any, index: number, array: FluxCompositerBase[]) => {
                if (!value.viewModelWatcher) {
                    value.viewModelWatcher = _.cloneDeep(value);
                }
                viewModelParent[index] = value.viewModelWatcher;
                value.doCompose(compMaps);
            });

            return this.promisifyData(DataStoreManager._actualModel[type].Data);
        }

    }

    static getById(type: string, id: string | number, viewModelParent: any, compMaps: CompositionMapping[] | boolean = null): FluxCompositerBase | Promise<any> {
        id = id.toString();
        if (!this._actualModel[type].Data || !this._actualModel[type].Data.length) {
            return DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(window[type].urlMapping.urlGetById + id)
                .then((d: FluxCompositerBase) => {
                    if (compMaps) {
                        d = InstanceFactory.convertToClasses(d);
                        var existingIndex: number = _.findIndex(DataStoreManager._actualModel[type].Data, function (o) { return o.UID == d.UID; });
                        if (existingIndex > -1) {
                            DataStoreManager._actualModel[type].Data[existingIndex] = d;
                        } else {
                            DataStoreManager._actualModel[type].Data.push(d);
                        }

                        var allComps: any[] = [];
                        var allCompMaps: CompositionMapping[] = (<FluxCompositerBase>DataStoreManager._actualModel[type].Data[0]).allCompMaps;
                        var l: number = allCompMaps.length;
                        for (let n: number = 0; n < l; n++) {
                            var compMap: CompositionMapping = allCompMaps[n];
                            if (compMap.CompositionType != CompositionMapping.ONE_TO_ONE && DataStoreManager._actualModel[compMap.ChildType].getAllCalled || PermissionMap.getPermission(compMap.ChildType).getAll) {
                                // if compMaps == true or if it's an array with an approved compMap...
                                if (typeof compMaps === "boolean" || (Array.isArray(compMaps) && compMaps.indexOf(compMap) > -1)) {
                                    allComps.push(DataStoreManager.getAll(compMap.ChildType, []));
                                }
                            }
                        }
                        return Promise.all(allComps)
                            .then(
                                function (whateverGotReturned) {
                                    if (!d.viewModelWatcher) {
                                        d.viewModelWatcher = _.cloneDeep(d);
                                    }
                                    d.doCompose(compMaps);
                                    viewModelParent.push(d.viewModelWatcher);
                                    return viewModelParent;
                                }
                            )
                            .catch(
                                function (reason) {
                                    console.log("getById (inner promise):", reason);
                                }
                            )
                    } else {
                        d = InstanceFactory.convertToClasses(d);

                        var existingIndex: number = _.findIndex(DataStoreManager._actualModel[type].Data, function (o) { return o.UID == d.UID; });
                        if (existingIndex > -1) {
                            DataStoreManager._actualModel[type].Data[existingIndex] = d;
                        } else {
                            DataStoreManager._actualModel[type].Data.push(d);
                        }

                        if (!d.viewModelWatcher) {
                            d.viewModelWatcher = _.cloneDeep(d);
                        }

                        //TODO Figger thisun' out: do we have to _assign here?  I hope not, because we really need viewModelParent to be a reference to viewModelWatcher
                        viewModelParent.test = d.viewModelWatcher;
                        d.doCompose(compMaps);
                        //DataStoreManager._actualModel[type].Data = d;

                        return this.promisifyData(d);
                    }
                })
                .catch((d: FluxCompositerBase) => {
                    console.log("getById:", d);
                    return d;
                })
        } else {
            var d: FluxCompositerBase = this.findByPropValue(this._actualModel[type].Data, this.uidString, id);
            return InstanceFactory.convertToClasses( _.assign(viewModelParent, d) );
        }
    }

    static save(viewModel: FluxCompositerBase): void | Promise<FluxCompositerBase>{
        //TODO: create copy without circular JSON, then post it.

        return XHR.POST(viewModel.thisClass["urlMapping"].urlSave, viewModel)
            .then((d) => {
                return DataStoreManager.commitToActualModel(d);
            });
    }

    // TODO: Doesn't always work, as drills into object nest before moving to next object.
    static getActualModelEquivalent(viewModelObj: FluxCompositerBase): FluxCompositerBase {
        if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName]) > -1) {
            viewModelObj = this.findByPropValue(this._actualModel[viewModelObj[this.classPropName]].Data, this.uidString, viewModelObj[this.uidString]);
            return viewModelObj;
        } else {
            console.log("dang dude... I'm not familiar with this class or object type");
        }
    }

    /**
     * Copies the properties of viewModelParent to same instance in actualModel, if found.
     * Otherwise, pushes viewModelParent to actualModel, if no already there.
     *
     * @param viewModelParent
     */
    // TODO... consider allowing array of instances rather than just 1 instance.
    static commitToActualModel(viewModelParent: any): FluxCompositerBase {
        var vmParent: FluxCompositerBase = InstanceFactory.convertToClasses(viewModelParent);
        var existingIndex: number = _.findIndex(DataStoreManager._actualModel[vmParent.TypeName].Data, function (o) { return o.UID == vmParent.UID; });

        if (existingIndex > -1) {
            vmParent = InstanceFactory.copyProperties(DataStoreManager._actualModel[vmParent.TypeName].Data[existingIndex], vmParent);
            InstanceFactory.copyProperties(DataStoreManager._actualModel[vmParent.TypeName].Data[existingIndex].viewModelWatcher, DataStoreManager._actualModel[vmParent.TypeName].Data[existingIndex]);
        } else {
            existingIndex = _.findIndex(DataStoreManager._actualModel[vmParent.TypeName].Data, function (o) { return o.UID == vmParent.UID; });
            DataStoreManager._actualModel[vmParent.TypeName].Data.push(vmParent);
            vmParent = InstanceFactory.copyProperties(DataStoreManager._actualModel[vmParent.TypeName].Data[existingIndex], vmParent);
            vmParent.viewModelWatcher = _.cloneDeep(vmParent);
        }
        //update vm watcher
        console.log(vmParent);
        return vmParent.viewModelWatcher;
        
    }

    static undo(viewModelParent: any): void {
        var existingIndex: number = _.findIndex(DataStoreManager._actualModel[viewModelParent.TypeName].Data, function (o) { return o.UID == viewModelParent.UID; });
        if (existingIndex > -1) {
            var actualModelInstance: FluxCompositerBase = DataStoreManager._actualModel[viewModelParent.TypeName].Data[existingIndex];
            InstanceFactory.copyProperties(actualModelInstance.viewModelWatcher, actualModelInstance, ["viewModelWatcher"]);
        }
    }

    // TODO: Return a USEFULL error if anything on ActualModel is passed for propParent
    static setViewModelProp(propParent: FluxCompositerBase, propName: string, value: any, optionalCallBack?: Function): void {
        propParent[propName] = value;
        if (optionalCallBack) {
            optionalCallBack();
        }
    }

    // also works for simply finding object by id: findByPropValue(obj, "id", "someId");
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

    private static promisifyData(data: any): any {
        if (!this.isPromisified) {
            return data;
        } else {
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

}