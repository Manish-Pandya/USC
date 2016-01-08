var Constants = (function () {

    var constants = {};

    constants.PENDING_CHANGE = {
        USER_STATUS:{
            NO_LONGER_CONTACT:"Still in this lab, but no longer a contact",
            NOW_A_CONTACT:"Still in this lab, but now a lab contact",
            MOVED_LABS:"In another PI's lab",
            LEFT_UNIVERSITY:"No longer at the univserity",
            ADDED:"Added",
            REMOVED:"Removed"
        },
        ROOM_STATUS:{
            ADDED:"Added",
            REMOVED:"Removed"
        },
        HAZARD_STATUS:{

        }
    };

    constants.ROLE = {
        NAME:{
            ADMIN:"Admin",
            SAFETY_INSPECTOR:"Safety Inspector",
            RADIATION_INSPECTOR:"Radiation Inspector",
            PRINCIPAL_INVESTIGATOR:"Principal Investigator",
            LAB_CONTACT:"Lab Contact",
            LAB_PERSONNEL:"Lab Personnel",
            RADIATION_USER:"Radiation User",
            RADIATION_ADMIN:"Radiation Admin",
            EMERGENCY_ACCOUNT:"Emergency Account",
            READ_ONLY:"Read Only",
            OCCUPATIONAL_HEALTH:"Occupational Health"
        }
    };

    constants.CARBOY_USE_CYCLE = {
        STATUS:{
            AVAILABLE:"Available",
            IN_USE:"In Use",
            DECAYING:"Decaying",
            PICKED_UP:"Picked Up",
            AT_RSO:"AT RSO"
        }
    };

    constants.INSPECTION = {
        STATUS:{
            NOT_ASSIGNED:"NOT ASSIGNED",
            NOT_SCHEDULED:"NOT SCHEDULED",
            SCHEDULED:"SCHEDULED",
            PENDING_CLOSEOUT:"PENDING CLOSEOUT",
            CLOSED_OUT:"CLOSED OUT",
            INCOMPLETE_REPORT:"INCOMPLETE REPORT",
            COMPLETE:"COMPLETE",
            OVERDUE_CAP:"OVERDUE CAP",
            PENDING_EHS_APPROVAL:"PENDING EHS APPROVAL",
            OVERDUE_FOR_INSPECTION:"OVERDUE FOR INSPECTION"
        }
    };

    constants.CORRECTIVE_ACTION = {
        STATUS:{
            INCOMPLETE:"Incomplete",
            PENDING:"Pending",
            COMPLETE:"Complete",
            ACCEPTED:"Accepted"
        }
    };

    constants.DRUM = {
        STATUS:{
            SHIPPED: "Shipped",

        }
    };

    constants.PICKUP = {
        STATUS:{
            PICKED_UP:"PICKED UP",
            AT_RSO:"AT RSO",
            REQUESTED:"REQUESTED",
        }
    };

    constants.PARCEL = {
        STATUS:{
            ARRIVED:"Arrived",
            PRE_ORDER:"Pre-order",
            ORDERED:"Ordered",
            WIPE_TESTED:"Wipe Tested",
            DELIVERED:"Delivered",
            DISPOSED:"Disposed"
        }
    };

    constants.INVENTORY = {
        STATUS:{
            LATE:"Late",
            COMPLETE:"Complete",
            NA:"N/A"
        }
    };

    constants.ISOTOPE = {
        EMITTER_TYPE:{
            ALPHA: "Alpha",
            BETA: "Beta",
            GAMMA: "Gamma"
        }
    };

    constants.WIPE_TEST = {
        READING_TYPE:{
            LSC:"LSC",
            ALPHA_BETA:"Alpha/Beta",
            MCA:"MCA"
        }
    };

    //these have to be strings instead of ints because the server will return IDS as strings, and we don't want to have to convert them all
    constants.BRANCH_HAZARD_IDS = ['1', '9999', '10009', '10010'];

    constants.MASTER_HAZARDS_BY_ID = {
        1: {Name:'Biological Safety', cssID:'biologicalMaterialsHeader'},
        9999: {Name:'Chemical Safety', cssID:'chemicalSafetyHeader'},
        10009: {Name:'Radiation Safety', cssID:'radiationSafetyHeader'},
        10010: {Name:'General Laboratory Safety', cssID:'generalSafetyHeader'}
    }

    constants.HAZARD_PI_ROOM = {
        STATUS:{
            STORED_ONLY: "Stored Only",
            OTHER_PI: "Used by another PI's lab",
            IN_USER: "In Use"
        }
    }

    constants.BIOSAFETY_CABINET = {
        FREQUENCY: {
            ANNUALLY: "Annually",
            BI_ANNUALLY: "Bi-annually",
            SEMI_ANNUALLY: "Semi-annually"
        }
    }

    constants.PROTOCOL_HAZARDS = [{Name: "Recombinant or Synthetic Nucleic Acids", Key_id: 1, Class: "Hazard"}, {Name: "Risk Group 2 (RG2) or Higher Agents", Key_id: 2, Class: "Hazard"}, {Name: "Human-Derived Materials", Key_id: 3, Class: "Hazard" }, {Name: "HHS Biological Toxins", Key_id: 4, Class: "Hazard"}]

    return constants;
})();
