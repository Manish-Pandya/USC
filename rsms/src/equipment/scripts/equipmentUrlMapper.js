'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Matt
 */

 var equipmentUrlMapper = {};

//"extend" the rad URL mapper from the "parent" url mapper, which can be found in /client-side-framework/scripts/genericmodel/urlMapper.js
 equipmentUrlMapper.list = urlMapper.list;
 equipmentUrlMapper.list = {

        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */
        'Autoclave'             : {getById: "getAutoclaveById"             , getAll: "getAllAutoclaves"             , save: "saveAutoclave"              },
        'BioSafetyCabinet'      : {getById: "getBioSafetyCabinetById"      , getAll: "getAllBioSafetyCabinets"      , save: "saveBioSafetyCabinet"       },
        'ChemFumeHood'          : {getById: "getChemFumeHoodById"          , getAll: "getAllChemFumeHoods"          , save: "saveChemFumeHood"           },
        'Lasers'                : {getById: "getLaserById"                 , getAll: "getAllLasers"                 , save: "saveLaser"                  },
        'XRay'                  : {getById: "getXRayById"                  , getAll: "getAllXRays"                  , save: "saveXRay"                   },
        'Room'                  : {getById: "getRoomById"                  , getAll: "getAllRooms"                  , save: ""                           },
        'Building'              : {getById: "getBuildingById"              , getAll: "getAllBuildings"              , save: ""                           },
        'PrincipalInvestigator' : {getById: "getPIById"                    , getAll: "getAllPIs"                    , save: ""                   },
};

urlMapper.list = equipmentUrlMapper.list;