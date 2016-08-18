var UrlMapping = (function () {
    function UrlMapping(urlGetAll, urlGetById, urlSave) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }
    UrlMapping.prototype.getUrlGetAll = function () { return this.urlGetAll; };
    UrlMapping.prototype.setUrlGetAll = function (url) { this.urlGetAll = url; };
    UrlMapping.prototype.getUrlGetById = function () { return this.urlGetById; };
    UrlMapping.prototype.setUrlGetById = function (url) { this.urlGetById = url; };
    UrlMapping.prototype.getUrlSave = function () { return this.urlSave; };
    UrlMapping.prototype.setUrlSave = function (url) { this.urlSave = url; };
    return UrlMapping;
}());
