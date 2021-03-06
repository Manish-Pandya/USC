var Constants = /** @class */ (function () {
    function Constants() {
    }
    Constants.PENDING_CHANGE = {
        USER_STATUS: {
            NO_LONGER_CONTACT: "Still in this lab, but no longer a contact",
            NOW_A_CONTACT: "Still in this lab, but now a lab contact",
            MOVED_LABS: "In another PI's lab",
            LEFT_UNIVERSITY: "No longer at the univserity",
            ADDED: "Added",
            REMOVED: "Removed"
        },
        ROOM_STATUS: {
            ADDED: "Added",
            REMOVED: "Removed"
        },
        HAZARD_STATUS: {}
    };

    Constants.POSITION = {};
    Constants.POSITION.LAB_PERSONNEL = ["Undergraduate",
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

    Constants.POSITION.PI = [
        "Assistant Professor",
        "Associate Professor",
        "Professor",
        "Distinguished Professor",
        "SmartState Endowed Chair"
    ];

    Constants.POSITION.EHS_PERSONNEL = [
        "Assistant Chemical Hygiene Officer",
        "Assistant RSO - Radioactive Materials",
        "Biological Safety Officer",
        "Chemical Hygiene Officer",
        "Chemical Waste Manager",
        "Health Physicist - Electronic Products",
        "Infectious Waste Management",
        "Radiation Safety Officer",
        "Research Safety Bureau Chief"
    ];

    Constants.ROLE = {
        NAME: {
            ADMIN: "Admin",
            SAFETY_INSPECTOR: "Safety Inspector",
            RADIATION_INSPECTOR: "Radiation Inspector",
            DEPARTMENT_CHAIR: "Department Chair",
            DEPARTMENT_COORDINATOR: "Department Safety Coordinator",
            PRINCIPAL_INVESTIGATOR: "Principal Investigator",
            LAB_CONTACT: "Lab Contact",
            LAB_PERSONNEL: "Lab Personnel",
            RADIATION_USER: "Radiation User",
            RADIATION_ADMIN: "Radiation Admin",
            RADIATION_CONTACT: "Radiation Contact",
            EMERGENCY_ACCOUNT: "Emergency Account",
            READ_ONLY: "Read Only",
            OCCUPATIONAL_HEALTH: "Occupational Health",
            IBC_MEMBER: "IBC Member",
            IBC_CHAIR: "IBC Chair",
            TEACHING_LAB_CONTACT: "Teaching Lab Contact"
        }
    };
    Constants.CARBOY_USE_CYCLE = {
        STATUS: {
            AVAILABLE: "Available",
            IN_USE: "In Use",
            DECAYING: "Decaying",
            PICKED_UP: "Picked Up",
            AT_RSO: "AT RSO",
            HOT_ROOM: "In Hot Room",
            MIXED_WASTE: "Mixed Waste",
            POURED: "Poured",
            DRUMMED: "In Drum"
        }
    };
    Constants.INSPECTION = {
        STATUS: {
            NOT_ASSIGNED: "NOT ASSIGNED",
            NOT_SCHEDULED: "NOT SCHEDULED",
            SCHEDULED: "SCHEDULED",
            OVERDUE_FOR_INSPECTION: "OVERDUE INSPECTION",
            INCOMPLETE_INSPECTION: "INCOMPLETE INSPECTION",
            INCOMPLETE_CAP: "INCOMPLETE CAP",
            OVERDUE_CAP: "OVERDUE CAP",
            SUBMITTED_CAP: "SUBMITTED CAP",
            CLOSED_OUT: "CLOSED OUT",
            INSPECTED: "INSPECTED",
        },
        SCHEDULE_STATUS: {
            NOT_ASSIGNED: "NOT ASSIGNED"
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
    Constants.CORRECTIVE_ACTION = {
        STATUS: {
            INCOMPLETE: "Incomplete",
            PENDING: "Pending",
            COMPLETE: "Complete",
            ACCEPTED: "Accepted"
        },
        NO_COMPLETION_DATE_REASON: {
            NEEDS_EHS: { LABEL: "Completion date depends on EHS.", VALUE: "needs_ehs" },
            NEEDS_FACILITIES: { LABEL: "Completion date depends on Facilities.", VALUE: "needs_facilities" },
            INSUFFICIENT_FUNDS: { LABEL: "Insufficient funds for corrective action.", VALUE: "insuficient_funds" }
        }
    };
    Constants.DRUM = {
        STATUS: {
            SHIPPED: "Shipped",
        }
    };
    Constants.PICKUP = {
        STATUS: {
            PICKED_UP: "PICKED UP",
            AT_RSO: "AT RSO",
            REQUESTED: "REQUESTED",
        }
    };
    Constants.PARCEL = {
        STATUS: {
            REQUESTED: "Requested",
            ARRIVED: "Arrived",
            ORDERED: "Ordered",
            WIPE_TESTED: "Wipe Tested",
            DELIVERED: "Delivered",
            DISPOSED: "Disposed"
        }
    };
    Constants.INVENTORY = {
        STATUS: {
            LATE: "Late",
            COMPLETE: "Complete",
            NA: "N/A"
        }
    };
    Constants.ISOTOPE = {
        EMITTER_TYPE: {
            ALPHA: "Alpha",
            BETA: "Beta",
            GAMMA: "Gamma",
            NEUTRON: "Neutron"
        }
    };
    Constants.WIPE_TEST = {
        READING_TYPE: {
            LSC: "LSC",
            ALPHA_BETA: "Alpha/Beta",
            MCA: "MCA",
            GM_METER: "GM Meter"
        }
    };
    //match the key_id for each waste type to a readable string
    Constants.WASTE_TYPE = {
        LIQUID: 1,
        CADAVER: 2,
        VIAL: 3,
        OTHER: 4,
        SOLID: 5,
        TRANSFER: 6,
        SAMPLE: 7
    };
    Constants.CONTAINTER_TYPE = [
        { Class: "WasteBag", Label: "Solid Waste" },
        { Class: "ScintVialCollection", Label: "Scintillation Vial" }
    ];
    //these have to be strings instead of ints because the server will return IDS as strings, and we don't want to have to convert them all
    Constants.BRANCH_HAZARD_IDS = ['1', '9999', '10009', '10010'];
    Constants.MASTER_HAZARDS_BY_ID = {
        '1': { Name: 'Biological Safety', cssID: 'biologicalMaterialsHeader' },
        '9999': { Name: 'Chemical Safety', cssID: 'chemicalSafetyHeader' },
        '10009': { Name: 'Radiation Safety', cssID: 'radiationSafetyHeader' },
        '10010': { Name: 'General Laboratory Safety', cssID: 'generalSafetyHeader' }
    };
    Constants.MASTER_HAZARD_IDS = {
        BIOLOGICAL: 1,
        CHEMICAL: 10009,
        RADIATION: 10010
    };
    Constants.CHECKLIST_CATEGORIES_BY_MASTER_ID = [
        { Key_id: 1, Label: 'Biological', Image: 'biohazard-white-con.png', cssID: 'biologicalMaterialsHeader' },
        { Key_id: 10009, Label: 'Chemical', Image: 'chemical-safety-large-icon.png', cssID: 'chemicalSafetyHeader' },
        { Key_id: 10010, Label: 'Radiation', Image: 'radiation-large-icon.png', cssID: 'radiationSafetyHeader' },
        { Key_id: 9999, Label: 'General', Image: 'gen-hazard-large-icon.png', cssID: 'generalSafetyHeader' }
    ];
    Constants.HAZARD_PI_ROOM = {
        STATUS: {
            STORED_ONLY: "Stored Only",
            OTHER_PI: "Other Lab's Hazard",
            IN_USE: "In Use"
        }
    };
    Constants.EQUIPMENT = {
        FREQUENCY: {
            ANNUALLY: "Annually",
            SEMI_ANNUALLY: "Semi-annually"
        },
        STATUS: {
            FAIL: "FAIL",
            PASS: "PASS",
            NEW: "NEW",
            OVERDUE: "OVERDUE",
            PENDING: "PENDING"
        }
    };
    Constants.BIOSAFETY_CABINET = {
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
        INACTIVATE_DISCLAIMER: "This cabinet must be unassigned from the PI to prevent the cabinet from being selected in the PI's Hazard Inventory"
    };
    Constants.CHEM_FUME_HOOD = {
        EQUIPMENT_CLASS: "ChemFumeHood",
        MANUFACTURER: ["Air Master Systems",
            "ESCO",
            "Flow Sciences",
            "Kewaunee Scientific",
            "Labconco",
            "Nuaire",
            "Terra Universal",
            "The Baker Company",
            "Other"
        ],
        TYPE: ["Constant Air Volume (CAV) - Always On",
            "Constant Air Volume (CAV) + On/Off",
            "Variable Air Volume (VAV)",
            "High Performance (Low Flow)"
        ],
        USES: ["General",
            "Radioisotope",
            "Perchloric Acid",
            "Polypropylene (Acid Resistant)",
            "HF Acid",
            "Chemical Waste",
            "Storage Only",
            "Canopy",
            "Walk-In",
            "Ductless Filtered",
            "Long-Term Experiment",
            "Reactor",
            "Equipment (list)",
            "Other"
        ],
        FEATURES: ["Horizontal Sash",
            "Vertical Sash",
            "Combination Sash",
            "Digital Air Monitor",
            "Magnehelic Gauge",
            "Airflow Alarm",
            "Zone Sensor",
            "Plumbing",
            "Electrical",
            "Vacuum",
            "Compressed Air",
            "Propane",
            "Natural Gas",
            "Nitrogen"
        ]
    };
    Constants.ROOM_HAZARDS = [
        { label: "Biological Hazards", value: "Bio_hazards_present" },
        { label: "Chemical Hazards", value: "Chem_hazards_present" },
        { label: "Radiation Hazards", value: "Rad_hazards_present" },
        { label: "Recombinant DNA", value: "Recombinant_dna_present" },
        { label: "Corrosive Gas", value: "Corrosive_gas_present" },
        { label: "Flammable Gas", value: "Flammable_gas_present" },
        { label: "Toxic Gas", value: "Toxic_gas_present" },
        { label: "HF", value: "Hf_present" },
        { label: "Lasers", value: "Lasers_present" },
        { label: "Xrays", value: "Xrays_present" },
        { label: "DLAR", value: "Animal_facility" }
    ];
    Constants.ROOM_HAZARD_STATUS = {
        IN_USE: { KEY: "IN_USE", LAB_LABEL: "Used by my lab", ADMIN_LABEL: "In use in room" },
        STORED_ONLY: { KEY: "STORED_ONLY", LAB_LABEL: "Stored by my lab", ADMIN_LABEL: "Stored only in room" },
        NOT_USED: { KEY: "NOT_USED", LAB_LABEL: "Not used by my lab", ADMIN_LABEL: "Not used in room" }
    };
    Constants.VERIFICATION = {
        STATUS: {
            COMPLETE: "COMPLETE",
            OVERDUE: "OVERDUE",
            PENDING: "PENDING"
        }
    };
    Constants.PROTOCOL_HAZARDS = [
        { Name: "Recombinant or Synthetic Nucleic Acids", Key_id: 1, Class: "Hazard" },
        { Name: "Risk Group 2 (RG2) or Higher Agents", Key_id: 2, Class: "Hazard" },
        { Name: "Human-Derived Materials", Key_id: 3, Class: "Hazard" },
        { Name: "HHS Biological Toxins", Key_id: 4, Class: "Hazard" }
    ];
    Constants.IBC_PROTOCOL_REVISION = {
        STATUS: {
            NOT_SUBMITTED: "Not Submitted",
            SUBMITTED: "Submitted",
            RETURNED_FOR_REVISION: "Returned for Revision",
            IN_REVIEW: "In Review",
            APPROVED: "Approved"
        }
    };
    Constants.IBC_ANSWER_TYPE = {
        MULTIPLE_CHOICE: "MULTIPLE_CHOICE",
        TABLE: "TABLE",
        FREE_TEXT: "FREE_TEXT",
        MULTI_SELECT: "MULTI_SELECT"
    };
    return Constants;
}());
