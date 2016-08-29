class XHR {

    static Request: any = XMLHttpRequest || ActiveXObject;

    static SUCCESS_CODES = [200,201]; 

    static GET(url){
        return this._sendRequest('GET', url);
    }

    static POST(url, body){
        return this._sendRequest('POST', url, body);
    }

    constructor() { }

    //-----------  Send Request  -----------//

    private static _sendRequest(method: string, url: string, body: any = null) {
        return new Promise((resolve, reject) => {
            var fullUrl = DataStoreManager.baseUrl + url;
            var xhr = new this.Request();
            console.log(method, fullUrl);
            
            xhr.open(method, fullUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');

            console.log(xhr.open(method, fullUrl));

            xhr.onload = () => {
                console.log("hello");
                if (this.SUCCESS_CODES.indexOf(xhr.status) > -1) {
                    return resolve(JSON.parse(xhr.responseText));
                }
                else {
                    return reject(xhr.statusText);
                }
            }

            xhr.onerror = function () {
                console.log("error", xhr.statusText);
                return reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            };

            if (!body) body = null;
            xhr.send(body);           
            
        })
    }
}