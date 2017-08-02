'use strict';

/**
 *  This links an object's entity name to the various get, getAll, and
 * save action strings to be included in a url's aciton paramenter.
 *
 * @author Perry
 */

 var urlMapper = {};

 urlMapper.list = {

        /*
            BEHOLD! The beauty that is this here alignment. Break the pattern and you will be shot.
        */

        'Authorization'         : {getById: "getAuthorizationById"         , getAll: "getAllAuthorizations"         , save: "saveAuthorization"          }, // note that you're supposed to get authorizations by PI Id usually - "getAuthorizationsByPIId"
        'Carboy'                : {getById: "getCarboyById"                , getAll: "getAllCarboys"                , save: "saveCarboy"                 },
        'CarboyUseCycle'        : {getById: "getCarboyUseCycleById"        , getAll: "getAllCarboyUseCycles"        , save: "saveCarboyUseCycle"         },
        'Drum'                  : {getById: "getDrumById"                  , getAll: "getAllDrums"                  , save: "saveDrum"                   },
        'Hazard'                : {getById: "getHazardById"                , getAll: "getAllHazards"                , save: "saveHazard"                 },
        'HazardDto'             : {getById: "getHazardById"                , getAll: "getHazardRoomDtosByPIId&id=1" , save: "saveHazard"                 },
        'Isotope'               : {getById: "getIsotopeById"               , getAll: "getAllIsotopes"               , save: "saveIsotope"                },
        'Parcel'                : {getById: "getParcelById"                , getAll: "getAllParcels"                , save: "saveParcel"                 },
        'ParcelUse'             : {getById: "getParcelUseById"             , getAll: "getAllParcelUses"             , save: "saveParcelUse"              },
        'ParcelUseAmount'       : {getById: ""                             , getAll: "getAllParcelUseAmounts"       , save: ""                           }, // parcelUseAmounts not meant to be directly retrieved or saved
        'Pickup'                : {getById: "getPickupById"                , getAll: "getAllPickups"                , save: "savePickup"                 },
        'PrincipalInvestigator' : {getById: "getRadPIById"                 , getAll: "getAllRadPis"                 , save: "savePI"                     },
        'PurchaseOrder'         : {getById: "getPurchaseOrderById"         , getAll: "getAllPurchaseOrders"         , save: "savePurchaseOrder"          },
        'SolidsContainer'       : {getById: "getSolidsContainerById"       , getAll: "getAllSolidsContainers"       , save: "saveSolidsContainer"        },
        'User'                  : {getById: "getUserById"                  , getAll: "getAllRadUsers"               , save: "saveUser"                   },
        'WasteBag'              : {getById: "getWasteBagById"              , getAll: "getAllWasteBags"              , save: "saveWasteBag"               },
        'WasteType'             : {getById: "getWasteTypeById"             , getAll: "getAllWasteTypes"             , save: "saveWasteType"              },
        'ScintVialCollection'   : {getById: "getSVCollectionById"          , getAll: "getAllSVCollections"          , save: "saveSVCollection"           },
        'ParcelWipeTest'        : {getById: "getParcelWipeTestById"        , getAll: "getAllParcelWipeTests"        , save: "saveParcelWipeTest"         },
        'PIWipeTest'            : { getById:"getPIWipeTestById"            , getAll: "getAllPIWipeTests"            , save: "savePIWipeTest"             },
        'PIWipe'                : { getById:"getPIWipeById"                , getAll: "getAllPIWipes"                , save: "savePIWipe"                 },
        'ParcelWipe'            : { getById: "getParcelWipeById"           , getAll: "getAllParcelWipes"            , save: "saveParcelWipe"             },
        'DrumWipe'              : { getById: "getDrumWipeById"             , getAll: "getAllDrumWipes"              , save: "saveDrumWipe"               },
        'DrumWipeTest'          : { getById: "getDrumWipeTestById"         , getAll: "getAllDrumWipeTests"         , save: "saveDrumWipeTest"           },
        'InspectionWipeTest'    : { getById: "getInspectionWipeTestById"   , getAll: "getAllInspectionWipeTests"    , save: "saveInspectionWipeTest"     },
        'InspectionWipe'        : {getById: "getInspectionWipeById"        , getAll: "getAllInspectionWipes"        , save: "saveInspectionWipe"         },
        'MiscellaneousWipeTest' : {getById: "getMiscellaneousWipeTestById" , getAll: "getAllMiscellaneousWipeTests" , save: "saveMiscellaneousWipeTest"  },
        'MiscellaneousWipe'     : {getById: "getMiscellaneousWipeById"     , getAll: "getAllMiscellaneousWipes"     , save: "saveMiscellaneousWipe"      },
        'Inspection'            : {getById: "getRadInspectionById"         , getAll: "getAllInspections"            , save: "saveInspection"             },
        'CarboyReadingAmount'   : { getById: "getCarboyReadingAmountById"  , getAll: "getAllCarboyReadingAmounts"   , save: "saveCarboyReadingAmount"    },
        'Inspector'             : { getById: "getInspectorById"            , getAll: "getAllInspectors"             , save: "saveInspector"              },

        'PIQuarterlyInventory'  : {getById: "getCarboyReadingAmountById"   , getAll: "getAllCarboyReadingAmounts"   , save: "savePIQuarterlyInventory"   },
        'PIAuthorization'       : {getById: "getPIAuthorizationByPIId"     , getAll: "getAllPIAuthorizations"       , save: "savePIAuthorization"        },
        'MiscellaneousWaste'    : { getById: "getMiscellaneousWasteById"   , getAll: "getAllMiscellaneousWaste"     , save: "saveMiscellaneousWaste"     },

        // Forgot to add Base module entities - add as needed.
        'Room'                  : {getById: "getRoomById"                  , getAll: "getAllRooms"                  , save: "saveRoom"                   },
        'Department'            : {getById: "getDepartmentById"            , getAll: "getAllDepartments"            , save: "saveDepartment"             },
        'Campus'                : {getById: "getCampusById"                , getAll: "getAllCampuses"               , save: "saveCampus"                 },
        'Verification'          : {getById: "getVerificationById"          , getAll: "getAllVerifications"          , save: "saveVerification"           },  
        'RadModelDto'           : {getById: "getVerificationById"          , getAll: "getRadModels"                 , save: "saveVerification"           },
        'Building'              : {getById: "getAllBuildings"              , getAll: "getAllBuildings"              , save: "saveBuilding"               }

};
