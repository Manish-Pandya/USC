////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

class UrlMapping {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    urlGetAll: string;
    urlGetById: string;
    urlSave: string;

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------

    constructor(urlGetAll: string, urlGetById: string, urlSave: string) {
        this.urlGetAll = urlGetAll;
        this.urlGetById = urlGetById;
        this.urlSave = urlSave;
    }

}