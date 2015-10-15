var Constants = (function () {

    var constants = {};

    constants.PENDING_CHANGE = {
        USER_STATUS:{
            NO_LONGER_CONTACT:"Still in this lab, but no longer a contact",
            NOW_A_CONTACT:"Still in this lab, but now a lab contact",
            MOVED_LABS:"In another PI's lab",
            LEFT_UNIVERSITY:"No longer at the univserity"
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
            READ_ONLY:"Read Only"
        }
    };
    
    constants.CARBOY_USE_CYCLE = {
        STATUS:{
            AVAILABLE:"Available",
            IN_USE:"In Use",
            DECAYING:"Decaying",
            PICKED_UP:"Picked Up"
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
            OVERDUE_CAP:"OVERDUE CAP",
            PENDING_EHS_APPROVAL:"PENDING EHS APPROVAL",
            OVERDUE_FOR_INSPECTION:"OVERDUE FOR INSPECTION"
        }
    };
    
    constants.HAZARD = {
        NAME:{
            
        }
    };
  
    return constants;

})();
