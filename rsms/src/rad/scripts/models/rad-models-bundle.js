'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var RadModelDTO = function () { };
extend(RadModelDTO, GenericModel);

var Authorization = function () { };
Authorization.prototype = {

    eagerAccessors: [
        { method: "loadIsotope", boolean: "Isotope_id" }
    ],

    RoomsRelationship: {
        name: 'PrincipalInvestigatorRoomRelation',
        className: 'Room',
        keyReference: 'Principal_investigator_id',
        otherKey: 'Room_id',
        paramValue: 'Key_id'
    },

    loadIsotope: function () {
        if (!this.Isotope) {
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    },

    loadRooms: function () {
        dataLoader.loadManyToManyRelationship(this, 'Rooms', this.RoomsRelationship, "getRoomsByPIId&id=" + this.Key_id);
    },

    loadRooms: function () {
        dataLoader.loadManyToManyRelationship(this, 'Rooms', this.RoomsRelationship, "getRoomsByPIId&id=" + this.Key_id);
    },

    loadActiveParcels: function () {

    }

}

// inherit from GenericModel
extend(Authorization, GenericModel);
'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var Carboy = function () { };
Carboy.prototype = {

    CarboyUseCyclesRelationship: {
        className: 'CarboyUseCycle',
        keyReference: 'Carboy_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    eagerAccessors: [
        { method: "loadCurrentCarboyUseCycle", boolean: "Current_carboy_use_cycle" }
    ],

    // TODO eager accessors, relationships, method names.
    loadCarboyUseCycles: function () {
        return dataLoader.loadOneToManyRelationship(this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship);
    },

    //if this carboy has a current use cycle, make sure it is a reference to the appropriate on in the dataStore
    loadCurrentCarboyUseCycle: function () {
        if (this.Current_carboy_use_cycle && dataStoreManager && dataStore.CarboyUseCycle) {
            this.Current_carboy_use_cycle = dataStoreManager.getById("CarboyUseCycle", this.Current_carboy_use_cycle.Key_id);
        }
    }
}

// inherit from GenericModel
extend(Carboy, GenericModel);
'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyReadingAmount = function () { };
CarboyReadingAmount.prototype = {
    className: "CarboyReadingAmount",

    eagerAccessors: [
		{ method: "loadIsotope", boolean: 'Isotope_id' },
    ],

    // TODO eager accessors, relationships, method names.
    loadIsotope: function () {
        if (this.Isotope_id) {
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    }
}

// inherit from GenericModel
extend(CarboyReadingAmount, GenericModel);

'use strict';
/* Auto-generated stub file for the Carboy class. */

//constructor
var CarboyUseCycle = function () { };
CarboyUseCycle.prototype = {
    className: "CarboyUseCycle",

    eagerAccessors: [
        { method: "loadCarboy", boolean: 'Carboy_id' },
        { method: "loadRoom", boolean: 'Room_id' },
    ],

    CarboyReadingAmountsRelationship: {

        className: 'CarboyReadingAmount',
        keyReference: 'Carboy_use_cycle_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    // TODO eager accessors, relationships, method names.
    loadCarboy: function () {
        dataLoader.loadChildObject(this, 'Carboy', 'Carboy', this.Carboy_id);
    },

    loadDrum: function () {
        dataLoader.loadChildObject(this, 'Drum', 'Drum', this.Drum_id);
    },

    loadCarboy_reading_amounts: function () {
        dataLoader.loadOneToManyRelationship(this, 'Carboy_reading_amounts', this.CarboyReadingAmountsRelationship);
    },

    loadPrincipalInvestigator: function () {
        dataLoader.loadChildObject(this, 'Principal_investigator', 'PrincipalInvestigator', this.Principal_investigator_id);
    },

    loadRoom: function () {
        dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
    }
}

// inherit from GenericModel
extend(CarboyUseCycle, GenericModel);
'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var Department = function () {
    Department.url = "";
    Department.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllDepartments";
};
Department.prototype = {}

// inherit from GenericModel
extend(Department, GenericModel);

Department();

'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var Drum = function () { };
Drum.prototype = {

    WasteBagsRelationship: {
        className: 'WasteBag',
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    SVCollectionRelationship: {
        className: 'ScintVialCollection',
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },

    DrumWipeTestRelationship: {

        className: 'DrumWipeTest',
        keyReference: 'Drum_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    loadWasteBags: function () {
        if (!this.WasteBags) {
            dataLoader.loadOneToManyRelationship(this, 'WasteBags', this.WasteBagsRelationship);
        }
    },
    loadScintVialCollections: function () {
        if (!this.ScintVialCollections) {
            dataLoader.loadOneToManyRelationship(this, 'ScintVialCollections', this.SVCollectionRelationship);
        }
    },
    loadDrumWipeTest: function () {
        dataLoader.loadOneToManyRelationship(this, 'Wipe_test', this.DrumWipeTestRelationship);
    }
}

// inherit from GenericModel
extend(Drum, GenericModel);


'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var DrumWipe = function () { };
DrumWipe.prototype = {
    className: "DrumWipe",
    Class: "DrumWipe"
}

// inherit from GenericModel
extend(DrumWipe, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var DrumWipeTest = function () { };
DrumWipeTest.prototype = {
    className: "DrumWipeTest",
    Class: 'DrumWipeTest',
    eagerAccessors: [
        {
            method: "loadDrum_wipes",
            boolean: 'HasWipes'
        }
    ],

    DrumWipesRelationship: {
        className: 'DrumWipe',
        keyReference: 'Drum_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    loadDrum_wipes: function () {
        this.Drum_wipes = [];
        dataLoader.loadOneToManyRelationship(this, 'Drum_wipes', this.DrumWipesRelationship);
    },

    createWipeTests: function () {
        if (!this.Drum_wipes) this.Drum_wipes = [];
        for (var i = 0; i < 3; i++) {
            var wipe = new window.DrumWipe();
            wipe.Drum_wipe_test_id = this.Key_id ? this.Key : null;
            wipe.Rading_type = "LSC";
            wipe.edit = true;
            wipe.Class = 'DrumWipe';
            this.Drum_wipes.push(wipe);
        }
    },

    addWipe: function () {
        if (!this.Drum_wipes) this.Drum_wipes = [];
        var wipe = this.inflator.instantiateObjectFromJson(new window.DrumWipe());
        wipe.Class = 'DrumWipe';
        wipe.Drum_wipe_test_id = this.Key_id ? this.Key : null;
        wipe.Rading_type = "LSC";
        wipe.edit = true;
        this.Drum_wipes.push(wipe);
        return wipe;
    }
}

// inherit from GenericModel
extend(DrumWipeTest, GenericModel);
'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var Hazard = function () {
    Hazard.url = "";
    Hazard.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllHazards";
};
Hazard.prototype = {

    ID_prop: "Hazard_id",
    //eagerAccessors:[{method:"loadSubHazards",boolean:"HasChildren"}],

    SubHazardsRelationship: {
        className: 'HazardDto',
        keyReference: 'Parent_hazard_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    loadSubHazards: function () {
        if (!this.ActiveSubHazards) {
            return dataLoader.loadOneToManyRelationship(this, 'ActiveSubHazards', this.SubHazardsRelationship);
        }
    }

}

//inherit from and extend GenericModel
extend(Hazard, GenericModel);

Hazard();

'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var Inspection = function () { };
Inspection.prototype = {
    className: "Inspection",

    loadPrincipalInvestigator: function () {
        if (this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
    }
}

// inherit from GenericModel
extend(Inspection, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var InspectionWipe = function () { };
InspectionWipe.prototype = {
    className: "InspectionWipe",

    eagerAccessors: [
		{ method: 'loadRoom', boolean: "Room_id" }
    ],

    loadRoom: function () {
        dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
    }
}

// inherit from GenericModel
extend(InspectionWipe, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var InspectionWipeTest = function () { };
InspectionWipeTest.prototype = {
    className: "InspectionWipeTest",
    InspectionWipesRelationship: {

        className: 'InspectionWipe',
        keyReference: 'Inspection_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },
    loadInspection_wipes: function () {
        this.Inspection_wipes = [];
        dataLoader.loadOneToManyRelationship(this, 'Inspection_wipes', this.InspectionWipesRelationship);
    },
}

// inherit from GenericModel
extend(InspectionWipeTest, GenericModel);
'use strict';

//constructor
var Isotope = function () { };
Isotope.prototype = {

    // no loaders or eager accesors to add currently

}

// inherit from GenericModel
extend(Isotope, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var IsotopeAmountDTO = function () { };
IsotopeAmountDTO.prototype = {}

// inherit from GenericModel
extend(IsotopeAmountDTO, GenericModel);
'use strict';

//constructor
var MiscellaneousWaste = function () { };

MiscellaneousWaste.prototype = {
    className: "MiscellaneousWaste"
}

//inherit from and extend GenericModel
extend(MiscellaneousWaste, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var MiscellaneousWipe = function () { };
MiscellaneousWipe.prototype = {
    className: "MiscellaneousWipe",
}

// inherit from GenericModel
extend(MiscellaneousWipe, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var MiscellaneousWipeTest = function () { };
MiscellaneousWipeTest.prototype = {
    className: "MiscellaneousWipeTest",

    MiscellaneousWipeRelationship: {
        className: 'MiscellaneousWipe',
        keyReference: 'Miscellaneous_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },
    loadMiscellaneous_wipes: function () {
        this.Miscellaneous_wipes = [];
        console.log(this.Miscellaneous_wipes);
        dataLoader.loadOneToManyRelationship(this, 'Miscellaneous_wipes', this.MiscellaneousWipeRelationship);
    }
}

// inherit from GenericModel
extend(MiscellaneousWipeTest, GenericModel);
'use strict';

//constructor
var Parcel = function () { };
Parcel.prototype = {

    className: "Parcel",

    eagerAccessors: [
        { method: "loadPurchaseOrder", boolean: 'Purchase_order_id' },
        { method: "loadAuthorization", boolean: 'Authorization_id' },
        { method: "loadParcelWipeTest", boolean: 'HasTests' }
    ],

    IsotopeRelationship: {
        className: 'Isotope',
        keyReference: 'Isotope_id',
        queryString: 'getIsotopeById',
        queryParam: ''
    },

    PurchaseOrderRelationship: {
        className: 'PurchaseOrder',
        keyReference: 'Purchase_order_id',
        queryString: 'getPurchaseOrderById',
        queryParam: ''
    },

    AuthorizationRelationship: {
        className: 'Authorization',
        keyReference: 'Parcel_id',
        queryParam: ''
    },

    ParcelUsesRelationship: {

        className: 'ParcelUse',
        keyReference: 'Parcel_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    ParcelWipeTestRelationship: {

        className: 'ParcelWipeTest',
        keyReference: 'Parcel_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'

    },

    loadIsotope: function () {
        if (!this.Isotope) {
            dataLoader.loadChildObject(this, 'Isotope', 'Isotope', this.Isotope_id);
        }
    },

    loadPurchaseOrder: function () {
        if (!this.PurchaseOrder) {
            dataLoader.loadChildObject(this, 'PurchaseOrder', 'PurchaseOrder', this.Purchase_order_id);
        }
    },

    loadPrincipalInvestigator: function () {
        if (!this.PrincipalInvestigator) {
            return dataLoader.loadChildObject(this, 'Principal_investigator', 'PrincipalInvestigator', this.Principal_investigator_id);

        }
    },

    loadUses: function () {
        return dataLoader.loadOneToManyRelationship(this, 'ParcelUses', this.ParcelUsesRelationship);

    },
    loadAuthorization: function () {
        if (!this.Authorization) {
            dataLoader.loadChildObject(this, "Authorization", "Authorization", this.Authorization_id);
        }
    },
    loadParcelWipeTest: function () {
        if (!this.Wipe_test) {
            dataLoader.loadOneToManyRelationship(this, 'Wipe_test', this.ParcelWipeTestRelationship);
        }
    }
}

// inherit from GenericModel
extend(Parcel, GenericModel);

'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var ParcelUse = function () { };
ParcelUse.prototype = {
    eagerAccessors: [
        { method: "loadParcelUseAmounts", boolean: 'Key_id' },
        { method: "loadDestinationParcel", boolean: 'Destination_parcel_id' }

    ],
    AmountsRelationship: {
        className: 'ParcelUseAmount',
        keyReference: 'Parcel_use_id',
        queryString: 'getParcelUseAmountById',
        paramValue: 'Key_id',
        queryParam: ''
    },
    DestiantionParcelRelationship: {
        className: 'Parcel',
        keyReference: 'Destination_parcel_id',
        queryString: 'getUserById',
        queryParam: ''
    },
    loadParcel: function () {
        if (!this.Parcel) {
            dataLoader.loadChildObject(this, 'Parcel', 'Parcel', this.Parcel_id);
        }
    },
    loadParcelUseAmounts: function () {
        if (!this.ParcelUseAmounts) {
            console.log('hello');
            dataLoader.loadOneToManyRelationship(this, "ParcelUseAmounts", this.AmountsRelationship);
        }
    },
    getIsPickedUp: function () {
        if (!this.IsPickedUp) {
            this.IsPickedUp = true;
            if (!this.ParcelUseAmounts.length) {
                this.IsPickedUp = false;
            }
            for (var i = 0; i < this.ParcelUseAmounts.length; i++) {
                if (!this.ParcelUseAmounts[i].IsPickedUp) {
                    this.IsPickedUp = false;
                    break;
                }
            }
        }
        return this.IsPickedUp;
    },

    loadDestinationParcel: function () {
        if (this.Destination_parcel_id) {
            dataLoader.loadChildObject(this, 'DestinationParcel', 'Parcel', this.Destination_parcel_id);
        }
    }
}

// inherit from GenericModel
extend(ParcelUse, GenericModel);
'use strict';
/* Auto-generated stub file for the ParcelUseAmount class. */

//constructor
var ParcelUseAmount = function () { };
ParcelUseAmount.prototype = {

    eagerAccessors: [
		{ method: "loadCarboy", boolean: "Carboy_id" },
    ],

    // Any future accessors, eager loaders, etc. will go here.
    loadCarboy: function () {
        dataLoader.loadChildObject(this, "Carboy", "CarboyUseCycle", this.Carboy_id);
    },

    // Any future accessors, eager loaders, etc. will go here.
    loadSolidsContainer: function () {
        dataLoader.loadChildObject(this, "SolidsContainer", "SolidsContainer", this.SolidsContainer_id);
    },
}

// inherit from GenericModel
extend(ParcelUseAmount, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var ParcelWipe = function () { };
ParcelWipe.prototype = {
    className: "ParcelWipe",
    Class: "ParcelWipe"
}

// inherit from GenericModel
extend(ParcelWipe, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var ParcelWipeTest = function () { };
ParcelWipeTest.prototype = {
    className: "ParcelWipeTest",
    Class: 'ParcelWipeTest',
    eagerAccessors: [
        {
            method: "loadParcel_wipes",
            boolean: 'Key_id'
        }
    ],

    ParcelWipesRelationship: {
        className: 'ParcelWipe',
        keyReference: 'Parcel_wipe_test_id',
        paramValue: 'Key_id',
        paramName: 'id'

    },
    loadParcel_wipes: function () {
        if(!this.Parcel_wipes)this.Parcel_wipes = [];
        dataLoader.loadOneToManyRelationship(this, 'Parcel_wipes', this.ParcelWipesRelationship);
    },

    loadPrincipal_investigator: function () {

    },

    createWipeTests: function () {
        if (!this.Parcel_wipes) this.Parcel_wipes = [];
        for (var i = 0; i < 7; i++) {
            var wipe = new window.ParcelWipe();
            wipe.Parcel_wipe_test_id = this.Key_id ? this.Key : null;
            wipe.Rading_type = "LSC";
            wipe.edit = true;
            wipe.Class = 'ParcelWipe';
            if (i == 0) wipe.Location = "Background";
            this.Parcel_wipes.push(wipe);
        }
    },

    addWipe: function () {
        var wipe = this.inflator.instantiateObjectFromJson(new window.ParcelWipe());
        wipe.Class = 'ParcelWipe';
        wipe.Parcel_wipe_test_id = this.Key_id ? this.Key : null;
        wipe.Rading_type = "LSC";
        wipe.edit = true;
        this.Parcel_wipes.push(wipe);
    }
}

// inherit from GenericModel
extend(ParcelWipeTest, GenericModel);
'use strict';
/* Auto-generated stub file for the Authorization class. */

//constructor
var PIAuthorization = function () { };
PIAuthorization.prototype = {
    className: "PIAuthorization",
    Class: "PIAuthorization",
    eagerAccessors: [
        //{method:"loadRooms", boolean:true},
        { method: "loadAuthorizations", boolean: true },
        { method: "loadDepartments", boolean: true }
    ],
    AuthorizationsRelationship: {
        className: 'Authorization',
        keyReference: 'Pi_authorization_id',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    RoomsRelationship: {
        table: 'pi_authorization_room',
        childClass: 'Room',
        parentProperty: 'Rooms',
        isMaster: true
    },

    DepartmentsRelationship: {
        table: 'pi_authorization_department',
        childClass: 'Department',
        parentProperty: 'Departments',
        isMaster: true
    },

    instantiateAuthorizations: function () {
        this.Authorizations = this.inflator.instateAllObjectsFromJson(this.Authorizations);
    },

    loadAuthorizations: function () {
        dataLoader.loadOneToManyRelationship(this, "Authorizations", this.AuthorizationsRelationship);
    },

    loadRooms: function () {
        return dataLoader.loadManyToManyRelationship(this, this.RoomsRelationship);
    },
    loadDepartments: function () {
        return dataLoader.loadManyToManyRelationship(this, this.DepartmentsRelationship);
    },
}

// inherit from GenericModel
extend(PIAuthorization, GenericModel);

'use strict';
/* Auto-generated stub file for the Pickup class. */

//constructor
var Pickup = function () { };
Pickup.prototype = {
    className: "Pickup",

    eagerAccessors: [
		{ method: "loadPrincipalInvestigator", boolean: "Principal_investigator_id" },
    ],


    CurrentScintVialCollectionRelationship: {
        className: 'ScintVialCollection',
        keyReference: 'Pickup_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    CarboyUseCyclesRelationship: {
        className: 'CarboyUseCycle',
        keyReference: 'Pickup_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    WasteBagRelationship: {
        className: 'WasteBag',
        keyReference: 'Pickup_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    loadPrincipalInvestigator: function () {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if (!this.PrincipalInvestigator && this.Principal_investigator_id) {
            dataLoader.loadChildObject(this, 'PrincipalInvestigator', 'PrincipalInvestigator', this.Principal_investigator_id);
        }
    },

    loadCarboyUseCycles: function () {
        dataLoader.loadOneToManyRelationship(this, 'Carboy_use_cycles', this.CarboyUseCyclesRelationship);
    },

    loadWasteBags: function () {
        this.WasteBags = [];
        dataLoader.loadOneToManyRelationship(this, 'Waste_bags', this.WasteBagRelationship);
        dataLoader.loadOneToManyRelationship(this, 'Waste_bagssss', this.WasteBagRelationship);

    },

    loadCurrentScintVialCollections: function () {
        this.CurrentScintVialCollections = [];
        dataLoader.loadOneToManyRelationship(this, 'Scint_vial_collections', this.CurrentScintVialCollectionRelationship);
    }

}

// inherit from GenericModel
extend(Pickup, GenericModel);
'use strict';
/* Auto-generated stub file for the Drum class. */

//constructor
var PIQuarterlyInventory = function () { };
PIQuarterlyInventory.prototype = {
    className: "PIQuarterlyInventory"
}

// inherit from GenericModel
extend(PIQuarterlyInventory, GenericModel);
'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var PIWipe = function () { };
PIWipe.prototype = {}

// inherit from GenericModel
extend(PIWipe, GenericModel);
'use strict';
/* Auto-generated stub file for the ParcelUse class. */

//constructor
var PIWipeTest = function () { };
PIWipeTest.prototype = {
    eagerAccessors: [
        { method: "loadPIWipes", boolean: 'Key_id' }
    ],
    PIWIpesRelationship: {
        className: 'PIWipe',
        keyReference: 'Pi_wipe_test_id',
        paramValue: 'Key_id',
        queryParam: ''
    },

    loadPIWipes: function () {
        dataLoader.loadOneToManyRelationship(this, "PIWipes", this.PIWIpesRelationship);
    }
}

// inherit from GenericModel
extend(PIWipeTest, GenericModel);
'use strict';

//generic model to be "extended" by "POJOs"

//constructor
//constructor
var PrincipalInvestigator = function () {
    PrincipalInvestigator.url = "";
    PrincipalInvestigator.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
};

PrincipalInvestigator.prototype = {
    eagerAccessors: [
        //{ method: "loadUser", boolean: "User_id" },
        { method: "loadCarboys", boolean: true },
        { method: "loadWasteBags", boolean: true },
        { method: "loadRooms", boolean: true },
    ],

    UserRelationship: {
        className: 'User',
        keyReference: 'User_id',
        queryString: 'getUserById',
        queryParam: ''
    },

    LabPersonnelRelationship: {
        className: 'User',
        keyReference: 'Supervisor_id',
        queryString: 'getUserById',
        queryParam: ''
    },

    PrincipalInvestigatorRoomRelationRelationship: {
        Class: 'PrincipalInvestigatorRoomRelation',
        foreignKey: 'Principal_investigator_id',
        queryString: 'getPrincipalInvestigatorRoomRelationsByPiId&id=',
        queryParam: 'Key_id'
    },

    RoomsRelationship: {
        table: 'principal_investigator_room',
        childClass: 'Room',
        parentProperty: 'Rooms',
        isMaster: true
    },

    AuthorizationsRelationship: {
        className: 'Authorization',
        keyReference: 'Principal_investigator_id',
        methodString: 'getAuthorizationsByPIId',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    ActiveParcelsRelationship: {
        className: 'Parcel',
        keyReference: 'Principal_investigator_id',
        methodString: 'getActiveParcelsFromPIById',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PurchaseOrdersRelationship: {
        className: 'PurchaseOrder',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    WasteBagsRelationship: {
        className: 'WasteBag',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    CurrentWasteBagRelationship: {
        className: 'WasteBag',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id',
        where: [{ 'Pickup_id': "IS NULL" }]
    },

    CurrentScintVialCollectionRelationship: {
        className: 'ScintVialCollection',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id',
        where: [{ 'Pickup_id': "IS NULL" }]
    },


    CarboyUseCyclesRelationship: {
        className: 'CarboyUseCycle',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PickupsRelationship: {
        className: 'Pickup',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    Pi_AuthorizationsRelationship: {
        className: 'PIAuthorization',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    WipeTestsRelationship: {
        className: 'PIWipeTest',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },

    PIAuthorizationsRelationship: {
        className: 'PIAuthorization',
        keyReference: 'Principal_investigator_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: 'id'
    },


    Buildings: {},

    loadActiveParcels: function () {
        return dataLoader.loadOneToManyRelationship(this, 'ActiveParcels', this.ActiveParcelsRelationship, null, true);
    },

    loadRooms: function () {
        return dataLoader.loadManyToManyRelationship(this, this.RoomsRelationship);
    },

    loadPurchaseOrders: function () {
        return dataLoader.loadOneToManyRelationship(this, 'PurchaseOrders', this.PurchaseOrdersRelationship);
    },
    loadCarboyUseCycles: function () {
        return dataLoader.loadOneToManyRelationship(this, 'CarboyUseCycles', this.CarboyUseCyclesRelationship);
    },

    loadPickups: function () {
        return dataLoader.loadOneToManyRelationship(this, 'Pickups', this.PickupsRelationship);
    },

    loadPIAuthorizations: function () {
        return dataLoader.loadOneToManyRelationship(this, 'Pi_authorization', this.PIAuthorizationsRelationship);
    },

    loadPIWipeTests: function () {
        return dataLoader.loadOneToManyRelationship(this, 'WipeTests', this.WipeTestsRelationship);
    },

    loadUser: function () {
        if (!this.User && this.User_id) {
            dataLoader.loadChildObject(this, 'User', 'User', this.User_id);
        }
    },

    loadWasteBags: function () {
        return dataLoader.loadOneToManyRelationship(this, 'WasteBags', this.WasteBagsRelationship);
    },

    loadCurrentWasteBag: function () {
        //return dataLoader.loadOneToManyRelationship(this, 'CurrentWasteBag', this.CurrentWasteBagRelationship);
    },

    loadCurrentScintVialCollections: function () {
        //this.CurrentScintVialCollections = [];
        dataLoader.loadOneToManyRelationship(this, 'CurrentScintVialCollections', this.CurrentScintVialCollectionRelationship);
    },
    loadLabPersonnel: function () {
        //this.CurrentScintVialCollections = [];
        dataLoader.loadOneToManyRelationship(this, 'LabPersonnel', this.LabPersonnelRelationship);
    },
    getName: function () {
        this.Name = "";

        if (this.User) this.Name = this.User.Name;
        console.log(this.Name);
        return this.Name;
    }

}
extend(PrincipalInvestigator, GenericModel);

'use strict';
/* Auto-generated stub file for the PurchaseOrder class. */

//constructor
var PurchaseOrder = function () { };
PurchaseOrder.prototype = {

    loadPrincipalInvestigator: function () {
        if (!this.Principal_investigator) {
            dataLoader.loadChildObject(this, 'Principal_investigator',
                'PrincipalInvestigator', this.Principal_investigator_id);
        }
    }

}

// inherit from GenericModel
extend(PurchaseOrder, GenericModel);
/* Auto-generated stub file for the Drum class. */

//constructor
var QuarterlyInventory = function () { };
QuarterlyInventory.prototype = {
    className: "QuarterlyInventory",
    PIQaurterlyInventoriesRelationship: {
        className: 'PIQuarterlyInventory',
        keyReference: 'quarterly_inventory_id',
        methodString: '',
        paramValue: 'Key_id',
        paramName: ''
    },
    loadPi_quarterly_inventories: function () {
        dataLoader.loadOneToManyRelationship(this, 'Pi_quarterly_inventories', this.PIQaurterlyInventoriesRelationship);
    }

}

// inherit from GenericModel
extend(QuarterlyInventory, GenericModel);
'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var RadModelDto = function () { };
RadModelDto.prototype = {}

// inherit from GenericModel
extend(RadModelDto, GenericModel);
'use strict';

//constructor
var Room = function () {
    Room.url = "";
    Room.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllRooms";
};
Room.prototype = {

    // many-to-many relationship for rooms-pi
    PIRelationship: {
        name: 'PrincipalInvestigatorRoomRelation',
        className: 'PrincipalInvestigator',
        keyReference: 'Room_id',
        otherKey: 'Principal_investigator_id',
        paramValue: 'Key_id'
    },

    // one-to-many relationship
    ContainerRelationship: {
        className: 'SolidsContainer',
        keyReference: 'Room_id',
        paramValue: 'Key_id'
    },

    loadPrincipalInvestigators: function () {
        if (!this.PrincipalInvestigators) {
            dataLoader.loadManyToManyRelationship(this, 'PrincipalInvestigators', this.PIRelationship);
        }
    }
}

extend(Room, GenericModel);

Room();

angular
    .module("room", [])
    .value("Room", Room);
'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var ScintVialCollection = function () { };

ScintVialCollection.prototype = {
    className: "ScintVialCollection",
    loadPickup: function () {
        if (!this.Pickup && this.Pickup_id) {
            dataLoader.loadChildObject(this, 'Pickup', 'Pickup', this.Pickup_id);
        }
    },

    loadDrum: function () {
        dataLoader.loadChildObject(this, 'Drum', 'Drum', this.Drum_id);
    },
}

//inherit from and extend GenericModel
extend(ScintVialCollection, GenericModel);
'use strict';
/* Auto-generated stub file for the SolidsContainer class. */

//constructor
var SolidsContainer = function () { };
SolidsContainer.prototype = {
    eagerAccessors: [/*{method:"loadWasteBagsForPickup", boolean: 'WasteBagsForPickup'}*/],

    WasteBagsForPickupRelationship: {
        className: 'WasteBag',
        keyReference: 'Container_id',
        paramValue: 'Key_id',
        paramName: 'id',
        where: [{ 'Pickup_id': "IS NULL" }, { 'Date_removed': "NOT NULL" }]
    },

    CurrentWasteBagsRelationship: {
        className: 'WasteBag',
        keyReference: 'Container_id',
        paramValue: 'Key_id',
        paramName: 'id',
        where: [{ 'Date_removed': "IS NULL" }]
    },

    loadRoom: function () {
        if (!this.Room && this.Room_id) {
            dataLoader.loadChildObject(this, 'Room', 'Room', this.Room_id);
        }
    },

    loadWasteBagsForPickup: function () {
        //alert('?')
        // this.WasteBagsForPickup = [];
        dataLoader.loadOneToManyRelationship(this, 'WasteBagsForPickup', this.WasteBagsForPickupRelationship, this.WasteBagsForPickupRelationship.where);
    },

    loadCurrentWasteBags: function () {
        // this.CurrentWasteBags = [];
        dataLoader.loadOneToManyRelationship(this, 'CurrentWasteBags', this.WasteBagsForPickupRelationship, this.CurrentWasteBagsRelationship.where);
    }

}

// inherit from GenericModel
extend(SolidsContainer, GenericModel);
'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var User = function (api) {
    User.url = "";
    User.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllUsers";
};

User.prototype = {
    className: "User",
    loadSupervisor: function () {
        // not all users have a supervisor, don't try to load something that doesn't exist.
        if (!this.Supervisor && this.Supervisor_id) {
            dataLoader.loadChildObject(this, 'Supervisor', 'PrincipalInvestigator', this.Supervisor_id);
        }
    }
}

//inherit from and extend GenericModel
extend(User, GenericModel);
'use strict';
/* Auto-generated stub file for the WasteBag class. */

//constructor
var WasteBag = function () { };
WasteBag.prototype = {

    eagerAccessors: [],

    loadContainer: function () {
        if (!this.Container && this.Container_id) {
            dataLoader.loadChildObject(this, 'Container', 'SolidsContainer', this.Container_id);
        }
    },

    loadDrum: function () {
        if (!this.Drum && this.Drum_id) {
            dataLoader.loadChildObject(this, 'Drum', 'Drum', this.Drum_id);
        }
    },

    loadPickup: function () {
        if (!this.Pickup && this.Pickup_id) {
            dataLoader.loadChildObject(this, 'Pickup', 'Pickup', this.Pickup_id);
        }
    },

    loadParcelUseAmounts: function () {
        if (!this.Pickup && this.Pickup_id) {
            dataLoader.loadChildObject(this, 'Pickup', 'Pickup', this.Pickup_id);
        }
    }

}

// inherit from GenericModel
extend(WasteBag, GenericModel);
'use strict';
/* Auto-generated stub file for the WasteType class. */

//constructor
var WasteType = function () { };
WasteType.prototype = {

    // Future accessors, eager loaders, etc will go here.
}

// inherit from GenericModel
extend(WasteType, GenericModel);

//constructor
var OtherWasteType = function () { };
OtherWasteType.prototype = {
    className: "OtherWasteType",
    Class: "OtherWasteType"
}

// inherit from GenericModel
extend(OtherWasteType, GenericModel);

//constructor
var RadCondition = function () { };
RadCondition.prototype = {
    className: "RadCondition",
    Class: "RadCondition"
}

// inherit from GenericModel
extend(RadCondition, GenericModel);