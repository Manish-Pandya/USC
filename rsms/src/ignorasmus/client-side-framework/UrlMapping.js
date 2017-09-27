////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var UrlMapping = /** @class */ (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    /**
     *
     * @param urlGetAll
     * @param urlGetById
     * @param urlSave
     */
    function UrlMapping(urlGetAll, urlGetById, urlSave) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }
    return UrlMapping;
}());
