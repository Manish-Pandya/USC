class XHR {

    static REQUEST: any = XMLHttpRequest || ActiveXObject;

    static SUCCESS_CODES = [200,201]; 

    static GET(url){
        return this._sendRequest('GET', url);
    }

    static POST(url, body){
        return this._sendRequest('POST', url, body);
    }

    //-----------  Send Request  -----------//

    private static _sendRequest(method: string, url: string, body: any = null) {
        return new Promise((resolve, reject) => {
            var fullUrl = DataStoreManager.baseUrl + url;
            var xhr = new this.REQUEST();
            
            xhr.open(method, fullUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = () => {
                if (this.SUCCESS_CODES.indexOf(xhr.status) > -1) {
                    return resolve(JSON.parse(xhr.responseText));
                }
                else {
                    return reject(xhr.statusText);
                }
            }

            xhr.onerror = () => {
                console.log("error", xhr.statusText);
                return reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            };

            //handle posted data if needed
            var postBody = body ? JSON.stringify(body) : null;
            xhr.send(postBody);           
            
        })
    }
}