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
        'BioSafetyCabinet'      : {getById: "getBioSafetyCabinetById"      , getAll: "getAllBioSafetyCabinets"      , save: "saveBioSafetyCabinet"       }
};

urlMapper.list = equipmentUrlMapper.list;
