class XHR {

    static REQUEST: any = XMLHttpRequest || ActiveXObject;

    static SUCCESS_CODES: number[] = [200,201]; 

    static GET(url): Promise<any> {
        return this._sendRequest('GET', url);
    }

    static POST(url, body): Promise<any> {
        return this._sendRequest('POST', url, body);
    }

    //-----------  Send Request  -----------//

    private static _sendRequest(method: string, url: string, body: any = null): Promise<any> {
        return new Promise<any>((resolve, reject) => {
            var fullUrl: string = DataStoreManager.baseUrl + url;
            var xhr = new this.REQUEST();
            
            xhr.open(method, fullUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = () => {
                if (this.SUCCESS_CODES.indexOf(xhr.status) > -1) {
                    resolve(JSON.parse(xhr.responseText));
                } else {
                    reject(xhr.statusText);
                }
            }

            xhr.onerror = () => {
                console.log("error", xhr.statusText);
                reject({
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