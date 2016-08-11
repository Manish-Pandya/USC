////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
//abstract specifies singleton in ts 1.x (ish)
var DataStorePromise = (function (_super) {
    __extends(DataStorePromise, _super);
    function DataStorePromise() {
        _super.apply(this, arguments);
    }
    DataStorePromise.returnPromise = function (url, testFunction) {
        var p = new Promise(function (resolve, reject) {
            DataStorePromise.getPromiseFuncs(resolve, reject);
            $.getJSON(url)
                .done(function (d) {
                /* return DataStorePromise.Resolve(d);
                 DataStorePromise.Resolve = function () { };*/
                var a = {};
                for (var i = 0; i < d.length; i++) {
                    a[d[i].Key_id] = d[i];
                }
                resolve(a);
                return a;
            })
                .fail(function (d) {
                console.log("uh oh");
                reject(d.statusText);
                //return DataStorePromise.Reject(d.statusText);
            });
        });
        return p;
    };
    DataStorePromise.getPromiseFuncs = function (resolve, reject) {
        if (!DataStorePromise.Resolve || !DataStorePromise.Reject) {
            console.log("hello");
            DataStorePromise.Resolve = resolve;
            DataStorePromise.Reject = reject;
        }
    };
    return DataStorePromise;
}(DataStoreManager));
