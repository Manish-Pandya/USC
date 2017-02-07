////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var XHR = (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function XHR() {
    } // Static class cannot be instantiated
    XHR.GET = function (url) {
        return this._sendRequest('GET', url);
    };
    XHR.POST = function (url, body) {
        return this._sendRequest('POST', url, body);
    };
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    /**
     * Sends the given HTTP request.
     *
     * @param method
     * @param url
     * @param body
     */
    XHR._sendRequest = function (method, url, body) {
        var _this = this;
        if (body === void 0) { body = null; }
        return new Promise(function (resolve, reject) {
            var fullUrl = DataStoreManager.baseUrl + url;
            var xhr = new _this.REQUEST();
            xhr.open(method, fullUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (_this.SUCCESS_CODES.indexOf(xhr.status) > -1) {
                    resolve(JSON.parse(xhr.responseText));
                }
                else {
                    reject(xhr.statusText);
                }
            };
            xhr.onerror = function () {
                console.log("error", xhr.statusText);
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            };
            // handle posted data if needed, removing circular references
            var postBody = body ? _this.stringifyCircularFix(body) : null;
            xhr.send(postBody);
        });
    };
    /**
     * Returns JSON string with circular reference entries are replaced with null.
     * Same parameters as JSON.stringify, so default replacer method can be substituted.
     *
     * @param obj
     * @param replacer
     * @param space
     */
    /** intentionally broken JSON
   var childObj = {}
   var parentObj = {
       test: childObj;
   };
   childObj.childTest = parentObj;
   **/
    XHR.stringifyCircularFix = function (obj, replacer, space) {
        var parentObj = obj;
        var lastKey;
        var cache = [];
        // Note this should always be 'function' instead of fat-arrow, as we need 'this' to mutate on recursion
        var json = JSON.stringify(obj, function (key, value) {
            if (typeof value === 'object' && value && this != parentObj) {
                if (cache.indexOf(value) != -1 && cache.indexOf(parentObj) != 1) {
                    try {
                        JSON.stringify(value);
                    }
                    catch (error) {
                        console.log("..." + lastKey + "." + key, "circular reference found and removed");
                        return; // circular reference found, discard key
                    }
                }
                cache.push(value); // store value in our collection
            }
            parentObj = this;
            lastKey = key;
            //console.log(key, value);
            return replacer ? replacer(key, value) : value;
        }, space);
        cache = null;
        console.log(JSON.parse(json));
        return json;
    };
    ;
    return XHR;
}());
//----------------------------------------------------------------------
//
//  Properties
//
//----------------------------------------------------------------------
XHR.REQUEST = XMLHttpRequest || ActiveXObject;
XHR.SUCCESS_CODES = [200, 201];
