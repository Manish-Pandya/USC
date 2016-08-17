////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
//abstract specifies singleton in ts 1.x (ish)
var DataStoreManager = (function () {
    function DataStoreManager() {
    }
    Object.defineProperty(DataStoreManager, "ActualModel", {
        get: function () {
            return this._actualModel;
        },
        set: function (value) {
            DataStoreManager._actualModel = InstanceFactory.convertToClasses(value);
        },
        enumerable: true,
        configurable: true
    });
    DataStoreManager.storeThings = function (things) {
        if (!DataStoreManager.ActualModel[things[0].Class])
            DataStoreManager.ActualModel[things[0].Class] = {};
        for (var i = 0; i < things.length; i++) {
            DataStoreManager.ActualModel[things[0].Class][things[i].Key_id].Model = things[i];
        }
        if (this.isPromisified) {
        }
    };
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    DataStoreManager.getAll = function (type, viewModelParent) {
        if (!DataStoreManager._actualModel[type]) {
            var json = $.getJSON(window[type].urlAll)
                .done(function (d) {
                DataStoreManager._actualModel[type] = InstanceFactory.convertToClasses(d);
                viewModelParent.splice(0, viewModelParent.length);
                // Dig this neat way to use viewModelParent as a reference instead of a value!
                Array.prototype.push.apply(viewModelParent, InstanceFactory.convertToClasses(d));
                return viewModelParent;
            })
                .fail(function (d) {
                console.log("shit... getJSON failed:", d.statusText);
            });
        }
        else {
            Array.prototype.push.apply(viewModelParent, _.cloneDeep(DataStoreManager._actualModel[type]));
            viewModelParent = _.cloneDeep(DataStoreManager._actualModel[type]);
        }
        return this.promisifyData(json);
        /*this._actualModel.User = {
            "14": {
                Data: { Key_id: 14, Name: "John Doe", Class: "User" },
                Promise:someThing
            },
            getAllCalled: Boolean = false,
            getAllPromise: promiseObjectResolutingFromCallToGetAllUsers
        }
        this._actualModel.PromiseCache*/
    };
    /*this._actualModel.User.getAll("User", $scope.allTheUsers).then(function () {
        $scope.allTheUsers = [];
    })*/
    DataStoreManager.getById = function (type, id, viewModelName) {
        var obj = this.findByPropValue(this._actualModel[type], this.uidString, id);
        if (obj && obj.viewModels && obj.viewModels.hasOwnProperty(viewModelName)) {
            return obj.viewModels[viewModelName];
        }
        else {
            throw new Error("No such id as " + id);
        }
    };
    DataStoreManager.syncViewModel = function (actualModelParent, viewModelParent) {
        var _this = this;
        //set appropriate viewModel
        viewModelParent = _.cloneDeep(actualModelParent);
        // get all classes from script tags
        var classNames = InstanceFactory.getClassNames("/rad/scripts/models/");
        // loop thru to set references
        var drillDown = function (parentNode, viewParentNode) {
            var className = parentNode.constructor.name;
            if (classNames.indexOf(className) > -1) {
                viewModelParent = viewParentNode; // Put actual reference by finding where it lives in viewModel
                if (!_this._actualModel[className]) {
                    _this._actualModel[className] = [];
                }
                _this._actualModel[className].push(parentNode);
            }
            for (var prop in parentNode) {
                if (parentNode.hasOwnProperty(prop) && prop != "viewModels" && parentNode[prop] && typeof parentNode[prop] === 'object') {
                    drillDown(parentNode[prop], viewParentNode[prop]);
                }
            }
        };
        drillDown(actualModelParent, viewModelParent);
    };
    DataStoreManager.commitToActualModel = function (viewModelParent) {
        var success;
        if (success) {
            // TODO: Drill into ActualModel, setting the appropriate props from viewModelParent.
            this._actualModel = _.cloneDeep(viewModelParent);
        }
        else {
            console.log("wtf");
        }
        return success;
    };
    // TODO: Return a USEFULL error if anything on ActualModel is passed for propParent
    DataStoreManager.setViewModelProp = function (propParent, propName, value, optionalCallBack) {
        propParent[propName] = value;
        if (optionalCallBack) {
            optionalCallBack();
        }
    };
    // also works for simply finding object by id: findByPropValue(obj, "id", "someId");
    DataStoreManager.findByPropValue = function (obj, propName, value) {
        //Early return
        if (obj[propName] === value) {
            return obj;
        }
        var result;
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop) && obj[prop] && typeof obj[prop] === 'object') {
                result = this.findByPropValue(obj[prop], propName, value);
                if (result) {
                    return result;
                }
            }
        }
        return result;
    };
    DataStoreManager.promisifyData = function (data) {
        if (!this.isPromisified) {
            return data;
        }
        else {
            var p = new Promise(function (resolve, reject) {
                if (data) {
                    resolve(data);
                }
                else {
                    reject("bad in dsm");
                }
            });
            return p;
        }
    };
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    DataStoreManager.classPropName = "Class";
    DataStoreManager.uidString = "Key_id";
    DataStoreManager.isPromisified = true;
    // NOTE: there's intentionally no getter
    DataStoreManager._actualModel = {};
    return DataStoreManager;
}());
