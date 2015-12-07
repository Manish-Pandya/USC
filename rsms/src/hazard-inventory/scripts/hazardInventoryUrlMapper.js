'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Matt
 */

 var hazardInventoryUrlMapper = {};

 hazardInventoryUrlMapper.list = {
        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */
        'PrincipalInvestigator' : {getById: "getPiForHazardInventory"      , getAll: "getAllPIs"                    , save: "savePI"                                        },
        'User'                  : {getById: "getUserById"                  , getAll: "getAllUsers"                  , save: "saveUsers"                                     },
        'HazardDto'             : {getById: "getHazardDtoById"             , getAll: "getAllRadPis"                 , save: "savePIHazardRoomMappings"                      },
        'PIHazardRoomDto'       : {getById: ""                             , getAll: ""                             , save: "savePrincipalInvestigatorHazardRoomRelation"   },
        'Inspection'            : {getById: ""                             , getAll: ""                             , save: ""   },

     //getInspectionsByPIId
     //PIHazardRoomDto
};

//"extend" the rad URL mapper from the "parent" url mapper, which can be found in /client-side-framework/scripts/genericmodel/urlMapper.js
$.extend(urlMapper.list,hazardInventoryUrlMapper.list);
