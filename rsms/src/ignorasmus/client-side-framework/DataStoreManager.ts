////////////////////////////////////////////////////////////////////////////////
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
    static baseUrl: string = "http://erasmus.graysail.com:9080/rsms/src/ajaxAction.php?action=";
    static isPromisified: boolean = true;

    // NOTE: there's intentionally no getter
    private static _actualModel: any = {};
    static get ActualModel(): any {
        return this._actualModel;
    }
    static set ActualModel(value: any) {
        this._actualModel = InstanceFactory.convertToClasses(value);
    }

    static storeThings(things: any[]): void {
        if (!DataStoreManager.ActualModel[things[0].Class]) DataStoreManager.ActualModel[things[0].Class] = {};
        for (let i = 0; i < things.length; i++) {
            DataStoreManager.ActualModel[things[0].Class][things[i].Key_id].Model = things[i];
        }
        if (this.isPromisified) {
            //TODO: store relevant promise
            //DataStoreManager[thing[0].Class][thing[0].Key_id].Promise = ...
        }
    }

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    // TODO: Consider method overload to allow multiple types and viewModelParents
    static getAll(type: string, viewModelParent: any[]): any[] {
        if (!DataStoreManager._actualModel[type]) {
            DataStoreManager._actualModel[type] = {};
            DataStoreManager._actualModel[type].getAllCalled = true;
            if (!DataStoreManager._actualModel[type].getAllPromise) {
                DataStoreManager._actualModel[type].getAllPromise = $.getJSON(DataStoreManager.baseUrl + window[type].urlMapping.urlGetAll)
                    .done(function (d) {
                        d = InstanceFactory.convertToClasses(d);
                        //DIG:  DataStoreManager._actualModel[type].Data is the holder for the actual data of this type.
                        //Time to decide for sure.  Do we have a seperate hashmap object, is Data a mapped object, or do we not need the performance boost of mapping at all?
                        DataStoreManager._actualModel[type].Data = d;
                        viewModelParent.splice(0, viewModelParent.length);
                        // Dig this neat way to use viewModelParent as a reference instead of a value!
                        Array.prototype.push.apply(viewModelParent, _.cloneDeep(d));
                        return viewModelParent;
                    })
                    .fail(function (d) {
                        console.log("shit... getJSON failed:", d.statusText);
                    })
            }
        } else {
            Array.prototype.push.apply(viewModelParent, _.cloneDeep(DataStoreManager._actualModel[type]));
            viewModelParent = _.cloneDeep(DataStoreManager._actualModel[type]);
        }
        return this.promisifyData(DataStoreManager._actualModel[type].getAllPromise);

        /*this._actualModel.User = {           
            "14": {
                Data: { Key_id: 14, Name: "John Doe", Class: "User" },
                Promise:someThing
            },            
            getAllCalled: Boolean = false,
            getAllPromise: promiseObjectResolutingFromCallToGetAllUsers
        }
        this._actualModel.PromiseCache*/
    }

    /*this._actualModel.User.getAll("User", $scope.allTheUsers).then(function () {
        $scope.allTheUsers = [];
    })*/

    static getById(type: string, id: string | number, viewModelName: string): any {
        var obj: any = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj && obj.viewModels && obj.viewModels.hasOwnProperty(viewModelName)) {
            return obj.viewModels[viewModelName];
        } else {
            throw new Error("No such id as " + id);
        }
    }

    static getActualModelEquivalent(viewModelObj: any): any {
        if (Array.isArray(viewModelObj)) {
            console.log("hey man... i expected this to be a single instance of an approved class");
        } else {
            if (viewModelObj[this.classPropName] && InstanceFactory._classNames.indexOf(viewModelObj[this.classPropName])) {
                viewModelObj = this.findByPropValue(this.ActualModel[viewModelObj[this.classPropName]], this.uidString, viewModelObj[this.uidString]);
                return viewModelObj;
            } else {
                console.log("shit dude... I'm not familiar with this class or object type");
            }
        }
    }

    private static commitToActualModel(viewModelParent: any): boolean {
        var success: boolean;
        if (success) {
            // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
            this._actualModel = _.cloneDeep(viewModelParent);
        } else {
            console.log("wtf");
        }
        return success;
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
                    resolve(data)
                } else {
                    reject("bad in dsm");
                }
            });
            return p;
        }
    }

}