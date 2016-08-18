////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

//abstract specifies singleton in ts 1.x (ish)
abstract class DataStorePromise extends DataStoreManager{ 

    static Resolve: Function;
    static Reject: Function;

    static getPromiseFuncs: Function = function (resolve, reject) {
        if (!DataStorePromise.Resolve || !DataStorePromise.Reject) {
            console.log("hello");
            DataStorePromise.Resolve = resolve;
            DataStorePromise.Reject = reject;
        }
    };

    static returnPromise(url: string, testFunction): any {
        var p = new Promise((resolve, reject) => {
            DataStorePromise.getPromiseFuncs(resolve, reject);            
            $.getJSON(url)
                .done(function (d) {
                   /* return DataStorePromise.Resolve(d);
                    DataStorePromise.Resolve = function () { };*/
                    var a = {};
                    for (let i = 0; i < d.length; i++) {
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
    }
}