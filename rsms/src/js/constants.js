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
            AT_RSO: "AT RSO",
            HOT_ROOM: "In Hot Room"
        }
    };

    constants.INSPECTION = {
        STATUS:{
            NOT_ASSIGNED:"NOT ASSIGNED",
            NOT_SCHEDULED:"NOT SCHEDULED",
            SCHEDULED: "SCHEDULED",
            OVERDUE_FOR_INSPECTION: "OVERDUE FOR INSPECTION",
            INCOMPLETE_INSPECTION: "INCOMPLETE INSPECTION",
            PENDING_CLOSEOUT:"PENDING CLOSEOUT",
            CLOSED_OUT:"CLOSED OUT",
            INCOMPLETE_REPORT:"INCOMPLETE REPORT",
            COMPLETE:"COMPLETE",
            OVERDUE_CAP:"OVERDUE CAP",
            PENDING_EHS_APPROVAL:"PENDING EHS APPROVAL",
        },
        SCHEDULE_STATUS:{
            NOT_ASSIGNED:"NOT ASSIGNED"
        },
        TYPE: {
            BIO: "BioSafety Inspection",
            CHEM: "Chemical Inspection",
            RAD: "Radiation Inspection"
        },
        MONTH_NAMES: [
                        { val: "01", string: "January" },
                        { val: "02", string: "February" },
                        { val: "03", string: "March" },
                        { val: "04", string: "April" },
                        { val: "05", string: "May" },
                        { val: "06", string: "June" },
                        { val: "07", string: "July" },
                        { val: "08", string: "August" },
                        { val: "09", string: "September" },
                        { val: "10", string: "October" },
                        { val: "11", string: "November" },
                        { val: "12", string: "December" }
        ],
        OTHER_DEFICIENCY_ID: 100032
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
        STATUS: {
            REQUESTED: "Requested",
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

    //match the key_id for each waste type to a readable string
    constants.WASTE_TYPE = {
        LIQUID: 1,
        CADAVER: 2,
        VIAL: 3,
        OTHER: 4,
        SOLID: 5
    }

    //these have to be strings instead of ints because the server will return IDS as strings, and we don't want to have to convert them all
    constants.BRANCH_HAZARD_IDS = ['1', '9999', '10009', '10010'];

    constants.MASTER_HAZARDS_BY_ID = {
        1: {Name:'Biological Safety', cssID:'biologicalMaterialsHeader'},
        9999: {Name:'Chemical Safety', cssID:'chemicalSafetyHeader'},
        10009: {Name:'Radiation Safety', cssID:'radiationSafetyHeader'},
        10010: {Name:'General Laboratory Safety', cssID:'generalSafetyHeader'}
    }

    constants.MASTER_HAZARD_IDS = {
        BIOLOGICAL: 1,
        CHEMICAL: 10009,
        RADIATION: 10010
    }

    constants.CHECKLIST_CATEGORIES_BY_MASTER_ID = [
        { Key_id: 1, Label: 'Biological', Image: 'biohazard-white-con.png', cssID: 'biologicalMaterialsHeader' },
        { Key_id: 10009, Label: 'Chemical', Image: 'chemical-safety-large-icon.png', cssID: 'chemicalSafetyHeader' },
        { Key_id: 10010, Label: 'Radiation', Image: 'radiation-large-icon.png', cssID: 'radiationSafetyHeader' },
        { Key_id: 9999, Label: 'General', Image: 'gen-hazard-large-icon.png', cssID: 'generalSafetyHeader' }
    ]

    constants.HAZARD_PI_ROOM = {
        STATUS:{
            STORED_ONLY: "Stored Only",
            OTHER_PI: "Other Lab's Hazard",
            IN_USE: "In Use"
        }
    }

    constants.BIOSAFETY_CABINET = {
        FREQUENCY: {
            ANNUALLY: "Annually",
            SEMI_ANNUALLY: "Semi-annually"
        },
        EQUIPMENT_CLASS: "BioSafetyCabinet",
        TYPE: ["Class I",
               "Class II, Type A1",
               "Class II, Type A2",
               "Class II, Type A/B3",
               "Class II, Type B1",
               "Class II, Type B2",
               "Horizontal Flow Clean Bench",
               "Vertical Flow Clean Bench"
        ],
        STATUS: {
            FAIL: "FAIL",
            PASS: "PASS",
            NEW_BSC: "NEW BSC",
            OVERDUE: "OVERDUE",
            PENDING: "PENDING"
        }
    }

    constants.ROOM_HAZARDS = {
        BIO_HAZARDS_PRESENT: { label: "Biological Hazards", value: "Bio_hazards_present" },
        CHEM_HAZARDS_PRESENT: { label: "Chemical Hazards", value: "Chem_hazards_present" },
        RAD_HAZARDS_PRESENT: { label: "Radiation Hazards", value: "Rad_hazards_present" }
    }

    constants.ROOM_HAZARD_STATUS = {
        IN_USE: { KEY: "IN_USE", LAB_LABEL: "Used by my lab in this room", ADMIN_LABEL: "In use in room" },
        STORED_ONLY: { KEY: "STORED_ONLY", LAB_LABEL: "Stored in this room", ADMIN_LABEL: "Stored only in room" },
        NOT_USED: { KEY: "NOT_USED", LAB_LABEL: "Not used by my lab in this room", ADMIN_LABEL: "Not used in room" }
    }

    constants.PROTOCOL_HAZARDS = [{Name: "Recombinant or Synthetic Nucleic Acids", Key_id: 1, Class: "Hazard"}, {Name: "Risk Group 2 (RG2) or Higher Agents", Key_id: 2, Class: "Hazard"}, {Name: "Human-Derived Materials", Key_id: 3, Class: "Hazard" }, {Name: "HHS Biological Toxins", Key_id: 4, Class: "Hazard"}]

    return constants;
})();
