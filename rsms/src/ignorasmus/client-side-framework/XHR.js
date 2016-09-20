var XHR = (function () {
    function XHR() {
    }
    XHR.GET = function (url) {
        return this._sendRequest('GET', url);
    };
    XHR.POST = function (url, body) {
        return this._sendRequest('POST', url, body);
    };
    //-----------  Send Request  -----------//
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
            //handle posted data if needed
            var postBody = body ? JSON.stringify(body) : null;
            xhr.send(postBody);
        });
    };
    XHR.REQUEST = XMLHttpRequest || ActiveXObject;
    XHR.SUCCESS_CODES = [200, 201];
    return XHR;
}());
