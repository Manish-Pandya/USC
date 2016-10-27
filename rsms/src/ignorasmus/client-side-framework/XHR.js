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
    XHR.stringifyCircularFix = function (obj, replacer, space) {
        var cache = [];
        var json = JSON.stringify(obj, function (key, value) {
            if (typeof value === 'object' && value !== null) {
                if (cache.indexOf(value) !== -1) {
                    return; // circular reference found, discard key
                }
                cache.push(value); // store value in our collection
            }
            return replacer ? replacer(key, value) : value;
        }, space);
        cache = null;
        return json;
    };
    ;
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------
    XHR.REQUEST = XMLHttpRequest || ActiveXObject;
    XHR.SUCCESS_CODES = [200, 201];
    return XHR;
}());
