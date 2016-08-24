var UrlMapping = (function () {
    function UrlMapping(urlGetAll, urlGetById, urlSave) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }
    return UrlMapping;
}());
