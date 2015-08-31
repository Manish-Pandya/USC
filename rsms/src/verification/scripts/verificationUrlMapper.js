'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Matt
 */

 var verificationUrlMapper = {};

//"extend" the rad URL mapper from the "parent" url mapper, which can be found in /client-side-framework/scripts/genericmodel/urlMapper.js
 verificationUrlMapper.list = urlMapper.list;
 verificationUrlMapper.list = {

        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */
        'Verification'          : {getById: "getVerificationById"          , getAll: "getAllVerifications"          , save: "saveVerification"           },
        'PrincipalInvestigator' : {getById: "getPIForVerification"         , getAll: "getAllRadPis"                 , save: "savePI"                     },
        'PendingUserChange'     : {getById: "getPendingUserChangeById"     , getAll: "getAllRadPis"                 , save: "savePendingUserChange"      }


};

urlMapper.list =  verificationUrlMapper.list;