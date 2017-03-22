class Constants  {

    public static PENDING_CHANGE = {
        USER_STATUS:{
            NO_LONGER_CONTACT:"Still in this lab, but no longer a contact",
            NOW_A_CONTACT:"Still in this lab, but now a lab contact",
            MOVED_LABS:"In another PI's lab",
            LEFT_UNIVERSITY:"No longer at the univserity",
            ADDED:"Added",
            REMOVED: "Removed"
        },
        ROOM_STATUS:{
            ADDED:"Added",
            REMOVED:"Removed"
        },
        HAZARD_STATUS:{

        }
    };

    public static POSITION: string[] = ["Undergraduate", 
        "Graduate Student", 
        "Post-Doctoral Fellow", 
        "Research Professor", 
        "Research Associate", 
        "Laboratory Technician", 
        "Research Specialist",
        "Scientific Staff", 
        "Intern",
        "Other"
    ];

    public static ROLE = {
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
            OCCUPATIONAL_HEALTH: "Occupational Health",
            IBC_MEMBER: "IBC Member",
            IBC_CHAIR: "IBC Chair"
        }
    };

    public static CARBOY_USE_CYCLE = {
        STATUS: {
            AVAILABLE: "Available",
            IN_USE: "In Use",
            DECAYING: "Decaying",
            PICKED_UP: "Picked Up",
            AT_RSO: "AT RSO",
            HOT_ROOM: "In Hot Room",
            MIXED_WASTE: "Mixed Waste"
        }
    };

    public static INSPECTION = {
        STATUS:{
            NOT_ASSIGNED:"NOT ASSIGNED",
            NOT_SCHEDULED:"NOT SCHEDULED",
            SCHEDULED: "SCHEDULED",
            OVERDUE_FOR_INSPECTION: "OVERDUE INSPECTION",
            INCOMPLETE_INSPECTION: "INCOMPLETE INSPECTION",
            INCOMPLETE_CAP: "INCOMPLETE CAP",
            OVERDUE_CAP: "OVERDUE CAP",
            SUBMITTED_CAP: "SUBMITTED CAP",
            CLOSED_OUT: "CLOSED OUT",
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

    public static CORRECTIVE_ACTION = {
        STATUS:{
            INCOMPLETE:"Incomplete",
            PENDING:"Pending",
            COMPLETE:"Complete",
            ACCEPTED:"Accepted"
        },
        NO_COMPLETION_DATE_REASON: {
            NEEDS_EHS: { LABEL: "Completion date depends on EHS.", VALUE: "needs_ehs" },
            NEEDS_FACILITIES: { LABEL: "Completion date depends on Facilities.", VALUE: "needs_facilities" },
            INSUFFICIENT_FUNDS: { LABEL: "Insufficient funds for corrective action.", VALUE: "insuficient_funds" }
        }
    };

    public static DRUM = {
        STATUS:{
            SHIPPED: "Shipped",

        }
    };

    public static PICKUP = {
        STATUS:{
            PICKED_UP:"PICKED UP",
            AT_RSO:"AT RSO",
            REQUESTED:"REQUESTED",
        }
    };

    public static PARCEL = {
        STATUS: {
            REQUESTED: "Requested",
            ARRIVED:"Arrived",
            ORDERED:"Ordered",
            WIPE_TESTED:"Wipe Tested",
            DELIVERED:"Delivered",
            DISPOSED:"Disposed"
        }
    };

    public static INVENTORY = {
        STATUS:{
            LATE:"Late",
            COMPLETE:"Complete",
            NA:"N/A"
        }
    };

    public static ISOTOPE = {
        EMITTER_TYPE:{
            ALPHA: "Alpha",
            BETA: "Beta",
            GAMMA: "Gamma"
        }
    };

    public static WIPE_TEST = {
        READING_TYPE:{
            LSC:"LSC",
            ALPHA_BETA:"Alpha/Beta",
            MCA: "MCA",
            GM_METER: "GM Meter"
        }
    };

    //match the key_id for each waste type to a readable string
    public static WASTE_TYPE = {
        LIQUID: 1,
        CADAVER: 2,
        VIAL: 3,
        OTHER: 4,
        SOLID: 5
    }

    //these have to be strings instead of ints because the server will return IDS as strings, and we don't want to have to convert them all
    public static BRANCH_HAZARD_IDS = ['1', '9999', '10009', '10010'];

    public static MASTER_HAZARDS_BY_ID = {
        1: {Name:'Biological Safety', cssID:'biologicalMaterialsHeader'},
        9999: {Name:'Chemical Safety', cssID:'chemicalSafetyHeader'},
        10009: {Name:'Radiation Safety', cssID:'radiationSafetyHeader'},
        10010: {Name:'General Laboratory Safety', cssID:'generalSafetyHeader'}
    }

    public static MASTER_HAZARD_IDS = {
        BIOLOGICAL: 1,
        CHEMICAL: 10009,
        RADIATION: 10010
    }

    public static CHECKLIST_CATEGORIES_BY_MASTER_ID = [
        { Key_id: 1, Label: 'Biological', Image: 'biohazard-white-con.png', cssID: 'biologicalMaterialsHeader' },
        { Key_id: 10009, Label: 'Chemical', Image: 'chemical-safety-large-icon.png', cssID: 'chemicalSafetyHeader' },
        { Key_id: 10010, Label: 'Radiation', Image: 'radiation-large-icon.png', cssID: 'radiationSafetyHeader' },
        { Key_id: 9999, Label: 'General', Image: 'gen-hazard-large-icon.png', cssID: 'generalSafetyHeader' }
    ]

    public static HAZARD_PI_ROOM = {
        STATUS:{
            STORED_ONLY: "Stored Only",
            OTHER_PI: "Other Lab's Hazard",
            IN_USE: "In Use"
        }
    }

    public static BIOSAFETY_CABINET = {
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

    public static ROOM_HAZARDS = {
        BIO_HAZARDS_PRESENT: { label: "Biological Hazards", value: "Bio_hazards_present" },
        CHEM_HAZARDS_PRESENT: { label: "Chemical Hazards", value: "Chem_hazards_present" },
        RAD_HAZARDS_PRESENT: { label: "Radiation Hazards", value: "Rad_hazards_present" }
    }

    public static ROOM_HAZARD_STATUS = {
        IN_USE: { KEY: "IN_USE", LAB_LABEL: "Used by my lab in room", ADMIN_LABEL: "In use in room" },
        STORED_ONLY: { KEY: "STORED_ONLY", LAB_LABEL: "Stored in room", ADMIN_LABEL: "Stored only in room" },
        NOT_USED: { KEY: "NOT_USED", LAB_LABEL: "Not used by my lab in room", ADMIN_LABEL: "Not used in room" }
    }

    public static VERIFICATION = {
        STATUS: {
            COMPLETE:"COMPLETE",
            OVERDUE: "OVERDUE",
            PENDING: "PENDING"
        }
    }

    public static PROTOCOL_HAZARDS = [
        { Name: "Recombinant or Synthetic Nucleic Acids", Key_id: 1, Class: "Hazard" },
        { Name: "Risk Group 2 (RG2) or Higher Agents", Key_id: 2, Class: "Hazard" },
        { Name: "Human-Derived Materials", Key_id: 3, Class: "Hazard" },
        { Name: "HHS Biological Toxins", Key_id: 4, Class: "Hazard" }
    ]

    public static IBC_PROTOCOL_REVISION = {
        STATUS: {
            NOT_SUBMITTED: "Not Submitted",
            SUBMITTED: "Submitted",
            RETURNED_FOR_REVISION: "Returned for Revision",
            IN_REVIEW: "In Review",
            APPROVED: "Approved"
        }
    }

    public static IBC_ANSWER_TYPE = {        
        MULTIPLE_CHOICE: "MULTIPLE_CHOICE",
        TABLE: "TABLE",
        FREE_TEXT: "FREE_TEXT",
        MULTI_SELECT: "MULTI_SELECT"
    }
}
