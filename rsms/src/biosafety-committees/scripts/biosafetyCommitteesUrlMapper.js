'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Matt
 */

 var bioSafetyCommitteesUrlMapper = {};

 bioSafetyCommitteesUrlMapper.list = {
        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */
        'PrincipalInvestigator' : {getById: "getPIById"                    , getAll: "getAllPIs"                    , save: "savePI"                                        },
        'User'                  : {getById: "getUserById"                  , getAll: "getAllUsers"                  , save: "saveUsers"                                     },
        'Hazard'                : {getById: "getHazardById"                , getAll: "getAllHazards"                , save: ""                                              },
        'Department'            : {getById: "getDepartmentById"            , getAll: "getAllDepartments"            , save: ""                                              },
        'BioSafetyProtocol'     : {getById: "getProtocolById"              , getAll: "getAllProtocols"              , save: "saveProtocol"                                              },
        'BiosafetyProtocol'     : {getById: "getProtocolById"              , getAll: "getAllProtocols"              , save: "saveProtocol"                                              },

};

//"extend" the rad URL mapper from the "parent" url mapper, which can be found in /client-side-framework/scripts/genericmodel/urlMapper.js
$.extend(urlMapper.list,bioSafetyCommitteesUrlMapper.list);
