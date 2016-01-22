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
            NOT_SCHEDULED:"NOT SCHEDULED",
            SCHEDULED:"SCHEDULED",
            OVERDUE_FOR_INSPECTION:"OVERDUE FOR INSPECTION",                                               INCOMPLETE_INSPECTION:"INCOMPLETE INSPECTION",
            PENDING_CLOSEOUT:"PENDING CLOSEOUT",
            OVERDUE_CAP:"OVERDUE CAP",
            CLOSED_OUT:"CLOSED OUT"
        },
        SCHEDULE_STATUS:{
            NOT_ASSIGNED:"NOT ASSIGNED",
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

    constants.CHECKLIST_CATEGORIES_BY_MASTER_ID = [
        {Key_id: 1,     Label:'Biological', Image:'biohazard-white-con.png',        cssID:'biologicalMaterialsHeader'},
        {Key_id: 10009,  Label:'Chemical',   Image:'chemical-safety-large-icon.png', cssID:'chemicalSafetyHeader'},
        {Key_id: 10010, Label:'Radiation',  Image:'radiation-large-icon.png',       cssID:'radiationSafetyHeader'},
        {Key_id: 9999, Label:'General',    Image:'gen-hazard-large-icon.png',      cssID:'generalSafetyHeader'}
    ]

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
    return constants;
})();
