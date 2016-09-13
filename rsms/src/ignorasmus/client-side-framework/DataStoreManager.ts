﻿////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

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

    // NOTE: there's intentionally no getter
    private static _actualModel: any = {};
    static get ActualModel(): any {
        return this._actualModel;
    }
    static set ActualModel(value: any) {
        this._actualModel = InstanceFactory.convertToClasses(value);
    }

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    // TODO: Consider method overload to allow multiple types and viewModelParents
    static getAll(type: string, viewModelParent: any[], compMaps: CompositionMapping[] | boolean = null): any[] {
        viewModelParent.splice(0, viewModelParent.length); // clear viewModelParent
        if (!DataStoreManager._actualModel[type].Data) {
            if (!DataStoreManager._actualModel[type].getAllCalled) {
                DataStoreManager._actualModel[type].getAllCalled = true;
                return DataStoreManager._actualModel[type].getAllPromise = XHR.GET(window[type].urlMapping.urlGetAll)
                    .then((d: any[]) => {
                        if (compMaps) {
                        // TODO: Remove unneeded code so we only care about listed compMaps (if compMaps is array)
                            var allComps: any[] = [];
                            var thisClass: Function = window[type];
                            for (var instanceProp in thisClass) {
                                if (thisClass[instanceProp] instanceof CompositionMapping && thisClass[instanceProp].CompositionType != CompositionMapping.ONE_TO_ONE) {
                                    console.log(instanceProp);
                                    allComps.push(DataStoreManager.getAll(thisClass[instanceProp].ChildType, []));
                                }
                            }
                            return Promise.all(allComps)
                                .then(
                                    function (whateverGotReturned) {
                                        d = InstanceFactory.convertToClasses(d);                                
                                        d.forEach((value: any, index: number, array: any[]) => {
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
                                    }
                                )
                    } else {
                        d = InstanceFactory.convertToClasses(d);
                        //DIG:  DataStoreManager._actualModel[type].Data is the holder for the actual data of this type.
                        //Time to decide for sure.  Do we have a seperate hashmap object, is Data a mapped object, or do we not need the performance boost of mapping at all?
                        DataStoreManager._actualModel[type].Data = d;
                        // Dig this neat way to use viewModelParent as a reference instead of a value!
                        Array.prototype.push.apply(viewModelParent, _.cloneDeep(d));
                        return viewModelParent;
                    }
                    })
                    .catch((d) => {
                        console.log("getAll:", d);
                        return d;
                    })
            }
        } else {
            Array.prototype.push.apply(viewModelParent, _.cloneDeep(DataStoreManager._actualModel[type]));
            viewModelParent = _.cloneDeep(DataStoreManager._actualModel[type]);
        }

        return this.promisifyData(DataStoreManager._actualModel[type].getAllPromise);
    }

    static getById(type: string, id: string | number, viewModelParent: any, compMaps: CompositionMapping[] | boolean = null): FluxCompositerBase {
        id = id.toString();
        if (!this._actualModel[type].Data) {
            return DataStoreManager._actualModel[type].getByIdPromise = XHR.GET(window[type].urlMapping.urlGetById + id)
                .then((d: FluxCompositerBase) => {
                    if (compMaps) {
                        // TODO: Do composition of all or list of provided compMaps
                    } else {
                        viewModelParent = InstanceFactory.convertToClasses(_.assign(viewModelParent, d) );
                        console.log(viewModelParent);
                        return viewModelParent;
                    }
                })
                .catch((d) => {
                    console.log("getById:", d);
                    return d;
                })
        } else {
            var d: FluxCompositerBase = this.findByPropValue(this._actualModel[type].Data, this.uidString, id);
            return InstanceFactory.convertToClasses( _.assign(viewModelParent, d) );
        }
        /*var obj: any = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj) {
            _.assign(viewModelParent, obj);
        } else {
            throw new Error("No such id as " + id + " already in actual model.");
        }*/
    }

    static getActualModelEquivalent(viewModelObj: any): any {
        if (Array.isArray(viewModelObj)) {
            console.log("hey man... i expected this to be a single instance of an approved class");
        } else {
            if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName])) {
                viewModelObj = this.findByPropValue(this.ActualModel[viewModelObj[this.classPropName]], this.uidString, viewModelObj[this.uidString]);
                return viewModelObj;
            } else {
                console.log("dang dude... I'm not familiar with this class or object type");
            }
        }
    }

    private static commitToActualModel(viewModelParent: any): boolean {
        // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
        this._actualModel = _.cloneDeep(viewModelParent);

        return true;
    }

    // TODO: Return a USEFULL error if anything on ActualModel is passed for propParent
    static setViewModelProp(propParent: any, propName: string, value: any, optionalCallBack?: Function): void {
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