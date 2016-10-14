////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var UrlMapping = (function () {
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function UrlMapping(urlGetAll, urlGetById, urlSave) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }
    return UrlMapping;
}());
