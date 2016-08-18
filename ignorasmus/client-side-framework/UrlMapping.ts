class UrlMapping {
    private urlGetAll: string;
    private urlGetById: string;
    private urlSave: string;

    constructor(urlGetAll: string, urlGetById: string, urlSave: string) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }

    public getUrlGetAll(): string { return this.urlGetAll }
    public setUrlGetAll(url: string): void { this.urlGetAll = url; }

    public getUrlGetById(): string { return this.urlGetById }
    public setUrlGetById(url: string): void { this.urlGetById = url; }

    public getUrlSave(): string { return this.urlSave }
    public setUrlSave(url: string): void { this.urlSave = url; }

}