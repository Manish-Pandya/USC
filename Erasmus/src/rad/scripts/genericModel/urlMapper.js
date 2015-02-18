'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Perry
 */

 var urlMapper = {};

 urlMapper.getList = function() {
    return {

        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */
                                   
        'Authorization'         : {getById: "getAuthorizationById"  , getAll: "getAllAuthorizations"  , save: "saveAuthorization" }, // note that you're supposed to get authorizations by PI Id usually - "getAuthorizationsByPIId"
        'Carboy'                : {getById: "getCarboyById"         , getAll: "getAllCarboys"         , save: "saveCarboy"        },
        'Drum'                  : {getById: "getDrumById"           , getAll: "getAllDrums"           , save: "saveDrum"          },
        'Hazard'                : {getById: "getHazardById"         , getAll: "getAllHazards"         , save: "saveHazard"        },
        'Isotope'               : {getById: "getIsotopeById"        , getAll: "getAllIsotopes"        , save: "saveIsotope"       },
        'Parcel'                : {getById: "getParcelById"         , getAll: "getAllParcels"         , save: "saveParcel"        },
        'ParcelUse'             : {getById: "getParcelUseById"      , getAll: "getAllParcelUses"      , save: "saveParcelUse"     },
        'ParcelUseAmount'       : {getById: ""                      , getAll: "getAllParcelUseAmounts", save: ""                  }, // parcelUseAmounts not meant to be directly retrieved or saved
        'Pickup'                : {getById: "getPickupById"         , getAll: "getAllPickups"         , save: "savePickup"        },
        'PrincipalInvestigator' : {getById: "getPIById"             , getAll: "getAllRadPis"          , save: "savePI"            },
        'PurchaseOrder'         : {getById: "getPurchaseOrderById"  , getAll: "getAllPurchaseOrders"  , save: "savePurchaseOrder" },
        'SolidsContainer'       : {getById: "getSolidsContainerById", getAll: "getAllSolidsContainers", save: ""                  },
        'User'                  : {getById: "getUserById"           , getAll: "getAllRadUsers"        , save: "saveUser"          },
        'WasteBag'              : {getById: "getWasteBagById"       , getAll: "getAllWasteBags"       , save: "saveWasteBag"      },
        'WasteType'             : {getById: "getWasteTypeById"      , getAll: "getAllWasteTypes"      , save: "saveWasteType"     },

        // Forgot to add Base module entities - add as needed.
        'Room'                  : {getById: "getRoomById"           , getAll: "getAllRooms"           , save: "saveRoom"          }
    };
 }