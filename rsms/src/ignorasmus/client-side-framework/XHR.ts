////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

abstract class XHR {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static REQUEST: any = XMLHttpRequest;
    static SUCCESS_CODES: number[] = [200,201]; 

    static GET(url): Promise<any> {
        return this._sendRequest('GET', url);
    }

    static POST(url: string, body): Promise<any> {
        return this._sendRequest('POST', url, body);
    }

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
     * Sends the given HTTP request.
     *
     * @param method
     * @param url
     * @param body
     */
    private static _sendRequest(method: string, url: string, body: any = null): Promise<any> {
        return new Promise<any>((resolve, reject) => {
            var fullUrl: string = DataStoreManager.baseUrl + url;
            var xhr = new this.REQUEST();
            
            xhr.open(method, fullUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = () => {
                //console.log(xhr, location.origin);
                if (this.SUCCESS_CODES.indexOf(xhr.status) > -1) {
                    resolve(JSON.parse(xhr.responseText));
                } else {
                    reject(xhr.statusText);
                    if (location.href.indexOf("erasmus") == -1) {
                        var pathArray = location.href.split('/');
                        var protocol = pathArray[0];
                        var host = pathArray[2];
                        var url = protocol + '//';
                        window.location.href = url + "radon.qa.sc.edu/rsms";
                    }
                }
            }

            xhr.onerror = () => {
                console.log("error", xhr);
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
                if (location.href.indexOf("erasmus") == -1) {
                    var pathArray = location.href.split('/');
                    var protocol = pathArray[0];
                    var host = pathArray[2];
                    var url = protocol + '//';
                    window.location.href = url + "radon.qa.sc.edu/rsms";
                }
            };
            
            // handle posted data if needed, removing circular references
            var postBody = body ? this.stringifyCircularFix(body) : null;
            xhr.send(postBody);           
        })
    }

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
    private static stringifyCircularFix(obj: any, replacer?: (key: string, value: any) => any, space?: string | number): string {
        var parentObj: any = obj;
        var lastKey: string;
        var cache: any[] = [];
        // Note this should always be 'function' instead of fat-arrow, as we need 'this' to mutate on recursion
        var json = JSON.stringify(obj, function (key, value) {
            if (typeof value === 'object' && value && this != parentObj) {
                if (cache.indexOf(value) != -1 && cache.indexOf(parentObj) != 1) {
                    try {
                        JSON.stringify(value);
                    } catch(error) {
                        console.log("..."+lastKey+"."+key, "circular reference found and removed");
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

}