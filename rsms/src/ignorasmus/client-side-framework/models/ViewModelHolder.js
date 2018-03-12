////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var ViewModelHolder = /** @class */ (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function ViewModelHolder(data) {
        if (data === void 0) { data = null; }
        //----------------------------------------------------------------------
        //
        //  Properties
        //
        //----------------------------------------------------------------------
        this.data = null;
        this.data = data;
    }
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    ViewModelHolder.prototype.save = function () {
        return DataStoreManager.save(this.data);
    };
    ViewModelHolder.prototype.undo = function () {
        if (Array.isArray(this.data)) {
            this.data.forEach(function (value) {
                DataStoreManager.undo(value);
            });
        }
        else {
            DataStoreManager.undo(this.data);
        }
    };
    return ViewModelHolder;
}());
